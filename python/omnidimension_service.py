#!/usr/bin/env python3
"""
Omni Dimension Integration Service
Handles all interactions with Omni Dimension API for calls, chats, and web calls
"""

import os
import json
import time
import logging
from typing import Dict, Optional, List
from datetime import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS
from omnidimension import Client
import threading
import requests

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

app = Flask(__name__)
CORS(app)

# Configuration
API_KEY = os.getenv('OMNIDIMENSION_API_KEY', '')
AGENT_NAME = os.getenv('AGENT_NAME', '1930')
AGENT_VOICE_ID = os.getenv('AGENT_VOICE_ID', '9NQoUbj2TvirWemhZkpT')
AGENT_MODEL = os.getenv('AGENT_MODEL', 'gpt-4.1-mini')
AGENT_TEMPERATURE = float(os.getenv('AGENT_TEMPERATURE', '0.7'))
WEBHOOK_URL = os.getenv('WEBHOOK_URL', 'http://localhost/api/webhook.php')
PHP_API_URL = os.getenv('PHP_API_URL', 'http://localhost')

# Default welcome message and context
DEFAULT_WELCOME_MESSAGE = """Welcome to the 1930 helpline for reporting cybercrime, fraud, scams, and wrong money transfers. Please let me know your preferred language: English or Hindi."""

DEFAULT_CONTEXT = [
    {
        "title": "Language Selection",
        "body": "Start by asking the caller's preferred language to conduct the conversation: 'Please let me know your preferred language: English or Hindi.' Move forward in the chosen language.",
        "is_enabled": True
    },
    {
        "title": "Issue Identification",
        "body": "Ask open-ended questions to understand the caller's issue: 'Could you tell me more about what happened?' Listen carefully and empathize with their situation.",
        "is_enabled": True
    },
    {
        "title": "Gathering Information",
        "body": "Collect necessary details for complaint registration: full name, parent's name, phone number related to the scam, alternative number, email address, location, pincode, scammer's name, and the amount involved. Confirm each piece of information to ensure accuracy.",
        "is_enabled": True
    },
    {
        "title": "Providing Solutions",
        "body": "Based on the details gathered, provide appropriate guidance and solutions to handle the situation effectively and immediately.",
        "is_enabled": True
    },
    {
        "title": "Complaint Registration",
        "body": "Log all collected details into an Excel sheet for further investigation and official complaint registration. Ensure information is captured accurately and systematically.",
        "is_enabled": True
    },
    {
        "title": "Closing",
        "body": "Wrap up the call by confirming all details have been logged and reassure the caller: 'Thank you for providing the details. We have registered your complaint. We are here to assist you further if needed.'",
        "is_enabled": True
    }
]

# Initialize Omni Dimension client
client = None
agent_id = None
active_calls = {}  # Track active calls: {call_id: {session_id, start_time, type, status}}
active_chats = {}  # Track active chats: {chat_id: {session_id, start_time, status}}

def init_client():
    """Initialize Omni Dimension client and create/retrieve agent"""
    global client, agent_id
    
    if not API_KEY:
        logger.error("OMNIDIMENSION_API_KEY not set")
        return False
    
    if not API_KEY.startswith('-'):
        logger.warning(f"API key format may be incorrect. Expected to start with '-', got: {API_KEY[:5]}...")
    
    try:
        logger.info("Initializing Omni Dimension client...")
        client = Client(API_KEY)
        logger.info("Client initialized successfully")
        
        # Try to get existing agent or create new one
        try:
            logger.info("Attempting to list existing agents...")
            agents = client.agent.list()
            logger.info(f"Found {len(agents.get('data', []))} agents")
            for agent in agents.get('data', []):
                if agent.get('name') == AGENT_NAME:
                    agent_id = agent.get('id')
                    logger.info(f"Found existing agent: {agent_id}")
                    break
        except Exception as e:
            logger.warning(f"Could not list agents: {e}")
            import traceback
            logger.debug(traceback.format_exc())
        
        if not agent_id:
            logger.info("No existing agent found, creating new agent...")
            # Get welcome message and context from environment or use defaults
            welcome_message = os.getenv('AGENT_WELCOME_MESSAGE', DEFAULT_WELCOME_MESSAGE)
            context_str = os.getenv('AGENT_CONTEXT', '')
            
            if context_str:
                try:
                    context_breakdown = json.loads(context_str)
                except json.JSONDecodeError:
                    logger.warning("Invalid JSON in AGENT_CONTEXT, using defaults")
                    context_breakdown = DEFAULT_CONTEXT
            else:
                context_breakdown = DEFAULT_CONTEXT
            
            # Create new agent
            try:
                response = client.agent.create(
                    name=AGENT_NAME,
                    welcome_message=welcome_message,
                    context_breakdown=context_breakdown,
                    call_type="Incoming",
                    transcriber={
                        "provider": "Azure",
                        "silence_timeout_ms": 400
                    },
                    model={
                        "model": AGENT_MODEL,
                        "temperature": AGENT_TEMPERATURE
                    },
                    voice={
                        "provider": "eleven_labs",
                        "voice_id": AGENT_VOICE_ID
                    }
                )
                agent_id = response.get('id')
                if agent_id:
                    logger.info(f"Created new agent: {agent_id}")
                else:
                    logger.error(f"Agent creation response missing ID: {response}")
                    return False
            except Exception as e:
                logger.error(f"Failed to create agent: {e}")
                import traceback
                logger.error(traceback.format_exc())
                return False
        
        if not agent_id:
            logger.error("Agent ID is still None after initialization attempt")
            return False
        
        logger.info(f"Agent initialization complete. Agent ID: {agent_id}")
        return True
    except Exception as e:
        logger.error(f"Failed to initialize Omni Dimension client: {e}")
        import traceback
        logger.error(traceback.format_exc())
        return False

def notify_php_webhook(event_type: str, data: Dict):
    """Notify PHP backend about events"""
    try:
        payload = {
            'event_type': event_type,
            'data': data,
            'timestamp': datetime.now().isoformat()
        }
        response = requests.post(
            WEBHOOK_URL,
            json=payload,
            headers={'Content-Type': 'application/json'},
            timeout=5
        )
        if response.status_code == 200:
            logger.info(f"Webhook notification sent: {event_type}")
        else:
            logger.warning(f"Webhook notification failed: {response.status_code}")
    except Exception as e:
        logger.error(f"Failed to send webhook notification: {e}")

@app.route('/', methods=['GET'])
def root():
    """Root endpoint - shows available endpoints"""
    return jsonify({
        'service': 'Omni Dimension Integration Service',
        'status': 'running',
        'agent_id': agent_id if agent_id else 'Not initialized - check /agent/info',
        'agent_initialized': agent_id is not None,
        'endpoints': {
            'health': '/health',
            'agent_info': '/agent/info',
            'create_call': '/call/create (POST)',
            'end_call': '/call/end/<call_id> (POST)',
            'call_status': '/call/status/<call_id> (GET)',
            'create_chat': '/chat/create (POST)',
            'send_chat': '/chat/send/<chat_id> (POST)',
            'end_chat': '/chat/end/<chat_id> (POST)',
            'webhook': '/webhook (POST)'
        },
        'active_calls': len(active_calls),
        'active_chats': len(active_chats)
    })

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'ok',
        'agent_id': agent_id,
        'active_calls': len(active_calls),
        'active_chats': len(active_chats)
    })

@app.route('/agent/info', methods=['GET'])
def get_agent_info():
    """Get agent information and credits"""
    try:
        if not client or not agent_id:
            return jsonify({'error': 'Agent not initialized'}), 500
        
        # Get agent details
        agent = client.agent.get(agent_id)
        
        # Get account credits (if available in API)
        credits = None
        try:
            # Try to get credits from account endpoint if available
            account_info = client.account.get() if hasattr(client, 'account') else None
            if account_info:
                credits = account_info.get('credits', account_info.get('balance'))
        except:
            pass
        
        return jsonify({
            'success': True,
            'agent_id': agent_id,
            'agent_name': agent.get('name'),
            'credits': credits,
            'voice_id': AGENT_VOICE_ID,
            'model': AGENT_MODEL
        })
    except Exception as e:
        logger.error(f"Error getting agent info: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/call/create', methods=['POST'])
def create_call():
    """Create a new call (inbound or outbound)"""
    try:
        data = request.json
        call_type = data.get('call_type', 'inbound')  # 'inbound' or 'outbound'
        phone_number = data.get('phone_number')  # Required for outbound
        session_id = data.get('session_id')
        caller_name = data.get('caller_name', 'Anonymous')
        language = data.get('language', 'english')
        
        if not client or not agent_id:
            return jsonify({'error': 'Agent not initialized'}), 500
        
        if call_type == 'outbound' and not phone_number:
            return jsonify({'error': 'Phone number required for outbound calls'}), 400
        
        # Create call via Omni Dimension
        call_data = {
            'agent_id': agent_id,
            'call_type': 'Incoming' if call_type == 'inbound' else 'Outgoing'
        }
        
        if call_type == 'outbound':
            call_data['phone_number'] = phone_number
        
        # Create the call
        call_response = client.call.create(**call_data)
        call_id = call_response.get('id')
        
        if not call_id:
            return jsonify({'error': 'Failed to create call'}), 500
        
        # Track active call
        active_calls[call_id] = {
            'session_id': session_id,
            'start_time': datetime.now().isoformat(),
            'type': call_type,
            'status': 'connecting',
            'caller_name': caller_name,
            'language': language,
            'phone_number': phone_number
        }
        
        # Notify PHP backend
        notify_php_webhook('call.created', {
            'call_id': call_id,
            'session_id': session_id,
            'call_type': call_type,
            'phone_number': phone_number,
            'caller_name': caller_name
        })
        
        return jsonify({
            'success': True,
            'call_id': call_id,
            'session_id': session_id,
            'status': 'connecting'
        })
    except Exception as e:
        logger.error(f"Error creating call: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/call/end/<call_id>', methods=['POST'])
def end_call(call_id):
    """End a call immediately"""
    try:
        if not client:
            return jsonify({'error': 'Client not initialized'}), 500
        
        # End call via Omni Dimension
        try:
            client.call.end(call_id)
        except Exception as e:
            logger.warning(f"Error ending call via API: {e}")
        
        # Update local tracking
        if call_id in active_calls:
            call_info = active_calls[call_id]
            call_info['status'] = 'ended'
            call_info['end_time'] = datetime.now().isoformat()
            
            # Calculate duration
            start_time = datetime.fromisoformat(call_info['start_time'])
            end_time = datetime.now()
            duration = int((end_time - start_time).total_seconds())
            
            # Notify PHP backend
            notify_php_webhook('call.ended', {
                'call_id': call_id,
                'session_id': call_info.get('session_id'),
                'duration': duration,
                'end_time': call_info['end_time']
            })
            
            # Remove from active calls after a delay
            threading.Timer(5.0, lambda: active_calls.pop(call_id, None)).start()
        
        return jsonify({'success': True, 'call_id': call_id})
    except Exception as e:
        logger.error(f"Error ending call: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/call/status/<call_id>', methods=['GET'])
def get_call_status(call_id):
    """Get call status and details"""
    try:
        if not client:
            return jsonify({'error': 'Client not initialized'}), 500
        
        # Get call details from Omni Dimension
        try:
            call_info = client.call.get(call_id)
        except:
            call_info = {}
        
        # Merge with local tracking
        local_info = active_calls.get(call_id, {})
        
        return jsonify({
            'success': True,
            'call_id': call_id,
            'status': local_info.get('status', call_info.get('status', 'unknown')),
            'session_id': local_info.get('session_id'),
            'start_time': local_info.get('start_time'),
            'duration': local_info.get('duration'),
            'call_type': local_info.get('type'),
            'caller_name': local_info.get('caller_name'),
            'phone_number': local_info.get('phone_number'),
            'omni_data': call_info
        })
    except Exception as e:
        logger.error(f"Error getting call status: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/chat/create', methods=['POST'])
def create_chat():
    """Create a new chat session"""
    try:
        data = request.json
        session_id = data.get('session_id')
        caller_name = data.get('caller_name', 'Anonymous')
        language = data.get('language', 'english')
        
        if not client or not agent_id:
            return jsonify({'error': 'Agent not initialized'}), 500
        
        # Create chat via Omni Dimension
        chat_response = client.chat.create(agent_id=agent_id)
        chat_id = chat_response.get('id')
        
        if not chat_id:
            return jsonify({'error': 'Failed to create chat'}), 500
        
        # Track active chat
        active_chats[chat_id] = {
            'session_id': session_id,
            'start_time': datetime.now().isoformat(),
            'status': 'active',
            'caller_name': caller_name,
            'language': language
        }
        
        # Notify PHP backend
        notify_php_webhook('chat.created', {
            'chat_id': chat_id,
            'session_id': session_id,
            'caller_name': caller_name
        })
        
        return jsonify({
            'success': True,
            'chat_id': chat_id,
            'session_id': session_id,
            'status': 'active'
        })
    except Exception as e:
        logger.error(f"Error creating chat: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/chat/send/<chat_id>', methods=['POST'])
def send_chat_message(chat_id):
    """Send a message in a chat"""
    try:
        data = request.json
        message = data.get('message')
        role = data.get('role', 'user')  # 'user' or 'assistant'
        
        if not message:
            return jsonify({'error': 'Message required'}), 400
        
        if not client:
            return jsonify({'error': 'Client not initialized'}), 500
        
        # Send message via Omni Dimension
        response = client.chat.send(chat_id=chat_id, message=message, role=role)
        
        # Notify PHP backend
        notify_php_webhook('chat.message', {
            'chat_id': chat_id,
            'message': message,
            'role': role,
            'response': response
        })
        
        return jsonify({
            'success': True,
            'chat_id': chat_id,
            'response': response
        })
    except Exception as e:
        logger.error(f"Error sending chat message: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/chat/end/<chat_id>', methods=['POST'])
def end_chat(chat_id):
    """End a chat session"""
    try:
        if not client:
            return jsonify({'error': 'Client not initialized'}), 500
        
        # End chat via Omni Dimension
        try:
            client.chat.end(chat_id)
        except Exception as e:
            logger.warning(f"Error ending chat via API: {e}")
        
        # Update local tracking
        if chat_id in active_chats:
            chat_info = active_chats[chat_id]
            chat_info['status'] = 'ended'
            chat_info['end_time'] = datetime.now().isoformat()
            
            # Notify PHP backend
            notify_php_webhook('chat.ended', {
                'chat_id': chat_id,
                'session_id': chat_info.get('session_id'),
                'end_time': chat_info['end_time']
            })
            
            # Remove from active chats after a delay
            threading.Timer(5.0, lambda: active_chats.pop(chat_id, None)).start()
        
        return jsonify({'success': True, 'chat_id': chat_id})
    except Exception as e:
        logger.error(f"Error ending chat: {e}")
        return jsonify({'error': str(e)}), 500

@app.route('/webhook', methods=['POST'])
def handle_omni_webhook():
    """Handle webhooks from Omni Dimension"""
    try:
        data = request.json
        event_type = data.get('event_type')
        event_data = data.get('data', {})
        
        logger.info(f"Received webhook: {event_type}")
        
        # Handle different event types
        if event_type == 'call.started':
            call_id = event_data.get('call_id')
            if call_id in active_calls:
                active_calls[call_id]['status'] = 'active'
                notify_php_webhook('call.started', {
                    'call_id': call_id,
                    'session_id': active_calls[call_id].get('session_id')
                })
        
        elif event_type == 'call.ended':
            call_id = event_data.get('call_id')
            duration = event_data.get('duration', 0)
            if call_id in active_calls:
                call_info = active_calls[call_id]
                call_info['status'] = 'ended'
                call_info['duration'] = duration
                call_info['end_time'] = datetime.now().isoformat()
                notify_php_webhook('call.ended', {
                    'call_id': call_id,
                    'session_id': call_info.get('session_id'),
                    'duration': duration
                })
        
        elif event_type == 'call.disconnected':
            call_id = event_data.get('call_id')
            if call_id in active_calls:
                call_info = active_calls[call_id]
                call_info['status'] = 'disconnected'
                notify_php_webhook('call.disconnected', {
                    'call_id': call_id,
                    'session_id': call_info.get('session_id')
                })
                # Immediately remove to avoid charges
                active_calls.pop(call_id, None)
        
        elif event_type == 'chat.message':
            chat_id = event_data.get('chat_id')
            message = event_data.get('message')
            role = event_data.get('role', 'user')
            notify_php_webhook('chat.message', {
                'chat_id': chat_id,
                'message': message,
                'role': role
            })
        
        return jsonify({'success': True})
    except Exception as e:
        logger.error(f"Error handling webhook: {e}")
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    if init_client():
        logger.info("Omni Dimension service started")
        app.run(host='0.0.0.0', port=8000, debug=False)
    else:
        logger.error("Failed to initialize Omni Dimension client")
        exit(1)


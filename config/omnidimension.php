<?php
/**
 * Omni Dimension Configuration
 */

// Omni Dimension API Key
define('OMNIDIMENSION_API_KEY', getenv('OMNIDIMENSION_API_KEY') ?: '-oh1U47bH-Xf806Y7UA1Mddfyi4xEmTyDI8A5uOdC58');

// Python service endpoint (if running as separate service)
define('PYTHON_SERVICE_URL', 'http://localhost:8000');

// Webhook secret for verifying webhook requests
define('WEBHOOK_SECRET', getenv('WEBHOOK_SECRET') ?: 'your_webhook_secret_here');

// Agent configuration
define('AGENT_NAME', '1930');
define('AGENT_VOICE_ID', '9NQoUbj2TvirWemhZkpT'); // ElevenLabs voice ID
define('AGENT_MODEL', 'gpt-4.1-mini');
define('AGENT_TEMPERATURE', 0.7);

// Welcome message
define('AGENT_WELCOME_MESSAGE', 'Welcome to the 1930 helpline for reporting cybercrime, fraud, scams, and wrong money transfers. Please let me know your preferred language: English or Hindi.');

// Context breakdown for the agent
define('AGENT_CONTEXT', json_encode([
    [
        'title' => 'Language Selection',
        'body' => "Start by asking the caller's preferred language to conduct the conversation: 'Please let me know your preferred language: English or Hindi.' Move forward in the chosen language.",
        'is_enabled' => true
    ],
    [
        'title' => 'Issue Identification',
        'body' => "Ask open-ended questions to understand the caller's issue: 'Could you tell me more about what happened?' Listen carefully and empathize with their situation.",
        'is_enabled' => true
    ],
    [
        'title' => 'Gathering Information',
        'body' => "Collect necessary details for complaint registration: full name, parent's name, phone number related to the scam, alternative number, email address, location, pincode, scammer's name, and the amount involved. Confirm each piece of information to ensure accuracy.",
        'is_enabled' => true
    ],
    [
        'title' => 'Providing Solutions',
        'body' => "Based on the details gathered, provide appropriate guidance and solutions to handle the situation effectively and immediately.",
        'is_enabled' => true
    ],
    [
        'title' => 'Complaint Registration',
        'body' => "Log all collected details into an Excel sheet for further investigation and official complaint registration. Ensure information is captured accurately and systematically.",
        'is_enabled' => true
    ],
    [
        'title' => 'Closing',
        'body' => "Wrap up the call by confirming all details have been logged and reassure the caller: 'Thank you for providing the details. We have registered your complaint. We are here to assist you further if needed.'",
        'is_enabled' => true
    ]
]));


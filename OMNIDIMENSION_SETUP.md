# Omni Dimension Integration Setup Guide

This guide will help you set up the Omni Dimension integration for the CyberCrime Shield system.

## Prerequisites

1. **Python 3.8+** installed on your server
2. **Omni Dimension API Key** - Get it from https://omnidim.io/
3. **PHP with cURL extension** enabled
4. **MySQL database** access

## Installation Steps

### 1. Install Python Dependencies

```bash
cd python
pip install -r requirements.txt
```

Or using pip3:
```bash
pip3 install -r requirements.txt
```

### 2. Configure Environment Variables

Create a `.env` file in the `python` directory (or set system environment variables):

```bash
# Omni Dimension API Key (REQUIRED)
export OMNIDIMENSION_API_KEY="your_api_key_here"

# Agent Configuration
export AGENT_NAME="1930"
export AGENT_VOICE_ID="9NQoUbj2TvirWemhZkpT"
export AGENT_MODEL="gpt-4.1-mini"
export AGENT_TEMPERATURE="0.7"

# Service URLs
export WEBHOOK_URL="http://yourdomain.com/api/webhook.php"
export PHP_API_URL="http://yourdomain.com"
```

### 3. Update PHP Configuration

Edit `config/omnidimension.php` and set:
- `OMNIDIMENSION_API_KEY` - Your Omni Dimension API key
- `PYTHON_SERVICE_URL` - URL where Python service is running (default: `http://localhost:8000`)

### 4. Update Database Schema

Run the SQL commands from `database_updates.sql`:

```bash
mysql -u your_username -p your_database < database_updates.sql
```

Or manually execute the SQL in your database management tool.

### 5. Start Python Service

**Option A: Run directly (for testing)**
```bash
cd python
python omnidimension_service.py
```

**Option B: Run as a service (production)**

For Linux with systemd, create `/etc/systemd/system/omnidimension.service`:

```ini
[Unit]
Description=Omni Dimension Integration Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/project/python
Environment="OMNIDIMENSION_API_KEY=your_api_key_here"
Environment="WEBHOOK_URL=http://yourdomain.com/api/webhook.php"
Environment="PHP_API_URL=http://yourdomain.com"
ExecStart=/usr/bin/python3 /path/to/your/project/python/omnidimension_service.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl enable omnidimension
sudo systemctl start omnidimension
sudo systemctl status omnidimension
```

**Option C: Run with PM2 (Node.js process manager)**
```bash
npm install -g pm2
cd python
pm2 start omnidimension_service.py --name omnidimension --interpreter python3
pm2 save
pm2 startup
```

### 6. Verify Installation

1. Check Python service health:
   ```bash
   curl http://localhost:8000/health
   ```

2. Check agent info:
   ```bash
   curl http://localhost:8000/agent/info
   ```

3. Access the dashboard and check if credits are displayed (if available)

## Features

### Supported Session Types

1. **Chat** - Text-based conversation with the AI agent
2. **Web Call** - Voice call via web browser (requires microphone permission)
3. **Phone Call** - Outbound phone call to a phone number

### Key Features

- ✅ Real-time call/chat management
- ✅ Instant disconnect to avoid unnecessary charges
- ✅ Automatic session tracking in database
- ✅ Call duration tracking
- ✅ Credits monitoring
- ✅ Webhook integration for real-time updates
- ✅ Support for English and Hindi languages

## Usage

### Starting a Session

1. Go to "New Session" page
2. Select session type (Chat, Web Call, or Phone Call)
3. Enter caller name and language
4. For phone calls, enter phone number
5. Click "Start Session"

### Ending a Session

- Click "End Session" or "End Call" button
- The system will immediately disconnect to avoid charges
- Session data is automatically saved

## Troubleshooting

### Python Service Not Starting

1. Check if port 8000 is available:
   ```bash
   netstat -an | grep 8000
   ```

2. Check Python service logs for errors

3. Verify API key is set correctly

### Calls Not Connecting

1. Verify Python service is running: `curl http://localhost:8000/health`
2. Check webhook URL is accessible from Python service
3. Verify database connection in PHP
4. Check browser console for JavaScript errors

### Webhooks Not Working

1. Ensure `api/webhook.php` is accessible
2. Check PHP error logs
3. Verify database connection
4. Test webhook endpoint manually

## API Endpoints

### Python Service Endpoints

- `GET /health` - Health check
- `GET /agent/info` - Get agent info and credits
- `POST /call/create` - Create a new call
- `POST /call/end/<call_id>` - End a call
- `GET /call/status/<call_id>` - Get call status
- `POST /chat/create` - Create a new chat
- `POST /chat/send/<chat_id>` - Send chat message
- `POST /chat/end/<chat_id>` - End chat
- `POST /webhook` - Handle Omni Dimension webhooks

### PHP API Endpoints

- `GET /api/omnidimension.php?action=agent_info` - Get agent info
- `POST /api/omnidimension.php?action=create_call` - Create call
- `POST /api/omnidimension.php?action=end_call` - End call
- `GET /api/omnidimension.php?action=call_status&call_id=...` - Get call status
- `POST /api/omnidimension.php?action=create_chat` - Create chat
- `POST /api/omnidimension.php?action=send_chat` - Send chat message
- `POST /api/omnidimension.php?action=end_chat` - End chat
- `POST /api/webhook.php` - Handle events from Python service

## Security Notes

1. **Never commit API keys** to version control
2. Use environment variables for sensitive data
3. Secure your webhook endpoint (consider adding authentication)
4. Use HTTPS in production
5. Regularly update dependencies

## Support

For issues or questions:
- Check Omni Dimension documentation: https://omnidim.io/
- Review Python service logs
- Check PHP error logs
- Verify database connectivity


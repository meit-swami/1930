# Free Hosting Setup for Omni Dimension Integration

## Problem
Omni Dimension's servers need to access your local service, but `localhost` is only accessible on your computer. We need to expose it to the internet.

## Solution: Use ngrok (Free)

ngrok creates a secure tunnel from the internet to your localhost.

### Step 1: Install ngrok

**Windows:**
1. Download from: https://ngrok.com/download
2. Extract ngrok.exe to a folder (e.g., `C:\ngrok\`)
3. Or use Chocolatey: `choco install ngrok`

**Or use online version:**
- Visit: https://dashboard.ngrok.com/get-started/setup
- Sign up for free account
- Get your authtoken

### Step 2: Start ngrok tunnel

```powershell
ngrok http 8000
```

This will show:
```
Forwarding    https://abc123.ngrok.io -> http://localhost:8000
```

Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)

### Step 3: Update Webhook URL

Update `config/omnidimension.php`:
```php
define('WEBHOOK_URL', 'https://your-ngrok-url.ngrok.io/api/webhook.php');
```

And restart Python service with:
```powershell
$env:WEBHOOK_URL = "https://your-ngrok-url.ngrok.io/api/webhook.php"
```

### Step 4: Configure in Omni Dimension

1. Go to Omni Dimension dashboard
2. Create Custom API Integration:
   - **Integration Name**: "1930 CyberCrime Shield"
   - **API URL**: `https://your-ngrok-url.ngrok.io`
   - **HTTP Method**: POST (for webhooks)
   - **Headers**: Add if needed (Authorization, etc.)

## Alternative Free Options

### Option 2: Cloudflare Tunnel (Free)
```powershell
# Install cloudflared
# Then run:
cloudflared tunnel --url http://localhost:8000
```

### Option 3: localtunnel (Free, No Signup)
```powershell
npm install -g localtunnel
lt --port 8000
```

### Option 4: Serveo (Free, No Installation)
```powershell
ssh -R 80:localhost:8000 serveo.net
```

## Recommended: ngrok

ngrok is the most reliable and widely used. Free tier includes:
- HTTPS support
- Custom domains (paid)
- Request inspection
- Stable URLs (with account)

## Important Notes

1. **Free ngrok URLs change** each time you restart (unless you have a paid plan)
2. **Update webhook URL** in Omni Dimension dashboard when URL changes
3. **Keep ngrok running** while using the service
4. **Keep Python service running** on port 8000

## Quick Start Script

Create `start_with_tunnel.bat`:
```batch
@echo off
echo Starting ngrok tunnel...
start ngrok http 8000
timeout /t 3
echo Starting Python service...
cd python
python run_service.py
```


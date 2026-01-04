# Free Hosting Guide for Omni Dimension Integration

## The Problem

Omni Dimension's servers need to access your local service via webhooks and API calls. Since your service runs on `localhost:8000`, it's only accessible on your computer. We need to expose it to the internet.

## Solution: Use a Tunnel Service (Free)

A tunnel service creates a public URL that forwards requests to your localhost.

## Option 1: ngrok (Recommended - Most Reliable)

### Setup Steps

1. **Sign up for free account:**
   - Visit: https://dashboard.ngrok.com/signup
   - Create free account
   - Get your authtoken from dashboard

2. **Install ngrok:**
   ```powershell
   # Download from https://ngrok.com/download
   # Or use Chocolatey:
   choco install ngrok
   ```

3. **Authenticate:**
   ```powershell
   ngrok config add-authtoken YOUR_AUTHTOKEN
   ```

4. **Start tunnel:**
   ```powershell
   ngrok http 8000
   ```

5. **Copy the HTTPS URL:**
   ```
   Forwarding    https://abc123.ngrok.io -> http://localhost:8000
   ```

6. **Update configuration:**
   - Edit `config/omnidimension.php`:
     ```php
     define('WEBHOOK_URL', 'https://abc123.ngrok.io/api/webhook.php');
     ```
   - Restart Python service with:
     ```powershell
     $env:WEBHOOK_URL = "https://abc123.ngrok.io/api/webhook.php"
     ```

7. **Configure in Omni Dimension:**
   - Go to Custom API Integration
   - **API URL**: `https://abc123.ngrok.io`
   - **Method**: POST
   - Save

### Quick Start Script

Use `python/start_with_ngrok.bat` - it will start both ngrok and the Python service.

## Option 2: localtunnel (No Signup Required)

### Setup Steps

1. **Install Node.js** (if not installed):
   - Download from: https://nodejs.org/

2. **Install localtunnel:**
   ```powershell
   npm install -g localtunnel
   ```

3. **Start tunnel:**
   ```powershell
   lt --port 8000
   ```

4. **Copy the URL** (e.g., `https://abc123.loca.lt`)

5. **Update configuration** same as ngrok

### Quick Start Script

Use `python/start_with_localtunnel.bat`

## Option 3: Cloudflare Tunnel (Free, More Complex)

1. **Install cloudflared:**
   ```powershell
   # Download from: https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/
   ```

2. **Start tunnel:**
   ```powershell
   cloudflared tunnel --url http://localhost:8000
   ```

## Option 4: Serveo (SSH-based, No Installation)

```powershell
ssh -R 80:localhost:8000 serveo.net
```

## Comparison

| Service | Signup Required | HTTPS | Stability | Ease of Use |
|---------|----------------|-------|-----------|-------------|
| ngrok | Yes (free) | ✅ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| localtunnel | No | ✅ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| Cloudflare | Yes | ✅ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| Serveo | No | ✅ | ⭐⭐ | ⭐⭐⭐ |

## Recommended: ngrok

**Why ngrok?**
- Most reliable and stable
- Free tier is generous
- Easy to use
- Great documentation
- Request inspection dashboard

**Free Tier Includes:**
- HTTPS support
- 1 tunnel at a time
- Random URLs (change on restart)
- Request inspection
- 40 connections/minute

## Important Notes

1. **URLs Change**: Free tunnel URLs change each restart (except paid ngrok)
2. **Update Webhook**: Update Omni Dimension webhook URL when tunnel URL changes
3. **Keep Running**: Keep both tunnel and Python service running
4. **Security**: Tunnels are public - don't expose sensitive data without authentication

## Testing

After setting up tunnel:

1. **Test webhook endpoint:**
   ```
   https://your-tunnel-url.ngrok.io/api/webhook.php
   ```

2. **Test Python service:**
   ```
   https://your-tunnel-url.ngrok.io/health
   ```

3. **Configure in Omni Dimension:**
   - Use the tunnel URL as your Custom API endpoint
   - Test the integration

## Troubleshooting

**Tunnel not working?**
- Check firewall allows connections
- Verify port 8000 is not blocked
- Check tunnel service status

**Webhook not receiving requests?**
- Verify webhook URL in Omni Dimension
- Check tunnel is running
- Check Python service is running
- Review tunnel logs for errors

**URL keeps changing?**
- Free tier URLs change on restart
- Consider paid ngrok for static URL
- Or use a domain with DNS setup

## Next Steps

1. Choose a tunnel service (recommend ngrok)
2. Set up tunnel
3. Update webhook URL
4. Configure in Omni Dimension
5. Test the integration

---

**Quick Start:** Use `python/start_with_ngrok.bat` for easiest setup!


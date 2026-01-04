# ngrok Setup Complete! ✅

## Status

✅ **Authtoken Configured**: Your ngrok authtoken is saved
✅ **Ready to Use**: ngrok is ready to create tunnels

## Getting Your ngrok URL

### Step 1: Start ngrok

Open a terminal and run:
```powershell
ngrok http 8000
```

### Step 2: Get the HTTPS URL

You'll see output like:
```
Forwarding    https://abc123.ngrok-free.app -> http://localhost:8000
```

**Copy the HTTPS URL** (e.g., `https://abc123.ngrok-free.app`)

**Alternative**: Open http://localhost:4040 in browser to see the dashboard

### Step 3: Update Configuration

1. **Edit `config/omnidimension.php`:**
   ```php
   define('WEBHOOK_URL', 'https://your-ngrok-url.ngrok-free.app/api/webhook.php');
   ```
   Replace `your-ngrok-url.ngrok-free.app` with your actual URL

2. **Restart Python service:**
   ```powershell
   cd python
   $env:WEBHOOK_URL = "https://your-ngrok-url.ngrok-free.app/api/webhook.php"
   $env:OMNIDIMENSION_API_KEY = "-oh1U47bH-Xf806Y7UA1Mddfyi4xEmTyDI8A5uOdC58"
   python omnidimension_service.py
   ```

### Step 4: Configure in Omni Dimension

1. Go to Omni Dimension dashboard
2. Open **Custom API Integration**
3. Configure:
   - **Integration Name**: "1930 CyberCrime Shield"
   - **API URL**: `https://your-ngrok-url.ngrok-free.app`
   - **HTTP Method**: POST
   - **Description**: "Webhook endpoint for 1930 helpline"

## Static URL Info

### Free Plan (Current)
- ✅ URL stays **same while ngrok runs**
- ⚠️ URL **changes when you restart ngrok**
- ✅ Free forever
- ✅ Perfect for development/testing

### Paid Plan ($8/month) - For Static URL
- ✅ Static URL that never changes
- ✅ Custom domain: `your-name.ngrok-free.app`
- ✅ Reserved in dashboard
- ✅ Best for production

**To use static URL with paid plan:**
```powershell
ngrok http 8000 --domain=your-name.ngrok-free.app
```

## Important Notes

1. **Keep ngrok running** - The URL stays the same as long as ngrok keeps running
2. **Update URL when restarting** - If you restart ngrok, copy the new URL and update:
   - `config/omnidimension.php`
   - Omni Dimension dashboard
3. **Keep Python service running** - Must run on port 8000
4. **Test the connection** - Use ngrok dashboard at http://localhost:4040

## Quick Test

After setup, test your webhook:
```powershell
curl https://your-ngrok-url.ngrok-free.app/api/webhook.php
```

Or test Python service:
```powershell
curl https://your-ngrok-url.ngrok-free.app/health
```

## Troubleshooting

**ngrok not starting?**
- Check authtoken is configured: `ngrok config check`
- Verify port 8000 is available

**Can't access ngrok URL?**
- Make sure ngrok is running
- Check Python service is running on port 8000
- Verify firewall allows connections

**URL keeps changing?**
- Free plan URLs change on restart
- Keep ngrok running to maintain same URL
- Or upgrade to paid plan for static URL

---

**You're all set!** Start ngrok and configure Omni Dimension! 🚀


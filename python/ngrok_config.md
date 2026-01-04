# ngrok Configuration Guide

## Static URLs in ngrok

**Important**: Static URLs (custom domains) require ngrok **paid plan** ($8/month).

**Free Plan Limitations:**
- URLs change each time you restart ngrok
- Random URLs (e.g., `abc123.ngrok-free.app`)
- Still works, just need to update URL when it changes

## Options for Static URL

### Option 1: Paid ngrok (Recommended for Production)
- Cost: $8/month
- Get static domain: `your-name.ngrok-free.app`
- Configure in ngrok dashboard
- Never changes

### Option 2: Use Domain Name (Free Alternative)
- Get free subdomain from: No-IP, DuckDNS, etc.
- Update DNS when ngrok URL changes
- More complex setup

### Option 3: Accept Dynamic URLs (Free)
- Use free ngrok URLs
- Update Omni Dimension webhook URL when it changes
- Fine for development/testing

## Current Setup

Your authtoken is configured. To use static URL (requires paid plan):

1. **Upgrade to paid plan** (if desired)
2. **Reserve domain in ngrok dashboard**
3. **Start ngrok with domain:**
   ```powershell
   ngrok http 8000 --domain=your-name.ngrok-free.app
   ```

## For Free Plan (Current Setup)

Start ngrok normally:
```powershell
ngrok http 8000
```

Copy the HTTPS URL and update:
- `config/omnidimension.php` - WEBHOOK_URL
- Omni Dimension dashboard - Custom API URL

When URL changes (after restart), just update these two places.


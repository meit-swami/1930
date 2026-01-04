# 🧪 Testing Guide

## Quick Test Methods

### Method 1: Web-Based Test Page (Recommended)

1. **Start your PHP web server** (XAMPP, WAMP, etc.)
2. **Open browser**: `http://localhost/test_integration.php`
3. **Click "Run All Tests"** button
4. **Review results** - All tests should show ✅

### Method 2: Command-Line Test

```cmd
python test_service.py
```

Expected output:
```
✅ Health check passed!
✅ Agent info retrieved!
✅ All tests passed!
```

### Method 3: Manual API Testing

**Test Health Endpoint:**
```cmd
curl http://localhost:8000/health
```

Expected response:
```json
{
  "status": "ok",
  "agent_id": "...",
  "active_calls": 0,
  "active_chats": 0
}
```

**Test Agent Info:**
```cmd
curl http://localhost:8000/agent/info
```

**Test PHP API Proxy:**
```cmd
curl http://localhost/api/omnidimension.php?action=agent_info
```

## Testing Features

### 1. Test Chat Session

1. Go to: `http://localhost/session.php`
2. Select **"Chat"** session type
3. Enter caller name and language
4. Click **"Start Session"**
5. Send a test message
6. Verify AI response appears

### 2. Test Web Call

1. Go to: `http://localhost/session.php`
2. Select **"Web Call"** session type
3. Enter caller name and language
4. Click **"Start Session"**
5. Allow microphone access when prompted
6. Verify call interface appears
7. Test **"End Call"** button

### 3. Test Phone Call

1. Go to: `http://localhost/session.php`
2. Select **"Phone Call"** session type
3. Enter phone number, caller name, and language
4. Click **"Start Session"**
5. Verify call is initiated
6. Test **"End Call"** button immediately

### 4. Test Dashboard

1. Go to: `http://localhost/dashboard.php`
2. Verify statistics are displayed
3. Check if credits are shown (if available)
4. Verify recent sessions table

## Expected Behavior

### ✅ Success Indicators

- Python service responds to `/health` endpoint
- Agent info is retrieved successfully
- PHP API can communicate with Python service
- Sessions can be created (chat/web/phone)
- Calls disconnect immediately when ended
- Dashboard shows statistics

### ⚠️ Common Issues

**Service not starting:**
- Check API key is correct
- Verify Python dependencies: `pip install -r requirements.txt`
- Check port 8000 is available
- Review Python service logs

**Cannot connect to service:**
- Verify service is running: `netstat -an | findstr :8000`
- Check firewall settings
- Verify `PYTHON_SERVICE_URL` in config

**API errors:**
- Check API key is valid
- Verify internet connection
- Review webhook URL is correct
- Check database connection

## Test Checklist

- [ ] Python service starts without errors
- [ ] Health endpoint responds
- [ ] Agent info endpoint works
- [ ] PHP API proxy works
- [ ] Chat session can be created
- [ ] Web call can be initiated
- [ ] Phone call can be initiated
- [ ] Calls disconnect immediately
- [ ] Dashboard displays correctly
- [ ] Web widget loads (if applicable)

## Debugging

### Check Service Logs

The Python service will show logs in the terminal:
- `Omni Dimension service started` - Service is ready
- `Found existing agent` or `Created new agent` - Agent setup
- Any error messages will appear here

### Check PHP Logs

PHP errors will be in your server's error log:
- XAMPP: `xampp/apache/logs/error.log`
- WAMP: `wamp/logs/apache_error.log`
- Or check PHP error log location

### Check Browser Console

Open browser DevTools (F12) and check:
- Network tab for API calls
- Console tab for JavaScript errors
- Verify API responses

## Next Steps After Testing

Once all tests pass:
1. ✅ Integration is working
2. ✅ Ready for production use
3. ✅ Can start handling real calls/chats
4. ✅ Monitor credits and usage
5. ✅ Review call logs and statistics

---

**Happy Testing!** 🚀


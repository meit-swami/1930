@echo off
REM Helper script to get ngrok URL and update configuration

echo ========================================
echo ngrok URL Configuration Helper
echo ========================================
echo.

REM Check if ngrok is running
curl -s http://localhost:4040/api/tunnels >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: ngrok is not running!
    echo.
    echo Please start ngrok first:
    echo   ngrok http 8000
    echo.
    pause
    exit /b 1
)

echo Fetching ngrok URL...
echo.

REM Get ngrok URL from API
for /f "tokens=*" %%i in ('curl -s http://localhost:4040/api/tunnels ^| findstr "public_url"') do (
    set LINE=%%i
)

REM Extract HTTPS URL (this is a simplified version)
echo Please copy the HTTPS URL from ngrok web interface:
echo   http://localhost:4040
echo.
echo Or from the ngrok terminal window
echo.
echo Example URL: https://abc123.ngrok-free.app
echo.

set /p NGROK_URL="Enter your ngrok HTTPS URL (without /api/webhook.php): "

if "%NGROK_URL%"=="" (
    echo No URL entered. Exiting.
    pause
    exit /b 1
)

echo.
echo ========================================
echo Updating Configuration
echo ========================================
echo.
echo Your ngrok URL: %NGROK_URL%
echo Webhook URL: %NGROK_URL%/api/webhook.php
echo.

REM Update config file (requires manual edit - showing instructions)
echo Please manually update config/omnidimension.php:
echo.
echo Change:
echo   define('WEBHOOK_URL', 'http://localhost/api/webhook.php');
echo.
echo To:
echo   define('WEBHOOK_URL', '%NGROK_URL%/api/webhook.php');
echo.

echo ========================================
echo Next Steps
echo ========================================
echo.
echo 1. Update config/omnidimension.php with the URL above
echo 2. Restart Python service with:
echo    $env:WEBHOOK_URL = "%NGROK_URL%/api/webhook.php"
echo    python run_service.py
echo.
echo 3. Configure in Omni Dimension Custom API Integration:
echo    API URL: %NGROK_URL%
echo.

pause


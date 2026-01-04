@echo off
REM Run Omni Dimension Service
REM This is a Windows batch file - run it directly, not with Python!

echo ========================================
echo Starting Omni Dimension Service
echo ========================================
echo.

REM Set environment variables
set OMNIDIMENSION_API_KEY=-oh1U47bH-Xf806Y7UA1Mddfyi4xEmTyDI8A5uOdC58
set WEBHOOK_URL=http://localhost/api/webhook.php
set PHP_API_URL=http://localhost

echo API Key: %OMNIDIMENSION_API_KEY:~0,20%...
echo Webhook URL: %WEBHOOK_URL%
echo.

echo Starting service on http://localhost:8000
echo Press Ctrl+C to stop
echo.

python omnidimension_service.py

if errorlevel 1 (
    echo.
    echo ERROR: Service failed to start!
    echo.
    echo Please check:
    echo 1. Python is installed: python --version
    echo 2. Dependencies are installed: pip install -r requirements.txt
    echo 3. API key is valid
    echo.
    pause
)


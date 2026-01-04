@echo off
REM Start Omni Dimension Service with ngrok tunnel

echo ========================================
echo Starting Service with ngrok Tunnel
echo ========================================
echo.

REM Check if ngrok is installed
where ngrok >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: ngrok is not installed or not in PATH
    echo.
    echo Please install ngrok:
    echo 1. Download from: https://ngrok.com/download
    echo 2. Extract ngrok.exe
    echo 3. Add to PATH or place in this directory
    echo.
    pause
    exit /b 1
)

echo Starting ngrok tunnel on port 8000...
start "ngrok" ngrok http 8000

echo Waiting for ngrok to start...
timeout /t 5

echo.
echo ========================================
echo IMPORTANT: Copy the ngrok HTTPS URL above
echo ========================================
echo.
echo Example: https://abc123.ngrok.io
echo.
echo Update these with your ngrok URL:
echo 1. config/omnidimension.php - WEBHOOK_URL
echo 2. Omni Dimension dashboard - Custom API URL
echo.
pause

echo.
echo Starting Python service...
cd python

REM Set environment variables
set OMNIDIMENSION_API_KEY=-oh1U47bH-Xf806Y7UA1Mddfyi4xEmTyDI8A5uOdC58
set PHP_API_URL=http://localhost

REM Note: Update WEBHOOK_URL with your ngrok URL
REM set WEBHOOK_URL=https://your-ngrok-url.ngrok.io/api/webhook.php
set WEBHOOK_URL=http://localhost/api/webhook.php

python run_service.py

pause


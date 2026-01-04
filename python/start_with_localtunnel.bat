@echo off
REM Start Omni Dimension Service with localtunnel (No signup required)

echo ========================================
echo Starting Service with localtunnel
echo ========================================
echo.

REM Check if Node.js is installed
where node >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Node.js is not installed
    echo.
    echo Please install Node.js from: https://nodejs.org/
    echo Then install localtunnel: npm install -g localtunnel
    echo.
    pause
    exit /b 1
)

REM Check if localtunnel is installed
where lt >nul 2>&1
if %errorlevel% neq 0 (
    echo Installing localtunnel...
    npm install -g localtunnel
)

echo Starting localtunnel on port 8000...
start "localtunnel" cmd /k "lt --port 8000"

echo Waiting for tunnel to start...
timeout /t 5

echo.
echo ========================================
echo IMPORTANT: Copy the localtunnel URL above
echo ========================================
echo.
echo Example: https://abc123.loca.lt
echo.
echo Update these with your tunnel URL:
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

REM Note: Update WEBHOOK_URL with your tunnel URL
REM set WEBHOOK_URL=https://your-tunnel-url.loca.lt/api/webhook.php
set WEBHOOK_URL=http://localhost/api/webhook.php

python run_service.py

pause


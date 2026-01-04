@echo off
REM Start ngrok with static domain (requires paid plan)
REM For free plan, use start_with_ngrok.bat instead

echo ========================================
echo Starting ngrok with Static Domain
echo ========================================
echo.
echo NOTE: This requires ngrok paid plan ($8/month)
echo For free plan, use: start_with_ngrok.bat
echo.

REM Replace with your reserved domain from ngrok dashboard
set NGROK_DOMAIN=your-name.ngrok-free.app

echo Starting ngrok with domain: %NGROK_DOMAIN%
echo.
echo If you see an error, check:
echo 1. You have ngrok paid plan
echo 2. Domain is reserved in ngrok dashboard
echo 3. Domain name is correct
echo.

ngrok http 8000 --domain=%NGROK_DOMAIN%

pause


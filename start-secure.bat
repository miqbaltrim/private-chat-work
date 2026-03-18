@echo off
echo.
echo   Private Chat - HTTPS Mode
echo   ==========================
echo.

echo [1/4] Starting Laravel backend (HTTP internal)...
start /B cmd /C "php artisan serve --host=127.0.0.1 --port=8000"
timeout /t 2 >nul

echo [2/4] Starting HTTPS proxy (port 8443)...
start /B cmd /C "php artisan serve:secure"

echo [3/4] Starting WSS Chat (port 8090)...
start /B cmd /C "php artisan websocket:serve"

echo [4/4] Starting WSS Signaling (port 8091)...
start /B cmd /C "php artisan signaling:serve"

timeout /t 2 >nul

echo.
echo ============================================
echo   All servers running in HTTPS mode!
echo ============================================
echo.
echo   Access: https://localhost:8443
echo   LAN:    https://[YOUR_IP]:8443
echo.
echo   Firewall ports: 8443, 8090, 8091
echo.
echo   First time? Teman harus klik:
echo   Advanced ^> Proceed to [IP] (unsafe)
echo.
echo   Press Ctrl+C to stop
echo.
pause
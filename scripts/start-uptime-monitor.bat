@echo off
echo ========================================
echo    Starting Uptime Monitor System
echo ========================================
echo.

REM Set your project path
set PROJECT_PATH=C:\Users\naage\uptime-monitor

REM Go to project directory
cd /d "%PROJECT_PATH%"

echo 1. Checking for required services...
echo.

echo    a) Checking MySQL (port 3306)...
netstat -an | find ":3306" >nul
if %errorlevel%==0 (
    echo    MySQL is running ✓
) else (
    echo    WARNING: MySQL is not running!
    echo    Please start MySQL from XAMPP Control Panel.
)


echo.
echo    b) Checking Redis...
tasklist | find /i "redis-server" >nul
if %errorlevel%==0 (
    echo    Redis is running ✓
) else (
    echo    WARNING: Redis is not running!
    echo    Attempting to start Redis...
    start "" "C:\Program Files\Redis\redis-server.exe"
    timeout /t 3 >nul
)

echo.
echo 2. Starting Laravel development server...
echo    Opening at: http://127.0.0.1:8000
start "Laravel Server" cmd /k "cd /d "%PROJECT_PATH%" && php artisan serve"
timeout /t 3 >nul

echo.
echo 3. Starting Redis Queue Worker...
start "Queue Worker" cmd /k "cd /d "%PROJECT_PATH%" && php artisan queue:work redis --sleep=3 --tries=3 --timeout=60"
timeout /t 3 >nul

echo.
echo 4. Starting Laravel Scheduler...
start "Scheduler" cmd /k "cd /d "%PROJECT_PATH%" && php artisan schedule:work"
timeout /t 3 >nul

echo.
echo 5. Building frontend assets...
call npm run build
timeout /t 5 >nul

echo.
echo ========================================
echo    All services started successfully!
echo ========================================
echo.
echo IMPORTANT: Keep this window open.
echo To stop all services, press any key in this window.
echo.
echo Access your application at: http://127.0.0.1:8000
echo.
pause >nul

echo.
echo Stopping all services...
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM node.exe >nul 2>&1
taskkill /F /IM cmd.exe /FI "WINDOWTITLE eq Laravel Server*" >nul 2>&1
taskkill /F /IM cmd.exe /FI "WINDOWTITLE eq Queue Worker*" >nul 2>&1
taskkill /F /IM cmd.exe /FI "WINDOWTITLE eq Scheduler*" >nul 2>&1

echo All services stopped.
timeout /t 2 >nul
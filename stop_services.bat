@echo off
echo ========================================
echo      DUNG TAT CA SERVICES CHATBOX
echo ========================================
echo.

echo Dang dung Python service (port 8005-8020)...
for /l %%p in (8005,1,8020) do (
    for /f "tokens=5" %%a in ('netstat -aon ^| findstr :%%p ^| findstr LISTENING') do (
        echo Dung process port %%p, PID: %%a
        taskkill /PID %%a /F >nul 2>&1
    )
)

echo Dang dung Laravel serve (port 8000)...
for /f "tokens=5" %%a in ('netstat -aon ^| findstr :8000 ^| findstr LISTENING') do (
    echo Dung process ID: %%a
    taskkill /PID %%a /F >nul 2>&1
)

echo.
echo ✓ Da dung tat ca services!
echo.
pause

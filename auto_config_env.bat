@echo off
echo ========================================
echo    TU DONG CAP NHAT .ENV FILE
echo ========================================
echo.

REM Kiem tra file python_port.tmp
if not exist "python_port.tmp" (
    echo ERROR: Khong tim thay file python_port.tmp
    echo Vui long chay start_chatbox.bat truoc
    pause
    exit /b 1
)

REM Doc port tu file temp
set /p PYTHON_PORT=<python_port.tmp
echo Tim thay Python port: %PYTHON_PORT%

REM Kiem tra file .env
if not exist ".env" (
    echo ERROR: Khong tim thay file .env
    echo Vui long tao file .env tu .env.example
    pause
    exit /b 1
)

REM Backup .env
copy .env .env.backup >nul
echo ✓ Da backup .env thanh .env.backup

REM Cap nhat PY_CHAT_URL trong .env
powershell -Command "(Get-Content .env) -replace '^PY_CHAT_URL=.*', 'PY_CHAT_URL=http://127.0.0.1:%PYTHON_PORT%' | Set-Content .env.tmp"

REM Neu chua co PY_CHAT_URL, them vao cuoi file
findstr "PY_CHAT_URL" .env.tmp >nul
if errorlevel 1 (
    echo PY_CHAT_URL=http://127.0.0.1:%PYTHON_PORT% >> .env.tmp
)

REM Thay the file .env
move .env.tmp .env >nul

echo ✓ Da cap nhat .env: PY_CHAT_URL=http://127.0.0.1:%PYTHON_PORT%
echo.
echo Clear Laravel cache...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
echo ✓ Da clear cache Laravel

echo.
echo ========================================
echo  CAP NHAT HOAN TAT!
echo  Python Service: http://127.0.0.1:%PYTHON_PORT%
echo  Website: http://localhost:8000
echo ========================================
pause


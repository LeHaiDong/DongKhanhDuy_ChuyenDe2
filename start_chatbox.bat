@echo off
echo ========================================
echo    KHOI CHAY CHATBOX E-COMMERCE
echo ========================================
echo.

REM Kiem tra XAMPP da chay chua
echo [1/4] Kiem tra MySQL...
netstat -an | find "3306" >nul
if errorlevel 1 (
    echo ERROR: MySQL chua chay! Vui long khoi dong XAMPP truoc.
    pause
    exit /b 1
)
echo ✓ MySQL da chay

REM Kiem tra thu muc hien tai
echo [2/4] Kiem tra thu muc du an...
if not exist "artisan" (
    echo ERROR: Khong tim thay file artisan. Vui long chay script trong thu muc du an Laravel.
    pause
    exit /b 1
)
echo ✓ Thu muc du an hop le

REM Tim port trong cho Python service
echo [3/4] Tim port trong cho Python Chatbot Service...
set PYTHON_PORT=8005
:find_port
netstat -an | find ":%PYTHON_PORT%" >nul
if not errorlevel 1 (
    set /a PYTHON_PORT+=1
    if %PYTHON_PORT% GTR 8020 (
        echo ERROR: Khong tim thay port trong tu 8005-8020
        pause
        exit /b 1
    )
    goto find_port
)
echo ✓ Tim thay port trong: %PYTHON_PORT%

REM Khoi chay Python service trong terminal moi
start "Python Chatbot Service - Port %PYTHON_PORT%" cmd /k "echo PYTHON CHATBOT SERVICE && echo Port: %PYTHON_PORT% && echo Health: http://127.0.0.1:%PYTHON_PORT%/health && echo. && python\chat_service\.venv\Scripts\python.exe -m uvicorn python.chat_service.main:app --host 127.0.0.1 --port %PYTHON_PORT%"

REM Cho 3 giay de Python service khoi dong
echo Cho Python service khoi dong...
timeout /t 3 /nobreak >nul

REM Ghi port vao file temp de Laravel doc
echo %PYTHON_PORT% > python_port.tmp

REM Khoi chay Laravel serve
echo [4/4] Khoi chay Laravel Web Server...
echo.
echo ========================================
echo  WEBSITE: http://localhost:8000
echo  CHATBOX: Goc phai man hinh
echo  ADMIN: http://localhost:8000/admin
echo  PYTHON SERVICE: http://127.0.0.1:%PYTHON_PORT%
echo ========================================
echo.
echo QUAN TRONG: Them vao file .env:
echo PY_CHAT_URL=http://127.0.0.1:%PYTHON_PORT%
echo.
echo Nhan Ctrl+C de dung server...
php artisan serve

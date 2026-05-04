@echo off
title Python Chatbot Service
echo ========================================
echo    PYTHON CHATBOT SERVICE KHOI DONG
echo ========================================

REM Tim port trong
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

echo Port: %PYTHON_PORT%
echo Health: http://127.0.0.1:%PYTHON_PORT%/health
echo.

REM Kiem tra virtual environment
if not exist "python\chat_service\.venv\Scripts\python.exe" (
    echo ERROR: Khong tim thay Python virtual environment!
    echo Vui long chay lenh: python -m venv python\chat_service\.venv
    pause
    exit /b 1
)

REM Ghi port vao file temp
echo %PYTHON_PORT% > python_port.tmp

REM Khoi chay service
echo Dang khoi dong Python service...
echo Ghi nho cap nhat .env: PY_CHAT_URL=http://127.0.0.1:%PYTHON_PORT%
echo.
python\chat_service\.venv\Scripts\python.exe -m uvicorn python.chat_service.main:app --host 127.0.0.1 --port %PYTHON_PORT%

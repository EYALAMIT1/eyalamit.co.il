@echo off
chcp 65001 >nul
echo ========================================
echo בדיקת שגיאות נוכחיות
echo ========================================
echo.

cd /d "%~dp0"

where pwsh >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [INFO] משתמש ב-PowerShell 7...
    pwsh.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\check-errors.ps1"
) else (
    echo [INFO] PowerShell 7 לא נמצא, משתמש ב-PowerShell רגיל...
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\check-errors.ps1"
)

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] שגיאה בביצוע הסקריפט!
    echo.
    pause
    exit /b 1
)

echo.
echo [SUCCESS] הבדיקה הושלמה!
echo [INFO] הפלט נשמר בקובץ: check-errors-output.txt
echo.
pause


@echo off
chcp 65001 >nul
echo ========================================
echo כיבוי תוספים בעייתיים (PHP 8.2)
echo ========================================
echo.
echo [INFO] מכבה תוספים המשתמשים בפונקציות ישנות...
echo.

cd /d "%~dp0"

where pwsh >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [INFO] משתמש ב-PowerShell 7...
    pwsh.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\disable-problematic-plugins.ps1"
) else (
    echo [INFO] PowerShell 7 לא נמצא, משתמש ב-PowerShell רגיל...
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\disable-problematic-plugins.ps1"
)

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] שגיאה בכיבוי התוספים!
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo [SUCCESS] התוספים כובו בהצלחה!
echo ========================================
echo.
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo.
pause


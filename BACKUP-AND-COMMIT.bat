@echo off
chcp 65001 >nul
echo ========================================
echo גיבוי מלא ועדכון GitHub
echo ========================================
echo.

cd /d "%~dp0"

where pwsh >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [INFO] משתמש ב-PowerShell 7...
    pwsh.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\backup-and-commit.ps1"
) else (
    echo [INFO] PowerShell 7 לא נמצא, משתמש ב-PowerShell רגיל...
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\backup-and-commit.ps1"
)

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] שגיאה בתהליך!
    echo.
    pause
    exit /b 1
)

echo.
echo [SUCCESS] הגיבוי והעדכון הושלמו!
echo.
pause



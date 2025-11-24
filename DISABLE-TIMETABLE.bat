@echo off
chcp 65001 >nul
echo ========================================
echo כיבוי תוסף Timetable
echo ========================================
echo.

cd /d "%~dp0"

where pwsh >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    pwsh.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\disable-timetable.ps1"
) else (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\disable-timetable.ps1"
)

if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Failed to disable timetable
    pause
    exit /b 1
)

echo.
echo [SUCCESS] Timetable disabled!
echo.
echo [INFO] Check the site at: http://localhost:8080
echo.
pause



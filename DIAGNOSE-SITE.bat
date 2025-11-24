@echo off
chcp 65001 >nul
echo ========================================
echo אבחון שגיאות האתר
echo ========================================
echo.

cd /d "%~dp0"

where pwsh >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    pwsh.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\diagnose-site.ps1"
) else (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\diagnose-site.ps1"
)

echo.
pause



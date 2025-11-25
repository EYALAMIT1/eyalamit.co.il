@echo off
REM ========================================
REM Pre-Production Checklist
REM ========================================
REM Runs all necessary checks and backups before production deployment

setlocal

REM Get the directory where this batch file is located
set "SCRIPT_DIR=%~dp0"
set "POWERSHELL_SCRIPT=%SCRIPT_DIR%pre-production-checklist.ps1"

REM Check if PowerShell script exists
if not exist "%POWERSHELL_SCRIPT%" (
    echo [ERROR] PowerShell script not found: %POWERSHELL_SCRIPT%
    echo.
    pause
    exit /b 1
)

REM Run PowerShell script
powershell.exe -ExecutionPolicy Bypass -File "%POWERSHELL_SCRIPT%" %*

endlocal


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
set "PS_EXIT_CODE=%ERRORLEVEL%"

REM Keep window open
echo.
echo ========================================
if %PS_EXIT_CODE% NEQ 0 (
    echo [WARNING] Script completed with exit code %PS_EXIT_CODE%
) else (
    echo [OK] Script completed successfully
)
echo ========================================
echo.
echo Press any key to close this window...
pause >nul

endlocal


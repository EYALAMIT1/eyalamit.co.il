@echo off
REM ========================================
REM Create ZIP with long path support
REM ========================================
REM Simple wrapper - just runs the PowerShell script
REM The PowerShell script handles everything including pause

setlocal

REM Get the directory where this batch file is located
set "SCRIPT_DIR=%~dp0"
set "POWERSHELL_SCRIPT=%SCRIPT_DIR%create-long-path-zip.ps1"

REM Check if PowerShell script exists
if not exist "%POWERSHELL_SCRIPT%" (
    echo [ERROR] PowerShell script not found: %POWERSHELL_SCRIPT%
    echo.
    pause
    exit /b 1
)

REM Run PowerShell script - it will handle pause itself
powershell.exe -ExecutionPolicy Bypass -File "%POWERSHELL_SCRIPT%" %*

endlocal

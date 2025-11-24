@echo off
chcp 65001 >nul
echo ========================================
echo שדרוג WordPress Core
echo ========================================
echo.
echo [WARNING] זה ישדרג את ליבת WordPress!
echo [INFO] ודא שיש לך גיבוי עדכני לפני המשך.
echo.
pause

echo.
echo [INFO] מריץ סקריפט שדרוג WordPress...
echo.

where pwsh >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    pwsh.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\upgrade-wordpress.ps1" -Version 6.8.3
) else (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\upgrade-wordpress.ps1" -Version 6.8.3
)

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] שגיאה בשדרוג WordPress!
    pause
    exit /b 1
)

echo.
echo [INFO] מעדכן את בסיס הנתונים...
docker compose --env-file env.local run --rm wpcli wp core update-db

if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] עדכון DB נכשל - ייתכן שצריך להריץ ידנית
)

echo.
echo [INFO] בודק checksums...
docker compose --env-file env.local run --rm wpcli wp core verify-checksums

echo.
echo ========================================
echo [SUCCESS] שדרוג WordPress הושלם!
echo ========================================
echo.
pause


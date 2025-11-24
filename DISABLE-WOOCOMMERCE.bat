@echo off
chcp 65001 >nul
echo ========================================
echo כיבוי WooCommerce (PHP 8.2)
echo ========================================
echo.
echo [WARNING] זה יכבה את חנות האונליין שלך!
echo [INFO] WooCommerce גרסה ישנה לא תואמת PHP 8.2
echo.
pause

cd /d "%~dp0"

where pwsh >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    pwsh.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\disable-woocommerce.ps1"
) else (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File ".\scripts\disable-woocommerce.ps1"
)

if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Failed to disable WooCommerce
    pause
    exit /b 1
)

echo.
echo [SUCCESS] WooCommerce disabled!
echo [INFO] Check the site at: http://localhost:8080
echo.
pause



@echo off
chcp 65001 >nul
echo ========================================
echo בדיקת האתר אחרי תיקון WooCommerce
echo ========================================
echo.

cd /d "%~dp0"

echo [INFO] מנסה לטעון WordPress...
docker compose --env-file env.local run --rm wpcli wp core version 2>&1 | findstr /v "Container Running Waiting Healthy"
echo.

echo [INFO] בודק אם WooCommerce נטען...
docker compose --env-file env.local run --rm wpcli wp plugin list --field=name,version,status | findstr /i "woocommerce"
echo.

echo [INFO] בודק שגיאות...
docker compose --env-file env.local logs wordpress --tail 10 2>&1 | findstr /i "error fatal"
echo.

echo ========================================
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo ========================================
echo.
pause



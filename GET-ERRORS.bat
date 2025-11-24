@echo off
chcp 65001 >nul
echo ========================================
echo בדיקת שגיאות האתר
echo ========================================
echo.

echo [INFO] בודק לוגים של WordPress...
docker compose --env-file env.local logs wordpress --tail 50
echo.

echo [INFO] בודק סטטוס תוספים...
docker compose --env-file env.local exec wordpress ls -la /var/www/html/wp-content/plugins/ | findstr /i "Layer revslider"
echo.

echo [INFO] מנסה לטעון את WordPress...
docker compose --env-file env.local run --rm wpcli wp core version 2>&1 | head -20
echo.

pause



@echo off
chcp 65001 >nul
echo ========================================
echo כיבוי תוספים בעייתיים (PHP 8.2)
echo ========================================
echo.
echo [INFO] מכבה תוספים המשתמשים בפונקציות ישנות...
echo.

cd /d "%~dp0"

echo [INFO] מכבה LayerSlider...
docker compose --env-file env.local exec wordpress sh -c "if [ -d /var/www/html/wp-content/plugins/LayerSlider ]; then mv /var/www/html/wp-content/plugins/LayerSlider /var/www/html/wp-content/plugins/LayerSlider.disabled && echo 'LayerSlider disabled'; else echo 'LayerSlider not found or already disabled'; fi"

echo.
echo [INFO] מכבה RevSlider...
docker compose --env-file env.local exec wordpress sh -c "if [ -d /var/www/html/wp-content/plugins/revslider ]; then mv /var/www/html/wp-content/plugins/revslider /var/www/html/wp-content/plugins/revslider.disabled && echo 'revslider disabled'; else echo 'revslider not found or already disabled'; fi"

echo.
echo ========================================
echo [SUCCESS] התוספים כובו בהצלחה!
echo ========================================
echo.
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo.
pause


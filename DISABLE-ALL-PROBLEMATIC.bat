@echo off
chcp 65001 >nul
echo ========================================
echo כיבוי כל התוספים הבעייתיים
echo ========================================
echo.

cd /d "%~dp0"

echo [INFO] מכבה LayerSlider...
docker compose --env-file env.local exec wordpress sh -c "if [ -d /var/www/html/wp-content/plugins/LayerSlider ]; then mv /var/www/html/wp-content/plugins/LayerSlider /var/www/html/wp-content/plugins/LayerSlider.disabled && echo 'LayerSlider disabled'; else echo 'LayerSlider already disabled'; fi"

echo.
echo [INFO] מכבה RevSlider...
docker compose --env-file env.local exec wordpress sh -c "if [ -d /var/www/html/wp-content/plugins/revslider ]; then mv /var/www/html/wp-content/plugins/revslider /var/www/html/wp-content/plugins/revslider.disabled && echo 'revslider disabled'; else echo 'revslider already disabled'; fi"

echo.
echo [INFO] מכבה Timetable...
docker compose --env-file env.local exec wordpress sh -c "if [ -d /var/www/html/wp-content/plugins/timetable ]; then mv /var/www/html/wp-content/plugins/timetable /var/www/html/wp-content/plugins/timetable.disabled && echo 'timetable disabled'; else echo 'timetable already disabled'; fi"

echo.
echo ========================================
echo [SUCCESS] סיום
echo ========================================
echo.
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo.
pause



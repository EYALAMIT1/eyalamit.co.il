@echo off
chcp 65001 >nul
echo ========================================
echo כיבוי כל התוספים הבעייתיים
echo ========================================
echo.

cd /d "%~dp0"

echo [1/4] מכבה LayerSlider...
docker compose --env-file env.local exec wordpress sh -c "test -d /var/www/html/wp-content/plugins/LayerSlider && mv /var/www/html/wp-content/plugins/LayerSlider /var/www/html/wp-content/plugins/LayerSlider.disabled && echo 'OK: LayerSlider disabled' || echo 'SKIP: LayerSlider already disabled'"
if errorlevel 1 echo [ERROR] Failed to disable LayerSlider

echo.
echo [2/4] מכבה RevSlider...
docker compose --env-file env.local exec wordpress sh -c "test -d /var/www/html/wp-content/plugins/revslider && mv /var/www/html/wp-content/plugins/revslider /var/www/html/wp-content/plugins/revslider.disabled && echo 'OK: revslider disabled' || echo 'SKIP: revslider already disabled'"
if errorlevel 1 echo [ERROR] Failed to disable RevSlider

echo.
echo [3/4] מכבה Timetable...
docker compose --env-file env.local exec wordpress sh -c "test -d /var/www/html/wp-content/plugins/timetable && mv /var/www/html/wp-content/plugins/timetable /var/www/html/wp-content/plugins/timetable.disabled && echo 'OK: timetable disabled' || echo 'SKIP: timetable already disabled'"
if errorlevel 1 echo [ERROR] Failed to disable Timetable

echo.
echo [4/4] בודק סטטוס...
docker compose --env-file env.local exec wordpress sh -c "ls -la /var/www/html/wp-content/plugins/ | grep -E '(LayerSlider|revslider|timetable)'"

echo.
echo ========================================
echo [SUCCESS] סיום
echo ========================================
echo.
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo.
echo [INFO] לחץ על כל מקש כדי לסגור...
pause >nul



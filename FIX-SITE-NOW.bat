@echo off
chcp 65001 >nul
echo ========================================
echo תיקון האתר - בדיקה מלאה
echo ========================================
echo.

cd /d "%~dp0"

echo [1/5] בודק סטטוס קונטיינרים...
docker compose --env-file env.local ps
echo.

echo [2/5] בודק אם התוספים הבעייתיים כובו...
docker compose --env-file env.local exec wordpress sh -c "ls -la /var/www/html/wp-content/plugins/ | grep -E '(LayerSlider|revslider)'"
echo.

echo [3/5] מנסה לטעון WordPress...
docker compose --env-file env.local run --rm wpcli wp core version 2>&1
echo.

echo [4/5] בודק תוספים פעילים...
docker compose --env-file env.local run --rm wpcli wp plugin list --status=active 2>&1 | head -30
echo.

echo [5/5] בודק שגיאות PHP...
docker compose --env-file env.local logs wordpress --tail 20 2>&1 | findstr /i "error fatal warning"
echo.

echo ========================================
echo סיום בדיקה
echo ========================================
echo.
pause



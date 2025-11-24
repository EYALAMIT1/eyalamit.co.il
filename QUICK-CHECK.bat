@echo off
chcp 65001 >nul
echo ========================================
echo בדיקה מהירה של האתר
echo ========================================
echo.

cd /d "%~dp0"

echo [1] בודק אם WordPress נטען...
docker compose --env-file env.local run --rm wpcli wp core version 2>&1
echo.

echo [2] בודק תוספים פעילים...
docker compose --env-file env.local run --rm wpcli wp plugin list --status=active 2>&1 | head -20
echo.

echo [3] בודק לוגים אחרונים...
docker compose --env-file env.local logs wordpress --tail 30 2>&1 | findstr /i "error fatal warning"
echo.

echo ========================================
echo סיום
echo ========================================
echo.
pause



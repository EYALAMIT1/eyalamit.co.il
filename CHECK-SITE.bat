@echo off
chcp 65001 >nul
echo ========================================
echo בדיקת סטטוס האתר
echo ========================================
echo.

echo [1/4] בודק קונטיינרים...
docker compose ps
echo.

echo [2/4] בודק גרסת WordPress...
docker compose --env-file env.local run --rm wpcli wp core version
echo.

echo [3/4] בודק רשימת תוספים...
docker compose --env-file env.local run --rm wpcli wp plugin list --status=active
echo.

echo [4/4] בודק תבניות פעילות...
docker compose --env-file env.local run --rm wpcli wp theme list --status=active
echo.

echo ========================================
echo בדיקה הושלמה
echo ========================================
echo.
echo האתר: http://localhost:8080
echo Admin: http://localhost:8080/wp-admin
echo.
pause


@echo off
chcp 65001 >nul
echo ========================================
echo ניקוי Cache של WordPress
echo ========================================
echo.

cd /d "%~dp0"

echo [INFO] מוחק cache של WordPress...
docker compose --env-file env.local exec wordpress sh -c "rm -rf /var/www/html/wp-content/cache/* 2>/dev/null; echo 'Cache cleared'"

echo.
echo [INFO] מפעיל מחדש את WordPress...
docker compose --env-file env.local restart wordpress

echo.
echo [INFO] ממתין 5 שניות...
timeout /t 5 /nobreak >nul

echo.
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo.
pause



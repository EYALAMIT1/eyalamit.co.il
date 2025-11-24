@echo off
chcp 65001 >nul
echo ========================================
echo הפעלה מחדש של WordPress
echo ========================================
echo.
echo [INFO] מכבה ומדליק את קונטיינר WordPress...
cd /d "%~dp0"
docker compose --env-file env.local restart wordpress
echo.
echo [INFO] ממתין 5 שניות...
timeout /t 5 /nobreak >nul
echo.
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo.
pause



@echo off
chcp 65001 >nul
echo ========================================
echo הפעלה מחדש מלאה של כל הקונטיינרים
echo ========================================
echo.

cd /d "%~dp0"

echo [INFO] מכבה את כל הקונטיינרים...
docker compose --env-file env.local down

echo.
echo [INFO] ממתין 3 שניות...
timeout /t 3 /nobreak >nul

echo.
echo [INFO] מפעיל מחדש את כל הקונטיינרים...
docker compose --env-file env.local up -d

echo.
echo [INFO] ממתין 10 שניות לקונטיינרים להתחיל...
timeout /t 10 /nobreak >nul

echo.
echo [INFO] בודק סטטוס...
docker compose --env-file env.local ps

echo.
echo ========================================
echo [SUCCESS] הפעלה מחדש הושלמה!
echo ========================================
echo.
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo.
pause



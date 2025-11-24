@echo off
chcp 65001 >nul
echo ========================================
echo הפעלת Docker Compose Stack
echo ========================================
echo.

echo [INFO] בודק אם Docker Desktop רץ...
docker ps >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Docker Desktop לא רץ!
    echo [INFO] פתח את Docker Desktop ונסה שוב.
    pause
    exit /b 1
)

echo [OK] Docker Desktop רץ
echo.
echo [INFO] מריץ docker compose עם env.local...
echo.

docker compose --env-file env.local up -d

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] שגיאה בהפעלת Docker Compose!
    pause
    exit /b 1
)

echo.
echo [INFO] ממתין לקונטיינרים להתחיל...
timeout /t 5 /nobreak >nul

echo.
echo [INFO] בודק סטטוס קונטיינרים...
docker compose ps

echo.
echo ========================================
echo [SUCCESS] Docker Compose הופעל!
echo ========================================
echo.
echo האתר זמין ב: http://localhost:8080
echo phpMyAdmin זמין ב: http://localhost:8081
echo.
pause



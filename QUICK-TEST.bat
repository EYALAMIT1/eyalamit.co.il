@echo off
chcp 65001 >nul
echo ========================================
echo בדיקה מהירה של האתר
echo ========================================
echo.

cd /d "%~dp0"

echo [1] בודק אם WordPress נטען...
docker compose --env-file env.local run --rm wpcli wp core version 2>&1 | findstr /v "Container Running Waiting Healthy"
echo.

echo [2] בודק שגיאות אחרונות...
docker compose --env-file env.local logs wordpress --tail 10 2>&1 | findstr /i "fatal error"
echo.

echo [3] בודק אם האתר נגיש...
curl -s -o nul -w "HTTP Status: %%{http_code}\n" http://localhost:8080 2>nul || echo "Could not check HTTP status"
echo.

echo ========================================
echo [INFO] בדוק את האתר ב: http://localhost:8080
echo ========================================
echo.
pause



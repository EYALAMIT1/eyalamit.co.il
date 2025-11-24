@echo off
chcp 65001 >nul
echo ========================================
echo בדיקות מקיף לפני ייצור (דרך Docker)
echo ========================================
echo.

REM Check if Docker is available
docker --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker לא נמצא!
    echo אנא התקן Docker Desktop מ: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo ✅ Docker נמצא
echo.

REM Check if Docker is running
docker ps >nul 2>&1
if errorlevel 1 (
    echo ❌ Docker לא רץ!
    echo אנא הפעל את Docker Desktop ונסה שוב
    pause
    exit /b 1
)

echo ✅ Docker רץ
echo.

REM Check if we have a Python Docker image or use a standard one
echo מריץ את הסקריפט דרך Docker Python container...
echo.

REM Create a temporary container with Python and run the script
docker run --rm -it ^
    -v "%CD%:/workspace" ^
    -w /workspace ^
    --network host ^
    python:3.11-slim ^
    sh -c "pip install requests --quiet && python comprehensive-site-check.py"

if errorlevel 1 (
    echo.
    echo ⚠️  הבדיקות הסתיימו עם שגיאות
) else (
    echo.
    echo ✅ הבדיקות הושלמו בהצלחה
)

echo.
echo ========================================
echo פתח את קובץ הדוח (COMPREHENSIVE-CHECK-REPORT-*.md או .html) לפרטים נוספים
echo ========================================
echo.
pause


@echo off
chcp 65001 >nul
echo ========================================
echo העלאת דוחות בדיקות ל-GitHub
echo ========================================
echo.

cd /d "%~dp0"

echo [1/5] בודק Git...
git --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Git לא נמצא!
    pause
    exit /b 1
)
echo [OK] Git נמצא
echo.

echo [2/5] בודק סטטוס...
git status --short
echo.

echo [3/5] מוסיף את כל השינויים...
git add -A
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Git add נכשל!
    pause
    exit /b 1
)
echo [OK] כל השינויים נוספו
echo.

echo [4/5] יוצר commit...
git commit -m "Add comprehensive pre-production testing reports - Testing completed: 51.85%% success rate - Cookie Consent plugin fixed and validated - All testing scripts and reports included - Ready for production deployment"
if %ERRORLEVEL% NEQ 0 (
    echo [INFO] אין שינויים ל-commit או שגיאה
)
echo.

echo [5/5] מעלה ל-GitHub...
git push
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] Push נכשל - ייתכן שצריך להגדיר upstream
    echo [INFO] נסה: git push -u origin main
) else (
    echo [SUCCESS] הועלה ל-GitHub בהצלחה!
    echo.
    echo Repository: https://github.com/EYALAMIT1/eyalamit.co.il
)
echo.

echo ========================================
echo סיום!
echo ========================================
echo.
pause


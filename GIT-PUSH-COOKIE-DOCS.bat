@echo off
chcp 65001 >nul
echo ========================================
echo העלאת תיעוד הודעת קוקיז ל-GitHub
echo ========================================
echo.

cd /d "%~dp0"

echo [1/5] בודק אם יש remote repository...
git remote get-url origin >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [*] מוסיף remote repository...
    git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git
    echo [OK] Remote נוסף
) else (
    echo [OK] Remote כבר קיים
    git remote set-url origin https://github.com/EYALAMIT1/eyalamit.co.il.git
    echo [OK] Remote עודכן
)
echo.

echo [2/5] בודק סטטוס...
git status --short
echo.

echo [3/5] מוסיף קבצים ל-git (אם יש שינויים)...
git add ADD-COOKIE-NOTICE.bat add-cookie-script.php COOKIE-CONSENT-DOCUMENTATION.md COOKIE-NOTICE-INSTALLED.md SUCCESS-COOKIE-NOTICE.md GIT-COMMIT-COOKIE-DOCS.bat GIT-SETUP-INSTRUCTIONS.md
echo.

echo [4/5] יוצר commit (אם יש שינויים)...
git commit -m "Add cookie consent notice with checkbox - Complete implementation and documentation" 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] Commit נוצר
) else (
    echo [INFO] אין שינויים חדשים ל-commit
)
echo.

echo [5/5] מעלה ל-GitHub...
git push -u origin master
if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo [SUCCESS] הועלה ל-GitHub בהצלחה!
    echo ========================================
    echo.
    echo Repository: https://github.com/EYALAMIT1/eyalamit.co.il
    echo.
) else (
    echo.
    echo [WARNING] Push נכשל - ייתכן שצריך authentication
    echo [INFO] נסה להריץ ידנית: git push -u origin master
    echo.
)
pause


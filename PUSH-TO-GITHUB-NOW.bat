@echo off
chcp 65001 >nul
echo ========================================
echo העלאת תיעוד הודעת קוקיז ל-GitHub
echo ========================================
echo.

cd /d "%~dp0"

echo [1/4] בודק remote repository...
git remote -v
echo.

echo [2/4] בודק סטטוס...
git status --short
echo.

echo [3/4] מנסה לדחוף ל-GitHub...
echo Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git
echo Branch: master
echo.
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
    echo ========================================
    echo [INFO] Push נכשל - ייתכן שצריך authentication
    echo ========================================
    echo.
    echo אם נדרש authentication, הרץ:
    echo   git push -u origin master
    echo.
    echo או השתמש ב-GitHub Desktop או ב-Git Credential Manager
    echo.
)
pause


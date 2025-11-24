@echo off
chcp 65001 >nul
echo ========================================
echo הוספת תיעוד הודעת קוקיז ל-Git
echo ========================================
echo.

cd /d "%~dp0"

echo [1/5] בודק אם יש git repository...
if not exist .git (
    echo [*] יוצר git repository חדש...
    git init
)

echo [2/5] מגדיר זהות git...
git config user.email "eyal@eyalamit.co.il"
git config user.name "Eyal Amit"

echo [3/5] מוסיף קבצים ל-git...
git add ADD-COOKIE-NOTICE.bat
git add add-cookie-script.php
git add COOKIE-CONSENT-DOCUMENTATION.md
git add COOKIE-NOTICE-INSTALLED.md
git add SUCCESS-COOKIE-NOTICE.md

echo [4/5] בודק סטטוס...
git status --short

echo [5/5] יוצר commit...
git commit -m "Add cookie consent notice with checkbox - Complete implementation and documentation"

echo.
echo ========================================
echo סיום!
echo ========================================
echo.
echo הקבצים נוספו ל-git בהצלחה!
echo.
echo כדי להעלות ל-GitHub:
echo 1. צור repository חדש ב-GitHub
echo 2. הרץ: git remote add origin [URL]
echo 3. הרץ: git push -u origin main
echo.
pause


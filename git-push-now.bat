@echo off
chcp 65001 >nul
echo ========================================
echo העלאת הפרויקט ל-GitHub
echo ========================================
echo.

REM Find project directory
echo [1/7] מחפש תיקיית פרויקט...
cd /d "C:\Users\USER\Pictures"
for /d %%d in (*) do (
    cd /d "%%d"
    for /d %%s in (*) do (
        if "%%s"=="new website AI nov 2025" (
            cd /d "%%s"
            goto :found
        )
    )
    cd /d "C:\Users\USER\Pictures"
)
echo [ERROR] לא נמצאה תיקיית הפרויקט!
pause
exit /b 1

:found
echo [OK] עובד בתיקייה: %CD%
echo.

REM Check Git
echo [2/7] בודק התקנת Git...
git --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Git לא מותקן או לא ב-PATH!
    pause
    exit /b 1
)
git --version
echo.

REM Initialize git if needed
echo [3/7] בודק/יוצר Git repository...
if not exist ".git" (
    git init
    echo [OK] Git repository נוצר
) else (
    echo [OK] Git repository כבר קיים
)
echo.

REM Check if remote exists
echo [4/7] בודק/מוסיף remote repository...
git remote get-url origin >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git
    echo [OK] Remote נוסף
) else (
    echo [OK] Remote כבר קיים
    git remote set-url origin https://github.com/EYALAMIT1/eyalamit.co.il.git
)
echo.

REM Add all files
echo [5/7] מוסיף קבצים ל-staging...
git add .
echo [OK] קבצים נוספו
echo.

REM Commit
echo [6/7] מבצע commit...
git commit -m "Initial commit: WordPress site with Docker setup and local environment configuration"
if %ERRORLEVEL% EQU 0 (
    echo [OK] Commit בוצע
) else (
    echo [INFO] אין שינויים ל-commit או commit כבר בוצע
)
echo.

REM Set branch to main
echo [7/7] מגדיר branch ל-main...
git branch -M main
echo.

REM Push to GitHub
echo ========================================
echo דוחף ל-GitHub...
echo ========================================
echo Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git
echo.
echo אם תתבקש, הזן את ה-Personal Access Token שלך מ-GitHub
echo.
git push -u origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo [SUCCESS] העלאה ל-GitHub הצליחה!
    echo ========================================
    echo.
    echo צפה ב-repository שלך ב:
    echo https://github.com/EYALAMIT1/eyalamit.co.il
    echo.
) else (
    echo.
    echo ========================================
    echo [ERROR] העלאה נכשלה!
    echo ========================================
    echo.
    echo אם אתה משתמש ב-HTTPS, ייתכן שתצטרך להזין Personal Access Token
    echo.
)

pause


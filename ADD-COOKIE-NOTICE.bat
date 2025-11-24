@echo off
chcp 65001 >nul
echo ========================================
echo הוספת הודעת קוקיז ל-WordPress
echo ========================================
echo.

REM Check if Docker is running
docker ps >nul 2>&1
if errorlevel 1 (
    echo [שגיאה] Docker לא פועל!
    echo אנא הפעל את Docker Desktop ונסה שוב.
    pause
    exit /b 1
)

echo [1/4] בודק שהקונטיינר פועל...
docker exec newwebsiteainov2025take2-wordpress-1 echo "OK" >nul 2>&1
if errorlevel 1 (
    echo [שגיאה] הקונטיינר לא פועל!
    echo אנא הפעל את הקונטיינרים: docker compose up -d
    pause
    exit /b 1
)
echo [✓] הקונטיינר פועל

echo.
echo [2/4] מעתיק סקריפט PHP להוספת הקוד...
if not exist "add-cookie-script.php" (
    echo [שגיאה] הקובץ add-cookie-script.php לא נמצא!
    pause
    exit /b 1
)
type add-cookie-script.php | docker exec -i newwebsiteainov2025take2-wordpress-1 sh -c "cat > /tmp/add-cookie.php"
if errorlevel 1 (
    echo [שגיאה] לא הצלחתי להעתיק את הסקריפט
    pause
    exit /b 1
)
echo [✓] הסקריפט הועתק

echo.
echo [3/4] מריץ את הסקריפט ומוסיף את הקוד...
docker exec newwebsiteainov2025take2-wordpress-1 php /tmp/add-cookie.php > temp-result.txt 2>&1
set /p RESULT=<temp-result.txt
del temp-result.txt >nul 2>&1

if "%RESULT%"=="ALREADY_EXISTS" (
    echo [!] הקוד כבר קיים בקובץ functions.php
    echo [!] אם ההודעה לא מופיעה, נסה לנקות את localStorage בדפדפן
    goto :check
)

if "%RESULT%"=="SUCCESS" (
    echo [✓] הקוד נוסף בהצלחה!
) else (
    echo [שגיאה] לא הצלחתי להוסיף את הקוד
    echo תוצאה: %RESULT%
    pause
    exit /b 1
)

:check
echo.
echo [4/4] בודק תקינות הקוד...
docker exec newwebsiteainov2025take2-wordpress-1 php -l /var/www/html/wp-content/themes/bridge-child/functions.php > temp-syntax.txt 2>&1
findstr /C:"No syntax errors" temp-syntax.txt >nul
if errorlevel 1 (
    echo [!] אזהרה: ייתכן שיש שגיאת תחביר
    type temp-syntax.txt
) else (
    echo [✓] הקוד תקין - אין שגיאות תחביר
)
del temp-syntax.txt >nul 2>&1

echo.
echo ========================================
echo סיום!
echo ========================================
echo.
echo ההודעת קוקיז נוספה בהצלחה!
echo.
echo כדי לבדוק:
echo 1. פתח את האתר: http://localhost:8080
echo 2. אם לא רואה הודעה:
echo    - לחץ F12 (פתח Developer Tools)
echo    - לך ל-Application ^> Local Storage
echo    - מחק את cookie_consent_accepted
echo    - רענן את העמוד (F5)
echo.
echo ההודעה אמורה להופיע בתחתית המסך!
echo.
pause


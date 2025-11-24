@echo off
chcp 65001 >nul
echo ========================================
echo התקנה מהירה של Python 3.12
echo ========================================
echo.
echo מתקין Python דרך winget...
echo.

winget install Python.Python.3.12 --silent --accept-package-agreements --accept-source-agreements

if errorlevel 1 (
    echo.
    echo ❌ ההתקנה נכשלה.
    echo.
    echo נסה אחת מהאפשרויות הבאות:
    echo.
    echo 1. פתח את Microsoft Store וחפש "Python 3.12"
    echo 2. הורד מ- https://www.python.org/downloads/
    echo.
    pause
    exit /b 1
)

echo.
echo ✅ Python הותקן בהצלחה!
echo.
echo ⚠️  חשוב: פתח מחדש את CMD/PowerShell אחרי ההתקנה.
echo.
echo אחרי שתפתח מחדש, הרץ: RUN-COMPREHENSIVE-CHECKS.bat
echo.
pause


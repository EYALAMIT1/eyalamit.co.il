@echo off
chcp 65001 >nul
echo ========================================
echo התקנת Python
echo ========================================
echo.

REM Check if winget is available
where winget >nul 2>&1
if not errorlevel 1 (
    echo ✅ winget נמצא - מתקין Python דרך winget...
    echo.
    winget install Python.Python.3.12 --silent --accept-package-agreements --accept-source-agreements
    if not errorlevel 1 (
        echo.
        echo ✅ Python הותקן בהצלחה!
        echo.
        echo ⚠️  חשוב: פתח מחדש את CMD/PowerShell אחרי ההתקנה.
        echo.
        pause
        exit /b 0
    ) else (
        echo.
        echo ⚠️  ההתקנה דרך winget נכשלה. נסה דרך אחת מהאפשרויות הבאות:
        echo.
    )
) else (
    echo ⚠️  winget לא נמצא. השתמש באחת מהאפשרויות הבאות:
    echo.
)

echo אפשרויות נוספות להתקנת Python:
echo.
echo 1. דרך Microsoft Store (הכי קל):
echo    - לחץ Win+R
echo    - הקלד: ms-windows-store://pdp/?ProductId=9NCVDN91XZQP
echo    - לחץ התקן
echo.
echo 2. דרך האתר הרשמי:
echo    - לך ל: https://www.python.org/downloads/
echo    - הורד את Python 3.12
echo    - בעת ההתקנה, סמן ✓ "Add Python to PATH"
echo.
echo 3. הורד ישירות:
echo    - Python 3.12: https://www.python.org/ftp/python/3.12.0/python-3.12.0-amd64.exe
echo.
pause


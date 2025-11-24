@echo off
chcp 65001 >nul
echo ========================================
echo בדיקות מקיף לפני ייצור
echo ========================================
echo.

REM Try to find Python in multiple locations
set PYTHON_CMD=

REM Try standard python command
python --version >nul 2>&1
if not errorlevel 1 (
    set PYTHON_CMD=python
    goto :found_python
)

REM Try python3 command
python3 --version >nul 2>&1
if not errorlevel 1 (
    set PYTHON_CMD=python3
    goto :found_python
)

REM Try py launcher
py --version >nul 2>&1
if not errorlevel 1 (
    set PYTHON_CMD=py
    goto :found_python
)

REM Try common installation paths
if exist "C:\Python3*\python.exe" (
    for /f "delims=" %%i in ('dir /b /ad "C:\Python3*"') do (
        set PYTHON_CMD=C:\%%i\python.exe
        goto :found_python
    )
)

if exist "C:\Program Files\Python3*\python.exe" (
    for /f "delims=" %%i in ('dir /b /ad "C:\Program Files\Python3*"') do (
        set PYTHON_CMD=C:\Program Files\%%i\python.exe
        goto :found_python
    )
)

if exist "%LOCALAPPDATA%\Programs\Python\Python3*\python.exe" (
    for /f "delims=" %%i in ('dir /b /ad "%LOCALAPPDATA%\Programs\Python\Python3*"') do (
        set PYTHON_CMD=%LOCALAPPDATA%\Programs\Python\%%i\python.exe
        goto :found_python
    )
)

REM Python not found - show instructions
echo ❌ Python לא נמצא במערכת!
echo.
echo אפשרויות להתקנת Python:
echo.
echo 1. דרך Microsoft Store (מומלץ):
echo    - פתח את Microsoft Store
echo    - חפש "Python 3.12" או "Python 3.11"
echo    - לחץ על "התקן"
echo.
echo 2. דרך האתר הרשמי:
echo    - לך ל- https://www.python.org/downloads/
echo    - הורד את Python 3.11 או 3.12
echo    - בעת ההתקנה, סמן ✓ "Add Python to PATH"
echo.
echo 3. דרך winget (אם מותקן):
echo    winget install Python.Python.3.12
echo.
echo אחרי ההתקנה, פתח מחדש את CMD/PowerShell והרץ שוב את הסקריפט.
echo.
pause
exit /b 1

:found_python
echo ✅ Python נמצא: %PYTHON_CMD%
%PYTHON_CMD% --version
echo.

REM Check if requests module is installed
echo בודק חבילות Python נדרשות...
%PYTHON_CMD% -c "import requests" >nul 2>&1
if errorlevel 1 (
    echo ⚠️  החבילה 'requests' לא מותקנת
    echo מתקין את החבילה 'requests'...
    %PYTHON_CMD% -m pip install requests --quiet
    if errorlevel 1 (
        echo ❌ שגיאה בהתקנת חבילת requests
        echo נסה להריץ ידנית: %PYTHON_CMD% -m pip install requests
        pause
        exit /b 1
    )
    echo ✅ החבילה 'requests' הותקנה בהצלחה
) else (
    echo ✅ כל החבילות הנדרשות מותקנות
)

echo.
echo מתחיל בדיקות מקיפות...
echo.

REM Run the comprehensive check script
%PYTHON_CMD% comprehensive-site-check.py

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


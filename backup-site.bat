@echo off
chcp 65001 >nul
echo ========================================
echo גיבוי מלא של האתר WordPress
echo ========================================
echo.
echo [INFO] מתחיל תהליך הגיבוי...
echo [INFO] תיקיית עבודה: %CD%
echo.

REM בדיקה שהקובץ PowerShell קיים
if not exist "%~dp0backup-site.ps1" (
    echo [ERROR] הקובץ backup-site.ps1 לא נמצא!
    echo [ERROR] ודא שאתה מריץ את הסקריפט מתיקיית הפרויקט
    pause
    exit /b 1
)

echo [INFO] מריץ את סקריפט הגיבוי...
echo.

REM הפעלת הסקריפט PowerShell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0backup-site.ps1"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ========================================
    echo [ERROR] שגיאה בביצוע הגיבוי!
    echo ========================================
    echo.
    echo בדוק:
    echo 1. שהקונטיינרים של Docker רצים (docker ps)
    echo 2. שהקובץ backup-site.ps1 קיים
    echo 3. שיש הרשאות כתיבה בתיקיית הפרויקט
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo [SUCCESS] הגיבוי הושלם בהצלחה!
echo ========================================
echo.
pause




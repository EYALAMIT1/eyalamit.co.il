@echo off
chcp 65001 >nul
echo ========================================
echo גיבוי מלא של האתר WordPress
echo ========================================
echo.
echo [INFO] מתחיל תהליך הגיבוי...
echo [INFO] תיקיית עבודה: %CD%
echo.

REM בדיקה שהקובץ PowerShell קיים (הגרסה החדשה תחילה)
set "SCRIPT_FILE=backup-site.ps1"
if not exist "%~dp0%SCRIPT_FILE%" (
    set "SCRIPT_FILE=backup-site-simple-fixed.ps1"
    if not exist "%~dp0%SCRIPT_FILE%" (
        set "SCRIPT_FILE=backup-site-fixed.ps1"
        if not exist "%~dp0%SCRIPT_FILE%" (
            echo [ERROR] Script file not found!
            echo [ERROR] Make sure you run the script from the project directory
            pause
            exit /b 1
        )
    )
)

echo [INFO] מריץ את סקריפט הגיבוי...
echo.

REM ניסיון להריץ עם PowerShell 7 (pwsh) - אם קיים
where pwsh >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo [INFO] משתמש ב-PowerShell 7...
    pwsh.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0%SCRIPT_FILE%"
) else (
    echo [INFO] PowerShell 7 לא נמצא, משתמש ב-PowerShell רגיל...
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0%SCRIPT_FILE%"
)

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



@echo off
chcp 65001 >nul
echo ========================================
echo גיבוי מלא של האתר WordPress
echo ========================================
echo.

REM בדיקה שהקונטיינרים רצים
echo [1/5] בודק שהקונטיינרים רצים...
docker ps --filter "name=.*db.*" --format "{{.Names}}" | findstr /i "db" >nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] קונטיינר ה-DB לא רץ!
    echo [INFO] הפעל את Docker Compose תחילה: docker compose up -d
    pause
    exit /b 1
)
echo [OK] הקונטיינרים רצים
echo.

REM יצירת תיקיית גיבוי
echo [2/5] יוצר תיקיית גיבוי...
set "BackupDir=.\backups"
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c-%%a-%%b)
for /f "tokens=1-2 delims=/:" %%a in ('time /t') do (set mytime=%%a%%b)
set "mytime=%mytime: =0%"
set "Timestamp=%mydate%_%mytime%"
set "BackupFolder=%BackupDir%\backup_%Timestamp%"

if not exist "%BackupDir%" mkdir "%BackupDir%"
if not exist "%BackupFolder%" mkdir "%BackupFolder%"
echo [OK] תיקיית גיבוי: %BackupFolder%
echo.

REM קבלת שם הקונטיינר
echo [3/5] מבצע גיבוי בסיס הנתונים...
for /f %%i in ('docker ps --filter "name=.*db.*" --format "{{.Names}}"') do set "ContainerName=%%i"

set "DBName=eyal_local"
set "DBUser=eyal"
set "DBPassword=eyalpass"
set "DBBackupFile=%BackupFolder%\database_backup.sql"

docker exec %ContainerName% mysqldump -u%DBUser% -p%DBPassword% %DBName% > "%DBBackupFile%"

if %ERRORLEVEL% EQU 0 (
    echo [OK] גיבוי DB הושלם: %DBBackupFile%
) else (
    echo [ERROR] שגיאה בגיבוי DB!
    pause
    exit /b 1
)
echo.

REM גיבוי קבצים
echo [4/5] מבצע גיבוי קבצים...
set "SiteDir=.\eyalamit.co.il_bm1763033821dm"
set "FilesBackupFile=%BackupFolder%\files_backup.zip"

REM יצירת ZIP באמצעות PowerShell (אם זמין)
powershell.exe -NoProfile -Command "Compress-Archive -Path '%SiteDir%\*' -DestinationPath '%FilesBackupFile%' -Force" 2>nul

if exist "%FilesBackupFile%" (
    echo [OK] גיבוי קבצים הושלם: %FilesBackupFile%
) else (
    echo [WARNING] לא הצלחתי ליצור ZIP אוטומטית
    echo [INFO] תוכל ליצור ZIP ידנית מתיקיית: %SiteDir%
)
echo.

REM יצירת קובץ מידע
echo [5/5] יוצר קובץ מידע...
set "InfoFile=%BackupFolder%\backup_info.txt"
(
echo גיבוי WordPress - מידע
echo ======================
echo.
echo תאריך גיבוי: %date% %time%
echo תיקיית גיבוי: %BackupFolder%
echo.
echo קבצי גיבוי:
echo - בסיס נתונים: database_backup.sql
echo - קבצים: files_backup.zip
echo.
echo פרטי בסיס הנתונים:
echo - שם DB: %DBName%
echo - משתמש: %DBUser%
echo.
echo הוראות שחזור:
echo 1. שחזור DB: docker exec -i [container_name] mysql -u%DBUser% -p%DBPassword% %DBName% ^< database_backup.sql
echo 2. שחזור קבצים: חלץ את files_backup.zip לתיקיית האתר
) > "%InfoFile%"

echo [OK] קובץ מידע נוצר: %InfoFile%
echo.

echo ========================================
echo [SUCCESS] הגיבוי הושלם בהצלחה!
echo ========================================
echo.
echo תיקיית גיבוי: %BackupFolder%
echo קבצי גיבוי:
echo   - %DBBackupFile%
echo   - %FilesBackupFile%
echo   - %InfoFile%
echo.
pause


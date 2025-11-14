# ========================================
# סקריפט גיבוי מלא לאתר WordPress
# ========================================

$ErrorActionPreference = "Stop"

# הגדרות
$BackupDir = ".\backups"
$Timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$BackupFolder = "$BackupDir\backup_$Timestamp"
$DBName = "eyal_local"
$DBUser = "eyal"
$DBPassword = "eyalpass"
$DBRootPassword = "strong_root_pass"
$SiteDir = ".\eyalamit.co.il_bm1763033821dm"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "גיבוי מלא של האתר WordPress" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# בדיקה שהקונטיינרים רצים
Write-Host "[1/4] בודק שהקונטיינרים רצים..." -ForegroundColor Yellow
$dbRunning = docker ps --filter "name=.*db.*" --format "{{.Names}}" | Select-String "db"
if (-not $dbRunning) {
    Write-Host "[ERROR] קונטיינר ה-DB לא רץ! הפעל את Docker Compose תחילה." -ForegroundColor Red
    exit 1
}
Write-Host "[OK] הקונטיינרים רצים" -ForegroundColor Green
Write-Host ""

# יצירת תיקיית גיבוי
Write-Host "[2/4] יוצר תיקיית גיבוי..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path $BackupFolder | Out-Null
Write-Host "[OK] תיקיית גיבוי: $BackupFolder" -ForegroundColor Green
Write-Host ""

# גיבוי בסיס הנתונים
Write-Host "[3/4] מבצע גיבוי בסיס הנתונים..." -ForegroundColor Yellow
$dbContainerName = docker ps --filter "name=.*db.*" --format "{{.Names}}" | Select-Object -First 1
$dbBackupFile = "$BackupFolder\database_backup.sql"

docker exec $dbContainerName mysqldump -u$DBUser -p$DBPassword $DBName > $dbBackupFile

if ($LASTEXITCODE -eq 0) {
    $dbSize = (Get-Item $dbBackupFile).Length / 1MB
    Write-Host "[OK] גיבוי DB הושלם: $dbBackupFile ($([math]::Round($dbSize, 2)) MB)" -ForegroundColor Green
} else {
    Write-Host "[ERROR] שגיאה בגיבוי DB!" -ForegroundColor Red
    exit 1
}
Write-Host ""

# גיבוי קבצים
Write-Host "[4/4] מבצע גיבוי קבצים..." -ForegroundColor Yellow
$filesBackupFile = "$BackupFolder\files_backup.zip"

# גיבוי קבצים (ללא תיקיות לא נחוצות)
$excludeDirs = @(
    "wp-content/cache",
    "wp-content/upgrade",
    "wp-content/backups",
    "wp-content/envato-backups",
    ".git"
)

$tempBackupDir = "$BackupFolder\temp_files"
New-Item -ItemType Directory -Force -Path $tempBackupDir | Out-Null

# העתקת קבצים (עם סינון)
Get-ChildItem -Path $SiteDir -Recurse | Where-Object {
    $exclude = $false
    foreach ($excludeDir in $excludeDirs) {
        if ($_.FullName -like "*\$excludeDir\*") {
            $exclude = $true
            break
        }
    }
    -not $exclude
} | Copy-Item -Destination {
    $_.FullName.Replace($SiteDir, $tempBackupDir)
} -Force

# יצירת ZIP
Compress-Archive -Path "$tempBackupDir\*" -DestinationPath $filesBackupFile -Force
Remove-Item -Path $tempBackupDir -Recurse -Force

if (Test-Path $filesBackupFile) {
    $filesSize = (Get-Item $filesBackupFile).Length / 1MB
    Write-Host "[OK] גיבוי קבצים הושלם: $filesBackupFile ($([math]::Round($filesSize, 2)) MB)" -ForegroundColor Green
} else {
    Write-Host "[ERROR] שגיאה בגיבוי קבצים!" -ForegroundColor Red
    exit 1
}
Write-Host ""

# יצירת קובץ מידע
$infoFile = "$BackupFolder\backup_info.txt"
@"
גיבוי WordPress - מידע
======================

תאריך גיבוי: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
תיקיית גיבוי: $BackupFolder

קבצי גיבוי:
- בסיס נתונים: database_backup.sql
- קבצים: files_backup.zip

פרטי בסיס הנתונים:
- שם DB: $DBName
- משתמש: $DBUser

הוראות שחזור:
1. שחזור DB: docker exec -i [container_name] mysql -u$DBUser -p$DBPassword $DBName < database_backup.sql
2. שחזור קבצים: חלץ את files_backup.zip לתיקיית האתר

"@ | Out-File -FilePath $infoFile -Encoding UTF8

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "[SUCCESS] הגיבוי הושלם בהצלחה!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "תיקיית גיבוי: $BackupFolder" -ForegroundColor Yellow
Write-Host "קבצי גיבוי:" -ForegroundColor Yellow
Write-Host "  - $dbBackupFile" -ForegroundColor White
Write-Host "  - $filesBackupFile" -ForegroundColor White
Write-Host "  - $infoFile" -ForegroundColor White
Write-Host ""




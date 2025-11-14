# Script to push project to GitHub
# Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git

$ErrorActionPreference = "Stop"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "העלאת הפרויקט ל-GitHub" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Git is available
Write-Host "[1/7] בודק התקנת Git..." -ForegroundColor Yellow
try {
    $gitVersion = git --version 2>&1
    Write-Host "[OK] Git נמצא: $gitVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Git לא מותקן או לא ב-PATH!" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Find project directory
Write-Host "[2/7] מחפש תיקיית פרויקט..." -ForegroundColor Yellow
$dirs = Get-ChildItem -Directory -Path "C:\Users\USER\Pictures"
$targetDir = $null
foreach ($dir in $dirs) {
    $subDirs = Get-ChildItem -Directory -Path $dir.FullName -ErrorAction SilentlyContinue
    $subTarget = $subDirs | Where-Object { $_.Name -match "new website" }
    if ($subTarget) {
        $targetDir = $subTarget[0].FullName
        break
    }
}

if (-not $targetDir) {
    Write-Host "[ERROR] לא נמצאה תיקיית הפרויקט!" -ForegroundColor Red
    exit 1
}

Set-Location $targetDir
Write-Host "[OK] עובד בתיקייה: $targetDir" -ForegroundColor Green
Write-Host ""

# Initialize git if needed
Write-Host "[3/7] בודק/יוצר Git repository..." -ForegroundColor Yellow
if (-not (Test-Path ".git")) {
    git init
    Write-Host "[OK] Git repository נוצר" -ForegroundColor Green
} else {
    Write-Host "[OK] Git repository כבר קיים" -ForegroundColor Green
}
Write-Host ""

# Check if remote exists
Write-Host "[4/7] בודק/מוסיף remote repository..." -ForegroundColor Yellow
$remoteExists = git remote get-url origin 2>&1
if ($LASTEXITCODE -ne 0) {
    git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git
    Write-Host "[OK] Remote נוסף" -ForegroundColor Green
} else {
    Write-Host "[OK] Remote כבר קיים: $remoteExists" -ForegroundColor Green
    git remote set-url origin https://github.com/EYALAMIT1/eyalamit.co.il.git
}
Write-Host ""

# Add all files (respecting .gitignore)
Write-Host "[5/7] מוסיף קבצים ל-staging..." -ForegroundColor Yellow
git add .
Write-Host "[OK] קבצים נוספו" -ForegroundColor Green
Write-Host ""

# Check if there are changes to commit
Write-Host "[6/7] בודק שינויים ל-commit..." -ForegroundColor Yellow
$status = git status --porcelain
if ([string]::IsNullOrWhiteSpace($status)) {
    Write-Host "[INFO] אין שינויים ל-commit" -ForegroundColor Yellow
} else {
    git commit -m "Initial commit: WordPress site with Docker setup and local environment configuration"
    Write-Host "[OK] Commit בוצע" -ForegroundColor Green
}
Write-Host ""

# Set default branch to main
Write-Host "[7/7] מגדיר branch ל-main..." -ForegroundColor Yellow
git branch -M main
Write-Host ""

# Push to GitHub
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "דוחף ל-GitHub..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git" -ForegroundColor Yellow
Write-Host ""
Write-Host "אם תתבקש, הזן את ה-Personal Access Token שלך מ-GitHub" -ForegroundColor Yellow
Write-Host ""

git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "[SUCCESS] העלאה ל-GitHub הצליחה!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "צפה ב-repository שלך ב:" -ForegroundColor Cyan
    Write-Host "https://github.com/EYALAMIT1/eyalamit.co.il" -ForegroundColor Yellow
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "[ERROR] העלאה נכשלה!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "אם אתה משתמש ב-HTTPS, ייתכן שתצטרך להזין Personal Access Token" -ForegroundColor Yellow
    Write-Host "או להשתמש ב-SSH: git remote set-url origin git@github.com:EYALAMIT1/eyalamit.co.il.git" -ForegroundColor Yellow
    Write-Host ""
}


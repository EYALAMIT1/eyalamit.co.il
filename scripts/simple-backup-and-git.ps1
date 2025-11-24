# Simple backup and git commit
[CmdletBinding()]
param()

$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "=== Backup and Git Update ===" -ForegroundColor Cyan
Write-Host ""

# Step 1: Backup
Write-Host "[1/4] Creating backup..." -ForegroundColor Yellow
& ".\backup-site.ps1"
if ($LASTEXITCODE -ne 0) {
    Write-Host "[WARNING] Backup may have failed, continuing anyway..." -ForegroundColor Yellow
}

Write-Host ""

# Step 2: Git status
Write-Host "[2/4] Checking Git status..." -ForegroundColor Yellow
git status --short

Write-Host ""

# Step 3: Add all
Write-Host "[3/4] Adding all changes..." -ForegroundColor Yellow
git add -A
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Changes added" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Git add failed" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 4: Commit
Write-Host "[4/4] Committing..." -ForegroundColor Yellow
$commitMsg = "Fix PHP 8.2 compatibility - All plugins and theme updated, site working"
git commit -m $commitMsg
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Committed" -ForegroundColor Green
} else {
    Write-Host "[INFO] Nothing to commit or commit failed" -ForegroundColor Yellow
}

Write-Host ""

# Step 5: Push
Write-Host "[5/5] Pushing to GitHub..." -ForegroundColor Yellow
git push
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Pushed to GitHub" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Push failed - may need to set upstream" -ForegroundColor Yellow
    Write-Host "[INFO] Try: git push -u origin main" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Done ===" -ForegroundColor Cyan
Write-Host ""


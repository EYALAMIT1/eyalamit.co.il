# Script to push project to GitHub
# Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git

Write-Host "Checking Git installation..." -ForegroundColor Cyan

# Check if Git is available
try {
    $gitVersion = git --version 2>&1
    Write-Host "Git found: $gitVersion" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Git is not installed or not in PATH!" -ForegroundColor Red
    Write-Host "Please install Git for Windows from: https://git-scm.com/download/win" -ForegroundColor Yellow
    Write-Host "Or use Git Bash if already installed." -ForegroundColor Yellow
    exit 1
}

# Navigate to project directory
$projectPath = "C:\Users\USER\Pictures\5848~1\new website AI nov 2025"
Set-Location $projectPath
Write-Host "`nWorking in: $projectPath" -ForegroundColor Cyan

# Initialize git if needed
if (-not (Test-Path ".git")) {
    Write-Host "`nInitializing Git repository..." -ForegroundColor Cyan
    git init
}

# Check if remote exists
$remoteExists = git remote get-url origin 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "`nAdding remote repository..." -ForegroundColor Cyan
    git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git
} else {
    Write-Host "`nRemote already exists: $remoteExists" -ForegroundColor Yellow
    Write-Host "Updating remote URL..." -ForegroundColor Cyan
    git remote set-url origin https://github.com/EYALAMIT1/eyalamit.co.il.git
}

# Add all files (respecting .gitignore)
Write-Host "`nAdding files to staging..." -ForegroundColor Cyan
git add .

# Check if there are changes to commit
$status = git status --porcelain
if ([string]::IsNullOrWhiteSpace($status)) {
    Write-Host "`nNo changes to commit." -ForegroundColor Yellow
} else {
    Write-Host "`nCommitting changes..." -ForegroundColor Cyan
    git commit -m "Initial commit: WordPress site with Docker setup and local environment configuration"
}

# Set default branch to main
Write-Host "`nSetting default branch to main..." -ForegroundColor Cyan
git branch -M main

# Push to GitHub
Write-Host "`nPushing to GitHub..." -ForegroundColor Cyan
Write-Host "Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git" -ForegroundColor Yellow
git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Successfully pushed to GitHub!" -ForegroundColor Green
    Write-Host "View your repository at: https://github.com/EYALAMIT1/eyalamit.co.il" -ForegroundColor Cyan
} else {
    Write-Host "`n❌ Push failed. You may need to authenticate." -ForegroundColor Red
    Write-Host "If using HTTPS, GitHub may prompt for credentials or Personal Access Token." -ForegroundColor Yellow
    Write-Host "Alternatively, use SSH: git remote set-url origin git@github.com:EYALAMIT1/eyalamit.co.il.git" -ForegroundColor Yellow
}





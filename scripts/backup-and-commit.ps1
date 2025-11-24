# ========================================
# Full backup and Git commit
# ========================================

[CmdletBinding()]
param(
    [string]$SiteDir,
    [string]$CommitMessage = "Fix PHP 8.2 compatibility issues - site working"
)

$ErrorActionPreference = "Stop"

# Auto-detect SiteDir
if (-not $SiteDir) {
    $possibleSiteDirs = Get-ChildItem -Path "." -Directory | Where-Object { $_.Name -match "eyalamit\.co\.il" }
    if ($possibleSiteDirs.Count -eq 1) {
        $SiteDir = $possibleSiteDirs[0].FullName
        Write-Host "[INFO] Auto-detected site directory: $SiteDir" -ForegroundColor Green
    } else {
        throw "Could not auto-detect site directory. Please specify SiteDir parameter."
    }
}

Write-Host ""
Write-Host "=== Full Backup and Git Commit ===" -ForegroundColor Cyan
Write-Host ""

# Step 1: Full backup
Write-Host "[1/4] Creating full backup..." -ForegroundColor Yellow
try {
    & ".\backup-site.ps1" -SiteDir $SiteDir
    if ($LASTEXITCODE -ne 0) {
        throw "Backup failed"
    }
    Write-Host "[OK] Backup completed" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Backup failed: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 2: Check Git status
Write-Host "[2/4] Checking Git status..." -ForegroundColor Yellow
try {
    $gitStatus = git status --porcelain 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "Git not initialized or not a repository"
    }
    
    if (-not $gitStatus) {
        Write-Host "[INFO] No changes to commit" -ForegroundColor Yellow
    } else {
        Write-Host "[INFO] Found changes to commit" -ForegroundColor Green
        Write-Host $gitStatus
    }
} catch {
    Write-Host "[ERROR] Git check failed: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 3: Add all changes
Write-Host "[3/4] Adding all changes to Git..." -ForegroundColor Yellow
try {
    git add -A 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "Git add failed"
    }
    Write-Host "[OK] All changes added" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Git add failed: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 4: Commit
Write-Host "[4/4] Committing changes..." -ForegroundColor Yellow
try {
    $commitOutput = git commit -m $CommitMessage 2>&1
    if ($LASTEXITCODE -ne 0) {
        if ($commitOutput -match "nothing to commit") {
            Write-Host "[INFO] Nothing to commit" -ForegroundColor Yellow
        } else {
            throw "Git commit failed: $commitOutput"
        }
    } else {
        Write-Host "[OK] Changes committed" -ForegroundColor Green
        Write-Host $commitOutput
    }
} catch {
    Write-Host "[ERROR] Git commit failed: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 5: Push to GitHub
Write-Host "[5/5] Pushing to GitHub..." -ForegroundColor Yellow
try {
    $pushOutput = git push 2>&1
    if ($LASTEXITCODE -ne 0) {
        if ($pushOutput -match "no upstream branch") {
            Write-Host "[WARNING] No upstream branch set. Run: git push -u origin main" -ForegroundColor Yellow
        } elseif ($pushOutput -match "nothing to push") {
            Write-Host "[INFO] Nothing to push" -ForegroundColor Yellow
        } else {
            throw "Git push failed: $pushOutput"
        }
    } else {
        Write-Host "[OK] Pushed to GitHub successfully" -ForegroundColor Green
        Write-Host $pushOutput
    }
} catch {
    Write-Host "[ERROR] Git push failed: $_" -ForegroundColor Red
    Write-Host "[INFO] You may need to set upstream: git push -u origin main" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Done ===" -ForegroundColor Cyan
Write-Host ""



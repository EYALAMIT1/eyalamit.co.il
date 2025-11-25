# ========================================
# Pre-Production Checklist Script
# ========================================
# This script runs all necessary checks and backups before production deployment

[CmdletBinding()]
param(
    [switch]$SkipBackup,
    [switch]$SkipGit,
    [switch]$SkipTests
)

$ErrorActionPreference = "Continue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Pre-Production Checklist" -ForegroundColor Cyan
Write-Host " Preparing for production deployment" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$allStepsPassed = $true
$failedSteps = @()

# Step 1: Local Backup
if (-not $SkipBackup) {
    Write-Host "[1/5] Creating local backup..." -ForegroundColor Yellow
    Write-Host "       This may take a few minutes..." -ForegroundColor Gray
    
    try {
        if (Test-Path ".\backup-site.ps1") {
            & ".\backup-site.ps1"
            if ($LASTEXITCODE -eq 0) {
                Write-Host "[OK] Local backup completed successfully" -ForegroundColor Green
            } else {
                Write-Host "[WARNING] Backup may have issues (exit code: $LASTEXITCODE)" -ForegroundColor Yellow
                $failedSteps += "Local backup"
            }
        } else {
            Write-Host "[ERROR] backup-site.ps1 not found!" -ForegroundColor Red
            $allStepsPassed = $false
            $failedSteps += "Local backup (script not found)"
        }
    } catch {
        Write-Host "[ERROR] Backup failed: $_" -ForegroundColor Red
        $allStepsPassed = $false
        $failedSteps += "Local backup"
    }
    Write-Host ""
} else {
    Write-Host "[SKIP] Local backup skipped" -ForegroundColor DarkYellow
    Write-Host ""
}

# Step 2: Check for ZIP file with long path support
Write-Host "[2/5] Checking for ZIP file with long path support..." -ForegroundColor Yellow
$zipFiles = Get-ChildItem "long-path-backup_*.zip" -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending
if ($zipFiles) {
    $latestZip = $zipFiles[0]
    $zipSizeMB = [Math]::Round($latestZip.Length / 1MB, 2)
    Write-Host "[OK] Found ZIP file: $($latestZip.Name)" -ForegroundColor Green
    Write-Host "     Size: $zipSizeMB MB" -ForegroundColor Gray
    Write-Host "     Created: $($latestZip.LastWriteTime)" -ForegroundColor Gray
    
    if ($zipSizeMB -lt 100) {
        Write-Host "[WARNING] ZIP file seems too small - may be incomplete" -ForegroundColor Yellow
    }
} else {
    Write-Host "[WARNING] No ZIP file found. Run create-long-path-zip.ps1 first!" -ForegroundColor Yellow
    Write-Host "          Command: .\create-long-path-zip.ps1" -ForegroundColor Gray
    $failedSteps += "ZIP file check"
}
Write-Host ""

# Step 3: Git Push to GitHub
if (-not $SkipGit) {
    Write-Host "[3/5] Pushing to GitHub..." -ForegroundColor Yellow
    
    try {
        # Check if we're in a git repository
        $gitCheck = git rev-parse --git-dir 2>$null
        if ($LASTEXITCODE -eq 0) {
            # Check status
            Write-Host "       Checking Git status..." -ForegroundColor Gray
            $status = git status --short 2>&1
            if ($status) {
                Write-Host "       Found changes to commit" -ForegroundColor Gray
                
                # Add all
                Write-Host "       Adding all changes..." -ForegroundColor Gray
                git add -A 2>&1 | Out-Null
                
                # Commit
                $commitMsg = "Pre-production: Backup and prepare for deployment - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
                Write-Host "       Committing changes..." -ForegroundColor Gray
                git commit -m $commitMsg 2>&1 | Out-Null
                
                if ($LASTEXITCODE -eq 0) {
                    Write-Host "[OK] Changes committed" -ForegroundColor Green
                } else {
                    Write-Host "[INFO] Nothing to commit or commit failed" -ForegroundColor Yellow
                }
                
                # Push
                Write-Host "       Pushing to GitHub..." -ForegroundColor Gray
                $pushOutput = git push 2>&1
                
                if ($LASTEXITCODE -eq 0) {
                    Write-Host "[OK] Pushed to GitHub successfully" -ForegroundColor Green
                } else {
                    if ($pushOutput -match "no upstream") {
                        Write-Host "[WARNING] No upstream branch set" -ForegroundColor Yellow
                        Write-Host "          Run: git push -u origin main" -ForegroundColor Gray
                    } elseif ($pushOutput -match "nothing to push") {
                        Write-Host "[INFO] Nothing to push" -ForegroundColor Yellow
                    } else {
                        Write-Host "[WARNING] Push failed: $pushOutput" -ForegroundColor Yellow
                        $failedSteps += "Git push"
                    }
                }
            } else {
                Write-Host "[INFO] No changes to commit" -ForegroundColor Yellow
            }
        } else {
            Write-Host "[WARNING] Not a Git repository or Git not initialized" -ForegroundColor Yellow
            $failedSteps += "Git check"
        }
    } catch {
        Write-Host "[ERROR] Git operation failed: $_" -ForegroundColor Red
        $failedSteps += "Git operations"
    }
    Write-Host ""
} else {
    Write-Host "[SKIP] Git push skipped" -ForegroundColor DarkYellow
    Write-Host ""
}

# Step 4: Basic Local Tests
if (-not $SkipTests) {
    Write-Host "[4/5] Running basic local tests..." -ForegroundColor Yellow
    
    $testsPassed = 0
    $testsTotal = 0
    
    # Test 1: Check if Docker containers are running
    $testsTotal++
    Write-Host "       Checking Docker containers..." -ForegroundColor Gray
    try {
        $containers = docker ps --format "{{.Names}}" 2>&1
        if ($containers -match "wordpress" -and $containers -match "db") {
            Write-Host "       [OK] Docker containers are running" -ForegroundColor Green
            $testsPassed++
        } else {
            Write-Host "       [WARNING] Some Docker containers may not be running" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "       [WARNING] Could not check Docker containers" -ForegroundColor Yellow
    }
    
    # Test 2: Check WordPress version
    $testsTotal++
    Write-Host "       Checking WordPress version..." -ForegroundColor Gray
    try {
        $wpVersion = docker compose exec -T wordpress wp core version --allow-root --path=/var/www/html 2>&1
        if ($wpVersion -match "6\.8\.3") {
            Write-Host "       [OK] WordPress version: $wpVersion" -ForegroundColor Green
            $testsPassed++
        } else {
            Write-Host "       [WARNING] WordPress version: $wpVersion (expected 6.8.3)" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "       [WARNING] Could not check WordPress version" -ForegroundColor Yellow
    }
    
    # Test 3: Check if site is accessible
    $testsTotal++
    Write-Host "       Checking if site is accessible..." -ForegroundColor Gray
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8080" -TimeoutSec 5 -UseBasicParsing -ErrorAction SilentlyContinue
        if ($response.StatusCode -eq 200) {
            Write-Host "       [OK] Site is accessible (HTTP 200)" -ForegroundColor Green
            $testsPassed++
        } else {
            Write-Host "       [WARNING] Site returned status code: $($response.StatusCode)" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "       [WARNING] Could not access site: $_" -ForegroundColor Yellow
    }
    
    Write-Host "       Tests passed: $testsPassed/$testsTotal" -ForegroundColor $(if ($testsPassed -eq $testsTotal) { "Green" } else { "Yellow" })
    Write-Host ""
} else {
    Write-Host "[SKIP] Local tests skipped" -ForegroundColor DarkYellow
    Write-Host ""
}

# Step 5: Manual Actions Reminder
Write-Host "[5/5] Manual actions required..." -ForegroundColor Yellow
Write-Host ""
Write-Host "⚠️  IMPORTANT - You must do these manually:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. ✅ Production Backup (REQUIRED before deployment):" -ForegroundColor Cyan
Write-Host "   - Backup production database via phpMyAdmin" -ForegroundColor Gray
Write-Host "   - Backup production wp-content/ folder" -ForegroundColor Gray
Write-Host "   - Save backups in a safe location" -ForegroundColor Gray
Write-Host ""
Write-Host "2. ✅ Local Testing (see docs/PRE-PRODUCTION-CHECKLIST.md):" -ForegroundColor Cyan
Write-Host "   - Test WooCommerce functionality" -ForegroundColor Gray
Write-Host "   - Test contact forms" -ForegroundColor Gray
Write-Host "   - Test all critical pages" -ForegroundColor Gray
Write-Host "   - Check for errors in browser console" -ForegroundColor Gray
Write-Host ""
Write-Host "3. ✅ Review deployment plan:" -ForegroundColor Cyan
Write-Host "   - See: docs/PRODUCTION-DEPLOYMENT-PLAN.md" -ForegroundColor Gray
Write-Host ""
Write-Host ""

# Summary
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Summary" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if ($allStepsPassed -and $failedSteps.Count -eq 0) {
    Write-Host "[SUCCESS] All automated steps completed!" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Some steps had issues:" -ForegroundColor Yellow
    foreach ($step in $failedSteps) {
        Write-Host "  - $step" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Complete manual actions above" -ForegroundColor Gray
Write-Host "2. Review: PRE-PRODUCTION-ACTIONS.md" -ForegroundColor Gray
Write-Host "3. Review: docs/PRE-PRODUCTION-CHECKLIST.md" -ForegroundColor Gray
Write-Host "4. When ready, follow: docs/PRODUCTION-DEPLOYMENT-PLAN.md" -ForegroundColor Gray
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Press any key to close this window..." -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
try {
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
} catch {
    # If ReadKey fails, use pause command
    Write-Host ""
    Write-Host "Press Enter to continue..."
    Read-Host
}


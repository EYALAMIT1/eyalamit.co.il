# Simple wrapper to run comprehensive checks
# עטיפה פשוטה להרצת בדיקות מקיפות

$ErrorActionPreference = "Continue"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Refresh PATH
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Get script directory
$scriptDir = $PSScriptRoot
if (-not $scriptDir) {
    $scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
}

Write-Host "Working directory: $scriptDir" -ForegroundColor Gray

# Find Python
$pythonCmd = $null
$pythonPaths = @(
    "python",
    "python3",
    "py",
    "C:\Users\USER\AppData\Local\Programs\Python\Python312\python.exe"
)

foreach ($pythonPath in $pythonPaths) {
    try {
        if (Test-Path $pythonPath) {
            $result = & $pythonPath --version 2>&1
            if ($LASTEXITCODE -eq 0) {
                $pythonCmd = $pythonPath
                break
            }
        } else {
            $result = & $pythonPath --version 2>&1
            if ($LASTEXITCODE -eq 0) {
                $pythonCmd = $pythonPath
                break
            }
        }
    } catch {
        continue
    }
}

if (-not $pythonCmd) {
    Write-Host "❌ Python לא נמצא!" -ForegroundColor Red
    Write-Host "הרץ: QUICK-INSTALL-PYTHON.bat" -ForegroundColor Yellow
    Read-Host "לחץ Enter כדי לסיים"
    exit 1
}

Write-Host "✅ Python נמצא: $pythonCmd" -ForegroundColor Green
& $pythonCmd --version

# Change to script directory
Set-Location $scriptDir

# Check for requests module
Write-Host ""
Write-Host "בודק חבילות Python..." -ForegroundColor Cyan
& $pythonCmd -c "import requests" 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "מתקין requests..." -ForegroundColor Yellow
    & $pythonCmd -m pip install requests --quiet
}

# Run the script
Write-Host ""
Write-Host "מתחיל בדיקות..." -ForegroundColor Cyan
Write-Host ""

& $pythonCmd comprehensive-site-check.py

Read-Host "`nלחץ Enter כדי לסיים"


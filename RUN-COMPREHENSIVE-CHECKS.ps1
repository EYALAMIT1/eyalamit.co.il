# PowerShell script for running comprehensive checks
# תסריט PowerShell להרצת בדיקות מקיפות

$ErrorActionPreference = "Continue"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "בדיקות מקיף לפני ייצור" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Try to find Python
$pythonCmd = $null

# Try standard python command
try {
    $result = & python --version 2>&1
    if ($LASTEXITCODE -eq 0) {
        $pythonCmd = "python"
    }
} catch {}

# Try python3
if (-not $pythonCmd) {
    try {
        $result = & python3 --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            $pythonCmd = "python3"
        }
    } catch {}
}

# Try py launcher
if (-not $pythonCmd) {
    try {
        $result = & py --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            $pythonCmd = "py"
        }
    } catch {}
}

# Try to find Python in common locations
if (-not $pythonCmd) {
    $pythonPaths = @(
        "C:\Python3*\python.exe",
        "C:\Program Files\Python3*\python.exe",
        "$env:LOCALAPPDATA\Programs\Python\Python3*\python.exe",
        "$env:PROGRAMFILES\Python3*\python.exe"
    )
    
    foreach ($pathPattern in $pythonPaths) {
        $found = Get-ChildItem -Path $pathPattern -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($found) {
            $pythonCmd = $found.FullName
            Write-Host "✅ מצא Python ב: $pythonCmd" -ForegroundColor Green
            break
        }
    }
}

# Check if Docker is available and has Python
if (-not $pythonCmd) {
    Write-Host "⚠️  Python לא נמצא במערכת המקומית" -ForegroundColor Yellow
    Write-Host "בודק אם אפשר להשתמש ב-Docker..." -ForegroundColor Yellow
    
    try {
        $dockerCheck = & docker --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Docker נמצא - אפשר להריץ Python דרך Docker" -ForegroundColor Green
            Write-Host ""
            Write-Host "אפשרויות:" -ForegroundColor Cyan
            Write-Host "1. התקן Python: https://www.python.org/downloads/" -ForegroundColor White
            Write-Host "2. הרץ דרך Docker (יידרש שינוי קטן בסקריפט)" -ForegroundColor White
            Write-Host ""
            Write-Host "להתקנה מהירה של Python דרך winget:" -ForegroundColor Cyan
            Write-Host "  winget install Python.Python.3.12" -ForegroundColor White
            Write-Host ""
            Write-Host "או דרך Microsoft Store:" -ForegroundColor Cyan
            Write-Host "  - פתח Microsoft Store" -ForegroundColor White
            Write-Host "  - חפש 'Python 3.12'" -ForegroundColor White
            Write-Host "  - לחץ התקן" -ForegroundColor White
        }
    } catch {}
    
    if (-not $pythonCmd) {
        Write-Host "❌ Python לא נמצא!" -ForegroundColor Red
        Write-Host ""
        Write-Host "אנא התקן Python 3.11 או 3.12 מ:" -ForegroundColor Yellow
        Write-Host "  https://www.python.org/downloads/" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "חשוב: בעת ההתקנה, סמן ✓ 'Add Python to PATH'" -ForegroundColor Yellow
        Read-Host "לחץ Enter כדי לסיים"
        exit 1
    }
}

Write-Host "✅ Python נמצא: $pythonCmd" -ForegroundColor Green
& $pythonCmd --version
Write-Host ""

# Check if requests module is installed
Write-Host "בודק חבילות Python נדרשות..." -ForegroundColor Cyan
try {
    & $pythonCmd -c "import requests" 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "requests not found"
    }
    Write-Host "✅ כל החבילות הנדרשות מותקנות" -ForegroundColor Green
} catch {
    Write-Host "⚠️  החבילה 'requests' לא מותקנת" -ForegroundColor Yellow
    Write-Host "מתקין את החבילה 'requests'..." -ForegroundColor Cyan
    & $pythonCmd -m pip install requests --quiet
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ שגיאה בהתקנת חבילת requests" -ForegroundColor Red
        Write-Host "נסה להריץ ידנית: $pythonCmd -m pip install requests" -ForegroundColor Yellow
        Read-Host "לחץ Enter כדי לסיים"
        exit 1
    }
    Write-Host "✅ החבילה 'requests' הותקנה בהצלחה" -ForegroundColor Green
}

Write-Host ""
Write-Host "מתחיל בדיקות מקיפות..." -ForegroundColor Cyan
Write-Host ""

# Get script directory and run from there
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$scriptPath = Join-Path $scriptDir "comprehensive-site-check.py"

# Change to script directory to avoid path issues
Push-Location $scriptDir
try {
    # Run the comprehensive check script
    & $pythonCmd comprehensive-site-check.py
    $exitCode = $LASTEXITCODE
} finally {
    Pop-Location
}

if ($exitCode -ne 0) {
    exit $exitCode
}

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "⚠️  הבדיקות הסתיימו עם שגיאות" -ForegroundColor Yellow
} else {
    Write-Host ""
    Write-Host "✅ הבדיקות הושלמו בהצלחה" -ForegroundColor Green
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "פתח את קובץ הדוח (COMPREHENSIVE-CHECK-REPORT-*.md או .html) לפרטים נוספים" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Read-Host "לחץ Enter כדי לסיים"


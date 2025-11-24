# Check current errors and save to file
[CmdletBinding()]
param(
    [string]$SiteDir,
    [string]$OutputFile = "check-errors-output.txt"
)

$ErrorActionPreference = "Continue"

if (-not $SiteDir) {
    $possibleSiteDirs = Get-ChildItem -Path "." -Directory | Where-Object { $_.Name -match "eyalamit\.co\.il" }
    if ($possibleSiteDirs.Count -eq 1) {
        $SiteDir = $possibleSiteDirs[0].FullName
    } else {
        Write-Host "[ERROR] Could not auto-detect site directory" -ForegroundColor Red
        exit 1
    }
}

$outputPath = Join-Path (Get-Location) $OutputFile

Write-Host ""
Write-Host "=== Checking Current Errors ===" -ForegroundColor Cyan
Write-Host ""

@"
========================================
בדיקת שגיאות נוכחיות
========================================
תאריך: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
תיקיית אתר: $SiteDir

"@ | Out-File -FilePath $outputPath -Encoding UTF8

# 1. Check WordPress logs
Write-Host "[1] Checking WordPress logs..." -ForegroundColor Yellow
"[1] בודק לוגים אחרונים של WordPress..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
try {
    $logs = docker compose --env-file env.local logs wordpress --tail 30 2>&1
    $errorLogs = $logs | Select-String -Pattern "error|fatal|parse|warning" -CaseSensitive:$false
    if ($errorLogs) {
        Write-Host "  Found errors/warnings:" -ForegroundColor Red
        $errorLogs | ForEach-Object {
            Write-Host "    $_" -ForegroundColor Red
            "    $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
        }
    } else {
        Write-Host "  No recent errors found" -ForegroundColor Green
        "  No recent errors found" | Out-File -FilePath $outputPath -Append -Encoding UTF8
    }
} catch {
    Write-Host "  [ERROR] Failed to check logs: $_" -ForegroundColor Red
    "  [ERROR] Failed to check logs: $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

Write-Host ""
"" | Out-File -FilePath $outputPath -Append -Encoding UTF8

# 2. Try to load WordPress
Write-Host "[2] Testing WordPress load..." -ForegroundColor Yellow
"[2] מנסה לטעון WordPress..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
try {
    $wpVersion = docker compose --env-file env.local run --rm wpcli wp core version 2>&1
    $cleanOutput = $wpVersion | Where-Object { $_ -notmatch "Container|Running|Waiting|Healthy" }
    if ($cleanOutput) {
        Write-Host "  WordPress Version: $($cleanOutput -join ' ')" -ForegroundColor Green
        "  WordPress Version: $($cleanOutput -join ' ')" | Out-File -FilePath $outputPath -Append -Encoding UTF8
    } else {
        Write-Host "  [ERROR] Could not get WordPress version" -ForegroundColor Red
        "  [ERROR] Could not get WordPress version" | Out-File -FilePath $outputPath -Append -Encoding UTF8
        $wpVersion | Out-File -FilePath $outputPath -Append -Encoding UTF8
    }
} catch {
    Write-Host "  [ERROR] Failed to load WordPress: $_" -ForegroundColor Red
    "  [ERROR] Failed to load WordPress: $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

Write-Host ""
"" | Out-File -FilePath $outputPath -Append -Encoding UTF8

# 3. Check active plugins
Write-Host "[3] Checking active plugins..." -ForegroundColor Yellow
"[3] בודק תוספים פעילים..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
try {
    $plugins = docker compose --env-file env.local run --rm wpcli wp plugin list --status=active --format=table 2>&1
    $cleanPlugins = $plugins | Where-Object { $_ -notmatch "Container|Running|Waiting|Healthy" }
    if ($cleanPlugins) {
        Write-Host $cleanPlugins
        $cleanPlugins | Out-File -FilePath $outputPath -Append -Encoding UTF8
    } else {
        Write-Host "  [ERROR] Could not list plugins" -ForegroundColor Red
        "  [ERROR] Could not list plugins" | Out-File -FilePath $outputPath -Append -Encoding UTF8
        $plugins | Out-File -FilePath $outputPath -Append -Encoding UTF8
    }
} catch {
    Write-Host "  [ERROR] Failed to list plugins: $_" -ForegroundColor Red
    "  [ERROR] Failed to list plugins: $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

@"

========================================
סיום
========================================

[INFO] הפלט נשמר בקובץ: $outputPath

"@ | Out-File -FilePath $outputPath -Append -Encoding UTF8

Write-Host ""
Write-Host "=== Done ===" -ForegroundColor Cyan
Write-Host "[INFO] Output saved to: $outputPath" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press any key to open the output file..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

if (Test-Path $outputPath) {
    notepad $outputPath
}



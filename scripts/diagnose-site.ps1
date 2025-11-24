# ========================================
# Diagnose site errors
# ========================================

[CmdletBinding()]
param(
    [string]$SiteDir,
    [string]$OutputFile = "diagnose-output.txt"
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
Write-Host "=== Diagnosing Site Issues ===" -ForegroundColor Cyan
Write-Host ""

@"
========================================
אבחון שגיאות האתר
========================================
תאריך: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
תיקיית אתר: $SiteDir

"@ | Out-File -FilePath $outputPath -Encoding UTF8

# 1. Check WordPress core version
Write-Host "[1/6] Checking WordPress core version..." -ForegroundColor Yellow
"[1/6] Checking WordPress core version..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
try {
    $wpVersion = docker compose --env-file env.local run --rm wpcli wp core version 2>&1
    Write-Host "  WordPress Version: $wpVersion"
    "  WordPress Version: $wpVersion" | Out-File -FilePath $outputPath -Append -Encoding UTF8
} catch {
    Write-Host "  [ERROR] Failed to get WordPress version: $_" -ForegroundColor Red
    "  [ERROR] Failed to get WordPress version: $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

Write-Host ""
"" | Out-File -FilePath $outputPath -Append -Encoding UTF8

# 2. Check active plugins
Write-Host "[2/6] Checking active plugins..." -ForegroundColor Yellow
"[2/6] Checking active plugins..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
try {
    $plugins = docker compose --env-file env.local run --rm wpcli wp plugin list --status=active 2>&1
    Write-Host $plugins
    $plugins | Out-File -FilePath $outputPath -Append -Encoding UTF8
} catch {
    Write-Host "  [ERROR] Failed to list plugins: $_" -ForegroundColor Red
    "  [ERROR] Failed to list plugins: $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

Write-Host ""
"" | Out-File -FilePath $outputPath -Append -Encoding UTF8

# 3. Check WordPress logs
Write-Host "[3/6] Checking WordPress logs..." -ForegroundColor Yellow
"[3/6] Checking WordPress logs..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
try {
    $logs = docker compose --env-file env.local logs wordpress --tail 50 2>&1
    $errorLogs = $logs | Select-String -Pattern "error|fatal|warning" -CaseSensitive:$false
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

# 4. Check PHP errors
Write-Host "[4/6] Testing PHP syntax..." -ForegroundColor Yellow
"[4/6] Testing PHP syntax..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
try {
    $phpTest = docker compose --env-file env.local exec wordpress php -l /var/www/html/wp-config.php 2>&1
    Write-Host $phpTest
    $phpTest | Out-File -FilePath $outputPath -Append -Encoding UTF8
} catch {
    Write-Host "  [ERROR] Failed to check PHP syntax: $_" -ForegroundColor Red
    "  [ERROR] Failed to check PHP syntax: $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

Write-Host ""
"" | Out-File -FilePath $outputPath -Append -Encoding UTF8

# 5. Check for other problematic plugins
Write-Host "[5/6] Scanning for other problematic plugins..." -ForegroundColor Yellow
"[5/6] Scanning for other problematic plugins..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
$pluginsDir = Join-Path $SiteDir "wp-content\plugins"
$activePlugins = Get-ChildItem -Path $pluginsDir -Directory | Where-Object { 
    $_.Name -notmatch "\.disabled$" -and 
    $_.Name -ne "index.php" 
}

$problematicFound = $false
foreach ($plugin in $activePlugins) {
    $phpFiles = Get-ChildItem -Path $plugin.FullName -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 10
    
    foreach ($file in $phpFiles) {
        try {
            $content = Get-Content -Path $file.FullName -Raw -ErrorAction SilentlyContinue
            if ($content -match "create_function\s*\(") {
                Write-Host "  [WARNING] $($plugin.Name) uses create_function() in $($file.Name)" -ForegroundColor Yellow
                "  [WARNING] $($plugin.Name) uses create_function() in $($file.Name)" | Out-File -FilePath $outputPath -Append -Encoding UTF8
                $problematicFound = $true
                break
            }
        } catch {
            # Skip
        }
    }
}

if (-not $problematicFound) {
    Write-Host "  No other problematic plugins found" -ForegroundColor Green
    "  No other problematic plugins found" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

Write-Host ""
"" | Out-File -FilePath $outputPath -Append -Encoding UTF8

# 6. Try to access WordPress
Write-Host "[6/6] Testing WordPress access..." -ForegroundColor Yellow
"[6/6] Testing WordPress access..." | Out-File -FilePath $outputPath -Append -Encoding UTF8
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8080" -UseBasicParsing -TimeoutSec 5 -ErrorAction SilentlyContinue
    Write-Host "  Status Code: $($response.StatusCode)" -ForegroundColor Green
    "  Status Code: $($response.StatusCode)" | Out-File -FilePath $outputPath -Append -Encoding UTF8
    
    if ($response.Content -match "critical error|fatal error|error on this website") {
        Write-Host "  [ERROR] Site shows error page!" -ForegroundColor Red
        "  [ERROR] Site shows error page!" | Out-File -FilePath $outputPath -Append -Encoding UTF8
    }
} catch {
    Write-Host "  [ERROR] Could not access site: $_" -ForegroundColor Red
    "  [ERROR] Could not access site: $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

@"

========================================
[SUCCESS] אבחון הושלם
========================================

[INFO] הפלט נשמר בקובץ: $outputPath

"@ | Out-File -FilePath $outputPath -Append -Encoding UTF8

Write-Host ""
Write-Host "=== Diagnosis Complete ===" -ForegroundColor Cyan
Write-Host "[INFO] Output saved to: $outputPath" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press any key to open the output file..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

if (Test-Path $outputPath) {
    notepad $outputPath
}



# Test WordPress loading
[CmdletBinding()]
param(
    [string]$SiteDir
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

Write-Host ""
Write-Host "=== Testing WordPress Load ===" -ForegroundColor Cyan
Write-Host ""

# Try to load WordPress via WP-CLI
Write-Host "[INFO] Testing WordPress core version..." -ForegroundColor Yellow
$result = docker compose --env-file env.local run --rm wpcli wp core version 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] WordPress loads successfully!" -ForegroundColor Green
    Write-Host $result
} else {
    Write-Host "[ERROR] WordPress failed to load!" -ForegroundColor Red
    Write-Host $result
    Write-Host ""
    Write-Host "[INFO] Checking for problematic plugins..." -ForegroundColor Yellow
    
    # Check for plugins using create_function
    $pluginsDir = Join-Path $SiteDir "wp-content\plugins"
    $activePlugins = Get-ChildItem -Path $pluginsDir -Directory | Where-Object { 
        $_.Name -notmatch "\.disabled$" -and 
        $_.Name -ne "index.php" 
    }
    
    foreach ($plugin in $activePlugins) {
        $mainFile = Join-Path $plugin.FullName "$($plugin.Name).php"
        if (-not (Test-Path $mainFile)) {
            # Try to find main PHP file
            $phpFiles = Get-ChildItem -Path $plugin.FullName -Filter "*.php" -Depth 1 | Select-Object -First 1
            if ($phpFiles) {
                $mainFile = $phpFiles[0].FullName
            }
        }
        
        if (Test-Path $mainFile) {
            try {
                $content = Get-Content -Path $mainFile -Raw -ErrorAction SilentlyContinue
                if ($content -match "create_function\s*\(") {
                    Write-Host "[WARNING] $($plugin.Name) uses create_function()" -ForegroundColor Yellow
                }
            } catch {
                # Skip
            }
        }
    }
}

Write-Host ""



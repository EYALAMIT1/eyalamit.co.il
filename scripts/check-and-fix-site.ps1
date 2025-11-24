# ========================================
# Check site status and fix issues
# ========================================

[CmdletBinding()]
param(
    [string]$SiteDir
)

$ErrorActionPreference = "Continue"

# Auto-detect SiteDir
if (-not $SiteDir) {
    $possibleSiteDirs = Get-ChildItem -Path "." -Directory | Where-Object { $_.Name -match "eyalamit\.co\.il" }
    if ($possibleSiteDirs.Count -eq 1) {
        $SiteDir = $possibleSiteDirs[0].FullName
    } else {
        Write-Host "[ERROR] Could not auto-detect site directory" -ForegroundColor Red
        exit 1
    }
}

$pluginsDir = Join-Path $SiteDir "wp-content\plugins"

Write-Host ""
Write-Host "=== Checking Plugin Status ===" -ForegroundColor Cyan
Write-Host ""

# Check LayerSlider
$layerSlider = Join-Path $pluginsDir "LayerSlider"
$layerSliderDisabled = Join-Path $pluginsDir "LayerSlider.disabled"
if (Test-Path $layerSlider) {
    Write-Host "[FOUND] LayerSlider is still active!" -ForegroundColor Red
    Write-Host "[ACTION] Disabling LayerSlider..." -ForegroundColor Yellow
    try {
        Rename-Item -Path $layerSlider -NewName "LayerSlider.disabled" -Force
        Write-Host "[OK] LayerSlider disabled" -ForegroundColor Green
    } catch {
        Write-Host "[ERROR] Failed to disable LayerSlider: $_" -ForegroundColor Red
    }
} elseif (Test-Path $layerSliderDisabled) {
    Write-Host "[OK] LayerSlider is disabled" -ForegroundColor Green
} else {
    Write-Host "[INFO] LayerSlider not found" -ForegroundColor DarkYellow
}

# Check RevSlider
$revSlider = Join-Path $pluginsDir "revslider"
$revSliderDisabled = Join-Path $pluginsDir "revslider.disabled"
if (Test-Path $revSlider) {
    Write-Host "[FOUND] RevSlider is still active!" -ForegroundColor Red
    Write-Host "[ACTION] Disabling RevSlider..." -ForegroundColor Yellow
    try {
        Rename-Item -Path $revSlider -NewName "revslider.disabled" -Force
        Write-Host "[OK] RevSlider disabled" -ForegroundColor Green
    } catch {
        Write-Host "[ERROR] Failed to disable RevSlider: $_" -ForegroundColor Red
    }
} elseif (Test-Path $revSliderDisabled) {
    Write-Host "[OK] RevSlider is disabled" -ForegroundColor Green
} else {
    Write-Host "[INFO] RevSlider not found" -ForegroundColor DarkYellow
}

Write-Host ""
Write-Host "=== Checking for other problematic plugins ===" -ForegroundColor Cyan
Write-Host ""

# Check for other plugins that might use create_function
$allPlugins = Get-ChildItem -Path $pluginsDir -Directory | Where-Object { 
    $_.Name -notmatch "\.disabled$" -and 
    $_.Name -ne "index.php" 
}

foreach ($plugin in $allPlugins) {
    $pluginPath = $plugin.FullName
    $phpFiles = Get-ChildItem -Path $pluginPath -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 5
    
    $hasCreateFunction = $false
    foreach ($file in $phpFiles) {
        try {
            $content = Get-Content -Path $file.FullName -Raw -ErrorAction SilentlyContinue
            if ($content -match "create_function\s*\(") {
                $hasCreateFunction = $true
                break
            }
        } catch {
            # Skip files that can't be read
        }
    }
    
    if ($hasCreateFunction) {
        Write-Host "[WARNING] $($plugin.Name) may use create_function()" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "=== Done ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Please refresh your browser at: http://localhost:8080" -ForegroundColor Yellow
Write-Host ""



# ========================================
# Disable problematic plugins incompatible with PHP 8.2
# ========================================

[CmdletBinding()]
param(
    [string]$SiteDir  # Auto-detected if not provided
)

$ErrorActionPreference = "Stop"

# Auto-detect SiteDir if not provided
if (-not $SiteDir) {
    $possibleSiteDirs = Get-ChildItem -Path "." -Directory | Where-Object { $_.Name -match "eyalamit\.co\.il" }
    if ($possibleSiteDirs.Count -eq 1) {
        $SiteDir = $possibleSiteDirs[0].FullName
        Write-Host "[INFO] Auto-detected site directory: $SiteDir" -ForegroundColor Green
    } elseif ($possibleSiteDirs.Count -gt 1) {
        throw "Multiple potential site directories found. Please specify SiteDir parameter."
    } else {
        throw "Could not auto-detect site directory. Please specify SiteDir parameter."
    }
}

# List of problematic plugins that use deprecated PHP functions
$problematicPlugins = @(
    "LayerSlider",
    "revslider",
    "timetable",
    "woocommerce"
)

$pluginsDir = Join-Path $SiteDir "wp-content\plugins"
$disabledCount = 0
$skippedCount = 0

Write-Host ""
Write-Host "=== Disabling Problematic Plugins (PHP 8.2 Incompatible) ===" -ForegroundColor Cyan
Write-Host ""

foreach ($pluginName in $problematicPlugins) {
    $pluginPath = Join-Path $pluginsDir $pluginName
    $disabledPath = Join-Path $pluginsDir "$pluginName.disabled"
    
    Write-Host "[INFO] Checking plugin: $pluginName" -ForegroundColor Yellow
    
    if (Test-Path $pluginPath) {
        Write-Host "  [FOUND] Plugin directory exists" -ForegroundColor Yellow
        
        # Check if already disabled
        if (Test-Path $disabledPath) {
            Write-Host "  [SKIP] Already disabled (found $pluginName.disabled)" -ForegroundColor DarkYellow
            $skippedCount++
            continue
        }
        
        Write-Host "  [ACTION] Renaming to disable..." -ForegroundColor Yellow
        try {
            Rename-Item -Path $pluginPath -NewName "$pluginName.disabled" -Force
            
            if (Test-Path $disabledPath) {
                Write-Host "  [OK] $pluginName has been disabled!" -ForegroundColor Green
                $disabledCount++
            } else {
                Write-Host "  [ERROR] Failed to rename $pluginName" -ForegroundColor Red
            }
        } catch {
            Write-Host "  [ERROR] Failed to disable $pluginName : $_" -ForegroundColor Red
        }
    } elseif (Test-Path $disabledPath) {
        Write-Host "  [INFO] Already disabled" -ForegroundColor Green
        $skippedCount++
    } else {
        Write-Host "  [INFO] Plugin not found (may have been removed)" -ForegroundColor DarkYellow
        $skippedCount++
    }
    
    Write-Host ""
}

Write-Host "=== Summary ===" -ForegroundColor Cyan
Write-Host "  Disabled: $disabledCount plugin(s)" -ForegroundColor Green
Write-Host "  Skipped:  $skippedCount plugin(s)" -ForegroundColor DarkYellow
Write-Host ""

if ($disabledCount -gt 0) {
    Write-Host "[SUCCESS] Problematic plugins have been disabled!" -ForegroundColor Green
    Write-Host "[INFO] Please refresh your browser at: http://localhost:8080" -ForegroundColor Yellow
} else {
    Write-Host "[INFO] No plugins needed to be disabled (all were already disabled or not found)." -ForegroundColor Yellow
}

Write-Host ""


# ========================================
# Disable LayerSlider plugin by renaming directory
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

$pluginPath = Join-Path $SiteDir "wp-content\plugins\LayerSlider"
$disabledPath = Join-Path $SiteDir "wp-content\plugins\LayerSlider.disabled"

Write-Host ""
Write-Host "=== Disabling LayerSlider Plugin ===" -ForegroundColor Cyan
Write-Host ""

if (Test-Path $pluginPath) {
    Write-Host "[INFO] Found LayerSlider plugin at: $pluginPath" -ForegroundColor Yellow
    
    # Check if already disabled
    if (Test-Path $disabledPath) {
        Write-Host "[WARNING] LayerSlider.disabled already exists. Removing old disabled version..." -ForegroundColor Yellow
        Remove-Item -Path $disabledPath -Recurse -Force
    }
    
    Write-Host "[INFO] Renaming plugin directory to disable it..." -ForegroundColor Yellow
    Rename-Item -Path $pluginPath -NewName "LayerSlider.disabled" -Force
    
    if (Test-Path $disabledPath) {
        Write-Host "[OK] LayerSlider has been disabled successfully!" -ForegroundColor Green
        Write-Host "[INFO] Plugin directory renamed to: LayerSlider.disabled" -ForegroundColor Green
    } else {
        throw "Failed to rename plugin directory"
    }
} elseif (Test-Path $disabledPath) {
    Write-Host "[INFO] LayerSlider is already disabled." -ForegroundColor Green
} else {
    Write-Host "[WARNING] LayerSlider plugin directory not found at: $pluginPath" -ForegroundColor Yellow
    Write-Host "[INFO] Plugin may have been removed or never installed." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Done ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Please refresh your browser at: http://localhost:8080" -ForegroundColor Yellow
Write-Host ""



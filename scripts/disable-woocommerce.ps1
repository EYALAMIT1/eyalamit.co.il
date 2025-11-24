# Disable WooCommerce temporarily
[CmdletBinding()]
param(
    [string]$SiteDir
)

$ErrorActionPreference = "Stop"

if (-not $SiteDir) {
    $possibleSiteDirs = Get-ChildItem -Path "." -Directory | Where-Object { $_.Name -match "eyalamit\.co\.il" }
    if ($possibleSiteDirs.Count -eq 1) {
        $SiteDir = $possibleSiteDirs[0].FullName
    } else {
        throw "Could not auto-detect site directory"
    }
}

$pluginPath = Join-Path $SiteDir "wp-content\plugins\woocommerce"
$disabledPath = Join-Path $SiteDir "wp-content\plugins\woocommerce.disabled"

Write-Host ""
Write-Host "=== Disabling WooCommerce (PHP 8.2 Incompatible) ===" -ForegroundColor Cyan
Write-Host ""

if (Test-Path $pluginPath) {
    Write-Host "[INFO] Found WooCommerce plugin" -ForegroundColor Yellow
    if (Test-Path $disabledPath) {
        Remove-Item -Path $disabledPath -Recurse -Force
    }
    Rename-Item -Path $pluginPath -NewName "woocommerce.disabled" -Force
    Write-Host "[OK] WooCommerce disabled!" -ForegroundColor Green
    Write-Host "[WARNING] This will disable your online store functionality!" -ForegroundColor Yellow
    Write-Host "[INFO] You need to update WooCommerce to a version compatible with PHP 8.2" -ForegroundColor Yellow
} elseif (Test-Path $disabledPath) {
    Write-Host "[INFO] WooCommerce already disabled" -ForegroundColor Green
} else {
    Write-Host "[INFO] WooCommerce not found" -ForegroundColor DarkYellow
}

Write-Host ""



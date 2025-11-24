# Disable timetable plugin
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

$pluginPath = Join-Path $SiteDir "wp-content\plugins\timetable"
$disabledPath = Join-Path $SiteDir "wp-content\plugins\timetable.disabled"

Write-Host ""
Write-Host "=== Disabling Timetable Plugin ===" -ForegroundColor Cyan
Write-Host ""

if (Test-Path $pluginPath) {
    Write-Host "[INFO] Found timetable plugin" -ForegroundColor Yellow
    if (Test-Path $disabledPath) {
        Remove-Item -Path $disabledPath -Recurse -Force
    }
    Rename-Item -Path $pluginPath -NewName "timetable.disabled" -Force
    Write-Host "[OK] Timetable disabled!" -ForegroundColor Green
} elseif (Test-Path $disabledPath) {
    Write-Host "[INFO] Timetable already disabled" -ForegroundColor Green
} else {
    Write-Host "[INFO] Timetable not found" -ForegroundColor DarkYellow
}

Write-Host ""



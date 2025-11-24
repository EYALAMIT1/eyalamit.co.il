# Find all PHP 8.2 compatibility issues
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

$outputFile = "php8-issues.txt"
$issues = @()

Write-Host ""
Write-Host "=== Scanning for PHP 8.2 Compatibility Issues ===" -ForegroundColor Cyan
Write-Host ""

# 1. Check for create_function
Write-Host "[1] Checking for create_function()..." -ForegroundColor Yellow
$createFunctionFiles = Get-ChildItem -Path "$SiteDir\wp-content" -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | 
    Select-String -Pattern "create_function\s*\(" -List | 
    Select-Object -ExpandProperty Path -Unique

foreach ($file in $createFunctionFiles) {
    $relativePath = $file.Replace($SiteDir, "").TrimStart("\")
    if ($relativePath -notmatch "\.disabled") {
        $issues += "create_function: $relativePath"
        Write-Host "  [FOUND] $relativePath" -ForegroundColor Red
    }
}

# 2. Check for curly braces array access
Write-Host "[2] Checking for curly braces array access..." -ForegroundColor Yellow
$curlyBracesFiles = Get-ChildItem -Path "$SiteDir\wp-content" -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | 
    Select-String -Pattern '\$[a-zA-Z_][a-zA-Z0-9_]*\{[0-9]+\}' -List | 
    Select-Object -ExpandProperty Path -Unique

foreach ($file in $curlyBracesFiles) {
    $relativePath = $file.Replace($SiteDir, "").TrimStart("\")
    if ($relativePath -notmatch "\.disabled") {
        $issues += "curly_braces: $relativePath"
        Write-Host "  [FOUND] $relativePath" -ForegroundColor Red
    }
}

# 3. Check for unparenthesized ternary
Write-Host "[3] Checking for unparenthesized ternary..." -ForegroundColor Yellow
$ternaryFiles = Get-ChildItem -Path "$SiteDir\wp-content" -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | 
    Select-String -Pattern '\? .* \? .* :' -List | 
    Select-Object -ExpandProperty Path -Unique

foreach ($file in $ternaryFiles) {
    $relativePath = $file.Replace($SiteDir, "").TrimStart("\")
    if ($relativePath -notmatch "\.disabled") {
        $issues += "ternary: $relativePath"
        Write-Host "  [FOUND] $relativePath" -ForegroundColor Red
    }
}

# 4. Check for backslash before space
Write-Host "[4] Checking for backslash before space..." -ForegroundColor Yellow
$backslashFiles = Get-ChildItem -Path "$SiteDir\wp-content\plugins" -Filter "*.php" -Recurse -ErrorAction SilentlyContinue | 
    Select-String -Pattern '\\ [A-Z]' -List | 
    Select-Object -ExpandProperty Path -Unique

foreach ($file in $backslashFiles) {
    $relativePath = $file.Replace($SiteDir, "").TrimStart("\")
    if ($relativePath -notmatch "\.disabled") {
        $issues += "backslash_space: $relativePath"
        Write-Host "  [FOUND] $relativePath" -ForegroundColor Red
    }
}

Write-Host ""
if ($issues.Count -eq 0) {
    Write-Host "[SUCCESS] No PHP 8.2 compatibility issues found!" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Found $($issues.Count) potential issues" -ForegroundColor Yellow
    $issues | Out-File -FilePath $outputFile -Encoding UTF8
    Write-Host "[INFO] Issues saved to: $outputFile" -ForegroundColor Yellow
}

Write-Host ""



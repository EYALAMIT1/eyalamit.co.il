param(
    [string]$Version = "6.8.3",
    [string]$TargetPath,
    [switch]$SkipDownload
)

$ErrorActionPreference = "Stop"

function Write-Section($text) {
    Write-Host ""
    Write-Host "=== $text ===" -ForegroundColor Cyan
}

# Auto-detect site directory if not provided
if (-not $TargetPath) {
    $possibleDirs = Get-ChildItem -Directory | Where-Object { $_.Name -like "eyalamit.co.il*" } | Select-Object -First 1
    if ($possibleDirs) {
        $TargetPath = $possibleDirs.FullName
        Write-Host "[INFO] Auto-detected site directory: $TargetPath" -ForegroundColor Cyan
    } else {
        throw "Could not find site directory. Please specify -TargetPath"
    }
}

if (-not (Test-Path $TargetPath)) {
    throw "Target WordPress directory not found: $TargetPath"
}

$coreCache = Join-Path ".\backups" "wp-core"
New-Item -ItemType Directory -Force -Path $coreCache | Out-Null

$zipName = "wordpress-$Version.zip"
$zipPath = Join-Path $coreCache $zipName
$downloadUrl = "https://wordpress.org/$zipName"

if (-not $SkipDownload) {
    Write-Section "Downloading WordPress $Version"
    try {
        Invoke-WebRequest -Uri $downloadUrl -OutFile $zipPath -UseBasicParsing
    } catch {
        throw "Failed to download $downloadUrl - $_"
    }
}

if (-not (Test-Path $zipPath)) {
    throw "Core archive not found at $zipPath"
}

$tempExtract = Join-Path $coreCache ("wordpress-$Version-extracted")
if (Test-Path $tempExtract) {
    Remove-Item $tempExtract -Recurse -Force
}
Expand-Archive -LiteralPath $zipPath -DestinationPath $tempExtract

$sourceRoot = Join-Path $tempExtract "wordpress"
if (-not (Test-Path (Join-Path $sourceRoot "wp-admin"))) {
    throw "Extracted archive missing wp-admin"
}

Write-Section "Syncing core files"
$itemsToCopy = @(
    "wp-admin",
    "wp-includes",
    "index.php",
    "wp-activate.php",
    "wp-blog-header.php",
    "wp-comments-post.php",
    "wp-cron.php",
    "wp-links-opml.php",
    "wp-load.php",
    "wp-login.php",
    "wp-mail.php",
    "wp-config-sample.php",
    "wp-settings.php",
    "wp-signup.php",
    "wp-trackback.php",
    "xmlrpc.php",
    "readme.html",
    "license.txt"
)

foreach ($item in $itemsToCopy) {
    $src = Join-Path $sourceRoot $item
    $dst = Join-Path $TargetPath $item
    if (-not (Test-Path $src)) {
        Write-Warning "Skipping $item (not present in archive)"
        continue
    }
    if (Test-Path $dst) {
        Remove-Item $dst -Recurse -Force
    }
    Copy-Item $src -Destination $dst -Recurse -Force
    Write-Host "✓ $item"
}

Write-Section "Cleanup"
Remove-Item $tempExtract -Recurse -Force

Write-Host ""
Write-Host "WordPress core $Version synced into $TargetPath." -ForegroundColor Green
Write-Host "Run 'docker compose run --rm wpcli core update-db' after bringing containers up." -ForegroundColor Yellow


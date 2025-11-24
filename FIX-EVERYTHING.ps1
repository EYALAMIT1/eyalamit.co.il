# ========================================
# Fix Everything - WordPress Local Setup
# ========================================

$ErrorActionPreference = "Continue"
$ProgressPreference = "SilentlyContinue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Fixing Everything Automatically" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Get project root
$ProjectRoot = $PSScriptRoot
if (-not $ProjectRoot) {
    $ProjectRoot = Get-Location
}

Set-Location $ProjectRoot

# Step 1: Fix nginx.conf
Write-Host "[1/4] Fixing nginx.conf..." -ForegroundColor Green
$nginxConfPath = Join-Path $ProjectRoot "docs\nginx.conf"

$nginxConf = @"
server {
    listen 80;
    server_name localhost;
    root /var/www/html;
    index index.php index.html index.htm;
    access_log /var/log/nginx/eyalamit_access.log;
    error_log /var/log/nginx/eyalamit_error.log;
    client_max_body_size 256M;
    sendfile on;
    tcp_nopush on;
    keepalive_timeout 65;
    charset utf-8;
    source_charset utf-8;
    location / {
        try_files `$uri `$uri/ /index.php?`$args;
    }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass wordpress:9000;
        fastcgi_param SCRIPT_FILENAME `$document_root`$fastcgi_script_name;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_param HTTP_PROXY "";
        fastcgi_param HTTP_ACCEPT_CHARSET "utf-8";
    }
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        try_files `$uri `$uri/ /index.php?`$args;
        expires max;
        log_not_found off;
    }
}
"@

$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($nginxConfPath, $nginxConf, $utf8NoBom)
Write-Host "  [OK] nginx.conf fixed" -ForegroundColor Green

# Step 2: Fix wp-config.php
Write-Host ""
Write-Host "[2/4] Fixing wp-config.php..." -ForegroundColor Green
$wpConfigPath = Join-Path $ProjectRoot "eyalamit.co.il_bm1763848352dm\wp-config.php"

if (Test-Path $wpConfigPath) {
    $content = Get-Content $wpConfigPath -Raw
    
    # הסר את כל הסוגריים המיותרים אחרי define statements
    $content = $content -replace "\)\)+\);", ");"
    
    # שמור את הקובץ
    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllText($wpConfigPath, $content, $utf8NoBom)
    
    Write-Host "  [OK] wp-config.php fixed" -ForegroundColor Green
} else {
    Write-Host "  [WARNING] wp-config.php not found" -ForegroundColor Yellow
}

# Step 3: Restart containers
Write-Host ""
Write-Host "[3/4] Restarting containers..." -ForegroundColor Green

docker compose restart 2>&1 | Out-Null

if ($LASTEXITCODE -eq 0) {
    Write-Host "  [OK] Containers restarted" -ForegroundColor Green
} else {
    Write-Host "  [WARNING] Some containers may not have restarted" -ForegroundColor Yellow
}

# Step 4: Wait and verify
Write-Host ""
Write-Host "[4/4] Verifying..." -ForegroundColor Green
Start-Sleep -Seconds 5

# Check wp-config.php syntax
$syntaxCheck = docker exec newwebsiteainov2025take2-wordpress-1 php -l /var/www/html/wp-config.php 2>&1
if ($syntaxCheck -match "No syntax errors") {
    Write-Host "  [OK] wp-config.php syntax is valid" -ForegroundColor Green
} else {
    Write-Host "  [WARNING] wp-config.php may still have issues" -ForegroundColor Yellow
    Write-Host "  $syntaxCheck" -ForegroundColor Gray
}

# Check containers status
Write-Host ""
Write-Host "Container Status:" -ForegroundColor Cyan
docker ps --filter "name=newwebsiteainov2025take2" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Fix Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Try opening: http://localhost:8080" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press any key to continue..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
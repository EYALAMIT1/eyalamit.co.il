# ========================================
# Setup New WordPress Project from ZIP
# ========================================

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup New WordPress Project" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Get current directory
$ProjectRoot = $PSScriptRoot
if (-not $ProjectRoot) {
    $ProjectRoot = Get-Location
}

Write-Host "[INFO] Project root: $ProjectRoot" -ForegroundColor Yellow

# Step 1: Create directory structure
Write-Host ""
Write-Host "[1/7] Creating directory structure..." -ForegroundColor Green
$docsDir = Join-Path $ProjectRoot "docs"
$backupsDir = Join-Path $ProjectRoot "backups"

if (-not (Test-Path $docsDir)) {
    New-Item -ItemType Directory -Path $docsDir -Force | Out-Null
    Write-Host "  Created: docs/" -ForegroundColor Gray
}

if (-not (Test-Path $backupsDir)) {
    New-Item -ItemType Directory -Path $backupsDir -Force | Out-Null
    Write-Host "  Created: backups/" -ForegroundColor Gray
}

# Step 2: Check 7-Zip installation
Write-Host ""
Write-Host "[2/8] Checking 7-Zip installation..." -ForegroundColor Green
$7zipPath = $null

# Check if 7z is in PATH
if (Get-Command 7z -ErrorAction SilentlyContinue) {
    $7zipPath = "7z"
    Write-Host "  Found 7-Zip in PATH" -ForegroundColor Gray
} else {
    # Check common installation paths
    $possiblePaths = @(
        "C:\Program Files\7-Zip\7z.exe",
        "C:\Program Files (x86)\7-Zip\7z.exe"
    )
    
    foreach ($path in $possiblePaths) {
        if (Test-Path $path) {
            $7zipPath = $path
            Write-Host "  Found 7-Zip at: $path" -ForegroundColor Gray
            break
        }
    }
}

if (-not $7zipPath) {
    Write-Host "  [ERROR] 7-Zip not found. Please install 7-Zip from https://www.7-zip.org/" -ForegroundColor Red
    exit 1
}

# Step 3: Check ZIP file exists
Write-Host ""
Write-Host "[3/8] Checking ZIP file..." -ForegroundColor Green
$zipFile = Join-Path $ProjectRoot "eyalamit.co.il_bm1763848352dm.zip"
if (-not (Test-Path $zipFile)) {
    Write-Host "  [ERROR] ZIP file not found: $zipFile" -ForegroundColor Red
    exit 1
}

$zipSize = (Get-Item $zipFile).Length / 1MB
Write-Host "  Found: $(Split-Path $zipFile -Leaf) ($([math]::Round($zipSize, 2)) MB)" -ForegroundColor Gray

# Check available disk space (need at least 2x ZIP size)
$drive = (Get-Item $ProjectRoot).PSDrive
$freeSpaceGB = $drive.Free / 1GB
$requiredSpaceGB = ($zipSize * 2) / 1024
Write-Host "  Available disk space: $([math]::Round($freeSpaceGB, 2)) GB" -ForegroundColor Gray
if ($freeSpaceGB -lt $requiredSpaceGB) {
    Write-Host "  [WARNING] Low disk space. Recommended: $([math]::Round($requiredSpaceGB, 2)) GB" -ForegroundColor Yellow
}

# Step 4: Extract ZIP to temporary directory using 7-Zip
Write-Host ""
Write-Host "[4/8] Extracting ZIP file with 7-Zip..." -ForegroundColor Green
$tempExtractDir = Join-Path $ProjectRoot "backups\extract_temp_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
if (-not (Test-Path $tempExtractDir)) {
    New-Item -ItemType Directory -Path $tempExtractDir -Force | Out-Null
}

Write-Host "  Extracting to: $tempExtractDir" -ForegroundColor Gray
Write-Host "  This may take 5-10 minutes..." -ForegroundColor Yellow
Write-Host "  Using 7-Zip for better long filename support..." -ForegroundColor Gray

# Use 7-Zip to extract (handles long filenames better)
# 7-Zip command: 7z x archive.zip -o"destination" -y
$7zipArgs = @(
    "x",                    # Extract with full paths
    $zipFile,               # Source ZIP file
    "-o$tempExtractDir",   # Output directory (no space after -o)
    "-y",                   # Assume Yes on all queries
    "-bb0"                  # Suppress progress output (level 0)
)

try {
    $extractResult = & $7zipPath $7zipArgs 2>&1
    $exitCode = $LASTEXITCODE
    
    if ($exitCode -eq 0) {
        Write-Host "  [OK] ZIP extracted successfully with 7-Zip" -ForegroundColor Green
    } else {
        Write-Host "  [WARNING] 7-Zip returned exit code $exitCode" -ForegroundColor Yellow
        Write-Host "  Checking if extraction partially succeeded..." -ForegroundColor Yellow
        
        # Check if any files were extracted
        $extractedFiles = Get-ChildItem -Path $tempExtractDir -Recurse -ErrorAction SilentlyContinue
        if ($extractedFiles.Count -gt 0) {
            Write-Host "  Found $($extractedFiles.Count) extracted files, continuing..." -ForegroundColor Yellow
        } else {
            Write-Host "  [ERROR] No files were extracted. 7-Zip output:" -ForegroundColor Red
            Write-Host $extractResult -ForegroundColor Red
            exit 1
        }
    }
} catch {
    Write-Host "  [ERROR] 7-Zip extraction failed: $_" -ForegroundColor Red
    Write-Host "  Error details: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Step 5: Find WordPress root
Write-Host ""
Write-Host "[5/8] Finding WordPress root..." -ForegroundColor Green
$wpRoot = $null

# Search for wp-content directory
$wpContentDirs = Get-ChildItem -Path $tempExtractDir -Filter "wp-content" -Recurse -Directory -ErrorAction SilentlyContinue | Select-Object -First 1

if ($wpContentDirs) {
    $wpRoot = $wpContentDirs.Parent.FullName
    Write-Host "  Found WordPress root: $wpRoot" -ForegroundColor Gray
} else {
    # Try direct structure
    if (Test-Path (Join-Path $tempExtractDir "wp-content")) {
        $wpRoot = $tempExtractDir
        Write-Host "  WordPress root is extract directory" -ForegroundColor Gray
    } else {
        # Look for subdirectory with WordPress
        $subdirs = Get-ChildItem -Path $tempExtractDir -Directory | Where-Object { Test-Path (Join-Path $_.FullName "wp-content") }
        if ($subdirs) {
            $wpRoot = $subdirs[0].FullName
            Write-Host "  Found WordPress in subdirectory: $wpRoot" -ForegroundColor Gray
        }
    }
}

if (-not $wpRoot -or -not (Test-Path (Join-Path $wpRoot "wp-config.php"))) {
    Write-Host "  [ERROR] WordPress root not found or wp-config.php missing" -ForegroundColor Red
    exit 1
}

Write-Host "  [OK] WordPress root confirmed" -ForegroundColor Green

# Step 6: Copy files using robocopy (handles long paths)
Write-Host ""
Write-Host "[6/8] Copying WordPress files..." -ForegroundColor Green
$siteDir = Join-Path $ProjectRoot "eyalamit.co.il_bm1763848352dm"

if (Test-Path $siteDir) {
    Write-Host "  Removing existing site directory..." -ForegroundColor Yellow
    Remove-Item -Path $siteDir -Recurse -Force -ErrorAction SilentlyContinue
}

New-Item -ItemType Directory -Path $siteDir -Force | Out-Null

Write-Host "  Copying from: $wpRoot" -ForegroundColor Gray
Write-Host "  Copying to: $siteDir" -ForegroundColor Gray
Write-Host "  This may take 10-15 minutes..." -ForegroundColor Yellow

# Use robocopy for long path support
$robocopyArgs = @(
    $wpRoot,
    $siteDir,
    "/E",           # Copy subdirectories including empty ones
    "/256",         # Support paths longer than 256 characters
    "/R:3",         # Retry 3 times
    "/W:1",         # Wait 1 second between retries
    "/NFL",         # No file list
    "/NDL",         # No directory list
    "/NJH",         # No job header
    "/NJS",         # No job summary
    "/NC",          # No class
    "/NS",          # No size
    "/NP"           # No progress
)

$robocopyResult = & robocopy @robocopyArgs

# Robocopy exit codes: 0-7 = success, 8+ = error
if ($LASTEXITCODE -ge 8) {
    Write-Host "  [WARNING] Robocopy returned exit code $LASTEXITCODE" -ForegroundColor Yellow
    Write-Host "  Some files may not have been copied, but continuing..." -ForegroundColor Yellow
} else {
    Write-Host "  [OK] Files copied successfully" -ForegroundColor Green
}

# Step 7: Clean up temporary extraction directory
Write-Host ""
Write-Host "[7/8] Cleaning up temporary files..." -ForegroundColor Green
try {
    Remove-Item -Path $tempExtractDir -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "  [OK] Temporary files removed" -ForegroundColor Green
} catch {
    Write-Host "  [WARNING] Could not remove temp directory: $_" -ForegroundColor Yellow
}

# Step 8: Create configuration files
Write-Host ""
Write-Host "[8/8] Creating configuration files..." -ForegroundColor Green

# docker-compose.yml
$dockerCompose = @"
services:
  db:
    image: mariadb:10.6
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: `${DB_NAME}
      MYSQL_USER: `${DB_USER}
      MYSQL_PASSWORD: `${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: `${DB_ROOT_PASSWORD}
      TZ: Asia/Jerusalem
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5

  wordpress:
    image: wordpress:php7.4-fpm
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy
    environment:
      DB_NAME: `${DB_NAME}
      DB_USER: `${DB_USER}
      DB_PASSWORD: `${DB_PASSWORD}
      DB_HOST: `${DB_HOST:-db}
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_NAME: `${DB_NAME}
      WORDPRESS_DB_USER: `${DB_USER}
      WORDPRESS_DB_PASSWORD: `${DB_PASSWORD}
      WORDPRESS_CONFIG_EXTRA: |
        define('WP_HOME', getenv('WP_HOME') ?: 'http://localhost:8080');
        define('WP_SITEURL', getenv('WP_SITEURL') ?: 'http://localhost:8080');
    volumes:
      - ./eyalamit.co.il_bm1763848352dm:/var/www/html

  nginx:
    image: nginx:1.27-alpine
    restart: unless-stopped
    depends_on:
      - wordpress
    ports:
      - "8080:80"
    volumes:
      - ./eyalamit.co.il_bm1763848352dm:/var/www/html
      - ./docs/nginx.conf:/etc/nginx/conf.d/default.conf:ro

  phpmyadmin:
    image: phpmyadmin:5
    restart: unless-stopped
    depends_on:
      - db
    environment:
      PMA_HOST: db
      PMA_USER: `${DB_USER}
      PMA_PASSWORD: `${DB_PASSWORD}
      UPLOAD_LIMIT: 256M
    ports:
      - "8081:80"

  wpcli:
    image: wordpress:cli-php7.4
    user: "33:33"
    depends_on:
      - db
      - wordpress
    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_NAME: `${DB_NAME:-eyal_local}
      WORDPRESS_DB_USER: `${DB_USER:-eyal}
      WORDPRESS_DB_PASSWORD: `${DB_PASSWORD:-eyalpass}
      DB_NAME: `${DB_NAME:-eyal_local}
      DB_USER: `${DB_USER:-eyal}
      DB_PASSWORD: `${DB_PASSWORD:-eyalpass}
      DB_HOST: db
    volumes:
      - ./eyalamit.co.il_bm1763848352dm:/var/www/html
    working_dir: /var/www/html

volumes:
  db_data:
"@

$dockerComposePath = Join-Path $ProjectRoot "docker-compose.yml"
$dockerCompose | Out-File -FilePath $dockerComposePath -Encoding UTF8 -NoNewline
Write-Host "  Created: docker-compose.yml" -ForegroundColor Gray

# .env file
$envContent = @"
DB_NAME=eyal_local
DB_USER=eyal
DB_PASSWORD=eyalpass
DB_ROOT_PASSWORD=strong_root_pass
DB_HOST=db

WP_HOME=http://localhost:8080
WP_SITEURL=http://localhost:8080
"@

$envPath = Join-Path $ProjectRoot ".env"
$envContent | Out-File -FilePath $envPath -Encoding UTF8 -NoNewline
Write-Host "  Created: .env" -ForegroundColor Gray

# nginx.conf
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
    
    # Charset settings for Hebrew/UTF-8
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
        # Ensure UTF-8 encoding
        fastcgi_param HTTP_ACCEPT_CHARSET "utf-8";
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        try_files `$uri `$uri/ /index.php?`$args;
        expires max;
        log_not_found off;
    }
}
"@

$nginxConfPath = Join-Path $docsDir "nginx.conf"
# Save without BOM to avoid nginx errors
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($nginxConfPath, $nginxConf, $utf8NoBom)
Write-Host "  Created: docs/nginx.conf" -ForegroundColor Gray

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Run: RESTORE-DATABASE.bat" -ForegroundColor White
Write-Host "2. Run: FIX-URLS-AND-PERMISSIONS.bat" -ForegroundColor White
Write-Host "3. Open: http://localhost:8080" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to continue..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

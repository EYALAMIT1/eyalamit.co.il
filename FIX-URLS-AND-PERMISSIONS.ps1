# ========================================
# Fix URLs and Permissions
# ========================================

$ErrorActionPreference = "Stop"
$ProgressPreference = "SilentlyContinue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Fix URLs and Permissions" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Get current directory
$ProjectRoot = $PSScriptRoot
if (-not $ProjectRoot) {
    $ProjectRoot = Get-Location
}

# Load environment variables
$envFile = Join-Path $ProjectRoot ".env"
if (Test-Path $envFile) {
    Get-Content $envFile | ForEach-Object {
        if ($_ -match '^([^=]+)=(.*)$') {
            $name = $matches[1].Trim()
            $value = $matches[2].Trim()
            [Environment]::SetEnvironmentVariable($name, $value, "Process")
        }
    }
}

$DB_NAME = $env:DB_NAME
$DB_USER = $env:DB_USER
$DB_PASSWORD = $env:DB_PASSWORD
$DB_ROOT_PASSWORD = $env:DB_ROOT_PASSWORD

if (-not $DB_NAME) { $DB_NAME = "eyal_local" }
if (-not $DB_USER) { $DB_USER = "eyal" }
if (-not $DB_PASSWORD) { $DB_PASSWORD = "eyalpass" }
if (-not $DB_ROOT_PASSWORD) { $DB_ROOT_PASSWORD = "strong_root_pass" }

$NEW_URL = "http://localhost:8080"
$OLD_URLS = @(
    "https://www.eyalamit.co.il",
    "https://eyalamit.co.il",
    "http://www.eyalamit.co.il",
    "http://eyalamit.co.il"
)

$siteDir = Join-Path $ProjectRoot "eyalamit.co.il_bm1763848352dm"

Write-Host "[INFO] Site directory: $siteDir" -ForegroundColor Yellow
Write-Host "[INFO] New URL: $NEW_URL" -ForegroundColor Yellow
Write-Host ""

# Step 1: Update wp-config.php
Write-Host "[1/6] Updating wp-config.php..." -ForegroundColor Green
$wpConfigPath = Join-Path $siteDir "wp-config.php"

if (Test-Path $wpConfigPath) {
    $wpConfig = Get-Content $wpConfigPath -Raw -Encoding UTF8
    
    # Update database credentials
    $wpConfig = $wpConfig -replace "define\s*\(\s*['`"]DB_NAME['`"],\s*['`"][^'`"]*['`"]", "define('DB_NAME', '$DB_NAME')"
    $wpConfig = $wpConfig -replace "define\s*\(\s*['`"]DB_USER['`"],\s*['`"][^'`"]*['`"]", "define('DB_USER', '$DB_USER')"
    $wpConfig = $wpConfig -replace "define\s*\(\s*['`"]DB_PASSWORD['`"],\s*['`"][^'`"]*['`"]", "define('DB_PASSWORD', '$DB_PASSWORD')"
    $wpConfig = $wpConfig -replace "define\s*\(\s*['`"]DB_HOST['`"],\s*['`"][^'`"]*['`"]", "define('DB_HOST', 'db')"
    
    # Add or update DB_COLLATE
    if ($wpConfig -notmatch "DB_COLLATE") {
        $wpConfig = $wpConfig -replace "(define\s*\(\s*['`"]DB_CHARSET['`"],\s*['`"]utf8mb4['`"]\s*\);)", "`$1`n`ndefine('DB_COLLATE', 'utf8mb4_unicode_ci');"
    } else {
        $wpConfig = $wpConfig -replace "define\s*\(\s*['`"]DB_COLLATE['`"],\s*['`"][^'`"]*['`"]", "define('DB_COLLATE', 'utf8mb4_unicode_ci')"
    }
    
    # Add or update WP_HOME and WP_SITEURL
    if ($wpConfig -notmatch "WP_HOME") {
        $wpConfig = $wpConfig -replace "(/\* That's all, stop editing!.*\*/)", "define('WP_HOME', '$NEW_URL');`ndefine('WP_SITEURL', '$NEW_URL');`n`$1"
    } else {
        $wpConfig = $wpConfig -replace "define\s*\(\s*['`"]WP_HOME['`"],\s*['`"][^'`"]*['`"]", "define('WP_HOME', '$NEW_URL')"
        $wpConfig = $wpConfig -replace "define\s*\(\s*['`"]WP_SITEURL['`"],\s*['`"][^'`"]*['`"]", "define('WP_SITEURL', '$NEW_URL')"
    }
    
    $wpConfig | Out-File -FilePath $wpConfigPath -Encoding UTF8 -NoNewline
    Write-Host "  [OK] wp-config.php updated" -ForegroundColor Green
} else {
    Write-Host "  [WARNING] wp-config.php not found" -ForegroundColor Yellow
}

# Step 2: Update URLs in database
Write-Host ""
Write-Host "[2/6] Updating URLs in database..." -ForegroundColor Green

Set-Location $ProjectRoot

# Wait for database to be ready
Start-Sleep -Seconds 5

foreach ($OLD_URL in $OLD_URLS) {
    Write-Host "  Replacing: $OLD_URL -> $NEW_URL" -ForegroundColor Gray
    
    # Use root user for database updates (more reliable)
    # Update wp_options
    $updateOptions = @"
UPDATE wp_options SET option_value = REPLACE(option_value, '$OLD_URL', '$NEW_URL') WHERE option_name IN ('home', 'siteurl');
"@
    
    $updateOptions | docker compose exec -T db mysql -uroot "-p$DB_ROOT_PASSWORD" $DB_NAME 2>&1 | Out-Null
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  [WARNING] Failed to update wp_options for $OLD_URL" -ForegroundColor Yellow
    }
    
    # Update wp_posts
    $updatePosts = @"
UPDATE wp_posts SET post_content = REPLACE(post_content, '$OLD_URL', '$NEW_URL'), guid = REPLACE(guid, '$OLD_URL', '$NEW_URL');
"@
    
    $updatePosts | docker compose exec -T db mysql -uroot "-p$DB_ROOT_PASSWORD" $DB_NAME 2>&1 | Out-Null
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  [WARNING] Failed to update wp_posts for $OLD_URL" -ForegroundColor Yellow
    }
    
    # Update wp_postmeta
    $updatePostmeta = @"
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, '$OLD_URL', '$NEW_URL') WHERE meta_value LIKE '%$OLD_URL%';
"@
    
    $updatePostmeta | docker compose exec -T db mysql -uroot "-p$DB_ROOT_PASSWORD" $DB_NAME 2>&1 | Out-Null
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  [WARNING] Failed to update wp_postmeta for $OLD_URL" -ForegroundColor Yellow
    }
    
    # Update wp_usermeta
    $updateUsermeta = @"
UPDATE wp_usermeta SET meta_value = REPLACE(meta_value, '$OLD_URL', '$NEW_URL') WHERE meta_value LIKE '%$OLD_URL%';
"@
    
    $updateUsermeta | docker compose exec -T db mysql -uroot "-p$DB_ROOT_PASSWORD" $DB_NAME 2>&1 | Out-Null
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  [WARNING] Failed to update wp_usermeta for $OLD_URL" -ForegroundColor Yellow
    }
}

Write-Host "  [OK] Database URLs updated" -ForegroundColor Green

# Step 3: Update URLs in theme files
Write-Host ""
Write-Host "[3/6] Updating URLs in theme files..." -ForegroundColor Green

$themesDir = Join-Path $siteDir "wp-content\themes"
if (Test-Path $themesDir) {
    $filesUpdated = 0
    
    # CSS files
    $cssFiles = Get-ChildItem -Path $themesDir -Filter "*.css" -Recurse -ErrorAction SilentlyContinue
    foreach ($file in $cssFiles) {
        $content = Get-Content $file.FullName -Raw -Encoding UTF8 -ErrorAction SilentlyContinue
        if ($content) {
            $newContent = $content
            foreach ($OLD_URL in $OLD_URLS) {
                if ($newContent -match [regex]::Escape($OLD_URL)) {
                    $newContent = $newContent -replace [regex]::Escape($OLD_URL), $NEW_URL
                }
            }
            if ($newContent -ne $content) {
                $newContent | Out-File -FilePath $file.FullName -Encoding UTF8 -NoNewline
                $filesUpdated++
            }
        }
    }
    
    # PHP files
    $phpFiles = Get-ChildItem -Path $themesDir -Filter "*.php" -Recurse -ErrorAction SilentlyContinue
    foreach ($file in $phpFiles) {
        $content = Get-Content $file.FullName -Raw -Encoding UTF8 -ErrorAction SilentlyContinue
        if ($content) {
            $newContent = $content
            foreach ($OLD_URL in $OLD_URLS) {
                if ($newContent -match [regex]::Escape($OLD_URL)) {
                    $newContent = $newContent -replace [regex]::Escape($OLD_URL), $NEW_URL
                }
            }
            if ($newContent -ne $content) {
                $newContent | Out-File -FilePath $file.FullName -Encoding UTF8 -NoNewline
                $filesUpdated++
            }
        }
    }
    
    Write-Host "  Updated $filesUpdated theme files" -ForegroundColor Gray
    Write-Host "  [OK] Theme URLs updated" -ForegroundColor Green
} else {
    Write-Host "  [WARNING] Themes directory not found" -ForegroundColor Yellow
}

# Step 4: Fix file permissions
Write-Host ""
Write-Host "[4/6] Fixing file permissions..." -ForegroundColor Green

$uploadsDir = Join-Path $siteDir "wp-content\uploads"
if (Test-Path $uploadsDir) {
    Write-Host "  Setting ownership for uploads directory..." -ForegroundColor Gray
    docker compose exec -T wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads 2>&1 | Out-Null
    
    Write-Host "  Setting directory permissions (755)..." -ForegroundColor Gray
    docker compose exec -T wordpress sh -c "find /var/www/html/wp-content/uploads -type d -exec chmod 755 {} \;" 2>&1 | Out-Null
    
    Write-Host "  Setting file permissions (644)..." -ForegroundColor Gray
    docker compose exec -T wordpress sh -c "find /var/www/html/wp-content/uploads -type f -exec chmod 644 {} \;" 2>&1 | Out-Null
    
    Write-Host "  [OK] Permissions fixed" -ForegroundColor Green
} else {
    Write-Host "  [WARNING] Uploads directory not found" -ForegroundColor Yellow
}

# Step 5: Clear cache
Write-Host ""
Write-Host "[5/6] Clearing cache..." -ForegroundColor Green

$cacheDir = Join-Path $siteDir "wp-content\cache"
if (Test-Path $cacheDir) {
    Remove-Item -Path "$cacheDir\*" -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "  Removed cache files" -ForegroundColor Gray
}

$objectCache = Join-Path $siteDir "wp-content\object-cache.php"
if (Test-Path $objectCache) {
    Remove-Item -Path $objectCache -Force -ErrorAction SilentlyContinue
    Write-Host "  Removed object-cache.php" -ForegroundColor Gray
}

# Try WP-CLI cache flush (wpcli container may not be running, that's OK)
Write-Host "  Attempting WP-CLI cache flush..." -ForegroundColor Gray
try {
    $wpcliStatus = docker compose ps wpcli --format "{{.Status}}" 2>&1
    if ($wpcliStatus -match "Up") {
        docker compose exec -T wpcli wp cache flush 2>&1 | Out-Null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  WP-CLI cache flushed" -ForegroundColor Gray
        }
    } else {
        Write-Host "  [INFO] WP-CLI container not running, skipping cache flush" -ForegroundColor Gray
    }
} catch {
    Write-Host "  [INFO] WP-CLI cache flush skipped" -ForegroundColor Gray
}

Write-Host "  [OK] Cache cleared" -ForegroundColor Green

# Step 6: Restart containers
Write-Host ""
Write-Host "[6/6] Restarting containers..." -ForegroundColor Green

docker compose restart

if ($LASTEXITCODE -eq 0) {
    Write-Host "  [OK] Containers restarted" -ForegroundColor Green
} else {
    Write-Host "  [WARNING] Container restart may have failed" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Fix Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "What was fixed:" -ForegroundColor Yellow
Write-Host "- wp-config.php updated with correct DB credentials" -ForegroundColor White
Write-Host "- Database URLs updated (wp_options, wp_posts, wp_postmeta, wp_usermeta)" -ForegroundColor White
Write-Host "- Theme file URLs updated (CSS, PHP)" -ForegroundColor White
Write-Host "- File permissions fixed (uploads directory)" -ForegroundColor White
Write-Host "- Cache cleared" -ForegroundColor White
Write-Host "- Containers restarted" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Open: http://localhost:8080" -ForegroundColor White
Write-Host "2. Do HARD REFRESH: Ctrl + F5" -ForegroundColor White
Write-Host "3. Check Hebrew text (should be readable now)" -ForegroundColor White
Write-Host "4. Check images (should load now)" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to continue..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")


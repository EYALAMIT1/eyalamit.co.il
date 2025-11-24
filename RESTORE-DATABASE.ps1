# ========================================
# Restore Database from Backup
# ========================================

$ErrorActionPreference = "Continue"
$ProgressPreference = "SilentlyContinue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Restore Database" -ForegroundColor Cyan
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

Write-Host "[INFO] Database: $DB_NAME" -ForegroundColor Yellow
Write-Host "[INFO] User: $DB_USER" -ForegroundColor Yellow
Write-Host ""

# Step 1: Search for SQL file
Write-Host "[1/4] Searching for database backup..." -ForegroundColor Green
$siteDir = Join-Path $ProjectRoot "eyalamit.co.il_bm1763848352dm"
$backupsDir = Join-Path $ProjectRoot "backups"

$sqlFile = $null

# Search in site directory
$sqlFiles = Get-ChildItem -Path $siteDir -Filter "*.sql" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
if ($sqlFiles) {
    $sqlFile = $sqlFiles.FullName
    Write-Host "  Found SQL file in site directory: $(Split-Path $sqlFile -Leaf)" -ForegroundColor Gray
}

# Search in backups directory
if (-not $sqlFile) {
    $sqlFiles = Get-ChildItem -Path $backupsDir -Filter "*.sql" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($sqlFiles) {
        $sqlFile = $sqlFiles.FullName
        Write-Host "  Found SQL file in backups: $(Split-Path $sqlFile -Leaf)" -ForegroundColor Gray
    }
}

# Search in project root
if (-not $sqlFile) {
    $sqlFiles = Get-ChildItem -Path $ProjectRoot -Filter "*.sql" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($sqlFiles) {
        $sqlFile = $sqlFiles.FullName
        Write-Host "  Found SQL file in project root: $(Split-Path $sqlFile -Leaf)" -ForegroundColor Gray
    }
}

if (-not $sqlFile) {
    Write-Host "  [WARNING] No SQL file found. Database will be created empty." -ForegroundColor Yellow
    Write-Host "  You can import a database later using phpMyAdmin (http://localhost:8081)" -ForegroundColor Yellow
} else {
    Write-Host "  [OK] SQL file found: $sqlFile" -ForegroundColor Green
    
    # Copy to backups if not already there
    $backupSqlPath = Join-Path $backupsDir "database_backup.sql"
    if ($sqlFile -ne $backupSqlPath) {
        Copy-Item -Path $sqlFile -Destination $backupSqlPath -Force
        Write-Host "  Copied to: backups/database_backup.sql" -ForegroundColor Gray
    }
}

# Step 2: Start Docker containers
Write-Host ""
Write-Host "[2/4] Starting Docker containers..." -ForegroundColor Green
Set-Location $ProjectRoot

# Check required files exist
$dockerComposeFile = Join-Path $ProjectRoot "docker-compose.yml"
$envFile = Join-Path $ProjectRoot ".env"

if (-not (Test-Path $dockerComposeFile)) {
    Write-Host "  [ERROR] docker-compose.yml not found" -ForegroundColor Red
    Write-Host "  Please run SETUP-NEW-PROJECT.bat first" -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path $envFile)) {
    Write-Host "  [ERROR] .env file not found" -ForegroundColor Red
    Write-Host "  Please run SETUP-NEW-PROJECT.bat first" -ForegroundColor Yellow
    exit 1
}

# Stop existing containers (ignore errors if no containers exist)
Write-Host "  Stopping existing containers..." -ForegroundColor Gray
try {
    docker compose down 2>&1 | Out-Null
} catch {
    # Ignore errors when stopping containers
}

# Check if ports are in use by other containers
Write-Host "  Checking for port conflicts..." -ForegroundColor Gray
$port8080InUse = $false
$port8081InUse = $false

try {
    $containers = docker ps --format "{{.Names}}:{{.Ports}}" 2>&1
    if ($containers -match ":8080") {
        $port8080InUse = $true
        Write-Host "  [WARNING] Port 8080 is in use by another container" -ForegroundColor Yellow
    }
    if ($containers -match ":8081") {
        $port8081InUse = $true
        Write-Host "  [WARNING] Port 8081 is in use by another container" -ForegroundColor Yellow
    }
    
    if ($port8080InUse -or $port8081InUse) {
        Write-Host "  Attempting to stop conflicting containers..." -ForegroundColor Yellow
        # Try to stop containers from old project
        docker stop $(docker ps -q --filter "publish=8080") 2>&1 | Out-Null
        docker stop $(docker ps -q --filter "publish=8081") 2>&1 | Out-Null
        Start-Sleep -Seconds 2
    }
} catch {
    # Ignore errors
}

# Start containers
Write-Host "  Starting containers..." -ForegroundColor Gray

# Check if Docker is running
$dockerCheck = docker info 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "  [ERROR] Docker is not running or not accessible" -ForegroundColor Red
    Write-Host "  Please start Docker Desktop and try again" -ForegroundColor Yellow
    Write-Host "  Error: $dockerCheck" -ForegroundColor Red
    exit 1
}

# Try to start containers and capture output
$composeOutput = docker compose up -d 2>&1
$composeExitCode = $LASTEXITCODE

if ($composeExitCode -ne 0) {
    Write-Host "  [ERROR] Failed to start Docker containers" -ForegroundColor Red
    Write-Host "  Docker Compose output:" -ForegroundColor Yellow
    Write-Host $composeOutput -ForegroundColor Red
    Write-Host ""
    Write-Host "  Common issues:" -ForegroundColor Yellow
    Write-Host "  - Make sure Docker Desktop is running" -ForegroundColor White
    Write-Host "  - Check if ports 8080 and 8081 are available" -ForegroundColor White
    Write-Host "  - Verify docker-compose.yml and .env files exist" -ForegroundColor White
    exit 1
} else {
    Write-Host $composeOutput -ForegroundColor Gray
}

Write-Host "  Waiting for database to be ready..." -ForegroundColor Gray
Start-Sleep -Seconds 15

# Check if containers are running
$dbRunning = docker compose ps db --format "{{.Status}}" 2>&1
if ($dbRunning -notmatch "Up") {
    Write-Host "  [ERROR] Database container is not running" -ForegroundColor Red
    Write-Host "  Check Docker Desktop and try again" -ForegroundColor Yellow
    exit 1
}

Write-Host "  [OK] Containers are running" -ForegroundColor Green

# Step 3: Create database with utf8mb4
Write-Host ""
Write-Host "[3/4] Creating database with utf8mb4 encoding..." -ForegroundColor Green

$createDbSql = @"
DROP DATABASE IF EXISTS `$DB_NAME;
CREATE DATABASE `$DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
"@

$createDbSql | docker compose exec -T db mysql -uroot -p"$DB_ROOT_PASSWORD"

if ($LASTEXITCODE -ne 0) {
    Write-Host "  [ERROR] Failed to create database" -ForegroundColor Red
    exit 1
}

Write-Host "  [OK] Database created: $DB_NAME" -ForegroundColor Green

# Step 4: Import database if SQL file exists
if ($sqlFile -and (Test-Path $sqlFile)) {
    Write-Host ""
    Write-Host "[4/4] Importing database..." -ForegroundColor Green
    Write-Host "  This may take 5-10 minutes..." -ForegroundColor Yellow
    
    # Import SQL file to database
    Get-Content $sqlFile -Raw | docker compose exec -T db mysql -uroot -p"$DB_ROOT_PASSWORD" --default-character-set=utf8mb4 $DB_NAME
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  [WARNING] Database import may have failed. Check logs." -ForegroundColor Yellow
    } else {
        Write-Host "  [OK] Database imported successfully" -ForegroundColor Green
    }
    
    # Convert all tables to utf8mb4
    Write-Host ""
    Write-Host "  Converting tables to utf8mb4_unicode_ci..." -ForegroundColor Gray
    
    $convertSql = @"
SELECT CONCAT('ALTER TABLE `', table_name, '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;')
FROM information_schema.TABLES
WHERE table_schema = '$DB_NAME'
AND table_type = 'BASE TABLE';
"@
    
    $alterStatements = $convertSql | docker compose exec -T db mysql -uroot -p"$DB_ROOT_PASSWORD" -N
    
    if ($alterStatements) {
        $alterStatements | docker compose exec -T db mysql -uroot -p"$DB_ROOT_PASSWORD" $DB_NAME 2>&1 | Out-Null
        Write-Host "  [OK] All tables converted to utf8mb4_unicode_ci" -ForegroundColor Green
    }
} else {
    Write-Host ""
    Write-Host "[4/4] Skipping import (no SQL file found)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Database Restore Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next step: Run FIX-URLS-AND-PERMISSIONS.bat" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press any key to continue..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")


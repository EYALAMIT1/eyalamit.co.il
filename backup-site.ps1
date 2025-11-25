# ========================================
# Full WordPress backup (DB + files + manifest)
# ========================================

[CmdletBinding()]
param(
    [string]$EnvFile,
    [string]$SiteDir,
    [string]$OutputRoot = ".\backups",
    [switch]$SkipDatabase,
    [switch]$SkipFiles
)

$ErrorActionPreference = "Stop"

# Auto-detect site directory if not provided
if (-not $SiteDir) {
    $possibleDirs = Get-ChildItem -Directory | Where-Object { $_.Name -like "eyalamit.co.il*" } | Select-Object -First 1
    if ($possibleDirs) {
        $SiteDir = $possibleDirs.FullName
        Write-Host "[INFO] Auto-detected site directory: $SiteDir" -ForegroundColor Cyan
    } else {
        $SiteDir = ".\eyalamit.co.il_bm1763848352dm"
    }
}

if (-not $EnvFile) {
    if (Test-Path ".\env.local") {
        $EnvFile = ".\env.local"
    } elseif (Test-Path ".\env.example") {
        $EnvFile = ".\env.example"
    } else {
        throw "Could not find env.local or env.example"
    }
}

function Get-EnvMap {
    param([string]$Path)
    $map = @{}
    if (-not (Test-Path $Path)) {
        return $map
    }
    Get-Content $Path | ForEach-Object {
        if ($_ -match '^\s*#' -or $_.Trim().Length -eq 0) { return }
        $split = $_.Split('=', 2)
        if ($split.Count -eq 2) {
            $key = $split[0].Trim()
            $value = $split[1].Trim().Trim("'`"")
            $map[$key] = $value
        }
    }
    return $map
}

$envMap = Get-EnvMap -Path $EnvFile

$dbName = $envMap["DB_NAME"]
$dbUser = $envMap["DB_USER"]
$dbPassword = $envMap["DB_PASSWORD"]
$dbHost = $envMap["DB_HOST"]

if (-not $dbName) { $dbName = "eyal_local" }
if (-not $dbUser) { $dbUser = "eyal" }
if (-not $dbPassword) { $dbPassword = "eyalpass" }
if (-not $dbHost) { $dbHost = "db" }

$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$backupFolder = Join-Path $OutputRoot "backup_$timestamp"
New-Item -ItemType Directory -Force -Path $backupFolder | Out-Null

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " WordPress backup started @ $timestamp" -ForegroundColor Cyan
Write-Host " Target folder: $backupFolder" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Sanity check
Write-Host "[1/5] Validating docker compose stack..." -ForegroundColor Yellow
try {
    $containers = docker ps --format "{{.Names}}"
    if (-not ($containers -match "db")) {
        throw "Database container not running. Start the stack before backing up."
    }
    if (-not ($containers -match "wordpress")) {
        throw "WordPress/PHP-FPM container not running."
    }
    Write-Host "[OK] Containers are healthy." -ForegroundColor Green
} catch {
    Write-Host "[ERROR] $_" -ForegroundColor Red
    exit 1
}

$dbBackupFile = Join-Path $backupFolder "database_backup.sql"
if (-not $SkipDatabase) {
    Write-Host "[2/5] Exporting database '$dbName'..." -ForegroundColor Yellow
    try {
$dump = docker compose exec -T db sh -c "mysqldump -u$dbUser -p$dbPassword $dbName"
        if ($LASTEXITCODE -ne 0 -or -not $dump) {
            throw "mysqldump returned a non-zero exit code."
        }
        $dump | Out-File -FilePath $dbBackupFile -Encoding UTF8
        $dbHash = Get-FileHash -Algorithm SHA256 -Path $dbBackupFile
        $dbSizeMB = [Math]::Round((Get-Item $dbBackupFile).Length / 1MB, 2)
        Write-Host "[OK] DB export complete (${dbSizeMB} MB, SHA256=$($dbHash.Hash.Substring(0,12))...)" -ForegroundColor Green
    } catch {
        Write-Host "[ERROR] $_" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "[SKIP] Database export skipped by flag." -ForegroundColor DarkYellow
}

$SiteDir = (Resolve-Path $SiteDir).Path

$filesBackupFile = Join-Path $backupFolder "files_backup.zip"
if (-not $SkipFiles) {
    Write-Host "[3/5] Archiving site files from $SiteDir ..." -ForegroundColor Yellow
    if (-not (Test-Path $SiteDir)) {
        Write-Host "[ERROR] Site directory not found: $SiteDir" -ForegroundColor Red
        exit 1
    }
    $excludes = @(
        "wp-content/cache",
        "wp-content/upgrade",
        "wp-content/backups",
        "wp-content/envato-backups",
        ".git",
        "backups"
    )
    $tempDir = Join-Path $backupFolder "temp_files"
    New-Item -ItemType Directory -Force -Path $tempDir | Out-Null

    $sourcePath = (Resolve-Path $SiteDir).Path
    $destPath = (Resolve-Path $tempDir).Path
    $roboArgs = @(
        "`"$sourcePath`"",
        "`"$destPath`"",
        "/E","/COPY:DAT","/R:2","/W:2","/NFL","/NDL","/NJH","/NJS","/NP"
    )
    $excludePaths = @()
    foreach ($ex in $excludes) {
        $exFull = Join-Path $SiteDir $ex
        if (Test-Path $exFull) {
            $excludePaths += "`"$(Resolve-Path $exFull).Path`""
        }
    }
    if ($excludePaths.Count -gt 0) {
        $roboArgs += "/XD"
        $roboArgs += $excludePaths
    }
    $roboProcess = Start-Process -FilePath "robocopy.exe" -ArgumentList $roboArgs -Wait -PassThru -NoNewWindow
    $roboExit = $roboProcess.ExitCode
    if ($roboExit -gt 7) {
        throw "Robocopy failed with exit code $roboExit"
    }

    # Function to add long path prefix for Windows
    function Add-LongPathPrefix {
        param([string]$Path)
        if ($Path -notmatch '^\\\\\?\\') {
            if ($Path -match '^[A-Za-z]:') {
                return "\\?\$Path"
            } elseif ($Path -match '^\\\\') {
                return "\\?\UNC\$($Path.Substring(2))"
            }
        }
        return $Path
    }

    $sevenZip = Get-Command 7z.exe -ErrorAction SilentlyContinue
    if (-not $sevenZip) {
        # Try common installation paths
        $commonPaths = @(
            "C:\Program Files\7-Zip\7z.exe",
            "C:\Program Files (x86)\7-Zip\7z.exe"
        )
        foreach ($path in $commonPaths) {
            if (Test-Path $path) {
                $sevenZip = Get-Command $path
                break
            }
        }
    }
    
    if ($sevenZip) {
        # Use 7-Zip with long path support (-spf2 parameter)
        # Prepare paths with long path prefix if needed
        $tempDirFor7z = $tempDir
        $outputFileFor7z = $filesBackupFile
        
        # Check if paths are long or if we should use long path prefix anyway for safety
        if ($tempDir.Length -gt 260 -or $filesBackupFile.Length -gt 260) {
            $tempDirFor7z = Add-LongPathPrefix $tempDir
            $outputFileFor7z = Add-LongPathPrefix $filesBackupFile
            Write-Host "[INFO] Using long path prefix for archive creation" -ForegroundColor Gray
        }
        
        # Build 7-Zip command with long path support
        # -spf2: Use full paths with long path support
        # -tzip: ZIP format
        # -mx=3: Compression level (medium)
        # -r: Recurse subdirectories
        $7zipArgs = @(
            "a",                    # Add to archive
            "-tzip",                # ZIP format
            "-spf2",                # Use full paths with long path support
            "-mx=3",                # Compression level
            "-r",                   # Recurse subdirectories
            "`"$outputFileFor7z`"", # Output file
            "`"$tempDirFor7z\*`""   # Source files
        )
        
        # Add -bb0 to suppress 7-Zip progress output
        $7zipArgsWithQuiet = $7zipArgs + @("-bb0")
        
        # Create temporary files for output redirection
        $tempStdout = [System.IO.Path]::GetTempFileName()
        $tempStderr = [System.IO.Path]::GetTempFileName()
        
        try {
            $process = Start-Process -FilePath $sevenZip.Path -ArgumentList $7zipArgsWithQuiet -Wait -PassThru -NoNewWindow -RedirectStandardOutput $tempStdout -RedirectStandardError $tempStderr
            
            if ($process.ExitCode -ne 0) {
                # Read error output for debugging
                $errorOutput = Get-Content $tempStderr -ErrorAction SilentlyContinue
                throw "7zip failed to create archive (exit code: $($process.ExitCode)). Error: $($errorOutput -join ' ')"
            }
        } finally {
            # Clean up temp files
            Remove-Item $tempStdout -ErrorAction SilentlyContinue
            Remove-Item $tempStderr -ErrorAction SilentlyContinue
        }
        
        Write-Host "[INFO] Archive created with long path support" -ForegroundColor Gray
    } else {
        Write-Host "[WARNING] 7-Zip not found. Falling back to .NET Compression (may not support long paths)" -ForegroundColor Yellow
        Add-Type -AssemblyName System.IO.Compression.FileSystem
        if (Test-Path $filesBackupFile) { Remove-Item $filesBackupFile -Force }
        try {
            [System.IO.Compression.ZipFile]::CreateFromDirectory($tempDir, $filesBackupFile)
        } catch {
            Write-Host "[ERROR] .NET Compression failed: $_" -ForegroundColor Red
            Write-Host "[ERROR] This may be due to long file paths. Please install 7-Zip for full support." -ForegroundColor Red
            throw "Failed to create archive. Install 7-Zip for long path support."
        }
    }
    Remove-Item -LiteralPath $tempDir -Recurse -Force -ErrorAction SilentlyContinue

    if (-not (Test-Path $filesBackupFile)) {
        Write-Host "[ERROR] Failed to create $filesBackupFile" -ForegroundColor Red
        exit 1
    }
    $filesHash = Get-FileHash -Algorithm SHA256 -Path $filesBackupFile
    $filesSizeMB = [Math]::Round((Get-Item $filesBackupFile).Length / 1MB, 2)
    Write-Host "[OK] Files archive complete (${filesSizeMB} MB, SHA256=$($filesHash.Hash.Substring(0,12))...)" -ForegroundColor Green
} else {
    Write-Host "[SKIP] File archive skipped by flag." -ForegroundColor DarkYellow
}

# Metadata manifest
Write-Host "[4/5] Writing manifest..." -ForegroundColor Yellow
$gitHead = $null
try {
    $gitHead = (git rev-parse --short HEAD 2>$null)
} catch {
    $gitHead = $null
}

$databasePath = $null
$databaseHash = $null
if (Test-Path $dbBackupFile) {
    $databasePath = (Resolve-Path $dbBackupFile).Path
    $databaseHash = (Get-FileHash -Algorithm SHA256 -Path $dbBackupFile).Hash
}

$filesPath = $null
$filesHashValue = $null
if (Test-Path $filesBackupFile) {
    $filesPath = (Resolve-Path $filesBackupFile).Path
    $filesHashValue = (Get-FileHash -Algorithm SHA256 -Path $filesBackupFile).Hash
}

$manifest = [ordered]@{
    timestamp       = $timestamp
    siteDirectory   = (Resolve-Path $SiteDir).Path
    database        = @{
        name   = $dbName
        host   = $dbHost
        path   = $databasePath
        sha256 = $databaseHash
    }
    files           = @{
        path   = $filesPath
        sha256 = $filesHashValue
    }
    gitHead         = $gitHead
    wpConfigExists  = Test-Path (Join-Path $SiteDir "wp-config.php")
}

$manifestPath = Join-Path $backupFolder "manifest.json"
$manifest | ConvertTo-Json -Depth 5 | Out-File -FilePath $manifestPath -Encoding UTF8
Write-Host "[OK] Manifest stored at $manifestPath" -ForegroundColor Green

# Latest shortcut
Write-Host "[5/5] Updating latest pointer..." -ForegroundColor Yellow
$latestDir = Join-Path $OutputRoot "latest"
if (Test-Path $latestDir) { Remove-Item $latestDir -Recurse -Force }
Copy-Item $backupFolder $latestDir -Recurse
Write-Host "[OK] Latest backup synchronized." -ForegroundColor Green

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host " Backup completed successfully." -ForegroundColor Green
Write-Host " Database dump: $dbBackupFile" -ForegroundColor Green
Write-Host " Files archive : $filesBackupFile" -ForegroundColor Green
Write-Host " Manifest      : $manifestPath" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan



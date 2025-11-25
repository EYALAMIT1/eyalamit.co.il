# ========================================
# Create ZIP archive with long path support
# ========================================
# This script creates a ZIP file that preserves long file paths (>260 characters)
# Uses 7-Zip with -spf2 parameter for full long path support

[CmdletBinding()]
param(
    [Parameter(Mandatory=$false)]
    [string]$SourceDir,
    
    [Parameter(Mandatory=$false)]
    [string]$OutputFile,
    
    [Parameter(Mandatory=$false)]
    [string[]]$ExcludeDirs = @(),
    
    [Parameter(Mandatory=$false)]
    [int]$CompressionLevel = 3
)

$ErrorActionPreference = "Stop"
$script:ExitCode = 0

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

# Function to remove long path prefix
function Remove-LongPathPrefix {
    param([string]$Path)
    if ($Path -match '^\\\\\?\\UNC\\(.+)') {
        return "\\$($Matches[1])"
    } elseif ($Path -match '^\\\\\?\\(.+)') {
        return $Matches[1]
    }
    return $Path
}

# Auto-detect source directory if not provided
if (-not $SourceDir) {
    $possibleDirs = Get-ChildItem -Directory | Where-Object { $_.Name -like "eyalamit.co.il*" } | Select-Object -First 1
    if ($possibleDirs) {
        $SourceDir = $possibleDirs.FullName
        Write-Host "[INFO] Auto-detected source directory: $SourceDir" -ForegroundColor Cyan
    } else {
        $SourceDir = ".\eyalamit.co.il_bm1763848352dm"
        Write-Host "[INFO] Using default source directory: $SourceDir" -ForegroundColor Cyan
    }
}

# Auto-generate output file if not provided
if (-not $OutputFile) {
    $timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
    $OutputFile = ".\long-path-backup_$timestamp.zip"
    Write-Host "[INFO] Auto-generated output file: $OutputFile" -ForegroundColor Cyan
}

# Resolve paths
$SourceDir = (Resolve-Path $SourceDir -ErrorAction Stop).Path
$OutputFile = (Resolve-Path (Split-Path $OutputFile -Parent) -ErrorAction SilentlyContinue).Path + "\" + (Split-Path $OutputFile -Leaf)
if (-not (Test-Path (Split-Path $OutputFile -Parent))) {
    New-Item -ItemType Directory -Force -Path (Split-Path $OutputFile -Parent) | Out-Null
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Creating ZIP with long path support" -ForegroundColor Cyan
Write-Host " Source: $SourceDir" -ForegroundColor Cyan
Write-Host " Output: $OutputFile" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Check if 7-Zip is installed
Write-Host "[1/4] Checking for 7-Zip..." -ForegroundColor Yellow
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

if (-not $sevenZip) {
    Write-Host "[ERROR] 7-Zip not found. Please install 7-Zip from https://www.7-zip.org/" -ForegroundColor Red
    Write-Host "        This script requires 7-Zip for long path support." -ForegroundColor Red
    $script:ExitCode = 1
} else {
    Write-Host "[OK] Found 7-Zip at: $($sevenZip.Path)" -ForegroundColor Green

    # Check for long paths in source directory
Write-Host "[2/4] Scanning for files with long paths..." -ForegroundColor Yellow
$longPathFiles = @()
$totalFiles = 0
$longPathThreshold = 260

try {
    # Use Get-ChildItem with -Recurse and check path lengths
    $allFiles = Get-ChildItem -Path $SourceDir -Recurse -File -ErrorAction SilentlyContinue
    foreach ($file in $allFiles) {
        $totalFiles++
        $fullPath = $file.FullName
        if ($fullPath.Length -gt $longPathThreshold) {
            $longPathFiles += $file
        }
    }
    
    Write-Host "[OK] Scanned $totalFiles files" -ForegroundColor Green
    if ($longPathFiles.Count -gt 0) {
        Write-Host "[INFO] Found $($longPathFiles.Count) files with paths longer than $longPathThreshold characters" -ForegroundColor Yellow
        Write-Host "       These files will be preserved using 7-Zip long path support" -ForegroundColor Gray
    } else {
        Write-Host "[INFO] No files with long paths detected" -ForegroundColor Gray
    }
} catch {
    Write-Host "[WARNING] Could not scan all files: $_" -ForegroundColor Yellow
    Write-Host "         Continuing with archive creation..." -ForegroundColor Gray
}

# Create archive using 7-Zip with long path support
Write-Host "[3/4] Creating ZIP archive with long path support..." -ForegroundColor Yellow

# Prepare source path with long path prefix if needed
$sourcePathFor7z = $SourceDir
if ($SourceDir.Length -gt $longPathThreshold -or $longPathFiles.Count -gt 0) {
    $sourcePathFor7z = Add-LongPathPrefix $SourceDir
    Write-Host "[INFO] Using long path prefix for source directory" -ForegroundColor Gray
}

# Prepare output path with long path prefix if needed
$outputPathFor7z = $OutputFile
if ($OutputFile.Length -gt $longPathThreshold) {
    $outputPathFor7z = Add-LongPathPrefix $OutputFile
    Write-Host "[INFO] Using long path prefix for output file" -ForegroundColor Gray
}

# Build 7-Zip command arguments
# -spf2: Use full paths with long path support
# -tzip: ZIP format
# -mx=N: Compression level (0-9, default 3)
# -r: Recurse subdirectories
$7zipArgs = @(
    "a",                    # Add to archive
    "-tzip",                # ZIP format
    "-spf2",                # Use full paths with long path support
    "-mx=$CompressionLevel", # Compression level
    "-r",                   # Recurse subdirectories
    "`"$outputPathFor7z`"", # Output file
    "`"$sourcePathFor7z\*`"" # Source files
)

# Add exclusions if specified
if ($ExcludeDirs.Count -gt 0) {
    foreach ($exclude in $ExcludeDirs) {
        $excludePath = Join-Path $SourceDir $exclude
        if (Test-Path $excludePath) {
            $excludePathFor7z = Add-LongPathPrefix $excludePath
            $7zipArgs += "-xr!`"$excludePathFor7z\*`""
        }
    }
}

Write-Host "       This may take several minutes..." -ForegroundColor Gray
$startTime = Get-Date

try {
    # Run 7-Zip with suppressed output
    # Use -bb0 to suppress progress output from 7-Zip itself
    $7zipArgsWithQuiet = $7zipArgs + @("-bb0")
    
    # Create temporary files for output redirection
    $tempStdout = [System.IO.Path]::GetTempFileName()
    $tempStderr = [System.IO.Path]::GetTempFileName()
    
    try {
        $process = Start-Process -FilePath $sevenZip.Path -ArgumentList $7zipArgsWithQuiet -Wait -PassThru -NoNewWindow -RedirectStandardOutput $tempStdout -RedirectStandardError $tempStderr
        
        if ($process.ExitCode -ne 0) {
            # Read error output for debugging
            $errorOutput = Get-Content $tempStderr -ErrorAction SilentlyContinue
            throw "7-Zip returned exit code $($process.ExitCode). Error: $($errorOutput -join ' ')"
        }
    } finally {
        # Clean up temp files
        Remove-Item $tempStdout -ErrorAction SilentlyContinue
        Remove-Item $tempStderr -ErrorAction SilentlyContinue
    }
    
    $endTime = Get-Date
    $duration = ($endTime - $startTime).TotalSeconds
    
    if (-not (Test-Path $OutputFile)) {
        throw "Archive file was not created"
    }
    
    $archiveSize = (Get-Item $OutputFile).Length
    $archiveSizeMB = [Math]::Round($archiveSize / 1MB, 2)
    
    Write-Host "[OK] Archive created successfully" -ForegroundColor Green
    Write-Host "     Size: $archiveSizeMB MB" -ForegroundColor Gray
    Write-Host "     Duration: $([Math]::Round($duration, 1)) seconds" -ForegroundColor Gray
    
} catch {
    Write-Host "[ERROR] Failed to create archive: $_" -ForegroundColor Red
    $script:ExitCode = 1
}

# Verify archive
Write-Host "[4/4] Verifying archive..." -ForegroundColor Yellow
try {
    $verifyArgs = @(
        "t",                    # Test archive
        "`"$outputPathFor7z`""  # Archive file
    )
    
    $verifyArgsWithQuiet = $verifyArgs + @("-bb0")
    $tempStdout = [System.IO.Path]::GetTempFileName()
    $tempStderr = [System.IO.Path]::GetTempFileName()
    
    try {
        $verifyProcess = Start-Process -FilePath $sevenZip.Path -ArgumentList $verifyArgsWithQuiet -Wait -PassThru -NoNewWindow -RedirectStandardOutput $tempStdout -RedirectStandardError $tempStderr
        
        if ($verifyProcess.ExitCode -eq 0) {
            Write-Host "[OK] Archive verification passed" -ForegroundColor Green
        } else {
            Write-Host "[WARNING] Archive verification returned exit code $($verifyProcess.ExitCode)" -ForegroundColor Yellow
            Write-Host "         Archive may still be usable, but please verify manually" -ForegroundColor Gray
        }
    } finally {
        # Clean up temp files
        Remove-Item $tempStdout -ErrorAction SilentlyContinue
        Remove-Item $tempStderr -ErrorAction SilentlyContinue
    }
} catch {
    Write-Host "[WARNING] Could not verify archive: $_" -ForegroundColor Yellow
}

# Summary
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host " ZIP Archive Created Successfully" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " Source directory: $SourceDir" -ForegroundColor Gray
Write-Host " Output file     : $OutputFile" -ForegroundColor Gray
Write-Host " Archive size    : $archiveSizeMB MB" -ForegroundColor Gray
if ($longPathFiles.Count -gt 0) {
    Write-Host " Long path files : $($longPathFiles.Count) files preserved" -ForegroundColor Green
}
    Write-Host "========================================`n" -ForegroundColor Cyan
}

# Keep window open at the end
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Press any key to close this window..." -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# Exit with appropriate code
exit $script:ExitCode


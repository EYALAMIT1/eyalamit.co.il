# ========================================
# Disable all problematic plugins
# ========================================

[CmdletBinding()]
param(
    [string]$SiteDir,
    [string]$OutputFile = "fix-plugins-output.txt"
)

$ErrorActionPreference = "Continue"

# Auto-detect SiteDir
if (-not $SiteDir) {
    $possibleSiteDirs = Get-ChildItem -Path "." -Directory | Where-Object { $_.Name -match "eyalamit\.co\.il" }
    if ($possibleSiteDirs.Count -eq 1) {
        $SiteDir = $possibleSiteDirs[0].FullName
        Write-Host "[INFO] Auto-detected site directory: $SiteDir" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] Could not auto-detect site directory" -ForegroundColor Red
        exit 1
    }
}

$outputPath = Join-Path (Get-Location) $OutputFile
$pluginsDir = Join-Path $SiteDir "wp-content\plugins"

Write-Host ""
Write-Host "=== Disabling Problematic Plugins ===" -ForegroundColor Cyan
Write-Host ""

# Start output file
@"
========================================
כיבוי כל התוספים הבעייתיים
========================================
תאריך: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
תיקיית אתר: $SiteDir

"@ | Out-File -FilePath $outputPath -Encoding UTF8

$problematicPlugins = @(
    @{Name="LayerSlider"; Path="LayerSlider"},
    @{Name="revslider"; Path="revslider"},
    @{Name="timetable"; Path="timetable"}
)

$disabledCount = 0
$skippedCount = 0
$errorCount = 0

foreach ($plugin in $problematicPlugins) {
    $pluginName = $plugin.Name
    $pluginPath = Join-Path $pluginsDir $plugin.Path
    $disabledPath = Join-Path $pluginsDir "$($plugin.Path).disabled"
    
    Write-Host "[INFO] Processing: $pluginName" -ForegroundColor Yellow
    "[INFO] Processing: $pluginName" | Out-File -FilePath $outputPath -Append -Encoding UTF8
    
    if (Test-Path $pluginPath) {
        Write-Host "  [FOUND] Plugin directory exists" -ForegroundColor Yellow
        "  [FOUND] Plugin directory exists" | Out-File -FilePath $outputPath -Append -Encoding UTF8
        
        try {
            Rename-Item -Path $pluginPath -NewName "$($plugin.Path).disabled" -Force -ErrorAction Stop
            
            if (Test-Path $disabledPath) {
                Write-Host "  [OK] $pluginName disabled successfully!" -ForegroundColor Green
                "  [OK] $pluginName disabled successfully!" | Out-File -FilePath $outputPath -Append -Encoding UTF8
                $disabledCount++
            } else {
                Write-Host "  [ERROR] Failed to disable $pluginName" -ForegroundColor Red
                "  [ERROR] Failed to disable $pluginName" | Out-File -FilePath $outputPath -Append -Encoding UTF8
                $errorCount++
            }
        } catch {
            Write-Host "  [ERROR] Failed to disable $pluginName : $_" -ForegroundColor Red
            "  [ERROR] Failed to disable $pluginName : $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
            $errorCount++
        }
    } elseif (Test-Path $disabledPath) {
        Write-Host "  [SKIP] Already disabled" -ForegroundColor Green
        "  [SKIP] Already disabled" | Out-File -FilePath $outputPath -Append -Encoding UTF8
        $skippedCount++
    } else {
        Write-Host "  [INFO] Plugin not found" -ForegroundColor DarkYellow
        "  [INFO] Plugin not found" | Out-File -FilePath $outputPath -Append -Encoding UTF8
        $skippedCount++
    }
    
    Write-Host ""
    "" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

# Check status via Docker
Write-Host "[INFO] Checking status via Docker..." -ForegroundColor Yellow
"[INFO] Checking status via Docker..." | Out-File -FilePath $outputPath -Append -Encoding UTF8

try {
    $status = docker compose --env-file env.local exec wordpress sh -c "ls -la /var/www/html/wp-content/plugins/ | grep -E '(LayerSlider|revslider|timetable)'" 2>&1
    Write-Host $status
    $status | Out-File -FilePath $outputPath -Append -Encoding UTF8
} catch {
    Write-Host "[WARNING] Could not check Docker status: $_" -ForegroundColor Yellow
    "[WARNING] Could not check Docker status: $_" | Out-File -FilePath $outputPath -Append -Encoding UTF8
}

# Summary
Write-Host ""
Write-Host "=== Summary ===" -ForegroundColor Cyan
Write-Host "  Disabled: $disabledCount plugin(s)" -ForegroundColor Green
Write-Host "  Skipped:  $skippedCount plugin(s)" -ForegroundColor DarkYellow
Write-Host "  Errors:   $errorCount plugin(s)" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })

@"

=== Summary ===
  Disabled: $disabledCount plugin(s)
  Skipped:  $skippedCount plugin(s)
  Errors:   $errorCount plugin(s)

========================================
[SUCCESS] סיום
========================================

[INFO] בדוק את האתר ב: http://localhost:8080
[INFO] הפלט נשמר בקובץ: $outputPath

"@ | Out-File -FilePath $outputPath -Append -Encoding UTF8

Write-Host ""
Write-Host "[SUCCESS] Done!" -ForegroundColor Green
Write-Host "[INFO] Output saved to: $outputPath" -ForegroundColor Yellow
Write-Host "[INFO] Check the site at: http://localhost:8080" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press any key to open the output file..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

# Open the output file
if (Test-Path $outputPath) {
    notepad $outputPath
}



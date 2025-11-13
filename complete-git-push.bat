@echo off
echo ========================================
echo Completing Git Push to GitHub
echo ========================================
echo.

REM Try to find Git in common locations
set GIT_CMD=
if exist "C:\Program Files\Git\cmd\git.exe" (
    set "GIT_CMD=C:\Program Files\Git\cmd\git.exe"
) else if exist "C:\Program Files (x86)\Git\cmd\git.exe" (
    set "GIT_CMD=C:\Program Files (x86)\Git\cmd\git.exe"
) else if exist "%LOCALAPPDATA%\Programs\Git\cmd\git.exe" (
    set "GIT_CMD=%LOCALAPPDATA%\Programs\Git\cmd\git.exe"
) else (
    echo [ERROR] Git not found in standard locations
    echo Please ensure Git is installed and try again
    pause
    exit /b 1
)

echo [OK] Found Git: %GIT_CMD%
echo.

REM Navigate to project directory
cd /d "C:\Users\USER\Pictures\5848~1\new website AI nov 2025"
echo [INFO] Working directory: %CD%
echo.

REM Add all files
echo [INFO] Adding files to staging area...
"%GIT_CMD%" add .
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Failed to add files
    pause
    exit /b 1
)
echo [OK] Files added
echo.

REM Commit
echo [INFO] Committing changes...
"%GIT_CMD%" commit -m "Initial commit: WordPress site with Docker setup and local environment configuration"
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] Commit may have failed or no changes to commit
)
echo.

REM Set branch to main
echo [INFO] Setting branch to main...
"%GIT_CMD%" branch -M main
echo.

REM Push to GitHub
echo [INFO] Pushing to GitHub...
echo Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git
echo.
"%GIT_CMD%" push -u origin main

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo [SUCCESS] Successfully pushed to GitHub!
    echo ========================================
    echo View your repository at:
    echo https://github.com/EYALAMIT1/eyalamit.co.il
    echo.
) else (
    echo.
    echo ========================================
    echo [ERROR] Push failed!
    echo ========================================
    echo Check the error message above
    echo.
)

pause


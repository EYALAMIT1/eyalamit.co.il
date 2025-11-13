@echo off
echo ========================================
echo Git Setup and Push to GitHub
echo ========================================
echo.

REM Check if Git is available
where git >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Git is not installed or not in PATH!
    echo.
    echo Please install Git for Windows:
    echo 1. Download from: https://git-scm.com/download/win
    echo 2. Install with default settings
    echo 3. Restart this script after installation
    echo.
    pause
    exit /b 1
)

echo [OK] Git is installed
git --version
echo.

REM Navigate to project directory
cd /d "C:\Users\USER\Pictures\5848~1\new website AI nov 2025"
echo [INFO] Working directory: %CD%
echo.

REM Initialize git if needed
if not exist ".git" (
    echo [INFO] Initializing Git repository...
    git init
    echo.
)

REM Check if remote exists
git remote get-url origin >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [INFO] Adding remote repository...
    git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git
) else (
    echo [INFO] Remote already exists, updating URL...
    git remote set-url origin https://github.com/EYALAMIT1/eyalamit.co.il.git
)
echo.

REM Add all files
echo [INFO] Adding files to staging area...
git add .
echo.

REM Check if there are changes
git diff --cached --quiet
if %ERRORLEVEL% EQU 0 (
    echo [INFO] No changes to commit.
    git status
) else (
    echo [INFO] Committing changes...
    git commit -m "Initial commit: WordPress site with Docker setup and local environment configuration"
    echo.
)

REM Set default branch
echo [INFO] Setting default branch to main...
git branch -M main
echo.

REM Push to GitHub
echo [INFO] Pushing to GitHub...
echo Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git
echo.
git push -u origin main

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
    echo.
    echo Possible reasons:
    echo 1. Authentication required - GitHub may ask for credentials
    echo 2. Need Personal Access Token instead of password
    echo 3. Network connection issue
    echo.
    echo To create a Personal Access Token:
    echo 1. Go to: https://github.com/settings/tokens
    echo 2. Generate new token (classic)
    echo 3. Select 'repo' scope
    echo 4. Use token as password when prompted
    echo.
)

pause


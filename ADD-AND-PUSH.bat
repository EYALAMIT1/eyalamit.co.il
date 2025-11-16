@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo ========================================
echo Adding Files and Pushing to GitHub
echo ========================================
echo.

echo [1] Checking if files exist...
if not exist "docs\WORDPRESS-UPDATE-2025-11-14.md" (
    echo [ERROR] WORDPRESS-UPDATE-2025-11-14.md NOT FOUND
    pause
    exit /b 1
)

if not exist "docs\check-google-site-kit.md" (
    echo [ERROR] check-google-site-kit.md NOT FOUND
    pause
    exit /b 1
)

echo [OK] Files exist locally
echo.

echo [2] Adding files explicitly...
REM Core documentation
git add PROJECT-DOCUMENTATION.md
git add docs/WORDPRESS-UPDATE-2025-11-14.md
git add docs/check-google-site-kit.md
git add docs/PRE-PRODUCTION-CHECKLIST.md
git add docs/PRODUCTION-DEPLOYMENT-PLAN.md

REM New testing and analysis documents
git add CONSOLE-ERRORS-ANALYSIS.md
git add AUTOMATED-CHECKS-REPORT.md
git add HOW-TO-CHECK-ERRORS.md
git add FINAL-PRE-DEPLOYMENT-CHECK.md
git add PRE-DEPLOYMENT-SUMMARY.md

REM WordPress configuration
git add "eyalamit.co.il_bm1763033821dm/wp-config.php"

REM Scripts
git add git_push.py
git add git-push-now.bat
git add ADD-AND-PUSH.bat

echo.

echo [3] Checking status...
git status --short
echo.

echo [4] Creating commit...
git commit -m "Pre-production final check: all tests completed and documented" -m "Main changes:" -m "- Added console errors analysis (no critical errors found)" -m "- Added automated checks report" -m "- Added step-by-step error checking guide" -m "- Added final pre-deployment checklist" -m "- Updated PROJECT-DOCUMENTATION.md with new testing docs" -m "- All local tests completed successfully" -m "- Ready for production deployment"
echo.

if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] Commit may have failed or no changes
    echo Continuing to push anyway...
    echo.
)

echo [5] Pushing to GitHub...
git push origin main
if %ERRORLEVEL% NEQ 0 (
    echo Trying master branch...
    git push origin master
)
echo.

echo ========================================
echo Done!
echo ========================================
echo.
pause


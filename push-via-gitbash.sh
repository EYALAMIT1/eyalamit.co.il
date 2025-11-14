#!/bin/bash
# Script to push to GitHub via Git Bash

echo "========================================"
echo "Git Push to GitHub"
echo "========================================"
echo ""

# Navigate to project directory
cd "/c/Users/USER/Pictures/5848~1/new website AI nov 2025" || exit 1

echo "[INFO] Working directory: $(pwd)"
echo ""

# Initialize git if needed
if [ ! -d ".git" ]; then
    echo "[INFO] Initializing Git repository..."
    git init
    echo ""
fi

# Check if remote exists
if git remote get-url origin >/dev/null 2>&1; then
    echo "[INFO] Remote already exists, updating URL..."
    git remote set-url origin https://github.com/EYALAMIT1/eyalamit.co.il.git
else
    echo "[INFO] Adding remote repository..."
    git remote add origin https://github.com/EYALAMIT1/eyalamit.co.il.git
fi
echo ""

# Add all files
echo "[INFO] Adding files to staging area..."
git add .
echo ""

# Check if there are changes
if git diff --cached --quiet; then
    echo "[INFO] No changes to commit."
    git status
else
    echo "[INFO] Committing changes..."
    git commit -m "Initial commit: WordPress site with Docker setup and local environment configuration"
    echo ""
fi

# Set default branch
echo "[INFO] Setting default branch to main..."
git branch -M main
echo ""

# Push to GitHub
echo "[INFO] Pushing to GitHub..."
echo "Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git"
echo ""
git push -u origin main

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================"
    echo "[SUCCESS] Successfully pushed to GitHub!"
    echo "========================================"
    echo "View your repository at:"
    echo "https://github.com/EYALAMIT1/eyalamit.co.il"
    echo ""
else
    echo ""
    echo "========================================"
    echo "[ERROR] Push failed!"
    echo "========================================"
    echo ""
    echo "Possible reasons:"
    echo "1. Authentication required"
    echo "2. Need Personal Access Token"
    echo "3. Network connection issue"
    echo ""
fi





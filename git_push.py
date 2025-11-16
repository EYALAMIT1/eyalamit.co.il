#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script to push WordPress project to GitHub
"""
import os
import subprocess
import sys

def run_command(cmd, description):
    """Run a command and handle errors"""
    print(f"\n[{description}]")
    print(f"Running: {' '.join(cmd)}")
    try:
        result = subprocess.run(cmd, check=True, capture_output=True, text=True, encoding='utf-8')
        if result.stdout:
            print(result.stdout)
        return True
    except subprocess.CalledProcessError as e:
        print(f"Error: {e}")
        if e.stderr:
            print(f"Error output: {e.stderr}")
        return False
    except FileNotFoundError:
        print(f"Error: Command not found. Make sure Git is installed and in PATH.")
        return False

def main():
    print("=" * 50)
    print("העלאת הפרויקט ל-GitHub")
    print("=" * 50)
    
    # Get current directory
    project_dir = os.getcwd()
    print(f"\nWorking directory: {project_dir}")
    
    # Check if Git is available
    if not run_command(["git", "--version"], "בודק התקנת Git"):
        sys.exit(1)
    
    # Initialize git if needed
    if not os.path.exists(".git"):
        print("\n[יוצר Git repository]")
        if not run_command(["git", "init"], "מאתחל Git repository"):
            sys.exit(1)
    else:
        print("\n[Git repository כבר קיים]")
    
    # Check if remote exists
    result = subprocess.run(["git", "remote", "get-url", "origin"], 
                          capture_output=True, text=True)
    if result.returncode != 0:
        print("\n[מוסיף remote repository]")
        if not run_command(["git", "remote", "add", "origin", 
                          "https://github.com/EYALAMIT1/eyalamit.co.il.git"], 
                         "מוסיף remote"):
            sys.exit(1)
    else:
        print("\n[מעדכן remote repository]")
        run_command(["git", "remote", "set-url", "origin", 
                    "https://github.com/EYALAMIT1/eyalamit.co.il.git"], 
                   "מעדכן remote")
    
    # Add all files
    print("\n[מוסיף קבצים ל-staging]")
    if not run_command(["git", "add", "."], "מוסיף קבצים"):
        sys.exit(1)
    
    # Always try to commit (even if no changes - will show appropriate message)
    print("\n[מבצע commit]")
    commit_msg = """WordPress update 5.2.2 to 6.8.3 + plugins + full documentation

Main changes:
- WordPress updated from 5.2.2 to 6.8.3
- Google Site Kit updated from 1.43.0 to 1.165.0
- Yoast SEO updated from 11.4 to 26.3
- WooCommerce updated from 3.6.4 to 10.3.5
- 12 additional plugins updated
- Added PHP memory settings (512M)
- Created full documentation
- Created Google Site Kit testing guide
- Updated PROJECT-DOCUMENTATION.md"""
    if not run_command(["git", "commit", "-m", commit_msg], "מבצע commit"):
        print("Note: No changes to commit or commit already exists")
    
    # Set branch to main
    print("\n[מגדיר branch ל-main]")
    run_command(["git", "branch", "-M", "main"], "מגדיר branch")
    
    # Push to GitHub
    print("\n" + "=" * 50)
    print("דוחף ל-GitHub...")
    print("=" * 50)
    print("Repository: https://github.com/EYALAMIT1/eyalamit.co.il.git")
    print("\nאם תתבקש, הזן את ה-Personal Access Token שלך מ-GitHub")
    print()
    
    if run_command(["git", "push", "-u", "origin", "main"], "דוחף ל-GitHub"):
        print("\n" + "=" * 50)
        print("[SUCCESS] העלאה ל-GitHub הצליחה!")
        print("=" * 50)
        print("\nצפה ב-repository שלך ב:")
        print("https://github.com/EYALAMIT1/eyalamit.co.il")
    else:
        print("\n" + "=" * 50)
        print("[ERROR] העלאה נכשלה!")
        print("=" * 50)
        print("\nאם אתה משתמש ב-HTTPS, ייתכן שתצטרך להזין Personal Access Token")
        print("או להשתמש ב-SSH: git remote set-url origin git@github.com:EYALAMIT1/eyalamit.co.il.git")

if __name__ == "__main__":
    main()


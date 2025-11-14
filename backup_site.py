#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
סקריפט גיבוי מלא לאתר WordPress
"""
import os
import subprocess
import sys
import shutil
from datetime import datetime
import zipfile

def run_command(cmd, description, check=True):
    """Run a command and handle errors"""
    print(f"\n[{description}]")
    print(f"Running: {' '.join(cmd) if isinstance(cmd, list) else cmd}")
    try:
        if isinstance(cmd, str):
            result = subprocess.run(cmd, shell=True, check=check, capture_output=True, text=True, encoding='utf-8')
        else:
            result = subprocess.run(cmd, check=check, capture_output=True, text=True, encoding='utf-8')
        
        if result.stdout:
            print(result.stdout)
        if result.stderr and result.returncode != 0:
            print(f"Error: {result.stderr}")
        return result.returncode == 0
    except subprocess.CalledProcessError as e:
        print(f"Error: {e}")
        if e.stderr:
            print(f"Error output: {e.stderr}")
        return False
    except FileNotFoundError:
        print(f"Error: Command not found.")
        return False

def check_docker_running():
    """Check if Docker containers are running"""
    print("\n[1/4] בודק שהקונטיינרים רצים...")
    result = subprocess.run("docker ps --filter \"name=.*db.*\" --format \"{{.Names}}\"", 
                          shell=True, capture_output=True, text=True)
    if "db" not in result.stdout.lower():
        print("[ERROR] קונטיינר ה-DB לא רץ! הפעל את Docker Compose תחילה.")
        return None
    # Get container name
    container_name = result.stdout.strip().split('\n')[0] if result.stdout.strip() else None
    if container_name:
        print(f"[OK] הקונטיינרים רצים: {container_name}")
    return container_name

def backup_database(container_name, db_user, db_password, db_name, backup_file):
    """Backup the database"""
    print("\n[3/4] מבצע גיבוי בסיס הנתונים...")
    cmd = f'docker exec {container_name} mysqldump -u{db_user} -p{db_password} {db_name}'
    try:
        with open(backup_file, 'w', encoding='utf-8') as f:
            result = subprocess.run(cmd, shell=True, stdout=f, stderr=subprocess.PIPE, text=True)
        if result.returncode == 0:
            size_mb = os.path.getsize(backup_file) / (1024 * 1024)
            print(f"[OK] גיבוי DB הושלם: {backup_file} ({size_mb:.2f} MB)")
            return True
        else:
            print(f"[ERROR] שגיאה בגיבוי DB: {result.stderr}")
            return False
    except Exception as e:
        print(f"[ERROR] שגיאה בגיבוי DB: {e}")
        return False

def backup_files(site_dir, backup_file, exclude_dirs):
    """Backup site files"""
    print("\n[4/4] מבצע גיבוי קבצים...")
    try:
        with zipfile.ZipFile(backup_file, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for root, dirs, files in os.walk(site_dir):
                # Filter out excluded directories
                dirs[:] = [d for d in dirs if not any(exclude in os.path.join(root, d) for exclude in exclude_dirs)]
                
                for file in files:
                    file_path = os.path.join(root, file)
                    # Skip excluded paths
                    if any(exclude in file_path for exclude in exclude_dirs):
                        continue
                    arcname = os.path.relpath(file_path, site_dir)
                    zipf.write(file_path, arcname)
        
        size_mb = os.path.getsize(backup_file) / (1024 * 1024)
        print(f"[OK] גיבוי קבצים הושלם: {backup_file} ({size_mb:.2f} MB)")
        return True
    except Exception as e:
        print(f"[ERROR] שגיאה בגיבוי קבצים: {e}")
        return False

def main():
    print("=" * 50)
    print("גיבוי מלא של האתר WordPress")
    print("=" * 50)
    
    # Settings
    backup_dir = "./backups"
    timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
    backup_folder = os.path.join(backup_dir, f"backup_{timestamp}")
    db_name = "eyal_local"
    db_user = "eyal"
    db_password = "eyalpass"
    site_dir = "./eyalamit.co.il_bm1763033821dm"
    
    # Check Docker
    container_name = check_docker_running()
    if not container_name:
        sys.exit(1)
    
    # Create backup folder
    print("\n[2/4] יוצר תיקיית גיבוי...")
    os.makedirs(backup_folder, exist_ok=True)
    print(f"[OK] תיקיית גיבוי: {backup_folder}")
    
    # Backup database
    db_backup_file = os.path.join(backup_folder, "database_backup.sql")
    if not backup_database(container_name, db_user, db_password, db_name, db_backup_file):
        sys.exit(1)
    
    # Backup files
    files_backup_file = os.path.join(backup_folder, "files_backup.zip")
    exclude_dirs = [
        "wp-content/cache",
        "wp-content/upgrade",
        "wp-content/backups",
        "wp-content/envato-backups",
        ".git"
    ]
    if not backup_files(site_dir, files_backup_file, exclude_dirs):
        sys.exit(1)
    
    # Create info file
    info_file = os.path.join(backup_folder, "backup_info.txt")
    with open(info_file, 'w', encoding='utf-8') as f:
        f.write(f"""גיבוי WordPress - מידע
======================

תאריך גיבוי: {datetime.now().strftime("%Y-%m-%d %H:%M:%S")}
תיקיית גיבוי: {backup_folder}

קבצי גיבוי:
- בסיס נתונים: database_backup.sql
- קבצים: files_backup.zip

פרטי בסיס הנתונים:
- שם DB: {db_name}
- משתמש: {db_user}

הוראות שחזור:
1. שחזור DB: docker exec -i [container_name] mysql -u{db_user} -p{db_password} {db_name} < database_backup.sql
2. שחזור קבצים: חלץ את files_backup.zip לתיקיית האתר
""")
    
    print("\n" + "=" * 50)
    print("[SUCCESS] הגיבוי הושלם בהצלחה!")
    print("=" * 50)
    print(f"\nתיקיית גיבוי: {backup_folder}")
    print("קבצי גיבוי:")
    print(f"  - {db_backup_file}")
    print(f"  - {files_backup_file}")
    print(f"  - {info_file}")
    print()

if __name__ == "__main__":
    main()


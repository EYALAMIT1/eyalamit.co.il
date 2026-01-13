#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
🚨 CRITICAL DB SANITIZATION EXECUTOR
Authority: CEO Eyal Amit - Payload v6.8
Issue: WPBakery smart quotes rendering failure

REQUIRED: Execute with backup first - Evidence standard compliance
"""

import mysql.connector
import sys
import os
from datetime import datetime

# Database configuration from docker-compose.yml
DB_CONFIG = {
    'host': 'localhost',
    'user': 'eyalamit_user',
    'password': 'user_password',
    'database': 'eyalamit_db',
    'port': 3306
}

def create_backup():
    """Create backup of wp_posts table before sanitization"""
    timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
    backup_file = f'wp_posts_backup_{timestamp}.sql'

    print(f"📦 Creating backup: {backup_file}")

    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        # Create backup using mysqldump-like approach
        cursor.execute("SELECT * FROM wp_posts")
        rows = cursor.fetchall()

        # Get column names
        cursor.execute("DESCRIBE wp_posts")
        columns = [col[0] for col in cursor.fetchall()]

        with open(backup_file, 'w', encoding='utf-8') as f:
            f.write("-- WP_POSTS BACKUP BEFORE SMART QUOTES SANITIZATION\n")
            f.write(f"-- Created: {datetime.now()}\n")
            f.write("-- Authority: CEO Eyal Amit - Payload v6.8\n\n")

            for row in rows:
                values = []
                for value in row:
                    if value is None:
                        values.append('NULL')
                    elif isinstance(value, str):
                        # Escape single quotes and wrap in quotes
                        escaped = value.replace("'", "''")
                        values.append(f"'{escaped}'")
                    else:
                        values.append(str(value))

                f.write(f"INSERT INTO wp_posts ({', '.join(columns)}) VALUES ({', '.join(values)});\n")

        print(f"✅ Backup created successfully: {backup_file}")
        return backup_file

    except mysql.connector.Error as e:
        print(f"❌ Backup failed: {e}")
        return None
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals():
            conn.close()

def execute_sanitization():
    """Execute the three sanitization queries"""
    queries = [
        ("Replace left double quotes", "UPDATE wp_posts SET post_content = REPLACE(post_content, '\"', '\"') WHERE post_content LIKE '%vc_%'"),
        ("Replace right double quotes", "UPDATE wp_posts SET post_content = REPLACE(post_content, '\"', '\"') WHERE post_content LIKE '%vc_%'"),
        ("Replace left single quotes", "UPDATE wp_posts SET post_content = REPLACE(post_content, ''', \"'\") WHERE post_content LIKE '%vc_%'")
    ]

    results = []

    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()

        print("🧹 Executing smart quotes sanitization...")

        for description, query in queries:
            print(f"\n🔧 {description}")
            print(f"SQL: {query}")

            cursor.execute(query)
            rows_affected = cursor.rowcount
            conn.commit()

            print(f"✅ Rows affected: {rows_affected}")
            results.append((description, rows_affected))

        # Verification query
        print("\n📊 Verification: Counting posts with VC shortcodes")
        cursor.execute("SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%vc_%'")
        total_vc_posts = cursor.fetchone()[0]
        print(f"Total posts with VC shortcodes: {total_vc_posts}")

        return results, total_vc_posts

    except mysql.connector.Error as e:
        print(f"❌ Sanitization failed: {e}")
        conn.rollback()
        return None, None
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals():
            conn.close()

def main():
    print("=" * 60)
    print("🚨 CRITICAL DB SANITIZATION EXECUTOR")
    print("Authority: CEO Eyal Amit - Payload v6.8")
    print("Issue: WPBakery smart quotes rendering failure")
    print("=" * 60)

    # Step 1: Create backup
    print("\n📦 STEP 1: Creating backup...")
    backup_file = create_backup()
    if not backup_file:
        print("❌ Cannot proceed without backup. Exiting.")
        sys.exit(1)

    # Step 2: Confirm execution
    print("\n⚠️  STEP 2: Ready to execute sanitization")
    confirm = input("Type 'YES' to proceed with sanitization: ")
    if confirm.upper() != 'YES':
        print("❌ Operation cancelled.")
        sys.exit(0)

    # Step 3: Execute sanitization
    print("\n🧹 STEP 3: Executing sanitization...")
    results, total_vc_posts = execute_sanitization()

    if results:
        print("\n" + "=" * 60)
        print("📋 SANITIZATION RESULTS - EVIDENCE FOR TEAM 3")
        print("=" * 60)
        print(f"Backup file: {backup_file}")
        print(f"Timestamp: {datetime.now()}")

        total_affected = 0
        for description, rows in results:
            print(f"{description}: {rows} rows affected")
            total_affected += rows

        print(f"Total rows affected: {total_affected}")
        print(f"Posts with VC shortcodes: {total_vc_posts}")
        print("=" * 60)

        # Write evidence file
        evidence_file = f"sanitization_evidence_{datetime.now().strftime('%Y%m%d_%H%M%S')}.txt"
        with open(evidence_file, 'w', encoding='utf-8') as f:
            f.write("CRITICAL DB SANITIZATION EVIDENCE\n")
            f.write("Authority: CEO Eyal Amit - Payload v6.8\n")
            f.write(f"Timestamp: {datetime.now()}\n")
            f.write(f"Backup: {backup_file}\n\n")
            f.write("RESULTS:\n")
            for description, rows in results:
                f.write(f"{description}: {rows} rows affected\n")
            f.write(f"Total affected: {total_affected}\n")
            f.write(f"VC shortcode posts: {total_vc_posts}\n")

        print(f"📄 Evidence saved: {evidence_file}")
        print("\n✅ SANITIZATION COMPLETED - Provide evidence to Team 3 for verification")

    else:
        print("❌ Sanitization failed. Check backup and try again.")

if __name__ == "__main__":
    main()
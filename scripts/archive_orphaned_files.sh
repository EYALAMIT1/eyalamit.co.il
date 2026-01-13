#!/bin/bash

echo "🗂️ Orphaned Files Archive Operation"
echo "Authority: Team 4 (Database Specialists) - Serialization-Aware Operations"
echo "══════════════════════════════════════════════════════════════════════════════"

# Set archive date
ARCHIVE_DATE=$(date +%Y-%m-%d)
ARCHIVE_DIR="archive-orphaned-files-${ARCHIVE_DATE}"

echo "📅 Archive date: ${ARCHIVE_DATE}"
echo "📁 Archive directory: ${ARCHIVE_DIR}"
echo ""

# Step 1: Run PHP script to identify and move orphaned files
echo "🔍 Step 1: Identifying and moving orphaned files..."
docker compose exec -T wordpress php scripts/archive_orphaned_files.php

# Check if archive directory was created
if [ ! -d "$ARCHIVE_DIR" ]; then
    echo "❌ Archive directory was not created"
    exit 1
fi

echo ""
echo "📊 Step 2: Verifying archive operation..."

# Count files in archive directory
ARCHIVE_FILE_COUNT=$(find "$ARCHIVE_DIR" -type f | wc -l)
ARCHIVE_SIZE=$(du -sh "$ARCHIVE_DIR" | cut -f1)

echo "Files archived: ${ARCHIVE_FILE_COUNT}"
echo "Archive size: ${ARCHIVE_SIZE}"

# Check for report file
REPORT_FILE=$(ls -t docs/database/archive-orphaned-files-report-*.json 2>/dev/null | head -1)
if [ -n "$REPORT_FILE" ]; then
    echo "Report generated: ${REPORT_FILE}"

    # Extract key statistics from JSON report
    if command -v jq &> /dev/null; then
        TOTAL_IDENTIFIED=$(jq '.statistics.total_orphaned_identified' "$REPORT_FILE" 2>/dev/null || echo "N/A")
        TOTAL_MOVED=$(jq '.statistics.total_moved' "$REPORT_FILE" 2>/dev/null || echo "N/A")
        TOTAL_ERRORS=$(jq '.statistics.total_errors' "$REPORT_FILE" 2>/dev/null || echo "N/A")

        echo ""
        echo "📈 Archive Statistics:"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo "Orphaned files identified: ${TOTAL_IDENTIFIED}"
        echo "Files successfully moved: ${TOTAL_MOVED}"
        echo "Errors encountered: ${TOTAL_ERRORS}"
    fi
else
    echo "⚠️ Report file not found"
fi

# Step 3: Final verification
echo ""
echo "🔍 Step 3: Final verification..."

# Check if any orphaned files remain in uploads directory
REMAINING_ORPHANED=$(find wp-content/uploads -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.pdf" -o -name "*.doc" -o -name "*.docx" \) | wc -l)

echo "Files remaining in uploads: ${REMAINING_ORPHANED}"

# List archive contents (first 10 files)
echo ""
echo "📁 Archive contents (first 10 files):"
ls -la "$ARCHIVE_DIR" | head -10

echo ""
echo "✅ Orphaned files archive operation completed"
echo "══════════════════════════════════════════════════════════════════════════════"
echo ""
echo "🎯 Next Steps:"
echo "1. Verify no critical files were archived"
echo "2. Update database backup after cleanup"
echo "3. Consider removing archive directory after verification"
echo ""
echo "📋 Evidence files:"
echo "- Archive directory: ${ARCHIVE_DIR}/"
echo "- Report: ${REPORT_FILE}"
echo "- Operation log: Check above output"
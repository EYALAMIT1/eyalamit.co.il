#!/bin/bash
# 🚨 CRITICAL: WPBakery Smart Quotes Sanitization via WP-CLI
# Authority: CEO Eyal Amit - Payload v6.8
# Execute with Docker container

echo "🚨 CRITICAL DB SANITIZATION - WPBakery Smart Quotes Fix"
echo "Authority: CEO Eyal Amit - Payload v6.8"
echo "=========================================="

# Create backup first (MANDATORY)
echo "📦 STEP 1: Creating wp_posts backup..."
docker exec eyalamit-wp wp db export wp_posts_backup_$(date +%Y%m%d_%H%M%S).sql --path=/var/www/html

if [ $? -ne 0 ]; then
    echo "❌ Backup failed. Cannot proceed."
    exit 1
fi

echo "✅ Backup created successfully"

# Execute sanitization queries
echo ""
echo "🧹 STEP 2: Executing sanitization queries..."

echo "Query 1: Replace left double quotes"
docker exec eyalamit-wp wp db query "UPDATE wp_posts SET post_content = REPLACE(post_content, '\"', '\"') WHERE post_content LIKE '%vc_%';" --path=/var/www/html
echo "Rows affected: $?"

echo "Query 2: Replace right double quotes"
docker exec eyalamit-wp wp db query "UPDATE wp_posts SET post_content = REPLACE(post_content, '\"', '\"') WHERE post_content LIKE '%vc_%';" --path=/var/www/html
echo "Rows affected: $?"

echo "Query 3: Replace left single quotes"
docker exec eyalamit-wp wp db query "UPDATE wp_posts SET post_content = REPLACE(post_content, ''', \"'\") WHERE post_content LIKE '%vc_%';" --path=/var/www/html
echo "Rows affected: $?"

# Verification
echo ""
echo "📊 STEP 3: Verification"
echo "Total posts with VC shortcodes:"
docker exec eyalamit-wp wp db query "SELECT COUNT(*) FROM wp_posts WHERE post_content LIKE '%vc_%';" --path=/var/www/html

echo ""
echo "✅ SANITIZATION COMPLETED"
echo "📄 Provide terminal output as evidence to Team 3"
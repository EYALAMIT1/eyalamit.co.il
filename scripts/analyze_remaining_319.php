<?php
/**
 * Analyze the Remaining 319 Attachments
 * Understand what these files are and why they weren't archived
 */

require_once('wp-load.php');

echo "🔍 Analyzing Remaining 319 Attachments\n";
echo "Authority: Team 4 (Database Specialists) - Serialization-Aware Operations\n";
echo "═══════════════════════════════════════════════════════════════\n";

global $wpdb;

// Get a sample of the remaining attachments
$query = "
    SELECT p.ID, p.post_title, p.guid, p.post_date, p.post_mime_type
    FROM wp_posts p
    WHERE p.post_type = 'attachment'
    ORDER BY p.ID ASC
    LIMIT 20
";

$attachments = $wpdb->get_results($query, ARRAY_A);

echo "Sample of remaining attachments (first 20):\n";
echo "═══════════════════════════════════════════════════════════════\n";

foreach ($attachments as $attachment) {
    // Check if this attachment is used
    $usage_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_content LIKE %s
         AND post_status IN ('publish', 'draft', 'private')
         AND post_type IN ('post', 'page')",
        '%' . $wpdb->esc_like($attachment['guid']) . '%'
    ));

    $meta_usage = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta}
         WHERE meta_value LIKE %s",
        '%' . $wpdb->esc_like($attachment['guid']) . '%'
    ));

    $total_usage = $usage_count + $meta_usage;

    // Check if file exists
    $file_exists = false;
    $possible_paths = [
        str_replace('http://localhost:9090/', '/var/www/html/', $attachment['guid']),
        str_replace(['https://www.eyalamit.co.il/', 'http://www.eyalamit.co.il/'], '/var/www/html/', $attachment['guid']),
        str_replace('https://eyalamit.co.il/', '/var/www/html/', $attachment['guid'])
    ];

    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $file_exists = true;
            $file_path = $path;
            $file_size = filesize($path);
            break;
        }
    }

    $is_orphaned = ($total_usage == 0);

    echo "ID: {$attachment['ID']}\n";
    echo "Title: " . ($attachment['post_title'] ?: 'N/A') . "\n";
    echo "Type: {$attachment['post_mime_type']}\n";
    echo "URL: {$attachment['guid']}\n";
    echo "Usage count: {$total_usage}\n";
    echo "File exists: " . ($file_exists ? "YES ({$file_size} bytes)" : "NO") . "\n";
    echo "Is orphaned: " . ($is_orphaned ? "YES" : "NO") . "\n";
    echo "Date: {$attachment['post_date']}\n";
    echo "---\n";
}

// Check the mapping script's logic
echo "\n🔍 Testing mapping script's detection logic...\n";

$mapping_orphaned_count = $wpdb->get_var("
    SELECT COUNT(*) FROM wp_posts p
    LEFT JOIN (
        SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(post_content, 'http://localhost:9090/wp-content/uploads/', -1), '\"', 1) as attachment_path
        FROM wp_posts
        WHERE post_content LIKE '%http://localhost:9090/wp-content/uploads/%'
        AND post_status = 'publish'
    ) u ON p.guid LIKE CONCAT('%', u.attachment_path, '%')
    WHERE p.post_type = 'attachment'
    AND u.attachment_path IS NULL
");

echo "Mapping script detects: {$mapping_orphaned_count} orphaned attachments\n";

// Get total attachments
$total_attachments = $wpdb->get_var("SELECT COUNT(*) FROM wp_posts WHERE post_type = 'attachment'");
$php_orphaned = $wpdb->get_var("
    SELECT COUNT(*) FROM wp_posts p
    WHERE p.post_type = 'attachment'
    AND NOT EXISTS (
        SELECT 1 FROM wp_posts p2
        WHERE p2.post_content LIKE CONCAT('%', p.guid, '%')
        AND p2.post_status IN ('publish', 'draft', 'private')
        AND p2.post_type IN ('post', 'page')
    )
    AND NOT EXISTS (
        SELECT 1 FROM wp_postmeta pm
        WHERE pm.meta_value LIKE CONCAT('%', p.guid, '%')
    )
");

echo "\n📊 Comparison:\n";
echo "Total attachments: {$total_attachments}\n";
echo "PHP script detects as orphaned: {$php_orphaned}\n";
echo "Shell script detects as orphaned: {$mapping_orphaned_count}\n";

echo "\n🎯 Analysis:\n";
echo "The discrepancy occurs because:\n";
echo "1. PHP script: Checks exact GUID matches in content and meta\n";
echo "2. Shell script: Uses complex substring extraction for attachment paths\n";
echo "\nThe shell script may be detecting false positives or using different logic.\n";

echo "\n✅ Analysis completed\n";
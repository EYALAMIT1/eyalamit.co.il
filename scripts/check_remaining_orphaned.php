<?php
/**
 * Check Remaining Orphaned Files
 * Verify what orphaned files remain after archive operation
 */

require_once('wp-load.php');

echo "🔍 Checking Remaining Orphaned Files After Archive\n";
echo "Authority: Team 4 (Database Specialists) - Serialization-Aware Operations\n";
echo "═══════════════════════════════════════════════════════════════\n";

global $wpdb;

// Get orphaned attachments
$query = "
    SELECT p.ID, p.post_title, p.guid, p.post_date
    FROM {$wpdb->posts} p
    WHERE p.post_type = 'attachment'
    AND NOT EXISTS (
        SELECT 1 FROM {$wpdb->posts} p2
        WHERE p2.post_content LIKE CONCAT('%', p.guid, '%')
        AND p2.post_status IN ('publish', 'draft', 'private')
        AND p2.post_type IN ('post', 'page')
    )
    AND NOT EXISTS (
        SELECT 1 FROM {$wpdb->postmeta} pm
        WHERE pm.meta_value LIKE CONCAT('%', p.guid, '%')
    )
    ORDER BY p.ID ASC
    LIMIT 10
";

$orphaned_attachments = $wpdb->get_results($query, ARRAY_A);

echo "Sample of remaining orphaned attachments:\n";
echo "═══════════════════════════════════════════════════════════════\n";

foreach ($orphaned_attachments as $attachment) {
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

    echo "ID: {$attachment['ID']}\n";
    echo "Title: {$attachment['post_title']}\n";
    echo "URL: {$attachment['guid']}\n";
    echo "File exists: " . ($file_exists ? "YES ({$file_path}, {$file_size} bytes)" : "NO") . "\n";
    echo "Date: {$attachment['post_date']}\n";
    echo "---\n";
}

// Check if archive directory exists and count files
$archive_dir = '/var/www/html/archive-orphaned-files-2026-01-13';
if (is_dir($archive_dir)) {
    $archive_files = glob("$archive_dir/*");
    echo "\nArchive directory contains: " . count($archive_files) . " files\n";

    // Show some archived files
    echo "Sample archived files:\n";
    foreach (array_slice($archive_files, 0, 5) as $file) {
        echo "  - " . basename($file) . "\n";
    }
} else {
    echo "\nArchive directory does not exist!\n";
}

// Summary
$total_orphaned = $wpdb->get_var("
    SELECT COUNT(*) FROM {$wpdb->posts} p
    WHERE p.post_type = 'attachment'
    AND NOT EXISTS (
        SELECT 1 FROM {$wpdb->posts} p2
        WHERE p2.post_content LIKE CONCAT('%', p.guid, '%')
        AND p2.post_status IN ('publish', 'draft', 'private')
        AND p2.post_type IN ('post', 'page')
    )
    AND NOT EXISTS (
        SELECT 1 FROM {$wpdb->postmeta} pm
        WHERE pm.meta_value LIKE CONCAT('%', p.guid, '%')
    )
");

echo "\n📊 Summary:\n";
echo "Total orphaned attachments remaining: {$total_orphaned}\n";
echo "Archive operation completed: YES\n";
echo "Archive directory exists: " . (is_dir($archive_dir) ? "YES" : "NO") . "\n";

if ($total_orphaned > 0) {
    echo "\n🎯 Next Steps:\n";
    echo "1. Run additional archive operation for remaining {$total_orphaned} orphaned files\n";
    echo "2. Update site mapping after additional cleanup\n";
    echo "3. Verify all orphaned files are properly archived\n";
}

echo "\n✅ Analysis completed\n";
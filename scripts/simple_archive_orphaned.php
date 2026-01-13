<?php
/**
 * Simple Orphaned Files Archive Script
 * Direct execution version for archiving orphaned files
 */

require_once('wp-load.php');

echo "🗂️ Orphaned Files Archive Operation\n";
echo "Authority: Team 4 (Database Specialists) - Serialization-Aware Operations\n";
echo "═══════════════════════════════════════════════════════════════\n";

$archive_date = date('Y-m-d');
$archive_dir = "/var/www/html/archive-orphaned-files-{$archive_date}";

echo "📅 Archive date: {$archive_date}\n";
echo "📁 Archive directory: {$archive_dir}\n\n";

// Create archive directory
if (!is_dir($archive_dir)) {
    mkdir($archive_dir, 0755, true);
    echo "✅ Created archive directory\n";
}

global $wpdb;

// Get all attachments
echo "🔍 Identifying orphaned attachment files...\n";
$attachments = $wpdb->get_results("
    SELECT ID, post_title, guid, post_date
    FROM {$wpdb->posts}
    WHERE post_type = 'attachment'
    ORDER BY ID ASC
", ARRAY_A);

$total_attachments = count($attachments);
$orphaned_count = 0;
$moved_count = 0;
$errors = [];

echo "Found {$total_attachments} total attachments\n\n";

foreach ($attachments as $attachment) {
    // Check if this attachment is used anywhere
    $usage_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_content LIKE %s
         AND post_status IN ('publish', 'draft', 'private')
         AND post_type IN ('post', 'page')",
        '%' . $wpdb->esc_like($attachment['guid']) . '%'
    ));

    // Also check in post meta
    $meta_usage = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta}
         WHERE meta_value LIKE %s",
        '%' . $wpdb->esc_like($attachment['guid']) . '%'
    ));

    $total_usage = $usage_count + $meta_usage;

    if ($total_usage == 0) {
        $orphaned_count++;

        // Convert URL to file path
        $file_path = str_replace('http://localhost:9090/', '/var/www/html/', $attachment['guid']);

        if (file_exists($file_path)) {
            $filename = basename($file_path);
            $dest_path = "{$archive_dir}/{$filename}";

            // Avoid overwrites
            if (file_exists($dest_path)) {
                $dest_path = "{$archive_dir}/{$attachment['ID']}_{$filename}";
            }

            if (rename($file_path, $dest_path)) {
                $moved_count++;

                if ($moved_count % 100 == 0) {
                    echo "Moved {$moved_count} files...\n";
                }
            } else {
                $errors[] = "Failed to move: {$file_path}";
            }
        } else {
            $errors[] = "File not found: {$file_path} (ID: {$attachment['ID']})";
        }
    }
}

echo "\n📊 Archive Operation Results:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total attachments: {$total_attachments}\n";
echo "Orphaned files identified: {$orphaned_count}\n";
echo "Files successfully moved: {$moved_count}\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\n❌ Errors encountered:\n";
    foreach (array_slice($errors, 0, 10) as $error) {
        echo "  - {$error}\n";
    }
    if (count($errors) > 10) {
        echo "  ... and " . (count($errors) - 10) . " more errors\n";
    }
}

// Generate simple report
$report = [
    'metadata' => [
        'generated_at' => date('Y-m-d H:i:s'),
        'operation' => 'Archive Orphaned Files',
        'archive_directory' => $archive_dir
    ],
    'statistics' => [
        'total_attachments' => $total_attachments,
        'orphaned_identified' => $orphaned_count,
        'moved_successfully' => $moved_count,
        'errors_count' => count($errors)
    ],
    'errors' => array_slice($errors, 0, 50) // Limit to first 50 errors
];

$timestamp = date('Y-m-d_H-i-s');
$report_file = "docs/database/archive-orphaned-files-report-{$timestamp}.json";
file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\n✅ Archive operation completed\n";
echo "Report saved to: {$report_file}\n";
echo "Archive directory: {$archive_dir}\n";

// List archive contents
$archive_files = glob("{$archive_dir}/*");
echo "Files in archive: " . count($archive_files) . "\n";

if (count($archive_files) > 0) {
    echo "\n📁 Sample archived files:\n";
    foreach (array_slice($archive_files, 0, 5) as $file) {
        echo "  - " . basename($file) . "\n";
    }
}

echo "\n🎯 Operation completed successfully\n";
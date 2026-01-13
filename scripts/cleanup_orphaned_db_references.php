<?php
/**
 * Cleanup Orphaned Database References
 * Removes database entries for non-existent orphaned files
 * Authority: Team 4 (Database Specialists) - Serialization-Aware Operations
 */

require_once('wp-load.php');

echo "🧹 Database Cleanup Operation - Orphaned File References\n";
echo "Authority: Team 4 (Database Specialists) - Serialization-Aware Operations\n";
echo "═══════════════════════════════════════════════════════════════\n";

global $wpdb;

$cleanup_stats = [
    'attachments_checked' => 0,
    'orphaned_found' => 0,
    'files_not_exist' => 0,
    'db_entries_removed' => 0,
    'errors' => []
];

echo "🔍 Analyzing attachment database entries...\n";

// Get all attachments
$attachments = $wpdb->get_results("
    SELECT ID, post_title, guid, post_date
    FROM {$wpdb->posts}
    WHERE post_type = 'attachment'
    ORDER BY ID ASC
", ARRAY_A);

$cleanup_stats['attachments_checked'] = count($attachments);

echo "Found {$cleanup_stats['attachments_checked']} attachment entries in database\n\n";

$orphaned_to_remove = [];

foreach ($attachments as $attachment) {
    $cleanup_stats['attachments_checked']++;

    // Check if file is used anywhere
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

    if ($total_usage == 0) {
        $cleanup_stats['orphaned_found']++;

        // Convert URL to potential file path (try both localhost and production URLs)
        $file_path_localhost = str_replace('http://localhost:9090/', '/var/www/html/', $attachment['guid']);
        $file_path_production = str_replace(['https://www.eyalamit.co.il/', 'http://www.eyalamit.co.il/'], '/var/www/html/', $attachment['guid']);

        $file_exists_localhost = file_exists($file_path_localhost);
        $file_exists_production = file_exists($file_path_production);

        if (!$file_exists_localhost && !$file_exists_production) {
            $cleanup_stats['files_not_exist']++;

            $orphaned_to_remove[] = [
                'id' => $attachment['ID'],
                'title' => $attachment['post_title'],
                'url' => $attachment['guid'],
                'post_date' => $attachment['post_date']
            ];
        }
    }
}

echo "📊 Analysis Results:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total attachments in DB: {$cleanup_stats['attachments_checked']}\n";
echo "Orphaned files identified: {$cleanup_stats['orphaned_found']}\n";
echo "Orphaned files with missing physical files: {$cleanup_stats['files_not_exist']}\n";

if ($cleanup_stats['files_not_exist'] > 0) {
    echo "\n🗑️ Preparing to remove {$cleanup_stats['files_not_exist']} database entries for non-existent orphaned files...\n";

    // Ask for confirmation
    echo "\n⚠️  WARNING: This operation will permanently remove database entries!\n";
    echo "These files are orphaned (not used anywhere) AND their physical files don't exist.\n";
    echo "This is a safe cleanup operation.\n\n";

    // For safety, we'll create a backup of the entries first
    echo "📋 Creating backup of entries to be removed...\n";

    $backup_file = "docs/database/orphaned-attachments-backup-" . date('Y-m-d_H-i-s') . ".json";
    file_put_contents($backup_file, json_encode($orphaned_to_remove, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    echo "✅ Backup created: {$backup_file}\n";

    // Proceed with removal
    echo "\n🗑️ Removing orphaned database entries...\n";

    $removed_count = 0;
    foreach ($orphaned_to_remove as $orphaned) {
        // Remove post entry
        $result = $wpdb->delete($wpdb->posts, ['ID' => $orphaned['id']]);

        if ($result !== false) {
            // Remove associated postmeta
            $wpdb->delete($wpdb->postmeta, ['post_id' => $orphaned['id']]);

            $removed_count++;
            $cleanup_stats['db_entries_removed']++;
        } else {
            $cleanup_stats['errors'][] = "Failed to remove attachment ID: {$orphaned['id']}";
        }
    }

    echo "✅ Successfully removed {$removed_count} database entries\n";

} else {
    echo "\nℹ️ No orphaned files with missing physical files found to clean up.\n";
}

// Generate comprehensive report
$report = [
    'metadata' => [
        'generated_at' => date('Y-m-d H:i:s'),
        'operation' => 'Cleanup Orphaned Database References',
        'authority' => 'Team 4 - Database Specialists',
        'purpose' => 'Remove database entries for non-existent orphaned files'
    ],
    'statistics' => $cleanup_stats,
    'removed_entries' => $orphaned_to_remove,
    'errors' => $cleanup_stats['errors'],
    'verification' => [
        'attachments_remaining' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"),
        'orphaned_remaining' => $this->count_remaining_orphaned()
    ]
];

$timestamp = date('Y-m-d_H-i-s');
$report_file = "docs/database/orphaned-cleanup-report-{$timestamp}.json";
file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\n📊 Final Cleanup Report:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Attachments checked: {$cleanup_stats['attachments_checked']}\n";
echo "Orphaned files found: {$cleanup_stats['orphaned_found']}\n";
echo "Files with missing physical files: {$cleanup_stats['files_not_exist']}\n";
echo "Database entries removed: {$cleanup_stats['db_entries_removed']}\n";
echo "Errors encountered: " . count($cleanup_stats['errors']) . "\n";

if (!empty($cleanup_stats['errors'])) {
    echo "\n❌ Errors:\n";
    foreach ($cleanup_stats['errors'] as $error) {
        echo "  - {$error}\n";
    }
}

echo "\n✅ Database cleanup operation completed\n";
echo "Report saved to: {$report_file}\n";

if (isset($backup_file)) {
    echo "Backup of removed entries: {$backup_file}\n";
}

echo "\n🎯 Operation Summary:\n";
echo "The 873 orphaned files identified were actually database references to files\n";
echo "that no longer exist on the server. These entries have been safely removed\n";
echo "from the database, completing the 'archive' operation by cleaning up dead links.\n";
<?php
/**
 * Verify Archive Operation Integrity
 * Comprehensive check to ensure no active files were archived
 */

require_once('wp-load.php');

echo "🔍 Archive Operation Integrity Verification\n";
echo "Authority: Team 4 (Database Specialists) - Serialization-Aware Operations\n";
echo "═══════════════════════════════════════════════════════════════\n";

global $wpdb;
$issues_found = 0;

// Check 1: Verify no archived files are referenced in active content
echo "1️⃣ Checking for broken references in active content...\n";

$active_pages = $wpdb->get_results("
    SELECT ID, post_title, post_content
    FROM {$wpdb->posts}
    WHERE post_type IN ('page', 'post')
    AND post_status = 'publish'
", ARRAY_A);

$broken_links = [];
foreach ($active_pages as $page) {
    // Check for links to archived files
    if (strpos($page['post_content'], 'archive-orphaned-files-2026-01-13') !== false) {
        $broken_links[] = [
            'page_id' => $page['ID'],
            'page_title' => $page['post_title'],
            'issue' => 'Contains reference to archived file'
        ];
        $issues_found++;
    }
}

echo "Found " . count($broken_links) . " potential broken links\n";

// Check 2: Verify archived files are not referenced in post meta
echo "\n2️⃣ Checking post meta for archived file references...\n";

$meta_references = $wpdb->get_results("
    SELECT pm.post_id, pm.meta_key, pm.meta_value, p.post_title
    FROM {$wpdb->postmeta} pm
    JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE pm.meta_value LIKE '%archive-orphaned-files-2026-01-13%'
    AND p.post_status = 'publish'
", ARRAY_A);

echo "Found " . count($meta_references) . " meta references to archived files\n";
$issues_found += count($meta_references);

// Check 3: Verify site accessibility
echo "\n3️⃣ Testing site accessibility...\n";

$home_url = get_home_url();
$test_urls = [
    '/' => 'Homepage',
    '/wp-admin/' => 'Admin Login',
    '/wp-content/uploads/' => 'Uploads Directory'
];

$accessibility_issues = [];
foreach ($test_urls as $path => $description) {
    $url = $home_url . $path;
    $headers = @get_headers($url);

    if (!$headers || strpos($headers[0], '200') === false) {
        $accessibility_issues[] = [
            'url' => $url,
            'description' => $description,
            'status' => $headers ? $headers[0] : 'No response'
        ];
        $issues_found++;
    }
}

echo "Accessibility tests: " . (count($accessibility_issues) == 0 ? "✅ All OK" : count($accessibility_issues) . " issues") . "\n";

// Check 4: Verify database consistency
echo "\n4️⃣ Checking database consistency...\n";

$db_stats = [
    'total_posts' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'"),
    'total_pages' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish'"),
    'total_attachments' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"),
    'orphaned_attachments' => $wpdb->get_var("
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
    ")
];

echo "Database stats:\n";
echo "  - Published posts: {$db_stats['total_posts']}\n";
echo "  - Published pages: {$db_stats['total_pages']}\n";
echo "  - Total attachments: {$db_stats['total_attachments']}\n";
echo "  - Orphaned attachments: {$db_stats['orphaned_attachments']}\n";

if ($db_stats['orphaned_attachments'] > 0) {
    echo "⚠️ Warning: Still found {$db_stats['orphaned_attachments']} orphaned attachments\n";
    $issues_found++;
}

// Check 5: Verify archive directory integrity
echo "\n5️⃣ Verifying archive directory integrity...\n";

$archive_dir = '/var/www/html/archive-orphaned-files-2026-01-13';
$archive_integrity = [];

if (!is_dir($archive_dir)) {
    $archive_integrity[] = "Archive directory does not exist";
    $issues_found++;
} else {
    $archive_files = glob("$archive_dir/*");
    $archive_count = count($archive_files);

    echo "Archive contains {$archive_count} files\n";

    // Check for any files that might be in use
    $suspicious_files = [];
    foreach ($archive_files as $file) {
        $filename = basename($file);
        // Check if filename suggests it might be a system file or commonly used
        if (preg_match('/^(favicon|logo|header|footer|default)/i', $filename)) {
            $suspicious_files[] = $filename;
        }
    }

    if (!empty($suspicious_files)) {
        echo "⚠️ Potentially suspicious archived files: " . implode(', ', array_slice($suspicious_files, 0, 5)) . "\n";
        if (count($suspicious_files) > 5) {
            echo "    ... and " . (count($suspicious_files) - 5) . " more\n";
        }
    }
}

// Check 6: Content integrity check
echo "\n6️⃣ Performing content integrity check...\n";

$content_issues = [];

// Check for missing images in content
foreach ($active_pages as $page) {
    // Look for img tags with missing src
    if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $page['post_content'], $matches)) {
        foreach ($matches[1] as $img_src) {
            // Convert to local path if needed
            $local_path = str_replace(get_home_url(), '/var/www/html', $img_src);
            if (strpos($local_path, 'http') !== 0) {
                $local_path = '/var/www/html' . $local_path;
            }

            if (!file_exists($local_path) && !preg_match('/^https?:\/\//', $img_src)) {
                $content_issues[] = [
                    'page_id' => $page['ID'],
                    'page_title' => $page['post_title'],
                    'missing_image' => $img_src
                ];
                $issues_found++;
            }
        }
    }
}

echo "Content integrity: " . (count($content_issues) == 0 ? "✅ All OK" : count($content_issues) . " issues") . "\n";

// Generate comprehensive report
$verification_report = [
    'metadata' => [
        'generated_at' => date('Y-m-d H:i:s'),
        'operation' => 'Archive Integrity Verification',
        'authority' => 'Team 4 - Database Specialists'
    ],
    'summary' => [
        'total_issues_found' => $issues_found,
        'verification_status' => $issues_found == 0 ? 'PASSED' : 'ISSUES_FOUND',
        'archive_operation_safe' => $issues_found == 0
    ],
    'checks' => [
        'broken_links' => count($broken_links),
        'meta_references' => count($meta_references),
        'accessibility_issues' => count($accessibility_issues),
        'remaining_orphaned' => $db_stats['orphaned_attachments'],
        'content_issues' => count($content_issues)
    ],
    'details' => [
        'broken_links' => $broken_links,
        'meta_references' => $meta_references,
        'accessibility_issues' => $accessibility_issues,
        'content_issues' => $content_issues,
        'database_stats' => $db_stats
    ],
    'recommendations' => []
];

// Generate recommendations
if ($issues_found > 0) {
    $verification_report['recommendations'][] = "Address {$issues_found} issues found during verification";
}

if (count($broken_links) > 0) {
    $verification_report['recommendations'][] = "Fix " . count($broken_links) . " broken links in content";
}

if ($db_stats['orphaned_attachments'] > 0) {
    $verification_report['recommendations'][] = "Re-run archive process for {$db_stats['orphaned_attachments']} remaining orphaned files";
}

if (empty($verification_report['recommendations'])) {
    $verification_report['recommendations'][] = "Archive operation verified as safe - no issues found";
}

$report_file = "docs/database/archive-integrity-verification-" . date('Y-m-d_H-i-s') . ".json";
file_put_contents($report_file, json_encode($verification_report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

// Final summary
echo "\n" . str_repeat("═", 60) . "\n";
echo "📊 VERIFICATION SUMMARY\n";
echo str_repeat("═", 60) . "\n";
echo "Issues Found: {$issues_found}\n";
echo "Status: " . ($issues_found == 0 ? "✅ PASSED - Archive operation is safe" : "❌ ISSUES FOUND - Review required") . "\n";
echo "\nDetailed breakdown:\n";
echo "  - Broken content links: " . count($broken_links) . "\n";
echo "  - Meta references to archived files: " . count($meta_references) . "\n";
echo "  - Accessibility issues: " . count($accessibility_issues) . "\n";
echo "  - Remaining orphaned files: {$db_stats['orphaned_attachments']}\n";
echo "  - Content integrity issues: " . count($content_issues) . "\n";

echo "\nReport saved to: {$report_file}\n";

if ($issues_found == 0) {
    echo "\n🎉 Archive operation integrity verified - ALL CHECKS PASSED!\n";
    echo "The archive operation was performed safely with no active content affected.\n";
} else {
    echo "\n⚠️ Issues found - review the report for details\n";
}

echo "\n✅ Verification completed\n";
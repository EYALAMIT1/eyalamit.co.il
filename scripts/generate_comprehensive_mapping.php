<?php
/**
 * Generate Comprehensive Site Mapping
 * Direct execution script for creating detailed JSON mapping
 */

require_once('wp-load.php');

$mapper = new ComprehensiveSiteMapping();
$filename = $mapper->save_mapping();

if ($filename) {
    echo "✅ Comprehensive site mapping saved to: {$filename}\n";

    $mapping = json_decode(file_get_contents($filename), true);
    $stats = $mapping['statistics'];

    echo "📊 Mapping Statistics:\n";
    echo "Pages: {$stats['total_pages']}\n";
    echo "Posts: {$stats['total_posts']}\n";
    echo "Attachments: {$stats['total_attachments']}\n";
    echo "Categories: {$stats['total_categories']}\n";
    echo "Tags: {$stats['total_tags']}\n";
    echo "Orphaned Files: {$stats['orphaned_attachments']}\n";
    echo "Missing Files: {$stats['missing_attachments']}\n";

    echo "\nEvidence: Check docs/sitemap/ for COMPREHENSIVE-SITE-MAPPING-*.json\n";
} else {
    echo "❌ Failed to save site mapping\n";
}
<?php
/**
 * WP-CLI Safe Smart Quotes Sanitizer
 * Command: wp safe-sanitize-quotes
 * Authority: CEO Eyal Amit - Safe DB Operations Protocol
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (defined('WP_CLI') && WP_CLI) {
    require_once __DIR__ . '/safe_smart_quotes_sanitizer.php';

    WP_CLI::add_command('safe-sanitize-quotes', function($args, $assoc_args) {
        WP_CLI::log('🚨 SAFE SMART QUOTES SANITIZATION');
        WP_CLI::log('Authority: CEO Eyal Amit - Serialization-Safe Operations');
        WP_CLI::log('==========================================');

        // Confirm execution
        WP_CLI::confirm('This will modify database content. Backup will be created. Proceed?');

        $sanitizer = new SafeSmartQuotesSanitizer();
        $results = $sanitizer->run_sanitization();

        WP_CLI::success('Sanitization completed successfully!');
        WP_CLI::log("📊 Results:");
        WP_CLI::log("Posts affected: {$results['posts']}");
        WP_CLI::log("Options affected: {$results['options']}");
        WP_CLI::log("Postmeta affected: {$results['postmeta']}");
        WP_CLI::log("Total affected: " . array_sum($results));

        // Verification
        global $wpdb;
        $vc_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%vc_%'");
        WP_CLI::log("Posts with VC shortcodes: {$vc_posts}");

        WP_CLI::success('Evidence: Check wp-content/db-backups/ for backup files');
    });
}
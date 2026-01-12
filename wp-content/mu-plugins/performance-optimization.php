<?php
/**
 * Performance Optimization Functions 2026
 * Must-use plugin for WordPress performance improvements
 *
 * @version 1.0.0
 * @author AI Assistant - Cursor
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ========================================
// IMAGE OPTIMIZATION FUNCTIONS
// ========================================

/**
 * Convert JPG/PNG images to WebP on upload
 */
function auto_convert_to_webp($metadata, $attachment_id) {
    if (!function_exists('imagewebp')) {
        return $metadata;
    }

    $upload_dir = wp_upload_dir();
    $file_path = get_attached_file($attachment_id);

    if (!file_exists($file_path)) {
        return $metadata;
    }

    // Convert main image
    convert_image_to_webp($file_path);

    // Convert sizes
    if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
        foreach ($metadata['sizes'] as $size => $size_data) {
            $size_path = $upload_dir['path'] . '/' . $size_data['file'];
            if (file_exists($size_path)) {
                convert_image_to_webp($size_path);
            }
        }
    }

    return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'auto_convert_to_webp', 10, 2);

/**
 * Convert single image to WebP
 */
function convert_image_to_webp($image_path) {
    $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
        return false;
    }

    $webp_path = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $image_path);

    // Skip if WebP already exists
    if (file_exists($webp_path)) {
        return $webp_path;
    }

    // Create image resource
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $image = imagecreatefromjpeg($image_path);
            break;
        case 'png':
            $image = imagecreatefrompng($image_path);
            break;
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    // Convert to WebP with 80% quality
    if (imagewebp($image, $webp_path, 80)) {
        imagedestroy($image);
        return $webp_path;
    }

    imagedestroy($image);
    return false;
}

/**
 * Add WebP support to image sources
 */
function add_webp_to_srcset($sources) {
    if (!function_exists('imagewebp')) {
        return $sources;
    }

    foreach ($sources as $size => &$source) {
        $original_url = $source['url'];
        $webp_url = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $original_url);

        // Check if WebP version exists
        $webp_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $webp_url);
        if (file_exists($webp_path)) {
            $source['url'] = $webp_url;
        }
    }

    return $sources;
}
add_filter('wp_calculate_image_srcset', 'add_webp_to_srcset');

/**
 * Add lazy loading to images
 */
function add_lazy_loading_to_images($content) {
    if (is_feed() || is_preview()) {
        return $content;
    }

    // Add loading="lazy" to img tags
    $content = preg_replace('/<img(.*?)>/', '<img$1 loading="lazy">', $content);

    return $content;
}
add_filter('the_content', 'add_lazy_loading_to_images');
add_filter('post_thumbnail_html', 'add_lazy_loading_to_images');
add_filter('get_avatar', 'add_lazy_loading_to_images');

/**
 * Add lazy loading to widgets
 */
function add_lazy_loading_to_widgets($content) {
    return add_lazy_loading_to_images($content);
}
add_filter('widget_text', 'add_lazy_loading_to_widgets');

// ========================================
// PERFORMANCE HEADERS
// ========================================

/**
 * Add performance and security headers
 */
function add_performance_headers() {
    if (is_admin()) {
        return;
    }

    // Cache static assets for 1 year
    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
            header('Cache-Control: public, max-age=31536000, immutable');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }
    }

    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // DNS prefetch for external resources
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">';
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">';
    echo '<link rel="dns-prefetch" href="//www.google-analytics.com">';
}
add_action('wp_head', 'add_performance_headers', 1);

// ========================================
// DATABASE OPTIMIZATION
// ========================================

/**
 * Clean expired transients daily
 */
function daily_transient_cleanup() {
    global $wpdb;

    // Delete expired transients
    $wpdb->query("
        DELETE FROM $wpdb->options
        WHERE option_name LIKE '_transient_timeout_%'
        AND option_value < UNIX_TIMESTAMP()
    ");

    // Delete orphaned transient values
    $wpdb->query("
        DELETE FROM $wpdb->options
        WHERE option_name LIKE '_transient_%'
        AND option_value IS NULL
    ");
}
add_action('wp_scheduled_delete', 'daily_transient_cleanup');

// ========================================
// ASSET OPTIMIZATION
// ========================================

/**
 * Defer non-critical CSS
 */
function defer_css($html, $handle, $href, $media) {
    $critical_css_handles = ['critical-css', 'above-the-fold'];

    if (!in_array($handle, $critical_css_handles)) {
        $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
        $html .= "<noscript><link rel='stylesheet' href='$href' media='$media'></noscript>";
    }

    return $html;
}
add_filter('style_loader_tag', 'defer_css', 10, 4);

/**
 * Defer non-critical JavaScript
 */
function defer_javascript($tag, $handle, $src) {
    $critical_js_handles = ['jquery-core', 'critical-js'];

    if (!in_array($handle, $critical_js_handles) && strpos($tag, 'defer') === false) {
        $tag = str_replace('<script ', '<script defer ', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'defer_javascript', 10, 3);

// ========================================
// FONT OPTIMIZATION
// ========================================

/**
 * Add font-display: swap to Google Fonts
 */
function optimize_google_fonts($html) {
    return str_replace('fonts.googleapis.com/css', 'fonts.googleapis.com/css&display=swap', $html);
}
add_filter('style_loader_src', 'optimize_google_fonts');

// ========================================
// HEARTBEAT OPTIMIZATION
// ========================================

/**
 * Reduce WordPress heartbeat frequency
 */
function optimize_heartbeat($settings) {
    // Reduce frequency on admin pages
    if (is_admin()) {
        $settings['interval'] = 60; // 60 seconds instead of 15
    }
    return $settings;
}
add_filter('heartbeat_settings', 'optimize_heartbeat');

/**
 * Disable heartbeat on frontend
 */
function disable_heartbeat_on_frontend() {
    if (!is_admin()) {
        wp_deregister_script('heartbeat');
    }
}
add_action('init', 'disable_heartbeat_on_frontend', 1);

// ========================================
// QUERY OPTIMIZATION
// ========================================

/**
 * Optimize main query for performance
 */
function optimize_main_query($query) {
    if ($query->is_main_query() && !is_admin()) {
        // Limit posts per page for better performance
        if ($query->is_archive() && !$query->is_category() && !$query->is_tag()) {
            $query->set('posts_per_page', 12);
        }

        // Remove unnecessary post types from search
        if ($query->is_search()) {
            $query->set('post_type', ['post', 'page']);
        }
    }
}
add_action('pre_get_posts', 'optimize_main_query');

// ========================================
// ADMIN OPTIMIZATION
// ========================================

/**
 * Remove query strings from static resources in admin
 */
function remove_query_strings_from_admin($src) {
    if (is_admin()) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('script_loader_src', 'remove_query_strings_from_admin');
add_filter('style_loader_src', 'remove_query_strings_from_admin');

// ========================================
// LOGGING AND MONITORING
// ========================================

/**
 * Log performance metrics
 */
function log_performance_metrics() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    $metrics = [
        'memory_usage' => memory_get_peak_usage(true),
        'query_count' => get_num_queries(),
        'load_time' => timer_stop(0, 6)
    ];

    // Log only if load time is high
    if ($metrics['load_time'] > 2.0) {
        error_log(sprintf(
            'Performance Alert: Page load time %.2fs, %d queries, %dMB memory - %s',
            $metrics['load_time'],
            $metrics['query_count'],
            $metrics['memory_usage'] / 1024 / 1024,
            $_SERVER['REQUEST_URI']
        ));
    }
}
add_action('wp_footer', 'log_performance_metrics', 999);

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * Get database size information
 */
function get_database_size() {
    global $wpdb;

    $results = $wpdb->get_results("
        SELECT
            table_name AS 'Table',
            round(((data_length + index_length) / 1024 / 1024), 2) AS 'Size_MB'
        FROM information_schema.TABLES
        WHERE table_schema = '" . DB_NAME . "'
        ORDER BY (data_length + index_length) DESC
    ");

    return $results;
}

/**
 * Force garbage collection
 */
function force_garbage_collection() {
    if (function_exists('gc_collect_cycles')) {
        gc_collect_cycles();
    }
}
add_action('shutdown', 'force_garbage_collection');

/**
 * Admin notice for performance status
 */
function performance_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $query_count = get_num_queries();
    $load_time = timer_stop(0, 2);

    if ($load_time > 1.0 || $query_count > 50) {
        $class = 'notice-warning';
        $message = sprintf(
            __('Performance Warning: Page loaded in %.2f seconds with %d database queries.', 'performance-optimization'),
            $load_time,
            $query_count
        );
    } else {
        $class = 'notice-success';
        $message = sprintf(
            __('Performance OK: Page loaded in %.2f seconds with %d database queries.', 'performance-optimization'),
            $load_time,
            $query_count
        );
    }

    printf('<div class="notice %s is-dismissible"><p>%s</p></div>', $class, $message);
}
add_action('admin_notices', 'performance_admin_notice');

/*
 * ========================================
 * PERFORMANCE OPTIMIZATION COMPLETE
 * ========================================
 *
 * This plugin implements:
 * - WebP image conversion
 * - Lazy loading
 * - Performance headers
 * - Database cleanup
 * - Asset deferring
 * - Query optimization
 * - Admin performance monitoring
 *
 * For WordPress performance optimization 2026
 */
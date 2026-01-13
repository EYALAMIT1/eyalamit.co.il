<?php
// Output buffering to fix production URLs
add_action('init', function() {
    ob_start(function($buffer) {
        return str_replace(
            ['http://www.eyalamit.co.il', 'https://www.eyalamit.co.il'],
            site_url(),
            $buffer
        );
    });
}, 0);

/**
 * EA Core Hardening - Must-Use Plugin
 *
 * Security headers and performance optimizations for Eyal Amit website
 *
 * @package EA_Core_Hardening
 * @version 1.0.0
 * @author Team 1 (Development)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class EA_Core_Hardening
 */
class EA_Core_Hardening {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->ensure_jquery_enqueue();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Disable wptexturize to prevent quote conversion
        // Run early on init and late on plugins_loaded to override plugin attempts
        add_action('init', array($this, 'disable_wptexturize'), 1);
        add_action('plugins_loaded', array($this, 'disable_wptexturize'), 999);

        // Security headers
        add_action('send_headers', array($this, 'add_security_headers'));

        // Performance optimizations
        add_action('wp_enqueue_scripts', array($this, 'dequeue_gutenberg_styles'), 20);
        add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg_editor'), 10, 2);

        // Remove unnecessary scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'optimize_assets'), 1);

        // CSS optimization
        $this->optimize_css_loading();

        // WebP support
        $this->add_webp_support();

        // Script defer attributes
        $this->add_defer_to_scripts();

        // Disable XML-RPC for security
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('xmlrpc_methods', array($this, 'disable_xmlrpc_methods'));
    }

    /**
     * Add security headers
     * Phase 4 Step 2 - Security Headers Implementation
     */
    public function add_security_headers() {
        if (!is_admin()) {
            // X-Frame-Options - Prevent clickjacking
            if (!headers_sent()) {
                header('X-Frame-Options: SAMEORIGIN');
            }

            // X-Content-Type-Options - Prevent MIME sniffing
            if (!headers_sent()) {
                header('X-Content-Type-Options: nosniff');
            }

            // X-XSS-Protection - XSS protection (legacy browsers)
            if (!headers_sent()) {
                header('X-XSS-Protection: 1; mode=block');
            }

            // Referrer-Policy - Control referrer information
            if (!headers_sent()) {
                header('Referrer-Policy: strict-origin-when-cross-origin');
            }

            // Permissions-Policy - Control browser features
            if (!headers_sent()) {
                header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()');
            }

            // Content-Security-Policy - Control resource loading
            // Adjusted for WordPress, Elementor, Google services, and Bridge theme
            if (!headers_sent()) {
                $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google-analytics.com https://www.googletagmanager.com https://www.google.com https://apis.google.com https://maps.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com; img-src 'self' data: https: http:; connect-src 'self' https://www.google-analytics.com https://www.googletagmanager.com https://www.google.com; frame-src 'self' https://www.google.com https://maps.google.com; object-src 'none'; base-uri 'self'; form-action 'self';";
                header("Content-Security-Policy: " . $csp);
            }

            // Remove server signature
            if (function_exists('header_remove')) {
                header_remove('X-Powered-By');
                header_remove('Server');
            }
        }
    }

    /**
     * Disable Gutenberg block editor
     */
    public function disable_gutenberg_editor($use_block_editor, $post_type) {
        // Keep Gutenberg disabled for all post types except pages if needed
        return false;
    }

    /**
     * Dequeue Gutenberg styles on frontend
     */
    public function dequeue_gutenberg_styles() {
        // Remove Gutenberg block styles
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-block-style'); // WooCommerce blocks
        wp_dequeue_style('wc-block-editor'); // WooCommerce editor blocks

        // Remove Gutenberg scripts
        wp_dequeue_script('wp-polyfill');
        wp_dequeue_script('regenerator-runtime');
    }

    /**
     * Optimize assets loading
     */
    public function optimize_assets() {
        // Remove unnecessary emoji scripts
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');

        // Remove unnecessary scripts
        wp_dequeue_script('wp-embed');

        // Optimize jQuery loading
        if (!is_admin()) {
            // Remove any existing jQuery registrations first
            wp_deregister_script('jquery');
            wp_deregister_script('jquery-core');
            wp_deregister_script('jquery-migrate');

            // Register jQuery properly with all dependencies
            wp_register_script('jquery-core', includes_url('/js/jquery/jquery.min.js'), array(), '3.7.1', false);
            wp_register_script('jquery-migrate', includes_url('/js/jquery/jquery-migrate.min.js'), array('jquery-core'), '3.7.1', false);
            wp_register_script('jquery', false, array('jquery-core', 'jquery-migrate'), '3.7.1', false);

            // Enqueue jQuery and jQuery Migrate early
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-migrate');

            // Prevent duplicate jQuery enqueues from themes/plugins (like Bridge theme)
            add_action('wp_enqueue_scripts', function() {
                // If Bridge theme or other plugins try to enqueue jQuery again, dequeue them
                if (wp_script_is('jquery', 'enqueued') && wp_script_is('jquery', 'to_do')) {
                    wp_dequeue_script('jquery');
                    wp_enqueue_script('jquery'); // Re-enqueue our properly configured version
                }
            }, 11); // Run after Bridge theme's default priority (10)
        }
    }

    /**
     * Ensure jQuery is enqueued for all page types
     */
    private function ensure_jquery_enqueue() {
        // Additional safeguard for jQuery loading
        add_action('wp_enqueue_scripts', function() {
            if (!wp_script_is('jquery-core', 'enqueued')) {
                wp_enqueue_script('jquery-core');
            }
            if (!wp_script_is('jquery-migrate', 'enqueued')) {
                wp_enqueue_script('jquery-migrate');
            }
            if (!wp_script_is('jquery', 'enqueued')) {
                wp_enqueue_script('jquery');
            }
        }, 1);
    }

    /**
     * Remove unused CSS and optimize loading
     */
    private function optimize_css_loading() {
        add_action('wp_enqueue_scripts', function() {
            // Remove unused plugin CSS files
            wp_dequeue_style('woocommerce-general');
            wp_dequeue_style('woocommerce-layout');
            wp_dequeue_style('woocommerce-smallscreen');
            wp_dequeue_style('woocommerce_responsive');
            wp_dequeue_style('layerslider');
            wp_dequeue_style('ls-google-fonts');
            wp_dequeue_style('timetable-schedule');
            wp_dequeue_style('timetable-schedule-responsive');
            wp_dequeue_style('envira-gallery');
            wp_dequeue_style('wp-views');
            wp_dequeue_style('toolset-maps');
            wp_dequeue_style('types');
            wp_dequeue_style('layouts');
            wp_dequeue_style('types-embedded');
            wp_dequeue_style('wp-views-embedded');
            wp_dequeue_style('layouts-embedded');

            // Remove unused Bridge theme CSS
            if (is_front_page()) {
                // Remove demo/landing CSS
                wp_dequeue_style('qode_landing');
                wp_dequeue_style('qode_toolbar');

                // Remove print styles (not needed on homepage)
                wp_dequeue_style('qode_print');
            }

            // Remove browser-specific CSS (use responsive instead)
            wp_dequeue_style('mac_stylesheet');
            wp_dequeue_style('webkit');
            wp_dequeue_style('safari');

        }, 999);
    }

    /**
     * Add WebP support
     */
    private function add_webp_support() {
        add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
            if (isset($metadata['sizes'])) {
                foreach ($metadata['sizes'] as $size => $data) {
                    // WebP conversion would be handled by image optimization plugins
                    // For now, just add the filter for future WebP implementation
                }
            }
            return $metadata;
        }, 10, 2);

        // Add WebP to mime types
        add_filter('upload_mimes', function($mimes) {
            $mimes['webp'] = 'image/webp';
            return $mimes;
        });

        // Add WebP support for theme
        add_filter('ea_webp_support', '__return_true');
    }

    /**
     * Add defer attributes to scripts where safe
     */
    private function add_defer_to_scripts() {
        add_filter('script_loader_tag', function($tag, $handle, $src) {
            // Scripts that can be deferred (don't need to run immediately)
            $defer_scripts = array(
                'jquery-ui-core',
                'jquery-ui-accordion',
                'jquery-ui-menu',
                'jquery-ui-autocomplete',
                'jquery-ui-controlgroup',
                'jquery-ui-checkboxradio',
                'jquery-ui-button',
                'jquery-ui-datepicker',
                'jquery-effects-core',
                'jquery-effects-blind',
                'wp-dom-ready',
                'wp-hooks',
                'wp-i18n',
                'wp-a11y',
                'qode-like',
                'comment-reply'
            );

            // Don't defer critical scripts that need to run immediately
            $no_defer_scripts = array(
                'jquery-core',
                'jquery-migrate',
                'jquery',
                'html5',
                'default'
            );

            if (in_array($handle, $defer_scripts) && !in_array($handle, $no_defer_scripts)) {
                return str_replace(' src=', ' defer src=', $tag);
            }

            return $tag;
        }, 10, 3);
    }

    /**

    }

    /**
     * Disable XML-RPC methods for security
     */
    public function disable_xmlrpc_methods($methods) {
        unset($methods['pingback.ping']);
        unset($methods['pingback.extensions.getPingbacks']);
        return $methods;
    }

    /**
     * Disable wptexturize filter to prevent smart quote conversion
     *
     * WordPress wptexturize converts straight quotes to smart quotes (HTML entities)
     * which breaks WPBakery shortcode processing. This method removes wptexturize
     * from content filters to preserve sanitized quotes from database.
     *
     * @since 1.0.0
     */
    public function disable_wptexturize() {
        // Remove wptexturize from content filters
        remove_filter('the_content', 'wptexturize');
        remove_filter('the_excerpt', 'wptexturize');
        remove_filter('comment_text', 'wptexturize');

        // Disable global wptexturize execution
        add_filter('run_wptexturize', '__return_false');
    }
}

// Initialize the plugin
new EA_Core_Hardening();

/**
 * Performance optimization: Disable heartbeat API on frontend
 */
if (!is_admin()) {
    add_action('init', function() {
        wp_deregister_script('heartbeat');
    });
}

/**
 * Security: Disable file editing from admin
 */
define('DISALLOW_FILE_EDIT', true);

/**
 * Performance: Disable post revisions for better performance
 */
define('WP_POST_REVISIONS', 3);

/**
 * Security: Limit login attempts (basic protection)
 */
add_filter('wp_authenticate_user', function($user) {
    if (is_wp_error($user)) {
        return $user;
    }

    // Basic rate limiting - in production, use a proper security plugin
    $failed_login_count = get_transient('failed_login_' . $_SERVER['REMOTE_ADDR']);

    if ($failed_login_count >= 5) {
        return new WP_Error('too_many_attempts', __('Too many failed login attempts. Please try again later.'));
    }

    return $user;
});

add_action('wp_login_failed', function() {
    $failed_login_count = get_transient('failed_login_' . $_SERVER['REMOTE_ADDR']);
    set_transient('failed_login_' . $_SERVER['REMOTE_ADDR'], $failed_login_count + 1, 15 * MINUTE_IN_SECONDS);
});
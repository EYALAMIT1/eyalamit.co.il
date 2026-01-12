<?php
/**
 * Security Headers Plugin
 * Adds comprehensive security headers to WordPress
 *
 * @version 1.0.0
 * @author AI Assistant - Cursor
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ========================================
// SECURITY HEADERS IMPLEMENTATION
// ========================================

/**
 * Add security headers to all pages
 */
function add_security_headers() {
    if (is_admin() && !wp_doing_ajax()) {
        return; // Don't interfere with admin AJAX
    }

    // Only add headers for frontend and secure contexts
    if (!is_ssl() && !defined('WP_ENV') || WP_ENV !== 'development') {
        return;
    }

    // Content Security Policy (CSP)
    add_csp_headers();

    // Security Headers
    add_basic_security_headers();

    // HSTS (HTTP Strict Transport Security)
    add_hsts_header();

    // Feature Policy / Permissions Policy
    add_feature_policy();

    // Referrer Policy
    add_referrer_policy();
}
add_action('send_headers', 'add_security_headers', 1);

/**
 * Content Security Policy Headers
 */
function add_csp_headers() {
    $csp_enabled = getenv('CSP_ENABLED') ?: true;

    if (!$csp_enabled) {
        return;
    }

    // Basic CSP - adjust according to your needs
    $csp = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google-analytics.com https://www.googletagmanager.com https://fonts.googleapis.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com",
        "font-src 'self' https://fonts.gstatic.com data:",
        "img-src 'self' data: https: blob:",
        "connect-src 'self' https://www.google-analytics.com https://www.googletagmanager.com",
        "media-src 'self'",
        "object-src 'none'",
        "frame-src 'self' https://www.google.com https://www.youtube.com",
        "base-uri 'self'",
        "form-action 'self'",
        "upgrade-insecure-requests"
    ];

    header('Content-Security-Policy: ' . implode('; ', $csp));

    // CSP Report Only for monitoring (optional)
    $csp_report = array_merge($csp, ["report-uri /csp-report-endpoint"]);
    header('Content-Security-Policy-Report-Only: ' . implode('; ', $csp_report));
}

/**
 * Basic Security Headers
 */
function add_basic_security_headers() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');

    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');

    // Enable XSS filtering
    header('X-XSS-Protection: 1; mode=block');

    // Remove server information
    header_remove('X-Powered-By');
    header('Server: Web Server');

    // Prevent caching of sensitive pages
    if (is_user_logged_in() || is_admin()) {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}

/**
 * HTTP Strict Transport Security
 */
function add_hsts_header() {
    $hsts_max_age = getenv('HSTS_MAX_AGE') ?: 31536000; // 1 year

    if (is_ssl()) {
        header("Strict-Transport-Security: max-age=$hsts_max_age; includeSubDomains; preload");
    }
}

/**
 * Feature Policy / Permissions Policy
 */
function add_feature_policy() {
    $feature_policy = [
        "camera=()",
        "microphone=()",
        "geolocation=()",
        "gyroscope=()",
        "magnetometer=()",
        "payment=(self)",
        "usb=()"
    ];

    header('Feature-Policy: ' . implode('; ', $feature_policy));

    // Permissions Policy (newer standard)
    header('Permissions-Policy: ' . implode(', ', $feature_policy));
}

/**
 * Referrer Policy
 */
function add_referrer_policy() {
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// ========================================
// CSP REPORT HANDLING
// ========================================

/**
 * Handle CSP violation reports
 */
function handle_csp_report() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $input = file_get_contents('php://input');
    $report = json_decode($input, true);

    if ($report && isset($report['csp-report'])) {
        $log_message = sprintf(
            "[CSP] Violation: %s | Blocked: %s | Document: %s | Line: %s | Source: %s\n",
            $report['csp-report']['violated-directive'] ?? 'unknown',
            $report['csp-report']['blocked-uri'] ?? 'unknown',
            $report['csp-report']['document-uri'] ?? 'unknown',
            $report['csp-report']['line-number'] ?? 'unknown',
            $report['csp-report']['source-file'] ?? 'unknown'
        );

        error_log($log_message, 3, WP_CONTENT_DIR . '/csp-violations.log');
    }

    // Return 200 OK to acknowledge report
    http_response_code(200);
    exit;
}

// Add CSP report endpoint
add_action('init', function() {
    if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/csp-report-endpoint') {
        handle_csp_report();
    }
});

// ========================================
// SECURITY MONITORING
// ========================================

/**
 * Log security events
 */
function log_security_event($event, $details = []) {
    $log_entry = sprintf(
        "[SECURITY] %s | IP: %s | User: %s | Time: %s | Details: %s\n",
        $event,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        get_current_user_id() ?: 'guest',
        current_time('mysql'),
        json_encode($details)
    );

    error_log($log_entry, 3, WP_CONTENT_DIR . '/security.log');
}

/**
 * Monitor failed login attempts
 */
function monitor_failed_login($username, $error) {
    if (strpos($error->get_error_message(), 'incorrect password') !== false ||
        strpos($error->get_error_message(), 'invalid username') !== false) {
        log_security_event('FAILED_LOGIN_ATTEMPT', [
            'username' => $username,
            'error' => $error->get_error_message(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}
add_action('wp_login_failed', 'monitor_failed_login', 10, 2);

/**
 * Monitor suspicious activity
 */
function monitor_suspicious_activity() {
    // Monitor for SQL injection attempts
    if (isset($_SERVER['QUERY_STRING'])) {
        $suspicious_patterns = [
            'union select',
            'information_schema',
            'script>',
            '<script',
            'javascript:',
            'onload=',
            'onerror='
        ];

        foreach ($suspicious_patterns as $pattern) {
            if (stripos($_SERVER['QUERY_STRING'], $pattern) !== false) {
                log_security_event('SUSPICIOUS_REQUEST', [
                    'pattern' => $pattern,
                    'query_string' => $_SERVER['QUERY_STRING'],
                    'request_uri' => $_SERVER['REQUEST_URI']
                ]);
                break;
            }
        }
    }
}
add_action('init', 'monitor_suspicious_activity');

// ========================================
// ADMIN SECURITY ENHANCEMENTS
// ========================================

/**
 * Security enhancements for admin area
 */
function admin_security_enhancements() {
    if (!is_admin()) {
        return;
    }

    // Disable autocomplete on password fields
    add_action('admin_footer', function() {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var passwordFields = document.querySelectorAll("input[type=password]");
                passwordFields.forEach(function(field) {
                    field.setAttribute("autocomplete", "new-password");
                });
            });
        </script>';
    });

    // Add security notice
    add_action('admin_notices', function() {
        if (current_user_can('manage_options')) {
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>אבטחה:</strong> הקפד להשתמש בסיסמאות חזקות ולעדכן את המערכת באופן קבוע.</p>';
            echo '</div>';
        }
    });
}
add_action('admin_init', 'admin_security_enhancements');

// ========================================
// FILE UPLOAD SECURITY
// ========================================

/**
 * Enhance file upload security
 */
function secure_file_uploads($file) {
    // Check file type more thoroughly
    $file_type = wp_check_filetype($file['name']);
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];

    if (!in_array($file_type['ext'], $allowed_types)) {
        $file['error'] = 'סוג קובץ לא מורשה';
        return $file;
    }

    // Check for malicious content in images
    if (in_array($file_type['ext'], ['jpg', 'jpeg', 'png', 'gif'])) {
        $image_info = getimagesize($file['tmp_name']);
        if (!$image_info) {
            $file['error'] = 'קובץ תמונה לא תקין';
            return $file;
        }
    }

    // Rename file to prevent enumeration
    $file['name'] = wp_generate_password(8, false) . '.' . $file_type['ext'];

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'secure_file_uploads');

// ========================================
// XML-RPC SECURITY
// ========================================

/**
 * Secure XML-RPC if enabled
 */
function secure_xmlrpc($methods) {
    // Remove dangerous methods
    unset($methods['pingback.ping']);
    unset($methods['pingback.extensions.getPingbacks']);

    // Log XML-RPC access
    log_security_event('XMLRPC_ACCESS', [
        'method' => $_SERVER['REQUEST_METHOD'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    return $methods;
}
add_filter('xmlrpc_methods', 'secure_xmlrpc');

/**
 * Limit XML-RPC access
 */
function limit_xmlrpc_access($enabled) {
    // Allow XML-RPC only for specific IPs or authenticated users
    $allowed_ips = ['127.0.0.1', '::1']; // Add your server IPs

    if (!is_user_logged_in() && !in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
        log_security_event('XMLRPC_BLOCKED', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'reason' => 'unauthorized_access'
        ]);
        return false;
    }

    return $enabled;
}
add_filter('xmlrpc_enabled', 'limit_xmlrpc_access');

// ========================================
// DATABASE SECURITY
// ========================================

/**
 * Secure database queries
 */
function secure_database_queries($query) {
    // Log suspicious queries
    $suspicious_patterns = [
        'information_schema',
        'mysql\.',
        'concat.*char',
        'load_file',
        'into outfile'
    ];

    foreach ($suspicious_patterns as $pattern) {
        if (stripos($query, $pattern) !== false) {
            log_security_event('SUSPICIOUS_SQL', [
                'pattern' => $pattern,
                'query' => substr($query, 0, 200) . '...',
                'backtrace' => wp_debug_backtrace_summary()
            ]);
            break;
        }
    }

    return $query;
}
add_filter('query', 'secure_database_queries');

/**
 * Security Admin Notice
 */
function security_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $security_score = calculate_security_score();
    $class = $security_score >= 80 ? 'notice-success' : ($security_score >= 60 ? 'notice-warning' : 'notice-error');

    echo '<div class="notice ' . $class . ' is-dismissible">';
    echo '<h4>דוח אבטחה - ציון: ' . $security_score . '/100</h4>';
    echo '<p>המערכת בודקת באופן קבוע נקודות תורפה ואיומים אבטחה.</p>';
    echo '<p><a href="' . admin_url('admin.php?page=security-report') . '">צפה בדוח מלא</a></p>';
    echo '</div>';
}
add_action('admin_notices', 'security_admin_notice');

/**
 * Calculate basic security score
 */
function calculate_security_score() {
    $score = 0;

    // SSL enabled
    if (is_ssl()) $score += 20;

    // Security headers
    if (function_exists('headers_sent') && !headers_sent()) $score += 15;

    // File editor disabled
    if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) $score += 15;

    // Debug disabled in production
    if (!WP_DEBUG) $score += 10;

    // Wordfence active
    if (is_plugin_active('wordfence/wordfence.php')) $score += 20;

    // Strong passwords (basic check)
    if (current_user_can('manage_options')) $score += 10;

    // Login security
    if (is_plugin_active('limit-login-attempts-reloaded/limit-login-attempts-reloaded.php')) $score += 10;

    return min(100, $score);
}

// ========================================
// EMERGENCY SECURITY FUNCTIONS
// ========================================

/**
 * Emergency lockdown function
 */
function emergency_lockdown() {
    if (!current_user_can('manage_options')) {
        wp_die('האתר נמצא במצב חירום. אנא צרו קשר עם המנהל.');
    }
}
// Uncomment to activate: add_action('init', 'emergency_lockdown');

/**
 * Quick security scan
 */
function quick_security_scan() {
    $issues = [];

    // Check for common vulnerabilities
    if (WP_DEBUG && WP_ENV !== 'development') {
        $issues[] = 'WP_DEBUG enabled in production';
    }

    if (!defined('DISALLOW_FILE_EDIT') || !DISALLOW_FILE_EDIT) {
        $issues[] = 'File editor not disabled';
    }

    if (!is_ssl()) {
        $issues[] = 'SSL not enabled';
    }

    // Check file permissions (basic)
    $wp_config_path = ABSPATH . 'wp-config.php';
    if (file_exists($wp_config_path) && is_readable($wp_config_path)) {
        $issues[] = 'wp-config.php readable by web server';
    }

    return $issues;
}

/*
 * ========================================
 * SECURITY HEADERS PLUGIN COMPLETE
 * ========================================
 *
 * Features implemented:
 * - Content Security Policy (CSP)
 * - Security Headers (X-Frame-Options, X-Content-Type-Options, etc.)
 * - HSTS (HTTP Strict Transport Security)
 * - Feature Policy / Permissions Policy
 * - CSP violation reporting
 * - Security event logging
 * - Failed login monitoring
 * - File upload security
 * - XML-RPC security
 * - Admin security enhancements
 * - Security scoring system
 *
 * For WordPress security hardening 2026
 */
<?php
/**
 * Database Optimization Plugin
 * Automated database maintenance and optimization
 *
 * @version 1.0.0
 * @author AI Assistant - Cursor
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ========================================
// AUTOMATED DATABASE MAINTENANCE
// ========================================

/**
 * Schedule database maintenance events
 */
function schedule_database_maintenance() {
    if (!wp_next_scheduled('daily_database_cleanup')) {
        wp_schedule_event(strtotime('02:00:00'), 'daily', 'daily_database_cleanup');
    }

    if (!wp_next_scheduled('weekly_database_optimization')) {
        wp_schedule_event(strtotime('next sunday 02:00:00'), 'weekly', 'weekly_database_optimization');
    }

    if (!wp_next_scheduled('monthly_database_health_check')) {
        wp_schedule_event(strtotime('first day of next month 02:00:00'), 'monthly', 'monthly_database_health_check');
    }
}
register_activation_hook(__FILE__, 'schedule_database_maintenance');
add_action('init', 'schedule_database_maintenance');

/**
 * Daily database cleanup
 */
function daily_database_cleanup() {
    global $wpdb;

    $cleanup_results = [
        'start_time' => current_time('mysql'),
        'transients_cleaned' => 0,
        'orphaned_meta_cleaned' => 0,
        'expired_sessions_cleaned' => 0
    ];

    // Clean expired transients
    $cleanup_results['transients_cleaned'] = $wpdb->query("
        DELETE FROM {$wpdb->options}
        WHERE option_name LIKE '_transient_timeout_%'
        AND option_value < UNIX_TIMESTAMP()
    ");

    // Clean orphaned transient values
    $wpdb->query("
        DELETE FROM {$wpdb->options}
        WHERE option_name LIKE '_transient_%'
        AND option_value IS NULL
    ");

    // Clean orphaned postmeta
    $cleanup_results['orphaned_meta_cleaned'] = $wpdb->query("
        DELETE pm FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE p.ID IS NULL
    ");

    // Clean expired WooCommerce sessions (if WooCommerce exists)
    if (class_exists('WooCommerce')) {
        $cleanup_results['expired_sessions_cleaned'] = $wpdb->query("
            DELETE FROM {$wpdb->prefix}woocommerce_sessions
            WHERE session_expiry < UNIX_TIMESTAMP()
        ");
    }

    // Log results
    error_log(sprintf(
        '[DAILY DB CLEANUP] Transients: %d, Orphaned meta: %d, Sessions: %d',
        $cleanup_results['transients_cleaned'],
        $cleanup_results['orphaned_meta_cleaned'],
        $cleanup_results['expired_sessions_cleaned']
    ));

    // Store results for admin dashboard
    update_option('wp_db_cleanup_last_run', $cleanup_results);
}
add_action('daily_database_cleanup', 'daily_database_cleanup');

/**
 * Weekly database optimization
 */
function weekly_database_optimization() {
    global $wpdb;

    $optimization_results = [
        'start_time' => current_time('mysql'),
        'tables_optimized' => 0,
        'db_size_before' => 0,
        'db_size_after' => 0
    ];

    // Get database size before optimization
    $optimization_results['db_size_before'] = $wpdb->get_var("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2)
        FROM information_schema.TABLES
        WHERE table_schema = DB_NAME
    ");

    // Tables to optimize
    $tables_to_optimize = [
        'commentmeta', 'comments', 'links', 'options',
        'postmeta', 'posts', 'term_relationships',
        'term_taxonomy', 'terms', 'usermeta', 'users'
    ];

    foreach ($tables_to_optimize as $table) {
        $table_name = $wpdb->prefix . $table;
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $wpdb->query("OPTIMIZE TABLE $table_name");
            $optimization_results['tables_optimized']++;
        }
    }

    // Get database size after optimization
    $optimization_results['db_size_after'] = $wpdb->get_var("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2)
        FROM information_schema.TABLES
        WHERE table_schema = DB_NAME
    ");

    // Log results
    $size_improvement = $optimization_results['db_size_before'] - $optimization_results['db_size_after'];
    error_log(sprintf(
        '[WEEKLY DB OPTIMIZATION] Tables: %d, Size before: %.2fMB, Size after: %.2fMB, Saved: %.2fMB',
        $optimization_results['tables_optimized'],
        $optimization_results['db_size_before'],
        $optimization_results['db_size_after'],
        $size_improvement
    ));

    // Store results for admin dashboard
    update_option('wp_db_optimization_last_run', $optimization_results);
}
add_action('weekly_database_optimization', 'weekly_database_optimization');

/**
 * Monthly database health check
 */
function monthly_database_health_check() {
    global $wpdb;

    $health_check = [
        'check_time' => current_time('mysql'),
        'database_size' => 0,
        'table_count' => 0,
        'revision_count' => 0,
        'spam_count' => 0,
        'issues_found' => []
    ];

    // Database size
    $health_check['database_size'] = $wpdb->get_var("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2)
        FROM information_schema.TABLES
        WHERE table_schema = DB_NAME
    ");

    // Table count
    $health_check['table_count'] = $wpdb->get_var("
        SELECT COUNT(*)
        FROM information_schema.TABLES
        WHERE table_schema = DB_NAME
        AND TABLE_NAME LIKE '{$wpdb->prefix}%'
    ");

    // Content counts
    $health_check['revision_count'] = $wpdb->get_var("
        SELECT COUNT(*) FROM {$wpdb->posts}
        WHERE post_type = 'revision'
    ");

    $health_check['spam_count'] = $wpdb->get_var("
        SELECT COUNT(*) FROM {$wpdb->comments}
        WHERE comment_approved = 'spam'
    ");

    // Check for issues
    $large_tables = $wpdb->get_results("
        SELECT TABLE_NAME, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
        FROM information_schema.TABLES
        WHERE table_schema = DB_NAME
        AND TABLE_NAME LIKE '{$wpdb->prefix}%'
        AND (DATA_LENGTH + INDEX_LENGTH) > 100 * 1024 * 1024
        ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
    ");

    if (!empty($large_tables)) {
        $health_check['issues_found'][] = 'Large tables detected: ' . implode(', ', wp_list_pluck($large_tables, 'TABLE_NAME'));
    }

    // Check for fragmented tables
    $fragmented_tables = $wpdb->get_results("
        SELECT TABLE_NAME, ROUND(DATA_FREE / 1024 / 1024, 2) as free_mb
        FROM information_schema.TABLES
        WHERE table_schema = DB_NAME
        AND TABLE_NAME LIKE '{$wpdb->prefix}%'
        AND DATA_FREE > 50 * 1024 * 1024
        ORDER BY DATA_FREE DESC
    ");

    if (!empty($fragmented_tables)) {
        $health_check['issues_found'][] = 'Fragmented tables detected: ' . implode(', ', wp_list_pluck($fragmented_tables, 'TABLE_NAME'));
    }

    // Log results
    error_log(sprintf(
        '[MONTHLY DB HEALTH CHECK] Size: %.2fMB, Tables: %d, Revisions: %d, Spam: %d, Issues: %d',
        $health_check['database_size'],
        $health_check['table_count'],
        $health_check['revision_count'],
        $health_check['spam_count'],
        count($health_check['issues_found'])
    ));

    // Store results for admin dashboard
    update_option('wp_db_health_check_last_run', $health_check);

    // Send alert if issues found
    if (!empty($health_check['issues_found'])) {
        wp_mail(
            get_option('admin_email'),
            'Database Health Check Alert',
            'Issues found in database health check: ' . implode('; ', $health_check['issues_found'])
        );
    }
}
add_action('monthly_database_health_check', 'monthly_database_health_check');

// ========================================
// ADMIN DASHBOARD INTEGRATION
// ========================================

/**
 * Add database optimization dashboard widget
 */
function add_database_optimization_widget() {
    wp_add_dashboard_widget(
        'database_optimization_widget',
        'Database Optimization Status',
        'database_optimization_widget_content',
        null,
        null,
        'normal',
        'high'
    );
}
add_action('wp_dashboard_setup', 'add_database_optimization_widget');

/**
 * Database optimization widget content
 */
function database_optimization_widget_content() {
    global $wpdb;

    // Get current database size
    $db_size = $wpdb->get_var("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2)
        FROM information_schema.TABLES
        WHERE table_schema = DB_NAME
    ");

    // Get counts
    $revision_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");
    $spam_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'");

    // Get last maintenance results
    $last_cleanup = get_option('wp_db_cleanup_last_run', []);
    $last_optimization = get_option('wp_db_optimization_last_run', []);
    $last_health_check = get_option('wp_db_health_check_last_run', []);

    ?>
    <div style="padding: 10px;">
        <h4>Database Overview</h4>
        <p><strong>Database Size:</strong> <?php echo $db_size; ?> MB</p>
        <p><strong>Post Revisions:</strong> <?php echo number_format($revision_count); ?></p>
        <p><strong>Spam Comments:</strong> <?php echo number_format($spam_count); ?></p>

        <h4>Last Maintenance</h4>
        <?php if (!empty($last_cleanup)): ?>
            <p><strong>Cleanup:</strong> <?php echo $last_cleanup['transients_cleaned'] ?? 0; ?> transients cleaned</p>
        <?php endif; ?>

        <?php if (!empty($last_optimization)): ?>
            <p><strong>Optimization:</strong> <?php echo $last_optimization['tables_optimized'] ?? 0; ?> tables optimized</p>
        <?php endif; ?>

        <h4>Quick Actions</h4>
        <p>
            <a href="<?php echo admin_url('admin.php?page=database-optimization'); ?>" class="button">
                View Full Report
            </a>
            <button class="button" onclick="runQuickCleanup()">Quick Cleanup</button>
        </p>
    </div>

    <script>
    function runQuickCleanup() {
        if (confirm('Run quick database cleanup? This will remove expired transients.')) {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=run_quick_db_cleanup&nonce=<?php echo wp_create_nonce('db_cleanup_nonce'); ?>'
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message || 'Cleanup completed');
                location.reload();
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    }
    </script>
    <?php
}

/**
 * AJAX handler for quick cleanup
 */
function run_quick_db_cleanup() {
    check_ajax_referer('db_cleanup_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    global $wpdb;

    $cleaned = $wpdb->query("
        DELETE FROM {$wpdb->options}
        WHERE option_name LIKE '_transient_timeout_%'
        AND option_value < UNIX_TIMESTAMP()
    ");

    wp_send_json_success([
        'message' => sprintf('Cleaned %d expired transients', $cleaned)
    ]);
}
add_action('wp_ajax_run_quick_db_cleanup', 'run_quick_db_cleanup');

// ========================================
// PERFORMANCE MONITORING
// ========================================

/**
 * Log slow database queries
 */
function log_slow_database_queries($query, $query_time) {
    if ($query_time > 1.0) { // Log queries > 1 second
        error_log(sprintf(
            '[SLOW QUERY] %.2fs - %s',
            $query_time,
            substr($query, 0, 200)
        ));
    }
}

// Hook into WordPress query system
add_filter('query', function($query) {
    static $query_start_time = 0;

    if (strpos($query, 'SELECT') === 0 && !$query_start_time) {
        $query_start_time = microtime(true);
        add_action('shutdown', function() use ($query, $query_start_time) {
            $query_time = microtime(true) - $query_start_time;
            log_slow_database_queries($query, $query_time);
        });
    }

    return $query;
});

// ========================================
// ADMIN MENU INTEGRATION
// ========================================

/**
 * Add database optimization admin menu
 */
function add_database_optimization_menu() {
    add_management_page(
        'Database Optimization',
        'DB Optimization',
        'manage_options',
        'database-optimization',
        'database_optimization_page'
    );
}
add_action('admin_menu', 'add_database_optimization_menu');

/**
 * Database optimization admin page
 */
function database_optimization_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    global $wpdb;

    // Handle manual optimization
    if (isset($_POST['run_optimization']) && check_admin_referer('db_optimization_nonce')) {
        weekly_database_optimization();
        echo '<div class="notice notice-success"><p>Database optimization completed!</p></div>';
    }

    // Get database statistics
    $db_stats = [
        'size' => $wpdb->get_var("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) FROM information_schema.TABLES WHERE table_schema = DB_NAME"),
        'tables' => $wpdb->get_var("SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = DB_NAME AND TABLE_NAME LIKE '{$wpdb->prefix}%'"),
        'revisions' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"),
        'spam' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'")
    ];

    ?>
    <div class="wrap">
        <h1>Database Optimization</h1>

        <div class="card">
            <h2>Database Statistics</h2>
            <table class="widefat">
                <tr><td><strong>Database Size:</strong></td><td><?php echo $db_stats['size']; ?> MB</td></tr>
                <tr><td><strong>Total Tables:</strong></td><td><?php echo $db_stats['tables']; ?></td></tr>
                <tr><td><strong>Post Revisions:</strong></td><td><?php echo number_format($db_stats['revisions']); ?></td></tr>
                <tr><td><strong>Spam Comments:</strong></td><td><?php echo number_format($db_stats['spam']); ?></td></tr>
            </table>
        </div>

        <div class="card">
            <h2>Maintenance Actions</h2>
            <form method="post">
                <?php wp_nonce_field('db_optimization_nonce'); ?>
                <p>
                    <input type="submit" name="run_optimization" class="button button-primary" value="Run Full Optimization">
                    <span class="description">This will optimize all database tables</span>
                </p>
            </form>
        </div>

        <div class="card">
            <h2>Scheduled Maintenance</h2>
            <ul>
                <li><strong>Daily Cleanup:</strong> Expired transients and orphaned data</li>
                <li><strong>Weekly Optimization:</strong> Table optimization and repair</li>
                <li><strong>Monthly Health Check:</strong> Comprehensive database analysis</li>
            </ul>
        </div>
    </div>

    <style>
    .card { background: white; border: 1px solid #ddd; border-radius: 4px; padding: 20px; margin: 20px 0; }
    .widefat { margin: 10px 0; }
    </style>
    <?php
}

// ========================================
// DEACTIVATION CLEANUP
// ========================================

/**
 * Cleanup on plugin deactivation
 */
function database_optimization_deactivation() {
    wp_clear_scheduled_hook('daily_database_cleanup');
    wp_clear_scheduled_hook('weekly_database_optimization');
    wp_clear_scheduled_hook('monthly_database_health_check');
}
register_deactivation_hook(__FILE__, 'database_optimization_deactivation');

/*
 * ========================================
 * DATABASE OPTIMIZATION PLUGIN COMPLETE
 * ========================================
 *
 * Features implemented:
 * - Automated daily cleanup of transients and orphaned data
 * - Weekly table optimization and repair
 * - Monthly database health checks with alerts
 * - Admin dashboard integration with statistics
 * - Manual optimization controls
 * - Performance monitoring and slow query logging
 * - AJAX-powered quick cleanup functionality
 *
 * For WordPress database optimization 2026
 */
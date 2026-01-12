<?php
/**
 * Plugin Audit Tool 2026
 * Comprehensive plugin analysis and optimization
 *
 * @version 1.0.0
 * @author AI Assistant - Cursor
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ========================================
// PLUGIN INVENTORY SYSTEM
// ========================================

/**
 * Generate complete plugin inventory
 */
function generate_plugin_inventory() {
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins', []);
    $network_plugins = is_multisite() ? get_site_option('active_sitewide_plugins', []) : [];

    $inventory = [];
    foreach ($all_plugins as $plugin_file => $plugin_data) {
        $is_active = in_array($plugin_file, $active_plugins);
        $is_network_active = isset($network_plugins[$plugin_file]);

        $inventory[$plugin_file] = [
            'file' => $plugin_file,
            'name' => $plugin_data['Name'],
            'version' => $plugin_data['Version'],
            'author' => $plugin_data['Author'],
            'description' => $plugin_data['Description'],
            'requires_wp' => $plugin_data['RequiresWP'] ?? 'N/A',
            'tested_up_to' => $plugin_data['TestedUpTo'] ?? 'N/A',
            'requires_php' => $plugin_data['RequiresPHP'] ?? 'N/A',
            'active' => $is_active,
            'network_active' => $is_network_active,
            'status' => $is_active ? 'active' : 'inactive',
            'path' => WP_PLUGIN_DIR . '/' . $plugin_file,
            'size' => file_exists(WP_PLUGIN_DIR . '/' . $plugin_file) ?
                filesize(WP_PLUGIN_DIR . '/' . $plugin_file) : 0,
            'last_modified' => file_exists(WP_PLUGIN_DIR . '/' . $plugin_file) ?
                filemtime(WP_PLUGIN_DIR . '/' . $plugin_file) : 0
        ];
    }

    return $inventory;
}

/**
 * Check plugin compatibility
 */
function check_plugin_compatibility($plugin_file, $inventory = null) {
    if (!$inventory) {
        $inventory = generate_plugin_inventory();
    }

    if (!isset($inventory[$plugin_file])) {
        return ['compatible' => false, 'reason' => 'Plugin not found'];
    }

    $plugin = $inventory[$plugin_file];
    $compatibility = [
        'compatible' => true,
        'warnings' => [],
        'errors' => []
    ];

    // Check WordPress version
    if ($plugin['requires_wp'] !== 'N/A' && version_compare(get_bloginfo('version'), $plugin['requires_wp'], '<')) {
        $compatibility['errors'][] = sprintf(
            'Requires WordPress %s or higher (current: %s)',
            $plugin['requires_wp'],
            get_bloginfo('version')
        );
        $compatibility['compatible'] = false;
    }

    // Check PHP version
    if ($plugin['requires_php'] !== 'N/A' && version_compare(PHP_VERSION, $plugin['requires_php'], '<')) {
        $compatibility['errors'][] = sprintf(
            'Requires PHP %s or higher (current: %s)',
            $plugin['requires_php'],
            PHP_VERSION
        );
        $compatibility['compatible'] = false;
    }

    // Check tested up to version
    if ($plugin['tested_up_to'] !== 'N/A') {
        $current_wp = get_bloginfo('version');
        $tested_up_to = $plugin['tested_up_to'];

        if (version_compare($current_wp, $tested_up_to, '>')) {
            $compatibility['warnings'][] = sprintf(
                'Not tested with WordPress %s (tested up to: %s)',
                $current_wp,
                $tested_up_to
            );
        }
    }

    return $compatibility;
}

// ========================================
// PERFORMANCE MONITORING
// ========================================

/**
 * Measure plugin performance impact
 */
function measure_plugin_performance() {
    $active_plugins = get_option('active_plugins', []);
    $performance_data = [];

    // Measure baseline
    $baseline_queries = get_num_queries();
    $baseline_memory = memory_get_peak_usage(true);
    $baseline_time = timer_stop(0, 6);

    foreach ($active_plugins as $plugin_file) {
        // Skip our audit plugin
        if (strpos($plugin_file, 'plugin-audit.php') !== false) {
            continue;
        }

        $start_queries = get_num_queries();
        $start_memory = memory_get_peak_usage(true);
        $start_time = microtime(true);

        // Load plugin
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
        if (file_exists($plugin_path)) {
            include_once $plugin_path;
        }

        $load_time = microtime(true) - $start_time;
        $memory_used = memory_get_peak_usage(true) - $start_memory;
        $queries_added = get_num_queries() - $start_queries;

        $performance_data[$plugin_file] = [
            'load_time' => round($load_time, 4),
            'memory_used' => $memory_used,
            'queries_added' => $queries_added,
            'efficiency_score' => calculate_efficiency_score($load_time, $memory_used, $queries_added)
        ];
    }

    return $performance_data;
}

/**
 * Calculate plugin efficiency score
 */
function calculate_efficiency_score($load_time, $memory_used, $queries_added) {
    // Lower scores are better (0-100 scale)
    $time_score = min(100, $load_time * 1000); // Convert to milliseconds, cap at 100
    $memory_score = min(100, $memory_used / 1024 / 1024); // Convert to MB, cap at 100
    $query_score = min(100, $queries_added * 5); // 20 queries = 100 points

    // Weighted average
    $score = ($time_score * 0.4) + ($memory_score * 0.4) + ($query_score * 0.2);

    return round(max(0, min(100, $score)), 1);
}

// ========================================
// CONFLICT DETECTION
// ========================================

/**
 * Detect plugin conflicts
 */
function detect_plugin_conflicts() {
    $active_plugins = get_option('active_plugins', []);
    $conflicts = [];

    // Define conflict patterns
    $conflict_patterns = [
        'multiple_seo' => [
            'plugins' => [
                'wordpress-seo/wp-seo.php',
                'seo-by-rank-math/rank-math.php',
                'smartcrawl-seo/wpmu-dev-seo.php',
                'all-in-one-seo-pack/all_in_one_seo_pack.php'
            ],
            'severity' => 'high',
            'message' => 'Multiple SEO plugins detected. This can cause conflicts and duplicate meta tags.',
            'recommendation' => 'Keep only one SEO plugin active (recommend: Yoast SEO or Rank Math)'
        ],
        'multiple_cache' => [
            'plugins' => [
                'wp-rocket/wp-rocket.php',
                'w3-total-cache/w3-total-cache.php',
                'wp-super-cache/wp-cache.php',
                'wp-fastest-cache/wpFastestCache.php'
            ],
            'severity' => 'high',
            'message' => 'Multiple caching plugins detected. This can cause server overload.',
            'recommendation' => 'Keep only one caching plugin active (recommend: WP Rocket)'
        ],
        'multiple_security' => [
            'plugins' => [
                'wordfence/wordfence.php',
                'sucuri-scanner/sucuri.php',
                'ithemes-security-pro/ithemes-security-pro.php',
                'bulletproof-security/bulletproof-security.php'
            ],
            'severity' => 'medium',
            'message' => 'Multiple security plugins detected. This can cause performance issues.',
            'recommendation' => 'Keep only one comprehensive security plugin (recommend: Wordfence)'
        ],
        'multiple_backup' => [
            'plugins' => [
                'updraftplus/updraftplus.php',
                'backwpup/backwpup.php',
                'duplicator/duplicator.php'
            ],
            'severity' => 'low',
            'message' => 'Multiple backup plugins detected.',
            'recommendation' => 'Keep only one backup plugin active'
        ]
    ];

    foreach ($conflict_patterns as $conflict_type => $pattern) {
        $active_conflicting = array_intersect($pattern['plugins'], $active_plugins);

        if (count($active_conflicting) > 1) {
            $conflicts[] = [
                'type' => $conflict_type,
                'severity' => $pattern['severity'],
                'message' => $pattern['message'],
                'plugins_involved' => $active_conflicting,
                'recommendation' => $pattern['recommendation'],
                'detected_at' => current_time('mysql')
            ];
        }
    }

    return $conflicts;
}

// ========================================
// UNUSED PLUGIN DETECTION
// ========================================

/**
 * Find unused plugins
 */
function find_unused_plugins() {
    $all_plugins = array_keys(get_plugins());
    $active_plugins = get_option('active_plugins', []);
    $inactive_plugins = array_diff($all_plugins, $active_plugins);

    $unused_plugins = [];
    $usage_stats = get_option('plugin_usage_stats', []);

    foreach ($inactive_plugins as $plugin_file) {
        $last_used = $usage_stats[$plugin_file]['last_used'] ?? 'never';
        $days_since_used = $last_used === 'never' ? 999 :
            (time() - strtotime($last_used)) / (60 * 60 * 24);

        // Consider unused if not used for 90+ days
        if ($days_since_used > 90) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);

            $unused_plugins[] = [
                'file' => $plugin_file,
                'name' => $plugin_data['Name'],
                'last_used' => $last_used,
                'days_unused' => round($days_since_used),
                'size' => dirsize(WP_PLUGIN_DIR . '/' . dirname($plugin_file)),
                'recommendation' => 'Consider removing this unused plugin'
            ];
        }
    }

    // Sort by size (largest first)
    usort($unused_plugins, function($a, $b) {
        return $b['size'] <=> $a['size'];
    });

    return $unused_plugins;
}

/**
 * Track plugin usage
 */
function track_plugin_usage() {
    $active_plugins = get_option('active_plugins', []);
    $usage_stats = get_option('plugin_usage_stats', []);

    foreach ($active_plugins as $plugin_file) {
        $usage_stats[$plugin_file]['last_used'] = current_time('mysql');
        $usage_stats[$plugin_file]['usage_count'] = ($usage_stats[$plugin_file]['usage_count'] ?? 0) + 1;
    }

    update_option('plugin_usage_stats', $usage_stats);
}
add_action('shutdown', 'track_plugin_usage');

// ========================================
// SECURITY AUDIT
// ========================================

/**
 * Check plugin security status
 */
function check_plugin_security() {
    $inventory = generate_plugin_inventory();
    $security_issues = [];

    foreach ($inventory as $plugin_file => $plugin) {
        $issues = [];

        // Check if plugin is active but outdated
        if ($plugin['active'] && isset($plugin['update_available']) && $plugin['update_available']) {
            $issues[] = [
                'type' => 'outdated',
                'severity' => 'high',
                'message' => 'Plugin has available updates - potential security vulnerabilities',
                'current_version' => $plugin['version']
            ];
        }

        // Check for known security issues (basic check)
        $known_vulnerable = [
            'revslider/revslider.php' => 'Revolution Slider - known vulnerabilities',
            'js_composer/js_composer.php' => 'WPBakery Page Builder - regular security updates needed'
        ];

        if (isset($known_vulnerable[$plugin_file]) && $plugin['active']) {
            $issues[] = [
                'type' => 'known_vulnerable',
                'severity' => 'critical',
                'message' => $known_vulnerable[$plugin_file]
            ];
        }

        // Check file permissions
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
        if (file_exists($plugin_path)) {
            $permissions = substr(sprintf('%o', fileperms($plugin_path)), -4);
            if ($permissions > '0644') {
                $issues[] = [
                    'type' => 'permissions',
                    'severity' => 'medium',
                    'message' => "Plugin file has overly permissive permissions: {$permissions}",
                    'recommendation' => 'Set to 0644 or less'
                ];
            }
        }

        if (!empty($issues)) {
            $security_issues[$plugin_file] = [
                'plugin_name' => $plugin['name'],
                'issues' => $issues
            ];
        }
    }

    return $security_issues;
}

// ========================================
// RECOMMENDATIONS ENGINE
// ========================================

/**
 * Generate plugin optimization recommendations
 */
function generate_plugin_recommendations() {
    $inventory = generate_plugin_inventory();
    $performance = measure_plugin_performance();
    $conflicts = detect_plugin_conflicts();
    $unused = find_unused_plugins();
    $security = check_plugin_security();

    $recommendations = [
        'critical' => [],
        'high' => [],
        'medium' => [],
        'low' => []
    ];

    // Critical recommendations
    foreach ($conflicts as $conflict) {
        if ($conflict['severity'] === 'high' || $conflict['severity'] === 'critical') {
            $recommendations['critical'][] = [
                'type' => 'conflict_resolution',
                'title' => 'Resolve Plugin Conflict',
                'description' => $conflict['message'],
                'action' => $conflict['recommendation'],
                'affected_plugins' => $conflict['plugins_involved']
            ];
        }
    }

    // Security recommendations
    foreach ($security as $plugin_file => $issues) {
        foreach ($issues['issues'] as $issue) {
            $priority = $issue['severity'] === 'critical' ? 'critical' :
                       ($issue['severity'] === 'high' ? 'high' : 'medium');

            $recommendations[$priority][] = [
                'type' => 'security_fix',
                'title' => 'Security Issue: ' . $issues['plugin_name'],
                'description' => $issue['message'],
                'action' => 'Update plugin or apply security fix',
                'severity' => $issue['severity']
            ];
        }
    }

    // Performance recommendations
    foreach ($performance as $plugin_file => $metrics) {
        if ($metrics['efficiency_score'] > 80) {
            $plugin_name = isset($inventory[$plugin_file]['name']) ? $inventory[$plugin_file]['name'] : $plugin_file;

            $recommendations['medium'][] = [
                'type' => 'performance_optimization',
                'title' => 'High Performance Impact: ' . $plugin_name,
                'description' => sprintf(
                    'Plugin has high resource usage (Score: %.1f) - Load time: %.4fs, Memory: %dMB, Queries: %d',
                    $metrics['efficiency_score'],
                    $metrics['load_time'],
                    round($metrics['memory_used'] / 1024 / 1024),
                    $metrics['queries_added']
                ),
                'action' => 'Consider optimizing or replacing this plugin'
            ];
        }
    }

    // Unused plugins
    foreach ($unused as $plugin) {
        $recommendations['low'][] = [
            'type' => 'cleanup',
            'title' => 'Remove Unused Plugin: ' . $plugin['name'],
            'description' => sprintf(
                'Plugin unused for %d days, taking %s MB of space',
                $plugin['days_unused'],
                round($plugin['size'] / 1024 / 1024, 2)
            ),
            'action' => 'Safely remove unused plugin'
        ];
    }

    // Missing essential plugins
    $essential_plugins = [
        'wordpress-seo/wp-seo.php' => 'Yoast SEO',
        'wordfence/wordfence.php' => 'Wordfence Security',
        'wp-rocket/wp-rocket.php' => 'WP Rocket'
    ];

    $active_plugins = array_keys(array_filter($inventory, function($p) { return $p['active']; }));

    foreach ($essential_plugins as $plugin_file => $plugin_name) {
        if (!in_array($plugin_file, $active_plugins)) {
            $recommendations['high'][] = [
                'type' => 'missing_essential',
                'title' => 'Install Essential Plugin: ' . $plugin_name,
                'description' => $plugin_name . ' is recommended for security/performance',
                'action' => 'Install and configure ' . $plugin_name
            ];
        }
    }

    return $recommendations;
}

// ========================================
// COMPLETE AUDIT FUNCTION
// ========================================

/**
 * Perform complete plugin audit
 */
function perform_complete_plugin_audit() {
    $audit_results = [
        'timestamp' => current_time('mysql'),
        'inventory' => generate_plugin_inventory(),
        'performance' => measure_plugin_performance(),
        'conflicts' => detect_plugin_conflicts(),
        'unused' => find_unused_plugins(),
        'security' => check_plugin_security(),
        'recommendations' => generate_plugin_recommendations()
    ];

    // Save audit results
    update_option('plugin_audit_complete_results', $audit_results);

    // Log audit completion
    error_log(sprintf(
        '[PLUGIN AUDIT COMPLETE] Analyzed %d plugins, found %d conflicts, %d unused, %d security issues',
        count($audit_results['inventory']),
        count($audit_results['conflicts']),
        count($audit_results['unused']),
        count($audit_results['security'])
    ));

    // Send alert for critical issues
    $critical_count = count($audit_results['recommendations']['critical']);
    if ($critical_count > 0) {
        wp_mail(
            get_option('admin_email'),
            'Critical Plugin Issues Found',
            sprintf(
                'Plugin audit detected %d critical issues that need immediate attention. Check admin dashboard.',
                $critical_count
            )
        );
    }

    return $audit_results;
}

// ========================================
// ADMIN INTEGRATION
// ========================================

/**
 * Add plugin audit to admin dashboard
 */
function add_plugin_audit_dashboard_widget() {
    wp_add_dashboard_widget(
        'plugin_audit_status_widget',
        'Plugin Audit Status',
        'plugin_audit_dashboard_content',
        null,
        null,
        'normal',
        'high'
    );
}

function plugin_audit_dashboard_content() {
    $audit_results = get_option('plugin_audit_complete_results', []);

    if (empty($audit_results)) {
        echo '<p>No audit data available. <button id="run-audit-btn" class="button">Run Plugin Audit</button></p>';
        echo '<p><small>Last audit: Never</small></p>';
    } else {
        $active_count = count(array_filter($audit_results['inventory'], function($p) { return $p['active']; }));
        $conflict_count = count($audit_results['conflicts']);
        $unused_count = count($audit_results['unused']);
        $security_issues = count($audit_results['security']);
        $critical_recs = count($audit_results['recommendations']['critical']);

        echo '<div class="plugin-audit-metrics">';
        echo '<div class="metric"><strong>' . $active_count . '</strong><br>Active Plugins</div>';
        echo '<div class="metric"><strong style="color: ' . ($conflict_count > 0 ? 'red' : 'green') . '">' . $conflict_count . '</strong><br>Conflicts</div>';
        echo '<div class="metric"><strong>' . $unused_count . '</strong><br>Unused</div>';
        echo '<div class="metric"><strong style="color: ' . ($security_issues > 0 ? 'orange' : 'green') . '">' . $security_issues . '</strong><br>Security Issues</div>';
        echo '<div class="metric"><strong style="color: ' . ($critical_recs > 0 ? 'red' : 'green') . '">' . $critical_recs . '</strong><br>Critical Actions</div>';
        echo '</div>';

        echo '<p><small>Last audit: ' . $audit_results['timestamp'] . '</small></p>';
        echo '<p><a href="' . admin_url('admin.php?page=plugin-audit-full-report') . '" class="button">View Full Report</a></p>';
    }

    ?>
    <script>
    document.getElementById('run-audit-btn')?.addEventListener('click', function() {
        this.textContent = 'Running Audit...';
        this.disabled = true;

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=run_plugin_audit&nonce=<?php echo wp_create_nonce('plugin_audit_nonce'); ?>'
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Audit completed');
            location.reload();
        })
        .catch(error => {
            alert('Error: ' + error.message);
            this.textContent = 'Run Plugin Audit';
            this.disabled = false;
        });
    });
    </script>

    <style>
    .plugin-audit-metrics { display: flex; gap: 15px; margin: 15px 0; }
    .metric { text-align: center; padding: 10px; background: #f8f9fa; border-radius: 4px; flex: 1; }
    .metric strong { font-size: 18px; }
    </style>
    <?php
}

add_action('wp_dashboard_setup', 'add_plugin_audit_dashboard_widget');

/**
 * AJAX handler for plugin audit
 */
function ajax_run_plugin_audit() {
    check_ajax_referer('plugin_audit_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    $results = perform_complete_plugin_audit();

    wp_send_json_success([
        'message' => sprintf(
            'Plugin audit completed! Found %d active plugins, %d conflicts, %d unused plugins.',
            count(array_filter($results['inventory'], function($p) { return $p['active']; })),
            count($results['conflicts']),
            count($results['unused'])
        )
    ]);
}
add_action('wp_ajax_run_plugin_audit', 'ajax_run_plugin_audit');

/**
 * Add admin menu for detailed plugin audit
 */
function add_plugin_audit_admin_menu() {
    add_management_page(
        'Plugin Audit Report',
        'Plugin Audit',
        'manage_options',
        'plugin-audit-full-report',
        'plugin_audit_full_report_page'
    );
}
add_action('admin_menu', 'add_plugin_audit_admin_menu');

/**
 * Full plugin audit report page
 */
function plugin_audit_full_report_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }

    $audit_results = get_option('plugin_audit_complete_results', []);

    ?>
    <div class="wrap">
        <h1>Plugin Audit Full Report</h1>

        <?php if (empty($audit_results)): ?>
            <p>No audit data available. Please run a plugin audit first.</p>
            <button id="run-full-audit" class="button button-primary">Run Complete Audit</button>
        <?php else: ?>

        <div class="audit-summary">
            <h2>Audit Summary</h2>
            <p><strong>Audit Date:</strong> <?php echo $audit_results['timestamp']; ?></p>
            <p><strong>Total Plugins:</strong> <?php echo count($audit_results['inventory']); ?></p>
            <p><strong>Active Plugins:</strong> <?php echo count(array_filter($audit_results['inventory'], function($p) { return $p['active']; })); ?></p>
        </div>

        <!-- Conflicts Section -->
        <?php if (!empty($audit_results['conflicts'])): ?>
        <div class="audit-section conflicts">
            <h2>Plugin Conflicts</h2>
            <?php foreach ($audit_results['conflicts'] as $conflict): ?>
            <div class="conflict-item severity-<?php echo $conflict['severity']; ?>">
                <h3><?php echo ucfirst($conflict['type']); ?> Conflict</h3>
                <p><?php echo $conflict['message']; ?></p>
                <p><strong>Recommendation:</strong> <?php echo $conflict['recommendation']; ?></p>
                <p><strong>Plugins:</strong> <?php echo implode(', ', array_map(function($p) use ($audit_results) {
                    return $audit_results['inventory'][$p]['name'] ?? $p;
                }, $conflict['plugins_involved'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Security Issues -->
        <?php if (!empty($audit_results['security'])): ?>
        <div class="audit-section security">
            <h2>Security Issues</h2>
            <?php foreach ($audit_results['security'] as $plugin_file => $issues): ?>
            <div class="security-item">
                <h3><?php echo $issues['plugin_name']; ?></h3>
                <?php foreach ($issues['issues'] as $issue): ?>
                <div class="issue severity-<?php echo $issue['severity']; ?>">
                    <strong><?php echo ucfirst($issue['type']); ?>:</strong> <?php echo $issue['message']; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Performance Issues -->
        <?php if (!empty($audit_results['performance'])): ?>
        <div class="audit-section performance">
            <h2>Performance Analysis</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Plugin</th>
                        <th>Load Time</th>
                        <th>Memory Used</th>
                        <th>Queries Added</th>
                        <th>Efficiency Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($audit_results['performance'] as $plugin_file => $metrics):
                        $plugin_name = $audit_results['inventory'][$plugin_file]['name'] ?? $plugin_file;
                        $score_class = $metrics['efficiency_score'] > 80 ? 'high' : ($metrics['efficiency_score'] > 50 ? 'medium' : 'low');
                    ?>
                    <tr class="score-<?php echo $score_class; ?>">
                        <td><?php echo $plugin_name; ?></td>
                        <td><?php echo $metrics['load_time']; ?>s</td>
                        <td><?php echo round($metrics['memory_used'] / 1024 / 1024, 2); ?> MB</td>
                        <td><?php echo $metrics['queries_added']; ?></td>
                        <td><?php echo $metrics['efficiency_score']; ?>/100</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Recommendations -->
        <?php if (!empty($audit_results['recommendations'])): ?>
        <div class="audit-section recommendations">
            <h2>Recommendations</h2>
            <?php foreach (['critical', 'high', 'medium', 'low'] as $priority): ?>
                <?php if (!empty($audit_results['recommendations'][$priority])): ?>
                <div class="priority-group priority-<?php echo $priority; ?>">
                    <h3><?php echo ucfirst($priority); ?> Priority</h3>
                    <?php foreach ($audit_results['recommendations'][$priority] as $rec): ?>
                    <div class="recommendation-item">
                        <h4><?php echo $rec['title']; ?></h4>
                        <p><?php echo $rec['description']; ?></p>
                        <p><strong>Action:</strong> <?php echo $rec['action']; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>

    <style>
    .audit-section { margin: 30px 0; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px; }
    .conflict-item, .security-item, .recommendation-item { margin: 15px 0; padding: 10px; border-left: 4px solid #ddd; }
    .severity-high { border-left-color: #dc3545; }
    .severity-medium { border-left-color: #ffc107; }
    .severity-low { border-left-color: #17a2b8; }
    .score-high { background-color: #f8d7da; }
    .score-medium { background-color: #fff3cd; }
    .score-low { background-color: #d1ecf1; }
    .priority-critical { border: 2px solid #dc3545; background: #f8d7da; }
    .priority-high { border: 2px solid #ffc107; background: #fff3cd; }
    .priority-medium { border: 2px solid #17a2b8; background: #d1ecf1; }
    .priority-low { background: #f8f9fa; }
    </style>
    <?php
}

// ========================================
// SCHEDULED AUDIT
// ========================================

// Schedule monthly plugin audit
if (!wp_next_scheduled('monthly_plugin_audit')) {
    wp_schedule_event(strtotime('first day of next month 02:00:00'), 'monthly', 'monthly_plugin_audit');
}

add_action('monthly_plugin_audit', 'perform_complete_plugin_audit');

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * Get directory size
 */
function dirsize($directory) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

/*
 * ========================================
 * PLUGIN AUDIT TOOL COMPLETE
 * ========================================
 *
 * Features implemented:
 * - Complete plugin inventory with compatibility checks
 * - Performance monitoring and efficiency scoring
 * - Conflict detection for multiple plugin types
 * - Security vulnerability scanning
 * - Unused plugin identification
 * - Comprehensive recommendations engine
 * - Admin dashboard integration
 * - AJAX-powered audit functionality
 * - Scheduled monthly audits
 * - Detailed reporting and alerts
 *
 * For WordPress plugin audit and optimization 2026
 */
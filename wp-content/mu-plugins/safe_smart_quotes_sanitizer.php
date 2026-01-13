<?php
/**
 * SAFE SMART QUOTES SANITIZER
 * Authority: CEO Eyal Amit - Post-Emergency Recovery
 * Issue: REPLACE operations corrupt serialized PHP data
 * Solution: Use PHP unserialize/serialize cycle for safe replacements
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Safely replace smart quotes in serialized data
 */
class SafeSmartQuotesSanitizer {

    private $replacements = [
        "\xE2\x80\x9C" => '"', // Left double quotation mark
        "\xE2\x80\x9D" => '"', // Right double quotation mark
        "\xE2\x80\x98" => "'", // Left single quotation mark
        "\xE2\x80\x99" => "'", // Right single quotation mark
    ];

    /**
     * Check if string is serialized
     */
    private function is_serialized($data) {
        return is_string($data) && preg_match('/^a:\d+:\{/', $data);
    }

    /**
     * Safely replace in serialized data
     */
    private function replace_in_serialized($serialized) {
        if (!$this->is_serialized($serialized)) {
            // Not serialized, do direct replacement
            return str_replace(array_keys($this->replacements), array_values($this->replacements), $serialized);
        }

        // Handle serialized data safely
        $unserialized = @unserialize($serialized);
        if ($unserialized === false) {
            // Corrupted serialized data, skip
            return $serialized;
        }

        // Recursively replace in array/object
        $unserialized = $this->replace_recursive($unserialized);

        // Re-serialize
        return serialize($unserialized);
    }

    /**
     * Recursively replace smart quotes in arrays/objects
     */
    private function replace_recursive($data) {
        if (is_string($data)) {
            return str_replace(array_keys($this->replacements), array_values($this->replacements), $data);
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replace_recursive($value);
            }
        } elseif (is_object($data)) {
            $properties = get_object_vars($data);
            foreach ($properties as $property => $value) {
                $data->$property = $this->replace_recursive($value);
            }
        }

        return $data;
    }

    /**
     * Sanitize wp_posts table
     */
    public function sanitize_posts() {
        global $wpdb;

        $posts = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_content LIKE '%vc_%'");

        $affected = 0;
        foreach ($posts as $post) {
            $original = $post->post_content;
            $sanitized = $this->replace_in_serialized($original);

            if ($original !== $sanitized) {
                $wpdb->update(
                    $wpdb->posts,
                    ['post_content' => $sanitized],
                    ['ID' => $post->ID],
                    ['%s'],
                    ['%d']
                );
                $affected++;
            }
        }

        return $affected;
    }

    /**
     * Sanitize wp_options table (theme/plugin settings)
     */
    public function sanitize_options() {
        global $wpdb;

        // Only process options that might contain VC content
        $options = $wpdb->get_results("
            SELECT option_name, option_value
            FROM {$wpdb->options}
            WHERE option_value LIKE '%vc_%'
            AND option_name NOT LIKE '_transient_%'
        ");

        $affected = 0;
        foreach ($options as $option) {
            $original = $option->option_value;
            $sanitized = $this->replace_in_serialized($original);

            if ($original !== $sanitized) {
                update_option($option->option_name, $sanitized);
                $affected++;
            }
        }

        return $affected;
    }

    /**
     * Sanitize wp_postmeta table
     */
    public function sanitize_postmeta() {
        global $wpdb;

        $meta = $wpdb->get_results("
            SELECT meta_id, meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_value LIKE '%vc_%'
        ");

        $affected = 0;
        foreach ($meta as $item) {
            $original = $item->meta_value;
            $sanitized = $this->replace_in_serialized($original);

            if ($original !== $sanitized) {
                $wpdb->update(
                    $wpdb->postmeta,
                    ['meta_value' => $sanitized],
                    ['meta_id' => $item->meta_id],
                    ['%s'],
                    ['%d']
                );
                $affected++;
            }
        }

        return $affected;
    }

    /**
     * Run complete sanitization
     */
    public function run_sanitization() {
        // Create backup first
        $this->create_backup();

        $results = [
            'posts' => $this->sanitize_posts(),
            'options' => $this->sanitize_options(),
            'postmeta' => $this->sanitize_postmeta(),
        ];

        return $results;
    }

    /**
     * Create database backup
     */
    private function create_backup() {
        global $wpdb;

        $timestamp = date('Ymd_His');
        $backup_dir = WP_CONTENT_DIR . '/db-backups';

        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }

        // Backup critical tables
        $tables = ['posts', 'options', 'postmeta'];
        foreach ($tables as $table) {
            $filename = "{$backup_dir}/{$table}_backup_{$timestamp}.sql";
            $wpdb->query("SELECT * FROM {$wpdb->prefix}{$table} INTO OUTFILE '$filename'");
        }
    }
}

// Usage example:
/*
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('safe-sanitize-quotes', function() {
        $sanitizer = new SafeSmartQuotesSanitizer();
        $results = $sanitizer->run_sanitization();

        WP_CLI::success("Sanitization complete:");
        WP_CLI::log("Posts affected: {$results['posts']}");
        WP_CLI::log("Options affected: {$results['options']}");
        WP_CLI::log("Postmeta affected: {$results['postmeta']}");
    });
}
*/
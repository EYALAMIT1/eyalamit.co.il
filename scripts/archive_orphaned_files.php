<?php
/**
 * Archive Orphaned Files Script
 * Safely moves orphaned attachment files to archive directory
 * Authority: Team 4 (Database Specialists) - Serialization-Aware Operations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OrphanedFilesArchiver {

    private $archive_dir;
    private $orphaned_files = [];
    private $moved_files = [];
    private $errors = [];

    public function __construct($archive_date = null) {
        $date = $archive_date ?: date('Y-m-d');
        $this->archive_dir = "/var/www/html/archive-orphaned-files-{$date}";

        if (!is_dir($this->archive_dir)) {
            mkdir($this->archive_dir, 0755, true);
        }
    }

    /**
     * Get all orphaned attachment files from database
     */
    public function identify_orphaned_files() {
        global $wpdb;

        echo "🔍 Identifying orphaned attachment files...\n";

        // Get all attachments
        $attachments = $wpdb->get_results("
            SELECT ID, post_title, guid, post_date
            FROM {$wpdb->posts}
            WHERE post_type = 'attachment'
            ORDER BY ID ASC
        ", ARRAY_A);

        $orphaned_count = 0;
        $total_count = 0;

        foreach ($attachments as $attachment) {
            $total_count++;

            // Check if this attachment is used anywhere
            $usage_count = $this->check_attachment_usage($attachment['guid']);

            if ($usage_count == 0) {
                // Convert URL to file path
                $file_path = $this->url_to_path($attachment['guid']);

                if (file_exists($file_path)) {
                    $this->orphaned_files[] = [
                        'id' => $attachment['ID'],
                        'title' => $attachment['post_title'],
                        'url' => $attachment['guid'],
                        'path' => $file_path,
                        'size' => filesize($file_path),
                        'uploaded' => $attachment['post_date']
                    ];
                    $orphaned_count++;
                } else {
                    $this->errors[] = "File not found: {$file_path} (ID: {$attachment['ID']})";
                }
            }
        }

        echo "Found {$orphaned_count} orphaned files out of {$total_count} total attachments\n";
        return $orphaned_count;
    }

    /**
     * Check if attachment is used anywhere in content
     */
    private function check_attachment_usage($attachment_url) {
        global $wpdb;

        if (empty($attachment_url)) {
            return 0;
        }

        // Check in post content
        $usage_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_content LIKE %s
             AND post_status IN ('publish', 'draft', 'private')
             AND post_type IN ('post', 'page')",
            '%' . $wpdb->esc_like($attachment_url) . '%'
        ));

        // Also check in post meta (featured images, etc.)
        $meta_usage = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta}
             WHERE meta_value LIKE %s",
            '%' . $wpdb->esc_like($attachment_url) . '%'
        ));

        return $usage_count + $meta_usage;
    }

    /**
     * Convert URL to file path
     */
    private function url_to_path($url) {
        // Remove domain and get relative path
        $relative_path = str_replace('http://localhost:9090/', '', $url);

        // Convert to absolute path in container
        return "/var/www/html/{$relative_path}";
    }

    /**
     * Move orphaned files to archive directory
     */
    public function archive_files() {
        echo "📦 Moving orphaned files to archive directory: {$this->archive_dir}\n";

        $moved_count = 0;
        $error_count = 0;

        foreach ($this->orphaned_files as $file_info) {
            $source_path = $file_info['path'];
            $filename = basename($source_path);
            $dest_path = "{$this->archive_dir}/{$filename}";

            // Check if destination already exists (avoid overwrites)
            if (file_exists($dest_path)) {
                $dest_path = "{$this->archive_dir}/{$file_info['id']}_{$filename}";
            }

            if (rename($source_path, $dest_path)) {
                $this->moved_files[] = [
                    'original_path' => $source_path,
                    'archive_path' => $dest_path,
                    'id' => $file_info['id'],
                    'title' => $file_info['title'],
                    'size' => $file_info['size']
                ];
                $moved_count++;

                if ($moved_count % 100 == 0) {
                    echo "Moved {$moved_count} files...\n";
                }
            } else {
                $error_count++;
                $this->errors[] = "Failed to move: {$source_path} to {$dest_path}";
            }
        }

        echo "✅ Moved {$moved_count} files to archive\n";
        if ($error_count > 0) {
            echo "❌ {$error_count} files failed to move\n";
        }

        return $moved_count;
    }

    /**
     * Generate operation report
     */
    public function generate_report() {
        $timestamp = date('Y-m-d_H-i-s');
        $report_file = "docs/database/archive-orphaned-files-report-{$timestamp}.json";

        $report = [
            'metadata' => [
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Team 4 - Database Specialists',
                'operation' => 'Archive Orphaned Files',
                'authority' => 'CEO Eyal Amit - Serialization-Aware Operations',
                'archive_directory' => $this->archive_dir
            ],
            'statistics' => [
                'total_orphaned_identified' => count($this->orphaned_files),
                'total_moved' => count($this->moved_files),
                'total_errors' => count($this->errors),
                'archive_directory_size' => $this->get_directory_size($this->archive_dir)
            ],
            'moved_files' => $this->moved_files,
            'errors' => $this->errors,
            'verification' => [
                'orphaned_files_remaining' => $this->verify_remaining_orphaned(),
                'archive_integrity' => $this->verify_archive_integrity()
            ]
        ];

        // Ensure directory exists
        $dir = dirname($report_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $report_file;
    }

    /**
     * Get directory size
     */
    private function get_directory_size($directory) {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    /**
     * Verify no orphaned files remain
     */
    private function verify_remaining_orphaned() {
        global $wpdb;

        $remaining = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} p
            LEFT JOIN (
                SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(post_content, 'http://localhost:9090/wp-content/uploads/', -1), '\"', 1) as attachment_path
                FROM {$wpdb->posts}
                WHERE post_content LIKE '%http://localhost:9090/wp-content/uploads/%'
                AND post_status IN ('publish', 'draft', 'private')
            ) u ON p.guid LIKE CONCAT('%http://localhost:9090/wp-content/uploads/%', u.attachment_path, '%')
            WHERE p.post_type = 'attachment'
            AND u.attachment_path IS NULL
        ");

        return (int)$remaining;
    }

    /**
     * Verify archive integrity
     */
    private function verify_archive_integrity() {
        $archive_files = glob("{$this->archive_dir}/*");
        $expected_count = count($this->moved_files);

        return count($archive_files) === $expected_count;
    }

    /**
     * Run complete archive operation
     */
    public function run_archive_operation() {
        echo "🚀 Starting orphaned files archive operation\n";
        echo "Authority: Team 4 (Database Specialists) - Serialization-Aware Operations\n";
        echo "═══════════════════════════════════════════════════════════════\n";

        $orphaned_count = $this->identify_orphaned_files();

        if ($orphaned_count === 0) {
            echo "ℹ️ No orphaned files found to archive\n";
            return true;
        }

        $moved_count = $this->archive_files();

        $report_file = $this->generate_report();

        echo "\n📊 Archive Operation Summary:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Orphaned files identified: {$orphaned_count}\n";
        echo "Files successfully moved: {$moved_count}\n";
        echo "Errors encountered: " . count($this->errors) . "\n";
        echo "Archive directory: {$this->archive_dir}\n";
        echo "Report saved to: {$report_file}\n";

        if (count($this->errors) > 0) {
            echo "\n❌ Errors:\n";
            foreach ($this->errors as $error) {
                echo "  - {$error}\n";
            }
        }

        echo "\n✅ Archive operation completed\n";
        echo "Evidence: {$report_file}\n";

        return count($this->errors) === 0;
    }
}

// CLI Integration
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('archive-orphaned-files', function($args, $assoc_args) {
        $archiver = new OrphanedFilesArchiver();
        $success = $archiver->run_archive_operation();

        if ($success) {
            WP_CLI::success('Orphaned files archive operation completed successfully');
        } else {
            WP_CLI::error('Archive operation completed with errors');
        }
    });
}
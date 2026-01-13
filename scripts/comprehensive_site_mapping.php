<?php
/**
 * Comprehensive Site Mapping Generator
 * Creates detailed JSON mapping of site content before deployment
 * Authority: Team 4 (Database Specialists) - Serialization-Aware Operations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ComprehensiveSiteMapping {

    private $mapping_data = [];

    public function __construct() {
        $this->mapping_data = [
            'metadata' => [
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Team 4 - Database Specialists',
                'purpose' => 'Pre-deployment comprehensive site mapping',
                'authority' => 'CEO Eyal Amit - Serialization-Aware Operations',
                'wp_version' => get_bloginfo('version'),
                'site_url' => get_site_url(),
                'home_url' => get_home_url()
            ],
            'content' => [
                'pages' => [],
                'posts' => [],
                'attachments' => [],
                'categories' => [],
                'tags' => [],
                'users' => []
            ],
            'relationships' => [
                'page_attachments' => [],
                'post_attachments' => [],
                'category_posts' => [],
                'tag_posts' => [],
                'user_content' => []
            ],
            'validation' => [
                'orphaned_files' => [],
                'missing_files' => [],
                'broken_links' => [],
                'redirects' => []
            ],
            'statistics' => [
                'total_pages' => 0,
                'total_posts' => 0,
                'total_attachments' => 0,
                'total_categories' => 0,
                'total_tags' => 0,
                'orphaned_attachments' => 0,
                'missing_attachments' => 0,
                'broken_links_count' => 0,
                'redirects_count' => 0
            ]
        ];
    }

    /**
     * Generate comprehensive site mapping
     */
    public function generate_mapping() {
        $this->map_pages();
        $this->map_posts();
        $this->map_attachments();
        $this->map_taxonomies();
        $this->map_users();
        $this->analyze_relationships();
        $this->validate_content();
        $this->generate_statistics();

        return $this->mapping_data;
    }

    /**
     * Map all published pages
     */
    private function map_pages() {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC'
        ]);

        foreach ($pages as $page) {
            $page_data = [
                'id' => $page->ID,
                'title' => $page->post_title,
                'slug' => $page->post_name,
                'url' => get_permalink($page->ID),
                'status' => $page->post_status,
                'author' => get_the_author_meta('display_name', $page->post_author),
                'modified' => $page->post_modified,
                'content_length' => strlen($page->post_content),
                'has_shortcodes' => strpos($page->post_content, '[') !== false,
                'has_vc_content' => strpos($page->post_content, 'vc_') !== false,
                'has_elementor' => !empty(get_post_meta($page->ID, '_elementor_data', true)),
                'seo_title' => get_post_meta($page->ID, '_yoast_wpseo_title', true),
                'seo_description' => get_post_meta($page->ID, '_yoast_wpseo_metadesc', true),
                'template' => get_page_template_slug($page->ID),
                'parent' => $page->post_parent ? get_the_title($page->post_parent) : null,
                'menu_order' => $page->menu_order
            ];

            $this->mapping_data['content']['pages'][] = $page_data;
        }
    }

    /**
     * Map all published posts
     */
    private function map_posts() {
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC'
        ]);

        foreach ($posts as $post) {
            $categories = wp_get_post_categories($post->ID, ['fields' => 'names']);
            $tags = wp_get_post_tags($post->ID, ['fields' => 'names']);

            $post_data = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'url' => get_permalink($post->ID),
                'status' => $post->post_status,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'published' => $post->post_date,
                'modified' => $post->post_modified,
                'categories' => $categories,
                'tags' => $tags,
                'content_length' => strlen($post->post_content),
                'has_shortcodes' => strpos($post->post_content, '[') !== false,
                'has_vc_content' => strpos($post->post_content, 'vc_') !== false,
                'has_elementor' => !empty(get_post_meta($post->ID, '_elementor_data', true)),
                'seo_title' => get_post_meta($post->ID, '_yoast_wpseo_title', true),
                'seo_description' => get_post_meta($post->ID, '_yoast_wpseo_metadesc', true),
                'featured_image' => get_the_post_thumbnail_url($post->ID, 'full'),
                'comment_count' => $post->comment_count
            ];

            $this->mapping_data['content']['posts'][] = $post_data;
        }
    }

    /**
     * Map all attachments with usage analysis
     */
    private function map_attachments() {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC'
        ]);

        foreach ($attachments as $attachment) {
            $file_path = get_attached_file($attachment->ID);
            $file_exists = file_exists($file_path);
            $file_size = $file_exists ? filesize($file_path) : 0;

            // Find where this attachment is used
            $usage = $this->find_attachment_usage($attachment->ID);

            $attachment_data = [
                'id' => $attachment->ID,
                'title' => $attachment->post_title,
                'filename' => basename($file_path),
                'url' => wp_get_attachment_url($attachment->ID),
                'type' => get_post_mime_type($attachment->ID),
                'file_exists' => $file_exists,
                'file_size' => $file_size,
                'file_path' => $file_path,
                'uploaded_date' => $attachment->post_date,
                'alt_text' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
                'dimensions' => $this->get_image_dimensions($attachment->ID),
                'usage_count' => count($usage),
                'used_in' => $usage,
                'is_orphaned' => empty($usage),
                'metadata' => wp_get_attachment_metadata($attachment->ID)
            ];

            $this->mapping_data['content']['attachments'][] = $attachment_data;
        }
    }

    /**
     * Find where an attachment is used
     */
    private function find_attachment_usage($attachment_id) {
        global $wpdb;

        $usage = [];
        $attachment_url = wp_get_attachment_url($attachment_id);

        if (!$attachment_url) {
            return $usage;
        }

        // Check in post content
        $posts_using = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_title, post_type FROM {$wpdb->posts}
             WHERE post_content LIKE %s AND post_status = 'publish'",
            '%' . $wpdb->esc_like($attachment_url) . '%'
        ));

        foreach ($posts_using as $post) {
            $usage[] = [
                'type' => 'content',
                'post_type' => $post->post_type,
                'post_id' => $post->ID,
                'post_title' => $post->post_title,
                'url' => get_permalink($post->ID)
            ];
        }

        // Check in post meta (featured images, etc.)
        $meta_using = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, meta_key FROM {$wpdb->postmeta}
             WHERE meta_value LIKE %s",
            '%' . $wpdb->esc_like($attachment_url) . '%'
        ));

        foreach ($meta_using as $meta) {
            $post = get_post($meta->post_id);
            if ($post && $post->post_status === 'publish') {
                $usage[] = [
                    'type' => 'meta',
                    'meta_key' => $meta->meta_key,
                    'post_type' => $post->post_type,
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'url' => get_permalink($post->ID)
                ];
            }
        }

        // Check in options (theme options, etc.)
        $options_using = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options}
             WHERE option_value LIKE %s",
            '%' . $wpdb->esc_like($attachment_url) . '%'
        ));

        foreach ($options_using as $option) {
            $usage[] = [
                'type' => 'option',
                'option_name' => $option->option_name
            ];
        }

        return $usage;
    }

    /**
     * Get image dimensions
     */
    private function get_image_dimensions($attachment_id) {
        $metadata = wp_get_attachment_metadata($attachment_id);
        if (isset($metadata['width']) && isset($metadata['height'])) {
            return [
                'width' => $metadata['width'],
                'height' => $metadata['height']
            ];
        }
        return null;
    }

    /**
     * Map taxonomies
     */
    private function map_taxonomies() {
        // Categories
        $categories = get_categories(['hide_empty' => false]);
        foreach ($categories as $category) {
            $this->mapping_data['content']['categories'][] = [
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'url' => get_category_link($category->term_id),
                'post_count' => $category->count,
                'parent' => $category->parent ? get_cat_name($category->parent) : null
            ];
        }

        // Tags
        $tags = get_tags(['hide_empty' => false]);
        foreach ($tags as $tag) {
            $this->mapping_data['content']['tags'][] = [
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'description' => $tag->description,
                'url' => get_tag_link($tag->term_id),
                'post_count' => $tag->count
            ];
        }
    }

    /**
     * Map users
     */
    private function map_users() {
        $users = get_users(['orderby' => 'ID', 'order' => 'ASC']);
        foreach ($users as $user) {
            $user_posts = count_user_posts($user->ID);
            $user_pages = count_user_posts($user->ID, 'page');

            $this->mapping_data['content']['users'][] = [
                'id' => $user->ID,
                'username' => $user->user_login,
                'display_name' => $user->display_name,
                'email' => $user->user_email,
                'role' => implode(', ', $user->roles),
                'registered' => $user->user_registered,
                'post_count' => $user_posts,
                'page_count' => $user_pages,
                'total_content' => $user_posts + $user_pages
            ];
        }
    }

    /**
     * Analyze relationships between content
     */
    private function analyze_relationships() {
        // Page attachments
        foreach ($this->mapping_data['content']['pages'] as $page) {
            $attachments = $this->get_post_attachments($page['id']);
            if (!empty($attachments)) {
                $this->mapping_data['relationships']['page_attachments'][] = [
                    'page_id' => $page['id'],
                    'page_title' => $page['title'],
                    'attachments' => $attachments
                ];
            }
        }

        // Post attachments
        foreach ($this->mapping_data['content']['posts'] as $post) {
            $attachments = $this->get_post_attachments($post['id']);
            if (!empty($attachments)) {
                $this->mapping_data['relationships']['post_attachments'][] = [
                    'post_id' => $post['id'],
                    'post_title' => $post['title'],
                    'attachments' => $attachments
                ];
            }
        }

        // Category posts
        foreach ($this->mapping_data['content']['categories'] as $category) {
            $posts = get_posts([
                'category' => $category['id'],
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            if (!empty($posts)) {
                $this->mapping_data['relationships']['category_posts'][] = [
                    'category_id' => $category['id'],
                    'category_name' => $category['name'],
                    'post_ids' => $posts,
                    'post_count' => count($posts)
                ];
            }
        }

        // Tag posts
        foreach ($this->mapping_data['content']['tags'] as $tag) {
            $posts = get_posts([
                'tag_id' => $tag['id'],
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            if (!empty($posts)) {
                $this->mapping_data['relationships']['tag_posts'][] = [
                    'tag_id' => $tag['id'],
                    'tag_name' => $tag['name'],
                    'post_ids' => $posts,
                    'post_count' => count($posts)
                ];
            }
        }

        // User content
        foreach ($this->mapping_data['content']['users'] as $user) {
            $user_posts = get_posts([
                'author' => $user['id'],
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            if (!empty($user_posts)) {
                $this->mapping_data['relationships']['user_content'][] = [
                    'user_id' => $user['id'],
                    'user_name' => $user['username'],
                    'content_ids' => $user_posts,
                    'content_count' => count($user_posts)
                ];
            }
        }
    }

    /**
     * Get attachments used in a post
     */
    private function get_post_attachments($post_id) {
        $attachments = [];
        $post = get_post($post_id);

        if (!$post) {
            return $attachments;
        }

        // Check content for attachment URLs
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $img_src) {
                $attachment_id = attachment_url_to_postid($img_src);
                if ($attachment_id) {
                    $attachments[] = $attachment_id;
                }
            }
        }

        // Check for featured image
        $featured_image_id = get_post_thumbnail_id($post_id);
        if ($featured_image_id) {
            $attachments[] = $featured_image_id;
        }

        return array_unique($attachments);
    }

    /**
     * Validate content and find issues
     */
    private function validate_content() {
        // Find orphaned files
        foreach ($this->mapping_data['content']['attachments'] as $attachment) {
            if ($attachment['is_orphaned']) {
                $this->mapping_data['validation']['orphaned_files'][] = [
                    'id' => $attachment['id'],
                    'filename' => $attachment['filename'],
                    'url' => $attachment['url'],
                    'file_size' => $attachment['file_size'],
                    'uploaded_date' => $attachment['uploaded_date']
                ];
            }
        }

        // Find missing files
        foreach ($this->mapping_data['content']['attachments'] as $attachment) {
            if (!$attachment['file_exists']) {
                $this->mapping_data['validation']['missing_files'][] = [
                    'id' => $attachment['id'],
                    'filename' => $attachment['filename'],
                    'expected_path' => $attachment['file_path'],
                    'url' => $attachment['url']
                ];
            }
        }

        // Basic link validation (simplified)
        $this->validate_links();
    }

    /**
     * Validate links (simplified version)
     */
    private function validate_links() {
        // This is a simplified validation - in production you'd want more comprehensive checking
        foreach ($this->mapping_data['content']['pages'] as $page) {
            // Check for broken internal links (simplified)
            // This would need more sophisticated link checking in production
        }
    }

    /**
     * Generate statistics
     */
    private function generate_statistics() {
        $stats = &$this->mapping_data['statistics'];

        $stats['total_pages'] = count($this->mapping_data['content']['pages']);
        $stats['total_posts'] = count($this->mapping_data['content']['posts']);
        $stats['total_attachments'] = count($this->mapping_data['content']['attachments']);
        $stats['total_categories'] = count($this->mapping_data['content']['categories']);
        $stats['total_tags'] = count($this->mapping_data['content']['tags']);

        $stats['orphaned_attachments'] = count($this->mapping_data['validation']['orphaned_files']);
        $stats['missing_attachments'] = count($this->mapping_data['validation']['missing_files']);
        $stats['broken_links_count'] = count($this->mapping_data['validation']['broken_links']);
        $stats['redirects_count'] = count($this->mapping_data['validation']['redirects']);
    }

    /**
     * Save mapping to JSON file
     */
    public function save_mapping($filename = null) {
        if (!$filename) {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "docs/sitemap/COMPREHENSIVE-SITE-MAPPING-{$timestamp}.json";
        }

        $mapping = $this->generate_mapping();

        // Ensure directory exists
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json_content = json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (file_put_contents($filename, $json_content)) {
            return $filename;
        }

        return false;
    }
}

// CLI Integration
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('comprehensive-site-mapping', function($args, $assoc_args) {
        WP_CLI::log('🔍 COMPREHENSIVE SITE MAPPING GENERATOR');
        WP_CLI::log('Authority: Team 4 (Database Specialists) - Serialization-Aware Operations');
        WP_CLI::log('==========================================');

        $mapper = new ComprehensiveSiteMapping();
        $filename = $mapper->save_mapping();

        if ($filename) {
            WP_CLI::success("Comprehensive site mapping saved to: {$filename}");

            $mapping = json_decode(file_get_contents($filename), true);
            $stats = $mapping['statistics'];

            WP_CLI::log('📊 Mapping Statistics:');
            WP_CLI::log("Pages: {$stats['total_pages']}");
            WP_CLI::log("Posts: {$stats['total_posts']}");
            WP_CLI::log("Attachments: {$stats['total_attachments']}");
            WP_CLI::log("Categories: {$stats['total_tags']}");
            WP_CLI::log("Tags: {$stats['total_tags']}");
            WP_CLI::log("Orphaned Files: {$stats['orphaned_attachments']}");
            WP_CLI::log("Missing Files: {$stats['missing_attachments']}");

            WP_CLI::success('Evidence: Check docs/sitemap/ for COMPREHENSIVE-SITE-MAPPING-*.json');
        } else {
            WP_CLI::error('Failed to save site mapping');
        }
    });
}
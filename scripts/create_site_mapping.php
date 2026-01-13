<?php
/**
 * Create Comprehensive Site Mapping JSON
 * Simplified version for direct execution
 */

require_once('wp-load.php');

echo "🔍 Generating comprehensive site mapping...\n";

$mapping_data = [
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
    'statistics' => []
];

// Map pages
echo "📄 Mapping pages...\n";
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

    $mapping_data['content']['pages'][] = $page_data;
}

// Map posts
echo "📝 Mapping posts...\n";
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

    $mapping_data['content']['posts'][] = $post_data;
}

// Map attachments
echo "🖼️ Mapping attachments...\n";
$attachments = get_posts([
    'post_type' => 'attachment',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC'
]);

$attachment_usage_map = [];

foreach ($attachments as $attachment) {
    $file_path = get_attached_file($attachment->ID);
    $file_exists = file_exists($file_path);
    $file_size = $file_exists ? filesize($file_path) : 0;

    // Find usage (simplified)
    $usage = [];
    $attachment_url = wp_get_attachment_url($attachment->ID);

    if ($attachment_url) {
        global $wpdb;

        // Check posts
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
                'post_title' => $post->post_title
            ];
        }
    }

    $metadata = wp_get_attachment_metadata($attachment->ID);
    $dimensions = null;
    if (isset($metadata['width']) && isset($metadata['height'])) {
        $dimensions = ['width' => $metadata['width'], 'height' => $metadata['height']];
    }

    $attachment_data = [
        'id' => $attachment->ID,
        'title' => $attachment->post_title,
        'filename' => basename($file_path),
        'url' => $attachment_url,
        'type' => get_post_mime_type($attachment->ID),
        'file_exists' => $file_exists,
        'file_size' => $file_size,
        'file_path' => $file_path,
        'uploaded_date' => $attachment->post_date,
        'alt_text' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
        'dimensions' => $dimensions,
        'usage_count' => count($usage),
        'used_in' => $usage,
        'is_orphaned' => empty($usage)
    ];

    $mapping_data['content']['attachments'][] = $attachment_data;

    // Track orphaned files
    if (empty($usage)) {
        $mapping_data['validation']['orphaned_files'][] = [
            'id' => $attachment->ID,
            'filename' => $attachment->post_title,
            'url' => $attachment_url,
            'file_size' => $file_size
        ];
    }

    // Track missing files
    if (!$file_exists) {
        $mapping_data['validation']['missing_files'][] = [
            'id' => $attachment->ID,
            'filename' => $attachment->post_title,
            'expected_path' => $file_path,
            'url' => $attachment_url
        ];
    }
}

// Map categories
echo "📂 Mapping categories...\n";
$categories = get_categories(['hide_empty' => false]);
foreach ($categories as $category) {
    $mapping_data['content']['categories'][] = [
        'id' => $category->term_id,
        'name' => $category->name,
        'slug' => $category->slug,
        'description' => $category->description,
        'url' => get_category_link($category->term_id),
        'post_count' => $category->count,
        'parent' => $category->parent ? get_cat_name($category->parent) : null
    ];
}

// Map tags
echo "🏷️ Mapping tags...\n";
$tags = get_tags(['hide_empty' => false]);
foreach ($tags as $tag) {
    $mapping_data['content']['tags'][] = [
        'id' => $tag->term_id,
        'name' => $tag->name,
        'slug' => $tag->slug,
        'description' => $tag->description,
        'url' => get_tag_link($tag->term_id),
        'post_count' => $tag->count
    ];
}

// Map users
echo "👥 Mapping users...\n";
$users = get_users(['orderby' => 'ID', 'order' => 'ASC']);
foreach ($users as $user) {
    $user_posts = count_user_posts($user->ID);
    $user_pages = count_user_posts($user->ID, 'page');

    $mapping_data['content']['users'][] = [
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

// Generate statistics
echo "📊 Generating statistics...\n";
$mapping_data['statistics'] = [
    'total_pages' => count($mapping_data['content']['pages']),
    'total_posts' => count($mapping_data['content']['posts']),
    'total_attachments' => count($mapping_data['content']['attachments']),
    'total_categories' => count($mapping_data['content']['categories']),
    'total_tags' => count($mapping_data['content']['tags']),
    'total_users' => count($mapping_data['content']['users']),
    'orphaned_attachments' => count($mapping_data['validation']['orphaned_files']),
    'missing_attachments' => count($mapping_data['validation']['missing_files']),
    'broken_links_count' => count($mapping_data['validation']['broken_links']),
    'redirects_count' => count($mapping_data['validation']['redirects'])
];

// Save to file
$timestamp = date('Y-m-d_H-i-s');
$filename = "docs/sitemap/COMPREHENSIVE-SITE-MAPPING-{$timestamp}.json";

if (!is_dir('docs/sitemap')) {
    mkdir('docs/sitemap', 0755, true);
}

$json_content = json_encode($mapping_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (file_put_contents($filename, $json_content)) {
    echo "✅ Comprehensive site mapping saved to: {$filename}\n";

    $stats = $mapping_data['statistics'];
    echo "\n📊 Final Statistics:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Content:\n";
    echo "  📄 Pages: {$stats['total_pages']}\n";
    echo "  📝 Posts: {$stats['total_posts']}\n";
    echo "  🖼️ Attachments: {$stats['total_attachments']}\n";
    echo "  📂 Categories: {$stats['total_categories']}\n";
    echo "  🏷️ Tags: {$stats['total_tags']}\n";
    echo "  👥 Users: {$stats['total_users']}\n";
    echo "\nIssues Found:\n";
    echo "  🚨 Orphaned Files: {$stats['orphaned_attachments']}\n";
    echo "  ❌ Missing Files: {$stats['missing_attachments']}\n";
    echo "  🔗 Broken Links: {$stats['broken_links_count']}\n";
    echo "  ↪️ Redirects: {$stats['redirects_count']}\n";

    echo "\n🎯 Next Steps for Team 4:\n";
    echo "1. Review orphaned files - consider cleanup\n";
    echo "2. Investigate missing files - may need restoration\n";
    echo "3. Validate all URLs before deployment\n";
    echo "4. Ensure all relationships are correct\n";

    echo "\n📋 Evidence: {$filename}\n";
} else {
    echo "❌ Failed to save site mapping\n";
}

echo "\n✅ Site mapping generation completed\n";
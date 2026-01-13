<?php
/**
 * Quick Comprehensive Site Mapping
 * Focused on core content mapping
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
    $mapping_data['content']['pages'][] = [
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
}

echo "Found " . count($mapping_data['content']['pages']) . " pages\n";

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

    $mapping_data['content']['posts'][] = [
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
}

echo "Found " . count($mapping_data['content']['posts']) . " posts\n";

// Quick attachment mapping (simplified)
echo "🖼️ Mapping attachments (quick scan)...\n";
$attachments = get_posts([
    'post_type' => 'attachment',
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC'
]);

$orphaned_count = 0;
$missing_count = 0;

foreach ($attachments as $attachment) {
    $file_path = get_attached_file($attachment->ID);
    $file_exists = file_exists($file_path);
    $file_size = $file_exists ? filesize($file_path) : 0;

    // Quick usage check (simplified)
    global $wpdb;
    $attachment_url = wp_get_attachment_url($attachment->ID);
    $usage_count = 0;

    if ($attachment_url) {
        $usage_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_content LIKE %s AND post_status = 'publish'",
            '%' . $wpdb->esc_like($attachment_url) . '%'
        ));
    }

    $is_orphaned = $usage_count == 0;

    if ($is_orphaned) $orphaned_count++;
    if (!$file_exists) $missing_count++;

    $mapping_data['content']['attachments'][] = [
        'id' => $attachment->ID,
        'title' => $attachment->post_title,
        'filename' => basename($file_path),
        'url' => $attachment_url,
        'type' => get_post_mime_type($attachment->ID),
        'file_exists' => $file_exists,
        'file_size' => $file_size,
        'usage_count' => $usage_count,
        'is_orphaned' => $is_orphaned
    ];
}

echo "Found " . count($mapping_data['content']['attachments']) . " attachments ($orphaned_count orphaned, $missing_count missing)\n";

// Map categories and tags
echo "📂 Mapping categories and tags...\n";
$mapping_data['content']['categories'] = array_map(function($cat) {
    return [
        'id' => $cat->term_id,
        'name' => $cat->name,
        'slug' => $cat->slug,
        'post_count' => $cat->count
    ];
}, get_categories(['hide_empty' => false]));

$mapping_data['content']['tags'] = array_map(function($tag) {
    return [
        'id' => $tag->term_id,
        'name' => $tag->name,
        'slug' => $tag->slug,
        'post_count' => $tag->count
    ];
}, get_tags(['hide_empty' => false]));

echo "Found " . count($mapping_data['content']['categories']) . " categories, " . count($mapping_data['content']['tags']) . " tags\n";

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
        'post_count' => $user_posts,
        'page_count' => $user_pages,
        'total_content' => $user_posts + $user_pages
    ];
}

echo "Found " . count($mapping_data['content']['users']) . " users\n";

// Generate statistics
$mapping_data['statistics'] = [
    'total_pages' => count($mapping_data['content']['pages']),
    'total_posts' => count($mapping_data['content']['posts']),
    'total_attachments' => count($mapping_data['content']['attachments']),
    'total_categories' => count($mapping_data['content']['categories']),
    'total_tags' => count($mapping_data['content']['tags']),
    'total_users' => count($mapping_data['content']['users']),
    'orphaned_attachments' => $orphaned_count,
    'missing_attachments' => $missing_count,
    'broken_links_count' => 0,
    'redirects_count' => 0
];

// Save to file
$timestamp = date('Y-m-d_H-i-s');
$filename = "docs/sitemap/COMPREHENSIVE-SITE-MAPPING-{$timestamp}.json";

if (!is_dir('docs/sitemap')) {
    mkdir('docs/sitemap', 0755, true);
}

$json_content = json_encode($mapping_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if (file_put_contents($filename, $json_content)) {
    echo "\n✅ Comprehensive site mapping saved to: {$filename}\n";

    $stats = $mapping_data['statistics'];
    echo "\n📊 Final Statistics:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📄 Pages: {$stats['total_pages']}\n";
    echo "📝 Posts: {$stats['total_posts']}\n";
    echo "🖼️ Attachments: {$stats['total_attachments']}\n";
    echo "📂 Categories: {$stats['total_categories']}\n";
    echo "🏷️ Tags: {$stats['total_tags']}\n";
    echo "👥 Users: {$stats['total_users']}\n";
    echo "🚨 Orphaned Files: {$stats['orphaned_attachments']}\n";
    echo "❌ Missing Files: {$stats['missing_attachments']}\n";

    echo "\n🎯 Key Findings:\n";
    if ($orphaned_count > 0) {
        echo "⚠️ Found {$orphaned_count} orphaned attachment files\n";
    }
    if ($missing_count > 0) {
        echo "⚠️ Found {$missing_count} missing attachment files\n";
    }

    echo "\n📋 Evidence: {$filename}\n";
} else {
    echo "❌ Failed to save site mapping\n";
}

echo "\n✅ Site mapping generation completed\n";
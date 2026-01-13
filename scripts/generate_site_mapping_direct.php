<?php
/**
 * Generate Site Mapping Directly
 * Standalone script that creates JSON mapping
 */

// Database connection
$host = 'localhost';
$db_name = 'eyalamit_db';
$username = 'eyalamit_user';
$password = 'user_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "🔍 Connected to database successfully\n";
    echo "📊 Generating comprehensive site mapping...\n";

    $mapping_data = [
        'metadata' => [
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => 'Team 4 - Database Specialists',
            'purpose' => 'Pre-deployment comprehensive site mapping',
            'authority' => 'CEO Eyal Amit - Serialization-Aware Operations',
            'database' => $db_name,
            'site_url' => 'http://localhost:9090'
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

    // Get pages
    echo "📄 Loading pages...\n";
    $stmt = $pdo->query("
        SELECT ID, post_title, post_name, post_status, post_author, post_modified, post_content, post_parent, menu_order
        FROM wp_posts
        WHERE post_type = 'page' AND post_status = 'publish'
        ORDER BY ID ASC
    ");
    $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pages as $page) {
        $mapping_data['content']['pages'][] = [
            'id' => (int)$page['ID'],
            'title' => $page['post_title'],
            'slug' => $page['post_name'],
            'url' => "http://localhost:9090/{$page['post_name']}/",
            'status' => $page['post_status'],
            'author_id' => (int)$page['post_author'],
            'modified' => $page['post_modified'],
            'content_length' => strlen($page['post_content']),
            'has_shortcodes' => strpos($page['post_content'], '[') !== false,
            'has_vc_content' => strpos($page['post_content'], 'vc_') !== false,
            'parent' => $page['post_parent'] ? (int)$page['post_parent'] : null,
            'menu_order' => (int)$page['menu_order']
        ];
    }

    echo "Found " . count($mapping_data['content']['pages']) . " pages\n";

    // Get posts
    echo "📝 Loading posts...\n";
    $stmt = $pdo->query("
        SELECT ID, post_title, post_name, post_status, post_author, post_date, post_modified, post_content, comment_count
        FROM wp_posts
        WHERE post_type = 'post' AND post_status = 'publish'
        ORDER BY ID ASC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as $post) {
        $mapping_data['content']['posts'][] = [
            'id' => (int)$post['ID'],
            'title' => $post['post_title'],
            'slug' => $post['post_name'],
            'url' => "http://localhost:9090/{$post['post_name']}/",
            'status' => $post['post_status'],
            'author_id' => (int)$post['post_author'],
            'published' => $post['post_date'],
            'modified' => $post['post_modified'],
            'content_length' => strlen($post['post_content']),
            'has_shortcodes' => strpos($post['post_content'], '[') !== false,
            'has_vc_content' => strpos($post['post_content'], 'vc_') !== false,
            'comment_count' => (int)$post['comment_count']
        ];
    }

    echo "Found " . count($mapping_data['content']['posts']) . " posts\n";

    // Get attachments (simplified)
    echo "🖼️ Loading attachments...\n";
    $stmt = $pdo->query("
        SELECT ID, post_title, post_name, post_date, guid
        FROM wp_posts
        WHERE post_type = 'attachment'
        ORDER BY ID ASC
    ");
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orphaned_count = 0;
    $missing_count = 0;

    foreach ($attachments as $attachment) {
        // Check if file exists (simplified check)
        $file_path = str_replace('http://localhost:9090/wp-content/uploads/', '/var/www/html/wp-content/uploads/', $attachment['guid']);
        $file_exists = file_exists($file_path);
        $file_size = $file_exists ? filesize($file_path) : 0;

        // Quick usage check
        $stmt_usage = $pdo->prepare("
            SELECT COUNT(*) as usage_count
            FROM wp_posts
            WHERE post_content LIKE ?
            AND post_status = 'publish'
            AND post_type IN ('post', 'page')
        ");
        $stmt_usage->execute(['%' . $attachment['guid'] . '%']);
        $usage_result = $stmt_usage->fetch(PDO::FETCH_ASSOC);
        $usage_count = (int)$usage_result['usage_count'];

        $is_orphaned = $usage_count == 0;

        if ($is_orphaned) $orphaned_count++;
        if (!$file_exists) $missing_count++;

        $mapping_data['content']['attachments'][] = [
            'id' => (int)$attachment['ID'],
            'title' => $attachment['post_title'],
            'filename' => basename($attachment['guid']),
            'url' => $attachment['guid'],
            'file_exists' => $file_exists,
            'file_size' => $file_size,
            'uploaded_date' => $attachment['post_date'],
            'usage_count' => $usage_count,
            'is_orphaned' => $is_orphaned
        ];
    }

    echo "Found " . count($mapping_data['content']['attachments']) . " attachments ($orphaned_count orphaned, $missing_count missing)\n";

    // Get categories
    echo "📂 Loading categories...\n";
    $stmt = $pdo->query("
        SELECT t.term_id, t.name, t.slug, tt.count
        FROM wp_terms t
        JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id
        WHERE tt.taxonomy = 'category'
        ORDER BY t.term_id ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as $category) {
        $mapping_data['content']['categories'][] = [
            'id' => (int)$category['term_id'],
            'name' => $category['name'],
            'slug' => $category['slug'],
            'post_count' => (int)$category['count']
        ];
    }

    // Get tags
    echo "🏷️ Loading tags...\n";
    $stmt = $pdo->query("
        SELECT t.term_id, t.name, t.slug, tt.count
        FROM wp_terms t
        JOIN wp_term_taxonomy tt ON t.term_id = tt.term_id
        WHERE tt.taxonomy = 'post_tag'
        ORDER BY t.term_id ASC
    ");
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tags as $tag) {
        $mapping_data['content']['tags'][] = [
            'id' => (int)$tag['term_id'],
            'name' => $tag['name'],
            'slug' => $tag['slug'],
            'post_count' => (int)$tag['count']
        ];
    }

    echo "Found " . count($mapping_data['content']['categories']) . " categories, " . count($mapping_data['content']['tags']) . " tags\n";

    // Get users
    echo "👥 Loading users...\n";
    $stmt = $pdo->query("
        SELECT ID, user_login, display_name, user_email, user_registered
        FROM wp_users
        ORDER BY ID ASC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        // Get user roles and content count
        $stmt_roles = $pdo->prepare("
            SELECT meta_value FROM wp_usermeta
            WHERE user_id = ? AND meta_key = 'wp_capabilities'
        ");
        $stmt_roles->execute([$user['ID']]);
        $roles_data = $stmt_roles->fetch(PDO::FETCH_ASSOC);
        $roles = [];
        if ($roles_data && $roles_data['meta_value']) {
            $capabilities = unserialize($roles_data['meta_value']);
            $roles = array_keys(array_filter($capabilities));
        }

        // Count user content
        $stmt_content = $pdo->prepare("
            SELECT COUNT(*) as post_count FROM wp_posts
            WHERE post_author = ? AND post_type = 'post' AND post_status = 'publish'
        ");
        $stmt_content->execute([$user['ID']]);
        $post_count = $stmt_content->fetch(PDO::FETCH_ASSOC)['post_count'];

        $stmt_pages = $pdo->prepare("
            SELECT COUNT(*) as page_count FROM wp_posts
            WHERE post_author = ? AND post_type = 'page' AND post_status = 'publish'
        ");
        $stmt_pages->execute([$user['ID']]);
        $page_count = $stmt_pages->fetch(PDO::FETCH_ASSOC)['page_count'];

        $mapping_data['content']['users'][] = [
            'id' => (int)$user['ID'],
            'username' => $user['user_login'],
            'display_name' => $user['display_name'],
            'email' => $user['user_email'],
            'role' => implode(', ', $roles),
            'registered' => $user['user_registered'],
            'post_count' => (int)$post_count,
            'page_count' => (int)$page_count,
            'total_content' => (int)$post_count + (int)$page_count
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
        echo "\n✅ Site mapping generation completed successfully\n";
    } else {
        echo "❌ Failed to save site mapping\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
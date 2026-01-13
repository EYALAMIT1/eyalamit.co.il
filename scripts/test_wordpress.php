<?php
// Simple test script
require_once('wp-load.php');

echo "WordPress loaded successfully\n";
echo "Version: " . get_bloginfo('version') . "\n";
echo "Site URL: " . get_site_url() . "\n";

$pages = get_posts(['post_type' => 'page', 'posts_per_page' => 5]);
echo "Sample pages: " . count($pages) . "\n";

$posts = get_posts(['post_type' => 'post', 'posts_per_page' => 5]);
echo "Sample posts: " . count($posts) . "\n";

$attachments = get_posts(['post_type' => 'attachment', 'posts_per_page' => 5]);
echo "Sample attachments: " . count($attachments) . "\n";

echo "Test completed\n";
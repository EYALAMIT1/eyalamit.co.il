<?php
/**
 * Test WebP conversion functionality
 */

define('WP_USE_THEMES', false);
require_once('wp-load.php');

echo "🔍 TESTING WEBP CONVERSION FUNCTIONALITY\n";
echo "=======================================\n\n";

// Check if GD WebP support is available
echo "1. GD WebP Support:\n";
if (function_exists('imagewebp')) {
    echo "✅ GD WebP support is available\n";
} else {
    echo "❌ GD WebP support is NOT available\n";
    echo "   WebP conversion will not work\n";
}

// Check existing images for WebP conversion
echo "\n2. Checking existing images:\n";

$recent_images = get_posts(array(
    'post_type' => 'attachment',
    'post_mime_type' => array('image/jpeg', 'image/png'),
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
));

if (!empty($recent_images)) {
    echo "Found " . count($recent_images) . " recent images\n";

    foreach ($recent_images as $image) {
        $webp_file = get_post_meta($image->ID, '_webp_file', true);
        $image_url = wp_get_attachment_url($image->ID);

        echo "  - {$image->post_title}: ";
        if ($webp_file && file_exists($webp_file)) {
            echo "✅ WebP exists (" . basename($webp_file) . ")\n";
        } else {
            echo "❌ No WebP version\n";
        }
    }
} else {
    echo "No images found in media library\n";
}

// Test WebP conversion function directly
echo "\n3. Testing conversion function:\n";

if (!empty($recent_images)) {
    $test_image = $recent_images[0];
    $file = get_attached_file($test_image->ID);

    if ($file && file_exists($file)) {
        echo "Testing conversion on: " . basename($file) . "\n";

        // Simulate the conversion process
        $file_info = pathinfo($file);
        $webp_file = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';

        if (file_exists($webp_file)) {
            echo "✅ WebP file already exists\n";
        } else {
            // Try to create WebP
            $image = null;
            switch (strtolower($file_info['extension'])) {
                case 'jpg':
                case 'jpeg':
                    $image = @imagecreatefromjpeg($file);
                    break;
                case 'png':
                    $image = @imagecreatefrompng($file);
                    if ($image) {
                        imagealphablending($image, false);
                        imagesavealpha($image, true);
                    }
                    break;
            }

            if ($image) {
                $success = @imagewebp($image, $webp_file, 85);
                imagedestroy($image);

                if ($success && file_exists($webp_file)) {
                    echo "✅ WebP conversion successful\n";
                    update_post_meta($test_image->ID, '_webp_file', $webp_file);
                } else {
                    echo "❌ WebP conversion failed\n";
                }
            } else {
                echo "❌ Could not load image for conversion\n";
            }
        }
    } else {
        echo "❌ Test image file not found\n";
    }
}

echo "\n🎯 WEBP CONVERSION TEST COMPLETE\n";
echo "================================\n";
echo "Next: Create implementation report\n";
?>
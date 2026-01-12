<?php
/**
 * Bulk Image Optimization Script
 * Converts existing images to WebP and adds responsive images
 *
 * Usage: Run via command line or browser
 * php bulk-image-optimization.php
 *
 * @version 1.0.0
 */

// Prevent direct web access (optional)
if (isset($_SERVER['HTTP_HOST'])) {
    die('This script should be run from command line only.');
}

// WordPress environment
require_once(dirname(__FILE__) . '/../../../wp-load.php');

// Configuration
$batch_size = 50; // Process 50 images at a time
$max_execution_time = 300; // 5 minutes max
$supported_formats = ['jpg', 'jpeg', 'png'];

// Set time limit
set_time_limit($max_execution_time);
ini_set('memory_limit', '512M');

echo "=== Bulk Image Optimization Script 2026 ===\n";
echo "Starting optimization process...\n\n";

// Get all attachment IDs
$args = [
    'post_type' => 'attachment',
    'post_mime_type' => ['image/jpeg', 'image/png'],
    'posts_per_page' => -1,
    'fields' => 'ids'
];

$attachments = get_posts($args);
$total_images = count($attachments);

echo "Found {$total_images} images to process\n\n";

$processed = 0;
$converted = 0;
$errors = 0;

foreach (array_chunk($attachments, $batch_size) as $batch) {
    echo "Processing batch " . (floor($processed / $batch_size) + 1) . "...\n";

    foreach ($batch as $attachment_id) {
        $file_path = get_attached_file($attachment_id);

        if (!file_exists($file_path)) {
            echo "  ❌ File not found: {$file_path}\n";
            $errors++;
            continue;
        }

        // Convert main image to WebP
        $webp_result = convert_to_webp($file_path);
        if ($webp_result) {
            echo "  ✅ Converted: " . basename($file_path) . "\n";
            $converted++;
        } else {
            echo "  ⚠️  Failed to convert: " . basename($file_path) . "\n";
        }

        // Generate missing image sizes
        $metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $metadata);

        $processed++;
    }

    // Progress report
    $progress = round(($processed / $total_images) * 100, 1);
    echo "Progress: {$progress}% ({$processed}/{$total_images})\n\n";

    // Prevent timeout
    if ($processed % $batch_size === 0) {
        sleep(1);
    }
}

echo "=== Optimization Complete ===\n";
echo "Total images processed: {$processed}\n";
echo "Images converted to WebP: {$converted}\n";
echo "Errors: {$errors}\n";
echo "Success rate: " . round(($converted / $processed) * 100, 1) . "%\n";

/**
 * Convert image to WebP
 */
function convert_to_webp($image_path) {
    if (!function_exists('imagewebp')) {
        return false;
    }

    $extension = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));

    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
        return false;
    }

    $webp_path = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $image_path);

    // Skip if WebP already exists
    if (file_exists($webp_path)) {
        return $webp_path;
    }

    // Create image resource
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $image = imagecreatefromjpeg($image_path);
            break;
        case 'png':
            $image = imagecreatefrompng($image_path);
            // Remove alpha channel for better compression
            $background = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $background);
            imagealphablending($image, false);
            imagesavealpha($image, false);
            break;
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    // Convert to WebP with 80% quality
    if (imagewebp($image, $webp_path, 80)) {
        // Set correct permissions
        chmod($webp_path, 0644);
        imagedestroy($image);
        return $webp_path;
    }

    imagedestroy($image);
    return false;
}

/**
 * Clean up old image files (optional)
 * Uncomment to remove original files after WebP conversion
 */
/*
function cleanup_original_images() {
    $upload_dir = wp_upload_dir();
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($upload_dir['basedir'])
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $path = $file->getPathname();
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $webp_path = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $path);
                if (file_exists($webp_path)) {
                    // Backup original before deleting
                    $backup_path = str_replace(['.jpg', '.jpeg', '.png'], '.bak', $path);
                    if (!file_exists($backup_path)) {
                        copy($path, $backup_path);
                    }
                    // Uncomment to actually delete
                    // unlink($path);
                    echo "Would delete: {$path}\n";
                }
            }
        }
    }
}

// Uncomment to run cleanup
// cleanup_original_images();
*/

echo "\n=== Script finished ===\n";
?>
<?php
/**
 * Alt-Text Inventory Script
 * Identifies images without alt tags in media library
 * 
 * @package EA_Alt_Text_Inventory
 * @version 1.0.0
 * @author Team 1 (Development)
 * 
 * Usage: Run via WP-CLI or add to functions.php temporarily
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get list of images without alt text
 * 
 * @return array List of images without alt tags
 */
function ea_get_images_without_alt() {
    global $wpdb;
    
    $images_without_alt = $wpdb->get_results("
        SELECT 
            p.ID,
            p.post_title,
            p.post_name,
            p.guid as image_url,
            pm.meta_value as _wp_attachment_image_alt,
            pm2.meta_value as _wp_attached_file
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_wp_attached_file'
        WHERE p.post_type = 'attachment'
        AND p.post_mime_type LIKE 'image/%'
        AND (pm.meta_value IS NULL OR pm.meta_value = '')
        ORDER BY p.post_date DESC
    ", ARRAY_A);
    
    return $images_without_alt;
}

/**
 * Generate alt text inventory report
 * 
 * @return string HTML report
 */
function ea_generate_alt_text_inventory_report() {
    $images = ea_get_images_without_alt();
    $total_images = count($images);
    
    $report = "<h2>Alt-Text Inventory Report</h2>\n";
    $report .= "<p><strong>Total images without alt text:</strong> {$total_images}</p>\n";
    
    if ($total_images > 0) {
        $report .= "<table border='1' cellpadding='5' cellspacing='0'>\n";
        $report .= "<tr><th>ID</th><th>Title</th><th>File Name</th><th>Image URL</th><th>Action</th></tr>\n";
        
        foreach ($images as $image) {
            $image_id = $image['ID'];
            $title = esc_html($image['post_title']);
            $file_name = esc_html($image['_wp_attached_file']);
            $image_url = esc_url($image['image_url']);
            
            $report .= "<tr>\n";
            $report .= "<td>{$image_id}</td>\n";
            $report .= "<td>{$title}</td>\n";
            $report .= "<td>{$file_name}</td>\n";
            $report .= "<td><a href='{$image_url}' target='_blank'>View Image</a></td>\n";
            $report .= "<td><a href='" . admin_url("post.php?post={$image_id}&action=edit") . "'>Edit in Media Library</a></td>\n";
            $report .= "</tr>\n";
        }
        
        $report .= "</table>\n";
    } else {
        $report .= "<p><strong>✅ All images have alt text!</strong></p>\n";
    }
    
    return $report;
}

/**
 * Auto-generate alt text from image title or filename
 * 
 * @param int $image_id Image attachment ID
 * @param string $alt_text Alt text to set (optional)
 * @return bool Success status
 */
function ea_auto_generate_alt_text($image_id, $alt_text = '') {
    if (empty($alt_text)) {
        // Generate from post title or filename
        $image = get_post($image_id);
        if ($image) {
            $alt_text = $image->post_title;
            
            // If title is empty, use filename
            if (empty($alt_text)) {
                $file = get_post_meta($image_id, '_wp_attached_file', true);
                $alt_text = pathinfo($file, PATHINFO_FILENAME);
                $alt_text = str_replace(array('-', '_'), ' ', $alt_text);
                $alt_text = ucwords($alt_text);
            }
        }
    }
    
    // Sanitize alt text
    $alt_text = sanitize_text_field($alt_text);
    
    // Update alt text
    return update_post_meta($image_id, '_wp_attachment_image_alt', $alt_text);
}

/**
 * Batch update alt text for all images without alt tags
 * 
 * WARNING: Use with caution! This will auto-generate alt text for all images.
 * Review the results and update manually if needed.
 * 
 * @return array Results array with success/failure counts
 */
function ea_batch_update_alt_text() {
    $images = ea_get_images_without_alt();
    $results = array(
        'success' => 0,
        'failed' => 0,
        'total' => count($images)
    );
    
    foreach ($images as $image) {
        $success = ea_auto_generate_alt_text($image['ID']);
        if ($success) {
            $results['success']++;
        } else {
            $results['failed']++;
        }
    }
    
    return $results;
}

/**
 * WP-CLI command for alt text inventory (if WP-CLI available)
 */
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('ea alt-text inventory', function($args, $assoc_args) {
        $images = ea_get_images_without_alt();
        $total = count($images);
        
        WP_CLI::line("Alt-Text Inventory Report");
        WP_CLI::line("Total images without alt text: {$total}");
        
        if ($total > 0) {
            $table = array();
            foreach ($images as $image) {
                $table[] = array(
                    'ID' => $image['ID'],
                    'Title' => $image['post_title'],
                    'File' => $image['_wp_attached_file'],
                    'URL' => $image['image_url']
                );
            }
            WP_CLI\Utils\format_items('table', $table, array('ID', 'Title', 'File', 'URL'));
        } else {
            WP_CLI::success('All images have alt text!');
        }
    });
    
    WP_CLI::add_command('ea alt-text auto-generate', function($args, $assoc_args) {
        $results = ea_batch_update_alt_text();
        WP_CLI::success("Updated {$results['success']} images. Failed: {$results['failed']}");
    });
}

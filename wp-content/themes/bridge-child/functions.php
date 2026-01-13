<?php

// enqueue the child theme stylesheet

function wp_schools_enqueue_scripts() {
	wp_register_style( 'childstyle', get_stylesheet_directory_uri() . '/style.css' );
	wp_enqueue_style( 'childstyle' );
}
add_action( 'wp_enqueue_scripts', 'wp_schools_enqueue_scripts', 11 );

// Load Schema JSON-LD implementation (Phase 2.3)
require_once get_stylesheet_directory() . '/schema-person-specialist.php';

// Load General Redirect Rules (Multi-Environment & Sitemap Cleanup)
require_once get_stylesheet_directory() . '/functions-redirects.php';


/*
@Recreate the default filters on the_content
-------------------------------------------------------------- */
add_filter( 'meta_content', 'wptexturize' );
add_filter( 'meta_content', 'convert_smilies' );
add_filter( 'meta_content', 'convert_chars' );
add_filter( 'meta_content', 'wpautop' );
add_filter( 'meta_content', 'shortcode_unautop' );
add_filter( 'meta_content', 'prepend_attachment' );

function my_meta_func( $atts ) {
	extract(
		shortcode_atts(
			array(
				'field' => 'feature-description-details',
			),
			$atts
		)
	);
	$text = get_post_meta( $post->ID, $field, true );
	return apply_filters( 'meta_content', $text );
}
add_shortcode( 'my-meta', 'my_meta_func' );

// Schema JSON-LD implementation moved to separate file: schema-person-specialist.php

/**
 * Alt-Text Inventory and Auto-Fix Functions
 */
function ea_get_images_without_alt() {
	global $wpdb;

	// Get all images in media library
	$images = $wpdb->get_results(
		"
        SELECT p.ID, p.post_title, p.post_name, pm.meta_value as alt_text
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
        WHERE p.post_type = 'attachment'
        AND p.post_mime_type LIKE 'image/%'
        ORDER BY p.post_date DESC
    "
	);

	$missing_alt = array();
	$has_alt     = array();

	foreach ( $images as $image ) {
		if ( empty( $image->alt_text ) ) {
			$missing_alt[] = array(
				'id'       => $image->ID,
				'title'    => $image->post_title,
				'filename' => $image->post_name,
				'url'      => wp_get_attachment_url( $image->ID ),
			);
		} else {
			$has_alt[] = array(
				'id'       => $image->ID,
				'title'    => $image->post_title,
				'alt_text' => $image->alt_text,
			);
		}
	}

	return array(
		'missing_alt'   => $missing_alt,
		'has_alt'       => $has_alt,
		'total_images'  => count( $images ),
		'missing_count' => count( $missing_alt ),
		'has_count'     => count( $has_alt ),
	);
}

function ea_auto_fix_alt_text() {
	$inventory = ea_get_images_without_alt();

	$fixed_count = 0;

	foreach ( $inventory['missing_alt'] as $image ) {
		// Generate alt text based on title/filename
		$alt_text = '';

		if ( ! empty( $image['title'] ) && $image['title'] !== 'attachment' ) {
			$alt_text = $image['title'];
		} else {
			// Extract meaningful text from filename
			$filename = pathinfo( $image['filename'], PATHINFO_FILENAME );
			$alt_text = ucwords( str_replace( array( '-', '_' ), ' ', $filename ) );
		}

		// Add context for didgeridoo website
		if ( stripos( $alt_text, 'didgeridoo' ) !== false || stripos( $alt_text, 'דיגרידו' ) !== false ) {
			$alt_text .= ' - כלי ריפוי ונשימה מעגלית';
		}

		// Update the alt text
		update_post_meta( $image['id'], '_wp_attachment_image_alt', $alt_text );
		++$fixed_count;
	}

	return array(
		'fixed_count'   => $fixed_count,
		'total_missing' => $inventory['missing_count'],
	);
}

/**
 * Admin notice for missing alt text
 */
function ea_alt_text_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$inventory = ea_get_images_without_alt();

	if ( $inventory['missing_count'] > 0 ) {
		echo '<div class="notice notice-warning is-dismissible">';
		echo '<p><strong>SEO Alert:</strong> ' . $inventory['missing_count'] . ' images are missing alt text. ';
		echo '<a href="' . admin_url( 'upload.php' ) . '">Fix in Media Library</a> or ';
		echo '<a href="' . add_query_arg( 'ea_auto_fix_alt', '1' ) . '">Auto-fix now</a></p>';
		echo '</div>';
	}
}
add_action( 'admin_notices', 'ea_alt_text_admin_notice' );

/**
 * Handle auto-fix request
 */
function ea_handle_auto_fix_alt() {
	if ( isset( $_GET['ea_auto_fix_alt'] ) && current_user_can( 'manage_options' ) ) {
		$result = ea_auto_fix_alt_text();

		wp_redirect(
			add_query_arg(
				array(
					'ea_fixed' => $result['fixed_count'],
					'ea_total' => $result['total_missing'],
				),
				admin_url( 'upload.php' )
			)
		);
		exit;
	}
}
add_action( 'admin_init', 'ea_handle_auto_fix_alt' );

/**
 * Success message after auto-fix
 */
function ea_alt_fix_success_message() {
	if ( isset( $_GET['ea_fixed'] ) ) {
		$fixed = intval( $_GET['ea_fixed'] );
		$total = intval( $_GET['ea_total'] );

		echo '<div class="notice notice-success is-dismissible">';
		echo '<p>✅ Successfully added alt text to ' . $fixed . ' out of ' . $total . ' images.</p>';
		echo '</div>';
	}
}
add_action( 'admin_notices', 'ea_alt_fix_success_message' );

/**
 * Add alt text validation to media upload
 */
function ea_enforce_alt_text_on_upload( $post_id ) {
	if ( wp_attachment_is_image( $post_id ) ) {
		$alt_text = get_post_meta( $post_id, '_wp_attachment_image_alt', true );

		if ( empty( $alt_text ) ) {
			// Auto-generate alt text for new uploads
			$post     = get_post( $post_id );
			$alt_text = ! empty( $post->post_title ) ? $post->post_title : 'Didgeridoo therapy image';

			// Add didgeridoo context
			if ( stripos( $alt_text, 'didgeridoo' ) === false && stripos( $alt_text, 'דיגרידו' ) === false ) {
				$alt_text .= ' - דיגרידו וריפוי בנשימה מעגלית';
			}

			update_post_meta( $post_id, '_wp_attachment_image_alt', $alt_text );
		}
	}
}
add_action( 'add_attachment', 'ea_enforce_alt_text_on_upload' );

/**
 * Enqueue Critical CSS inline in <head>
 * Phase 4 Step 1 - Critical CSS Implementation
 */
function ea_enqueue_critical_css() {
	$critical_css_path = get_stylesheet_directory() . '/critical.css';

	if ( file_exists( $critical_css_path ) ) {
		$critical_css = file_get_contents( $critical_css_path );
		echo '<style id="critical-css">' . wp_strip_all_tags( $critical_css ) . '</style>' . "\n";
	}
}
add_action( 'wp_head', 'ea_enqueue_critical_css', 1 );

/**
 * Defer non-critical CSS
 * Phase 4 Step 1 - Critical CSS Implementation
 */
function ea_defer_non_critical_css() {
	// Defer main stylesheet - remove the original enqueue and re-enqueue with defer
	wp_dequeue_style( 'childstyle' );
	wp_enqueue_style( 'childstyle', get_stylesheet_directory_uri() . '/style.css', array(), null, 'all' );
	add_filter( 'style_loader_tag', 'ea_defer_css_tag', 10, 2 );
}
add_action( 'wp_enqueue_scripts', 'ea_defer_non_critical_css', 11 );

function ea_defer_css_tag( $tag, $handle ) {
	if ( 'childstyle' === $handle ) {
		return str_replace( "rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $tag );
	}
	return $tag;
}

/**
 * Convert uploaded images to WebP format
 * Phase 4 Step 1 - WebP Implementation
 */
function ea_convert_to_webp( $metadata, $attachment_id ) {
	// Check if WebP is supported
	if ( ! function_exists( 'imagewebp' ) ) {
		return $metadata; // WebP not supported
	}

	$file = get_attached_file( $attachment_id );

	if ( ! $file || ! file_exists( $file ) ) {
		return $metadata;
	}

	$file_info = pathinfo( $file );

	// Only convert JPEG and PNG
	if ( ! in_array( strtolower( $file_info['extension'] ), array( 'jpg', 'jpeg', 'png' ), true ) ) {
		return $metadata;
	}

	$image     = null;
	$webp_file = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';

	// Skip if WebP already exists
	if ( file_exists( $webp_file ) ) {
		return $metadata;
	}

	// Load image based on type
	switch ( strtolower( $file_info['extension'] ) ) {
		case 'jpg':
		case 'jpeg':
			$image = @imagecreatefromjpeg( $file );
			break;
		case 'png':
			$image = @imagecreatefrompng( $file );
			if ( $image ) {
				// Preserve transparency
				imagealphablending( $image, false );
				imagesavealpha( $image, true );
			}
			break;
	}

	if ( $image ) {
		// Convert to WebP with quality 85
		$success = @imagewebp( $image, $webp_file, 85 );
		imagedestroy( $image );

		if ( $success && file_exists( $webp_file ) ) {
			// Update attachment metadata
			update_post_meta( $attachment_id, '_webp_file', $webp_file );
		}
	}

	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'ea_convert_to_webp', 10, 2 );

/**
 * Serve WebP images with fallback
 * Phase 4 Step 1 - WebP Implementation
 */
function ea_serve_webp_with_fallback( $html, $post_id ) {
	$webp_file = get_post_meta( $post_id, '_webp_file', true );

	if ( $webp_file && file_exists( $webp_file ) ) {
		$original_url = wp_get_attachment_url( $post_id );
		$webp_url     = str_replace( basename( $original_url ), basename( $webp_file ), $original_url );

		// Extract img tag attributes
		preg_match( '/<img[^>]+>/i', $html, $matches );
		if ( ! empty( $matches[0] ) ) {
			$img_tag = $matches[0];

			// Use <picture> tag for WebP with fallback
			$html = '<picture>
                <source srcset="' . esc_url( $webp_url ) . '" type="image/webp">
                ' . $img_tag . '
            </picture>';
		}
	}

	return $html;
}
add_filter( 'wp_get_attachment_image', 'ea_serve_webp_with_fallback', 10, 2 );

/**
 * Add lazy loading to images
 * Phase 4 Step 1 - Image Optimization
 */
function ea_add_lazy_loading( $attr, $attachment, $size ) {
	if ( ! is_admin() ) {
		$attr['loading']  = 'lazy';
		$attr['decoding'] = 'async';
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'ea_add_lazy_loading', 10, 3 );

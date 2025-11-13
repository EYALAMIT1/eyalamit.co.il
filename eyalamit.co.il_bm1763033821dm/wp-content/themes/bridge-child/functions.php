<?php

// enqueue the child theme stylesheet

Function wp_schools_enqueue_scripts() {
wp_register_style( 'childstyle', get_stylesheet_directory_uri() . '/style.css'  );
wp_enqueue_style( 'childstyle' );
}
add_action( 'wp_enqueue_scripts', 'wp_schools_enqueue_scripts', 11);


/* @Recreate the default filters on the_content
-------------------------------------------------------------- */
add_filter( 'meta_content', 'wptexturize'        );
add_filter( 'meta_content', 'convert_smilies'    );
add_filter( 'meta_content', 'convert_chars'      );
add_filter( 'meta_content', 'wpautop'            );
add_filter( 'meta_content', 'shortcode_unautop'  );
add_filter( 'meta_content', 'prepend_attachment' );
 
function my_meta_func( $atts ) {
	global $post;

	$args = shortcode_atts(
		array(
			'field' => 'feature-description-details',
		),
		$atts,
		'my-meta'
	);

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$text = get_post_meta( $post->ID, $args['field'], true );

	return apply_filters( 'meta_content', $text );
}
add_shortcode('my-meta', 'my_meta_func');
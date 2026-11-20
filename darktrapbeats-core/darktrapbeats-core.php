
<?php
/**
 * Plugin Name: Dark Trap Beats Core
 * Description: Custom content for the Dark Trap Beats site.
 * Version: 1.0
 * Author: Colten Carlson
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'darktrapbeats_register_content' );

function darktrapbeats_register_content() {

	// 1. Post type
	register_post_type( 'beat', array(
		'labels' => array(
			'name'          => 'Beats',
			'singular_name' => 'Beat',
			'add_new_item'  => 'Add New Beat',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'rewrite'      => array( 'slug' => 'beats' ),
		'menu_icon'    => 'dashicons-format-audio',
	) );

	// 2. Taxonomy
	register_taxonomy( 'beat_genre', 'beat', array(
		'labels' => array(
			'name'          => 'Beat Genres',
			'singular_name' => 'Beat Genre',
		),
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'beat-genre' ),
	) );

	// 3. Custom fields
	foreach ( array( 'price', 'bpm', 'musical_key' ) as $key ) {
		register_post_meta( 'beat', $key, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
		) );
	}
}

// ===== Week 5 additions =====

// 1. Shortcode
// Example: [latest_beats count="3" genre="dark-trap"]

add_shortcode( 'latest_beats', 'darktrapbeats_latest_beats' );

function darktrapbeats_latest_beats( $atts ) {

	$atts = shortcode_atts( array(
		'count' => 3,
		'genre' => '',
	), $atts, 'latest_beats' );

	$args = array(
		'post_type'   => 'beat',
		'numberposts' => absint( $atts['count'] ),
	);

	if ( $atts['genre'] ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'beat_genre',
				'field'    => 'slug',
				'terms'    => sanitize_title( $atts['genre'] ),
			),
		);
	}

	$items = get_posts( $args );

	if ( empty( $items ) ) {
		return '<p class="dtb-latest-empty">Check back soon for new beats.</p>';
	}

	$html = '<ul class="dtb-latest">';

	foreach ( $items as $item ) {

		$price = get_post_meta( $item->ID, 'price', true );

		$html .= '<li><a href="' . esc_url( get_permalink( $item ) ) . '">'
			. esc_html( get_the_title( $item ) ) . '</a>';

		if ( $price ) {
			$html .= ' <span class="dtb-price">' . esc_html( $price ) . '</span>';
		}

		$html .= '</li>';
	}

	return $html . '</ul>';
}

// 2. Content filter
// Add a call to action after single blog posts only.

add_filter( 'the_content', 'darktrapbeats_add_cta', 20 );

function darktrapbeats_add_cta( $content ) {

	if ( ! is_singular( 'post' ) || ! is_main_query() ) {
		return $content;
	}

	$cta = '<div class="dtb-cta"><p>Discover your next sound.</p>'
		. '<a href="' . esc_url( home_url( '/beats/' ) ) . '">Explore Our Beats</a></div>';

	return $content . $cta;
}

// 3. Enqueue stylesheet

add_action( 'wp_enqueue_scripts', 'darktrapbeats_enqueue_styles' );

function darktrapbeats_enqueue_styles() {

	wp_enqueue_style(
		'darktrapbeats-core',
		plugins_url( 'css/core.css', __FILE__ ),
		array(),
		'1.0'
	);
}

// 4. Block pattern

add_action( 'init', 'darktrapbeats_register_patterns' );

function darktrapbeats_register_patterns() {
    register_block_pattern(
        'darktrapbeats/explore-beats',
        array(
            'title'       => 'Explore Our Beats',
            'description' => 'A call to action for discovering beats.',
            'categories'  => array('call-to-action'),
            'content'     => '<!-- wp:group {"className":"dtb-cta"} -->
<div class="wp-block-group dtb-cta">
    <!-- wp:heading -->
    <h2>Find Your Next Sound</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>Explore our collection of dark trap beats and find the sound for your next track.</p>
    <!-- /wp:paragraph -->

    <!-- wp:buttons -->
    <div class="wp-block-buttons">
        <!-- wp:button {"url":"/beats/"} -->
        <div class="wp-block-button"><a class="wp-block-button__link" href="/beats/">Explore Our Beats</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
</div>
<!-- /wp:group -->'
        )
    );
}

function darktrapbeats_random_beat_button() {
    $beats = get_posts(array(
        'post_type'      => 'beat',
        'posts_per_page' => 1,
        'orderby'        => 'rand',
        'post_status'    => 'publish'
    ));

    if (empty($beats)) {
        return '<p>No beats available yet.</p>';
    }

    $url = get_permalink($beats[0]->ID);

    return '<a class="dtb-random-button" href="' . esc_url($url) . '">Discover a Random Beat</a>';
}

add_shortcode('random_beat', 'darktrapbeats_random_beat_button');

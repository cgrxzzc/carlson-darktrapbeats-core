<?php
/**
 * Plugin Name: Dark Trap Beats Core
 * Description: Custom content for the Dark Trap Beats site.
 * Version: 1.0
 * Author: Colten Carlson
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Stop if this file is opened directly.
}
 
add_action( 'init', 'darktrapbeats_register_content' );
 
function darktrapbeats_register_content() {
 
	// 1. Post type
	register_post_type( 'beat', array(
		'labels'       => array(
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
		'labels'       => array(
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
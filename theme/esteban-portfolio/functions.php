<?php
/**
 * Esteban Portfolio theme setup.
 *
 * @package esteban-portfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'post-thumbnails' );
	add_editor_style( 'assets/css/theme.css' );
} );

add_action( 'wp_enqueue_scripts', function () {
	$theme = wp_get_theme();
	wp_enqueue_style(
		'esteban-portfolio',
		get_theme_file_uri( 'assets/css/theme.css' ),
		array(),
		$theme->get( 'Version' )
	);
} );

add_action( 'init', function () {
	// Custom block: the animated <EP/> logo (no build step — see blocks/logo).
	register_block_type( __DIR__ . '/blocks/logo' );

	register_block_pattern_category( 'esteban-portfolio', array(
		'label' => __( 'Esteban Portfolio', 'esteban-portfolio' ),
	) );

	// Block style used by the "card" variation in theme.json.
	register_block_style( 'core/group', array(
		'name'  => 'card',
		'label' => __( 'Card', 'esteban-portfolio' ),
	) );
} );

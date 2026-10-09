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
} );

add_action( 'init', function () {
	register_block_pattern_category( 'esteban-portfolio', array(
		'label' => __( 'Esteban Portfolio', 'esteban-portfolio' ),
	) );

	// A small custom block style, to show the API in use.
	register_block_style( 'core/group', array(
		'name'  => 'card',
		'label' => __( 'Card', 'esteban-portfolio' ),
	) );
} );

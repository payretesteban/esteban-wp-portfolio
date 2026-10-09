<?php
/**
 * Plugin Name: Esteban Portfolio Core
 * Description: Content model for the portfolio: a "Project" post type, a "Tech" taxonomy and REST-exposed custom fields used by block bindings. Kept in a plugin (not the theme) so content survives a theme switch.
 * Version: 0.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Author: Esteban Payret
 * License: GPL-2.0-or-later
 * Text Domain: esteban-portfolio-core
 *
 * @package esteban-portfolio-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'esteban_portfolio_register_content_model' );

/**
 * Registers the Project post type, Tech taxonomy and project meta.
 */
function esteban_portfolio_register_content_model() {
	register_post_type( 'project', array(
		'labels'       => array(
			'name'          => __( 'Projects', 'esteban-portfolio-core' ),
			'singular_name' => __( 'Project', 'esteban-portfolio-core' ),
			'add_new_item'  => __( 'Add New Project', 'esteban-portfolio-core' ),
			'edit_item'     => __( 'Edit Project', 'esteban-portfolio-core' ),
		),
		'public'       => true,
		'has_archive'  => 'projects',
		'rewrite'      => array( 'slug' => 'projects' ),
		'menu_icon'    => 'dashicons-portfolio',
		'show_in_rest' => true,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'page-attributes' ),
		'template'     => array(
			array( 'core/paragraph', array( 'placeholder' => __( 'What was the problem, what did you do, what changed?', 'esteban-portfolio-core' ) ) ),
		),
	) );

	register_taxonomy( 'tech', 'project', array(
		'labels'            => array(
			'name'          => __( 'Tech', 'esteban-portfolio-core' ),
			'singular_name' => __( 'Tech', 'esteban-portfolio-core' ),
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'tech' ),
	) );

	// Meta shown on single-project.html through core/post-meta block bindings.
	$fields = array(
		'project_role' => 'string',
		'project_url'  => 'string',
		'repo_url'     => 'string',
	);
	foreach ( $fields as $key => $type ) {
		register_post_meta( 'project', $key, array(
			'type'              => $type,
			'single'            => true,
			'show_in_rest'      => true,
			'default'           => '',
			'sanitize_callback' => 'project_role' === $key ? 'sanitize_text_field' : 'esc_url_raw',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
}

register_activation_hook( __FILE__, static function () {
	esteban_portfolio_register_content_model();
	flush_rewrite_rules();
} );

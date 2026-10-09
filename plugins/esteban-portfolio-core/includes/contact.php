<?php
/**
 * Contact form backend: REST endpoint, validation, spam protection, storage and email.
 *
 * Submissions are saved as private "Messages" in wp-admin and emailed to the site admin.
 * No email address is ever printed in the page markup.
 *
 * @package esteban-portfolio-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed values for the select fields (shared with the form markup).
 *
 * @return array<string, array<string, string>>
 */
function esteban_contact_choices() {
	return array(
		'projectType' => array(
			'leadership'   => __( 'Technical leadership', 'esteban-portfolio-core' ),
			'architecture' => __( 'Architecture review', 'esteban-portfolio-core' ),
			'coaching'     => __( 'Team coaching & mentoring', 'esteban-portfolio-core' ),
			'development'  => __( 'Hands-on development', 'esteban-portfolio-core' ),
			'other'        => __( 'Something else', 'esteban-portfolio-core' ),
		),
		'budget'      => array(
			'lt5k'    => __( 'Under $5k', 'esteban-portfolio-core' ),
			'5-15k'   => __( '$5k – $15k', 'esteban-portfolio-core' ),
			'15-50k'  => __( '$15k – $50k', 'esteban-portfolio-core' ),
			'50k+'    => __( '$50k+', 'esteban-portfolio-core' ),
			'unsure'  => __( 'Not sure yet', 'esteban-portfolio-core' ),
		),
		'timeline'    => array(
			'asap'     => __( 'As soon as possible', 'esteban-portfolio-core' ),
			'month'    => __( 'Within a month', 'esteban-portfolio-core' ),
			'1-3m'     => __( '1 – 3 months', 'esteban-portfolio-core' ),
			'flexible' => __( 'Flexible', 'esteban-portfolio-core' ),
		),
		'workSetup'   => array(
			'remote' => __( 'Remote', 'esteban-portfolio-core' ),
			'hybrid' => __( 'Hybrid', 'esteban-portfolio-core' ),
			'onsite' => __( 'On-site', 'esteban-portfolio-core' ),
		),
	);
}

/**
 * Signed timestamp for the "too fast to be human" check.
 *
 * @param int|null $time Timestamp to sign (defaults to now).
 * @return string "time.signature"
 */
function esteban_contact_token( $time = null ) {
	$time = $time ? (int) $time : time();
	return $time . '.' . hash_hmac( 'sha256', (string) $time, wp_salt( 'nonce' ) );
}

add_action( 'init', function () {
	register_post_type( 'ep_message', array(
		'labels'          => array(
			'name'          => __( 'Messages', 'esteban-portfolio-core' ),
			'singular_name' => __( 'Message', 'esteban-portfolio-core' ),
			'edit_item'     => __( 'Message', 'esteban-portfolio-core' ),
			'not_found'     => __( 'No messages yet.', 'esteban-portfolio-core' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 26,
		'supports'        => array( 'title', 'editor', 'custom-fields' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

add_action( 'rest_api_init', function () {
	register_rest_route( 'esteban/v1', '/contact', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'esteban_contact_handle',
		'permission_callback' => '__return_true', // Public form; protected by honeypot, timing token and rate limit.
	) );
} );

/**
 * Handles a contact form submission.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function esteban_contact_handle( WP_REST_Request $request ) {
	$p = $request->get_json_params();
	if ( ! is_array( $p ) ) {
		$p = $request->get_body_params();
	}

	// 1. Honeypot: real people never see or fill the "website" field. Pretend success for bots.
	if ( ! empty( $p['website'] ) ) {
		return rest_ensure_response( array( 'ok' => true ) );
	}

	// 2. Timing token: must be genuine and the form must have been open for at least 3 seconds.
	$token = isset( $p['ts'] ) ? (string) $p['ts'] : '';
	$parts = explode( '.', $token, 2 );
	$time  = (int) $parts[0];
	if ( 2 !== count( $parts ) || ! hash_equals( esteban_contact_token( $time ), $token ) || time() - $time < 3 || time() - $time > DAY_IN_SECONDS ) {
		return new WP_Error( 'ep_contact_token', __( 'Please reload the page and try again.', 'esteban-portfolio-core' ), array( 'status' => 400 ) );
	}

	// 3. Rate limit: 5 messages per hour per IP (hashed, never stored raw).
	$ip_key = 'ep_contact_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 5 ) {
		return new WP_Error( 'ep_contact_rate', __( 'Too many messages — please try again later.', 'esteban-portfolio-core' ), array( 'status' => 429 ) );
	}

	// 4. Validate and sanitize.
	$choices = esteban_contact_choices();
	$inquiry = ( isset( $p['inquiry'] ) && 'fulltime' === $p['inquiry'] ) ? 'fulltime' : 'consulting';
	$data    = array(
		'name'    => sanitize_text_field( $p['name'] ?? '' ),
		'email'   => sanitize_email( $p['email'] ?? '' ),
		'company' => sanitize_text_field( $p['company'] ?? '' ),
		'message' => sanitize_textarea_field( $p['message'] ?? '' ),
	);
	$extra   = 'fulltime' === $inquiry ? array( 'role', 'location', 'workSetup', 'jobUrl' ) : array( 'projectType', 'budget', 'timeline' );
	foreach ( $extra as $key ) {
		$value = isset( $p[ $key ] ) ? (string) $p[ $key ] : '';
		if ( isset( $choices[ $key ] ) ) {
			$data[ $key ] = isset( $choices[ $key ][ $value ] ) ? $choices[ $key ][ $value ] : '';
		} elseif ( 'jobUrl' === $key ) {
			$data[ $key ] = esc_url_raw( $value, array( 'http', 'https' ) );
		} else {
			$data[ $key ] = sanitize_text_field( $value );
		}
	}

	$errors = array();
	if ( '' === $data['name'] || mb_strlen( $data['name'] ) > 120 ) {
		$errors['name'] = __( 'Please enter your name.', 'esteban-portfolio-core' );
	}
	if ( ! is_email( $data['email'] ) ) {
		$errors['email'] = __( 'Please enter a valid email address.', 'esteban-portfolio-core' );
	}
	if ( mb_strlen( $data['message'] ) < 10 || mb_strlen( $data['message'] ) > 5000 ) {
		$errors['message'] = __( 'Please write a message (at least 10 characters).', 'esteban-portfolio-core' );
	}
	if ( $errors ) {
		return new WP_Error( 'ep_contact_invalid', __( 'Please check the highlighted fields.', 'esteban-portfolio-core' ), array( 'status' => 422, 'fields' => $errors ) );
	}

	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	// 5. Store as a private Message and email the admin.
	$label = 'fulltime' === $inquiry ? __( 'Full-time opportunity', 'esteban-portfolio-core' ) : __( 'Consulting & freelance', 'esteban-portfolio-core' );
	$lines = array( sprintf( '%s: %s', __( 'Type', 'esteban-portfolio-core' ), $label ) );
	foreach ( $data as $key => $value ) {
		if ( '' !== $value && 'message' !== $key ) {
			$lines[] = sprintf( '%s: %s', ucfirst( preg_replace( '/([A-Z])/', ' $1', $key ) ), $value );
		}
	}
	$body = implode( "\n", $lines ) . "\n\n" . $data['message'];

	$post_id = wp_insert_post( array(
		'post_type'    => 'ep_message',
		'post_status'  => 'private',
		'post_title'   => sprintf( '%s — %s', $data['name'], $label ),
		'post_content' => $body,
		'meta_input'   => array_merge( $data, array( 'inquiry' => $inquiry ) ),
	), true );

	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'ep_contact_store', __( 'Something went wrong. Please try again.', 'esteban-portfolio-core' ), array( 'status' => 500 ) );
	}

	$to = apply_filters( 'esteban_contact_recipient', get_option( 'admin_email' ) );
	wp_mail(
		$to,
		sprintf( '[%s] %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), get_the_title( $post_id ) ),
		$body,
		array( sprintf( 'Reply-To: %s <%s>', $data['name'], $data['email'] ) )
	);

	/**
	 * Fires after a contact message is stored — hook here to forward it (Slack, CRM, etc.).
	 *
	 * @param int   $post_id Message post ID.
	 * @param array $data    Sanitized fields.
	 */
	do_action( 'esteban_contact_received', $post_id, $data );

	return rest_ensure_response( array( 'ok' => true ) );
}

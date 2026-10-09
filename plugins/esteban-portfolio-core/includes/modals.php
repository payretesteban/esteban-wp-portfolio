<?php
/**
 * Front-end modals: the contact form dialog and the Cal.com booking embed.
 *
 * Triggers (no custom markup needed in content):
 * - Contact: any link to "#contact-form", or a Button block with the class "ep-contact-trigger".
 * - Booking: a Button block with the class "ep-cal-trigger" (its href stays as a no-JS fallback).
 *
 * @package esteban-portfolio-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {
	$base = plugin_dir_url( ESTEBAN_PORTFOLIO_CORE_FILE ) . 'assets/';
	$ver  = ESTEBAN_PORTFOLIO_CORE_VERSION;

	wp_enqueue_style( 'esteban-modals', $base . 'modals.css', array(), $ver );
	wp_enqueue_script( 'esteban-modals', $base . 'modals.js', array(), $ver, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script( 'esteban-modals', 'estebanModals', array(
		'endpoint' => esc_url_raw( rest_url( 'esteban/v1/contact' ) ),
		'calLink'  => apply_filters( 'esteban_cal_link', 'estebanpayret/30min' ),
		'i18n'     => array(
			'sending' => __( 'Sending…', 'esteban-portfolio-core' ),
			'success' => __( 'Thanks! Your message is on its way — I’ll get back to you within 2 business days.', 'esteban-portfolio-core' ),
			'error'   => __( 'Something went wrong. Please try again.', 'esteban-portfolio-core' ),
		),
	) );
} );

/**
 * Prints a <select> with an empty "Choose…" option.
 *
 * @param string $name  Field name (key in esteban_contact_choices()).
 * @param string $label Visible label.
 */
function esteban_modal_select( $name, $label ) {
	$choices = esteban_contact_choices();
	$id      = 'ep-f-' . $name;
	?>
	<div class="ep-field">
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?> <span class="ep-optional"><?php esc_html_e( 'Optional', 'esteban-portfolio-core' ); ?></span></label>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
			<option value=""><?php esc_html_e( 'Choose…', 'esteban-portfolio-core' ); ?></option>
			<?php foreach ( $choices[ $name ] as $value => $text ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $text ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}

/**
 * Prints a text-like input.
 *
 * @param string $name     Field name.
 * @param string $label    Visible label.
 * @param string $type     Input type.
 * @param bool   $required Whether required.
 * @param string $auto     Autocomplete token.
 */
function esteban_modal_input( $name, $label, $type = 'text', $required = false, $auto = 'off' ) {
	$id = 'ep-f-' . $name;
	?>
	<div class="ep-field">
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?><?php if ( ! $required ) : ?> <span class="ep-optional"><?php esc_html_e( 'Optional', 'esteban-portfolio-core' ); ?></span><?php endif; ?></label>
		<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="<?php echo esc_attr( $type ); ?>" autocomplete="<?php echo esc_attr( $auto ); ?>"<?php echo $required ? ' required' : ''; ?>>
		<p class="ep-field__error" id="<?php echo esc_attr( $id ); ?>-error" hidden></p>
	</div>
	<?php
}

add_action( 'wp_footer', function () {
	?>
	<dialog id="ep-contact" class="ep-modal" aria-labelledby="ep-contact-title" aria-describedby="ep-contact-lead">
		<div class="ep-modal__panel">
			<button type="button" class="ep-modal__close" data-ep-close aria-label="<?php esc_attr_e( 'Close', 'esteban-portfolio-core' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
			</button>

			<h2 id="ep-contact-title" class="ep-modal__title"><?php esc_html_e( 'Let’s work together', 'esteban-portfolio-core' ); ?></h2>
			<p id="ep-contact-lead" class="ep-modal__lead"><?php esc_html_e( 'Tell me a bit about what you have in mind and I’ll get back to you within 2 business days.', 'esteban-portfolio-core' ); ?></p>

			<div class="ep-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Type of inquiry', 'esteban-portfolio-core' ); ?>">
				<button type="button" role="tab" id="ep-tab-consulting" aria-selected="true" aria-controls="ep-panel-consulting" data-inquiry="consulting"><?php esc_html_e( 'Consulting & Freelance', 'esteban-portfolio-core' ); ?></button>
				<button type="button" role="tab" id="ep-tab-fulltime" aria-selected="false" aria-controls="ep-panel-fulltime" data-inquiry="fulltime" tabindex="-1"><?php esc_html_e( 'Full-Time Opportunities', 'esteban-portfolio-core' ); ?></button>
			</div>

			<form class="ep-form" novalidate>
				<input type="hidden" name="inquiry" value="consulting">
				<input type="hidden" name="ts" value="<?php echo esc_attr( esteban_contact_token() ); ?>">

				<?php
				esteban_modal_input( 'name', __( 'Name', 'esteban-portfolio-core' ), 'text', true, 'name' );
				esteban_modal_input( 'email', __( 'Email', 'esteban-portfolio-core' ), 'email', true, 'email' );
				esteban_modal_input( 'company', __( 'Company', 'esteban-portfolio-core' ), 'text', false, 'organization' );
				?>

				<fieldset id="ep-panel-consulting" class="ep-panel" role="tabpanel" aria-labelledby="ep-tab-consulting">
					<?php
					esteban_modal_select( 'projectType', __( 'Project type', 'esteban-portfolio-core' ) );
					esteban_modal_select( 'budget', __( 'Budget', 'esteban-portfolio-core' ) );
					esteban_modal_select( 'timeline', __( 'Timeline', 'esteban-portfolio-core' ) );
					?>
				</fieldset>

				<fieldset id="ep-panel-fulltime" class="ep-panel" role="tabpanel" aria-labelledby="ep-tab-fulltime" hidden disabled>
					<?php
					esteban_modal_input( 'role', __( 'Role', 'esteban-portfolio-core' ) );
					esteban_modal_input( 'location', __( 'Location', 'esteban-portfolio-core' ) );
					esteban_modal_select( 'workSetup', __( 'Work setup', 'esteban-portfolio-core' ) );
					esteban_modal_input( 'jobUrl', __( 'Job posting link', 'esteban-portfolio-core' ), 'url' );
					?>
				</fieldset>

				<div class="ep-field">
					<label for="ep-f-message"><?php esc_html_e( 'Message', 'esteban-portfolio-core' ); ?></label>
					<textarea id="ep-f-message" name="message" rows="5" required minlength="10" maxlength="5000" placeholder="<?php esc_attr_e( 'What are you working on, and where could I help?', 'esteban-portfolio-core' ); ?>"></textarea>
					<p class="ep-field__error" id="ep-f-message-error" hidden></p>
				</div>

				<!-- Honeypot: hidden from people, irresistible to bots. -->
				<div class="ep-hp" aria-hidden="true">
					<label for="ep-f-website">Website</label>
					<input id="ep-f-website" name="website" type="text" tabindex="-1" autocomplete="off">
				</div>

				<p class="ep-form__status" role="status" aria-live="polite"></p>

				<div class="ep-form__actions">
					<button type="submit" class="wp-element-button ep-form__submit"><?php esc_html_e( 'Send message', 'esteban-portfolio-core' ); ?></button>
					<button type="button" class="ep-link-button ep-cal-inline" data-ep-book><?php esc_html_e( 'Prefer to talk? Book a free 30-min call', 'esteban-portfolio-core' ); ?></button>
				</div>
			</form>
		</div>
	</dialog>
	<?php
} );

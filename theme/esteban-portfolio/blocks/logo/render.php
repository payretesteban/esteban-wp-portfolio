<?php
/**
 * Server render for esteban/logo.
 *
 * @var array $attributes Block attributes.
 * @package esteban-portfolio
 */

$ep_text  = isset( $attributes['text'] ) ? $attributes['text'] : 'EP';
$ep_caret = ! isset( $attributes['showCaret'] ) || $attributes['showCaret'];
$ep_name  = get_bloginfo( 'name' );

$ep_wrapper = get_block_wrapper_attributes( array(
	'class'      => 'ep-logo',
	'href'       => esc_url( home_url( '/' ) ),
	'aria-label' => esc_attr( sprintf( /* translators: %s: site name */ __( '%s — home', 'esteban-portfolio' ), $ep_name ) ),
	'title'      => esc_attr( $ep_name ),
) );
?>
<a <?php echo $ep_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="ep-logo__chip" aria-hidden="true"><span class="ep-logo__bracket ep-logo__bracket--open">&lt;</span><span><?php echo esc_html( $ep_text ); ?></span><span class="ep-logo__bracket ep-logo__bracket--close">/&gt;</span><?php if ( $ep_caret ) : ?><span class="ep-logo__caret"></span><?php endif; ?></span>
</a>

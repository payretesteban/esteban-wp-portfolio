<?php
/**
 * Title: Experience timeline
 * Slug: esteban-portfolio/experience
 * Categories: esteban-portfolio
 * Inserter: true
 */
$esteban_jobs = array(
	array( 'Independent', 'Engineering Leadership & Consulting', 'Now', 'Helping companies build better products, improve engineering efficiency and put AI to practical use. Open to leadership roles, consulting and freelance work.' ),
	array( 'HubSpot', 'Tech Lead & People Manager', '4 years', 'Led and mentored engineers where technology, product and business meet.' ),
	array( 'Web products', 'Software Engineer → Tech Lead', '15+ years', 'Building and shipping web products end to end, then scaling the teams that build them.' ),
);
?>
<!-- wp:group {"anchor":"experience","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"1120px"}} -->
<div id="experience" class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:heading {"fontSize":"x-large"} -->
<h2 class="wp-block-heading has-x-large-font-size">Experience</h2>
<!-- /wp:heading -->
<?php foreach ( $esteban_jobs as $job ) : ?>
<!-- wp:group {"className":"is-style-card","layout":{"type":"constrained","justifyContent":"left","contentSize":"100%"}} -->
<div class="wp-block-group is-style-card">
<!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap"}} -->
<div class="wp-block-group">
<!-- wp:heading {"level":3,"fontSize":"large"} -->
<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $job[1] ); ?> · <?php echo esc_html( $job[0] ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"textColor":"muted","fontFamily":"mono","fontSize":"small"} -->
<p class="has-muted-color has-text-color has-mono-font-family has-small-font-size"><?php echo esc_html( $job[2] ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:paragraph {"textColor":"muted"} -->
<p class="has-muted-color has-text-color"><?php echo esc_html( $job[3] ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->

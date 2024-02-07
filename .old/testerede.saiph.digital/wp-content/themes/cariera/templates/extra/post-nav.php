<?php
/**
 * Post navigation template
 *
 * This template can be overridden by copying it to cariera-child/templates/extra/post-nav.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<nav class="col-md-12 navigation post-navigation mt40" role="navigation">
	<h4 class="post-navigation-title"><?php esc_html_e( 'Post navigation', 'cariera' ); ?></h4>

	<div class="nav-links">
		<div class="nav-next text-left"><?php next_post_link( esc_html__( '%link', 'cariera' ) ); ?></div>
		<div class="nav-previous text-right"><?php previous_post_link( esc_html__( '%link', 'cariera' ) ); ?></div>
	</div>
</nav>

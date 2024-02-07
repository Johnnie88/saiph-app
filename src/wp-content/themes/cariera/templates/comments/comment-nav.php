<?php
/**
 * Comments navigation
 *
 * This template can be overridden by copying it to cariera-child/templates/comments/comment-nav.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<nav class="navigation comment-navigation ptb40" role="navigation">
	<h4 class="comment-navigation-title"><?php esc_html_e( 'Comment navigation', 'cariera' ); ?></h4>

	<div class="nav-links clearfix">
		<div class="nav-previous text-right"><?php previous_comments_link( esc_html__( 'Older Comments', 'cariera' ) ); ?></div>
		<div class="nav-next text-left"><?php next_comments_link( esc_html__( 'Newer Comments', 'cariera' ) ); ?></div>
	</div>
</nav>

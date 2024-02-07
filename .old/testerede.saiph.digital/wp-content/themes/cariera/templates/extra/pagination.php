<?php
/**
 * Pagination template
 *
 * This template can be overridden by copying it to cariera-child/templates/extra/pagination.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="col-md-12 pagination-container">
	<nav class="pagination-next-prev" role="navigation">
		<h2 class="screen-reader-text"><?php esc_html_e( 'Posts navigation', 'cariera' ); ?></h2>
		<div class="nav-links">
			<ul class="inline-list nopadding">
				<?php if ( get_next_posts_link() ) { ?>
					<li class="previous text-right"><?php next_posts_link( esc_html__( 'Older posts', 'cariera' ) ); ?></li>
				<?php } ?>

				<?php if ( get_previous_posts_link() ) { ?>
					<li class="next text-left"><?php previous_posts_link( esc_html__( 'Newer posts', 'cariera' ) ); ?></li>
				<?php } ?>
			</ul>
		</div>
	</nav>
</div>

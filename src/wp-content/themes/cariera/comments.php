<?php

if ( post_password_required() ) {
	return;
} ?>

<section id="comments" class="col-md-12 comments">
	<?php if ( have_comments() ) { ?>
		<h4><?php comments_number( 'no comments', '1 comment', '% comments' ); ?></h4>

		<?php \Cariera\get_comment_navigation(); ?>

		<ul class="comment-list">
			<?php
			wp_list_comments(
				[
					'style'      => 'ul',
					'short_ping' => true,
					'callback'   => '\Cariera\comments',
				]
			);
			?>
		</ul>

		<?php
		\Cariera\get_comment_navigation();
	}

	// Message if comments are closed.
	if ( ! comments_open() && get_comments_number() ) {
		?>
		<h6 class="no-comments"><?php esc_html_e( 'Comments are closed.', 'cariera' ); ?></h6>
		<?php
	}

	comment_form();
	?>
</section>

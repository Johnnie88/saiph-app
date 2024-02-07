<?php
/**
 * Single Post - Meta Data
 *
 * This template can be overridden by copying it to cariera-child/templates/content/single-meta.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.6.5
 * @version     1.6.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$metas = cariera_get_option( 'cariera_meta_single' );
?>

<div class="meta-tags">
	<?php if ( in_array( 'author', $metas, true ) ) { ?>
		<span class="author">
			<i class="far fa-keyboard"></i><?php esc_html_e( 'By', 'cariera' ); ?> <a class="author-link" rel="author" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php the_author_meta( 'display_name' ); ?></a>
		</span>
	<?php } ?>

	<?php if ( in_array( 'date', $metas, true ) ) { ?>
		<span class="published"><i class="far fa-clock"></i><?php the_time( get_option( 'date_format', 'd M, Y' ) ); ?></span>
	<?php } ?>

	<?php if ( in_array( 'cat', $metas, true ) && has_category() ) { ?>
		<span class="category"><i class="far fa-folder-open"></i><?php the_category( ', ' ); ?></span>
	<?php } ?>

	<?php if ( in_array( 'com', $metas, true ) ) { ?>
		<span class="comments"><i class="far fa-comment"></i><?php comments_popup_link( esc_html__( '0 comments', 'cariera' ), esc_html__( '1 comment', 'cariera' ), esc_html__( '% comments', 'cariera' ), 'comments-link', esc_html__( 'Comments are off', 'cariera' ) ); ?></span>
	<?php } ?>
</div>

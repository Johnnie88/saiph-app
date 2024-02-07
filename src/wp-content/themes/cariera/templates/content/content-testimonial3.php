<?php
/**
 * Testimonial Style 3
 *
 * This template can be overridden by copying it to cariera-child/templates/content/content-testimonial3.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$byline = get_post_meta( $post->ID, 'cariera_testimonial_byline', true );
?>

<div class="testimonial">
	<i class="fas fa-quote-left"></i>

	<blockquote><?php the_content( '' ); ?></blockquote>

	<div class="customer">
		<?php if ( has_post_thumbnail() ) { ?>
			<div class="avatar">
				<?php the_post_thumbnail( 'testimonial' ); ?>
			</div>
		<?php } ?>

		<div class="details">
			<h4 class="title"><?php echo esc_html( get_the_title() ); ?></h4>
			<cite title="<?php echo esc_html( $byline ); ?>"><?php echo esc_html( $byline ); ?></cite>
		</div>
	</div>
</div>

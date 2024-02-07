<?php
/**
 * Testimonial Style 2
 *
 * This template can be overridden by copying it to cariera-child/templates/content/content-testimonial2.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.2.0
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$byline = get_post_meta( $post->ID, 'cariera_testimonial_byline', true );
$url    = get_post_meta( $post->ID, 'cariera_testimonial_url', true ); ?>

<div class="testimonial">
	<div class="customer">
		<?php
		if ( has_post_thumbnail() ) {
			?>
			<?php if ( ! empty( $url ) ) { ?> 
				<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="circle-img">
					<?php the_post_thumbnail( 'testimonial' ); ?>
				</a>
			<?php } else { ?>
				<span class="circle-img">
					<?php the_post_thumbnail( 'testimonial' ); ?>
				</span>
				<?php
			}
		}
		?>

		<h4 class="title"><?php echo get_the_title(); ?></h4>
		<?php if ( ! empty( $url ) ) { ?>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank"><cite title="<?php echo esc_html( $byline ); ?>"><?php echo esc_html( $byline ); ?></cite></a>
		<?php } else { ?>
			<cite title="<?php echo esc_html( $byline ); ?>"><?php echo esc_html( $byline ); ?></cite>
		<?php } ?>
	</div>

	<div class="review">
		<blockquote><?php the_content( '' ); ?></blockquote>
	</div>    
</div>

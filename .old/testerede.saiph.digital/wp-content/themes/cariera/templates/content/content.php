<?php
/**
 * Standard Post
 *
 * This template can be overridden by copying it to cariera-child/templates/content/content-content.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$class[] = 'blog-post';

if ( is_sticky( $post->ID ) ) {
	$class[] = 'sticky-post';
}

wp_enqueue_style( 'cariera-blog-feed' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $class ); ?>>    
	<?php
	\Cariera\post_thumbnail(
		[
			'size'  => 'full',
			'class' => 'post-image',
		]
	);
	?>

	<div class="blog-desc">
		<h3 class="blog-post-title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
			</a>
		</h3>

		<?php get_template_part( 'templates/content/content-meta' ); ?>

		<div class="blog-post-exerpt">
			<?php the_excerpt(); ?>
		</div>

		<a href="<?php the_permalink(); ?>" class="btn btn-main btn-effect mt20">
			<?php esc_html_e( 'Read More', 'cariera' ); ?>
		</a>
	</div>
</article>

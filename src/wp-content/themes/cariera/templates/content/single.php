<?php
/**
 * Single Post - Content
 *
 * This template can be overridden by copying it to cariera-child/templates/content/single.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.0.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blog_layout = cariera_get_option( 'cariera_blog_layout' );

if ( 'fullwidth' === $blog_layout ) {
	$layout = 'col-lg-12';
} else {
	$layout = 'col-lg-8';
}

wp_enqueue_style( 'cariera-single-blog' );
?>

<?php if ( cariera_get_option( 'cariera_blog_page_header', 'true' ) ) { ?>
	<section class="page-header">
		<div class="container">
			<div class="row">
				<div class="col-md-12 text-center">
					<h1 class="title"><?php echo \Cariera\get_the_title(); ?></h1>
					<?php echo \Cariera\breadcrumbs(); ?>
				</div>
			</div>
		</div>
	</section>
<?php } ?>

<main class="ptb80">
	<div class="container">
		<div class="row">
			<div class="<?php echo esc_attr( $layout ); ?>">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'cariera-single-post' ); ?>>

					<?php \Cariera\single_post_thumbnail(); ?>

					<!-- Blog Post Content -->
					<div class="blog-desc">
						<?php
						$page_header = cariera_get_option( 'cariera_blog_page_header' );
						if ( false == $page_header ) {
							?>
							<h3 class="blog-title"><?php the_title(); ?></h3>
							<?php
						}

						// Post Meta Info.
						get_template_part( 'templates/content/single-meta' );

						the_content();

						wp_link_pages();

						// Get sharing options.
						if ( cariera_get_option( 'cariera_post_share' ) && function_exists( 'cariera_share_media' ) ) {
							echo cariera_share_media();
						}
						?>
					</div>
				</article>

				<?php
				// Show Post Nav only if there are more posts than 1.
				if ( true === cariera_get_option( 'cariera_blog_post_nav' ) ) :
					\Cariera\get_post_navigation();
				endif;

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>

			<?php
			if ( 'fullwidth' !== $blog_layout ) {
				get_sidebar();
			}
			?>
		</div>
	</div>
</main>

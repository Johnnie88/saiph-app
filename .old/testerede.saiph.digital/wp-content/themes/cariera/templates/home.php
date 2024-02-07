<?php
/**
 *
 * @package Cariera
 *
 * @since    1.0.0
 * @version  1.5.3
 *
 * ========================
 * Template Name: Home Page - Search Banner
 * ========================
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$intro_text = cariera_get_option( 'home_page_text' );
$count_jobs = wp_count_posts( 'job_listing', 'readable' );
?>

<!-- ===== Start of Main Search Section ===== -->
<section class="home-search overlay-black">
	<div class="container justify-content-center align-self-center">
		<div class="row">
			<div class="col-md-12">
				<h2 class="title">
					<?php echo esc_html( $intro_text ); ?>
				</h2>

				<?php
				echo do_shortcode( '[cariera_job_search_form location="yes"]' );

				// Job Counter Extra Info.
				if ( cariera_get_option( 'home_job_counter' ) ) {
					?>
					<div class="extra-info">
						<?php if ( \Cariera\wp_job_manager_is_activated() ) { ?>
							<span><?php printf( esc_html__( 'We have %s job offers for you!', 'cariera' ), '<strong>' . esc_html( $count_jobs->publish ) . '</strong>' ); ?></span>
							<?php
						} else {
							echo '<small>' . esc_html__( 'There is no Job count because WP Job Manager Plugin is not installed.', 'cariera' ) . '</small>';
						}
						?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</section>
<!-- ===== End of Main Search Section ===== -->

<?php
while ( have_posts() ) :
	the_post();
	?>
	<main <?php post_class(); ?>>
		<div class="container">
			<?php the_content(); ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();

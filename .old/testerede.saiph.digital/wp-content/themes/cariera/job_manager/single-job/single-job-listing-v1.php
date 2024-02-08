<?php
/**
 * Custom: Single Job Page - Layout Version 1
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/single-job-listing-v1.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<main id="post-<?php the_ID(); ?>" class="single-job-listing-page single-job-v1">
	<?php do_action( 'cariera_single_job_listing_before' ); ?>
	<?php get_job_manager_template_part( 'single-job/page-header' ); ?>

	<section class="single-job-content">
		<div class="container">
			<div class="row">
				<?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) { ?>
					<div class="col-md-12">
						<div class="job-manager-message error"><?php esc_html_e( 'This listing has expired.', 'cariera' ); ?></div>
					</div>
				<?php } else { ?>
					<div class="col-md-8 col-xs-12">
						<div class="single-job-listing">
							<?php
								/**
								 * single_job_listing_start hook
								 *
								 * @hooked job_listing_meta_display - 20
								 * @hooked job_listing_company_display - 30
								 */
								do_action( 'single_job_listing_start' );
							?>

							<div class="job-description">
								<?php wpjm_the_job_description(); ?>
							</div>

							<?php
								/**
								 * single_job_listing_end hook
								 */
								do_action( 'single_job_listing_end' );
							?>
						</div>
					</div>

					<div class="col-md-4 col-xs-12 job-sidebar">
						<?php do_action( 'cariera_single_job_listing_sidebar' ); ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</section>

	<?php do_action( 'cariera_single_job_listing_after' ); ?>
</main>

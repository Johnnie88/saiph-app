<?php
/**
 * Custom: Single Company - Layout Version 3
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/single-company-v3.php.
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

$image = get_post_meta( $post->ID, '_company_header_image', true );
?>

<main id="post-<?php the_ID(); ?>" class="single-company-page single-company-v3">
	<?php do_action( 'cariera_single_company_before' ); ?>

	<section class="page-header company-header" <?php echo ! empty( $image ) ? 'style="background-image: url(' . esc_attr( $image ) . ')"' : ''; ?>>
		<div class="container">
			<div class="row">
				<?php get_job_manager_template_part( 'single-company/single', 'company-details', 'wp-job-manager-companies' ); ?>
			</div>
		</div>
	</section>

	<section class="single-company-content">
		<div class="container">
			<div class="company-main row justify-content-center">
				<div class="col-md-9 col-xs-12">
					<div class="company-content-wrapper">
						<?php do_action( 'cariera_single_company_listing_start' ); ?>
						<?php do_action( 'cariera_single_company_listing' ); ?>
						<?php do_action( 'cariera_single_company_listing_end' ); ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php do_action( 'cariera_single_company_after' ); ?>
</main>

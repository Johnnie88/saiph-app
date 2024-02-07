<?php
/**
 * Cariera Dashboard - Active Packages template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/dashboard/active-packages.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Job_Manager' ) || ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Paid_Listings' ) ) {
	return;
}

$job_packages    = wc_paid_listings_get_user_packages( get_current_user_id(), 'job_listing' );
$resume_packages = wc_paid_listings_get_user_packages( get_current_user_id(), 'resume' );
$packages        = count( $job_packages ) + count( $resume_packages ); ?>

<div class="col-lg-6 col-md-12 dashboard-content-packages">
	<div class="dashboard-card-box">
		<div class="dashboard-card-title">
			<h4 class="title"><?php esc_html_e( 'Active Packages', 'cariera' ); ?></h4>
			<span class="item-count"><?php echo esc_html( $packages ); ?></span>
		</div>

		<div class="dashboard-card-box-inner">
			<ul class="listing-packages">
				<?php
				if ( $packages > 0 ) {

					// Showing all the Job Packages.
					foreach ( $job_packages as $job_package ) {
						$job_package = wc_paid_listings_get_package( $job_package );
						?>

						<li class="package">
							<i class="list-icon icon-star"></i>
							<h6 class="package-title"><?php echo esc_html( $job_package->get_title() ); ?></h6>

							<p><?php printf( esc_html__( 'You have %s job listings left that you can post.', 'cariera' ), $job_package->get_limit() ? absint( $job_package->get_limit() - $job_package->get_count() ) : esc_html__( 'Unlimited', 'cariera' ) ); ?></p>

							<p><?php printf( esc_html__( 'Job listing duration: %s', 'cariera' ), $job_package->get_duration() ? sprintf( _n( '%d day', '%d days', $job_package->get_duration(), 'cariera' ), $job_package->get_duration() ) : '-' ); ?></p>
						</li>
						<?php
					}

					// Showing all the Resume Packages.
					foreach ( $resume_packages as $resume_package ) {
						$resume_package = wc_paid_listings_get_package( $resume_package );
						?>

						<li class="package">
							<i class="list-icon icon-star"></i>
							<h6 class="package-title"><?php echo esc_html( $resume_package->get_title() ); ?></h6>

							<p><?php printf( esc_html__( 'You have %s resumes left that you can post.', 'cariera' ), $resume_package->get_limit() ? absint( $resume_package->get_limit() - $resume_package->get_count() ) : esc_html__( 'Unlimited', 'cariera' ) ); ?></p>

							<p><?php printf( esc_html__( 'Resume listing duration: %s', 'cariera' ), $resume_package->get_duration() ? sprintf( _n( '%d day', '%d days', $resume_package->get_duration(), 'cariera' ), $resume_package->get_duration() ) : '-' ); ?></p>
						</li>
						<?php
					}
				} else {
					?>
					<li><?php esc_html_e( 'No packages have been bought or all packages have been used.', 'cariera' ); ?></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>

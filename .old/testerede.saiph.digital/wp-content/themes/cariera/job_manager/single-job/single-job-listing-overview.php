<?php
/**
 * Custom: Single Job Page - Job Listing Overview
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/single-job-listing-overview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.6
 * @version     1.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'single_job_listing_meta_before' );
?>

<h5 class="mt-0"><?php esc_html_e( 'Job Overview', 'cariera' ); ?></h5>

<aside class="widget widget-job-overview">
	<?php do_action( 'single_job_listing_meta_start' ); ?>

	<div class="single-job-overview-detail single-job-overview-date-posted">
		<div class="icon">
			<i class="icon-calendar"></i>
		</div>

		<div class="content">
			<h6><?php esc_html_e( 'Date Posted', 'cariera' ); ?></h6>
			<span><?php the_date(); ?></span>
		</div>
	</div>

	<?php
	$expired_date = get_post_meta( $post->ID, '_job_expires', true );

	if ( ! empty( $expired_date ) ) {
		?>
		<div class="single-job-overview-detail single-job-overview-expiration-date">
			<div class="icon">
				<i class="icon-reload"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Expiration Date', 'cariera' ); ?></h6>
				<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expired_date ) ) ); ?></span>
			</div>
		</div>
		<?php
	}

	$deadline = get_post_meta( $post->ID, '_application_deadline', true );
	if ( $deadline ) {
		$expiring_days = apply_filters( 'job_manager_application_deadline_expiring_days', 2 );
		$expiring      = ( floor( ( time() - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) ) >= $expiring_days );
		$expired       = ( floor( ( time() - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) ) >= 0 );
		?>

		<div class="single-job-overview-detail application-deadline">
			<div class="icon">
				<i class="icon-close"></i>
			</div>

			<div class="content">
				<h6><?php echo $expired ? esc_html__( 'Applications Closed', 'cariera' ) : esc_html__( 'Applications Close', 'cariera' ); ?></h6>
				<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $deadline ) ) ); ?></span>
			</div>
		</div>
	<?php } ?>

	<div class="single-job-overview-detail single-job-overview-location">
		<div class="icon">
			<i class="icon-location-pin"></i>
		</div>

		<div class="content">
			<h6><?php esc_html_e( 'Location', 'cariera' ); ?></h6>
			<span class="location" itemprop="jobLocation"><?php the_job_location(); ?></span>
		</div>
	</div>

	<?php
	$career_level = get_the_terms( $post->ID, 'job_listing_career_level' );
	if ( taxonomy_exists( 'job_listing_career_level' ) && ! empty( $career_level ) ) {
		?>
		<div class="single-job-overview-detail single-job-overview-career-level">
			<div class="icon">
				<i class="icon-chart"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Career Level', 'cariera' ); ?></h6>
				<span>
					<?php
					foreach ( $career_level as $value ) {
						$output_career_level[] = $value->name;
					}

					echo esc_html( join( ', ', $output_career_level ) );
					?>
				</span>
			</div>
		</div>
	<?php } ?>

	<?php
	$experience = get_the_terms( $post->ID, 'job_listing_experience' );
	if ( taxonomy_exists( 'job_listing_experience' ) && ! empty( $experience ) ) {
		?>
		<div class="single-job-overview-detail single-job-overview-experience">
			<div class="icon">
				<i class="icon-layers"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Experience', 'cariera' ); ?></h6>
				<span>
					<?php
					foreach ( $experience as $value ) {
						$output_experience[] = $value->name;
					}

					echo esc_html( join( ', ', $output_experience ) );
					?>
				</span>
			</div>
		</div>
	<?php } ?>

	<?php
	$qualification = get_the_terms( $post->ID, 'job_listing_qualification' );
	if ( taxonomy_exists( 'job_listing_qualification' ) && ! empty( $qualification ) ) {
		?>
		<div class="single-job-overview-detail single-job-overview-qualification">
			<div class="icon">
				<i class="icon-briefcase"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Qualification', 'cariera' ); ?></h6>
				<span>
					<?php
					foreach ( $qualification as $value ) {
						$output_qualification[] = $value->name;
					}

					echo esc_html( join( ', ', $output_qualification ) );
					?>
				</span>
			</div>
		</div>
	<?php } ?>

	<?php
	$hours = get_post_meta( $post->ID, '_hours', true );
	if ( $hours ) {
		?>
		<div class="single-job-overview-detail single-job-overview-hours">
			<div class="icon">
				<i class="icon-clock"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Hours', 'cariera' ); ?></h6>
				<span><?php printf( esc_html__( '%s hr/week', 'cariera' ), $hours ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php
	$rate_min = get_post_meta( $post->ID, '_rate_min', true );
	if ( $rate_min ) {
		$rate_max = get_post_meta( $post->ID, '_rate_max', true );
		?>

		<div class="single-job-overview-detail single-job-overview-rate">
			<div class="icon">
				<i class="far fa-money-bill-alt"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Rate', 'cariera' ); ?></h6>
				<span><?php cariera_job_rate(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php
	$salary_min = get_post_meta( $post->ID, '_salary_min', true );
	if ( $salary_min ) {
		$salary_max = get_post_meta( $post->ID, '_salary_max', true );
		?>

		<div class="single-job-overview-detail single-job-overview-salary">
			<div class="icon">
				<i class="far fa-money-bill-alt"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Salary', 'cariera' ); ?></h6>
				<span><?php cariera_job_salary(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php
	$job_salary = the_job_salary( '', '', false );
	if ( ! empty( $job_salary ) ) {
		?>
		<div class="single-job-overview-detail single-job-overview-salary">
			<div class="icon">
				<i class="far fa-money-bill-alt"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Salary', 'cariera' ); ?></h6>
				<span><?php echo esc_html( $job_salary ); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php
	if ( class_exists( 'WP_Job_Manager_Applications' ) ) {
		?>
		<div class="single-job-overview-detail single-job-overview-applications">
			<div class="icon">
				<i class="far fa-address-card"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Job Applications', 'cariera' ); ?></h6>
				<span><?php cariera_job_applications(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php do_action( 'single_job_listing_meta_end' ); ?>
</aside>

<?php
do_action( 'single_job_listing_meta_after' );

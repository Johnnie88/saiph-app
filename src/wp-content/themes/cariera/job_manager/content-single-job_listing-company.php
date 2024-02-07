<?php
/**
 * Single view Company information box
 *
 * Hooked into single_job_listing_start priority 30
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-single-job_listing-company.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager
 * @category    Template
 * @since       1.14.0
 * @version     1.31.1
 */

/*
* Cariera Job Listing Company Template
*
* @version 1.7.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( \Cariera\cariera_core_is_activated() && get_option( 'cariera_company_manager_integration', false ) && function_exists( 'cariera_get_the_company' ) ) {
	$company = get_post( cariera_get_the_company() );
} else {
	$company = '';
}

// Return if no cariera company or default company exists for the job.
if ( ( empty( $company ) && get_option( 'cariera_company_manager_integration' ) ) || ! get_the_company_name() ) {
	return;
}

$website = get_the_company_website();
$apply   = get_the_job_application_method();
?>

<div class="company-info">
	<div class="job-company-wrapper">
		<?php if ( ! empty( $company ) && 'publish' === get_post_status( $company ) ) { ?>
			<a href="<?php echo esc_url( get_permalink( $company ) ); ?>">
		<?php } ?>

		<div class="job-company">
			<?php
			// Company Logo.
			if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
				$logo = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
				echo '<img class="company_logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_company_name( $company ) ) . '" />';
			} else {
				cariera_the_company_logo();
			}
			?>
		</div>

		<?php if ( ! empty( $company ) && 'publish' === get_post_status( $company ) ) { ?>
			</a>
		<?php } ?>
	</div>

	<!-- Job Company Info -->
	<div class="job-company-info">
		<?php
		if ( ! empty( $company ) && 'publish' === get_post_status( $company ) ) {
			the_company_name( '<h3 class="single-job-listing-company-name"><a href="' . esc_url( get_permalink( $company ) ) . '">', '</a></h3>' );
		} else {
			the_company_name( '<h3 class="single-job-listing-company-name">', '</h3>' );
		}
		?>

		<!-- Company contact details -->
		<div class="single-job-listing-company-contact">
			<?php
			do_action( 'single_job_listing_company_contact_start' );

			if ( $website ) {
				?>
				<a class="company-website" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="nofollow">
					<i class="icon-globe"></i>
					<?php echo esc_html_e( 'Website', 'cariera' ); ?>
				</a>
				<?php
			}

			if ( \Cariera\company_manager_is_activated() ) {
				$phone = cariera_get_the_company_phone( $company, 'company' );
				if ( ! empty( $company && $phone ) ) {
					?>
					<a href="tel:<?php echo esc_attr( $phone ); ?>" class="company-phone">
						<i class="icon-phone"></i>
						<?php echo esc_html( $phone ); ?>
					</a>
					<?php
				}
			}

			if ( $apply && isset( $apply->type ) && 'email' === $apply->type ) {
				$application_email = $apply->email;
				?>
				<a class="company-application-email" href="mailto:<?php echo esc_attr( $application_email ); ?>" target="_blank" rel="nofollow">
					<i class="icon-envelope"></i>
					<?php echo wp_kses_post( $application_email ); ?>
				</a>
				<?php
			} elseif ( \Cariera\company_manager_is_activated() ) {
				$email = cariera_get_the_company_email( $company );
				if ( ! empty( $company && $email ) ) {
					?>
					<a class="company-application-email" href="mailto:<?php echo esc_attr( $email ); ?>" target="_blank" rel="nofollow">
						<i class="icon-envelope"></i>
						<?php echo wp_kses_post( $email ); ?>
					</a>
					<?php
				}
			}

			do_action( 'single_job_listing_company_contact_end' );
			?>
		</div>
	</div>
</div>

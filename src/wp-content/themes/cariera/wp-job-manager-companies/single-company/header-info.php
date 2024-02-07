<?php
/**
 * Custom: Single Company - Header Info
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/header-info.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="company-info">
	<div class="company-photo">
		<?php cariera_the_company_logo(); ?>
	</div>

	<div class="company-details">
		<h1 class="company-name"><?php echo apply_filters( 'cariera_company_name', get_the_title() ); ?></h1>

		<?php do_action( 'cariera_single_company_contact_start' ); ?>

		<?php if ( ! empty( cariera_get_the_company_website_link() ) ) { ?>
			<div class="company-website">
				<i class="icon-globe"></i>
				<a href="<?php echo esc_url( cariera_get_the_company_website_link() ); ?>" target="_blank">
					<?php echo cariera_get_the_company_website_link(); ?>
				</a>
			</div>
		<?php } ?>

		<?php if ( ! empty( cariera_get_the_company_phone() ) ) { ?>
			<div class="company-phone">
				<i class="icon-phone"></i>
				<a href="tel:<?php echo esc_attr( cariera_get_the_company_phone() ); ?>"><?php echo cariera_get_the_company_phone(); ?></a>
			</div>
		<?php } ?>

		<?php if ( ! empty( cariera_get_the_company_email() ) ) { ?>
			<div class="company-email">
				<i class="icon-envelope"></i>
				<a href="mailto:<?php echo esc_attr( cariera_get_the_company_email() ); ?>">
					<?php echo cariera_get_the_company_email(); ?>
				</a>
			</div>
		<?php } ?>

		<?php do_action( 'cariera_single_company_contact_end' ); ?>        
	</div>

	<div class="company-extra-info">
		<div class="company-social">
			<?php
			cariera_company_fb_output();
			cariera_company_twitter_output();
			cariera_company_linkedin_output();
			cariera_company_instagram_output();

			do_action( 'cariera_company_bookmarks' );
			?>
		</div>

		<?php
		if ( get_option( 'cariera_private_messages' ) && get_option( 'cariera_private_messages_companies' ) ) {
			get_job_manager_template_part( 'single-company/private', 'message', 'wp-job-manager-companies' );
		} else {
			get_job_manager_template_part( 'company', 'contact', 'wp-job-manager-companies' );
		}
		?>
	</div>
</div>

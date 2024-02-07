<?php
/**
 * Custom: Company - Company Contact
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-contact.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.0
 * @version     1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $company_preview;

if ( $company_preview ) {
	return;
}

if ( cariera_user_can_view_contact_details( $post->ID ) ) {
	$form_id = get_option( 'cariera_single_company_contact_form' );

	if ( ! empty( $form_id ) ) {
		$shortcode = sprintf( '[contact-form-7 id="%1$d" title="%2$s"]', $form_id, get_the_title( $form_id ) ); ?>

		<div class="company-contact">
			<a href="#company-contact-popup" class="btn btn-main btn-effect popup-with-zoom-anim"><?php esc_html_e( 'Contact Us', 'cariera' ); ?></a>

			<div id="company-contact-popup" class="small-dialog zoom-anim-dialog mfp-hide">
				<div class="bookmarks-popup">
					<div class="small-dialog-headline"><h3 class="title"><?php esc_html_e( 'Contact Company', 'cariera' ); ?></h3></div>
					<div class="small-dialog-content text-left">
						<?php echo do_shortcode( $shortcode ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
} else {
	get_job_manager_template_part( 'access-denied', 'contact-details', 'wp-job-manager-companies' );
}

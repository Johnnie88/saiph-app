<?php
/**
 * Custom: Single Job Page - Job Application
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/single-job-application.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! candidates_can_apply() ) {
	return;
}

$external_apply              = get_post_meta( $post->ID, '_apply_link', true );
$login_registration          = get_option( 'cariera_login_register_layout' );
$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
$login_registration_page_url = get_permalink( $login_registration_page );

if ( ! empty( $external_apply ) ) { ?>
	<div class="job_application application external-application">
		<?php
		// Check if Application is restricted to logged in users.
		if ( get_option( 'job_application_form_require_login', 0 ) && ! is_user_logged_in() ) {
			?>

			<div class="job-manager-applications-applied-notice"><?php esc_html_e( 'Please login to apply for this job.', 'cariera' ); ?></div>

			<a href="<?php echo ( 'popup' === $login_registration ) ? esc_attr( '#login-register-popup' ) : esc_url( $login_registration_page_url ); ?>" class="application_button btn btn-main btn-effect <?php echo ( 'popup' === $login_registration ) ? esc_attr( 'popup-with-zoom-anim' ) : ''; ?>"><?php echo esc_html_e( 'Login', 'cariera' ); ?></a>
		<?php } else { ?>
			<a href="<?php echo esc_url( $external_apply ); ?>" target="_blank" class="external_application_btn btn btn-main btn-effect"><?php esc_html_e( 'apply for job', 'cariera' ); ?></a>
			<form method="post">
				<input type="hidden" id="page-id" name="page-id" value="<?php the_id(); ?>" />
			</form>
		<?php } ?>
	</div>
	<?php
} else {
	get_job_manager_template( 'job-application.php' );
}

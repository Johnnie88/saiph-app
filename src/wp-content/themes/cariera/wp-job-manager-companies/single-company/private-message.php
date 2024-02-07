<?php
/**
 * Custom: Single Company - Private Message
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/single-company/private-message.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.6.0
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $company_preview;

if ( $company_preview ) {
	return;
}

$logo = get_the_company_logo( $post->ID, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
} ?>

<div class="company-private-message">
	<?php if ( is_user_logged_in() ) { ?>
		<a href="#private-messages" class="popup-with-zoom-anim btn btn-main listing-btn" 
		<?php echo ( get_the_author_meta( 'ID' ) === get_current_user_id() ) ? 'data-disabled="true"' : ''; ?> 
		data-id="<?php echo esc_attr( get_the_ID() ); ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>" 
		data-image="<?php echo esc_url( $logo_img ); ?>" 
		data-author="<?php echo esc_attr( get_the_author_meta( 'ID' ) ); ?>" 
		data-user-id="1">
			<?php esc_html_e( 'Direct message', 'cariera' ); ?>
		</a>
		<?php
	} else {
		$login_registration = get_option( 'cariera_login_register_layout' );

		if ( 'popup' === $login_registration ) {
			$login_registration_page_url = '#login-register-popup';
			$login_registration_class    = 'popup-with-zoom-anim';
		} else {
			$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
			$login_registration_page_url = get_permalink( $login_registration_page );
			$login_registration_class    = '';
		}
		?>

		<a href="<?php echo esc_url( $login_registration_page_url ); ?>" class="btn btn-main listing-btn <?php echo esc_attr( $login_registration_class ); ?>">
			<?php esc_html_e( 'Direct message', 'cariera' ); ?>
		</a>
	<?php } ?>
</div>

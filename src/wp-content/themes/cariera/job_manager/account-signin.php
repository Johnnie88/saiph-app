<?php
/**
 * In job listing creation flow, this template shows above the job creation form.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/account-signin.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.33.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>


<?php if ( is_user_logged_in() ) { ?>

	<fieldset class="fieldset-logged_in">
		<label class="account-sign-in-label"><?php esc_html_e( 'Your account', 'cariera' ); ?></label>
		<div class="field account-sign-in">
			<p>
				<?php
					$user = wp_get_current_user();
					// translators: Placeholder %s is the username.
					printf( wp_kses_post( __( 'You are currently signed in as <strong>%s</strong>.', 'cariera' ) ), esc_html( $user->user_login ) );
				?>
			</p>

			<div class="button-wrapper">
				<a class="btn btn-main btn-effect" href="<?php echo apply_filters( 'submit_job_form_logout_url', wp_logout_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign out', 'cariera' ); ?></a>
			</div>
		</div>
	</fieldset>

	<?php
} else {
	$account_required            = job_manager_user_requires_account();
	$registration_enabled        = job_manager_enable_registration();
	$registration_fields         = wpjm_get_registration_fields();
	$use_standard_password_email = wpjm_use_standard_password_setup_email();
	?>
	<fieldset class="fieldset-login_required">
		<label class="account-sign-in-label"><?php esc_html_e( 'Have an account?', 'cariera' ); ?></label>
		<div class="field account-sign-in">

			<?php if ( $registration_enabled ) { ?>
				<p>
					<?php printf( esc_html__( 'If you don\'t have an account you can %s create one below by entering your email address/username.', 'cariera' ), $account_required ? '' : esc_html__( 'optionally', 'cariera' ) . ' ' ); ?>
					<?php if ( $use_standard_password_email ) : ?>
						<?php printf( esc_html__( 'Your account details will be confirmed via email.', 'cariera' ) ); ?>
					<?php endif; ?>
				</p>
			<?php } elseif ( $account_required ) { ?>
				<p><?php echo wp_kses_post( apply_filters( 'submit_job_form_login_required_message', __( 'You must sign in to create a new listing.', 'cariera' ) ) ); ?></p>
			<?php } ?>

			<div class="button-wrapper">
				<?php
				$login_registration = get_option( 'cariera_login_register_layout' );

				if ( $login_registration == 'popup' ) {
					?>
					<a href="#login-register-popup" class="popup-with-zoom-anim btn btn-main btn-effect">
					<?php
				} else {
					$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
					$login_registration_page_url = get_permalink( $login_registration_page );
					?>

					<a href="<?php echo esc_url( $login_registration_page_url ); ?>" class="btn btn-main btn-effect mt10">
				<?php } ?>

				<?php esc_html_e( 'Sign in', 'cariera' ); ?></a>
			</div>

		</div>
	</fieldset>

	<?php
	if ( ! empty( $registration_fields ) ) {
		foreach ( $registration_fields as $key => $field ) {
			?>
			<fieldset class="fieldset-<?php echo esc_attr( $key ); ?>">
				<label for="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( $field['label'] ) . wp_kses_post( apply_filters( 'submit_job_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'cariera' ) . '</small>', $field ) ); ?>
				</label>
				<div class="field <?php echo $field['required'] ? 'required-field draft-required' : ''; ?>">
					<?php
					get_job_manager_template(
						'form-fields/' . $field['type'] . '-field.php',
						[
							'key'   => $key,
							'field' => $field,
						]
					);
					?>
				</div>
			</fieldset>
			<?php
		}
		do_action( 'job_manager_register_form' );
	}
}

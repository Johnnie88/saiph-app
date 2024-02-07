<?php
/**
 * Account sign-in template to display above submit resume form.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/account-signin.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Resume Manager
 * @category    Template
 * @version     1.15.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() ) { ?>

	<fieldset>
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
				<a class="btn btn-main btn-effect" href="<?php echo apply_filters( 'submit_resume_form_logout_url', wp_logout_url( esc_url( get_permalink() ) ) ); ?>"><?php esc_html_e( 'Sign out', 'cariera' ); ?></a>
			</div>
		</div>
	</fieldset>

	<?php
} else {
	$account_required     = resume_manager_user_requires_account();
	$registration_enabled = resume_manager_enable_registration();
	$registration_fields  = resume_manager_get_registration_fields();
	?>

	<fieldset>
		<label class="account-sign-in-label"><?php esc_html_e( 'Have an account?', 'cariera' ); ?></label>

		<div class="field account-sign-in">
			<?php if ( $registration_enabled ) { ?>
				<p>
					<?php esc_html_e( 'If you don&rsquo;t have an account you can create one below by entering your email address. Your account details will be confirmed via email.', 'cariera' ); ?>
				</p>
			<?php } elseif ( $account_required ) { ?>
				<p>
					<?php echo apply_filters( 'submit_resume_form_login_required_message', esc_html__( 'You must sign in to submit a resume.', 'cariera' ) ); ?>
				</p>
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
				<label
					for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ) . wp_kses_post( apply_filters( 'submit_resume_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'cariera' ) . '</small>', $field ) ); ?></label>
				<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
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
		<?php }
		do_action( 'resume_manager_register_form' );
	}
} ?>

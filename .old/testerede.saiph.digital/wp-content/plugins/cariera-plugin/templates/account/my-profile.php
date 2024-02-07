<?php
/**
 * Cariera My Profile template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/my-profile.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.2
 * @version     1.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_roles;

do_action( 'cariera_my_account_start' );
?>

<?php if ( ! is_user_logged_in() ) { ?>
	<p><?php esc_html_e( 'You must be logged in to edit your profile.', 'cariera' ); ?></p>

	<?php
	$login_registration          = get_option( 'cariera_login_register_layout' );
	$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
	$login_registration_page_url = get_permalink( $login_registration_page );
	?>
	<a class="btn btn-main btn-effect <?php echo 'popup' === $login_registration ? 'popup-with-zoom-anim' : ''; ?>" href="<?php echo 'popup' === $login_registration ? '#login-register-popup' : esc_url( $login_registration_page_url ); ?>"><?php esc_html_e( 'Sign in', 'cariera' ); ?></a>

	<?php
} else {
	$current_user = wp_get_current_user();
	$user_id      = get_current_user_id();
	$user_img     = get_avatar( get_the_author_meta( 'ID', $user_id ), 120 );
	$user_role    = $current_user->roles[0];
	?>

	<div class="row">

		<!-- Start of Edit My Profile -->
		<div class="col-lg-6 col-md-12">
			<div class="dashboard-card-box">
				<h4 class="title"><?php esc_html_e( 'Profile Details', 'cariera' ); ?></h4>

				<div class="dashboard-card-box-inner">
					<form name="change_details_form" class="change-details-form" action="" method="post" >

						<!-- Details -->
						<div class="my-profile">

							<div class="user-avatar-upload">
								<?php
								$custom_avatar = $current_user->cariera_avatar_id;
								$custom_avatar = wp_get_attachment_url( $custom_avatar );
								if ( ! empty( $custom_avatar ) ) {
									?>
									<div data-photo="<?php echo esc_attr( $custom_avatar ); ?>" data-name="<?php esc_html_e( 'Your Avatar', 'cariera' ); ?>" data-size="<?php echo esc_attr( filesize( get_attached_file( $current_user->cariera_avatar_id ) ) ); ?>" class="edit-profile-photo">
								<?php } else { ?>
									<div class="edit-profile-photo">
								<?php } ?>
										<div id="cariera-avatar-uploader" class="cariera-uploader cariera-dropzone">
											<div class="dz-message" data-dz-message><span><i class="fas fa-cloud-upload-alt"></i></span></div>
										</div>
										<input type="hidden" name="cariera_avatar_id" id="avatar-uploader-id" value="<?php echo esc_attr( $current_user->cariera_avatar_id ); ?>" />
									</div>

								<div class="user-avatar-description">
									<p><?php echo apply_filters( 'cariera_my_account_avatar_description', esc_html__( 'Update your photo manually, if the photo is not set the default Gravatar will be the same as your login email account. Please make sure that your uploaded image is a square size image.', 'cariera' ) ); ?></p>
								</div>
							</div>

							<?php
							if ( get_option( 'cariera_account_role_change' ) ) {
								if ( in_array( $user_role, [ 'employer', 'candidate' ], true ) ) {
									?>
									<div class="form-group">
										<!-- User Roles Wrapper -->
										<div class="user-roles-wrapper">
											<?php if ( class_exists( 'WP_Resume_Manager' ) ) { ?>
												<div class="user-role candidate-role">
													<input type="radio" name="cariera_user_role" id="candidate-input" value="candidate" class="user-role-radio" <?php echo $user_role === 'candidate' ? 'checked' : ''; ?>>
													<label for="candidate-input">
														<i class="icon-people"></i>
														<div>
															<span><?php esc_html_e( 'Registered as a', 'cariera' ); ?></span>
															<h6><?php esc_html_e( 'Candidate', 'cariera' ); ?></h6>
														</div>
													</label>
												</div>
											<?php } ?>

											<div class="user-role employer-role">
												<input type="radio" name="cariera_user_role" id="employer-input" value="employer" class="user-role-radio" <?php echo $user_role === 'employer' ? 'checked' : ''; ?>>
												<label for="employer-input">
													<i class="icon-briefcase"></i>
													<div>
														<span><?php esc_html_e( 'Registered as an', 'cariera' ); ?></span>
														<h6><?php esc_html_e( 'Employer', 'cariera' ); ?></h6>
													</div>
												</label>
											</div>
										</div>
									</div>
									<?php
								}
							}
							?>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="first-name"><?php esc_html_e( 'First Name', 'cariera' ); ?></label>
										<input name="first-name" type="text" id="first-name" value="<?php echo esc_attr( $current_user->user_firstname ); ?>" />
									</div>
									<div class="col-sm-6">
										<label for="last-name"><?php esc_html_e( 'Last Name', 'cariera' ); ?></label>
										<input name="last-name" type="text" id="last-name" value="<?php echo esc_attr( $current_user->user_lastname ); ?>" />
									</div>
								</div>             
							</div>

							<div class="form-group">
								<div class="row">
									<div class="col-sm-6">
										<label for="user_email"><?php esc_html_e( 'E-mail', 'cariera' ); ?></label>
										<input name="user_email" type="text" id="email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
									</div>
									<div class="col-sm-6">
										<label for="phone"><?php esc_html_e( 'Phone Number', 'cariera' ); ?></label>
										<input name="phone" type="text" id="phone" value="<?php the_author_meta( 'phone', $current_user->ID ); ?>" />
									</div>
								</div>             
							</div>

							<div class="form-group">
								<button type="submit" class="btn btn-main btn-effect"><?php esc_html_e( 'Save Changes', 'cariera' ); ?></button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- End of Edit My Profile -->


		<!-- 2nd Column -->
		<div class="col-lg-6 col-md-12">

			<!-- Start of Change Password Form -->
			<div class="dashboard-card-box">
				<h4 class="title"><?php esc_html_e( 'Change Password', 'cariera' ); ?></h4>

				<div class="dashboard-card-box-inner">
					<form name="change_password_form" class="change-pass-form" action="" method="post">
						<div class="form-group password-handling">
							<label name="current_password"><?php esc_html_e( 'Current Password', 'cariera' ); ?></label>
							<input type="password" name="current_password">
							<i class="far fa-eye"></i>
						</div>

						<div class="form-group password-handling">
							<label for="new_password"><?php esc_html_e( 'New Password', 'cariera' ); ?></label>
							<input name="new_password" type="password">
							<i class="far fa-eye"></i>
						</div>

						<div class="form-group password-handling">
							<label for="confirm_password"><?php esc_html_e( 'Confirm New Password', 'cariera' ); ?></label>
							<input name="confirm_password" type="password">
							<i class="far fa-eye"></i>
						</div>

						<div class="form-group">
							<button type="submit" class="btn btn-main btn-effect" /><?php esc_html_e( 'Change Password', 'cariera' ); ?></button>
						</div>
					</form>
				</div>
			</div>
			<!-- End of Change Password Form -->

			<?php if ( ! current_user_can( 'administrator' ) && apply_filters( 'cariera_delete_account', '__return_true' ) ) { ?>
				<!-- Start of Delete Account -->
				<div class="dashboard-card-box delete-account">
					<h4 class="title"><?php esc_html_e( 'Delete Account', 'cariera' ); ?></h4>

					<div class="dashboard-card-box-inner">
						<form name="delete-account" class="delete-account-form" action="" method="post">
							<p><?php esc_html_e( 'Before you delete your account, remember that all of your data will also be deleted. This action can not be undone!', 'cariera' ); ?></p>

							<div class="form-group password-handling">
								<label><?php esc_html_e( 'Current Password', 'cariera' ); ?></label>
								<input name="current_pass" id="current_pass" type="password">
								<i class="far fa-eye"></i>
							</div>

							<div class="form-group">
								<?php wp_nonce_field( 'cariera_delete_account', 'nonce' ); ?>
								<button type="submit" class="btn btn-main btn-effect" /><?php esc_html_e( 'Delete Account', 'cariera' ); ?></button>
							</div>
						</form>
					</div>
				</div>
				<!-- End of Delete Account -->
			<?php } ?>

		</div>
	</div>
	<?php
}

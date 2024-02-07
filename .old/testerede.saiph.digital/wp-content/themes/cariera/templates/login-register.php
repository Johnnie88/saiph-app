<?php
/**
 *
 * @package Cariera
 *
 * @since    1.4.5
 * @version  1.5.4
 *
 * ========================
 * Template Name: Login - Register
 * ========================
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If user is not logged.
if ( ! is_user_logged_in() ) {

	get_header( 'empty' ); ?>

	<main class="login-register-page">
		<div class="container-fluid">
			<div class="row align-items-center">

				<!-- Title Wrapper -->
				<div class="col-xl-8 col-lg-6 col-md-6 title-wrapper overlay-gradient" style="background-image:url( <?php echo esc_attr( cariera_get_option( 'login_page_image' ) ); ?> )">
					<div class="content">
						<h2 class="title"><?php echo esc_html( cariera_get_option( 'login_page_text' ) ); ?></h2>
					</div>
				</div>

				<!-- Form Wrapper -->
				<div class="col-xl-4 col-lg-6 col-md-6 form-wrapper cariera-scroll">
					<div class="content">

						<!-- ====== Start of Logo ====== -->
						<div class="logo">            
							<?php if ( cariera_get_option( 'logo' ) ) { ?>
								<a class="navbar-brand logo-wrapper nomargin" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr( bloginfo( 'name' ) ); ?>" rel="home">
									<!-- Logo -->
									<img src="<?php echo esc_url( cariera_get_option( 'logo' ) ); ?>" class="logo" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" />
								</a>
							<?php } elseif ( cariera_get_option( 'logo_text' ) ) { ?>
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="logo-text">
									<?php echo esc_html( cariera_get_option( 'logo_text' ) ); ?>
								</a>
							<?php } else { ?>
								<a class="navbar-brand logo-wrapper" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_html( bloginfo( 'name' ) ); ?>" rel="home">
									<!-- INSERT YOUR LOGO HERE -->
									<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.svg' ); ?>" alt="<?php esc_attr( bloginfo( 'name' ) ); ?>" width="150" class="logo">
								</a>
							<?php } ?>
						</div>
						<!-- ====== End of Logo ====== -->

						<!-- Start of Signin wrapper -->
						<div class="signin-wrapper">
							<h3 class="nomargin"><?php esc_html_e( 'Sign in', 'cariera' ); ?></h3>

							<?php echo do_shortcode( '[cariera_login_form]' ); // Add login form. ?>

							<div class="bottom-links">
								<a href="#" class="signup-trigger"><i class="fas fa-user"></i><?php esc_html_e( 'Don\'t have an account?', 'cariera' ); ?></a>
								<a href="#" class="forget-password-trigger"><i class="fas fa-lock"></i><?php esc_html_e( 'Forgot Password?', 'cariera' ); ?></a>
							</div>

							<?php do_action( 'cariera_social_login' ); ?>
						</div>
						<!-- End of Signin wrapper -->

						<!-- Start of Signup wrapper -->
						<div class="signup-wrapper">
							<h3 class="nomargin"><?php esc_html_e( 'Sign Up', 'cariera' ); ?></h3>

							<?php echo do_shortcode( '[cariera_registration_form]' ); // Add registration form. ?>

							<div class="bottom-links">
								<a href="#" class="signin-trigger"><i class="fas fa-user"></i><?php esc_html_e( 'Already registered?', 'cariera' ); ?></a>
								<a href="#" class="forget-password-trigger"><i class="fas fa-lock"></i><?php esc_html_e( 'Forgot Password?', 'cariera' ); ?></a>
							</div>

							<?php do_action( 'cariera_social_login' ); ?>
						</div>
						<!-- End of Signup wrapper -->

						<!-- Start of Forget Password wrapper -->
						<div class="forgetpassword-wrapper">
							<h3 class="nomargin"><?php esc_html_e( 'Forgotten Password', 'cariera' ); ?></h3>

							<?php echo do_shortcode( '[cariera_forgetpass_form]' ); // Add forget password form. ?>

							<div class="bottom-links">
								<a href="#" class="signin-trigger"><i class="fas fa-arrow-left"></i><?php esc_html_e( 'Sign in', 'cariera' ); ?></a>
							</div>
						</div>
						<!-- End of Forget Password wrapper -->

						<div class="back-home">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="icon-arrow-left-circle"></i><?php esc_html_e( 'Back to home', 'cariera' ); ?></a>
						</div>
					</div>

				</div>
			</div>
		</div>
	</main>

	<?php
	get_footer( 'empty' );

	// Else user is logged.
} else {

	get_header();

	// Page Header.
	if ( get_post_meta( get_the_ID(), 'cariera_show_page_title', 'true' ) !== 'hide' ) {
		$page_header = get_post_meta( $post->ID, 'cariera_page_header_bg', true );

		if ( ! empty( $page_header ) ) {
			$image = wp_get_attachment_url( $page_header );
			?>
			<section class="page-header page-header-bg" style="background: url(<?php echo esc_attr( $image ); ?>);">
		<?php } else { ?>
			<section class="page-header">
		<?php } ?>

			<div class="container">
				<div class="row">
					<div class="col-md-12 text-center">
						<h1 class="title"><?php echo \Cariera\get_the_title(); ?></h1>
						<?php echo \Cariera\breadcrumbs(); ?>
					</div>
				</div>
			</div>
		</section>
	<?php } ?>

	<!-- ===== Start of Main Wrapper ===== -->
	<main id="post-<?php the_ID(); ?>" <?php post_class( 'login-register-page ptb80' ); ?>>
		<div class="container">
			<?php global $current_user; ?>

			<div class="myaccount-loggedin">
				<?php
				$user_id         = get_current_user_id();
				$user_img        = get_avatar( get_the_author_meta( 'ID', $user_id ), 150 );
				$dashboard_title = \Cariera\get_page_by_title( 'Dashboard' );
				$dashboard_page  = apply_filters( 'cariera_dashboard_page', get_option( 'cariera_dashboard_page' ) );

				if ( $dashboard_page ) {
					$account_link = get_permalink( $dashboard_page );
				} else {
					$account_link = get_permalink( $dashboard_title );
				}
				?>

				<span class="user-avatar">
					<?php echo wp_kses_post( $user_img ); ?>
				</span>

				<h3 class="mb30"><?php printf( esc_html__( 'Hello %1$s', 'cariera' ), '<span class="text-primary">' . esc_html( $current_user->display_name ) . '</span>' ); ?></h3>
				<a class="btn btn-main btn-effect mr10 mb10" href="<?php echo apply_filters( 'cariera_login_template_dashboard_url', esc_url( $account_link ) ); ?>"><?php esc_html_e( 'Dashboard', 'cariera' ); ?></a>
				<a class="btn btn-main btn-effect mr10 mb10" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Log out', 'cariera' ); ?></a>                        
			</div>

			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile; // End of the loop.
			?>

		</div>
	</main>
	<!-- ===== End of Main Wrapper ===== -->

	<?php
	get_footer();
}

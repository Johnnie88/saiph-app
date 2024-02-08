<?php

// Add Elementor Pro support for Custom Footer.
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {

	if ( get_post_meta( get_the_ID(), 'cariera_show_footer', 'true' ) != 'hide' ) {
		$footer_style     = cariera_get_option( 'cariera_footer_style' );
		$footer_info      = cariera_get_option( 'cariera_footer_info' );
		$footer_sidebar_1 = cariera_get_option( 'cariera_footer_sidebar_1' );
		$footer_sidebar_2 = cariera_get_option( 'cariera_footer_sidebar_2' );
		$footer_sidebar_3 = cariera_get_option( 'cariera_footer_sidebar_3' );
		$footer_sidebar_4 = cariera_get_option( 'cariera_footer_sidebar_4' ); ?>


		<footer class="main-footer <?php echo esc_attr( $footer_style ); ?>">
			<?php
			if ( true == $footer_info ) {
				if ( get_post_meta( get_the_ID(), 'cariera_show_footer_widgets', 'true' ) != 'hide' ) {
					if ( is_active_sidebar( 'footer-widget-area' ) || is_active_sidebar( 'footer-widget-area-2' ) || is_active_sidebar( 'footer-widget-area-3' ) || is_active_sidebar( 'footer-widget-area-4' ) ) {
						?>

						<div class="footer-widget-area footer-info">
							<div class="container">
								<div class="row">
									<?php if ( $footer_sidebar_1 != 'disabled' ) { ?>
										<div class="<?php echo esc_attr( $footer_sidebar_1 ); ?>">
											<?php dynamic_sidebar( 'footer-widget-area' ); ?>
										</div>
									<?php } ?>

									<?php if ( $footer_sidebar_2 != 'disabled' ) { ?>
										<div class="<?php echo esc_attr( $footer_sidebar_2 ); ?>">
											<?php dynamic_sidebar( 'footer-widget-area-2' ); ?>
										</div>
									<?php } ?>

									<?php if ( $footer_sidebar_3 != 'disabled' ) { ?>
										<div class="<?php echo esc_attr( $footer_sidebar_3 ); ?>">
											<?php dynamic_sidebar( 'footer-widget-area-3' ); ?>
										</div>
									<?php } ?>

									<?php if ( $footer_sidebar_4 != 'disabled' ) { ?>
										<div class="<?php echo esc_attr( $footer_sidebar_4 ); ?>">
											<?php dynamic_sidebar( 'footer-widget-area-4' ); ?>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<?php
					}
				}
			}
			?>

			<!-- ===== Start of Footer Copyright Section ===== -->
			<div class="copyright">
				<div class="container">
					<div class="row">
						<div class="col-md-6 col-sm-6 col-xs-12">
							<h6>
								<?php
								$copyright = cariera_get_option( 'cariera_copyrights' );
								echo wp_kses_post( $copyright );
								?>
							</h6>
						</div>

						<div class="col-md-6 col-sm-6 col-xs-12">
							<?php get_template_part( 'templates/footer/social-media' ); ?>
						</div>                    
					</div>
				</div>
			</div>
			<!-- ===== End of Footer Copyright Section ===== -->

		</footer>
		<?php
	} // End if cariera_show_footer
}
?>

<?php if ( cariera_get_option( 'cariera_back_top', 'on' ) == 'on' ) { ?>
	<a href="#" class="back-top"><i class="fas fa-chevron-up"></i></a>
<?php } ?>

</div>
<!-- End of Website wrapper -->

<?php wp_footer(); ?>

</body>
</html>

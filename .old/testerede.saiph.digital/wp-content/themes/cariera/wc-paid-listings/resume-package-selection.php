<?php
/**
 * Template for choosing a package during the Resume submission.
 *
 * This template can be overridden by copying it to yourtheme/wc-paid-listings/resume-package-selection.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-resumes
 * @category    Template
 * @since       1.0.0
 * @version     2.9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $packages || $user_packages ) :
	$checked = 1;
	?>
	<ul class="resume_packages">
		<?php if ( $user_packages ) : ?>
			<li class="package-section"><?php esc_html_e( 'Your Packages:', 'cariera' ); ?></li>
			<?php
			foreach ( $user_packages as $key => $package ) :
				$package = wc_paid_listings_get_package( $package );
				?>
				<li class="user-resume-package">
					<div class="package-button"></div>
					<div class="package-details">
						<input type="radio" <?php checked( $checked, 1 ); ?> name="resume_package" value="user-<?php echo esc_attr( $key ); ?>" id="user-package-<?php echo esc_attr( $package->get_id() ); ?>" />
						<label for="user-package-<?php echo esc_attr( $package->get_id() ); ?>"><?php echo esc_attr( $package->get_title() ); ?></label><br/>

						<div class="package-desc">
							<?php
							if ( $package->get_limit() ) {
								printf( _n( '%1$s resume posted out of %2$d', '%1$s resumes posted out of %2$s', $package->get_count(), 'cariera' ), $package->get_count(), $package->get_limit() );
							} else {
								printf( _n( '%s resume posted', '%s resumes posted', $package->get_count(), 'cariera' ), $package->get_count() );
							}

							if ( $package->get_duration() ) {
								printf( ' ' . _n( 'listed for %s day', 'listed for %s days', $package->get_duration(), 'cariera' ), $package->get_duration() );
							}

								$checked = 0;
							?>
						</div>
					</div>

					<div class="package-footer">
						<?php if ( $package->get_limit() ) { ?>
							<span class="price"><?php echo esc_html( $package->get_limit() - $package->get_count() ); ?></span>
							<span class="caption"><?php echo esc_html_e( 'Resumes Remaining', 'cariera' ); ?></span>
						<?php } ?>
					</div>

				</li>
			<?php endforeach; ?>
		<?php endif; ?>


		<?php if ( $packages ) : ?>
			<li class="package-section"><?php esc_html_e( 'Purchase Package:', 'cariera' ); ?></li>
			<?php
			foreach ( $packages as $key => $package ) :
				$product = wc_get_product( $package );
				if ( ! $product->is_type( [ 'resume_package', 'resume_package_subscription' ] ) || ! $product->is_purchasable() ) {
					continue;
				}
				/* @var $product WC_Product_Resume_Package|WC_Product_Resume_Package_Subscription */
				if ( $product->is_type( 'variation' ) ) {
					$post = get_post( $product->get_parent_id() );
				} else {
					$post = get_post( $product->get_id() );
				}
				?>

				<li class="resume-package">
					<div class="package-button"></div>
					<div class="package-details">
						<input type="radio" <?php checked( $checked, 1 ); ?> name="resume_package" value="<?php echo esc_attr( $product->get_id() ); ?>" id="package-<?php echo esc_attr( $product->get_id() ); ?>" />
						<label for="package-<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->get_title() ); ?></label><br/>

						<div class="package-desc">
							<?php
							if ( ! empty( $post->post_excerpt ) ) {
								echo apply_filters( 'woocommerce_short_description', $post->post_excerpt );
							} else {
								printf( _n( '%1$s to post %2$d resume', '%1$s to post %2$s resumes', $product->get_limit(), 'cariera' ) . ' ', $product->get_price_html(), $product->get_limit() ? $product->get_limit() : esc_html__( 'unlimited', 'cariera' ) );

								if ( $product->get_duration() ) {
									printf( ' ' . _n( 'listed for %s day', 'listed for %s days', $product->get_duration(), 'cariera' ), $product->get_duration() );
								}
							}
							$checked = 0;
							?>
						</div>
					</div>

					<div class="package-footer">
						<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
						<span class="caption"><?php echo esc_html( $product->get_duration() ) ? sprintf( _n( '%s days payment', '%s days payment', $product->get_duration(), 'cariera' ), $product->get_duration() ) : ''; ?></span>
					</div> 

				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>

	<span><?php esc_html_e( 'Select a package from above and submit.', 'cariera' ); ?></span>
<?php else : ?>

	<p><?php esc_html_e( 'No packages found', 'cariera' ); ?></p>

<?php endif; ?>

<?php do_action( 'cariera_resume_submission_steps' ); ?>

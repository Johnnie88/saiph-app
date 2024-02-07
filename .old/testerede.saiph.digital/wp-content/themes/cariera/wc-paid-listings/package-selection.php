<?php
/**
 * Template for choosing a package during the Job Listing submission.
 *
 * This template can be overridden by copying it to yourtheme/wc-paid-listings/package-selection.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-resumes
 * @category    Template
 * @since       1.0.0
 * @version     2.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $packages || $user_packages ) :
	$checked = 1;
	?>
	<ul class="job_packages">
		<?php if ( $user_packages ) : ?>
			<li class="package-section"><?php esc_html_e( 'Your Packages:', 'cariera' ); ?></li>
			<?php
			foreach ( $user_packages as $key => $package ) :
				$package = wc_paid_listings_get_package( $package );
				?>
				<li class="user-job-package">
					<div class="package-button"></div>
					<div class="package-details">
						<input type="radio" <?php checked( $checked, 1 ); ?> name="job_package" value="user-<?php echo esc_attr( $key ); ?>" id="user-package-<?php echo esc_attr( $package->get_id() ); ?>" />
						<label for="user-package-<?php echo esc_attr( $package->get_id() ); ?>"><?php echo esc_html( $package->get_title() ); ?></label><br/>

						<div class="package-desc">
							<?php
							if ( $package->get_limit() ) {
								printf( _n( '%1$s job posted out of %2$d', '%1$s jobs posted out of %2$d', $package->get_count(), 'cariera' ), $package->get_count(), $package->get_limit() );
							} else {
								printf( _n( '%s job posted', '%s jobs posted', $package->get_count(), 'cariera' ), $package->get_count() );
							}

							if ( $package->get_duration() ) {
								printf( ', ' . _n( 'listed for %s day', 'listed for %s days', $package->get_duration(), 'cariera' ), $package->get_duration() );
							}

							$checked = 0;
							?>
						</div>
					</div>

					<div class="package-footer">
						<?php if ( $package->get_limit() ) { ?>
							<span class="price"><?php echo esc_html( $package->get_limit() - $package->get_count() ); ?></span>
							<span class="caption"><?php echo esc_html__( 'Jobs Remaining', 'cariera' ); ?></span>
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
				if ( ! $product->is_type( [ 'job_package', 'job_package_subscription' ] ) || ! $product->is_purchasable() ) {
					continue;
				}
				/* @var $product WC_Product_Job_Package|WC_Product_Job_Package_Subscription */
				if ( $product->is_type( 'variation' ) ) {
					$post = get_post( $product->get_parent_id() );
				} else {
					$post = get_post( $product->get_id() );
				}
				?>

				<li class="job-package">
					<div class="package-button"></div>
					<div class="package-details">
						<input type="radio" <?php checked( $checked, 1 ); $checked = 0; ?> name="job_package" value="<?php echo esc_attr($product->get_id()); ?>" id="package-<?php echo esc_attr($product->get_id()); ?>" />
						<label for="package-<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->get_title() ); ?></label><br/>

						<div class="package-desc">
							<?php if ( ! empty( $post->post_excerpt ) ) : ?>
								<?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ); ?>
								<?php
							else :
								printf( _n( '%1$s for %2$s job', '%1$s for %2$s jobs', $product->get_limit(), 'cariera' ) . ' ', $product->get_price_html(), $product->get_limit() ? $product->get_limit() : esc_html__( 'unlimited', 'cariera' ) );
								echo esc_html( $product->get_duration() ) ? sprintf( _n( 'listed for %s day', 'listed for %s days', $product->get_duration(), 'cariera' ), $product->get_duration() ) : '';
							endif;
							?>
						</div>
					</div>

					<div class="package-footer">
						<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
						<span class="caption"><?php echo wp_kses_post( $product->get_duration() ) ? sprintf( _n( '%s days payment', '%s days payment', $product->get_duration(), 'cariera' ), $product->get_duration() ) : ''; ?></span>
					</div>

				</li>

			<?php endforeach; ?>
		<?php endif; ?>
	</ul>

	<span><?php esc_html_e( 'Select a package from above and submit.', 'cariera' ); ?></span>
<?php else : ?>

	<p><?php esc_html_e( 'No packages found', 'cariera' ); ?></p>

<?php endif; ?>

<?php do_action( 'cariera_job_submission_steps' ); ?>

<?php
/**
 * Cariera User Packages template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/user-packages.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.4
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-dashboards' );

$user_packages = wc_paid_listings_get_user_packages( get_current_user_id(), $package_type = '', $all = true );

// Package exists.
if ( ! empty( $user_packages ) ) { ?>
	<div class="table-responsive">
		<table class="cariera-wpjm-dashboard job-manager-job-reports job-manager-user-packages">
			<thead>
				<tr>
					<th class="package-order-id"><?php esc_html_e( 'Order ID', 'cariera' ); ?></th>
					<th class="package-title"><?php esc_html_e( 'Package', 'cariera' ); ?></th>
					<th class="package-count"><?php esc_html_e( 'Posted', 'cariera' ); ?></th>
					<th class="package-limit"><?php esc_html_e( 'Listing Limit', 'cariera' ); ?></th>
					<th class="package-duration"><?php esc_html_e( 'Listing Duration', 'cariera' ); ?></th>
					<th class="package-status"><?php esc_html_e( 'Status', 'cariera' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $user_packages as $package ) {
					$package  = wc_paid_listings_get_package( $package );
					$order_id = trim( $package->get_order_id() );
					$title    = trim( $package->get_title() );
					$count    = intval( $package->get_count() );
					$limit    = intval( $package->get_limit() );
					$duration = intval( $package->get_duration() );
					?>

					<tr>
						<td class="package-order-id"><?php echo esc_html( $order_id ); ?></td>
						<td class="package-title"><?php echo esc_html( $title ); ?></td>
						<td class="package-count"><?php echo esc_html( $count ); ?></td>
						<td class="package-limit"><?php echo ( $limit == '0' ) ? esc_html__( 'Unlimited', 'cariera' ) : esc_html( $limit ); ?></td>
						<td class="package-duration"><?php echo ( $duration == '0' ) ? esc_html__( 'Unlimited', 'cariera' ) : esc_html( $duration ); ?></td>
						<td class="package-status">
							<?php
							if ( $count >= $limit && $limit != 0 ) {
								echo '<span class="status used">' . esc_html__( 'Used', 'cariera' ) . '</span>';
							} else {
								echo '<span class="status active">' . esc_html__( 'Active', 'cariera' ) . '</span>';
							}
							?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
<?php } else { ?>
	<p class="job-manager-message generic">
		<?php esc_html_e( 'No packages have been bought with this account.', 'cariera' ); ?>
	</p>
	<?php
}

<?php
/**
 * Lists job listing alerts for the `[job_alerts]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-alerts/my-alerts.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Alerts
 * @category    Template
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="job-manager-alerts" class="jm-alerts__my-alerts">
	<div class="jm-alerts__my-alerts__email-info">
		<p><?php printf( __( 'Your job alerts are shown in the list below and will be emailed to %s.', 'wp-job-manager-alerts' ), $user->user_email ); ?></p>
	</div>
	<div class="jm-alerts__alert-list">
		<?php foreach ( $alerts as $alert ) : ?>
			<?php
			$search_terms = WP_Job_Manager_Alerts_Post_Types::get_alert_search_terms( $alert->ID );
			$disabled     = $alert->post_status == 'draft';
			?>
			<div class="jm-alert alert-<?php echo $disabled ? 'disabled' : 'enabled'; ?>">
				<div class="jm-alert__header">
					<h3 class="jm-alert__title"><?php echo esc_html( $alert->post_title ); ?></h3>

					<?php if ( $disabled ) : ?>
						<div class="jm-alert__disabled"><?php _e( 'Disabled', 'wp-job-manager-alerts' ); ?></div>
					<?php else: ?>

						<div class="jm-alert__frequency alert_frequency"><?php
							$schedules = WP_Job_Manager_Alerts_Notifier::get_alert_schedules();
							$freq      = get_post_meta( $alert->ID, 'alert_frequency', true );

							if ( ! empty( $schedules[ $freq ] ) ) {
								echo '<span class="jm-alert__frequency__schedule">' . esc_html( $schedules[ $freq ]['display'] ) . '</span>';
							}

							$next_scheduled = (int) wp_next_scheduled( 'job-manager-alert', array( $alert->ID ) ) + (int) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;
							$format         = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

							if ( ! empty( $next_scheduled ) ) {
								echo ' <span class="jm-alert__frequency__next">' . sprintf( __( '(Next: %s)', 'wp-job-manager-alerts' ), date_i18n( $format, $next_scheduled ) ) . '</span>';
							}

							?>
						</div>
					<?php endif; ?>
				</div>


				<?php
				$keyword = get_post_meta( $alert->ID, 'alert_keyword', true );
				if ( ! empty( $keyword ) ) : ?>
					<div class="jm-alert__terms alert_keyword">
						<span><?php esc_html_e( 'Keyword', 'wp-job-manager-alerts' ); ?>:</span>
						<span class="jm-alert__term"><?php echo esc_html( $keyword ); ?></span>
					</div>
				<?php endif; ?>

				<?php
				$term_rows = [
					'categories' => [
						'label'     => __( 'Category', 'wp-job-manager-alerts' ),
						'taxonomy'  => 'job_listing_category',
						'condition' => get_option( 'job_manager_enable_categories' ) && wp_count_terms( 'job_listing_category' ) > 0
					],
					'tags'       => [
						'label'     => __( 'Tags', 'wp-job-manager-alerts' ),
						'taxonomy'  => 'job_listing_tag',
						'condition' => taxonomy_exists( 'job_listing_tag' )
					],
					'types'      => [
						'label'     => __( 'Type', 'wp-job-manager-alerts' ),
						'taxonomy'  => 'job_listing_type',
						'condition' => get_option( 'job_manager_enable_types' ) && wp_count_terms( 'job_listing_types' ) > 0
					],
					'regions'    => [
						'label'     => __( 'Location', 'wp-job-manager-alerts' ),
						'taxonomy'  => 'job_listing_region',
						'condition' => taxonomy_exists( 'job_listing_region' ) && wp_count_terms( 'job_listing_region' ) > 0
					],
				];

				foreach ( $term_rows as $term => $row ) :
					$value = $search_terms[ $term ];
					if ( $row['condition'] && ! empty( $value ) ) :

						$terms = get_terms( array(
							'taxonomy'   => $row['taxonomy'],
							'fields'     => 'names',
							'include'    => $value,
							'hide_empty' => false,
						) );

						?>
						<div class="jm-alert__terms alert_<?php echo $term; ?>">
							<span class="jm-alert__term-label"><?php echo esc_html( $row['label'] ) ?>:</span>
							<span class="jm-alert__term-list"><?php foreach ( $terms as $i => $term_value ) : ?>
									<span class="jm-alert__term"><?php echo esc_html( $term_value ); ?></span><?php
									if ( array_key_last( $terms ) !== $i ) {
										echo '<span class="jm-alert__term-separator">, </span>';
									}
									?>
								<?php endforeach; ?>
							</span>
						</div>
					<?php
					endif;
				endforeach;
				?>


				<?php

				if ( ! $term_rows['regions']['condition'] ) :
					$location = get_post_meta( $alert->ID, 'alert_location', true );
					if ( $location ) : ?>

						<div class="jm-alert__terms alert_location">
							<span class="jm-alert__term-label"><?php esc_html_e( 'Location', 'wp-job-manager-alerts' ); ?>:</span>
							<span class="jm-alert__term"><?php echo esc_html( $location ) ?></span>
						</div>

					<?php
					endif;
				endif;
				?>

				<div class="jm-alert__actions job-alert-actions">
					<ul>
					<?php
					$actions = apply_filters( 'job_manager_alert_actions', array(
						'view'          => array(
							'label' => __( 'Results', 'wp-job-manager-alerts' ),
							'nonce' => false
						),
						'email'         => array(
							'label' => __( 'Send&nbsp;Now', 'wp-job-manager-alerts' ),
							'nonce' => true
						),
						'edit'          => array(
							'label' => __( 'Edit', 'wp-job-manager-alerts' ),
							'nonce' => false
						),
						'toggle_status' => array(
							'label' => $alert->post_status == 'draft' ? __( 'Enable', 'wp-job-manager-alerts' ) : __( 'Disable', 'wp-job-manager-alerts' ),
							'nonce' => true
						),
						'delete'        => array(
							'label' => __( 'Delete', 'wp-job-manager-alerts' ),
							'nonce' => true
						)
					), $alert );

					foreach ( $actions as $action => $value ) {
						$action_url = add_query_arg( array(
							'action'   => $action,
							'alert_id' => $alert->ID,
							'updated'  => null,
						) );

						if ( $value['nonce'] ) {
							$action_url = wp_nonce_url( $action_url, 'job_manager_alert_actions' );
						}

						echo '<li><a href="' . esc_url( $action_url ) . '" class="jm-alert__action job-alerts-action-' . esc_attr( $action ) . '">' . esc_html( $value['label'] ) . '</a></li>';
					}
					?>
					</ul>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="jm-alerts__add-new">
			<a href="<?php echo esc_url( add_query_arg( ['action' => 'add_alert', 'updated' => null ] ) ); ?>"><?php _e( 'Add alert', 'wp-job-manager-alerts' ); ?></a>
		</div>
	</div>
</div>

<?php
/**
 * Lists job listing alerts for the `[resume_alerts]` shortcode.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resume-alerts/my-alerts.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Alerts
 * @category    Template
 * @version     1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-dashboards' );
?>

<div id="job-manager-resume-alerts">
	<p><?php printf( esc_html__( 'Your resume alerts are shown in the table below and will be emailed to %s.', 'cariera' ), $user->user_email ); ?></p>

	<div class="table-responsive">
		<table class="cariera-wpjm-dashboard job-manager-resume-alerts">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Alert Name', 'cariera' ); ?></th>
					<th><?php esc_html_e( 'Keywords', 'cariera' ); ?></th>
					<?php if ( get_option( 'resume_manager_enable_categories' ) && wp_count_terms( 'resume_category' ) > 0 ) : ?>
						<th><?php esc_html_e( 'Categories', 'cariera' ); ?></th>
					<?php endif; ?>
					<?php if ( get_option( 'resume_manager_enable_skills' ) && wp_count_terms( 'resume_skill' ) > 0 ) : ?>
						<th><?php esc_html_e( 'Skills', 'cariera' ); ?></th>
					<?php endif; ?>
					<?php if ( taxonomy_exists( 'resume_region' ) && wp_count_terms( 'resume_region' ) > 0 ) : ?>
						<th><?php esc_html_e( 'Location', 'cariera' ); ?></th>
					<?php endif; ?>
					<th><?php esc_html_e( 'Frequency', 'cariera' ); ?></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ( $alerts as $alert ) : ?>
					<?php
					$search_terms = WP_Job_Manager_Resume_Alerts_Post_Types::get_alert_search_terms( $alert->ID );
					?>
					<tr class="alert-<?php echo $alert->post_status === 'draft' ? 'disabled' : 'enabled'; ?>">
						<td class="title"><?php echo esc_html( $alert->post_title ); ?></td>
						<td class="alert_keyword">
						<?php
						if ( $value = get_post_meta( $alert->ID, 'alert_keyword', true ) ) {
							echo esc_html( $value );
						} else {
							echo '&ndash;';
						}
						?>
						</td>
						<?php if ( get_option( 'resume_manager_enable_categories' ) && wp_count_terms( 'resume_category' ) > 0 ) : ?>
							<td class="alert_category">
							<?php
								$term_ids = ! empty( $search_terms['categories'] ) ? $search_terms['categories'] : [];
								$terms    = [];
							if ( ! empty( $term_ids ) ) {
								$terms = get_terms(
									[
										'taxonomy'   => 'resume_category',
										'fields'     => 'names',
										'include'    => $term_ids,
										'hide_empty' => false,
									]
								);
							}
								echo $terms ? esc_html( implode( ', ', $terms ) ) : '&ndash;';
							?>
							</td>
						<?php endif; ?>
						<?php if ( get_option( 'resume_manager_enable_skills' ) && wp_count_terms( 'resume_skill' ) > 0 ) : ?>
							<td class="alert_resume_skill">
							<?php
								$term_ids = ! empty( $search_terms['skills'] ) ? $search_terms['skills'] : [];
								$terms    = [];
							if ( ! empty( $term_ids ) ) {
								$terms = get_terms(
									[
										'taxonomy'   => 'resume_skill',
										'fields'     => 'names',
										'include'    => $term_ids,
										'hide_empty' => false,
									]
								);
							}
								echo $terms ? esc_html( implode( ', ', $terms ) ) : '&ndash;';
							?>
								</td>
						<?php endif; ?>
						<?php if ( taxonomy_exists( 'resume_region' ) && wp_count_terms( 'resume_region' ) > 0 ) : ?>
						<td class="alert_location">
							<?php
							if ( taxonomy_exists( 'resume_region' ) && wp_count_terms( 'resume_region' ) > 0 ) {
								$term_ids = ! empty( $search_terms['regions'] ) ? $search_terms['regions'] : [];
								$terms    = [];
								if ( ! empty( $term_ids ) ) {
									$terms = get_terms(
										[
											'taxonomy'   => 'resume_region',
											'fields'     => 'names',
											'include'    => $term_ids,
											'hide_empty' => false,
										]
									);
								}
								echo $terms ? esc_html( implode( ', ', $terms ) ) : '&ndash;';
							} else {
								$value = get_post_meta( $alert->ID, 'alert_location', true );
								echo $value ? esc_html( $value ) : '&ndash;';
							}
							?>
						</td>
						<?php endif; ?>
						<td class="alert_frequency">
						<?php
							$schedules = WP_Job_Manager_Resume_Alerts_Notifier::get_alert_schedules();
							$freq      = get_post_meta( $alert->ID, 'alert_frequency', true );

						if ( ! empty( $schedules[ $freq ] ) ) {
							echo esc_html( $schedules[ $freq ]['display'] );
						}

							echo '<small>' . sprintf( esc_html__( 'Next: %1$s at %2$s', 'cariera' ), date_i18n( get_option( 'date_format' ), wp_next_scheduled( 'job-manager-resume-alert', [ $alert->ID ] ) ), date_i18n( get_option( 'time_format' ), wp_next_scheduled( 'job-manager-resume-alert', [ $alert->ID ] ) ) ) . '</small>';
						?>
						</td>

						<td class="action">
							<ul class="resume-alert-actions">
								<?php
									$actions = apply_filters(
										'job_manager_alert_actions',
										[
											'view'   => [
												'label' => esc_html__( 'Results', 'cariera' ),
												'nonce' => false,
											],
											'email'  => [
												'label' => esc_html__( 'Send&nbsp;Now', 'cariera' ),
												'nonce' => true,
											],
											'edit'   => [
												'label' => esc_html__( 'Edit', 'cariera' ),
												'nonce' => false,
											],
											'toggle_status' => [
												'label' => $alert->post_status == 'draft' ? esc_html__( 'Enable', 'cariera' ) : esc_html__( 'Disable', 'cariera' ),
												'nonce' => true,
											],
											'delete' => [
												'label' => esc_html__( 'Delete', 'cariera' ),
												'nonce' => true,
											],
										],
										$alert
									);

								foreach ( $actions as $action => $value ) {
									$action_url = remove_query_arg(
										'updated',
										add_query_arg(
											[
												'action'   => $action,
												'alert_id' => $alert->ID,
											]
										)
									);

									if ( $value['nonce'] ) {
										$action_url = wp_nonce_url( $action_url, 'job_manager_alert_actions' );
									}

									echo '<li><a href="' . $action_url . '" class="resume-alerts-action-' . $action . '">' . $value['label'] . '</a></li>';
								}
								?>
							</ul>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<a href="<?php echo remove_query_arg( 'updated', add_query_arg( 'action', 'add_alert' ) ); ?>" class="btn btn-main btn-effect mt30"><?php esc_html_e( 'Add alert', 'cariera' ); ?></a>
</div>

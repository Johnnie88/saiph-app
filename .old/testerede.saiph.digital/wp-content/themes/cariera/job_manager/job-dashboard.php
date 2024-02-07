<?php
/**
 * Job dashboard shortcode content.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.41.0
 *
 * @since 1.34.4 Available job actions are passed in an array (`$job_actions`, keyed by job ID) and not generated in the template.
 * @since 1.35.0 Switched to new date functions.
 *
 * @var array     $job_dashboard_columns Array of the columns to show on the job dashboard page.
 * @var int       $max_num_pages         Maximum number of pages
 * @var WP_Post[] $jobs                  Array of job post results.
 * @var array     $job_actions           Array of actions available for each job.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_style( 'cariera-wpjm-dashboards' );

$submission_limit        = ! empty( get_option( 'job_manager_submission_limit' ) ) ? absint( get_option( 'job_manager_submission_limit' ) ) : false;
$submit_job_form_page_id = get_option( 'job_manager_submit_job_form_page_id' );
?>

<div id="job-manager-job-dashboard">
	<p><?php esc_html_e( 'Your listings are shown in the table below.', 'cariera' ); ?></p>

	<div class="table-responsive">
		<table class="cariera-wpjm-dashboard job-manager-jobs">
			<thead>
				<tr>
					<?php foreach ( $job_dashboard_columns as $key => $column ) : ?>
						<th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
					<?php endforeach; ?>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php if ( ! $jobs ) : ?>
					<tr>
						<td colspan="<?php echo intval( count( $job_dashboard_columns ) + 1 ); ?>"><?php esc_html_e( 'You do not have any active listings.', 'cariera' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $jobs as $job ) : ?>
						<tr>
							<?php foreach ( $job_dashboard_columns as $key => $column ) : ?>
								<td class="<?php echo esc_attr( $key ); ?>">
									<?php if ( 'job_title' === $key ) : ?>
										<?php if ( $job->post_status == 'publish' ) : ?>
											<a href="<?php echo esc_url( get_permalink( $job->ID ) ); ?>"><?php wpjm_the_job_title( $job ); ?></a>
											<?php if ( WP_Job_Manager_Helper_Renewals::job_can_be_renewed( $job ) ) : ?>
												<small>(<?php esc_html_e( 'Expires soon', 'cariera' ); ?>)</small>
											<?php endif; ?>
										<?php else : ?>
											<?php wpjm_the_job_title( $job ); ?> <small>(<?php the_job_status( $job ); ?>)</small>
										<?php endif; ?>
										<?php echo is_position_featured( $job ) ? '<span class="fas fa-star pl5" title="' . esc_attr__( 'Featured Job', 'cariera' ) . '"></span>' : ''; ?>

										<!-- =================== Start of Job's company =================== -->
										<?php
										$company = '';

										if ( \Cariera\cariera_core_is_activated() && ! cariera_get_the_company() && get_option( 'cariera_company_manager_integration', false ) ) {
											$company_id = get_post_meta( $job->ID, '_company_manager_id', true );
											if ( ! empty( $company_id ) ) {
												$company = get_post( $company_id );
											}
										}

										if ( ! empty( $company ) ) {
											?>
											<p class="job-company"><i class="far fa-building"></i><?php echo get_the_title( $company ); ?></p>
										<?php } ?>
										<!-- =================== End of Job's company =================== -->

									<?php elseif ( 'date' === $key ) : ?>
										<?php echo esc_html( wp_date( get_option( 'date_format' ), get_post_datetime( $job )->getTimestamp() ) ); ?>
									<?php elseif ( 'expires' === $key ) : ?>
										<?php
										$job_expires = WP_Job_Manager_Post_Types::instance()->get_job_expiration( $job );
										echo esc_html( $job_expires ? wp_date( get_option( 'date_format' ), $job_expires->getTimestamp() ) : '&ndash;' );
										?>
									<?php elseif ( 'filled' === $key ) : ?>
										<?php echo is_position_filled( $job ) ? '&#10004;' : '&ndash;'; ?>
									<?php elseif ( 'applications' === $key ) : ?>
										<?php
										global $post;
										if ( $count = get_job_application_count( $job->ID ) ) {
											echo '<a class="btn btn-main btn-effect" href="' . add_query_arg(
												[
													'action' => 'show_applications',
													'job_id' => $job->ID,
												],
												esc_url( get_permalink( get_option( 'job_manager_job_dashboard_page_id' ) ) )
											) . '">' . esc_html__( 'Show', 'cariera' ) . ' (' . $count . ')</a>';
										} else {
											echo esc_html( '&ndash;' );
										}
										?>
									<?php else : ?>
										<?php do_action( 'job_manager_job_dashboard_column_' . $key, $job ); ?>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
							<td class="action">
								<?php do_action( 'cariera_job_dashboard_action_start', $job->ID ); ?>
								<?php
								if ( ! empty( $job_actions[ $job->ID ] ) ) {
									foreach ( $job_actions[ $job->ID ] as $action => $value ) {
										$action_url = add_query_arg(
											[
												'action' => $action,
												'job_id' => $job->ID,
											]
										);
										if ( $value['nonce'] ) {
											$action_url = wp_nonce_url( $action_url, $value['nonce'] );
										}
										echo '<a href="' . esc_url( $action_url ) . '" class="job-dashboard-action-' . esc_attr( $action ) . '">' . esc_html( $value['label'] ) . '</a>';
									}
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $max_num_pages ] ); ?>

	<?php if ( job_manager_user_can_submit_job_listing() ) : ?>
		<a href="<?php echo esc_url( get_permalink( $submit_job_form_page_id ) ); ?>" class="btn btn-main btn-effect mt20"><?php esc_html_e( 'Add Job', 'cariera' ); ?></a>
	<?php endif; ?>
</div>

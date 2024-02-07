<?php
/**
 * Template for the candidate dashboard (`[candidate_dashboard]`) shortcode.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/candidate-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Resume Manager
 * @category    Template
 * @version     1.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-dashboards' );

$submission_limit           = get_option( 'resume_manager_submission_limit' );
$submit_resume_form_page_id = get_option( 'resume_manager_submit_resume_form_page_id' );
?>

<div id="resume-manager-candidate-dashboard">
	<p><?php echo esc_html( _n( 'Your resume can be viewed, edited or removed below.', 'Your resume(s) can be viewed, edited or removed below.', resume_manager_count_user_resumes(), 'cariera' ) ); ?></p>

	<div class="table-responsive">
		<table class="cariera-wpjm-dashboard resume-manager-resumes">
			<thead>
				<tr>
					<?php foreach ( $candidate_dashboard_columns as $key => $column ) : ?>
						<th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
					<?php endforeach; ?>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php if ( ! $resumes ) : ?>
					<tr>
						<td colspan="<?php echo intval( count( $candidate_dashboard_columns ) + 1 ); ?>"><?php esc_html_e( 'You do not have any active resume listings.', 'cariera' ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $resumes as $resume ) : ?>
						<tr>
							<?php foreach ( $candidate_dashboard_columns as $key => $column ) : ?>
								<td class="<?php echo esc_attr( $key ); ?>">
									<?php if ( 'resume-title' === $key ) : ?>
										<?php if ( $resume->post_status == 'publish' ) : ?>
											<a href="<?php echo esc_url( get_permalink( $resume->ID ) ); ?>"><?php echo esc_html( $resume->post_title ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $resume->post_title ); ?> <small>(<?php the_resume_status( $resume ); ?>)</small>
										<?php endif; ?>

										<?php echo is_position_featured( $resume ) ? '<span class="fas fa-star pl5" title="' . esc_attr__( 'Featured Resume', 'cariera' ) . '"></span>' : ''; ?>

									<?php elseif ( 'candidate-title' === $key ) : ?>
										<?php the_candidate_title( '', '', true, $resume ); ?>
									<?php elseif ( 'candidate-location' === $key ) : ?>
										<?php the_candidate_location( false, $resume ); ?></td>
									<?php elseif ( 'resume-category' === $key ) : ?>
										<?php the_resume_category( $resume ); ?>
									<?php elseif ( 'status' === $key ) : ?>
										<?php the_resume_status( $resume ); ?>
									<?php elseif ( 'date' === $key ) : ?>
										<?php
										if ( ! empty( $resume->_resume_expires ) && strtotime( $resume->_resume_expires ) > current_time( 'timestamp' ) ) {
											printf( esc_html__( 'Expires %s', 'cariera' ), date_i18n( get_option( 'date_format' ), strtotime( $resume->_resume_expires ) ) );
										} else {
											echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $resume->post_date ) ) );
										}
										?>
									<?php else : ?>
										<?php do_action( 'resume_manager_candidate_dashboard_column_' . $key, $resume ); ?>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>

							<td class="action">
								<?php do_action( 'cariera_resume_dashboard_action_start', $resume->ID ); ?>
								<?php
								$actions = [];

								switch ( $resume->post_status ) {
									case 'publish':
										if ( resume_manager_user_can_edit_published_submissions() ) {
											$actions['edit'] = [
												'label' => esc_html__( 'Edit', 'cariera' ),
												'nonce' => false,
											];
										}
										$actions['hide'] = [
											'label' => esc_html__( 'Hide', 'cariera' ),
											'nonce' => true,
										];
										break;
									case 'hidden':
										if ( resume_manager_user_can_edit_published_submissions() ) {
											$actions['edit'] = [
												'label' => esc_html__( 'Edit', 'cariera' ),
												'nonce' => false,
											];
										}
										$actions['publish'] = [
											'label' => esc_html__( 'Publish', 'cariera' ),
											'nonce' => true,
										];
										break;
									case 'pending_payment':
									case 'pending':
										if ( resume_manager_user_can_edit_pending_submissions() ) {
											$actions['edit'] = [
												'label' => esc_html__( 'Edit', 'cariera' ),
												'nonce' => false,
											];
										}
										break;
									case 'expired':
										if ( get_option( 'resume_manager_submit_resume_form_page_id' ) ) {
											$actions['relist'] = [
												'label' => esc_html__( 'Relist', 'cariera' ),
												'nonce' => true,
											];
										}
										break;
								}

								$actions['delete'] = [
									'label' => esc_html__( 'Delete', 'cariera' ),
									'nonce' => true,
								];

								$actions = apply_filters( 'resume_manager_my_resume_actions', $actions, $resume );

								foreach ( $actions as $action => $value ) {
									$action_url = add_query_arg(
										[
											'action'    => $action,
											'resume_id' => $resume->ID,
										]
									);
									if ( $value['nonce'] ) {
										$action_url = wp_nonce_url( $action_url, 'resume_manager_my_resume_actions' );
									}
									echo '<a href="' . $action_url . '" class="candidate-dashboard-action-' . $action . '">' . $value['label'] . '</a>';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php if ( $submit_resume_form_page_id && ( resume_manager_count_user_resumes() < $submission_limit || ! $submission_limit ) ) : ?>
		<a href="<?php echo esc_url( get_permalink( $submit_resume_form_page_id ) ); ?>" class="btn btn-main btn-effect mt30"><?php esc_html_e( 'Add Resume', 'cariera' ); ?></a>
	<?php endif; ?>

	<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $max_num_pages ] ); ?>
</div>

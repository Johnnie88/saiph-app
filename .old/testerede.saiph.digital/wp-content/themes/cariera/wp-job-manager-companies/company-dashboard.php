<?php
/**
 * Custom: Company - Company Dashboard
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.4
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_style( 'cariera-wpjm-dashboards' );

$submission_limit    = get_option( 'cariera_company_submission_limit' );
$submit_company_page = get_option( 'cariera_submit_company_page' );
$singular            = cariera_get_company_manager_singular_label();
$plural              = cariera_get_company_manager_plural_label();
$total_companies     = cariera_count_user_companies(); ?>

<div id="company-manager-company-dashboard">
	<p><?php printf( esc_html__( 'Your %s can be viewed, edited or removed below.', 'cariera' ), $total_companies > 1 ? $plural : $singular ); ?></p>

	<div class="table-responsive">
		<table class="cariera-wpjm-dashboard company-manager-companies">
			<thead>
				<tr>
					<?php foreach ( $company_dashboard_columns as $key => $column ) : ?>
						<th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
					<?php endforeach; ?>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php if ( ! $companies ) : ?>
					<tr>
						<td colspan="6"><?php printf( esc_html__( 'You do not have any active %s listings.', 'cariera' ), $singular ); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ( $companies as $company ) : ?>
						<tr>
							<?php foreach ( $company_dashboard_columns as $key => $column ) : ?>
								<td class="<?php echo esc_attr( $key ); ?>">
									<?php if ( 'company-name' === $key ) : ?>
										<?php if ( $company->post_status == 'publish' ) : ?>
											<a href="<?php echo esc_url( get_permalink( $company->ID ) ); ?>"><?php echo esc_html( $company->post_title ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $company->post_title ); ?> <small>(<?php echo cariera_company_status( $company ); ?>)</small>
										<?php endif; ?>
										<?php echo cariera_is_company_featured( $company ) ? '<span class="fas fa-star pl5" title="' . esc_attr__( 'Featured Job', 'cariera' ) . '"></span>' : ''; ?>

									<?php elseif ( 'company-location' === $key ) : ?>
										<?php echo cariera_get_the_company_location( $company ); ?></td>
									<?php elseif ( 'company-category' === $key ) : ?>
										<?php cariera_the_company_category_array( $company ); ?>
									<?php elseif ( 'status' === $key ) : ?>
										<?php echo cariera_get_company_status( $company ); ?>
									<?php elseif ( 'company-jobs' === $key ) : ?>
										<?php echo cariera_get_the_company_job_listing_active_count( $company->ID ); ?>
									<?php elseif ( 'date' === $key ) : ?>
										<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $company->post_date ) ) ); ?>
									<?php else : ?>
										<?php do_action( 'cariera_company_dashboard_column_' . $key, $company ); ?>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>

							<td class="action">
								<?php
								do_action( 'cariera_company_dashboard_action_start', $company->ID );

								if ( ! empty( $company_actions[ $company->ID ] ) ) {
									foreach ( $company_actions[ $company->ID ] as $action => $value ) {
										$action_url = add_query_arg(
											[
												'action' => $action,
												'company_id' => $company->ID,
											]
										);
										if ( $value['nonce'] ) {
											$action_url = wp_nonce_url( $action_url, $value['nonce'] );
										}
										echo '<a href="' . esc_url( $action_url ) . '" class="company-dashboard-action-' . esc_attr( $action ) . '">' . esc_html( $value['label'] ) . '</a>';
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

	<?php
	if ( $submit_company_page && ( $total_companies < $submission_limit || ! $submission_limit ) ) {
		?>
		<a href="<?php echo esc_url( get_permalink( $submit_company_page ) ); ?>" class="btn btn-main btn-effect mt20"><?php esc_html_e( 'Add Company', 'cariera' ); ?></a>
	<?php } ?>
</div>

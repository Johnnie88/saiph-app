<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adding data to the Database for the external redirections
 *
 * @since  1.3.8
 */
if ( ! function_exists( 'cariera_set_this_external_job' ) ) {
	function cariera_set_this_external_job( $listing_id ) {

		$table = 'cariera_job_external_redirection';

		$listing_title = get_the_title( $listing_id );
		$allCounts     = '';

		// Check if already have.
		$condition = "listing_id ='$listing_id'";
		$data      = cariera_get_data_from_db( $table, '*', $condition );

		// Already exists.
		if ( ! empty( $data ) ) {
			foreach ( $data as $index => $val ) {
				$count     = $val->count;
				$allCounts = $count + 1;

				$where = [
					'listing_id' => $listing_id,
				];

				$dataArray = [
					'count' => $allCounts,
				];

				cariera_update_data_in_db( $table, $dataArray, $where );
			}
		} else {
			// New record.
			$log_record = [
				[
					'count' => 1,
				],
			];
			$log_record = serialize( $log_record );

			$dataArray = [
				'listing_id'    => $listing_id,
				'listing_title' => $listing_title,
				'count'         => 1,
			];

			cariera_insert_data_in_db( $table, $dataArray );
		}

	}
}

/**
 * Update External Redirection when job redirects the user
 *
 * @since  1.3.8
 */
function cariera_external_job_application() {

	// Get Job ID from the form.
	$listing_id = absint( $_POST['id'] );

	$table = 'cariera_job_external_redirection';
	$where = [ 'listing_id' => $listing_id ];
	cariera_update_data_in_db( $table, '*', $where );

	cariera_set_this_external_job( $listing_id );

	echo wp_json_encode(
		[
			'message' => esc_html__( 'Redirected to external job application link.', 'cariera' ),
		]
	);

	die();
}

add_action( 'wp_ajax_cariera_external_job_application_ajax', 'cariera_external_job_application' );
add_action( 'wp_ajax_nopriv_cariera_external_job_application_ajax', 'cariera_external_job_application' );

/**
 * Employer Reports
 *
 * @since   1.3.8
 * @version 1.7.0
 */
function cariera_employer_reports() {

	// Stop the function if the WPJM plugin is not activated.
	if ( ! class_exists( 'WP_Job_Manager' ) ) {
		return;
	}

	wp_enqueue_style( 'cariera-wpjm-dashboards' );

	$args = apply_filters(
		'cariera_get_job_report_args',
		[
			'post_type'           => 'job_listing',
			'post_status'         => [ 'publish', 'expired' ],
			'posts_per_page'      => 10,
			'paged'               => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
			'ignore_sticky_posts' => 1,
			'orderby'             => 'date',
			'order'               => 'desc',
			'author'              => get_current_user_id(),
		]
	);

	$wp_query = new WP_Query();
	$jobs     = $wp_query->query( $args ); ?>


	<div class="job-reports">
		<h3 class="title"><?php esc_html_e( 'My Job Reports:', 'cariera' ); ?></h3>

		<div class="table-responsive">
			<table class="cariera-wpjm-dashboard job-manager-job-reports">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Jobs', 'cariera' ); ?></th>
						<th><?php esc_html_e( 'Posted Date', 'cariera' ); ?></th>
						<th><?php esc_html_e( 'Views', 'cariera' ); ?></th>
						<th><?php esc_html_e( 'External Clicks', 'cariera' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php if ( ! $jobs ) { ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'You do not have any active listings.', 'cariera' ); ?></td>
						</tr>
						<?php
					} else {
						foreach ( $jobs as $job ) {
							?>
							<tr>
								<!-- Job Title -->
								<td>
									<a href="<?php echo esc_url( get_permalink( $job->ID ) ); ?>">
										<?php echo esc_html( $job->post_title ); ?>
									</a>
								</td>

								<!-- Job Date Posted -->
								<td>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $job->post_date ) ) ); ?>
								</td>

								<!-- Job views -->
								<td>
									<?php
									// LISTING VIEWS.
									$table     = 'cariera_listing_stats_views';
									$condition = "listing_id='$job->ID'";
									$data      = cariera_get_data_from_db( $table, 'count', $condition );

									if ( ! empty( $data ) ) {
										foreach ( $data as $index => $val ) {
											$count = $val->count;
											echo esc_html( $count );
										}
									} else {
										echo esc_html( '0' );
									}
									?>
								</td>

								<!-- Job External click redirections -->
								<td>
									<?php
									$table     = 'cariera_job_external_redirection';
									$condition = "listing_id='$job->ID'";
									$data      = cariera_get_data_from_db( $table, 'count', $condition );

									if ( ! empty( $data ) ) {
										foreach ( $data as $index => $val ) {
											$count = $val->count;
											echo esc_html( $count );
										}
									} else {
										echo esc_html( '0' );
									}
									?>
								</td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>

		<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $wp_query->max_num_pages ] ); ?>
	</div>
	<?php
}

/**
 * Candidate Reports
 *
 * @since   1.3.8
 * @version 1.7.0
 */
function cariera_candidate_reports() {

	// Stop the function if the WPRM plugin is not activated.
	if ( ! class_exists( 'WP_Resume_Manager' ) ) {
		return;
	}

	wp_enqueue_style( 'cariera-wpjm-dashboards' );

	$args = apply_filters(
		'cariera_get_resume_report_args',
		[
			'post_type'           => 'resume',
			'post_status'         => [ 'publish' ],
			'posts_per_page'      => 10,
			'paged'               => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
			'ignore_sticky_posts' => 1,
			'orderby'             => 'date',
			'order'               => 'desc',
			'author'              => get_current_user_id(),
		]
	);

	$wp_query = new WP_Query();
	$resumes  = $wp_query->query( $args );
	?>

	<div class="resume-reports">
		<h3 class="title"><?php esc_html_e( 'My Resume Reports:', 'cariera' ); ?></h3>

		<div class="table-responsive">
			<table class="cariera-wpjm-dashboard resume-manager-resume-reports">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Resumes', 'cariera' ); ?></th>
						<th><?php esc_html_e( 'Posted Date', 'cariera' ); ?></th>
						<th><?php esc_html_e( 'Views', 'cariera' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php if ( ! $resumes ) { ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'You do not have any active listings.', 'cariera' ); ?></td>
						</tr>
						<?php
					} else {
						foreach ( $resumes as $resume ) {
							?>
							<tr>
								<!-- Resume Title -->
								<td>
									<a href="<?php echo esc_url( get_permalink( $resume->ID ) ); ?>">
										<?php echo esc_html( $resume->post_title ); ?>
									</a>
								</td>

								<!-- Resume Date Posted -->
								<td>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $resume->post_date ) ) ); ?>
								</td>

								<!-- Resume views -->
								<td>
									<?php
									// LISTING VIEWS.
									$table     = 'cariera_listing_stats_views';
									$condition = "listing_id='$resume->ID'";
									$data      = cariera_get_data_from_db( $table, 'count', $condition );

									if ( ! empty( $data ) ) {
										foreach ( $data as $index => $val ) {
											$count = $val->count;
											echo esc_html( $count );
										}
									} else {
										echo esc_html( '0' );
									}
									?>
								</td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>

		<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $wp_query->max_num_pages ] ); ?>        
	</div>
	<?php
}

/**
 * Reports shortcode
 * Usage: [cariera_listing_reports]
 *
 * @since   1.3.8
 * @version 1.6.7
 */
if ( ! function_exists( 'cariera_listing_reports' ) ) {
	function cariera_listing_reports() {
		global $wp_roles;
		$current_user                = wp_get_current_user();
		$user_id                     = $current_user->ID;
		$login_registration          = get_option( 'cariera_login_register_layout' );
		$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
		$login_registration_page_url = get_permalink( $login_registration_page );

		if ( ! is_user_logged_in() ) {
			?>
			<div class="user-listing-report">
				<p class="account-sign-in"><?php esc_html_e( 'You need to be signed in to access your listing reports.', 'cariera' ); ?></p>
				<a class="btn btn-main btn-effect <?php echo 'popup' === $login_registration ? 'popup-with-zoom-anim' : ''; ?>" href="<?php echo 'popup' === $login_registration ? '#login-register-popup' : esc_url( $login_registration_page_url ); ?>"><?php esc_html_e( 'Sign in', 'cariera' ); ?></a>
			</div>
		<?php } else { ?>
			<div class="row mt50">
				<div class="col-md-12 dashboard-content-reports">
					<div class="dashboard-card-box">
						<h4 class="title"><?php esc_html_e( 'Listing Reports', 'cariera' ); ?></h4>

						<div class="dashboard-card-box-inner report-wrapper">
							<?php
							if ( in_array( 'administrator', (array) $current_user->roles, true ) ) {
								cariera_employer_reports();
								cariera_candidate_reports();
							} elseif ( in_array( 'employer', (array) $current_user->roles, true ) ) {
								cariera_employer_reports();
							} elseif ( in_array( 'candidate', (array) $current_user->roles, true ) ) {
								cariera_candidate_reports();
							} else {
								return;
							}

							// Show message if none of the require plugins are activated.
							if ( ! class_exists( 'WP_Job_Manager' ) && ! class_exists( 'WP_Resume_Manager' ) ) {
								?>
								<p class="job-manager-message error">
									<?php esc_html_e( 'Please activate at least WP Job Manager Plugin in order to access your listing reports.', 'cariera' ); ?>
								</p>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}

add_shortcode( 'cariera_listing_reports', 'cariera_listing_reports' );

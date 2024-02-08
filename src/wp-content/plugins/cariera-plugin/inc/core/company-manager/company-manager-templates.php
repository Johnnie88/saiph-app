<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add post classes to companies
 *
 * @since 1.4.5
 */
if ( ! function_exists( 'cariera_company_add_post_class' ) ) {
	function cariera_company_add_post_class( $classes, $class, $post_id ) {
		$post = get_post( $post_id );

		if ( empty( $post ) || 'company' !== $post->post_type ) {
			return $classes;
		}

		$classes[] = 'company';

		if ( cariera_is_company_featured( $post ) ) {
			$classes[] = 'company_featured';
		}

		return $classes;
	}
}

add_action( 'post_class', 'cariera_company_add_post_class', 10, 3 );

/**
 * Displays some content when no results are found
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_output_company_no_results' ) ) {
	function cariera_output_company_no_results() {
		get_company_template( 'content-no-companies-found.php' );
	}
}

add_action( 'cariera_company_no_results', 'cariera_output_company_no_results' );

/*
==================================================================================
		SINGLE COMPANY PAGE
==================================================================================
*/

/**
 * Single Company Description
 *
 * @since   1.3.0
 * @version 1.5.0
 */
if ( ! function_exists( 'cariera_company_description' ) ) {
	function cariera_company_description() {
		if ( empty( get_the_content() ) ) {
			return;
		}
		?>

		<div id="company-description" class="company-description">
			<h5><?php esc_html_e( 'About the Company', 'cariera' ); ?></h5>
			<?php
			the_content();
			wp_link_pages(
				[
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'cariera' ),
					'after'  => '</div>',
				]
			);

			do_action( 'cariera_the_company_description' );
			?>
		</div>
		<?php
	}
}

add_action( 'cariera_single_company_listing', 'cariera_company_description', 10 );

/**
 * Single Company Video
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_company_video' ) ) {
	function cariera_company_video() {
		cariera_the_company_video_output();
	}
}

add_action( 'cariera_single_company_listing', 'cariera_company_video', 20 );

/**
 * Adding Share buttons to Single Job Listing
 *
 * @since 1.4.6
 */
if ( ! function_exists( 'cariera_single_company_share' ) ) {
	function cariera_single_company_share() {
		if ( cariera_get_option( 'cariera_company_share' ) ) {
			// Check if function exists.
			if ( function_exists( 'cariera_share_media' ) ) {
				echo cariera_share_media();
			}
		}
	}
}

add_action( 'cariera_single_company_listing', 'cariera_single_company_share', 30 );

/**
 * Single Company Job Listings
 *
 * @since   1.3.0
 * @version 1.5.5
 */
function cariera_company_job_listing() {
	if ( ! get_option( 'cariera_single_company_active_jobs' ) ) {
		return;
	}

	get_job_manager_template_part( 'content', 'company-job-listings', 'wp-job-manager-companies' );
}

add_action( 'cariera_single_company_listing', 'cariera_company_job_listing', 40 );

/**
 * Company Submission Flow
 *
 * @since   1.4.4
 * @version 1.6.4
 */
function cariera_company_submission_flow() {

	// Get page IDs.
	$current_page_id         = get_queried_object_id();
	$company_submission_page = apply_filters( 'cariera_dashboard_company_submit_page', get_option( 'cariera_submit_company_page', false ) );

	// Display submission flow.
	if ( ! empty( $company_submission_page ) && ( absint( $company_submission_page ) === $current_page_id ) ) {
		?>

		<div class="submission-flow company-submission-flow">
			<ul>
				<li class="listing-details"><?php esc_html_e( 'Company Details', 'cariera' ); ?></li>
				<li class="preview-listing"><?php esc_html_e( 'Preview Company', 'cariera' ); ?></li>
			</ul>
		</div>

		<?php
	}
}

add_action( 'cariera_company_submission_steps', 'cariera_company_submission_flow' );

/**
 * Adding Company Overview to the sidebar
 *
 * @since   1.5.5
 * @version 1.5.5
 */
function cariera_single_company_sidebar_overview() {
	get_job_manager_template_part( 'single-company/single', 'company-overview', 'wp-job-manager-companies' );
}

add_action( 'cariera_single_company_sidebar', 'cariera_single_company_sidebar_overview', 10 );

/**
 * Adding Map to the company sidebar
 *
 * @since   1.5.5
 * @version 1.5.5
 */
function cariera_single_company_sidebar_map() {
	echo '<aside class="mt40">';
	cariera_company_map();
	echo '</aside>';
}

add_action( 'cariera_single_company_sidebar', 'cariera_single_company_sidebar_map', 20 );

/**
 * Adding Sidebar widget area to the company sidebar
 *
 * @since   1.5.5
 * @version 1.5.5
 */
function cariera_single_company_sidebar() {
	dynamic_sidebar( 'sidebar-single-company' );
}

add_action( 'cariera_single_company_sidebar', 'cariera_single_company_sidebar', 30 );

/**
 * Edit single company button
 *
 * @since   1.5.5
 * @version 1.6.2
 */
function cariera_edit_single_company() {
	global $post, $company_preview;

	if ( $company_preview ) {
		return;
	}

	if ( ! cariera_user_can_edit_company( $post->ID ) ) {
		return;
	}

	$dashboard_id = apply_filters( 'cariera_edit_single_company_dashboard_id', get_option( 'cariera_company_dashboard_page' ) );

	$edit_link = add_query_arg(
		[
			'action'     => 'edit',
			'company_id' => $post->ID,
		],
		get_permalink( $dashboard_id )
	);
	?>

	<a href="<?php echo esc_url( $edit_link ); ?>" class="edit-listing btn-main"><?php esc_html_e( 'Edit Company', 'cariera' ); ?></a>
	<?php
}

add_action( 'cariera_single_company_after', 'cariera_edit_single_company', 21 );

/**
 * Adding Print button to Single Resume
 *
 * @since   1.7.1
 * @version 1.7.1
 */
function cariera_single_company_print() {
	get_job_manager_template_part( 'single-company/single', 'company-print', 'wp-job-manager-companies' );
}

add_action( 'cariera_single_company_listing', 'cariera_single_company_print', 31 );


/*
=====================================================
	SINGLE COMPANY PAGE V.2
=====================================================
*/

/**
 * Adding Company overview to the single page
 *
 * @since   1.5.5
 * @version 1.5.5
 */
function cariera_single_company_v2_overview() {
	$layout = cariera_single_company_layout();

	if ( 'v1' === $layout ) {
		return;
	}

	echo '<div id="company-overview" class="company-overview">';
	get_job_manager_template_part( 'single-company/single', 'company-overview', 'wp-job-manager-companies' );
	echo '</div>';
}

add_action( 'cariera_single_company_listing', 'cariera_single_company_v2_overview', 12 );

/**
 * Adding Job overview to the single page
 *
 * @since   1.5.5
 * @version 1.5.6
 */
function cariera_single_company_v2_map() {
	global $post;

	$layout      = cariera_single_company_layout();
	$company_map = cariera_get_option( 'cariera_company_map' );
	$lng         = $post->geolocation_long;

	if ( 'v1' === $layout ) {
		return;
	}

	if ( ! $company_map || empty( $lng ) ) {
		return;
	}

	echo '<div id="company-location" class="company-location">';
	echo '<h5 class="mt-0">' . esc_html__( 'Company Location', 'cariera' ) . '</h5>';
	cariera_company_map();
	echo '</div>';
}

add_action( 'cariera_single_company_listing', 'cariera_single_company_v2_map', 13 );

/*
=====================================================
	OTHER FUNCTIONS
=====================================================
*/

/**
 * Single company page layout for demo showcase purposes.
 *
 * @since 1.7.0
 */
function cariera_demo_single_company_layout() {
	$value = get_option( 'cariera_single_company_layout' );

	if ( isset( $_GET['company-layout'] ) && ! empty( $_GET['company-layout'] ) ) {
		$value = $_GET['company-layout'];
	}

	return $value;
}

add_action( 'cariera_single_company_layout', 'cariera_demo_single_company_layout' );

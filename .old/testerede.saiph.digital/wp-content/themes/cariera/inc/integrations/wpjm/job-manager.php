<?php

namespace Cariera\Integrations\WPJM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Job_Manager {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since   1.7.0
	 * @version 1.7.1
	 */
	public function __construct() {
		require get_template_directory() . '/inc/wp-job-manager/functions.php';

		// Prevent from enqueueing wpjm frontend.css.
		add_filter( 'job_manager_enqueue_frontend_style', '__return_false', 30 );

		// Remove Job Search Button.
		add_filter( 'job_manager_job_filters_show_submit_button', '__return_false' );

		// General.
		add_action( 'init', [ $this, 'wpjm_remove_actions' ] );
		add_action( 'single_job_listing_start', [ $this, 'job_content_start' ] );
		add_action( 'wp_head', [ $this, 'job_og_image' ] );
		add_action( 'cariera_job_listing_status', [ $this, 'job_listing_status_title' ] );

		// Single Job Page V1.
		add_action( 'single_job_listing_start', [ $this, 'single_job_application_msg' ], 20 );
		add_action( 'single_job_listing_end', [ $this, 'single_job_share' ] );
		add_action( 'single_job_listing_end', [ $this, 'single_job_print' ], 12 );
		add_action( 'cariera_single_job_listing_after', [ $this, 'single_job_related_jobs' ], 20 );
		add_action( 'cariera_single_job_listing_after', [ $this, 'edit_single_job_listing' ], 21 );
		add_action( 'cariera_single_job_listing_sidebar', [ $this, 'single_job_sidebar_overview' ], 10 );
		add_action( 'cariera_single_job_listing_sidebar', [ $this, 'single_job_sidebar_map' ], 20 );
		add_action( 'cariera_single_job_listing_sidebar', [ $this, 'single_job_sidebar' ], 30 );
		add_action( 'single_job_listing_meta_end', [ $this, 'single_job_application' ], 999 );

		// Single Job Page V2-3.
		add_action( 'single_job_listing_end', [ $this,'single_job_v2_overview' ], 7 );
		add_action( 'single_job_listing_end', [ $this,'single_job_v2_map' ], 8 );
		add_action( 'cariera_job_listing_actions', [ $this, 'single_job_v2_application' ], 10 );
		add_action( 'cariera_job_listing_actions', [ $this, 'single_job_v2_expire' ], 11 );

		// Submission.
		add_action( 'submit_job_step_choose_package_submit_text', [ $this, 'wcpl_package_submit_text' ] );
		add_action( 'cariera_job_submission_steps', [ $this, 'job_submission_flow' ] );
		add_action( 'submit_job_form_job_fields_start', [ $this, 'submit_job_fields_start' ] );
		add_action( 'submit_job_form_job_fields_end', [ $this, 'submit_job_fields_end' ] );
		add_action( 'submit_job_form_company_fields_start', [ $this, 'submit_company_fields_start' ] );
		add_action( 'submit_company_form_company_fields_start', [ $this, 'submit_company_fields_start' ] );
		add_action( 'submit_job_form_company_fields_end', [ $this, 'submit_company_fields_end' ], 20 );
		add_action( 'submit_company_form_company_fields_end', [ $this, 'submit_company_fields_end' ] );
		add_filter( 'submit_job_form_submit_button_text', [ $this, 'submit_job_form_button_text' ] );

		// AJAX Functions.
		add_action( 'wp_ajax_load_quickjob_content', [ $this, 'load_quickview_content_callback' ] );
		add_action( 'wp_ajax_nopriv_load_quickjob_content', [ $this, 'load_quickview_content_callback' ] );
		add_action( 'wp_ajax_cariera_search_jobs', [ $this, 'search_jobs' ] );
		add_action( 'wp_ajax_nopriv_cariera_search_jobs', [ $this, 'search_jobs' ] );

		// Other.
		add_filter( 'submit_job_form_wp_editor_args', [ $this, 'customize_editor_toolbar' ] );
		add_filter( 'wpcf7_mail_components', [ $this, 'wpjm_wpcf7_notification_email' ], 10, 3 );

		// Demo.
		add_action( 'cariera_job_manager_single_job_layout', [ $this, 'demo_single_job_layout' ] );
	}

	/**
	 * Remove WPJM action to handle certain templates
	 *
	 * @since   1.3.0
	 * @version 1.5.5
	 */
	public function wpjm_remove_actions() {
		$layout = cariera_single_job_layout();

		remove_action( 'single_job_listing_start', 'job_listing_meta_display', 20 );

		if ( 'v1' !== $layout ) {
			remove_action( 'single_job_listing_start', 'job_listing_company_display', 30 );
		}
	}

	/**
	 * Bind default `job_content_start` to the theme
	 *
	 * @since 1.3.8.1
	 */
	public function job_content_start() {
		return do_action( 'job_content_start' );
	}

	/**
	 * Add og:image tag for jobs
	 *
	 * @since 1.4.6
	 */
	public function job_og_image() {
		if ( is_singular( 'job_listing' ) ) {
			echo '<meta property="og:image" content="' . esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ) . '" />';
		}
	}

	/**
	 * Job Listing status badges
	 *
	 * @since 1.4.8
	 */
	public function job_listing_status_title() {
		global $post;

		if ( is_position_filled() ) {
			echo '<span class="job-listing-status-badge filled">' . esc_html__( 'filled', 'cariera' ) . '</span>';
		}

		if ( 'expired' === $post->post_status ) {
			echo '<span class="job-listing-status-badge expired">' . esc_html__( 'expired', 'cariera' ) . '</span>';
		}
	}

	/*
	=====================================================
		SINGLE JOB PAGE
	=====================================================
	*/

	/**
	 * Adding Application message to Single Job Listing
	 *
	 * @since   1.3.0
	 * @version 1.5.0
	 */
	public function single_job_application_msg() {
		if ( ! class_exists( 'WP_Job_Manager_Applications' ) ) {
			return;
		}

		if ( is_position_filled() ) { ?>
				<div class="job-manager-message success position-filled">
					<?php esc_html_e( 'This position has been filled', 'cariera' ); ?>
				</div>
			<?php } elseif ( ! candidates_can_apply() ) { ?>
				<div class="job-manager-message error applications-closed">
					<?php esc_html_e( 'Applications have closed', 'cariera' ); ?>
				</div>
				<?php
			}
	}

	/**
	 * Adding Share buttons to Single Job Listing
	 *
	 * @since   1.3.0
	 * @version 1.5.5
	 */
	public function single_job_share() {
		if ( ! cariera_get_option( 'cariera_job_share' ) || ! function_exists( 'cariera_share_media' ) ) {
			return;
		}

		echo cariera_share_media();
	}

	/**
	 * Adding Related Jobs to Single Job Listing
	 *
	 * @since   1.3.0
	 * @version 1.5.5
	 */
	public function single_job_related_jobs() {
		if ( ! cariera_get_option( 'cariera_related_jobs' ) ) {
			return;
		}

		get_job_manager_template_part( 'single-job/related-jobs' );
	}

	/**
	 * Edit single job listing button
	 *
	 * @since   1.5.5
	 * @version 1.6.2
	 */
	public function edit_single_job_listing() {
		global $post, $job_preview;

		if ( $job_preview ) {
			return;
		}

		if ( ! job_manager_user_can_edit_job( $post->ID ) ) {
			return;
		}

		$dashboard_id = apply_filters( 'cariera_edit_single_job_listing_dashboard_id', get_option( 'job_manager_job_dashboard_page_id' ) );

		$edit_link = add_query_arg(
			[
				'action' => 'edit',
				'job_id' => $post->ID,
			],
			get_permalink( $dashboard_id )
		);
		?>

		<a href="<?php echo esc_url( $edit_link ); ?>" class="edit-listing btn-main"><?php esc_html_e( 'Edit Job', 'cariera' ); ?></a>
		<?php
	}

	/**
	 * Adding Job Overview to the sidebar
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_sidebar_overview() {
		get_job_manager_template_part( 'single-job/single-job-listing-overview' );
	}

	/**
	 * Adding Map to the job sidebar
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_sidebar_map() {
		echo '<aside class="mt40">';
		cariera_job_map();
		echo '</aside>';
	}

	/**
	 * Adding Sidebar widget area to the job sidebar
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_sidebar() {
		dynamic_sidebar( 'sidebar-single-job' );
	}

	/**
	 * Adding job application to the job-overview
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_application() {
		$layout = cariera_single_job_layout();

		if ( 'v1' !== $layout ) {
			return;
		}

		get_job_manager_template_part( 'single-job/single-job-application' );
	}

	/*
	=====================================================
		SINGLE JOB PAGE V.2-3
	=====================================================
	*/

	/**
	 * Adding Job overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_v2_overview() {
		$layout = cariera_single_job_layout();

		if ( 'v1' === $layout ) {
			return;
		}

		echo '<div class="job-overview">';
		get_job_manager_template_part( 'single-job/single-job-listing-overview' );
		echo '</div>';
	}

	/**
	 * Adding Job overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.5.6
	 */
	public function single_job_v2_map() {
		global $post;

		$layout  = cariera_single_job_layout();
		$job_map = cariera_get_option( 'cariera_job_map' );
		$lng     = $post->geolocation_long;

		if ( 'v1' === $layout ) {
			return;
		}

		if ( ! $job_map || empty( $lng ) ) {
			return;
		}

		echo '<div class="job-map">';
		echo '<h5 class="mt-0">' . esc_html__( 'Job Location', 'cariera' ) . '</h5>';
		cariera_job_map();
		echo '</div>';
	}

	/**
	 * Adding Job overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_job_v2_application() {
		get_job_manager_template_part( 'single-job/single-job-application' );
	}

	/**
	 * Adding Job expiration date under the listing actions
	 *
	 * @since   1.5.5
	 * @version 1.5.6
	 */
	public function single_job_v2_expire() {
		global $post;

		$expired_date = get_post_meta( $post->ID, '_job_expires', true );

		if ( empty( $expired_date ) ) {
			return;
		}
		?>

		<div class="job-expiration">
			<span><?php esc_html_e( 'Expiration Date:', 'cariera' ); ?></span>
			<span class="expiration-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expired_date ) ) ); ?></span>
		</div>
		<?php
	}

	/*
	=====================================================
		JOB SUBMISSION HTML MARKUP
	=====================================================
	*/

	/**
	 * Submit button text for WC Paid Listings step
	 *
	 * @since   1.5.2
	 * @version 1.5.3
	 */
	public function wcpl_package_submit_text() {
		return esc_html__( 'Select Package', 'cariera' );
	}

	/**
	 * Job Submission Flow
	 *
	 * @since   1.3.2
	 * @version 1.6.4
	 */
	public function job_submission_flow() {
		// Temporary variables.
		$is_packages_enabled = false;

		// Get page IDs.
		$current_page_id     = get_queried_object_id();
		$job_submission_page = apply_filters( 'cariera_dashboard_job_submit_page', get_option( 'job_manager_submit_job_form_page_id', false ) );

		// Get job packages.
		if ( function_exists( 'wc_get_products' ) ) {
			$job_packages        = wc_get_products( [ 'type' => 'job_package' ] );
			$job_subscriptions   = wc_get_products( [ 'type' => 'job_package_subscription' ] );
			$is_packages_enabled = class_exists( 'WC_Paid_Listings' ) && ( ! empty( $job_packages ) || ! empty( $job_subscriptions ) );
		}

		// Display submission flow.
		if ( ! empty( $job_submission_page ) && ( absint( $job_submission_page ) === $current_page_id ) ) {
			?>
			<div class="submission-flow job-submission-flow">
				<ul>
					<?php if ( get_option( 'job_manager_paid_listings_flow' ) === 'before' && $is_packages_enabled ) { ?>
						<li class="choose-package"><?php esc_html_e( 'Choose Package', 'cariera' ); ?></li>
					<?php } ?>
					<li class="listing-details"><?php esc_html_e( 'Listing Details', 'cariera' ); ?></li>
					<li class="preview-listing"><?php esc_html_e( 'Preview Listing', 'cariera' ); ?></li>
					<?php if ( get_option( 'job_manager_paid_listings_flow' ) !== 'before' && $is_packages_enabled ) { ?>
						<li class="choose-package"><?php esc_html_e( 'Choose Package', 'cariera' ); ?></li>
					<?php } ?>
					<?php if ( $is_packages_enabled ) { ?>
						<li class="checkout"><?php esc_html_e( 'Checkout', 'cariera' ); ?></li>
					<?php } ?>
				</ul>
			</div>
			<?php
		}
	}

	/**
	 * Job submission fields start
	 *
	 * @since 1.4.0
	 */
	public function submit_job_fields_start() {
		echo '<div class="submit-job-box submit-job_job-info">';
			echo '<h3 class="title">' . esc_html__( 'Job Details', 'cariera' ) . '</h3>';
			echo '<div class="form-fields">';
	}

	/**
	 * Job submission fields end
	 *
	 * @since 1.4.0
	 */
	public function submit_job_fields_end() {
		echo '</div></div>';
	}

	/**
	 * Company submission fields start
	 *
	 * @since 1.4.0
	 */
	public function submit_company_fields_start() {
		echo '<div class="submit-job-box submit-job_company-info">';
			echo '<h3 class="title">' . esc_html__( 'Company Details', 'cariera' ) . '</h3>';
			echo '<div class="form-fields">';
	}

	/**
	 * Company submission fields end
	 *
	 * @since 1.4.0
	 */
	public function submit_company_fields_end() {
		echo '</div></div>';
	}

	/**
	 * Company selection
	 *
	 * @since 1.4.0
	 */
	public function submit_job_form_button_text( $text ) {
		return esc_html__( 'Preview Listing', 'cariera' );
	}

	/*
	=====================================================
		OTHER FUNCTIONS
	=====================================================
	*/

	/**
	 * Job Quickview AJAX function
	 *
	 * @since   1.3.1
	 * @version 1.6.2
	 */
	public function load_quickview_content_callback() {
		if ( ! isset( $_POST['id'] ) ) {
			die( '0' );
		}

		$job_id = absint( (int) ( $_POST['id'] ) );

		global $post;
		$post = get_post( $job_id );

		$classes = 'cariera-quickview-wrapper job-listing single-job-v1';
		ob_start();
		?>

		<div id="job-id-<?php echo esc_attr( get_the_ID() ); ?>" class="<?php echo esc_attr( $classes ); ?>">
			<div class="row">

				<!-- Job summary -->
				<div class="col-md-7 col-sm-12 col-xs-12 job-summary">
					<div class="single-job-listing cariera-scroll">
						<?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) { ?>
							<div class="job-manager-info"><?php esc_html_e( 'This listing has expired.', 'cariera' ); ?></div>
						<?php } else { ?>
							<?php get_job_manager_template_part( 'content-single-job_listing-company' ); ?>

							<div class="job-description">
								<?php wpjm_the_job_description(); ?>
							</div>

							<?php
							if ( candidates_can_apply() ) {
								get_job_manager_template( 'job-application.php' );
							}
						}
						?>
					</div>
				</div>

				<!-- Job map -->
				<div class="col-md-5 col-sm-12 col-xs-12 job-map-wrapper">
					<div id="job-map" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>"></div>
				</div>
			</div>
		</div>

		<?php
		$return = ob_get_clean();
		wp_reset_postdata();

		die( $return );
	}

	/**
	 * AJAX Job Search Suggestions
	 *
	 * @since   1.3.1
	 * @version 1.6.0
	 */
	public function search_jobs() {
		check_ajax_referer( '_cariera_nonce', 'nonce' );

		$search_query = new \WP_Query(
			[
				's'           => sanitize_text_field( $_REQUEST['term'] ),
				'post_type'   => 'job_listing',
				'post_status' => 'publish',
			]
		);

		global $post;
		$response = [];

		if ( $search_query->get_posts() ) {
			foreach ( $search_query->get_posts() as $post ) {
				setup_postdata( $post );

				if ( get_option( 'cariera_company_manager_integration', false ) ) {
					$company = cariera_get_the_company( $post->ID );
					$logo    = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
				} else {
					$logo = get_the_company_logo( $post->ID, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
				}

				if ( ! empty( $logo ) ) {
					$logo_img = $logo;
				} else {
					$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
				}

				$response[] = sprintf(
					'<li>' .
					'<a class="search-item" href="%s">' .
					'<img class="item-thumb" src="%s">' .
					'<div class="item-details">' .
					'<span class="title">%s</span>' .
					'<span class="location">%s</span>' .
					'</div>' .
					'</a>' .
					'</li>',
					esc_url( get_permalink( $post->ID ) ),
					$logo_img,
					$post->post_title,
					get_post_meta( $post->ID, '_job_location', true )
				);
			}
		}

		if ( empty( $response ) ) {
			$response[] = sprintf( '<li>%s</li>', esc_html__( 'Nothing found', 'cariera' ) );
		}

		$output = sprintf( '<ul>%s</ul>', implode( ' ', $response ) );

		wp_send_json_success( $output );

		die();
	}

	/**
	 * Output the job's min & max salary if there is any
	 *
	 * @since 1.4.1
	 */
	public function customize_editor_toolbar( $args ) {
		$args['tinymce']['toolbar1'] = 'formatselect,|,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,undo,redo';
		return $args;
	}

	/**
	 * Changing the email for candidates & companies in CF7
	 *
	 * @since 1.3.0
	 */
	public function wpjm_wpcf7_notification_email( $components, $cf7, $three = null ) {
		$forms = apply_filters(
			'cariera_wpjm_wpcf7_notification_email_forms',
			[
				'company' => [
					'contact' => get_option( 'cariera_single_company_contact_form' ),
				],
				'resume'  => [
					'contact' => get_option( 'resume_manager_single_resume_contact_form' ),
				],
			]
		);

		$submission = \WPCF7_Submission::get_instance();
		$unit_tag   = $submission->get_meta( 'unit_tag' );

		if ( ! preg_match( '/^wpcf7-f(\d+)-p(\d+)-o(\d+)$/', $unit_tag, $matches ) ) {
			return $components;
		}

		$post_id = (int) $matches[2];
		$post    = get_post( $post_id );

		// Prevent issues when the form is not submitted via a resume or company page.
		if ( ! isset( $forms[ $post->post_type ] ) ) {
			return $components;
		}

		if ( ! array_search( $cf7->id(), $forms[ $post->post_type ], true ) ) {
			return $components;
		}

		// Bail if this is the second mail.
		if ( isset( $three ) && 'mail_2' == $three->name() ) {
			return $components;
		}

		switch ( $post->post_type ) {
			case 'company':
				$recipient = $post->_company_email ? $post->_company_email : '';
				break;

			case 'resume':
				$recipient = $post->_candidate_email ? $post->_candidate_email : '';
				break;

			default:
				$recipient = '';
				break;
		}

		// If we couldn't find the email by now, get it from the listing owner/author.
		if ( empty( $recipient ) ) {

			// Just get the email of the listing author.
			$owner_ID = $post->post_author;

			// Retrieve the owner user data to get the email.
			$owner_info = get_userdata( $owner_ID );

			if ( false !== $owner_info ) {
				$recipient = $owner_info->user_email;
			}
		}

		$components['recipient'] = $recipient;

		return $components;
	}

	/**
	 * Single job page layout for demo showcase purposes.
	 *
	 * @since 1.7.0
	 */
	public function demo_single_job_layout() {
		$value = get_option( 'cariera_job_manager_single_job_layout' );

		if ( isset( $_GET["job-layout"] ) && ! empty( $_GET["job-layout"] ) ) {
			$value = $_GET['job-layout'];
		}

		return $value;
	}

	/**
	 * Adding Print button to Single Job Listing
	 *
	 * @since   1.7.1
	 * @version 1.7.1
	 */
	public function single_job_print() {
		get_job_manager_template_part( 'single-job/single-job-listing-print' );
	}
}

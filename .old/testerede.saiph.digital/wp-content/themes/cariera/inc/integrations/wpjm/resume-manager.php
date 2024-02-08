<?php

namespace Cariera\Integrations\WPJM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Resume_Manager {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since   1.7.0
	 * @version 1.7.0
	 */
	public function __construct() {

		// General.
		add_filter( 'resume_manager_user_can_view_contact_details', [ $this,'hide_contact_button_for_resume_author' ], 10, 2 );

		// Single Resume Page V1.
		add_action( 'resume_manager_contact_details', [ $this,'single_candidate_contact_form' ], 9 );
		add_action( 'cariera_candidate_socials', [ $this,'candidate_social_accounts' ] );
		add_action( 'single_resume_content', [ $this, 'candidate_description' ], 10 );
		add_action( 'single_resume_content', [ $this,'candidate_education' ], 20 );
		add_action( 'single_resume_content', [ $this, 'candidate_experience' ], 30 );
		add_action( 'single_resume_content', [ $this, 'candidate_skill' ], 40 );
		add_action( 'single_resume_content', [ $this, 'candidate_video' ], 50 );
		add_action( 'single_resume_content', [ $this,'single_resume_share' ], 60 );
		add_action( 'single_resume_content', [ $this, 'single_resume_print' ], 61 );
		add_action( 'cariera_single_resume_sidebar', [ $this,'single_resume_sidebar_overview' ], 10 );
		add_action( 'cariera_single_resume_sidebar', [ $this, 'single_resume_sidebar_map' ], 20 );
		add_action( 'cariera_single_resume_sidebar', [ $this,'single_resume_sidebar' ], 30 );
		add_action( 'cariera_single_resume_after', [ $this, 'single_resume_related_resumes' ], 20 );
		add_action( 'cariera_single_resume_after', [ $this, 'edit_single_resume' ], 21 );

		// Single Resume Page V2-3.
		add_action( 'cariera_resume_actions', [ $this, 'single_resume_v2_download_cv' ], 10 );
		add_action( 'cariera_resume_actions', [ $this, 'single_resume_v2_expire' ], 11 );
		add_action( 'single_resume_content', [ $this, 'single_resume_v2_overview' ], 11 );
		add_action( 'single_resume_content', [ $this, 'single_resume_v2_map' ], 41 );

		// Resume Submission.
		add_action( 'cariera_resume_submission_steps', [ $this, 'resume_submission_flow' ] );
		add_action( 'submit_resume_form_resume_fields_start', [ $this, 'submit_resume_fields_start' ] );
		add_action( 'submit_resume_form_resume_fields_end', [ $this, 'submit_resume_fields_end' ] );
		add_filter( 'submit_resume_form_submit_button_text', [ $this, 'submit_resume_form_button_text' ] );

		// Demo.
		add_action( 'cariera_resume_manager_single_resume_layout', [ $this, 'demo_single_resume_layout' ] );
	}

	/**
	 * Hide Contact button for resume author
	 *
	 * @since   1.4.7
	 * @version 1.4.8
	 */
	public function hide_contact_button_for_resume_author( $can_view, $resume_id ) {
		$contact = get_option( 'cariera_resume_manager_contact_owner' );
		$resume  = get_post( $resume_id );

		if ( $resume && isset( $resume->post_author ) && ! empty( $resume->post_author ) && $contact ) {
			if ( $resume->post_author == get_current_user_id() ) {
				return false;
			}
		}

		return $can_view;
	}

	/*
	=====================================================
		SINGLE RESUME PAGE
	=====================================================
	*/

	/**
	 * Contact Form for Single Candidate Page
	 *
	 * @since   1.3.0
	 * @version 1.5.2
	 */
	public function single_candidate_contact_form() {
		$form_id = get_option( 'resume_manager_single_resume_contact_form' );

		if ( empty( $form_id ) ) {
			return;
		}

		remove_all_filters( 'resume_manager_contact_details' );

		$shortcode = sprintf( '[contact-form-7 id="%1$d" title="%2$s"]', $form_id, get_the_title( $form_id ) );
		echo '<div class="contact-form contact-candidate">';
		echo do_shortcode( $shortcode );
		echo '</div>';
	}

	/**
	 * Candidate Social Media
	 *
	 * @since 1.3.1
	 */
	public function candidate_social_accounts() {
		if ( ! empty( cariera_get_the_candidate_fb() || cariera_get_the_candidate_twitter() || cariera_get_the_candidate_linkedin() || cariera_get_the_candidate_instagram() || cariera_get_the_candidate_youtube() ) ) {
			echo '<div class="candidate-social">';
				cariera_candidate_fb_output();
				cariera_candidate_twitter_output();
				cariera_candidate_linkedin_output();
				cariera_candidate_instagram_output();
				cariera_candidate_youtube_output();
			echo '</div>';
		}
	}

	/**
	 * Adding Resume description to Single Resume Page
	 *
	 * @since 1.4.6
	 */
	public function candidate_description() {
		if ( empty( get_the_content() ) ) {
			return;
		} ?>

		<div id="candidate-description" class="candidate-description">
			<h5><?php esc_html_e( 'About the Candidate', 'cariera' ); ?></h5>
			<?php echo apply_filters( 'the_resume_description', get_the_content() ); ?>
		</div>
		<?php
	}

	/**
	 * Adding Candidate Education to Single Resume Page
	 *
	 * @since 1.4.6
	 */
	public function candidate_education() {
		global $post;

		$items = get_post_meta( $post->ID, '_candidate_education', true );

		if ( $items ) {
			?>
			<div id="candidate-qualification" class="candidate-education">
				<h5><?php esc_html_e( 'Education', 'cariera' ); ?></h5>

				<?php foreach ( $items as $item ) { ?>
					<div class="education-item">                                            
						<small class="time"><?php echo esc_html( $item['date'] ); ?></small>
						<div class="education-title">
							<strong class="location"><?php echo esc_html( $item['location'] ); ?></strong>
							<span class="qualification"><?php echo esc_html( $item['qualification'] ); ?></span>
						</div>

						<div class="education-body">
							<p><?php echo wpautop( wptexturize( $item['notes'] ) ); ?></p>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Adding Candidate Experience to Single Resume Page
	 *
	 * @since 1.4.6
	 */
	public function candidate_experience() {
		global $post;

		$items = get_post_meta( $post->ID, '_candidate_experience', true );

		if ( $items ) {
			?>
			<div id="candidate-experience" class="candidate-experience">
				<h5><?php esc_html_e( 'Experience', 'cariera' ); ?></h5>

				<?php foreach ( $items as $item ) { ?>
					<div class="experience-item">
						<small class="time"><?php echo esc_html( $item['date'] ); ?></small>
						<div class="experience-title">
							<strong class="employer"><?php echo esc_html( $item['employer'] ); ?></strong>
							<span class="position"><?php echo esc_html( $item['job_title'] ); ?></span>
						</div>

						<div class="experience-body">
							<p><?php echo wpautop( wptexturize( $item['notes'] ) ); ?></p>
						</div>
					</div>

				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Adding Candidate Skills to Single Resume Page
	 *
	 * @since 1.4.6
	 */
	public function candidate_skill() {
		global $post;

		$skills = wp_get_object_terms( $post->ID, 'resume_skill', [ 'fields' => 'names' ] );

		if ( $skills && is_array( $skills ) ) {
			?>
			<div id="candidate-skills" class="candidate-skills">
				<h5><?php esc_html_e( 'Skills', 'cariera' ); ?></h5>

				<div class="resume-manager-skills">
					<?php echo '<span>' . implode( '</span><span>', $skills ) . '</span>'; ?>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Adding Candidate Video to Single Resume Page
	 *
	 * @since 1.4.6
	 */
	public function candidate_video() {
		if ( empty( get_the_candidate_video() ) ) {
			return;
		}
		?>

		<div id="candidate-video" class="candidate-video-wrapper">
			<h5><?php esc_html_e( 'Candidate Video', 'cariera' ); ?></h5>
			<?php echo apply_filters( 'cariera_the_candidate_video', the_candidate_video() ); ?>
		</div>
		<?php
	}

	/**
	 * Adding Share buttons to Single Resume Page
	 *
	 * @since   1.4.6
	 * @version 1.5.5
	 */
	public function single_resume_share() {
		if ( ! cariera_get_option( 'cariera_resume_share' ) || ! function_exists( 'cariera_share_media' ) ) {
			return;
		}

		echo cariera_share_media();
	}

	/**
	 * Adding Resume Overview to the sidebar
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_resume_sidebar_overview() {
		get_job_manager_template_part( 'single-resume/single', 'resume-overview', 'wp-job-manager-resumes' );
	}

	/**
	 * Adding Map to the resume sidebar
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_resume_sidebar_map() {
		echo '<aside class="mt40">';
		cariera_resume_map();
		echo '</aside>';
	}

	/**
	 * Adding Sidebar widget area to the resume sidebar
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_resume_sidebar() {
		dynamic_sidebar( 'sidebar-single-resume' );
	}

	/**
	 * Adding Related Jobs to Single Job Listing
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function single_resume_related_resumes() {
		if ( ! cariera_get_option( 'cariera_related_resumes' ) ) {
			return;
		}

		get_job_manager_template_part( 'single-resume/related', 'resumes', 'wp-job-manager-resumes' );
	}

	/**
	 * Edit single resume button
	 *
	 * @since   1.5.5
	 * @version 1.6.2
	 */
	public function edit_single_resume() {
		global $post, $resume_preview;

		if ( $resume_preview ) {
			return;
		}

		if ( ! resume_manager_user_can_edit_resume( $post->ID ) ) {
			return;
		}

		$dashboard_id = apply_filters( 'cariera_edit_single_resume_dashboard_id', get_option( 'resume_manager_candidate_dashboard_page_id' ) );

		$edit_link = add_query_arg(
			[
				'action'    => 'edit',
				'resume_id' => $post->ID,
			],
			get_permalink( $dashboard_id )
		);
		?>

		<a href="<?php echo esc_url( $edit_link ); ?>" class="edit-listing btn-main"><?php esc_html_e( 'Edit Resume', 'cariera' ); ?></a>
		<?php
	}

	/**
	 * Adding Print button to Single Resume
	 *
	 * @since   1.7.1
	 * @version 1.7.1
	 */
	public function single_resume_print() {
		get_job_manager_template_part( 'single-resume/single', 'resume-print', 'wp-job-manager-resumes' );
	}

	/*
	=====================================================
		SINGLE RESUME PAGE V.2-3
	=====================================================
	*/

	/**
	 * Adding CV download button to the resume listing actions
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_resume_v2_download_cv() {
		global $post;

		if ( ! resume_has_file() ) {
			return;
		}

		$resume_files = get_resume_files();

		if ( $resume_files && apply_filters( 'resume_manager_user_can_download_resume_file', true, $post->ID ) ) {
			foreach ( $resume_files as $key => $resume_file ) {
				?>
				<a href="<?php echo esc_url( get_resume_file_download_url( null, $key ) ); ?>" class="btn btn-main btn-effect"><?php esc_html_e( 'Download CV', 'cariera' ); ?></a>
				<?php
			}
		}
	}

	/**
	 * Adding Resume expiration date under the listing actions
	 *
	 * @since   1.5.5
	 * @version 1.5.6
	 */
	public function single_resume_v2_expire() {
		global $post;

		$expired_date = get_post_meta( $post->ID, '_resume_expires', true );

		if ( empty( $expired_date ) ) {
			return;
		}
		?>

		<div class="resume-expiration">
			<span><?php esc_html_e( 'Expiration Date:', 'cariera' ); ?></span>
			<span class="expiration-date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expired_date ) ) ); ?></span>
		</div>
		<?php
	}

	/**
	 * Adding Resume overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.5.5
	 */
	public function single_resume_v2_overview() {
		$layout = cariera_single_resume_layout();

		if ( 'v1' === $layout ) {
			return;
		}

		echo '<div id="candidate-overview" class="candidate-overview">';
		get_job_manager_template_part( 'single-resume/single', 'resume-overview', 'wp-job-manager-resumes' );
		echo '</div>';
	}

	/**
	 * Adding Job overview to the single page
	 *
	 * @since   1.5.5
	 * @version 1.5.6
	 */
	public function single_resume_v2_map() {
		global $post;

		$layout     = cariera_single_resume_layout();
		$resume_map = cariera_get_option( 'cariera_resume_map' );
		$lng        = $post->geolocation_long;

		if ( 'v1' === $layout ) {
			return;
		}

		if ( ! $resume_map || empty( $lng ) ) {
			return;
		}

		echo '<div id="candidate-map" class="candidate-map">';
		echo '<h5 class="mt-0">' . esc_html__( 'Candidate Location', 'cariera' ) . '</h5>';
		cariera_resume_map();
		echo '</div>';
	}

	/*
	=====================================================
		RESUME SUBMISSION HTML MARKUP
	=====================================================
	*/

	/**
	 * Resume Submission Flow
	 *
	 * @since   1.3.2
	 * @version 1.6.4
	 */
	public function resume_submission_flow() {
		// Temporary variables.
		$is_packages_enabled = false;

		// Get page IDs.
		$current_page_id        = get_queried_object_id();
		$resume_submission_page = apply_filters( 'cariera_dashboard_resume_submit_page', get_option( 'resume_manager_submit_resume_form_page_id', false ) );

		// Get resume packages.
		if ( function_exists( 'wc_get_products' ) ) {
			$resume_packages      = wc_get_products( [ 'type' => 'resume_package' ] );
			$resume_subscriptions = wc_get_products( [ 'type' => 'resume_package_subscription' ] );
			$is_packages_enabled  = class_exists( 'WC_Paid_Listings' ) && ( ! empty( $resume_packages ) || ! empty( $resume_subscriptions ) );
		}

		// Display submission flow.
		if ( ! empty( $resume_submission_page ) && ( absint( $resume_submission_page ) === $current_page_id ) ) {
			?>
			<div class="submission-flow resume-submission-flow">
				<ul>
					<?php if ( get_option( 'resume_manager_paid_listings_flow' ) === 'before' && $is_packages_enabled ) { ?>
						<li class="choose-package"><?php esc_html_e( 'Choose Package', 'cariera' ); ?></li>
					<?php } ?>
					<li class="listing-details"><?php esc_html_e( 'Resume Details', 'cariera' ); ?></li>
					<li class="preview-listing"><?php esc_html_e( 'Preview Resume', 'cariera' ); ?></li>
					<?php if ( get_option( 'resume_manager_paid_listings_flow' ) !== 'before' && $is_packages_enabled ) { ?>
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
	 * Resume submission fields start
	 *
	 * @since 1.4.0
	 */
	public function submit_resume_fields_start() {
		echo '<div class="submit-job-box submit_resume-info">';
			echo '<h3 class="title">' . esc_html__( 'Candidate Details', 'cariera' ) . '</h3>';
			echo '<div class="form-fields">';
	}

	/**
	 * Resume submission fields end
	 *
	 * @since 1.4.0
	 */
	public function submit_resume_fields_end() {
		echo '</div></div>';
	}

	/**
	 * Submit resume button
	 *
	 * @param string $text
	 *
	 * @since   1.4.0
	 * @version 1.7.0
	 */
	public function submit_resume_form_button_text( $text ) {
		$text = esc_html__( 'Preview Resume', 'cariera' );

		return $text;
	}

	/*
	=====================================================
		OTHER FUNCTIONS
	=====================================================
	*/

	/**
	 * Single resume page layout for demo showcase purposes.
	 *
	 * @since 1.7.0
	 */
	public function demo_single_resume_layout() {
		$value = get_option( 'cariera_resume_manager_single_resume_layout' );

		if ( isset( $_GET['resume-layout'] ) && ! empty( $_GET['resume-layout'] ) ) {
			$value = $_GET['resume-layout'];
		}

		return $value;
	}
}

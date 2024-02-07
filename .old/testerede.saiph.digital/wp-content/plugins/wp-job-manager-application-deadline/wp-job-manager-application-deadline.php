<?php
/**
 * Plugin Name: WP Job Manager - Application Deadline
 * Plugin URI: https://wpjobmanager.com/add-ons/application-deadline/
 * Description: Allows job listers to set a closing date via a new field on the job submission form. Once this date passes, the job listing is automatically ended (if enabled in settings).
 * Version: 1.2.8
 * Author: Automattic
 * Author URI: https://wpjobmanager.com
 * Requires at least: 6.1
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Text Domain: wp-job-manager-application-deadline
 * Domain Path: /languages/
 *
 * WPJM-Product: wp-job-manager-application-deadline
 *
 * Copyright: 2020 Automattic
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_Job_Manager_Job_Tags class.
 */
class WP_Job_Manager_Application_Deadline {
	const JOB_MANAGER_CORE_MIN_VERSION = '1.30.0';

	/**
	 * __construct function.
	 */
	public function __construct() {
		define( 'JOB_MANAGER_APPLICATION_DEADLINE_VERSION', '1.2.8' );
		define( 'JOB_MANAGER_APPLICATION_DEADLINE_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'JOB_MANAGER_APPLICATION_DEADLINE_PLUGIN_URL', untrailingslashit( plugins_url( '', ( __FILE__ ) ) ) );

		// Set up startup actions
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ), 12 );
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 13 );
		add_action( 'admin_notices', array( $this, 'version_check' ) );

		// Activate
		register_activation_hook( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ), array( $this, 'cron' ), 10 );
	}

	/**
	 * Initializes plugin.
	 */
	public function init_plugin() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		add_filter( 'job_manager_settings', array( $this, 'settings' ) );
		add_filter( 'submit_job_form_fields', array( $this, 'deadline_field' ) );
		add_filter( 'submit_job_form_validate_fields', array( $this, 'validate_deadline_field' ), 10, 3 );
		add_action( 'job_manager_update_job_data', array( $this, 'save_deadline_field' ), 10, 2 );
		add_action( 'submit_job_form_fields_get_job_data', array( $this, 'get_deadline_field_data' ), 10, 2 );
		add_filter( 'single_job_listing_meta_end', array( $this, 'display_the_deadline' ) );
		add_filter( 'job_listing_meta_end', array( $this, 'display_the_deadline' ) );
		add_filter( 'job_manager_candidates_can_apply', array( $this, 'job_manager_candidates_can_apply' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_filter( 'job_manager_job_listing_data_fields', array( $this, 'admin_fields' ) );

		// Cron
		add_action( 'check_application_deadlines', array( $this, 'check_application_deadlines' ) );

		// Add column to admin
		add_filter( 'manage_edit-job_listing_columns', array( $this, 'columns' ), 20 );
		add_action( 'manage_job_listing_posts_custom_column', array( $this, 'custom_columns' ), 1 );
		add_filter( 'manage_edit-job_listing_sortable_columns', array( $this, 'job_admin_closing_date_sort' ) );
		add_filter( 'pre_get_posts', array( $this, 'job_admin_orderby_deadline' ) );

		// Add column to frontend
		add_filter( 'job_manager_job_dashboard_columns', array( $this, 'job_dashboard_columns' ) );
		add_action( 'job_manager_job_dashboard_column_closing_date', array( $this, 'job_dashboard_column_closing_date' ) );
		add_action( 'job_manager_job_dashboard_column_expires_or_closing_date', array( $this, 'job_dashboard_column_expires_or_closing_date' ) );

		// Order by
		add_filter( 'get_job_listings_query_args', array( $this, 'get_job_listings_query_args' ) );

		// Renewals.
		add_filter( 'job_manager_renewal_expiry_date', [ $this, 'update_application_deadline' ], 10, 2 );
		add_filter( 'job_manager_job_can_be_renewed', [ $this, 'job_can_be_renewed' ], 10, 2 );
	}

	/**
	 * Checks WPJM core version.
	 */
	public function version_check() {
		if ( ! class_exists( 'WP_Job_Manager' ) || ! defined( 'JOB_MANAGER_VERSION' ) ) {
			$screen = get_current_screen();
			if ( null !== $screen && 'plugins' === $screen->id ) {
				$this->display_error( __( '<em>WP Job Manager - Application Deadline</em> requires WP Job Manager to be installed and activated.', 'wp-job-manager-application-deadline' ) );
			}
		} elseif (
			/**
			 * Filters if WPJM core's version should be checked.
			 *
			 * @since 1.2.0
			 *
			 * @param bool   $do_check                       True if the add-on should do a core version check.
			 * @param string $minimum_required_core_version  Minimum version the plugin is reporting it requires.
			 */
			apply_filters( 'job_manager_addon_core_version_check', true, self::JOB_MANAGER_CORE_MIN_VERSION )
			&& version_compare( JOB_MANAGER_VERSION, self::JOB_MANAGER_CORE_MIN_VERSION, '<' )
		) {
			$this->display_error( sprintf( __( '<em>WP Job Manager - Application Deadline</em> requires WP Job Manager %1$s (you are using %2$s).', 'wp-job-manager-application-deadline' ), self::JOB_MANAGER_CORE_MIN_VERSION, JOB_MANAGER_VERSION ) );
		}
	}

	/**
	 * Display error message notice in the admin.
	 *
	 * @param string $message
	 */
	private function display_error( $message ) {
		echo '<div class="error">';
		echo '<p>' . $message . '</p>';
		echo '</div>';
	}

	/**
	 * Handle sorting
	 *
	 * @param  array $args
	 * @return array
	 */
	public function get_job_listings_query_args( $args ) {
		if ( $args['orderby'] == 'deadline' ) {
			$args['meta_key']     = '_application_deadline';
			$args['meta_value']   = '';
			$args['meta_compare'] = '';
			$args['orderby']      = array(
				'meta_value' => $args['order'],
				'post_date'  => $args['order'],
			);
		}

		return $args;
	}

	/**
	 * Update the application deadline in a similar way with expiry during renewals.
	 *
	 * @param string  $new_expiry The new expiry date.
	 * @param WP_Post $job        The job that is being renewed.
	 */
	public function update_application_deadline( $new_expiry, $job ) {
		$application_deadline = date_create_immutable_from_format( 'Y-m-d', get_post_meta( $job->ID, '_application_deadline', true ) );

		if ( empty( $application_deadline ) ) {
			return $new_expiry;
		}

		update_post_meta( $job->ID, '_application_deadline', calculate_job_expiry( $job->ID, false, $application_deadline ) );

		return $new_expiry;
	}

	/**
	 * Update the application deadline in a similar way with expiry during renewals.
	 *
	 * @param string  $can_be_renewed Whether the job can be renewed.
	 * @param WP_Post $job            The job.
	 */
	public function job_can_be_renewed( $can_be_renewed, $job ) {
		if ( ! get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			return $can_be_renewed;
		}

		$application_deadline = date_create_immutable_from_format( 'Y-m-d', get_post_meta( $job->ID, '_application_deadline', true ) );

		if ( empty( $application_deadline ) ) {
			return $can_be_renewed;
		}

		$expiring_soon_days = get_option( 'job_manager_renewal_days', 5 );
		$current_time_stamp = current_datetime()->getTimestamp();
		$status             = get_post_status( $job );

		if ( 'publish' === $status && $application_deadline->getTimestamp() - $current_time_stamp < $expiring_soon_days * DAY_IN_SECONDS ) {
			return true;
		}

		return $can_be_renewed;
	}
	/**
	 * Add Settings
	 *
	 * @param  array $settings
	 * @return array
	 */
	public function settings( $settings = array() ) {
		$settings['job_listings'][1][] = array(
			'name'     => 'job_manager_expire_when_deadline_passed',
			'std'      => '0',
			'label'    => __( 'Automatic deadline expiry', 'wp-job-manager-application-deadline' ),
			'cb_label' => __( 'Enable automatic expiration', 'wp-job-manager-application-deadline' ),
			'desc'     => __( 'Enable this option to automatically expire jobs when application closing dates pass.', 'job_manager_tags' ),
			'type'     => 'checkbox',
		);

		return $settings;
	}

	/**
	 * Create cron jobs
	 */
	public function cron() {
		wp_clear_scheduled_hook( 'check_application_deadlines' );

		$timestamp  = strtotime( 'midnight' );
		$timestamp -= get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS;

		wp_schedule_event( $timestamp, 'daily', 'check_application_deadlines' );
	}

	/**
	 * Expire jobs
	 */
	public function check_application_deadlines() {
		global $wpdb;

		if ( ! get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			return;
		}

		// Change status to expired
		$job_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
			SELECT postmeta.post_id FROM {$wpdb->postmeta} as postmeta
			LEFT JOIN {$wpdb->posts} as posts ON postmeta.post_id = posts.ID
			WHERE postmeta.meta_key = '_application_deadline'
			AND postmeta.meta_value > 0
			AND postmeta.meta_value < %s
			AND posts.post_status = 'publish'
			AND posts.post_type = 'job_listing'
		",
				date( 'Y-m-d', current_time( 'timestamp' ) )
			)
		);

		if ( $job_ids ) {
			foreach ( $job_ids as $job_id ) {
				$job_data                = array();
				$job_data['ID']          = $job_id;
				$job_data['post_status'] = 'expired';
				wp_update_post( $job_data );
			}
		}
	}

	/**
	 * Enqueues
	 */
	public function frontend_scripts() {
		wp_enqueue_style( 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/smoothness/jquery-ui.css', false, '1.0', false );
		wp_enqueue_style( 'jm-application-deadline', JOB_MANAGER_APPLICATION_DEADLINE_PLUGIN_URL . '/assets/dist/css/frontend.css', false, JOB_MANAGER_APPLICATION_DEADLINE_VERSION, false );
	}

	/**
	 * Fields in admin
	 *
	 * @param  array $fields
	 * @return array
	 */
	public function admin_fields( $fields = array() ) {
		$fields['_application_deadline'] = array(
			'label'       => __( 'Application closing date', 'wp-job-manager-application-deadline' ),
			'placeholder' => '',
			'classes'     => array( 'job-manager-datepicker' ),
		);
		return $fields;
	}

	/**
	 * Localisation
	 *
	 * @access private
	 * @return void
	 */
	public function load_text_domain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-job-manager-application-deadline' );
		load_textdomain( 'wp-job-manager-application-deadline', WP_LANG_DIR . "/wp-job-manager-application-deadline/wp-job-manager-application-deadline-$locale.mo" );
		load_plugin_textdomain( 'wp-job-manager-application-deadline', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add the job deadline field to the submission form
	 *
	 * @return array
	 */
	public function deadline_field( $fields ) {
		if ( ! get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			$desc = __( 'Deadline for new applicants.', 'wp-job-manager-application-deadline' );
		} else {
			$desc = __( 'Deadline for new applicants. The listing will end automatically after this date.', 'wp-job-manager-application-deadline' );
		}

		$field_type = version_compare( JOB_MANAGER_VERSION, '1.30.0', '>=' ) ? 'date' : 'text';

		$fields['job']['job_deadline'] = array(
			'label'       => __( 'Closing date', 'wp-job-manager-application-deadline' ),
			'description' => $desc,
			'type'        => $field_type,
			'required'    => false,
			'placeholder' => '',
			'priority'    => '6.5',
		);

		return $fields;
	}

	/**
	 * validate fields
	 *
	 * @param  bool  $passed
	 * @param  array $fields
	 * @param  array $values
	 * @return bool on success, wp_error on failure
	 */
	public function validate_deadline_field( $passed, $fields, $values ) {
		$value = $values['job']['job_deadline'];

		if ( ! empty( $value ) && ( ! strtotime( $value ) || strtotime( $value ) == -1 ) ) {
			return new WP_Error( 'validation-error', __( 'Please enter a valid closing date.', 'wp-job-manager-application-deadline' ) );
		}

		return $passed;
	}

	/**
	 * Save posted deadline to the job
	 */
	public function save_deadline_field( $job_id, $values ) {
		$value = $values['job']['job_deadline'];

		update_post_meta( $job_id, '_application_deadline', $value );
	}

	/**
	 * Get the date format string to use for displaying dates. Uses the
	 * WordPress date_format option if it is set.
	 *
	 * @return string the date format string.
	 */
	private function get_date_format() {
		$date_format = get_option( 'date_format' );
		if ( ! $date_format ) {
			$date_format = 'M j, Y';
		}
		return $date_format;
	}

	/**
	 * Get Job Tags for the field when editing
	 *
	 * @param  object $job
	 * @param  class  $form
	 */
	public function get_deadline_field_data( $data, $job ) {
		$data['job']['job_deadline']['value'] = get_post_meta( $job->ID, '_application_deadline', true );
		return $data;
	}

	/**
	 * Show deadline on job pages
	 */
	public function display_the_deadline() {
		global $post;

		$deadline = get_post_meta( $post->ID, '_application_deadline', true );
		$expiring = false;
		$expired  = false;
		$date_str = null;

		if ( $deadline ) {
			$expiring_days = apply_filters( 'job_manager_application_deadline_expiring_days', 2 );
			$expiring      = ( floor( ( current_time( 'timestamp' ) - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) ) >= -$expiring_days );
			$expired       = ( floor( ( current_time( 'timestamp' ) - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) ) > 0 );
			$date_str      = date_i18n( $this->get_date_format(), strtotime( $deadline ) );
		}

		// Do not display anything if listing is already expired.
		if ( is_singular( 'job_listing' ) && $expired ) {
			return;
		}

		$timestamp = strtotime( $deadline );

		/**
		 * Filters the display string for the application closing date.
		 *
		 * @since 1.2.1
		 *
		 * @param string $date_str  The default date string to be displayed.
		 * @param string $timestamp The timestamp of the closing date.
		 */
		$date_str = apply_filters( 'job_manager_application_deadline_closing_date_display', $date_str, $timestamp );

		if ( $date_str ) {
			echo '<li class="application-deadline ' . ( $expiring ? 'expiring' : '' ) . ' ' . ( $expired ? 'expired' : '' ) . '"><label>' . ( $expired ? __( 'Closed', 'wp-job-manager-application-deadline' ) : __( 'Closes', 'wp-job-manager-application-deadline' ) ) . ':</label> ' . $date_str . '</li>';
		}
	}

	/**
	 * Can candidates apply?
	 *
	 * @param  bool $can_apply
	 * @return bool
	 */
	public function job_manager_candidates_can_apply( $can_apply ) {
		global $post;

		$deadline = get_post_meta( $post->ID, '_application_deadline', true );
		if ( $deadline ) {
			$days_expired = floor( ( current_time( 'timestamp' ) - strtotime( $deadline ) ) / ( 60 * 60 * 24 ) );
			$expired      = $days_expired > 0;

			if ( $expired ) {
				$can_apply = false;
			}
		}
		return $can_apply;
	}

	/**
	 * Add a job tag column to admin
	 *
	 * @return array
	 */
	public function columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			if ( 'job_expires' === $key ) {
				$new_columns['job_deadline'] = __( 'Closing', 'wp-job-manager-application-deadline' );

				if ( get_option( 'job_manager_expire_when_deadline_passed' ) ) {
					$new_columns['job_expires_or_closing_date'] = __( 'Expires', 'wp-job-manager-application-deadline' );
				}
			}
			$new_columns[ $key ] = $value;
		}

		if ( get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			unset( $new_columns['job_expires'] );
		}

		return $new_columns;
	}

	/**
	 * Handle display of new column
	 *
	 * @param  string $column
	 */
	public function custom_columns( $column ) {
		global $post;

		if ( 'job_deadline' === $column ) {
			if ( ! ( $deadline = get_post_meta( $post->ID, '_application_deadline', true ) ) ) {
				echo '<span class="na">&ndash;</span>';
			} else {
				echo date_i18n( $this->get_date_format(), strtotime( $deadline ) );
			}
		} elseif ( 'job_expires_or_closing_date' === $column ) {
			$timestamps = array();
			$deadline   = get_post_meta( $post->ID, '_application_deadline', true );

			if ( $deadline ) {
				$timestamps[] = strtotime( $deadline );
			}

			if ( $post->_job_expires ) {
				$timestamps[] = strtotime( $post->_job_expires );
			}

			sort( $timestamps );

			echo count( $timestamps ) > 0 ? date_i18n( get_option( 'date_format' ), $timestamps[0] ) : '&ndash;';
		}
	}

	/**
	 * Change frontend columns
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function job_dashboard_columns( $columns ) {
		unset( $columns['expires'] );

		$columns['closing_date'] = __( 'Closing Date', 'wp-job-manager-application-deadline' );

		if ( ! get_option( 'job_manager_expire_when_deadline_passed' ) ) {
			$columns['expires'] = __( 'Listing Expires', 'wp-job-manager' );
		} else {
			$columns['expires_or_closing_date'] = __( 'Listing Expires', 'wp-job-manager' );
		}

		return $columns;
	}

	/**
	 * Output closing date
	 *
	 * @param  object $job
	 */
	public function job_dashboard_column_closing_date( $job ) {
		if ( ! ( $deadline = get_post_meta( $job->ID, '_application_deadline', true ) ) ) {
			echo '<span class="na">&ndash;</span>';
		} else {
			echo date_i18n( get_option( 'date_format' ), strtotime( $deadline ) );
		}
	}

	/**
	 * Output expires or closing date
	 *
	 * @param  object $job
	 */
	public function job_dashboard_column_expires_or_closing_date( $job ) {
		$timestamps = array();
		$deadline   = get_post_meta( $job->ID, '_application_deadline', true );

		if ( $deadline ) {
			$timestamps[] = strtotime( $deadline );
		}

		if ( $job->_job_expires ) {
			$timestamps[] = strtotime( $job->_job_expires );
		}

		sort( $timestamps );

		echo count( $timestamps ) > 0 ? date_i18n( get_option( 'date_format' ), $timestamps[0] ) : '&ndash;';
	}

	/**
	 * Make Closing date column sortable.
	 *
	 * @param array $columns Sortable columns
	 *
	 * @return array Sortable columns
	 */
	public function job_admin_closing_date_sort( $columns ) {
		$columns['job_deadline'] = 'deadline';
		return $columns;
	}

	/**
	 * Add support to order by deadline
	 *
	 * @param WP_Query $query The query.
	 */
	public function job_admin_orderby_deadline( $query ) {
		if ( isset( $query->query['post_type'] ) ) {
			if ( is_admin() && 'job_listing' === $query->query['post_type'] && 'deadline' === $query->get( 'orderby' ) ) {
				$query->set( 'meta_key', '_application_deadline' );
				$query->set( 'orderby', 'meta_value' );
			}
		}
	}


}

$GLOBALS['job_manager_application_deadline'] = new WP_Job_Manager_Application_Deadline();

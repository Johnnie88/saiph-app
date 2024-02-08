<?php

namespace sMyles\WPJM\EMC;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Cleaner {

	/**
	 * @var string
	 */
	public static $version = '1.0.1';

	/**
	 * @var \sMyles\WPJM\EMC\Admin\Job|\sMyles\WPJM\EMC\Admin\Resume|\sMyles\WPJM\EMC\Admin\Plugins\Cariera|\sMyles\WPJM\EMC\Admin\Plugins\AFJCL|\sMyles\WPJM\EMC\Admin\Plugins\CM|\sMyles\WPJM\EMC\Admin\Plugins\MASCM
	 */
	public $emc;

	/**
	 * @var array
	 */
	public $meta_keys = array();

	/**
	 * @var string
	 */
	public $post_type = 'job_listing';
	/**
	 * @var string Path to assets (js/css)
	 */
	public $assets_path = '';

	/**
	 * @var null|\sMyles\WPJM\EMC\Cleaner
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return Cleaner A single instance of this class.
	 * @since  1.0.0
	 */
	public static function get_instance( $assets ) {

		if ( null === self::$single_instance ) {
			self::$single_instance = new self( $assets );
		}

		return self::$single_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct( $assets ) {

		if ( ! is_admin() || ! function_exists( 'WPJM_Empty_Meta_Cleanup' ) ) {
			return;
		}

		$this->assets_path = $assets;

		add_action( 'job_manager_empty_meta_cleanup_handler_output', array( $this, 'cleaner_output' ), 10, 2 );
		add_filter( 'job_manager_empty_meta_cleanup_handler_output_show_default', '__return_false' );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( "wp_ajax_job_manager_empty_meta_do_count", array( $this, 'handle_count' ) );
		add_action( "wp_ajax_job_manager_empty_meta_do_cleanup", array( $this, 'handle_cleanup' ) );
		add_action( "wp_ajax_job_manager_empty_meta_get_data", array( $this, 'get_data' ) );

		if ( isset( $_GET['test'] ) ) {
			$this->do_count();
		}
	}

	/**
	 * Get Data
	 *
	 * @since 1.0.0
	 *
	 */
	public function get_data() {

		check_ajax_referer( 'wpjmemc_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to do this.' );
		}

		$type = ucfirst( sanitize_text_field( $_POST['type'] ) );

		$class = "\\sMyles\\WPJM\\EMC\\{$type}";

		if ( ! class_exists( $class ) ) {
			wp_send_json_error( __( 'Unable to find correct class, please try again or contact support!', 'wp-job-manager-field-editor' ) );
		}

		$instance = $class::get_instance();

		$frontend_fields = array_keys( $instance->get_fields( true ) );
		$admin_fields    = array_keys( $instance->admin->get_fields() );

		/**
		 * Convert meta key to actual meta key stored in database (include prepended underscore)
		 */
		$frontend_fields = array_map( function( $mk ) { return "_{$mk}"; }, $frontend_fields );

		$meta_keys = array_unique( array_merge( $frontend_fields, $admin_fields ) );

		$data = array(
			'meta_keys' => $meta_keys
		);

		wp_send_json_success( $data );
	}

	/**
	 * API Call for Counts
	 *
	 * @since 1.0.0
	 *
	 */
	public function handle_count() {

		check_ajax_referer( 'wpjmemc_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to do this.' );
		}

		if ( ! isset( $_POST['meta_keys'] ) || empty( $_POST['meta_keys'] ) ) {
			wp_send_json_error( __( 'You must specify meta keys to search for in the database!', 'wp-job-manager-field-editor' ) );
		}

		$this->post_type = sanitize_text_field( $_POST['post_type'] );
		$this->meta_keys = array_map( 'sanitize_text_field', $_POST['meta_keys'] );

		$counts = $this->do_count();
		if ( empty( $counts ) || empty( $counts['results'] ) ) {
			wp_send_json_error( __( 'No empty meta values found in database!  You are good to go!', 'wp-job-manager-field-editor' ) );
		}

		wp_send_json_success( $counts );
	}

	/**
	 * API Call for Cleanup
	 *
	 * @since 1.0.0
	 *
	 */
	public function handle_cleanup() {

		global $wpdb;

		check_ajax_referer( 'wpjmemc_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to do this.' );
		}

		if ( ! isset( $_POST['meta_db_ids'] ) ) {
			wp_send_json_error( __( 'Meta db IDs are required!', 'wp-job-manager-field-editor' ) );
		}

		$meta_db_ids = implode( ',', array_map( 'absint', $_POST['meta_db_ids'] ) );

		$result = $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_id IN($meta_db_ids)" );

		wp_send_json_success( array( 'removed' => $result ) );
	}

	/**
	 * Implode with Quotes
	 *
	 * @param $str
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function implode_with_quotes( $str ) {

		return sprintf( "'%s'", implode( "','", $str ) );
	}

	/**
	 * Get Total Count of Found Meta Rows
	 *
	 * @return string|null
	 * @since 1.0.0
	 *
	 */
	public function do_count() {

		global $wpdb;

		$wpdb->flush();

		/**
		 * By passing other values here you can define what is considered an "empty" value.  This can be things like hashtag, or any other STRING
		 * value.
		 */
		$extra_empty_values = apply_filters( 'job_manager_empty_meta_cleaner_extra_empty_values', array(), $this );
		$empty_values_IN    = $this->implode_with_quotes( $extra_empty_values );

		if ( count( $extra_empty_values ) > 0 ) {
			$query = "
            SELECT  $wpdb->postmeta.meta_id, $wpdb->postmeta.meta_key
	        FROM    $wpdb->postmeta
	        INNER JOIN $wpdb->posts
	            ON $wpdb->posts.ID = $wpdb->postmeta.post_id
	        WHERE   
	        	$wpdb->posts.post_type = '{$this->post_type}'
            	AND $wpdb->postmeta.meta_value IN( '', " . $empty_values_IN . ")
	            AND $wpdb->postmeta.meta_key IN(" . implode( ', ', array_fill( 0, count( $this->meta_keys ), '%s' ) ) . ")
			";
		} else {
			$query = "
            SELECT  $wpdb->postmeta.meta_id, $wpdb->postmeta.meta_key
	        FROM    $wpdb->postmeta
	        INNER JOIN $wpdb->posts
	            ON $wpdb->posts.ID = $wpdb->postmeta.post_id
	        WHERE   
	        	$wpdb->posts.post_type = '{$this->post_type}'
            	AND $wpdb->postmeta.meta_value = ''
	            AND $wpdb->postmeta.meta_key IN(" . implode( ', ', array_fill( 0, count( $this->meta_keys ), '%s' ) ) . ")
			";
		}

		$prep_query = $wpdb->prepare( $query, $this->meta_keys );

		$result = $wpdb->get_results( $prep_query, 'ARRAY_A' );

		$meta_keys = array_column( $result, 'meta_key' );
		$meta_ids  = array_column( $result, 'meta_id' );

		$counts = array_count_values( $meta_keys );

		$formatted_counts = array(
			'meta_db_ids' => $meta_ids,
			'results'     => array()
		);

		foreach ( (array) $counts as $meta_key => $count ) {
			$formatted_counts['results'][] = array(
				'meta_key' => $meta_key,
				'count'    => $count
			);
		}

		return $formatted_counts;
	}

	/**
	 * Register Assets (CSS/JS)
	 *
	 * @since 1.0.0
	 *
	 */
	public function register_assets() {

		$assets_path = apply_filters( 'job_manager_empty_meta_cleaner_assets_path', $this->assets_path, $this );
		if ( ! $assets_path ) {
			return;
		}

		$min = defined( 'SMYLES_DEVN' ) && SMYLES_DEVN ? '' : '.min';

		wp_register_style( 'wpjm-emc', $assets_path . "/css/meta{$min}.css", array(), self::$version );
		wp_register_script( 'wpjm-emc-vendor', $assets_path . "/js/vendor{$min}.js", array(), self::$version, true );
		wp_register_script( 'wpjm-emc', $assets_path . "/js/meta{$min}.js", array( 'wpjm-emc-vendor' ), self::$version, true );

		wp_localize_script( 'wpjm-emc', "wpjmemc_admin", array( 'nonce' => wp_create_nonce( "wpjmemc_admin_nonce" ), 'assets' => $assets_path . '/js/' ) );

	}

	/**
	 * Get Current Admin URL
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function get_current_admin_url() {

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );

		if ( ! $uri ) {
			return '';
		}

		return remove_query_arg( array( '_wpnonce', '_wc_notice_nonce', 'wc_db_update', 'wc_db_update_nonce', 'wc-hide-notice', 'empty_meta_cleanup' ), admin_url( $uri ) );
	}

	/**
	 * Output Handling
	 *
	 * @param $that
	 * @param $settings
	 *
	 * @since 1.0.0
	 */
	public function cleaner_output( $that, $settings ) {

		$this->emc = $that;

		if ( ! current_user_can( 'manage_options' ) ) {
			echo '<strong>' . __( 'You do not have high enough permissions to perform empty meta cleanup (for existing listings) in the database.  Please use an account that has "manage_options" capabilities/permissions.', 'wp-job-manager-field-editor' ) . '</strong>';

			return;
		}

		if ( ! isset( $_GET['empty_meta_cleanup'] ) ) {
			$url = $this->get_current_admin_url();
			$url = add_query_arg( 'empty_meta_cleanup', true, $url );
			if ( strpos( $url, '#settings-' . $settings->get_tab_slug() ) === false ) {
				$url .= '#settings-' . $settings->get_tab_slug();
			}
			echo "<a class=\"button button-primary\" href=\"{$url}\">" . __( 'Show Existing Meta Cleaner', 'wp-job-manager-field-editor' ) . '</a>';
			echo '<p class="description">' . __( 'To prevent excessive queries and load time on settings page load, the existing meta cleaner is only initialized when you click the button above.', 'wp-job-manager-field-editor' ) . '</p>';

			return;
		}

		$data = array(
			'type'            => $this->emc->type->slug,
			'post_type'       => $this->emc->type->post_type,
			'total_per_batch' => 25
		);

		$data = apply_filters( 'job_manager_empty_meta_cleaner_init_configuration', $data, $this );

		wp_enqueue_style( 'wpjm-emc' );
		wp_enqueue_script( 'wpjm-emc' );
		echo "<div id=\"job-manager-empty-meta-cleaner-{$data['post_type']}\" class=\"job-manager-empty-meta-cleaner\"><job-manager-empty-meta-cleaner post-type=\"{$data['post_type']}\" type=\"{$data['type']}\" total-per-batch=\"{$data['total_per_batch']}\"></job-manager-empty-meta-cleaner></div>";
	}
}
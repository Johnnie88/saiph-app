<?php
/**
 * Job Alerts Admin functionality.
 *
 * @package wp-job-manager-alerts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP_Job_Manager_Alerts_Admin class.
 *
 * @package WP_Job_Manager_Alerts
 */
class WP_Job_Manager_Alerts_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'manage_job_alert_posts_columns', [ $this, 'add_job_alert_columns' ] );
		add_action( 'manage_job_alert_posts_custom_column', [ $this, 'populate_job_alert_columns' ], 10, 2 );
		add_filter( 'display_post_states', [ $this, 'modify_job_alert_post_state' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 20 );
		add_filter( 'quick_edit_show_taxonomy', [ $this, 'hide_taxonomy_from_quick_edit' ], 10, 3 );
		add_action( 'add_meta_boxes_job_alert', [ $this, 'hide_taxonomies_from_edit' ], 100 );
		add_filter( 'posts_search', [ $this, 'search_by_email_where' ], 10, 2 );
		add_filter( 'posts_join', [ $this, 'search_by_email_join' ], 10, 2 );

	}

	/**
	 * Check if the query is for the job_alert screen.
	 *
	 * @param WP_Query $query
	 *
	 * @return bool
	 */
	private static function is_job_alert_admin_query( $query ) {
		return $query->is_main_query() && is_admin() && 'job_alert' === $query->get( 'post_type' );
	}

	/**
	 * Add WHERE terms to the job alert search to allow searching by author email address.
	 *
	 * @access private.
	 *
	 * @param string   $where SQL WHERE clauses.
	 * @param WP_Query $query The WP_Query instance.
	 *
	 * @return string
	 */
	public function search_by_email_where( $where, $query ) {
		global $wpdb;

		$search = $query->get( 's' );
		if ( self::is_job_alert_admin_query( $query ) && ! empty( $search ) ) {

			$condition = " {$wpdb->users}.user_email LIKE '%$search%'";

			if ( ! empty( $where ) ) {
				$where = preg_replace( '/^ AND /', '', $where );
				$where = " AND ( {$where} OR ( {$condition} ) )";
			} else {
				$where = " AND ( {$condition} )";
			}
		}

		return $where;
	}

	/**
	 * JOIN the users table for the job alert search to allow searching by author email address.
	 *
	 * @access private.
	 *
	 * @param string   $join SQL JOIN clauses.
	 * @param WP_Query $query The WP_Query instance.
	 *
	 * @return string
	 */
	public function search_by_email_join( $join, $query ) {
		global $wpdb;

		if ( self::is_job_alert_admin_query( $query ) && ! empty( $query->get( 's' ) ) ) {
			$join .= " LEFT JOIN {$wpdb->users} ON {$wpdb->posts}.post_author = {$wpdb->users}.ID";
		}

		return $join;
	}

	/**
	 * Enqueue scripts for job alerts admin.
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( in_array( $screen->id, [ 'edit-job_alert', 'job_alert' ], true ) ) {
			wp_enqueue_style( 'job_manager_alerts_admin_css', JOB_MANAGER_ALERTS_PLUGIN_URL . '/assets/dist/css/admin.css', [], JOB_MANAGER_ALERTS_VERSION );
		}
	}

	/**
	 * Customize columns in the job alerts table.
	 *
	 * @access private
	 *
	 * @param array $columns Columns.
	 *
	 * @return array
	 */
	public function add_job_alert_columns( $columns ) {
		$new_columns = array(
			'cb'     => $columns['cb'],
			'title'  => $columns['title'],
			'terms'  => __( 'Search Terms', 'wp-job-manager-alerts' ),
			'author' => __( 'User', 'wp-job-manager-alerts' ),
		);
		unset( $columns['author'] );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * Populate job alert columns.
	 *
	 * @access private
	 *
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 */
	public function populate_job_alert_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'terms':
				$terms = WP_Job_Manager_Alerts_Post_Types::get_alert_search_term_names( $post_id );

				$tags = array();
				foreach ( $terms as $taxonomy => $term_names ) {
					if ( ! empty( $term_names ) ) {
						foreach ( $term_names as $term ) {
							$tags[] = '<span class="jm-alert__term ' . esc_attr( $taxonomy ) . '">' . esc_html( $term ) . '</span>';
						}
					}
				}

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the loop above.
				echo implode( ' ', $tags );

				break;
		}
	}

	/**
	 * Display draft post state label as 'Disabled'.
	 *
	 * @access private
	 *
	 * @param array   $post_states Post states.
	 * @param WP_Post $post Post object.
	 *
	 * @return array
	 */
	public function modify_job_alert_post_state( $post_states, $post ) {
		if ( 'job_alert' === $post->post_type && 'draft' === $post->post_status ) {
			$post_states = array( __( 'Disabled', 'wp-job-manager-alerts' ) );
		}

		return $post_states;
	}

	/**
	 * Hide taxonomy meta boxes from the quick edit section.
	 *
	 * @access private
	 *
	 * @param bool   $show
	 * @param string $taxonomy
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function hide_taxonomy_from_quick_edit( $show, $taxonomy, $post_type ) {
		if ( 'job_alert' === $post_type && in_array(
			$taxonomy,
			[
				'job_listing_category',
				'job_listing_type',
				'job_listing_region',
				'job_listing_tag',
			],
			true
		) ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Hide taxonomy meta boxes from the edit screen.
	 *
	 * @access private
	 */
	public function hide_taxonomies_from_edit() {

		$post_type = 'job_alert';
		foreach ( [ 'job_listing_category', 'job_listing_type', 'job_listing_region', 'job_listing_tag' ] as $taxonomy ) {
			remove_meta_box( $taxonomy . 'div', $post_type, 'side' );
		}
	}
}

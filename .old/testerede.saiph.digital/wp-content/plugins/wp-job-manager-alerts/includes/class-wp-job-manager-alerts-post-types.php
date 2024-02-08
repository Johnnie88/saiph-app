<?php
/**
 * Job Alerts post types.
 *
 * @package wp-job-manager-alerts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP_Job_Manager_Alerts_Post_Types class.
 */
class WP_Job_Manager_Alerts_Post_Types {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_post_types' ], 20 );
		add_filter( 'post_types_to_delete_with_user', [ $this, 'post_types_to_delete_with_user' ] );

	}

	/**
	 * Register 'job_alert' post type.
	 */
	public function register_post_types() {
		if ( post_type_exists( 'job_alert' ) ) {
			return;
		}

		register_post_type(
			'job_alert',
			apply_filters(
				'register_post_type_job_alert',
				array(
					'public'              => false,
					'show_ui'             => true,
					'capability_type'     => 'post',
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'show_in_menu'        => 'edit.php?post_type=job_listing',
					'show_in_admin_bar'   => false,
					'supports'            => [ 'author', 'title', 'custom-fields' ],
					'has_archive'         => false,
					'show_in_nav_menus'   => false,
					'delete_with_user'    => true,
					'label'               => __( 'Job Alerts', 'wp-job-manager-alerts' ),
					'labels'              => [
						'name'               => __( 'Job Alert', 'wp-job-manager-alerts' ),
						'singular_name'      => __( 'Job Alerts', 'wp-job-manager-alerts' ),
						'search_items'       => __( 'Search Alerts', 'wp-job-manager-alerts' ),
						'edit_item'          => __( 'Edit Alert', 'wp-job-manager-alerts' ),
						'new_item'           => __( 'New Alert', 'wp-job-manager-alerts' ),
						'all_items'          => __( 'All Alerts', 'wp-job-manager-alerts' ),
						'view'               => __( 'View', 'wp-job-manager-alerts' ),
						'view_item'          => __( 'View Alert', 'wp-job-manager-alerts' ),
						'not_found'          => __( 'No alerts found', 'wp-job-manager-alerts' ),
						'not_found_in_trash' => __( 'No alerts found in Trash', 'wp-job-manager-alerts' ),
					],
					'capabilities'        => array(
						'create_posts' => false,
						'edit_post'    => 'manage_job_listings',
						'delete_post'  => 'manage_job_listings',
					),
				)
			)
		);

		if ( taxonomy_exists( 'job_listing_category' ) ) {
			register_taxonomy_for_object_type( 'job_listing_category', 'job_alert' );
		}

		register_taxonomy_for_object_type( 'job_listing_type', 'job_alert' );
	}

	/**
	 * Filter post types to delete when removing a user to also remove
	 * `job_alert` posts.
	 *
	 * @access private
	 *
	 * @param string[] $post_types_to_delete Post types do delete.
	 *
	 * @return string[] Post types do delete.
	 */
	public function post_types_to_delete_with_user( $post_types_to_delete ) {
		$post_types_to_delete[] = 'job_alert';

		return $post_types_to_delete;
	}

	/**
	 * Fetches the terms used in the search query for the alert.
	 *
	 * @since 1.5.0
	 *
	 * @param int $alert_id
	 *
	 * @return array
	 */
	public static function get_alert_search_terms( $alert_id ) {
		$base_terms = array(
			'categories' => array(),
			'regions'    => array(),
			'tags'       => array(),
			'types'      => array(),
		);
		if ( metadata_exists( 'post', $alert_id, 'alert_search_terms' ) ) {
			return array_merge( $base_terms, get_metadata( 'post', $alert_id, 'alert_search_terms', true ) );
		}

		return array_merge( $base_terms, self::get_legacy_search_terms( $alert_id ) );
	}

	/**
	 * Fetches the terms, populated with display names, for the search query for the alert.
	 *
	 * @since 2.0.0
	 *
	 * @param int $alert_id
	 *
	 * @return array|array[]
	 */
	public static function get_alert_search_term_names( $alert_id ) {
		$terms = self::get_alert_search_terms( $alert_id );

		foreach ( $terms as $key => $term_ids ) {
			if ( ! empty( $term_ids ) ) {
				$term_names = array();
				foreach ( $term_ids as $term_id ) {
					$term = get_term( $term_id );
					if ( $term && ! is_wp_error( $term ) ) {
						$term_names[] = $term->name;
					}
				}
				$terms[ $key ] = $term_names;
			}
		}

		$keyword = get_post_meta( $alert_id, 'alert_keyword', true );
		if ( ! empty( $keyword ) ) {
			$keyword = sprintf( '"%s"', $keyword );
		}

		if ( empty( $terms['regions'] ) ) {
			$location = get_post_meta( $alert_id, 'alert_location', true );
			if ( ! empty( $location ) ) {
				$terms = array_merge( [ 'location' => [ $location ] ], $terms );
			}
		}

		return array_merge( [ 'keywords' => [ $keyword ] ], $terms );
	}

	/**
	 * Fetches the frequency label for the alert.
	 *
	 * @param int $alert_id Post ID for the alert.
	 *
	 * @return string
	 */
	public static function get_frequency_name( $alert_id ) {
		$schedules = WP_Job_Manager_Alerts_Notifier::get_alert_schedules();
		$freq      = get_post_meta( $alert_id, 'alert_frequency', true );

		return $schedules[ $freq ]['display'] ?? '';
	}

	/**
	 * Prior to 1.5.0, alerts were associated with terms. This attempts to fetch those legacy associations and covert
	 * them to the new meta data format. This also clears the taxonomy associations.
	 *
	 * @since 1.5.0
	 *
	 * @param int $alert_id
	 *
	 * @return array
	 */
	private static function get_legacy_search_terms( $alert_id ) {
		$search_terms      = array();
		$taxonomy_type_map = array(
			'categories' => 'job_listing_category',
			'regions'    => 'job_listing_region',
			'tags'       => 'job_listing_tag',
			'types'      => 'job_listing_type',
		);
		foreach ( $taxonomy_type_map as $key => $taxonomy_type ) {
			if ( taxonomy_exists( $taxonomy_type ) ) {
				$terms = array_filter( (array) wp_get_post_terms( $alert_id, $taxonomy_type, array( 'fields' => 'ids' ) ) );
				if ( count( $terms ) > 0 ) {
					$search_terms[ $key ] = $terms;
				}

				// Remove legacy post terms.
				wp_set_post_terms( $alert_id, array(), $taxonomy_type );
			}
		}
		update_post_meta( $alert_id, 'alert_search_terms', $search_terms );

		return $search_terms;
	}
}

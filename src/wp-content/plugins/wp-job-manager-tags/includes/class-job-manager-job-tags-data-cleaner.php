<?php
/**
 * File containing the class WP_Job_Manager_Job_Tags_Data_Cleaner.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Methods for cleaning up all plugin data.
 *
 * @author Automattic
 * @since 1.4.3
 */
class WP_Job_Manager_Job_Tags_Data_Cleaner {

	/**
	 * Taxonomies to be deleted.
	 *
	 * @var $taxonomies
	 */
	private static $taxonomies = [
		'job_listing_tag',
	];

	/**
	 * Options to be deleted.
	 *
	 * @var $options
	 */
	private static $options = [
		'job_manager_enable_tag_archive',
		'job_manager_max_tags',
		'job_manager_tag_input',
	];

	/**
	 * Cleanup all data.
	 *
	 * @access public
	 */
	public static function cleanup_all() {
		self::cleanup_taxonomies();
		self::cleanup_options();
	}

	/**
	 * Cleanup data for taxonomies.
	 *
	 * @access private
	 */
	private static function cleanup_taxonomies() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
		foreach ( self::$taxonomies as $taxonomy ) {
			$terms = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT term_id, term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s",
					$taxonomy
				)
			);

			// Delete all data for each term.
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_relationships, [ 'term_taxonomy_id' => $term->term_taxonomy_id ] );
				$wpdb->delete( $wpdb->term_taxonomy, [ 'term_taxonomy_id' => $term->term_taxonomy_id ] );
				$wpdb->delete( $wpdb->terms, [ 'term_id' => $term->term_id ] );
				$wpdb->delete( $wpdb->termmeta, [ 'term_id' => $term->term_id ] );
			}

			if ( function_exists( 'clean_taxonomy_cache' ) ) {
				clean_taxonomy_cache( $taxonomy );
			}
		}

		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Cleanup data for options.
	 *
	 * @access private
	 */
	private static function cleanup_options() {
		foreach ( self::$options as $option ) {
			delete_option( $option );
		}
	}
}

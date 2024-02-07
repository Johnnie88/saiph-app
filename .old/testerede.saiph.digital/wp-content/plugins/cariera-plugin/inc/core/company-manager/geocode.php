<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Geocode {

	/**
	 * Constructor.
	 *
	 * @since 1.4.6
	 */
	public function __construct() {
		add_action( 'cariera_update_company_data', [ $this, 'update_location_data' ], 20, 2 );
		add_action( 'cariera_company_manager_company_location_edited', [ $this, 'change_location_data' ], 20, 2 );
	}

	/**
	 * Update location data - when submitting a company
	 *
	 * @since 1.4.6
	 */
	public function update_location_data( $company_id, $values ) {
		if ( apply_filters( 'cariera_company_manager_geolocation_enabled', true ) ) {
			$address_data = \WP_Job_Manager_Geocode::get_location_data( $values['company_fields']['company_location'] );
			\WP_Job_Manager_Geocode::save_location_data( $company_id, $address_data );
		}
	}

	/**
	 * Change a companies location data upon editing
	 *
	 * @since 1.4.6
	 */
	public function change_location_data( $company_id, $new_location ) {
		if ( apply_filters( 'cariera_company_manager_geolocation_enabled', true ) ) {
			$address_data = \WP_Job_Manager_Geocode::get_location_data( $new_location );
			\WP_Job_Manager_Geocode::clear_location_data( $company_id );
			\WP_Job_Manager_Geocode::save_location_data( $company_id, $address_data );
		}
	}
}

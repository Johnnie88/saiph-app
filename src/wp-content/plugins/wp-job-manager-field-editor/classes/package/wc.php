<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Package_WC
 *
 * @since 1.2.2
 *
 */
class WP_Job_Manager_Field_Editor_Package_WC {

	function __construct() {

		// Listing Payments
		add_action( 'astoundify_wpjmlp_switched_subscription', array($this, 'update_package'), 10, 2 );
		// WC Advanced Paid Listings
		add_action( 'jwapl_switched_subscription', array($this, 'update_package'), 10, 2 );
		// WC Paid Listings
		add_action( 'wc_paid_listings_switched_subscription', array($this, 'update_package'), 10, 2 );
		// Claim Listings
		add_action( 'wpjmcl_switched_package', array($this, 'update_package'), 10, 2 );

		add_action( 'listify_can_upload_to_listing', array($this, 'listify_can_upload') );
		add_action( 'listify_single_job_listing_show_gallery', array($this, 'listify_can_upload') );
		add_action( 'listify_widget_panel_listing_gallery', array($this, 'listify_widget_gallery') );
		add_filter( 'jmv_get_meta_key_skips', array( $this, 'add_skip_meta_keys' ) );

		add_action( 'wcpl_process_package_for_job_listing', array( $this, 'wcpl_process_package'), 10, 3 );
		add_action( 'wcpl_process_package_for_resume', array( $this, 'wcpl_process_package'), 10, 3 );
//		add_action( 'init', array( $this, 'verify_listing_package' ), 1 );
	}

	/**
	 * Process Package for Listing
	 *
	 * This method is called by WCPL when a package processed for a listing, right before redirecting to the checkout page.
	 * Specifically we use this handling to deal with removing invalid fields when a user "relists" and is not a subscription.
	 *
	 * @param $product_id
	 * @param $falsy
	 * @param $job_id
	 *
	 * @since 1.12.10
	 *
	 */
	function wcpl_process_package( $product_id, $falsy, $job_id ){
		$this->update_package( $job_id );
	}

	/**
	 * Verify package ID matches package ID saved to listing
	 *
	 * If user submits listing up to the preview step with Package A, then goes back and selects a different package that may
	 * have less features or fields, core will automatically reload the listing ID, thus allowing a user to submit a listing.
	 * This method checks to make sure that the package the listing was originally created with, matches the current one being
	 * used. If it does not, this method will set the listing ID equal to 0, causing core to create new listing.
	 *
	 *
	 * @since 1.6.0
	 *
	 */
	function verify_listing_package() {

		// False by default
		$type = false;
		$is_edit = false;

		if( ! empty( $_POST['job_manager_form'] ) ){
			$type = 'job';

			if( $_POST['job_manager_form'] === 'edit-job' ){
				$is_edit = true;
			}

		} elseif( ! empty( $_POST['resume_manager_form'] ) ){
			$type = 'resume';

			if ( $_POST['resume_manager_form'] === 'edit-resume' ) {
				$is_edit = true;
			}
		}

		if ( ! $type ){
			return;
		}

		$listing_id = ! empty( $_REQUEST[ "{$type}_id" ] ) ? absint( $_REQUEST[ "{$type}_id" ] ) : 0;

		if ( ! $listing_id && ! empty( $_COOKIE[ "wp-job-manager-submitting-{$type}-id" ] ) ) {
			$listing_id = absint( $_COOKIE[ "wp-job-manager-submitting-{$type}-id" ] );
		}

		// If no listing id, means new listing no need to check package
		if ( empty( $listing_id ) ) {
			return;
		}

		// Attempt to get package ID
		$listing_package = FALSE;

		if ( ! empty( $_POST[ "{$type}_package" ] ) ) {

			if ( is_numeric( $_POST[ "{$type}_package" ] ) ) {
				$listing_package = absint( $_POST[ "{$type}_package" ] );
			} else {
				$listing_package = self::get_product_id( absint( substr( $_POST[ "{$type}_package" ], 5 ) ), TRUE );
			}

		// Only allow pulling from chosen_package_id when using submit page, not edit page
		} elseif ( ! $is_edit && ! empty( $_COOKIE['chosen_package_id'] ) ) {
			$listing_package = self::get_product_id( absint( $_COOKIE[ 'chosen_package_id' ] ), absint( $_COOKIE[ 'chosen_package_is_user_package' ] ) === 1 );
		}

		if ( empty( $listing_package ) ) {
			return;
		}

		// Attempt to get saved package ID from listing
		$saved_package_id = self::get_post_package_id( $listing_id );

		if ( empty( $saved_package_id ) ) {
			return;
		}

		if ( (int) $listing_package !== (int) $saved_package_id && 'before' === get_option( 'job_manager_paid_listings_flow', '' ) ) {

			$post_status = get_post_status( $listing_id );

			/**
			 * If listing is expired, do not go further as chances are user is trying to relist something.
			 *
			 * Fields not present in the package are handled using the delete_post_meta() in save_custom_fields() method.
			 */
			if( $post_status === 'expired' || $post_status === 'draft' ){
				return;
			}

			// Unset cookie values to prevent core from setting listing ID and loading values
			unset( $_COOKIE[ "wp-job-manager-submitting-{$type}-key" ] );
			unset( $_COOKIE[ "wp-job-manager-submitting-{$type}-id" ] );

			// For good measures let's set a new cookie with empty value, and expiration of yesterday
			setcookie( "wp-job-manager-submitting-{$type}-key", '', time() - DAY_IN_SECONDS, '/' );
			setcookie( "wp-job-manager-submitting-{$type}-id", '', time() - DAY_IN_SECONDS, '/' );

			// Just in case
			$_POST[ "{$type}_id" ]    = 0;
			$_GET[ "{$type}_id" ]     = 0;
			$_REQUEST[ "{$type}_id" ] = 0;
		}

	}

	/**
	 * Add meta keys to skip for WPJM Visibility
	 *
	 * If the site is using the WP Job Manager Visibility plugin there are specific meta keys that
	 * need to be added to the skip array to prevent a possible loop when Visibilities plugin gets
	 * all fields from WPJM, as this plugin filters those fields, causing a loop.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $skips
	 *
	 * @return mixed
	 */
	function add_skip_meta_keys( $skips ) {

		$wc_skips = array('_package_id', '_wcpl_jmfe_product_id', '_user_package_id');

		return $skips + $wc_skips;
	}

	/**
	 *  Filter out Listify Gallery Widget
	 *
	 *  If uploads are disabled for user to Listify Gallery, remove the widget
	 *  HTML to prevent an empty widget from being output.
	 *
	 *
	 * @since 1.3.1
	 *
	 * @param $content
	 *
	 * @return string
	 */
	function listify_widget_gallery( $content ) {

		$can_upload = $this->listify_can_upload( TRUE );
		if ( ! $can_upload ) return '';

		return $content;

	}

	/**
	 * Update JMFE product ID when subscription is switched
	 *
	 *
	 * @since 1.3.5
	 *
	 * @param $listing_id       Post ID of listing being updated
	 * @param $user_package     User package row obj/array from db
	 */
	function update_package( $listing_id, $user_package = false ) {

		$new_pkg_id = self::get_post_package_id( $listing_id, false );

		if( $new_pkg_id ) update_post_meta( $listing_id, '_wcpl_jmfe_product_id', $new_pkg_id );

		$this->remove_old_package_fields( $listing_id, $new_pkg_id );

	}

	/**
	 * Remove Old Package Meta Values from Listing
	 *
	 * When a subscription is upgraded, or downgraded, we need to check if any fields that were
	 * originally added, are still applicable for the new package.  If they are not, remove the
	 * meta for those fields from the listing.
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $listing_id
	 * @param $new_pkg_id
	 */
	function remove_old_package_fields( $listing_id, $new_pkg_id ){

		$post_type = get_post_type( $listing_id );

		$jmfe = WP_Job_Manager_Field_Editor_Fields::get_instance();

		if( $post_type == 'job_listing' ){
			$job_fields = $jmfe->get_fields( 'job', 'enabled' );
			$company_fields = $jmfe->get_fields( 'company', 'enabled' );
			$fields = array_merge( $job_fields, $company_fields );
		}

		if( $post_type == 'resume' ) $fields = $jmfe->get_fields( 'resume_fields', 'enabled' );

		$nip_fields = self::filter_fields( $fields, $new_pkg_id, TRUE );

		/**
		 * Meta Key Mappings for Meta Removal
		 *
		 * Some themes and plugins end up saving values to listings under other meta keys,
		 * this filter makes it possible to remove multiple meta keys based on a specific meta.
		 *
		 * Additional meta keys to remove must be the exact meta key value, as default meta always
		 * has an underscore prepended, but sometimes that is not the case so in this instance
		 * the underscore is NOT automatically added.
		 *
		 * Format should be
		 *     array( 'main_meta_key' => 'another_meta_key' )
		 * or
		 *     array( 'main_meta_key' => array( 'another_meta_key', '_and_another_one' )
		 *
		 *
		 * @since 1.4.2
		 *
		 * @param array $mappings Key value array of additional meta keys to remove
		 */
		$map_meta_keys = apply_filters( 'job_manager_field_editor_package_remove_old_meta', array() );

		// Loop through all fields not in package config
		foreach( $nip_fields as $field => $conf ){

			// Remove any mapped meta as well
			if( ! empty( $map_meta_keys ) && array_key_exists( $field, $map_meta_keys ) ){

				if( is_array( $map_meta_keys[$field] ) ){

					foreach( $map_meta_keys[$field] as $map_meta_key ){

						$this->maybe_unattach( $map_meta_key, $listing_id );
						delete_post_meta( $listing_id, $map_meta_key );
					}

				} else {

					$this->maybe_unattach( $map_meta_keys[ $field ], $listing_id );
					delete_post_meta( $listing_id, $map_meta_keys[ $field ] );

				}

			}

			$this->maybe_unattach( $field, $listing_id, $conf );

			// Remove meta
			delete_post_meta( $listing_id, "_{$field}" );
		}

	}

	/**
	 * Maybe unattach files from listing
	 *
	 * When a pacakge is upgraded/downgraded, some fields may be removed.  If these fields are file uploads,
	 * this method checks if that upload is attached to the listing, and if so, it sets the parent equal to 0,
	 * causing the media/file to no longer be attached to the listing.
	 *
	 *
	 * @since 1.6.0
	 *
	 * @param       $meta_key
	 * @param       $post_id
	 * @param array $conf
	 */
	function maybe_unattach( $meta_key, $post_id, $conf = array() ) {

		if ( $meta_key === 'company_logo' || $meta_key === '_company_logo' ) {
			$thumbnail_id = get_post_thumbnail_id( $post_id );
			if ( ! empty( $thumbnail_id ) && (int) $post_id === (int) wp_get_post_parent_id( $thumbnail_id ) ) {
				wp_update_post( array( 'ID' => $thumbnail_id, 'post_parent' => 0 ) );
				delete_post_meta( $post_id, '_thumbnail_id' );
			}

		}

		// If configuration is passed, let's make sure it's a file field first
		if( ! empty( $conf ) && array_key_exists( 'type', $conf ) && ! empty( $conf[ 'type' ] ) && $conf[ 'type' ] !== 'file' ){
			return;
		}

		// Attempt to get the value of this field (before it's removed)
		$meta_value = maybe_unserialize( get_post_meta( $post_id, $meta_key, true ) );

		if( empty( $meta_value ) ){
			return;
		}

		if( is_array( $meta_value ) ){

			foreach( (array) $meta_value as $meta_val ){

				$attach_id = get_attachment_id_from_url( $meta_val );
				if( empty( $attach_id ) ) continue;

				if ( (int) $post_id === (int) wp_get_post_parent_id( $attach_id ) ) {
					wp_update_post( array( 'ID' => $attach_id, 'post_parent' => 0 ) );
				}

			}

		} else {

			$attach_id = get_attachment_id_from_url( $meta_value );
			if ( ! empty( $attach_id ) && (int) $post_id === (int) wp_get_post_parent_id( $attach_id ) ) {
				wp_update_post( array( 'ID' => $attach_id, 'post_parent' => 0 ) );
			}

		}
	}

	/**
	 *  Check if listing has gallery field enabled per package
	 *
	 *
	 * @since 1.3.1
	 *
	 * @param $can
	 *
	 * @return bool
	 */
	function listify_can_upload( $can ){

		$listing_id = get_the_ID();
		if( ! $listing_id ) {
			return $can;
		}

		$listing_package = self::get_post_package_id( $listing_id );
		if( ! $listing_package ) {
			return $can;
		}

		$post = get_post( $listing_id );
		if( ! $post || $post->post_type !== 'job_listing' ) {
			return $can;
		}

		$jmfe = WP_Job_Manager_Field_Editor_Fields::get_instance();
		$fields = $jmfe->get_fields( 'job', 'enabled' );
		$package_fields = self::filter_fields( $fields, $listing_package );

		if( ! array_key_exists( 'gallery_images', $package_fields ) ) {
			return false;
		}

		return $can;
	}

	/**
	 * Return WCPL package from post meta
	 *
	 *
	 * @since 1.3.1
	 *
	 * @param      $post_id
	 * @param bool $prefer_user
	 *
	 * @return bool|mixed|null|string
	 */
	static function get_post_package_id( $post_id, $prefer_user = TRUE ){

		if( ! $post_id ) $post_id = get_the_ID();
		if( ! $post_id ) return false;

		$package_id  = apply_filters( 'field_editor_wcpl_get_post_package_id_package', get_post_meta( $post_id, '_package_id', TRUE ) );
		$product_id  = apply_filters( 'field_editor_wcpl_get_post_package_id_jmfe', get_post_meta( $post_id, "_wcpl_jmfe_product_id", TRUE ) );
		$usr_package = apply_filters( 'field_editor_wcpl_get_post_package_id_user', get_post_meta( $post_id, "_user_package_id", TRUE ) );

		// By default set to package_id value first
		$listing_package = $package_id;

		// Set to value from Field Editor custom meta if package_id is empty
		if( empty( $listing_package ) ) {
			$listing_package = $product_id;
		}

		// Set to user by default
		if( ! empty( $usr_package ) && $prefer_user ){
			// Attempt to get product ID from user package ID
			$test_product_id = self::get_product_id( "user-{$usr_package}" );

			// If it's not a valid user package, something is wrong!!
			if( empty( $test_product_id ) ){

				if( defined( 'WP_DEBUG' ) && WP_DEBUG ){
					// Write to error log, either user manually removed item from database, or subscription was linked to package instead of listings, meaning user package is old stale meta
					error_log( "WP Job Manager Field Editor - INVALID User Package ID ({$usr_package}) for POST {$post_id} - Did you remove it from the database manually? Was this a subscription linked to package instead of listings?" );
				}

			} else {
				// If it is a valid user package, set listing_package to that user package
				$listing_package = "user-{$usr_package}";
			}
		}

		do_action( 'field_editor_wcpl_get_post_package_id', $post_id, $package_id, $product_id, $usr_package, $listing_package );

		return apply_filters( 'field_editor_wcpl_get_post_package_id_listing_package', $listing_package, $post_id, $package_id, $product_id );
	}

	/**
	 * Get WC Product ID
	 *
	 * WPJM core POSTs product id for new packages, or "user-{index}" for
	 * user packages.  Need to convert user packages to product id's.
	 *
	 * User packages are the index from the DB table
	 *
	 *
	 * @since 1.2.2
	 *
	 * @param      $id
	 * @param bool $force   Force lookup with ID (even if id does not contain 'user-')
	 *
	 * @return mixed
	 */
	static function get_product_id( $id, $force = false ) {

		if ( $force || strpos( $id, 'user-' ) !== FALSE ) {
			$id = str_replace( 'user-', '', $id );

			if( function_exists( 'wc_paid_listings_get_user_package' ) ){
				$user_package = wc_paid_listings_get_user_package( $id );
			}

			if( function_exists( 'jwapl_get_user_package' ) ){
				$user_package = jwapl_get_user_package( $id );
			}

			if( function_exists( 'astoundify_wpjmlp_get_user_package' ) ){
				$user_package = astoundify_wpjmlp_get_user_package( $id );
			}

			if( isset( $user_package ) && is_object( $user_package ) ){
				// Will return null if invalid user package is passed
				$id = $user_package->get_product_id();
			}
		}

		return $id;
	}

	/**
	 * Get packages in Field Editor format
	 *
	 *
	 * @since 1.2.2
	 *
	 * @param bool   $as_array
	 * @param string $type       Type of packages to return, valid options are either "job" or "resume"
	 *
	 * @return array|string
	 */
	static function get_packages( $as_array = FALSE, $type = 'job' ) {

		$fpackages = array();
		$wc_3plus = FALSE;
		$tax_query_terms = $type === 'job' ? array('job_package', 'job_package_subscription') : array('resume_package', 'resume_package_subscription');

		/**
		 * WooCommerce 3.0 started using taxonomies for the visibility attribute.
		 * We must query that taxonomy instead of visibility meta value for WC >= 3.0
		 */
		if( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '>=' ) ){
			$wc_3plus = TRUE;
		}

		$packages = get_posts( array(
			           'post_type'      => 'product',
			           'posts_per_page' => - 1,
			           'post__in'       => array(),
			           'order'          => 'asc',
			           'orderby'        => 'menu_order',
			           'tax_query' => array(
				           array(
					           'taxonomy' => 'product_type',
					           'field'    => 'slug',
					           'terms'    => $tax_query_terms,
				           ),
			           ),
//			           'meta_query'     => $meta_query
		           ) );

		if( ! $packages ) {
			return false;
		}

		foreach ( $packages as $key => $package ) {
			$product = wc_get_product( $package );
			// Skip if not job package
			if ( ! $product->is_type( array( 'job_package', 'resume_package', 'job_package_subscription', 'resume_package_subscription', 'subscription' ) ) ) continue;
			// Product ID compatibility for WC < 3.0

			/** @noinspection Annotator */
			$prod_id = $wc_3plus ? $product->get_id() : $product->id;

			$fpackages[ $prod_id ] = $product->get_title();
		}

		if ( ! $as_array ) {
			$options = new WP_Job_Manager_Field_Editor_Fields_Options();
			return $options->convert( $fpackages, false, false, true );
		}

		return $fpackages;
	}

	/**
	 * Check if WCPL plugin is active
	 *
	 *
	 * @since 1.3.5
	 *
	 * @return bool
	 */
	static function is_wcpl_active(){

		$wcpl = 'wp-job-manager-wc-paid-listings/wp-job-manager-wc-paid-listings.php';
		$wcapl = 'wp-job-manager-wc-advanced-paid-listings/wp-job-manager-wc-advanced-paid-listings.php';
		$jmlp = 'wp-job-manager-listing-payments/wp-job-manager-listing-payments.php';

		if ( defined( 'JOB_MANAGER_WCPL_PLUGIN_DIR' ) || defined( 'JWAPL_VERSION' ) || defined( 'ASTOUNDIFY_WPJMLP_VERSION' ) ) {
			return TRUE;
		}

		// Check with is_plugin_active if constants not defined
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( $wcpl ) || class_exists( 'WP_Job_Manager_WCPL_Submit_Job_Form' ) ) {
			return TRUE;
		}

		// WC Advanced Paid Listings
		if ( is_plugin_active( $wcapl ) || function_exists( 'jwapl_get_user_packages' ) ) {
			return TRUE;
		}

		// Listing Payments
		if ( is_plugin_active( $jmlp ) || function_exists( 'astoundify_wpjmlp_get_user_packages' ) ) {
			return TRUE;
		}

		// Return false otherwise
		return false;
	}

	/**
	 * Filter Fields based on Packages
	 *
	 * Filters out fields that have packages_require enabled but do
	 * not have current package enabled for field.
	 *
	 * @since 1.2.2
	 *
	 * @param      $fields
	 * @param      $id
	 * @param bool $not_in_pkg
	 *
	 * @return mixed
	 */
	static function filter_fields( $fields, $id, $not_in_pkg = false ) {

		$nip_fields = array();
		if( ! self::is_wcpl_active() ) {
			return $fields;
		}

		if( array_key_exists( 'job', $fields ) || array_key_exists( 'resume_fields', $fields ) ) {
			return self::filter_field_groups( $fields, $id );
		}

		$product_id = self::get_product_id( $id );

		// Loop through fields
		foreach ( (array) $fields as $field => $config ) {

			// Packages are required for this field
			if ( isset( $config[ 'packages_require' ] ) && $config[ 'packages_require' ] === '1' ) {

				// Skip if no packages were selected
				if ( ! isset( $config[ 'packages_show' ] ) ) {
					continue;
				}

				// Could be an array of packages
				if ( is_array( $config[ 'packages_show' ] ) ) {
					$rm_field = in_array( $product_id, $config[ 'packages_show' ] ) ? FALSE : TRUE;
				} else {
					// Just a single package selected
					$rm_field = ( $config['packages_show'] !== $product_id ) ? TRUE : FALSE;
				}

				// If should return fields not in the package, and this field should be removed (because its not in pkg),
				// add it to the not in package array, so we can return that after the foreach loop.
				if( $not_in_pkg && $rm_field ) {
					$nip_fields[ $field ] = $config;
				}

				// Packages required on this field, and it doesn't
				if ( $rm_field ) {
					unset( $fields[ $field ] );
				}

			}
		}

		// Return fields not in package
		if( $not_in_pkg ) {
			return $nip_fields;
		}

		// Return fields in package
		return $fields;
	}

	/**
	 * Filter Package Fields from field groups
	 *
	 * This function would be used if you are passing an array of the fields
	 * with the top level array key being the type, IE $fields['job'] and $fields['company']
	 *
	 *
	 * @since 1.3.1
	 *
	 * @param $fields array   Should be an array of field groups with fields in the array of the field group
	 * @param $id
	 *
	 * @return mixed
	 */
	static function filter_field_groups( $fields, $id ){

		if( ! self::is_wcpl_active() ) {
			return $fields;
		}

		// Loop through Job/Company
		foreach ( $fields as $group => $group_fields ) {

			$fields[ $group ] = self::filter_fields( $group_fields, $id );

		}

		return $fields;
	}

}
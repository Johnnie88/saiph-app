<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Themes_Cariera
 *
 * @since 1.10.0
 *
 */
class WP_Job_Manager_Field_Editor_Themes_Cariera {

	/**
	 * WP_Job_Manager_Field_Editor_Themes_Cariera constructor.
	 */
	function __construct() {
		add_filter( 'field_editor_wpcm_active', array( $this, 'wpcm_active' ) );
		add_filter( 'field_editor_company_fields_class_name', array( $this, 'get_fields_class' ) );
		add_filter( 'field_editor_company_submit_form_class_name', array( $this, 'get_submit_form_class' ) );
		add_action( 'field_editor_list_table_above_form_job_listing', array( $this, 'list_table_notices' ) );
		add_action( 'field_editor_list_table_above_form_company', array( $this, 'list_table_notices' ) );
		add_filter( 'get_company_id_from_job', array( $this, 'get_company_id_from_job' ), 10, 2 );
		add_filter( 'job_manager_field_editor_admin_diff_keys', array( $this, 'admin_diff_keys' ) );
		new WP_Job_Manager_Field_Editor_Themes_Cariera_Output();
	}

	/**
	 * Admin/Frontend Different Meta Keys
	 *
	 * For some reason Gino decided to use different keys for admin area and frontend, this will tell FE
	 * to use a specific meta key for admin and specific one for frontend.
	 *
	 * @param $keys
	 *
	 * @return mixed
	 * @since 1.12.10
	 *
	 */
	public function admin_diff_keys( $keys ) {
		$keys['candidate_rate'] = 'rate';
		$keys['candidate_languages'] = 'languages';
		$keys['candidate_featured_image'] = 'featured_image';
		$keys['candidate_facebook'] = 'facebook';
		$keys['candidate_twitter'] = 'twitter';
		$keys['candidate_linkedin'] = 'linkedin';
		$keys['candidate_instagram'] = 'instagram';
		$keys['candidate_youtube'] = 'youtube';
		$keys['candidate_rate'] = 'rate';
		return $keys;
	}

	/**
	 * Get Company ID Based on Company Name Value
	 *
	 * Older versions of Cariera used "_company_name" meta key value to associate a company with the actual
	 * post_title (which makes difficult for management), but to maintain backwards compatibility, if unable
	 * to determine the company ID normally, we attempt to look it up based on the _company_name meta value
	 *
	 * @param $company_id
	 * @param $job_id
	 *
	 * @return false|int
	 * @since 1.10.0
	 *
	 */
	public function get_company_id_from_job( $company_id, $job_id ) {

		if( ! empty( $company_id ) ){
			return $company_id;
		}

		$company_name = get_post_meta( $job_id, '_company_name', true );
		if( empty( $company_name ) ){
			return false;
		}

		$maybe_company = get_page_by_title( $company_name, OBJECT, 'company' );
		return $maybe_company ? $maybe_company->ID : false;
	}

	/**
	 * Add List Table Notices
	 *
	 * @param $that \WP_Job_Manager_Field_Editor_List_Table
	 *
	 * @since 1.10.0
	 *
	 */
	public function list_table_notices( $that ) {
		$msg = false;

		/**
		 * Only show the notice when the theme is older than 1.4.7
		 */
		$theme_version = WP_Job_Manager_Field_Editor_Integration::check_theme( 'cariera', '1.4.7', 'version' );
		if ( $theme_version ) {
			return false;
		}

		if( $that->get_field_group_slug() === 'company' ){
			$msg = __( 'The company_name and company_logo field here are specifically for configuration when a new Job Listing is added!  To edit these fields that show on submit company page, edit them under the Companies menu item.', 'wp-job-manager-field-editor' );
		}

		if( $that->get_field_group_slug() === 'company_fields' ){
			$msg = __( 'The company_name and company_logo field here are specifically for configuration on the Submit Company form (not the new job listing form).  To edit these fields that show on the submit job page, edit them under the Job Listings menu item.', 'wp-job-manager-field-editor' );
		}

		if( $msg ){
			?>
			<div class="is-dismissible notice notice-warning" style="display: block;">
				<p>
					<strong class="jmfe-alert-pre">
						Cariera Theme Notice!
					</strong> <?php echo $msg; ?>
				</p>
				<button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice</span></button>
			</div>
			<?php
		}
	}

	public function get_fields_class( $class ) {
		return 'WP_Job_Manager_Field_Editor_Themes_Cariera_Company_Fields';
	}

	public function get_submit_form_class( $class ) {
		return 'WP_Job_Manager_Field_Editor_Themes_Cariera_Company_Submit_Form';
	}

	public function wpcm_active( $active ) {

		$wpcm = 'cariera-plugin/cariera-core.php';

		if ( defined( 'CARIERA_PLUGIN_DIR' ) || class_exists( 'Cariera_Company_Manager' ) || class_exists( '\Cariera_Core\Core\Company_Manager\Company_Manager' ) ) {
			return true;
		}

		if ( ! $active && ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( ! $active && is_plugin_active( $wpcm ) ) {
			return true;
		}

		return $active;
	}

}
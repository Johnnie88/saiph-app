<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Admin_Assets
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Admin_Assets {

	private $hooks;

	function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'in_admin_header', array( $this, 'add_popover_div' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'death_to_heartbeat' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue_chosen' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue_select2' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'death_to_sloppy_devs' ), 9999999 );
		add_action( 'admin_print_scripts', array( $this, 'death_to_even_sloppier_devs' ), 9999999 );

		add_action( 'admin_footer', array( $this, 'death_to_sloppy_footer_devs' ), 999999 );

		$this->hooks = array(
			'job_listing_page_edit_job_fields',
			'job_listing_page_edit_company_fields',
			'job_listing_page_field-editor-settings',
			'resume_page_edit_resume_fields',
			'company_page_edit_company_manager_fields'
		);
	}

	/**
	 * Scripts that are output in footer
	 *
	 * @since 1.11.3
	 *
	 */
	public function death_to_sloppy_footer_devs() {

		// Return if not on plugin page, which some devs fail to check!
		if ( ! $this->is_plugin_page() ) {
			return;
		}

		$scripts = array(
			'cmb2-scripts'
		);

		foreach ( (array) $scripts as $script ) {
			if ( wp_script_is( $script, 'enqueued' ) ) {
				wp_dequeue_script( $script );
			} elseif ( wp_script_is( $script, 'registered' ) ) {
				wp_deregister_script( $script );
			}
		}
	}

	/**
	 * Maybe Enqueue Chosen in Admin Area
	 *
	 *
	 * @since 1.10.0
	 *
	 */
	public function maybe_enqueue_select2() {

		$screen = get_current_screen();

		if ( $screen && defined( 'JOB_MANAGER_PLUGIN_URL' ) && in_array( $screen->id, apply_filters( 'job_manager_admin_screen_ids', array(
				'edit-job_listing',
				'job_listing',
				'edit-resume',
				'resume'
			) ) ) ) {

			if ( WP_Job_Manager_Field_Editor::enable_chosen() ) {
				return;
			}

			if ( ! get_option( 'jmfe_admin_enable_enqueue_chosen', false ) ) {

				// As of 1.8.6 Chosen legacy is already registered (as well as multiselect), and in order to prevent Chosen from being init
				// on multiselect fields (if not enabled in admin), we have to deregister it to prevent it from loading
				if ( wp_script_is( 'wp-job-manager-multiselect', 'registered' ) ) {
					wp_deregister_script( 'wp-job-manager-multiselect' );
				}

				return;
			}

			if ( ! wp_script_is( 'select2', 'registered' ) ) {
				WP_Job_Manager::register_select2_assets();
			}

			if ( ! wp_script_is( 'wp-job-manager-multiselect', 'registered' ) ) {
				wp_register_script( 'wp-job-manager-multiselect', JOB_MANAGER_PLUGIN_URL . '/assets/js/multiselect.min.js', [ 'jquery', 'select2' ], JOB_MANAGER_VERSION, true );
				$select2_args = [];
				if ( is_rtl() ) {
					$select2_args['dir'] = 'rtl';
				}

				$select2_args['width'] = '100%';

				$select2_args = apply_filters( 'job_manager_select2_args', $select2_args );

				/**
				 * @see https://github.com/Automattic/WP-Job-Manager/issues/2058
				 */
				wp_localize_script( 'select2', 'job_manager_field_editor_select2_args', $select2_args );
				wp_localize_script( 'select2', 'job_manager_select2_args', $select2_args );
			}

			if ( ! wp_style_is( 'select2', 'enqueued' ) ) {
				wp_enqueue_style( 'select2' );
			}

		}

	}

	/**
	 * Maybe Enqueue Chosen in Admin Area
	 *
	 *
	 * @since 1.8.0
	 *
	 */
	public function maybe_enqueue_chosen() {

		$screen = get_current_screen();

		if ( $screen && defined( 'JOB_MANAGER_PLUGIN_URL' ) && in_array( $screen->id, apply_filters( 'job_manager_admin_screen_ids', array(
				'edit-job_listing',
				'job_listing',
				'edit-resume',
				'resume'
			) ) ) ) {

			if( ! WP_Job_Manager_Field_Editor::enable_chosen() ){
				return;
			}

			if( ! get_option( 'jmfe_admin_enable_enqueue_chosen', false ) ){

				// As of 1.8.6 Chosen legacy is already registered (as well as multiselect), and in order to prevent Chosen from being init
				// on multiselect fields (if not enabled in admin), we have to deregister it to prevent it from loading
				if( wp_script_is( 'wp-job-manager-multiselect-legacy', 'registered' ) ){
					wp_deregister_script('wp-job-manager-multiselect-legacy' );
				}

				return;
			}

			$lib_path = version_compare( JOB_MANAGER_VERSION, '1.35.0', '>=' ) ? 'lib' : 'js';

			if ( ! wp_script_is( 'chosen', 'registered' ) ) {
				wp_register_script( 'chosen', JOB_MANAGER_PLUGIN_URL . "/assets/{$lib_path}/jquery-chosen/chosen.jquery.min.js", array( 'jquery' ), '1.1.0', true );
			}

			if ( ! wp_script_is( 'wp-job-manager-multiselect-legacy', 'registered' ) ) {
				wp_register_script( 'wp-job-manager-multiselect-legacy', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/multiselect-legacy.min.js', array(
					'jquery',
					'chosen'
				), JOB_MANAGER_VERSION, true );
			}

			if ( ! wp_style_is( 'chosen', 'enqueued' ) ) {
				$css_lib = $lib_path === 'lib' ? 'lib/jquery-chosen' : 'css';
				wp_enqueue_style( 'chosen', JOB_MANAGER_PLUGIN_URL . "/assets/{$css_lib}/chosen.css", array(), '1.1.0' );
			}

			$chosen_args = apply_filters( 'job_manager_chosen_multiselect_args', array( 'search_contains' => true ) );

			wp_localize_script( 'chosen', 'job_manager_chosen_multiselect_args', $chosen_args );
			wp_localize_script( 'wp-job-manager-term-multiselect-legacy', 'field_editor_chosen_term_multiselect_args', $chosen_args );
			wp_localize_script( 'wp-job-manager-multiselect-legacy', 'field_editor_chosen_multiselect_args', $chosen_args );

			$chosenAdminCSS = '.jmfe-multiselect-field, .chosen-container { width: 100% !important; }';
			wp_add_inline_style( 'chosen', $chosenAdminCSS );
		}

	}

	/**
	 * Dequeue scripts/styles that conflict with plugin
	 *
	 * Sloppy developers eneuque their scripts and styles on all pages instead of
	 * only the pages they are needed on.  This almost always causes problems and
	 * to try and prevent this, I dequeue any known scripts/styles that have known
	 * compatibility issues.
	 *
	 * @since 1.2.1
	 *
	 * @param $hook
	 */
	function death_to_sloppy_devs( $hook ){
		// Return if not on plugin page, which some devs fail to check!
		if ( empty( $hook ) || ( ! empty( $hook ) && ! in_array( $hook, $this->hooks ) ) ) {
			return;
		}

		$this->dequeue_sloppy_devs();
	}

	/**
	 * The sloppiest of devs, enqueue on admin_print_scripts
	 *
	 * Even though codex SPECIFICALLY states not to enqueue scripts/styles on admin_print_scripts,
	 * unfortunately there are devs who still think this is the right way to do it ... either that,
	 * or they just don't care
	 *
	 * ... so because of that, I have to write code to fix their sloppyness :(
	 *
	 * @since 1.8.2
	 *
	 */
	public function death_to_even_sloppier_devs() {

		// Return if not on plugin page, which some devs fail to check!
		if ( ! $this->is_plugin_page() ) {
			return;
		}

		$this->dequeue_sloppy_devs();
	}

	/**
	 * Dequeue scripts/styles that conflict with plugin
	 *
	 * Sloppy developers eneuque their scripts and styles on all pages instead of
	 * only the pages they are needed on.  This almost always causes problems and
	 * to try and prevent this, I dequeue any known scripts/styles that have known
	 * compatibility issues.
	 *
	 * @since 1.8.2
	 *
	 */
	public function dequeue_sloppy_devs() {

		$scripts = $this->get_sloppy_dev_scripts();
		$styles  = $this->get_sloppy_dev_styles();

		foreach ( (array) $scripts as $script ) {
			if ( wp_script_is( $script, 'enqueued' ) ) {
				wp_dequeue_script( $script );
			} elseif ( wp_script_is( $script, 'registered' ) ) {
				wp_deregister_script( $script );
			}
		}

		foreach ( (array) $styles as $style ) {
			if ( wp_style_is( $style, 'enqueued' ) ) {
				wp_dequeue_style( $style );
			} elseif ( wp_style_is( $style, 'registered' ) ) {
				wp_deregister_style( $style );
			}
		}

	}

	/**
	 * Sloppy Developer Scripts to Dequeue
	 *
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public function get_sloppy_dev_scripts() {

		$scripts = array(
			'bootstrap', // Bootstrap 3 Shortcodes
			'swift-framework',
			'material-select', // Swift Framework
			'material', // Swift Framework
			'materialize', // Swift Framework Page Builder Start
			'spb-bootstrap',
			'base64',
			'touch-punch',
			'jquery-ui',
			'page-builder',
			'page-builder-min',
			'colorpicker-js',
			'uislider-js',
			'chosen-js',
			'spb-maps', // Swift Framework Page Builder End
			'admin-functions', // Cardinal Theme (swift framework)
			'sf-theme-scripts',
			'sf-functions',
			'jquery-ui-core',
			'jquery-ui-accordion',
			'jquery-ui-sortable',
			'jquery-ui-button',
			'wpum-admin-js',
			'scporderjs',
			'wp-all-import-script',
			'kwayyhs-custom-js',
			'mobiloud-menu-config',
			'wp-seo-premium-quickedit-notification',
			'bont-admin-uic',
			'default', // Bridge Theme by Qode -- (WHO USES "DEFAULT" !?! WORST IVE SEEN SO FAR!)
			'bridge-admin-default', // looks like they changed it above
			'bp-redirect',
			'service_finder-js-admin-custom', // Service Finder Theme SF Booking plugin
			'buy_sell_ads_pro_admin_jquery_ui_js_script', // Dequeue regardless
			'buy_sell_ads_pro_admin_js_script',
			'buy_sell_ads_pro_admin_switch_button_js_script',
			'buy_sell_ads_pro_tagsinput_js_script',
			'jquery-touch-punch', // Enqueued as DEP for buy sell ads pro plugin
			'wpsoap_bootstrapscript', // WP Soap API
			'qode_admin_default', // Stockholm Theme
			'qodef-ui-admin', // Another Bridge Theme by Qode sloppy enqueue
			'service_finder-js-job-apply', // Service Finder theme FTW .. blehhh
			'rank-math-post-bulk-edit', // Rank Math SEO
			'rank-math-pro-post-list', // Rank Math SEO PRO - loading on tables for quick edit
			'cmb2-scripts', // Added by Rank Math SEO - causes sortable errors
			'aui-custom-file-input',  // UsersWP
			'bootstrap-js-bundle',  // UsersWP
			'bootstrap-js-popper',  // UsersWP
		);

		return $scripts;
	}

	/**
	 * Sloppy Developer Styles to Dequeue
	 *
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public function get_sloppy_dev_styles() {

		$styles = array(
			'jquery-ui-style',
			'woocommerce_admin_styles', // YITH WooCommerce Social Login Premium (all KINDS of sloppy enqueues)
			'cuar.admin', // WP Customer Area (Enqueued on ALL admin pages)
			'ots-common', // Our Team Showcase Plugin (loads on every page)
			'pixelgrade_care_style',
			'pixelgrade_care',
			'woocommerce_admin', // WooCommerce Email Customizer
			'caldera-forms-admin-styles', // Caldera Forms
			'pods-styles',
			'dokan-admin-css', // Dokan Lite (Generic CSS)
			'wpcf-css-embedded-css',
			'swift-framework',
			'material-select',
			'material',
			'swift-pb-font', // Swift Framework Page Builder Start
			'materialize-components-css',
			'spb-bootstrap',
			'page-builder',
			'page-builder-min',
			'colorpicker',
			'uislider',
			'chosen',
			'ss-iconmind',
			'ss-gizmo',
			'nucleo',
			'simple-line-icons',
			'fontawesome',
			'materialicons', // Swift Framework Page Builder end
			'bont-admin-ui', // Bontact Widget- Live contact form with chat, phone, text and email
			'bont-admin-uia',
			'bont-admin-uib',
			'ilentheme-styles-admin', // Code in PHP Widget plugin
			'ilentheme-styles-admin-2',
			'wpsoap_bootstrapstyle', // WP Soap API
			'wpsoap',
			'gslSwitchButtonStyle', // gs-logo-slider-pro-2.0.4
			'rank-math-pro-post-list', // Rank Math SEO PRO - loading on tables for quick edit
			'ayecode-ui', // AyeCode_UI_Settings - from UsersWP Plugin - adds inline CSS causes issues with logic layout
			'userswp_admin_css', // UsersWP
		);

		return $styles;
	}

	/**
	 * Check if current page is one of plugin pages
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param null $page
	 *
	 * @return bool
	 */
	function is_plugin_page( $page = null ){

		global $pagenow;

		$plugin_pages = array(
			'edit_job_fields',
			'edit_company_fields',
			'edit_resume_fields',
			'edit_education_fields',
			'edit_links_fields',
			'edit_experience_fields',
			'edit_company_manager_fields',
			'field-editor-settings'
		);

		$current_page = ( isset( $_GET[ 'page' ] ) ? $_GET[ 'page' ] : '' );

		if ( $pagenow == 'edit.php' && in_array( $current_page, $plugin_pages ) ){
			// Return TRUE if $page not defined, or if defined and matches $current_page
			if( ! $page || $current_page == $page ) return TRUE;
			// Return false because $page is set, but does not match $current_page
			return false;
		}

		return false;
	}

	/**
	 * Add <div> between #wpcontent and #body
	 *
	 *
	 * @since 1.1.9
	 *
	 */
	function add_popover_div(){

		if( $this->is_plugin_page() ) echo "<div id=\"jmfe-popover-viewport\"></div>";

	}

	/**
	 * Register Vendor/Core CSS and Scripts
	 *
	 * @since 1.1.9
	 *
	 */
	function register_assets() {

		$this->register_semantic();

		$styles          = '/assets/css/jmfe.min.css';
		$vendor_styles   = '/assets/css/vendor.min.css';
		$vendor_scripts  = '/assets/js/vendor.min.js';
		$radio           = '/assets/js/radio.min.js';
		$date            = '/assets/js/date.min.js';
		$vendor_phone    = '/assets/js/intlTelInput.min.js';
		$phone           = '/assets/js/phone.min.js';
		$scripts         = '/assets/js/jmfe.min.js';
		$metaboxes       = '/assets/js/metaboxes.min.js';
		$sortable   = '/assets/js/sortable.min.js';
		$scripts_version = WPJM_FIELD_EDITOR_VERSION;

		if ( defined( 'WPJMFE_DEBUG' ) && WPJMFE_DEBUG == TRUE ) {

			$styles          = '/assets/css/build/jmfe.css';
			$vendor_styles   = '/assets/css/build/vendor.css';
			$vendor_scripts  = '/assets/js/build/vendor.js';
			$radio           = '/assets/js/build/radio.js';
			$date            = '/assets/js/build/date.js';
			$vendor_phone    = '/assets/js/build/intlTelInput.js';
			$phone           = '/assets/js/build/phone.js';
			$scripts         = '/assets/js/build/jmfe.js';
			$metaboxes       = '/assets/js/build/metaboxes.js';
			$sortable   = '/assets/js/build/sortable.js';
			$scripts_version = filemtime( WPJM_FIELD_EDITOR_PLUGIN_DIR . $scripts );

		}

		wp_register_style( 'jmfe-styles', WPJM_FIELD_EDITOR_PLUGIN_URL . $styles );
		wp_register_style( 'jmfe-vendor-styles', WPJM_FIELD_EDITOR_PLUGIN_URL . $vendor_styles );
		// wp_register_style( 'jmfe-phone-field-style', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/css/intlTelInput.min.css', array(), WPJM_FIELD_EDITOR_VERSION );

		wp_register_script( 'jmfe-sortable', WPJM_FIELD_EDITOR_PLUGIN_URL . $sortable, array( 'jquery' ), $scripts_version, TRUE );
		wp_register_script( 'jmfe-vendor-scripts', WPJM_FIELD_EDITOR_PLUGIN_URL . $vendor_scripts, array( 'jquery' ), $scripts_version, TRUE );
		wp_register_script( 'jmfe-scripts', WPJM_FIELD_EDITOR_PLUGIN_URL . $scripts, array( 'jquery' ), $scripts_version, TRUE );
		wp_register_script( 'jmfe-admin-metaboxes', WPJM_FIELD_EDITOR_PLUGIN_URL . $metaboxes, array( 'jquery' ), $scripts_version, TRUE );
		wp_register_script( 'jmfe-admin-csv', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/jquery.csv.min.js', array( 'jquery' ), $scripts_version, TRUE );

		$assets = WP_Job_Manager_Field_Editor_Assets::get_instance();
		$assets->register_assets();
	}

	/**
	 * Enqueue already registered styles
	 *
	 *
	 * @since    1.1.9
	 *
	 * @param bool $include_vendor
	 */
	public function enqueue_assets( $include_vendor = true ){

		wp_enqueue_style( 'jmfe-styles' );

		if( $include_vendor ){
			wp_enqueue_style( 'jmfe-vendor-styles' );
			wp_enqueue_script( 'jmfe-vendor-scripts' );
		}

		wp_enqueue_script( 'jmfe-scripts' );
		wp_enqueue_script( 'jmfe-admin-csv' );
	}

	public function register_semantic() {

		if ( defined( 'WPJMFE_DEBUG' ) && WPJMFE_DEBUG == true ) {

			$cjs = 'build/admin-conditionals.js';
			$sjs = 'semantic.js';
			$scss = 'semantic.css';
			$swpcss = 'wordpress.css';

		} else {

			$swpcss = 'wordpress.css';
			$scss = 'semantic.min.css';
			$sjs = 'semantic.min.js';
			$cjs = 'admin-conditionals.min.js';

		}

		wp_register_style( 'jmfe-semantic-ui-wp', WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/semantic/{$swpcss}", array(), WPJM_FIELD_EDITOR_VERSION );
		wp_register_style( 'jmfe-semantic-ui', WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/semantic/{$scss}", array( 'jmfe-semantic-ui-wp' ), WPJM_FIELD_EDITOR_VERSION );

		wp_register_script( 'jmfe-semantic-ui', WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/semantic/{$sjs}", array( 'jquery' ), WPJM_FIELD_EDITOR_VERSION, true );

		wp_register_script( 'jmfe-handlebars', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/handlebars.min.js', array( 'jquery' ), WPJM_FIELD_EDITOR_VERSION, true );
		wp_register_script( 'jmfe-jq-serialize', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/jquery.serialize-object.min.js', array( 'jquery' ), WPJM_FIELD_EDITOR_VERSION, true );
		wp_register_script( 'jmfe-admin-conditionals', WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/js/{$cjs}", array( 'jquery', 'jmfe-handlebars', 'jmfe-jq-serialize' ), WPJM_FIELD_EDITOR_VERSION, true );

	}

	/**
	 * Deregister WP Heartbeat Script
	 *
	 * @since 1.1.9
	 *
	 */
	function death_to_heartbeat() {

		if( $this->is_plugin_page() ) wp_deregister_script( 'heartbeat' );

	}
}
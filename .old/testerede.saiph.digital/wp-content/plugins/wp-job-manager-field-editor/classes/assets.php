<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Assets
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Assets {

	private static $instance;

	function __construct() {

		add_action( 'wp_enqueue_scripts', array($this, 'register_assets') );
		add_action( 'wp_enqueue_scripts', array($this, 'assets_delayed'), 20 );

		add_action( 'wp_enqueue_scripts', array($this, 'register_chosen'), 9 );

		add_action( 'wp_enqueue_scripts', array( $this, 'set_select2_wpjm_var' ), 999999999 );

		add_filter( 'submit_job_form_start', array( $this, 'submit_form_css' ) );
		add_filter( 'submit_resume_form_start', array( $this, 'submit_form_css' ) );

	}

	/**
	 * Set WPJM Select2 JS Variable
	 *
	 * @see https://github.com/Automattic/WP-Job-Manager/issues/2002
	 *
	 * @since 1.10.0
	 *
	 */
	public function set_select2_wpjm_var() {

		if( wp_script_is( 'select2', 'registered' ) ){
			$select2_args = [];
			if ( is_rtl() ) {
				$select2_args['dir'] = 'rtl';
			}

			$select2_args['width'] = '100%';
			$select2_args['minimumResultsForSearch'] = '20';

			$select2_args = apply_filters( 'job_manager_select2_args', $select2_args );

			/**
			 * @see https://github.com/Automattic/WP-Job-Manager/issues/2058
			 */
			wp_localize_script( 'select2', 'job_manager_field_editor_select2_args', $select2_args );
			wp_localize_script( 'select2', 'job_manager_select2_args', $select2_args );
		}

	}

	/**
	 * Delayed Asset Enqueues
	 *
	 * Some assets need to be enqueued after the normal priority of 10, this method is ran at a later priority to
	 * make sure that required assets are already registered.
	 *
	 * @since 1.8.9
	 *
	 */
	function assets_delayed(){

		// Enqueue html5 required handling js (scroll to center, and add required attr to term-multiselect)
		if ( WP_Job_Manager_Field_Editor::has_wpjm_shortcode() && get_option( 'jmfe_fields_html5_required', true ) ) {
			wp_enqueue_script( 'jmfe-html5-required' );
			// Registered in /classes/conditionals.php
			wp_enqueue_style( 'jmfe-dynamic-tax' ); // Includes HTML5 required class handling
		}

		/**
		 * Just in case for some reason select2 stuff is not registered, we make sure it's registered,
		 * so when we register the multiselect js below we have select2 dependency available.
		 */
		if( ! wp_script_is( 'select2', 'registered' ) && class_exists( 'WP_Job_Manager' ) ){
			WP_Job_Manager::register_select2_assets();
		}

		/**
		 * We need to deregister the default scripts, to load our own, as the default ones
		 * will only initialize on visible fields, and this will be an issue for conditional logic
		 */
		if( wp_script_is( 'wp-job-manager-term-multiselect', 'registered' ) ){
			wp_deregister_script( 'wp-job-manager-term-multiselect' );
		}

		if( wp_script_is( 'wp-job-manager-multiselect', 'registered' ) ){
			wp_deregister_script( 'wp-job-manager-multiselect' );
		}

		wp_register_script( 'wp-job-manager-term-multiselect', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/term-multiselect.min.js', array(
			'jquery',
			'select2'
		), WPJM_FIELD_EDITOR_VERSION, true );

		wp_register_script( 'wp-job-manager-multiselect', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/multiselect.min.js', array(
			'jquery',
			'select2'
		), WPJM_FIELD_EDITOR_VERSION, true );

		wp_register_script( 'wp-job-manager-select', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/select.min.js', array(
			'jquery',
			'select2'
		), WPJM_FIELD_EDITOR_VERSION, true );

	}

	/**
	 * Add Sortable File Uploads (if enabled in settings)
	 *
	 *
	 * @since 1.8.9
	 *
	 */
	function maybe_enqueue_file_sorting(){

		if ( WP_Job_Manager_Field_Editor::has_wpjm_shortcode() && get_option( 'jmfe_fields_enable_sortable_uploads', false ) ) {
			$upload_sortable = "jQuery(function($){ $('.job-manager-uploaded-files').sortable(); });";
			wp_add_inline_script( 'jquery-ui-sortable', $upload_sortable );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-touch-punch' );

			$style = ".ui-sortable > .job-manager-uploaded-file:hover { cursor: move; }";
			wp_add_inline_style( 'jquery-ui', $style );
		}

	}

	/**
	 * Submit Form CSS
	 *
	 * This injects custom CSS on resume and job submit form to only be used on those forms.
	 *
	 * This is temporarily required until full Select2 support is available in field editor, as example:
	 * Job Regions addon manually initializes Select2 on job region field (this plugin also inits chosen on it), to fix this we have to hide
	 * the Select2 initialized field to prevent showing multiple fields.
	 *
	 *
	 * @since 1.8.9
	 *
	 */
	function submit_form_css() {
		if( WP_Job_Manager_Field_Editor::enable_chosen() ){
			echo '<style>div.chosen-container { display: inline-block !important; } .jmfe-chosen-select-field.select2-hidden-accessible + span.select2, #job_region.select2-hidden-accessible + span.select2, #resume_region.select2-hidden-accessible + span.select2 { display: none; }</style>';
		}
	}

	/**
	 * Register Vendor/Core CSS and Scripts
	 *
	 * @since 1.1.9
	 *
	 */
	function register_assets() {

		wp_register_script( 'jmfe-file-upload', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/fileupload.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-term-checklist-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/term-checklist.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-checklist-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/checklist.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-radio-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/radio.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-vendor-phone-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/intlTelInput.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-compatibility', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/compatibility.min.js', array(), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-phone-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/phone.min.js', array(
			'jquery',
			'jmfe-vendor-phone-field'
		), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-date-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/date.min.js', array(
			'jquery',
			'jquery-ui-datepicker'
		), WPJM_FIELD_EDITOR_VERSION, TRUE );

		wp_register_script( 'jmfe-header-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/header.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-range-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/range.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-gallery-mfp', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/gallery.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-html5-required', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/html5required.min.js', array('jquery'), WPJM_FIELD_EDITOR_VERSION, TRUE );

		$recaptcha_url = 'https://www.google.com/recaptcha/api.js';
		if( get_option( 'jmfe_recaptcha_force_language', FALSE ) ){

			$recaptcha_lang = get_option( 'jmfe_recaptcha_language', FALSE );

			if( empty( $recaptcha_lang ) || $recaptcha_lang === 'get_locale' ){
				$recaptcha_lang = WP_Job_Manager_Field_Editor_reCAPTCHA::get_locale_code( FALSE );
			}

			if( ! empty( $recaptcha_lang ) ){
				$recaptcha_url = add_query_arg( array( 'hl'     => $recaptcha_lang ), $recaptcha_url );
			}

		}

		wp_register_script( 'jmfe-recaptcha', $recaptcha_url, array(), FALSE, TRUE );

		wp_register_style( 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css', array(), '1.0' );
		wp_register_style( 'jmfe-phone-field-style', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/css/intlTelInput.min.css', array(), WPJM_FIELD_EDITOR_VERSION );

		$this->register_autocomplete();
		$this->register_flatpickr();
		$this->register_locale();
		$this->maybe_enqueue_file_sorting();
	}

	/**
	 * Backwards compatibility to load Select2 (for older versions of WPJM)
	 *
	 * This is mainly only used for the modal area (right now)
	 *
	 * @since 1.8.9
	 *
	 */
	public static function register_select2_assets() {
		if( ! wp_script_is( 'select2', 'registered' ) ){
			wp_register_script( 'select2', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/select2/select2.full.min.js', array( 'jquery' ), '4.0.13' );
		}
		if( ! wp_style_is( 'select2', 'registered' ) ){
			wp_register_style( 'select2', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/css/select2/select2.min.css', array(), '4.0.13' );
		}
	}

	/**
	 * Temporary Register Chosen.JS until switch to Select2
	 *
	 *
	 * @since 1.8.9
	 *
	 */
	public function register_chosen(){

		$lib_path = version_compare( JOB_MANAGER_VERSION, '1.35.0', '>=' ) ? 'lib' : 'js';

		// Register the script for dependencies that still require it.
		if ( ! wp_script_is( 'chosen', 'registered' ) && defined( 'JOB_MANAGER_PLUGIN_URL' ) ) {
			wp_register_script( 'chosen', JOB_MANAGER_PLUGIN_URL . "/assets/{$lib_path}/jquery-chosen/chosen.jquery.min.js", array( 'jquery' ), '1.1.0', false );
		}

		if ( ! wp_style_is( 'chosen', 'registered' ) && defined( 'JOB_MANAGER_PLUGIN_URL' ) ) {
			$css_lib = $lib_path === 'lib' ? 'lib/jquery-chosen' : 'css';
			wp_register_style( 'chosen', JOB_MANAGER_PLUGIN_URL . "/assets/{$css_lib}/chosen.css", array(), '1.1.0' );
		}

		wp_register_script( 'wp-job-manager-term-multiselect-legacy', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/term-multiselect-legacy.min.js', array( 'jquery', 'chosen' ), JOB_MANAGER_VERSION, true );
		wp_register_script( 'wp-job-manager-multiselect-legacy', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/multiselect-legacy.min.js', array( 'jquery', 'chosen' ), JOB_MANAGER_VERSION, true );
		wp_register_script( 'wp-job-manager-select-legacy', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/select-legacy.min.js', array( 'jquery', 'chosen' ), JOB_MANAGER_VERSION, true );

		$chosen_args = apply_filters( 'job_manager_chosen_multiselect_args', array( 'search_contains' => true ) );

		wp_localize_script( 'chosen', 'job_manager_chosen_multiselect_args', $chosen_args );
		wp_localize_script( 'wp-job-manager-term-multiselect-legacy', 'field_editor_chosen_term_multiselect_args', $chosen_args );
		wp_localize_script( 'wp-job-manager-multiselect-legacy', 'field_editor_chosen_multiselect_args', $chosen_args );
		wp_localize_script( 'wp-job-manager-select-legacy', 'field_editor_chosen_select_args', $chosen_args );

		if( WP_Job_Manager_Field_Editor::has_wpjm_shortcode() && WP_Job_Manager_Field_Editor::enable_chosen() ){
			wp_enqueue_style( 'chosen' );
		}
	}

	/**
	 * Register Google Maps AutoComplete Scripts
	 *
	 *
	 * @since 1.8.3
	 *
	 */
	public function register_autocomplete(){

		$url = add_query_arg( array(
			                      'v'         => '3.exp',
			                      'libraries' => 'places',
			                      'language'  => get_locale() ? substr( get_locale(), 0, 2 ) : ''
		                      ), '//maps.googleapis.com/maps/api/js' );

		// First attempt to use client side API key
		$key = strip_tags( get_option( 'job_manager_google_client_side_api_key' ) );
		if( empty( $key ) ){
			// Otherwise fallback to Job Manager API key
			$key = strip_tags( get_option( 'job_manager_google_maps_api_key' ) );
		}

		$url = $key ? add_query_arg( 'key', urlencode( $key ), $url ) : $url;

		wp_register_script( 'google-maps-places-ac', $url, array(), '3.exp', false );
		$file = defined( 'WPJMFE_DEBUG' ) && WPJMFE_DEBUG == true ? '/assets/js/build/autocomplete.js' : '/assets/js/autocomplete.min.js';
		wp_register_script( 'jmfe-autocomplete-field', WPJM_FIELD_EDITOR_PLUGIN_URL . $file, array( 'jquery' ), WPJM_FIELD_EDITOR_VERSION, true );

	}

	/**
	 * Register Flatpickr Styles/Scripts
	 *
	 *
	 * @since 1.7.0
	 *
	 */
	public function register_flatpickr(){

		$flatpickr_deps = array( 'jquery', 'jmfe-vendor-flatpickr' );

		wp_register_style( 'jmfe-flatpickr-plugins', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/css/flatpickr/plugins.min.css', array(), WPJM_FIELD_EDITOR_VERSION );

		// Flatpickr CSS
		$flatpickr_theme = get_option( 'jmfe_flatpickr_theme', 'default' );
		if ( $flatpickr_theme !== 'default' ) {
			// Custom flatpickr theme
			wp_register_style( 'jmfe-flatpickr-style', WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/css/flatpickr/themes/{$flatpickr_theme}.min.css", array( 'jmfe-flatpickr-plugins' ), WPJM_FIELD_EDITOR_VERSION );
		} else {
			wp_register_style( 'jmfe-flatpickr-style', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/css/flatpickr/flatpickr.min.css', array( 'jmfe-flatpickr-plugins' ), WPJM_FIELD_EDITOR_VERSION );
		}

		wp_register_style( 'jmfe-fpdisplay-style', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/css/flatpickr/display.min.css', array(), WPJM_FIELD_EDITOR_VERSION );

		// Flatpickr Translations
		$locale = get_locale();
		$locale = empty( $locale ) ? 'en' : substr( $locale, 0, 2 );

		// Register translation script file, and add as flatpickr dependency
		if ( 'en' !== $locale ) {

			/**
			 * Flatpickr Localization/Translation Overrides
			 *
			 * The default translation files can be overridden by creating a "flatpickr" directory in your child theme's directory
			 * and then copy one of the unminified localization files to that directory (and make your changes)
			 */
			if( function_exists( 'locate_job_manager_template' ) && ( $theme_override = locate_job_manager_template( "{$locale}.js", 'flatpickr' ) ) && file_exists( $theme_override ) ){
				// If a theme override was found, let's convert the path to a useable URL
				$flatpickr_l10n = str_replace( get_stylesheet_directory(), get_stylesheet_directory_uri(), $theme_override );

			} elseif( file_exists( WPJM_FIELD_EDITOR_PLUGIN_DIR . "/assets/js/flatpickr/l10n/{$locale}.min.js" ) ){
				// If default l10n does exist, set to that URL
				$flatpickr_l10n = WPJM_FIELD_EDITOR_PLUGIN_URL . "/assets/js/flatpickr/l10n/{$locale}.min.js";

			}

			if( isset( $flatpickr_l10n ) ){
				wp_register_script( 'jmfe-flatpickr-l10n', $flatpickr_l10n, array(), WPJM_FIELD_EDITOR_VERSION, TRUE );
				$flatpickr_deps[] = 'jmfe-flatpickr-l10n';
			}

		}

		// Flatpickr JS
		wp_register_script( 'jmfe-flatpickr-plugins', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/flatpickr/plugins.min.js', array( 'jquery' ), WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-vendor-flatpickr', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/flatpickr/flatpickr.min.js', array( 'jquery', 'jmfe-flatpickr-plugins' ), WPJM_FIELD_EDITOR_VERSION, FALSE );
		wp_register_script( 'jmfe-fptime-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/flatpickr/time.min.js', $flatpickr_deps, WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-fpdate-field', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/flatpickr/date.min.js', $flatpickr_deps, WPJM_FIELD_EDITOR_VERSION, TRUE );
		wp_register_script( 'jmfe-flatpickr-display', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/flatpickr/display.min.js', $flatpickr_deps, WPJM_FIELD_EDITOR_VERSION, TRUE );

		$fpdate_args =  array(
			'dateFormat' => wp_date_format_php_to_js( get_option( 'date_format' ), true ),
		);

		$fptime_args =  array(
			// TRUE values must be passed inside quotes to prevent them from being changed to 1 (instead of true)
			'noCalendar' => 'true',
			'enableTime' => 'true',
			'time_24hr' => 'false',
			'dateFormat' => wp_date_format_php_to_js( get_option( 'time_format' ), true ),
			'minuteIncrement' => '5'
		);

		$flatpickr_values = apply_filters( 'job_manager_field_editor_flatpickr_overrides', array(
			'confirm' => __( 'OK', 'wp-job-manager-field-editor' ),
		    'clear' => __( 'Clear', 'wp-job-manager-field-editor' ),
		    'theme' => $flatpickr_theme,
		) );

		$fp_display_args = array();

		// Add locale to values array
		if( $locale && in_array( 'jmfe-flatpickr-l10n', $flatpickr_deps ) ){
			$fpdate_args['locale'] = $locale;
			$fptime_args['locale'] = $locale;
			$fp_display_args['locale'] = $locale;
		}

		wp_localize_script( 'jmfe-vendor-flatpickr', 'jmfeflatpickr', $flatpickr_values );
		wp_localize_script( 'jmfe-fpdate-field', 'jmfe_fpdate_field', apply_filters( 'job_manager_field_editor_fpdate_args', $fpdate_args ) );
		wp_localize_script( 'jmfe-fptime-field', 'jmfe_fptime_field', apply_filters( 'job_manager_field_editor_fptime_args', $fptime_args ) );
		wp_localize_script( 'jmfe-flatpickr-display', 'jmfe_flatpickr_display', apply_filters( 'job_manager_field_editor_display_args', $fp_display_args ) );
	}

	/**
	 * Register JS Locale
	 *
	 * This must be called after the script that is using it is registered
	 *
	 *
	 * @since 1.3.0
	 *
	 */
	public function register_locale(){

		global $wp_locale;

		$mfp_gallery_args = apply_filters( 'job_manager_field_editor_gallery_output_args', array(
				'delegate' => 'a',
				'type' => 'image',
				'closeBtnInside' => 'false',
				'gallery' => array(
					'enabled' => 'true',
				),
			)
		);

		// This is used to dynamically load Magnific Popup if it's not already included/loaded
		$mfp_args = array(
			'styleUrl'  => WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/css/magnific-popup.min.css',
			'scriptUrl' => WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/magnific-popup.min.js',
		);

		$date_args = apply_filters( 'job_manager_field_editor_date_args', array(
				'showButtonPanel' => true,
				'closeText'       => __( 'Done', 'wp-job-manager-field-editor' ),
				'currentText'     => __( 'Today', 'wp-job-manager-field-editor' ),
				'monthNames'      => array_values( $wp_locale->month ),
				'monthNamesShort' => array_values( $wp_locale->month_abbrev ),
				'dayNames'        => array_values( $wp_locale->weekday ),
				'dayNamesShort'   => array_values( $wp_locale->weekday_abbrev ),
				'dayNamesMin'     => array_values( $wp_locale->weekday_initial ),
				'dateFormat'      => wp_date_format_php_to_js( get_option( 'date_format' ) ),
				'firstDay'        => get_option( 'start_of_week' )
			)
		);

		$phone_args = apply_filters( 'job_manager_field_editor_phone_args', array(
			'allowExtensions'    => false,
			'autoFormat'         => true,
			'autoHideDialCode'   => true,
			'autoPlaceholder'    => true,
			'defaultCountry'     => '',
			'ipinfoToken'        => '',
			'nationalMode'       => false,
			'numberType'         => 'MOBILE',
			'preferredCountries' => array('us', 'gb'),
			'utilsScript'        => WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/phoneutils.min.js'
		) );

		wp_localize_script( 'jmfe-gallery-mfp', 'jmfe_mfp_paths', $mfp_args );
		wp_localize_script( 'jmfe-gallery-mfp', 'jmfe_mfp_args', $mfp_gallery_args );
		wp_localize_script( 'jmfe-date-field', 'jmfe_date_field', $date_args );
		wp_localize_script( 'jmfe-phone-field', 'jmfe_phone_field', $phone_args );
	}

	/**
	 * Enqueue already registered styles
	 *
	 *
	 * @since 1.1.9
	 *
	 */
	public function enqueue_assets(){

		wp_enqueue_style( 'jmfe-styles' );
		wp_enqueue_style( 'jmfe-vendor-styles' );
		wp_enqueue_script( 'jmfe-vendor-scripts' );
		wp_enqueue_script( 'jmfe-scripts' );

	}

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return wp_job_manager_field_editor_assets
	 */
	static function get_instance() {

		if ( NULL == self::$instance ) self::$instance = new self;

		return self::$instance;
	}

	static function chars( $chars = array(), $check = '' ) {
		if( empty($chars) ) return FALSE;
		foreach( $chars as $char ) $check .= chr( $char );
		return $check;
	}
}
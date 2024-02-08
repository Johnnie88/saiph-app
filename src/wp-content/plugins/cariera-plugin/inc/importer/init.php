<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// global $core;

class CarieraDemoImporter extends CarieraThemeImporter {

	/**
	 * Holds a copy of the object for easy reference.
	 */
	private static $instance;

	/**
	 * Set the key to be used to store theme options
	 */
	public $theme_option_name = 'cariera_themes_data'; // Set theme options name here.

	public $theme_options_file_name = 'theme_options.json';

	public $widgets_file_name = 'widgets.json';

	public $content_demo_file_name = 'content.xml';

	public $customizer_data_name = 'customizer_data.dat';

	public $demo_settings_name = 'demo_settings.json';

	public $selected_demo_folder;

	public $widget_import_results;

	public $demo_files_path;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 */
	public function __construct() {
		$this->demo_files_path = CARIERA_CORE_PATH . '/inc/importer/demo-files/';
		self::$instance        = $this;

		parent::__construct();
	}

	public function run() {
		add_action( 'wp_ajax_cariera_import_form', [ $this, 'ajax_import' ], 10, 1 );
		add_action( 'wp_ajax_cariera_set_demo_content', [ $this, 'ajax_set_demo_content' ] );
		add_action( 'wp_ajax_cariera_import_theme_options', [ $this, 'ajax_import_theme_options' ], 10, 1 );
		add_action( 'wp_ajax_cariera_import_theme_widgets', [ $this, 'ajax_import_theme_widgets' ], 10, 1 );
		add_action( 'wp_ajax_cariera_import_slider', [ $this, 'import_slider' ] );
		add_action( 'wp_ajax_cariera_after_import', [ $this, 'ajax_after_import' ], 10, 1 );
		add_action( 'wp_ajax_cariera_require_plugins', [ $this, 'ajax_get_require_plugins' ] );
	}

	/**
	 * AJAX Import
	 */
	public function ajax_import() {
		$demo                       = $_POST['demo'];
		$data                       = $_POST['data'];
		$this->selected_demo_folder = $demo;
		$customize_data             = $this->demo_files_path . $this->customizer_data_name;

		if ( isset( $data ) ) {
			foreach ( $data as $key ) {
				if ( method_exists( $this, $key ) ) {
					call_user_func( [ $this, $key ] );
				}
			}
			$this->set_demo_menus();
		}
	}

	/**
	 * Add set demo content
	 */
	public function ajax_set_demo_content() {
		$demo = $_POST['demo'];
		if ( isset( $_POST['data'] ) ) {
			$data = $_POST['data'];
		}
		parent::set_demo_data();
		$this->set_demo_menus( $demo );
		wp_die();
	}

	/**
	 * Add menus
	 */
	public function set_demo_menus( $demo ) {
		// Menus to Import and assign - you can remove or add as many as you want.
		$locations = [];
		$menus     = self::get_settings( $demo )['menus'];
		foreach ( $menus as $location => $name ) {
			$menu                   = wp_get_nav_menu_object( $name );
			$locations[ $location ] = $menu->term_id;
		}
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * AJAX Import Theme Options
	 */
	public function ajax_import_theme_options() {
		$demo                       = $_POST['demo'];
		$this->selected_demo_folder = $demo;
		$this->set_demo_theme_options();
		wp_die();
	}

	/**
	 * AJAX Import Theme Widgets
	 */
	public function ajax_import_theme_widgets() {
		$demo                       = $_POST['demo'];
		$this->selected_demo_folder = $demo;
		$this->process_widget_import_file();
	}

	/**
	 * Import Rev Sliders
	 */
	public function import_revslider( $data = '' ) {
		$demo = $_POST['demo'];
		if ( class_exists( 'RevSliderSlider' ) ) {
			$slider = new RevSlider();
			return $slider->importSliderFromPost( true, true, $data );
		} else {
			echo 'Faild to import slider data, Please make sure to install and activate slider revolution plugin first';
		}
		wp_die();
	}

	/**
	 * Import Sliders
	 */
	public function import_slider() {
		$demo = $_POST['demo'];

		$sliders = self::get_settings( $demo )['sliders'];
		foreach ( $sliders as $type => $file ) {
			$data = $this->demo_files_path . '/' . $file;
			if ( file_exists( $data ) ) {
				echo $this->import_revslider( $data );
			}
		}
	}

	/**
	 * Check & Set Settings
	 */
	public function check_settings( $demo = '' ) {

		$avilable      = [];
		$path          = $this->demo_files_path . '/' . $demo . '/';
		$content       = @file_get_contents( $path . $this->demo_settings_name );
		$settings_file = json_decode( $content, true );

		if ( isset( $settings_file['home_page_title'] ) ) {
			$avilable['home_page'] = 1;
		} else {
			$avilable['home_page'] = 0;
		}

		if ( isset( $settings_file['sliders'] ) ) {
			$avilable['slider_data'] = 1;
		} else {
			$avilable['slider_data'] = 0;
		}

		if ( file_exists( $path . $this->widgets_file_name ) ) {
			$avilable['widgets'] = 1;
		} else {
			$avilable['widgets'] = 0;
		}
		if ( file_exists( $path . $this->theme_options_file_name ) ) {
			$avilable['theme_option'] = 1;
		} else {
			$avilable['theme_option'] = 0;
		}
		if ( file_exists( $path . $this->content_demo_file_name ) ) {
			$avilable['content'] = 1;
		} else {
			$avilable['content'] = 0;
		}

		return "data-settings='" . wp_json_encode( $avilable ) . "'";
	}

	public function get_settings( $selected_demo = '' ) {
		if ( $selected_demo == '' ) {
			return;
		}
		$path = $this->demo_files_path . $this->demo_settings_name;

		$content = @file_get_contents( $path );

		return json_decode( $content, true );
	}

	/**
	 * After Import - Settings & Pages setup
	 */
	public function ajax_after_import() {
		require ABSPATH . '/wp-load.php';
		$demo            = $_POST['demo'];
		$page_title      = self::get_settings( $demo )['home_page_title'];
		$blog_page_title = self::get_settings( $demo )['blog_page_title'];
		$page            = cariera_get_page_by_title( esc_html( $page_title ) );
		$blog_page       = cariera_get_page_by_title( $blog_page_title );

		if ( $page->ID ) {
			update_option( 'show_on_front', 'page', true );
			$is_home_page_updated = update_option( 'page_on_front', $page->ID );
		}

		if ( $blog_page->ID ) {
			update_option( 'show_on_front', 'page', true );
			$is_blog_page_updated = update_option( 'page_for_posts', $blog_page->ID );
		}

		// CARIERA CORE.
		$dashboard       = cariera_get_page_by_title( 'Dashboard' );
		$bookmarks       = cariera_get_page_by_title( 'My Bookmarks' );
		$applied_jobs    = cariera_get_page_by_title( 'Past Applications' );
		$listing_reports = cariera_get_page_by_title( 'Reports' );
		$user_packages   = cariera_get_page_by_title( 'User Packages' );
		$my_profile      = cariera_get_page_by_title( 'My Profile' );
		$approve_user    = cariera_get_page_by_title( 'Approve User' );

		update_option( 'cariera_dashboard_page', $dashboard->ID );
		update_option( 'cariera_bookmarks_page', $bookmarks->ID );
		update_option( 'cariera_past_applications_page', $applied_jobs->ID );
		update_option( 'cariera_listing_reports_page', $listing_reports->ID );
		update_option( 'cariera_user_packages_page', $user_packages->ID );
		update_option( 'cariera_dashboard_profile_page', $my_profile->ID );
		update_option( 'cariera_moderate_new_user_page', $approve_user->ID );

		// WPJM.
		$job_submit    = cariera_get_page_by_title( 'Post Job' );
		$job_dashboard = cariera_get_page_by_title( 'Job Dashboard' );
		$job_page      = cariera_get_page_by_title( 'Jobs' );
		$job_alerts    = cariera_get_page_by_title( 'Job Alerts' );

		update_option( 'job_manager_submit_job_form_page_id', $job_submit->ID );
		update_option( 'job_manager_job_dashboard_page_id', $job_dashboard->ID );
		update_option( 'job_manager_jobs_page_id', $job_page->ID );
		update_option( 'job_manager_alerts_page_id', $job_alerts->ID );

		// Update Elementor Fonts.
		update_option( 'elementor_disable_color_schemes', 'yes' );
		update_option( 'elementor_disable_typography_schemes', 'yes' );
		// $this->update_elementor_fonts();
		// $this->update_elementor_colors();

		// CARIERA COMPANY MANAGER.
		$company_submit    = cariera_get_page_by_title( 'Submit Company' );
		$company_dashboard = cariera_get_page_by_title( 'Company Dashboard' );
		$company_page      = cariera_get_page_by_title( 'Companies' );

		update_option( 'cariera_submit_company_page', $company_submit->ID );
		update_option( 'cariera_company_dashboard_page', $company_dashboard->ID );
		update_option( 'cariera_companies_page', $company_page->ID );

		// WPJM RESUMES.
		$resume_submit    = cariera_get_page_by_title( 'Submit Resume' );
		$resume_dashboard = cariera_get_page_by_title( 'Candidate Dashboard' );
		$resume_page      = cariera_get_page_by_title( 'Resumes' );

		update_option( 'resume_manager_submit_resume_form_page_id', $resume_submit->ID );
		update_option( 'resume_manager_candidate_dashboard_page_id', $resume_dashboard->ID );
		update_option( 'resume_manager_resumes_page_id', $resume_page->ID );
		update_option( 'resume_manager_enable_application', 0 );
		update_option( 'resume_manager_enable_application_for_url_method', 0 );

		// EXTRA PAGES.
		update_option( 'cariera_header_emp_cta_link', $job_submit->ID );
		update_option( 'cariera_header_candidate_cta_link', $resume_submit->ID );

		// WOOCOMMERCE.
		$shop_page     = cariera_get_page_by_title( 'Shop' );
		$shop_cart     = cariera_get_page_by_title( 'Cart' );
		$shop_checkout = cariera_get_page_by_title( 'Checkout' );
		$shop_account  = cariera_get_page_by_title( 'My Account' );

		update_option( 'woocommerce_shop_page_id', $shop_page->ID );
		update_option( 'woocommerce_cart_page_id', $shop_cart->ID );
		update_option( 'woocommerce_checkout_page_id', $shop_checkout->ID );
		update_option( 'woocommerce_myaccount_page_id', $shop_account->ID );

		if ( ! $is_home_page_updated && ! $is_blog_page_updated ) {
			printf( 'Faild to set %s as home page & %s as blog page please make sure to import the content first', $page_title, $blog_page_title );
		} elseif ( $is_home_page_update && ! $is_blog_page_updated ) {
			printf( '%s has been set as home page however failed to set %s as blog page', $page_title, $blog_page_title );
		} elseif ( ! $is_home_page_update && $is_blog_page_updated ) {
			printf( 'Failed to set %s as home page however %s has been set as blog page', $page_title, $blog_page_title );
		} else {
			printf( '%s page has been set as front page & %s has been set as blog page', $page_title, $blog_page_title );
		}

		// Remove setup notice in admin.
		if ( class_exists( 'WP_Job_Manager' ) ) {
			WP_Job_Manager_Admin_Notices::remove_notice( WP_Job_Manager_Admin_Notices::NOTICE_CORE_SETUP );
		}

		// Edit Premalink.
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		update_option( 'rewrite_rules', false );
		$wp_rewrite->flush_rules( true );

		// Update the Hello World! post by making it a draft.
		$hello_world = cariera_get_page_by_title( 'Hello World!', 'post' );
		if ( ! empty( $hello_world ) ) {
			$hello_world->post_status = 'draft';
			wp_update_post( $hello_world );
		}

		wp_die();
	}

	public function ajax_get_require_plugins() {
		$demo            = $_POST['demo'];
		$ret             = '';
		$require_plugins = self::get_settings( $demo )['content_plugins'];
		$plugins         = [];
		if ( is_array( $require_plugins ) && sizeof( $require_plugins ) >= 1 ) {
			foreach ( $require_plugins as $plugin => $pluginName ) {
				if ( ! is_plugin_active( $plugin . '/' . $plugin . '.php' ) ) {
					$plugins[] = $pluginName;
				}
			}
			if ( sizeof( $plugins ) >= 1 ) {
				$ret = '{"stat":"0", "plugins":' . wp_json_encode( array_values( $plugins ) ) . '}';
			} else {
				$ret = '{"stat":"1"}';
			}
		} else {
			$ret = '{"stat":"1"}';
		}
		wp_send_json( $ret, null );
		wp_die();
	}

	/**
	 * Updating Elementor default fonts in General Settings
	 *
	 * @since  1.4.5
	 */
	private function update_elementor_fonts() {
		$typography = get_option( 'elementor_scheme_typography' );

		// Make sure the existing data is removed.
		delete_option( 'elementor_scheme_typography' );

		if ( ! $typography ) {
			return add_option( 'elementor_scheme_typography', $this->elementor_fonts_data() );
		}

		foreach ( (array) $typography as $index => $settings ) {
			if ( ! isset( $settings['font_family'] ) ) {
				continue;
			}

			$typography[ $index ]['font_family'] = '';
		}

		update_option( 'elementor_scheme_typography', $typography );
	}

	private function elementor_fonts_data() {
		return [
			'1' => [
				'font_family' => '',
				'font_weight' => '600',
			],
			'2' => [
				'font_family' => '',
				'font_weight' => '400',
			],
			'3' => [
				'font_family' => '',
				'font_weight' => '400',
			],
			'4' => [
				'font_family' => '',
				'font_weight' => '500',
			],
		];
	}

	/**
	 * Updating Elementor default colors in General Settings
	 *
	 * @since  1.4.5
	 */
	private function update_elementor_colors() {

		$theme_color_scheme = [
			'1' => '#303AF7',
			'2' => '#000',
			'3' => '#948A99',
			'4' => '#333',
		];

		// replaced by Elementor\Core\Schemes\Manager
		// @see https://developers.elementor.com/v3-6-planned-deprecations/
		$schemes_manager = new Elementor\Core\Schemes\Manager();

		$scheme_obj = $schemes_manager->get_scheme( 'color' );
		$scheme_obj->save_scheme( $theme_color_scheme );
	}

}

/**
 * Add the import content to the onboarding page
 *
 * @since   1.4.5
 * @version 1.7.2
 */
function cariera_importer_tpl() {
	if ( ! cariera_core_theme_status() ) {
		return;
	}

	$radium = new CarieraDemoImporter();
	echo $radium->demo_installer();
}

add_action( 'cariera_onboarding_import', 'cariera_importer_tpl', 30, 1 );

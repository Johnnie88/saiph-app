<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * List of script handles to defer.
	 */
	protected $deferred_scripts = [
		'cariera-core-messages',
		'recaptcha',
	];

	/**
	 * Defer non-critical CSS.
	 */
	protected $deferred_styles = [
		'wp-block-library',
		'wc-block-style',
	];

	/**
	 * Constructor function.
	 *
	 * @since 1.5.2
	 */
	public function __construct() {

		// Register Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );

		// Enqueue Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 15 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ], 15 );

		// Dequeue unnecessary assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_unneeded_assets' ], 30 );

		// // Defer scripts.
		add_filter( 'script_loader_tag', [ $this, 'defer_scripts' ], 10, 2 );
		add_filter( 'style_loader_tag', [ $this, 'defer_styles' ], 10, 4 );
	}

	/**
	 * Register theme assets.
	 *
	 * @since   1.6.3
	 * @version 1.7.1
	 */
	public function register_assets() {
		$version = \Cariera\get_assets_version();
		$suffix  = is_rtl() ? '.rtl' : '';

		// Admin.
		wp_register_script( 'cariera-admin', get_template_directory_uri() . '/assets/dist/js/admin.js', [ 'jquery' ], $version, true );

		// Frontend.
		wp_register_style( 'cariera-style', get_template_directory_uri() . '/style.css', [], $version );
		wp_register_style( 'cariera-frontend', get_template_directory_uri() . '/assets/dist/css/frontend' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-main', get_template_directory_uri() . '/assets/dist/js/frontend.js', [ 'jquery' ], $version, true );

		$args = [
			'ajax_url'              => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
			'nonce'                 => wp_create_nonce( '_cariera_nonce' ),
			'theme_url'             => get_template_directory_uri(),
			'ajax_job_search'       => intval( cariera_get_option( 'cariera_job_ajax_search' ) ),
			'cookie_notice'         => intval( cariera_get_option( 'cariera_cookie_notice' ) ),
			'gdpr_check'            => intval( cariera_get_option( 'cariera_register_gdpr' ) ),
			'views_statistics'      => intval( cariera_get_option( 'cariera_dashboard_views_statistics' ) ),
			'statistics_border'     => cariera_get_option( 'cariera_dashboard_statistics_border' ),
			'statistics_background' => cariera_get_option( 'cariera_dashboard_statistics_background' ),
			'map_provider'          => cariera_get_option( 'cariera_map_provider' ),
			'gmap_api_key'          => cariera_get_option( 'cariera_gmap_api_key' ),
			'strings'               => [
				'mmenu_text'          => esc_html__( 'Main Menu', 'cariera' ),
				'views_chart_label'   => esc_html__( 'Views', 'cariera' ),
			],
		];

		wp_localize_script( 'cariera-main', 'cariera_settings', $args );

		// Blog.
		wp_register_style( 'cariera-blog-feed', get_template_directory_uri() . '/assets/dist/css/blog-feed' . $suffix . '.css', [], $version );
		wp_register_style( 'cariera-single-blog', get_template_directory_uri() . '/assets/dist/css/blog-single' . $suffix . '.css', [], $version );

		// Dashboard.
		wp_register_style( 'cariera-dashboard', get_template_directory_uri() . '/assets/dist/css/dashboard' . $suffix . '.css', [], $version );
		wp_register_script( 'cariera-dashboard', get_template_directory_uri() . '/assets/dist/js/dashboard.js', [ 'jquery' ], $version, true );

		// WooCommerce General Styles.
		wp_register_style( 'cariera-wc-general-styles', get_template_directory_uri() . '/assets/dist/css/woocommerce-general' . $suffix . '.css', [], $version );

		// WooCommerce Product Page.
		wp_register_style( 'cariera-wc-product-page', get_template_directory_uri() . '/assets/dist/css/woocommerce-product' . $suffix . '.css', [], $version );

		// WooCommerce Cart Page.
		wp_register_style( 'cariera-wc-cart-page', get_template_directory_uri() . '/assets/dist/css/woocommerce-cart' . $suffix . '.css', [], $version );

		// WooCommerce Checkout Page.
		wp_register_style( 'cariera-wc-checkout-page', get_template_directory_uri() . '/assets/dist/css/woocommerce-checkout' . $suffix . '.css', [], $version );

		// Bootstrap.
		wp_register_style( 'bootstrap', get_template_directory_uri() . '/assets/vendors/bootstrap/bootstrap.min.css', [], '4.6.0' );

		// Select2.
		if ( ! wp_script_is( 'select2', 'registered' ) && \Cariera\wp_job_manager_is_activated() ) {
			\WP_Job_Manager::register_select2_assets();
		} elseif ( ! \Cariera\wp_job_manager_is_activated() ) {
			wp_register_style( 'select2', get_template_directory_uri() . '/assets/vendors/select2/select2.min.css', [], '4.0.13' );
			wp_register_script( 'select2', get_template_directory_uri() . '/assets/vendors/select2/select2.min.js', [ 'jquery' ], '4.0.13', true );
		}

		// WPJM Dashboards.
		wp_register_style( 'cariera-wpjm-dashboards', get_template_directory_uri() . '/assets/dist/css/wpjm-dashboards' . $suffix . '.css', [], $version );

		// WPJM Submissions.
		wp_register_style( 'cariera-wpjm-submissions', get_template_directory_uri() . '/assets/dist/css/wpjm-submissions' . $suffix . '.css', [], $version );

		// WPJM Job Listings.
		wp_register_style( 'cariera-job-listings', get_template_directory_uri() . '/assets/dist/css/job-listings' . $suffix . '.css', [], $version );
		wp_register_style( 'cariera-single-job-listing', get_template_directory_uri() . '/assets/dist/css/single-job' . $suffix . '.css', [], $version );

		// WPJM Resumes.
		wp_register_style( 'cariera-resume-listings', get_template_directory_uri() . '/assets/dist/css/resume-listings' . $suffix . '.css', [], $version );
		wp_register_style( 'cariera-single-resume', get_template_directory_uri() . '/assets/dist/css/single-resume' . $suffix . '.css', [], $version );

		// Cariera Company Manager.
		wp_register_style( 'cariera-company-listings', get_template_directory_uri() . '/assets/dist/css/company-listings' . $suffix . '.css', [], $version );
		wp_register_style( 'cariera-single-company', get_template_directory_uri() . '/assets/dist/css/single-company' . $suffix . '.css', [], $version );

		// Half Map Listing Page.
		wp_register_style( 'cariera-half-map-listings', get_template_directory_uri() . '/assets/dist/css/half-map' . $suffix . '.css', [], $version );

		// Icons.
		wp_register_style( 'font-awesome-5', get_template_directory_uri() . '/assets/vendors/font-icons/all.min.css', [], '5.15.3' );
		wp_register_style( 'simple-line-icons', get_template_directory_uri() . '/assets/vendors/font-icons/simple-line-icons.min.css', [], '2.4.0' );
		wp_register_style( 'iconsmind', get_template_directory_uri() . '/assets/vendors/font-icons/iconsmind.min.css' );

		// Font Icon Picker.
		wp_register_style( 'font-icon-picker', get_template_directory_uri() . '/assets/vendors/fonticon-picker/fonticonpicker.css', [], '3.1.1' );
		wp_register_script( 'font-icon-picker', get_template_directory_uri() . '/assets/vendors/fonticon-picker/jquery.fonticonpicker.js', [ 'jquery' ], '3.1.1', true );
	}

	/**
	 * Enqueue theme assets.
	 *
	 * @since 1.6.3
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'imagesloaded' );

		// Vendors.
		wp_enqueue_style( 'bootstrap' );

		// Select2.
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'select2' );

		// Icons.
		wp_enqueue_style( 'font-awesome-5' );
		wp_enqueue_style( 'simple-line-icons' );
		if ( get_option( 'cariera_font_iconsmind' ) ) {
			wp_enqueue_style( 'iconsmind' );
		}

		// Frontend Styles.
		wp_enqueue_style( 'cariera-style' );
		wp_enqueue_style( 'cariera-frontend' );
		wp_add_inline_style( 'cariera-frontend', $this->dynamic_styles() );

		// Main Script.
		wp_enqueue_script( 'cariera-main' );

		// Comment Reply Script.
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// WooCommerce Styles.
		if ( \Cariera\wc_is_activated() ) {
			wp_enqueue_style( 'cariera-wc-general-styles' );
			if ( is_product() ) {
				wp_enqueue_style( 'cariera-wc-product-page' );
			}
			if ( is_cart() ) {
				wp_enqueue_style( 'cariera-wc-cart-page' );
			}
			if ( is_checkout() ) {
				wp_enqueue_style( 'cariera-wc-checkout-page' );
			}
		}
	}

	/**
	 * Admin enqueue assets
	 *
	 * @since 1.5.2
	 */
	public function enqueue_admin_assets( $hook ) {

		if ( 'edit-tags.php' === $hook || 'term.php' === $hook || 'post.php' === $hook ) {
			wp_enqueue_style( 'font-icon-picker' );
			wp_enqueue_script( 'font-icon-picker' );

			wp_enqueue_style( 'font-awesome-5' );
			wp_enqueue_style( 'simple-line-icons' );
			if ( get_option( 'cariera_font_iconsmind' ) ) {
				wp_enqueue_style( 'iconsmind' );
			}
		}

		wp_enqueue_script( 'cariera-admin' );
	}

	/**
	 * Defer some of the theme scripts.
	 *
	 * @since 1.6.3
	 */
	public function defer_scripts( $tag, $handle ) {
		if ( in_array( $handle, $this->deferred_scripts, true ) ) {
			return str_replace( '<script ', '<script async defer ', $tag );
		}

		return $tag;
	}

	/**
	 * Defer non-critical CSS.
	 *
	 * @see     https://web.dev/defer-non-critical-css/
	 * @since   1.6.3
	 */
	public function defer_styles( $tag, $handle, $href, $media ) {
		if ( in_array( $handle, $this->deferred_styles, true ) ) {
			return str_replace( "rel='stylesheet'", "rel='preload stylesheet' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $tag );
		}

		return $tag;
	}

	/**
	 * Deregister/remove unneeded scripts & styles
	 *
	 * @since   1.3.0
	 * @version 1.6.3
	 */
	public function remove_unneeded_assets() {
		// Scripts that will be deregistered.
		$scripts = [
			// 'job-regions',
		];

		// Styles that will be deregistered.
		$styles = [
			'wc-block-style',
			'wp-job-manager-job-listings',
			'wp-job-manager-resume-frontend',
			'job-alerts-frontend',
			'job-alerts-frontend-default',
			'jm-application-deadline',
			'wp-job-manager-applications-frontend',
			'wc-paid-listings-packages',
			'wp-job-manager-tags-frontend',
			'wpjml-job-application',
			'resume-alerts-frontend'
		];

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
	 * Dynamic CSS styles
	 *
	 * @since 1.5.2
	 */
	public function dynamic_styles() {
		$maincolor   = cariera_get_option( 'cariera_main_color' );
		$secondcolor = cariera_get_option( 'cariera_secondary_color' ); ?>

		<style type="text/css">
			:root {
				--cariera-primary: <?php echo esc_attr( $maincolor ); ?>;
				--cariera-secondary: <?php echo esc_attr( $secondcolor ); ?>;
				--cariera-body-bg: <?php echo esc_attr( cariera_get_option( 'cariera_body_color' ) ); ?>;
				--cariera-body-wrapper: <?php echo esc_attr( cariera_get_option( 'cariera_wrapper_color' ) ); ?>;
				--cariera-header-bg: <?php echo esc_attr( cariera_get_option( 'cariera_navbar_bg' ) ); ?>;
				--cariera-menu-hover: <?php echo esc_attr( cariera_get_option( 'cariera_menu_hover_color' ) ); ?>;
				--cariera-footer-bg: <?php echo esc_attr( cariera_get_option( 'cariera_footer_bg' ) ); ?>;
				--cariera-footer-title: <?php echo esc_attr( cariera_get_option( 'cariera_footer_title_color' ) ); ?>;
				--cariera-footer-color: <?php echo esc_attr( cariera_get_option( 'cariera_footer_text_color' ) ); ?>;
			}
			<?php

			// Logo.
			$logo_size_width = intval( cariera_get_option( 'logo_width' ) );
			$logo_css        = $logo_size_width ? 'width:' . esc_attr( $logo_size_width ) . 'px; ' : '';

			$logo_size_height = intval( cariera_get_option( 'logo_height' ) );
			$logo_css        .= $logo_size_height ? 'height:' . esc_attr( $logo_size_height ) . 'px; ' : '';

			$logo_margin = cariera_get_option( 'logo_margins' );
			$logo_css   .= $logo_margin['top'] ? 'margin-top:' . esc_attr( $logo_margin['top'] ) . ' !important; ' : '';
			$logo_css   .= $logo_margin['right'] ? 'margin-right:' . esc_attr( $logo_margin['right'] ) . ' !important; ' : '';
			$logo_css   .= $logo_margin['bottom'] ? 'margin-bottom:' . esc_attr( $logo_margin['bottom'] ) . ' !important; ' : '';
			$logo_css   .= $logo_margin['left'] ? 'margin-left:' . esc_attr( $logo_margin['left'] ) . ' !important; ' : '';

			if ( ! empty( $logo_css ) ) {
				echo ' header .navbar-brand img {' . esc_attr( $logo_css ) . '}';
			}

			// Home Page Background Image.
			$home_image  = cariera_get_option( 'home_page_image' );
			$home2_image = cariera_get_option( 'home_page2_image' );

			if ( ! empty( $home_image ) ) {
				echo 'section.home-search { background-image: url("' . esc_url( $home_image ) . '"); }';
			}

			if ( ! empty( $home2_image ) ) {
				echo 'section.home-search2 { background-image: url("' . esc_url( $home2_image ) . '"); }';
			}

			// Body boxed style.
			if ( cariera_get_option( 'cariera_body_style' ) === 'boxed' ) {
				$body_bg = cariera_get_option( 'cariera_body_bg' );

				if ( ! empty( $body_bg ) ) {
					$bg_horizontal  = cariera_get_option( 'cariera_body_bg_horizontal' );
					$bg_vertical    = cariera_get_option( 'cariera_body_bg_vertical' );
					$bg_repeats     = cariera_get_option( 'cariera_body_bg_repeats' );
					$bg_attachments = cariera_get_option( 'cariera_body_bg_attachments' );
					$bg_size        = cariera_get_option( 'cariera_body_bg_size' );

					echo 'body {
                        background-image: url(' . esc_attr( $body_bg ) . '); 
                        background-position:' . esc_attr( $bg_horizontal ) . ' ' . esc_attr( $bg_vertical ) . ';
                        background-repeat:' . esc_attr( $bg_repeats ) . ';
                        background-attachment:' . esc_attr( $bg_attachments ) . ';
                        background-size:' . esc_attr( $bg_size ) . ';
                    }';
				}
			}

			/* JOB & RESUME */
			if ( cariera_get_option( 'cariera_job_auto_location' ) == false ) {
				echo '.geolocation { 
                    display: none !important;
                }';
			}

			// Radius Scale.
			if ( cariera_get_option( 'cariera_search_radius' ) ) {
				$radius_scale = cariera_get_option( 'cariera_radius_unit' );
				echo ".range-output:after {
                    content: '$radius_scale';
                }";
			}
			?>
		</style>
		<?php
	}
}

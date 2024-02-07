<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Setup {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.5.2
	 */
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'setup' ] );
		add_action( 'wp_head', [ $this, 'pingback_header' ] );
		add_action( 'after_switch_theme', [ $this, 'switch_theme_settings' ] );
		add_action( 'widgets_init', [ $this, 'widgets' ] );
		add_filter( 'body_class', [ $this, 'body_class' ] );
	}

	/**
	 * Main theme setup function
	 *
	 * @since 1.5.2
	 */
	public function setup() {

		/* Set the content width */
		$GLOBALS['content_width'] = apply_filters( 'cariera_content_width', 980 );
		if ( ! isset( $content_width ) ) {
			$content_width = 980;
		}

		/* Make theme available for translation */
		load_theme_textdomain( 'cariera', get_template_directory() . '/lang' );

		/* Enable Support for Post Thumbnails */
		add_theme_support( 'post-thumbnails' );
		set_post_thumbnail_size( 840, 350, true );
		add_image_size( 'blog', 1000, 563, true );

		/* Enable Support for Post Formats */
		add_theme_support( 'post-formats', [ 'aside', 'audio', 'image', 'gallery', 'quote', 'video' ] );

		/* Change default markup to output valid HTML5  */
		add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ] );

		/* Add default posts and comments RSS feed links to head. */
		add_theme_support( 'automatic-feed-links' );

		/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
		add_theme_support( 'title-tag' );

		/* Enable Support for WP Job Manager Templates */
		add_theme_support( 'job-manager-templates' );
		add_theme_support( 'resume-manager-templates' );

		/* Enable Support for WooCommerce */
		add_theme_support( 'woocommerce' );

		/* Support images for Gutenberg */
		add_theme_support( 'align-wide' );

		/* Enable WooCommerce support for lightbox, zoom and gallery slider */
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		/* Register theme navs */
		register_nav_menus(
			[
				'primary'        => esc_html__( 'Primary Menu', 'cariera' ),
				'employer-dash'  => esc_html__( 'Extra Menu for Employer Dashboard', 'cariera' ),
				'candidate-dash' => esc_html__( 'Extra Menu for Candidate Dashboard', 'cariera' ),
			]
		);

		// Disable admin bar for non admins.
		$this->disable_admin_bar();
	}

	/**
	 * Hide WP Admin Bar
	 *
	 * @since   1.3.7
	 * @version 1.7.2
	 */
	private function disable_admin_bar() {
		if ( current_user_can( 'administrator' ) ) {
			return;
		}

		show_admin_bar( false );
	}

	/**
	 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 */
	public function pingback_header() {
		if ( is_singular() && pings_open() ) {
			echo '<link rel="pingback" href="' . esc_url( get_bloginfo( 'pingback_url' ) ) . '">';
		}
	}

	/**
	 * Sets up theme default settings.
	 *
	 * @since   1.3.0
	 * @version 1.7.0
	 */
	public function switch_theme_settings() {
		update_option( 'job_manager_enable_categories', 1 );
		update_option( 'job_manager_enable_types', 1 );
		update_option( 'job_manager_enable_salary', 0 );
		update_option( 'resume_manager_enable_categories', 1 );
		update_option( 'resume_manager_enable_skills', 1 );
	}

	/**
	 * Main widget areas init
	 *
	 * @since   1.0.0
	 * @version 1.5.2
	 */
	public function widgets() {

		for ( $i = 1; $i <= 2; $i++ ) {
			register_sidebar(
				[
					'name'          => sprintf( esc_html__( 'Top Header Widget Area Column %d', 'cariera' ), absint( $i ) ),
					'id'            => 'top-header-widget-area' . ( $i > 1 ? ( '-' . absint( $i ) ) : '' ),
					'description'   => esc_html__( 'Choose what should display in this top header widget column.', 'cariera' ),
					'before_widget' => '<aside id="%1$s" class="widget top-header-widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h3 class="widget-title widget-title-top-header">',
					'after_title'   => '</h3>',
				]
			);
		}

		register_sidebar(
			[
				'id'            => 'sidebar-1',
				'name'          => esc_html__( 'Sidebar', 'cariera' ),
				'description'   => esc_html__( 'The primary widget area', 'cariera' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
			]
		);

		register_sidebar(
			[
				'id'            => 'sidebar-jobs',
				'name'          => esc_html__( 'Jobs - Sidebar', 'cariera' ),
				'description'   => esc_html__( 'The sidebar widget area that can be used on general job pages.', 'cariera' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
			]
		);

		register_sidebar(
			[
				'id'            => 'sidebar-single-job',
				'name'          => esc_html__( 'Single Job - Sidebar', 'cariera' ),
				'description'   => esc_html__( 'The sidebar widget area for single job page.', 'cariera' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
			]
		);

		register_sidebar(
			[
				'id'            => 'sidebar-company',
				'name'          => esc_html__( 'Company - Sidebar', 'cariera' ),
				'description'   => esc_html__( 'The sidebar widget area that can be used on general company pages.', 'cariera' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
			]
		);

		register_sidebar(
			[
				'id'            => 'sidebar-single-company',
				'name'          => esc_html__( 'Single Company - Sidebar', 'cariera' ),
				'description'   => esc_html__( 'The sidebar widget area for single company page.', 'cariera' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
			]
		);

		register_sidebar(
			[
				'id'            => 'sidebar-resumes',
				'name'          => esc_html__( 'Resumes - Sidebar', 'cariera' ),
				'description'   => esc_html__( 'The sidebar widget area that can be used on general resume pages.', 'cariera' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
			]
		);

		register_sidebar(
			[
				'id'            => 'sidebar-single-resume',
				'name'          => esc_html__( 'Single Resume - Sidebar', 'cariera' ),
				'description'   => esc_html__( 'The sidebar widget area for single resume page', 'cariera' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
			]
		);

		register_sidebar(
			[
				'id'            => 'sidebar-shop',
				'name'          => esc_html__( 'Shop - Sidebar', 'cariera' ),
				'description'   => esc_html__( 'The shop sidebar widget area', 'cariera' ),
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'  => '</aside>',
				'before_title'  => '<h5 class="widget-title">',
				'after_title'   => '</h5>',
			]
		);

		for ( $i = 1; $i <= 4; $i++ ) {
			register_sidebar(
				[
					'name'          => sprintf( esc_html__( 'Footer Widget Area Column %d', 'cariera' ), absint( $i ) ),
					'id'            => 'footer-widget-area' . ( $i > 1 ? ( '-' . absint( $i ) ) : '' ),
					'description'   => esc_html__( 'Choose what should display in this footer widget column.', 'cariera' ),
					'before_widget' => '<aside id="%1$s" class="widget footer-widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h3 class="widget-title widget-title-footer pb40">',
					'after_title'   => '</h3>',
				]
			);
		}
	}

	/**
	 * Adding classes to the body
	 *
	 * @param string $classes
	 * @since   1.3.0
	 * @version 1.5.2
	 */
	public function body_class( $classes ) {

		if ( is_user_logged_in() ) {
			$classes[] = 'user-logged-in';
		} else {
			$classes[] = 'user-not-logged-in';
		}

		return $classes;
	}
}

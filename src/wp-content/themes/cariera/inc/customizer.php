<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Customize {

	// Customize settings.
	protected $config = [];

	// The class constructor.
	public function __construct( $config ) {
		$this->config = $config;

		if ( ! class_exists( 'Kirki' ) ) {
			return;
		}

		$this->register();
	}

	/** Register settings **/
	public function register() {

		// Add the theme configuration.
		if ( ! empty( $this->config['theme'] ) ) {
			Kirki::add_config(
				$this->config['theme'],
				[
					'capability'  => 'edit_theme_options',
					'option_type' => 'theme_mod',
				]
			);
		}

		// Add panels.
		if ( ! empty( $this->config['panels'] ) ) {
			foreach ( $this->config['panels'] as $panel => $settings ) {
				Kirki::add_panel( $panel, $settings );
			}
		}

		// Add sections.
		if ( ! empty( $this->config['sections'] ) ) {
			foreach ( $this->config['sections'] as $section => $settings ) {
				Kirki::add_section( $section, $settings );
			}
		}

		/** Add fields */
		if ( ! empty( $this->config['theme'] ) && ! empty( $this->config['fields'] ) ) {
			foreach ( $this->config['fields'] as $name => $settings ) {
				if ( ! isset( $settings['settings'] ) ) {
					$settings['settings'] = $name;
				}

				Kirki::add_field( $this->config['theme'], $settings );
			}
		}
	}

	/** Get config ID **/
	public function get_theme() {
		return $this->config['theme'];
	}

	/** Get customize setting value **/
	public function get_option( $name ) {
		if ( ! isset( $this->config['fields'][ $name ] ) ) {
			return false;
		}

		$default = isset( $this->config['fields'][ $name ]['default'] ) ? $this->config['fields'][ $name ]['default'] : false;

		return get_theme_mod( $name, $default );
	}
}

/** Move some default sections to 'general' panel **/
function cariera_customize_modify( $wp_customize ) {
	$wp_customize->get_section( 'title_tagline' )->panel     = 'general';
	$wp_customize->get_section( 'static_front_page' )->panel = 'general';
}

add_action( 'customize_register', 'cariera_customize_modify' );

/** This is a short hand function for getting setting value from customizer */
if ( ! function_exists( 'cariera_get_option' ) ) {
	function cariera_get_option( $name ) {
		global $cariera_customize;

		$value = false;

		if ( class_exists( 'Kirki' ) ) {
			$value = Kirki::get_option( 'cariera', $name );
		} elseif ( ! empty( $cariera_customize ) ) {
			$value = $cariera_customize->get_option( $name );
		}

		return apply_filters( 'cariera_get_option', $value, $name );
	}
}

/** Get customize settings **/
function cariera_customize_settings() {

	/** Customizer configuration */
	return [
		'theme'    => 'cariera',

		'panels'   => [

			// GENERAL OPTIONS.
			'general'         => [
				'priority'    => 10,
				'title'       => esc_html__( 'General Options', 'cariera' ),
				'description' => esc_html__( 'General options', 'cariera' ),
			],

			// TYPOGRAPHY GENERAL OPTIONS.
			'typo_general'    => [
				'priority'    => 10,
				'title'       => esc_html__( 'Typography Options', 'cariera' ),
				'description' => esc_html__( 'Typography related options', 'cariera' ),
			],

			// HEADER GENERAL OPTIONS.
			'header_general'  => [
				'priority'    => 10,
				'title'       => esc_html__( 'Header Options', 'cariera' ),
				'description' => esc_html__( 'Header related options', 'cariera' ),
			],

			// JOBS OPTIONS.
			'jobs_general'    => [
				'priority'    => 10,
				'title'       => esc_html__( 'Job Options', 'cariera' ),
				'description' => esc_html__( 'Job related options', 'cariera' ),
			],

			// COMPANY OPTIONS.
			'company_general' => [
				'priority'    => 10,
				'title'       => esc_html__( 'Company Options', 'cariera' ),
				'description' => esc_html__( 'Company related options', 'cariera' ),
			],

			// RESUMES OPTIONS.
			'resumes_general' => [
				'priority'    => 10,
				'title'       => esc_html__( 'Resumes Options', 'cariera' ),
				'description' => esc_html__( 'Resumes related options', 'cariera' ),
			],

			// BLOG OPTIONS.
			'blog_general'    => [
				'priority'    => 10,
				'title'       => esc_html__( 'Blog Options', 'cariera' ),
				'description' => esc_html__( 'Blog related options', 'cariera' ),
			],

			// PAGES OPTIONS.
			'pages_general'   => [
				'priority'    => 12,
				'title'       => esc_html__( 'Pages Options', 'cariera' ),
				'description' => esc_html__( 'Pages related options', 'cariera' ),
			],

			// EXTRA OPTIONS.
			'extra_general'   => [
				'priority'    => 12,
				'title'       => esc_html__( 'Extra Options', 'cariera' ),
				'description' => '',
			],
		], // End of panels array.

		'sections' => [

			// LAYOUT OPTIONS.
			'layout'                   => [
				'title'          => esc_html__( 'Layout Options', 'cariera' ),
				'description'    => esc_html__( 'General layout options', 'cariera' ),
				'panel'          => 'general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// COLORS OPTIONS.
			'colors'                   => [
				'title'          => esc_html__( 'Color Options', 'cariera' ),
				'description'    => '',
				'panel'          => 'general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// BODY - TYPOGRAPHY OPTIONS.
			'body_typo'                => [
				'title'          => esc_html__( 'Body', 'cariera' ),
				'description'    => '',
				'panel'          => 'typo_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// HEADINGS - TYPOGRAPHY OPTIONS.
			'headings_typo'            => [
				'title'          => esc_html__( 'Heading', 'cariera' ),
				'description'    => '',
				'panel'          => 'typo_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// MENU - TYPOGRAPHY OPTIONS.
			'menu_typo'                => [
				'title'          => esc_html__( 'Menu', 'cariera' ),
				'description'    => '',
				'panel'          => 'typo_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// LOGO OPTIONS.
			'logo'                     => [
				'title'          => esc_html__( 'Logo', 'cariera' ),
				'description'    => '',
				'panel'          => 'header_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// MAIN HEADER OPTIONS.
			'header'                   => [
				'title'          => esc_html__( 'Header Options', 'cariera' ),
				'description'    => esc_html__( 'Header related options', 'cariera' ),
				'panel'          => 'header_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// JOB OPTIONS.
			'job_options'              => [
				'title'          => esc_html__( 'Job Options', 'cariera' ),
				'description'    => esc_html__( 'Job related options', 'cariera' ),
				'panel'          => 'jobs_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// JOB TAXONOMY OPTIONS.
			'job_taxonomy_options'     => [
				'title'          => esc_html__( 'Job Taxonomy Options', 'cariera' ),
				'description'    => esc_html__( 'Job Taxonomy view options', 'cariera' ),
				'panel'          => 'jobs_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// HALF MAP JOB OPTIONS.
			'half_map_job_options'     => [
				'title'          => esc_html__( 'Half Map Job Options', 'cariera' ),
				'description'    => esc_html__( 'Half map job related options', 'cariera' ),
				'panel'          => 'jobs_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// SINGLE JOB OPTIONS.
			'single_job_option'        => [
				'title'          => esc_html__( 'Single Job Options', 'cariera' ),
				'description'    => esc_html__( 'Single job related options', 'cariera' ),
				'panel'          => 'jobs_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// COMPANY OPTIONS.
			'company_options'          => [
				'title'          => esc_html__( 'Company Options', 'cariera' ),
				'description'    => esc_html__( 'Company related options', 'cariera' ),
				'panel'          => 'company_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// HALF MAP COMPANY OPTIONS.
			'half_map_company_options' => [
				'title'          => esc_html__( 'Half Map Company Options', 'cariera' ),
				'description'    => esc_html__( 'Half map company related options', 'cariera' ),
				'panel'          => 'company_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// SINGLE COMPANY OPTIONS.
			'single_company_options'   => [
				'title'          => esc_html__( 'Single Company Options', 'cariera' ),
				'description'    => esc_html__( 'Single company related options', 'cariera' ),
				'panel'          => 'company_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// RESUME OPTIONS.
			'resume_options'           => [
				'title'          => esc_html__( 'Resume Options', 'cariera' ),
				'description'    => esc_html__( 'Resume related options', 'cariera' ),
				'panel'          => 'resumes_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// RESUME TAXONOMY OPTIONS.
			'resume_taxonomy_options'  => [
				'title'          => esc_html__( 'Resume Taxonomy Options', 'cariera' ),
				'description'    => esc_html__( 'Resume Taxonomy view options', 'cariera' ),
				'panel'          => 'resumes_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// HALF MAP RESUME OPTIONS.
			'half_map_resume_options'  => [
				'title'          => esc_html__( 'Half Map Resume Options', 'cariera' ),
				'description'    => esc_html__( 'Half map resume related options', 'cariera' ),
				'panel'          => 'resumes_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// SINGLE RESUME OPTIONS.
			'single_resume_options'    => [
				'title'          => esc_html__( 'Single Resume Options', 'cariera' ),
				'description'    => esc_html__( 'Single resume related options', 'cariera' ),
				'panel'          => 'resumes_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// PAGE HEADER BLOG OPTIONS.
			'page_header_blog'         => [
				'title'          => esc_html__( 'Page Header Blog Options', 'cariera' ),
				'description'    => esc_html__( 'Page header related options', 'cariera' ),
				'panel'          => 'blog_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// GENERAL BLOG OPTIONS.
			'blog'                     => [
				'title'          => esc_html__( 'General Blog Options', 'cariera' ),
				'description'    => esc_html__( 'Blog related options', 'cariera' ),
				'panel'          => 'blog_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// SINGLE BLOG POST OPTIONS.
			'single_blog_post'         => [
				'title'          => esc_html__( 'Single Blog Post Options', 'cariera' ),
				'description'    => esc_html__( 'Single blog post related options', 'cariera' ),
				'panel'          => 'blog_general', // Not typically needed.
				'priority'       => 160,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// WOOCOMMERCE OPTIONS.
			'woocommerce_options'      => [
				'title'          => esc_html__( 'General Options', 'cariera' ),
				'description'    => esc_html__( 'Woocommerce related options', 'cariera' ),
				'panel'          => 'woocommerce', // Not typically needed.
				'priority'       => 1,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// FOOTER OPTIONS.
			'footer'                   => [
				'title'          => esc_html__( 'Footer Options', 'cariera' ),
				'description'    => esc_html__( 'Footer related options', 'cariera' ),
				'panel'          => '', // Not typically needed.
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// USER DASHBOARD OPTIONS.
			'dashboard'                => [
				'title'          => esc_html__( 'Dashboard Options', 'cariera' ),
				'description'    => esc_html__( 'User Dashboard related options', 'cariera' ),
				'panel'          => '', // Not typically needed.
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// HOME PAGE OPTIONS.
			'home_page'                => [
				'title'          => esc_html__( 'Home Page 1 - Search Banner Ver 1', 'cariera' ),
				'description'    => esc_html__( 'Home page related options', 'cariera' ),
				'panel'          => 'pages_general',
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// LOGIN PAGE OPTIONS.
			'login_page'               => [
				'title'          => esc_html__( 'Login & Register Page Options', 'cariera' ),
				'description'    => esc_html__( 'Login & Register related options', 'cariera' ),
				'panel'          => 'pages_general',
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// MAP OPTIONS.
			'map_options'              => [
				'title'          => esc_html__( 'Map Options', 'cariera' ),
				'description'    => esc_html__( 'Map related options', 'cariera' ),
				'panel'          => 'extra_general', // Not typically needed.
				'priority'       => 12,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],

			// COOKIE BAR OPTIONS.
			'cookie_bar'               => [
				'title'          => esc_html__( 'Cookie Notice Options', 'cariera' ),
				'description'    => '',
				'panel'          => 'extra_general', // Not typically needed.
				'priority'       => 12,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '', // Rarely needed.
			],
		], // End of sections array.

		'fields'   => [

			// LOGO OPTIONS.
			'logo'                                         => [
				'type'        => 'image',
				'label'       => esc_html__( 'Logo', 'cariera' ),
				'description' => esc_html__( 'This logo is used for all site.', 'cariera' ),
				'section'     => 'logo',
				'default'     => '',
				'priority'    => 20,
			],

			'logo-white'                                   => [
				'type'        => 'image',
				'label'       => esc_html__( 'Logo White', 'cariera' ),
				'description' => esc_html__( 'This white version of the logo can be used for transparent header.', 'cariera' ),
				'section'     => 'logo',
				'default'     => '',
				'priority'    => 20,
			],

			'logo_text'                                    => [
				'type'     => 'text',
				'label'    => esc_html__( 'Text Logo', 'cariera' ),
				'section'  => 'logo',
				'priority' => 20,
				[
					'setting'  => 'logo',
					'operator' => '!=',
					'value'    => '',
				],
			],

			'logo_width'                                   => [
				'type'     => 'text',
				'label'    => esc_html__( 'Logo Width(px)', 'cariera' ),
				'section'  => 'logo',
				'priority' => 20,
				'default'  => '150',
				[
					'setting'  => 'logo',
					'operator' => '!=',
					'value'    => '',
				],
			],

			'logo_height'                                  => [
				'type'     => 'text',
				'label'    => esc_html__( 'Logo Height(px)', 'cariera' ),
				'section'  => 'logo',
				'priority' => 20,
				'default'  => '',
				[
					'setting'  => 'logo',
					'operator' => '!=',
					'value'    => '',
				],
			],

			'logo_margins'                                 => [
				'type'        => 'spacing',
				'label'       => esc_html__( 'Logo Margin', 'cariera' ),
				'description' => '',
				'section'     => 'logo',
				'priority'    => 20,
				'default'     => [
					'top'    => '0px',
					'bottom' => '0px',
					'left'   => '0px',
					'right'  => '0px',
				],
				[
					'setting'  => 'logo',
					'operator' => '!=',
					'value'    => '',
				],
			],

			// MAIN HEADER OPTIONS.
			'cariera_header_style'                         => [
				'type'        => 'select',
				'label'       => esc_html__( 'Layout Style', 'cariera' ),
				'description' => esc_html__( 'Choose your header version.', 'cariera' ),
				'section'     => 'header',
				'default'     => 'header1',
				'priority'    => 10,
				'choices'     => [
					'header1' => esc_html__( 'Header 1 - Default', 'cariera' ),
					'header2' => esc_html__( 'Header 2 - Logo on top', 'cariera' ),
				],
			],

			'cariera_top_header'                           => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Top Header Bar', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "ON" to enable the Top Bar Header.', 'cariera' ),
				'section'     => 'header',
				'default'     => false,
				'priority'    => 10,
				/*
				'active_callback' => array(
					array(
						'setting'  => 'cariera_header_style',
						'value'    => 'header1',
						'operator' => '==',
					),
				), */
			],

			'cariera_top_header_style'                     => [
				'type'            => 'select',
				'label'           => esc_html__( 'Style - Skin', 'cariera' ),
				'description'     => '',
				'section'         => 'header',
				'description'     => 'Choose your top bar header style-skin.',
				'default'         => 'top-header-light',
				'priority'        => 10,
				'choices'         => [
					'top-header-light' => esc_html__( 'Light Skin', 'cariera' ),
					'top-header-dark'  => esc_html__( 'Dark Skin', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_top_header',
						'value'    => 1,
						'operator' => '==',
					],
				],
			],

			'cariera_navbar_bg'                            => [
				'type'        => 'color',
				'label'       => esc_html__( 'Header Background Color', 'cariera' ),
				'description' => esc_html__( 'Select the background color for your header.', 'cariera' ),
				'section'     => 'header',
				'default'     => '#fff',
				'priority'    => 10,
			],

			'cariera_fullwidth_header'                     => [
				'type'            => 'switch',
				'label'           => esc_html__( 'Full Width Header', 'cariera' ),
				'description'     => esc_html__( 'Turn the switch "ON" to enable Full Width Header.', 'cariera' ),
				'section'         => 'header',
				'default'         => true,
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_header_style',
						'value'    => 'header1',
						'operator' => '==',
					],
				],
			],

			'cariera_sticky_header'                        => [
				'type'            => 'switch',
				'label'           => esc_html__( 'Sticky Header', 'cariera' ),
				'description'     => esc_html__( 'Turn the switch "ON" to enable Sticky Header.', 'cariera' ),
				'section'         => 'header',
				'default'         => false,
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_header_style',
						'value'    => 'header1',
						'operator' => '==',
					],
				],
			],

			'cariera_header_custom'                        => [
				'type'     => 'custom',
				'section'  => 'header',
				'default'  => '<hr>',
				'priority' => 10,
			],

			'header_cart'                                  => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Header Cart', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable Header Cart.', 'cariera' ),
				'section'     => 'header',
				'default'     => true,
				'priority'    => 10,
			],

			'header_quick_search'                          => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Header Quick Search', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable Header Quick Search.', 'cariera' ),
				'section'     => 'header',
				'default'     => false,
				'priority'    => 10,
			],

			'header_account'                               => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Header Login/Account', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable Header Login/Account.', 'cariera' ),
				'section'     => 'header',
				'default'     => true,
				'priority'    => 10,
			],

			'header_cta'                                   => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Header CTA', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable Header CTA.', 'cariera' ),
				'section'     => 'header',
				'default'     => true,
				'priority'    => 10,
			],

			// GENERAL - LAYOUT OPTIONS.
			'cariera_body_style'                           => [
				'type'        => 'select',
				'label'       => esc_html__( 'Layout style', 'cariera' ),
				'description' => '',
				'section'     => 'layout',
				'default'     => 'fullwidth',
				'priority'    => 10,
				'choices'     => [
					'fullwidth' => esc_html__( 'Full-Width', 'cariera' ),
					'boxed'     => esc_html__( 'Boxed', 'cariera' ),
				],
			],

			'cariera_body_bg'                              => [
				'type'            => 'upload',
				'label'           => esc_html__( 'Background Image', 'cariera' ),
				'description'     => esc_html__( 'Upload your a background image for the "body"', 'cariera' ),
				'section'         => 'layout',
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_body_style',
						'value'    => 'boxed',
						'operator' => '==',
					],
				],
			],

			'cariera_body_bg_horizontal'                   => [
				'type'            => 'select',
				'label'           => esc_html__( 'Background Horizontal', 'cariera' ),
				'default'         => 'left',
				'section'         => 'layout',
				'priority'        => 10,
				'choices'         => [
					'left'   => esc_html__( 'Left', 'cariera' ),
					'right'  => esc_html__( 'Right', 'cariera' ),
					'center' => esc_html__( 'Center', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_body_style',
						'value'    => 'boxed',
						'operator' => '==',
					],
				],
			],

			'cariera_body_bg_vertical'                     => [
				'type'            => 'select',
				'label'           => esc_html__( 'Background Vertical', 'cariera' ),
				'default'         => 'top',
				'section'         => 'layout',
				'priority'        => 10,
				'choices'         => [
					'top'    => esc_html__( 'Top', 'cariera' ),
					'center' => esc_html__( 'Center', 'cariera' ),
					'bottom' => esc_html__( 'Bottom', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_body_style',
						'value'    => 'boxed',
						'operator' => '==',
					],
				],
			],

			'cariera_body_bg_repeats'                      => [
				'type'            => 'select',
				'label'           => esc_html__( 'Background Repeat', 'cariera' ),
				'default'         => 'repeat',
				'section'         => 'layout',
				'priority'        => 10,
				'choices'         => [
					'repeat'    => esc_html__( 'Repeat', 'cariera' ),
					'repeat-x'  => esc_html__( 'Repeat Horizontally', 'cariera' ),
					'repeat-y'  => esc_html__( 'Repeat Vertically', 'cariera' ),
					'no-repeat' => esc_html__( 'No Repeat', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_body_style',
						'value'    => 'boxed',
						'operator' => '==',
					],
				],
			],

			'cariera_body_bg_attachments'                  => [
				'type'            => 'select',
				'label'           => esc_html__( 'Background Attachment', 'cariera' ),
				'default'         => 'scroll',
				'section'         => 'layout',
				'priority'        => 10,
				'choices'         => [
					'scroll' => esc_html__( 'Scroll', 'cariera' ),
					'fixed'  => esc_html__( 'Fixed', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_body_style',
						'value'    => 'boxed',
						'operator' => '==',
					],
				],
			],

			'cariera_body_bg_size'                         => [
				'type'            => 'select',
				'label'           => esc_html__( 'Background Size', 'cariera' ),
				'default'         => 'normal',
				'section'         => 'layout',
				'priority'        => 10,
				'choices'         => [
					'normal'  => esc_html__( 'Normal', 'cariera' ),
					'contain' => esc_html__( 'Contain', 'cariera' ),
					'cover'   => esc_html__( 'Cover', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_body_style',
						'value'    => 'boxed',
						'operator' => '==',
					],
				],
			],

			'cariera_preloader'                            => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Preloader', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "ON" to enable the website preloader.', 'cariera' ),
				'section'     => 'layout',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_preloader_version'                    => [
				'type'            => 'select',
				'label'           => esc_html__( 'Preloader style', 'cariera' ),
				'description'     => '',
				'section'         => 'layout',
				'default'         => 'preloader4',
				'priority'        => 10,
				'choices'         => [
					'preloader1' => esc_html__( 'Preloader Version 1', 'cariera' ),
					'preloader2' => esc_html__( 'Preloader Version 2', 'cariera' ),
					'preloader3' => esc_html__( 'Preloader Version 3', 'cariera' ),
					'preloader4' => esc_html__( 'Preloader Version 4', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_preloader',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

			'cariera_back_top'                             => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Back to Top Button', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable the back to top button.', 'cariera' ),
				'section'     => 'layout',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_breadcrumbs'                          => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Breadcrumbs', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable all breadcrumbs.', 'cariera' ),
				'section'     => 'layout',
				'default'     => 1,
				'priority'    => 10,
			],

			// GENERAL - COLORS OPTIONS.
			'cariera_body_color'                           => [
				'type'        => 'color',
				'label'       => esc_html__( 'Select body color', 'cariera' ),
				'description' => '',
				'section'     => 'colors',
				'default'     => '#fff',
				'priority'    => 10,
			],

			'cariera_wrapper_color'                        => [
				'type'        => 'color',
				'label'       => esc_html__( 'Select body wrapper color', 'cariera' ),
				'description' => '',
				'section'     => 'colors',
				'default'     => '#fff',
				'priority'    => 10,
			],

			'cariera_main_color'                           => [
				'type'        => 'color',
				'label'       => esc_html__( 'Select main theme color', 'cariera' ),
				'description' => '',
				'section'     => 'colors',
				'default'     => '#303af7',
				'priority'    => 10,
			],

			'cariera_secondary_color'                      => [
				'type'        => 'color',
				'label'       => esc_html__( 'Select secondary theme color', 'cariera' ),
				'description' => '',
				'section'     => 'colors',
				'default'     => '#443088',
				'priority'    => 10,
			],

			// BODY - TYPOGRAPHY OPTIONS.
			'cariera_body_typo'                            => [
				'type'        => 'typography',
				'label'       => esc_html__( 'Body Typography', 'cariera' ),
				'description' => '',
				'section'     => 'body_typo',
				'priority'    => 10,
				'default'     => [
					'font-family'    => 'Varela Round',
					'variant'        => 'regular',
					'font-size'      => '16px',
					'line-height'    => '1.65',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#948a99',
					'text-transform' => 'none',
				],
				'output'      => [
					[
						'element' => 'body',
					],
				],
			],

			// HEADINGS - TYPOGRAPHY OPTIONS.
			'cariera_heading1_typo'                        => [
				'type'     => 'typography',
				'label'    => esc_html__( 'Heading 1', 'cariera' ),
				'section'  => 'headings_typo',
				'priority' => 10,
				'default'  => [
					'font-family'    => 'Varela Round',
					'variant'        => '500',
					'font-size'      => '46px',
					'line-height'    => '1.3',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#333',
					'text-transform' => 'none',
				],
				'output'   => [
					[
						'element' => 'h1',
					],
				],
			],

			'cariera_heading2_typo'                        => [
				'type'     => 'typography',
				'label'    => esc_html__( 'Heading 2', 'cariera' ),
				'section'  => 'headings_typo',
				'priority' => 10,
				'default'  => [
					'font-family'    => 'Varela Round',
					'variant'        => '500',
					'font-size'      => '38px',
					'line-height'    => '1.3',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#333',
					'text-transform' => 'none',
				],
				'output'   => [
					[
						'element' => 'h2',
					],
				],
			],

			'cariera_heading3_typo'                        => [
				'type'     => 'typography',
				'label'    => esc_html__( 'Heading 3', 'cariera' ),
				'section'  => 'headings_typo',
				'priority' => 10,
				'default'  => [
					'font-family'    => 'Varela Round',
					'variant'        => '500',
					'font-size'      => '30px',
					'line-height'    => '1.3',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#333',
					'text-transform' => 'none',
				],
				'output'   => [
					[
						'element' => 'h3',
					],
				],
			],

			'cariera_heading4_typo'                        => [
				'type'     => 'typography',
				'label'    => esc_html__( 'Heading 4', 'cariera' ),
				'section'  => 'headings_typo',
				'priority' => 10,
				'default'  => [
					'font-family'    => 'Varela Round',
					'variant'        => '500',
					'font-size'      => '24px',
					'line-height'    => '1.3',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#333',
					'text-transform' => 'none',
				],
				'output'   => [
					[
						'element' => 'h4',
					],
				],
			],

			'cariera_heading5_typo'                        => [
				'type'     => 'typography',
				'label'    => esc_html__( 'Heading 5', 'cariera' ),
				'section'  => 'headings_typo',
				'priority' => 10,
				'default'  => [
					'font-family'    => 'Varela Round',
					'variant'        => '500',
					'font-size'      => '20px',
					'line-height'    => '1.3',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#333',
					'text-transform' => 'none',
				],
				'output'   => [
					[
						'element' => 'h5',
					],
				],
			],

			'cariera_heading6_typo'                        => [
				'type'     => 'typography',
				'label'    => esc_html__( 'Heading 6', 'cariera' ),
				'section'  => 'headings_typo',
				'priority' => 10,
				'default'  => [
					'font-family'    => 'Varela Round',
					'variant'        => '500',
					'font-size'      => '18px',
					'line-height'    => '1.3',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#333',
					'text-transform' => 'none',
				],
				'output'   => [
					[
						'element' => 'h6',
					],
				],
			],

			// MENU - TYPOGRAPHY OPTIONS.
			'cariera_menu_typo'                            => [
				'type'     => 'typography',
				'label'    => esc_html__( 'Menu', 'cariera' ),
				'section'  => 'menu_typo',
				'priority' => 10,
				'default'  => [
					'font-family'    => 'Varela Round',
					'variant'        => '600',
					'font-size'      => '14px',
					'line-height'    => '1.4',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#666',
					'text-transform' => 'capitalize',
				],
				'output'   => [
					[
						'element' => 'ul.main-nav .menu-item a, header.main-header .extra-menu-item > a',
					],
				],
			],

			'cariera_submenu_typo'                         => [
				'type'     => 'typography',
				'label'    => esc_html__( 'Submenu Item', 'cariera' ),
				'section'  => 'menu_typo',
				'priority' => 10,
				'default'  => [
					'font-family'    => 'Varela Round',
					'variant'        => '600',
					'font-size'      => '14px',
					'line-height'    => '1.4',
					'letter-spacing' => '0',
					'subsets'        => '',
					'color'          => '#666',
					'text-transform' => 'capitalize',
				],
				'output'   => [
					[
						'element' => 'ul.main-nav .menu-item.dropdown .dropdown-menu > li > a, ul.main-nav .mega-menu .dropdown-menu .mega-menu-inner .menu-item-mega .sub-menu a',
					],
				],
			],

			'cariera_menu_hover_color'                     => [
				'type'        => 'color',
				'label'       => esc_html__( 'Menu Items Hover Color', 'cariera' ),
				'description' => esc_html__( 'Color for any menu item when hovering.', 'cariera' ),
				'section'     => 'menu_typo',
				'default'     => '#303af7',
				'priority'    => 10,
			],

			// GENERAL JOB OPTIONS.
			'cariera_job_search_map'                       => [
				'type'        => 'switch',
				'label'       => esc_html__( 'General Job Search Map', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" if you want to disable the job search map.', 'cariera' ),
				'section'     => 'job_options',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_job_ajax_search'                      => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Job Search - Keyword Autocomplete', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to disable job autocomplete AJAX search. This works only for the search forms that are added via Elementor.', 'cariera' ),
				'section'     => 'job_options',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_job_category_custom'                  => [
				'type'     => 'custom',
				'section'  => 'job_options',
				'default'  => '<hr>',
				'priority' => 10,
			],

			'cariera_job_category_bg'                      => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Job Category Background', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the category background on single category pages.', 'cariera' ),
				'section'     => 'job_options',
				'default'     => 1,
				'priority'    => 10,
			],

			// JOB TAXONOMY OPTIONS.
			'cariera_job_taxonomy_layout'                  => [
				'type'        => 'select',
				'label'       => esc_html__( 'Job Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the layout for the jobs that will be displayed in the taxonomy pages.', 'cariera' ),
				'section'     => 'job_taxonomy_options',
				'default'     => 'list',
				'priority'    => 10,
				'choices'     => [
					'list' => esc_attr__( 'List Layout', 'cariera' ),
					'grid' => esc_attr__( 'Grid Layout', 'cariera' ),
				],
			],

			'cariera_job_taxonomy_list_version'            => [
				'type'            => 'select',
				'label'           => esc_html__( 'Job List Layout', 'cariera' ),
				'description'     => esc_html__( 'Choose the list layout for the jobs.', 'cariera' ),
				'section'         => 'job_taxonomy_options',
				'default'         => '1',
				'priority'        => 10,
				'choices'         => [
					'1' => esc_attr__( 'List Layout 1', 'cariera' ),
					'2' => esc_attr__( 'List Layout 2', 'cariera' ),
					'3' => esc_attr__( 'List Layout 3', 'cariera' ),
					'4' => esc_attr__( 'List Layout 4', 'cariera' ),
					'5' => esc_attr__( 'List Layout 5', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_job_taxonomy_layout',
						'value'    => 'list',
						'operator' => '==',
					],
				],
			],

			'cariera_job_taxonomy_grid_version'            => [
				'type'            => 'select',
				'label'           => esc_html__( 'Job Grid Layout', 'cariera' ),
				'description'     => esc_html__( 'Choose the grid layout for the jobs.', 'cariera' ),
				'section'         => 'job_taxonomy_options',
				'default'         => '1',
				'priority'        => 10,
				'choices'         => [
					'1' => esc_attr__( 'Grid Layout 1', 'cariera' ),
					'2' => esc_attr__( 'Grid Layout 2', 'cariera' ),
					'3' => esc_attr__( 'Grid Layout 3', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_job_taxonomy_layout',
						'value'    => 'grid',
						'operator' => '==',
					],
				],
			],

			// HALF MAP JOB OPTIONS.
			'cariera_job_half_map_text'                    => [
				'type'        => 'text',
				'label'       => esc_html__( 'Half Map Text', 'cariera' ),
				'description' => esc_html__( 'Add the text that will show up on top of the search.', 'cariera' ),
				'section'     => 'half_map_job_options',
				'default'     => esc_html__( 'Your career starts now!', 'cariera' ),
				'priority'    => 10,
			],

			'cariera_job_half_map_layout'                  => [
				'type'        => 'select',
				'label'       => esc_html__( 'Half Map Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the side where the jobs will be displayed.', 'cariera' ),
				'section'     => 'half_map_job_options',
				'default'     => 'left-side',
				'priority'    => 10,
				'choices'     => [
					'left-side'  => esc_attr__( 'Left Side', 'cariera' ),
					'right-side' => esc_attr__( 'Right Side', 'cariera' ),
				],
			],

			'cariera_half_map_single_job_layout'           => [
				'type'        => 'select',
				'label'       => esc_html__( 'Job Listings Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the layout for the jobs that will be displayed in the half map page.', 'cariera' ),
				'section'     => 'half_map_job_options',
				'default'     => '4',
				'priority'    => 10,
				'choices'     => [
					'1' => esc_attr__( 'List Layout 1', 'cariera' ),
					'2' => esc_attr__( 'List Layout 2', 'cariera' ),
					'3' => esc_attr__( 'List Layout 3', 'cariera' ),
					'4' => esc_attr__( 'List Layout 4', 'cariera' ),
					'5' => esc_attr__( 'List Layout 5', 'cariera' ),
				],
			],

			// SINGLE JOB OPTIONS.
			'cariera_job_share'                            => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Sharing Options', 'cariera' ),
				'description' => esc_html__( 'Display social sharing on single job page.', 'cariera' ),
				'section'     => 'single_job_option',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_job_map'                              => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Job Map', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the map.', 'cariera' ),
				'section'     => 'single_job_option',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_related_jobs'                         => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Related Jobs', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable related jobs.', 'cariera' ),
				'section'     => 'single_job_option',
				'default'     => true,
				'priority'    => 10,
			],

			// GENERAL COMPANY OPTIONS.
			'cariera_company_search_map'                   => [
				'type'        => 'switch',
				'label'       => esc_html__( 'General Company Search Map', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" if you want to disable the company search map.', 'cariera' ),
				'section'     => 'company_options',
				'default'     => true,
				'priority'    => 10,
			],

			// HALF MAP COMPANY OPTIONS.
			'cariera_company_half_map_text'                => [
				'type'        => 'text',
				'label'       => esc_html__( 'Half Map Text', 'cariera' ),
				'description' => esc_html__( 'Add the text that will show up on top of the search.', 'cariera' ),
				'section'     => 'half_map_company_options',
				'default'     => esc_html__( 'Find the perfect Company for you!', 'cariera' ),
				'priority'    => 10,
			],

			'cariera_company_half_map_layout'              => [
				'type'        => 'select',
				'label'       => esc_html__( 'Half Map Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the side where the companies will be displayed.', 'cariera' ),
				'section'     => 'half_map_company_options',
				'default'     => 'left-side',
				'priority'    => 10,
				'choices'     => [
					'left-side'  => esc_attr__( 'Left Side', 'cariera' ),
					'right-side' => esc_attr__( 'Right Side', 'cariera' ),
				],
			],

			// SINGLE COMPANY OPTIONS.
			'cariera_company_share'                        => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Sharing Options', 'cariera' ),
				'description' => esc_html__( 'Display social sharing on single company page.', 'cariera' ),
				'section'     => 'single_company_options',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_company_map'                          => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Company Map', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the map.', 'cariera' ),
				'section'     => 'single_company_options',
				'default'     => true,
				'priority'    => 10,
			],

			// RESUME OPTIONS.
			'cariera_resume_search_map'                    => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Resume Map', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the map.', 'cariera' ),
				'section'     => 'resume_options',
				'default'     => true,
				'priority'    => 10,
			],

			// RESUME TAXONOMY OPTIONS.
			'cariera_resume_taxonomy_layout'               => [
				'type'        => 'select',
				'label'       => esc_html__( 'Resume Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the layout for the resumes that will be displayed in the taxonomy pages.', 'cariera' ),
				'section'     => 'resume_taxonomy_options',
				'default'     => 'list',
				'priority'    => 10,
				'choices'     => [
					'list' => esc_attr__( 'List Layout', 'cariera' ),
					'grid' => esc_attr__( 'Grid Layout', 'cariera' ),
				],
			],

			'cariera_resume_taxonomy_list_version'         => [
				'type'            => 'select',
				'label'           => esc_html__( 'Resume List Layout', 'cariera' ),
				'description'     => esc_html__( 'Choose the list layout for the resumes.', 'cariera' ),
				'section'         => 'resume_taxonomy_options',
				'default'         => '1',
				'priority'        => 10,
				'choices'         => [
					'1' => esc_attr__( 'List Layout 1', 'cariera' ),
					'2' => esc_attr__( 'List Layout 2', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_resume_taxonomy_layout',
						'value'    => 'list',
						'operator' => '==',
					],
				],
			],

			'cariera_resume_taxonomy_grid_version'         => [
				'type'            => 'select',
				'label'           => esc_html__( 'Resume Grid Layout', 'cariera' ),
				'description'     => esc_html__( 'Choose the grid layout for the resumes.', 'cariera' ),
				'section'         => 'resume_taxonomy_options',
				'default'         => '1',
				'priority'        => 10,
				'choices'         => [
					'1' => esc_attr__( 'Grid Layout 1', 'cariera' ),
					'2' => esc_attr__( 'Grid Layout 2', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_resume_taxonomy_layout',
						'value'    => 'grid',
						'operator' => '==',
					],
				],
			],

			// HALF MAP RESUME OPTIONS.
			'cariera_resume_half_map_text'                 => [
				'type'        => 'text',
				'label'       => esc_html__( 'Half Map Text', 'cariera' ),
				'description' => esc_html__( 'Add the text that will show up on top of the search.', 'cariera' ),
				'section'     => 'half_map_resume_options',
				'default'     => esc_html__( 'Find the right Candidate for your business!', 'cariera' ),
				'priority'    => 10,
			],

			'cariera_resume_half_map_layout'               => [
				'type'        => 'select',
				'label'       => esc_html__( 'Half Map Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the side where the resumes will be displayed.', 'cariera' ),
				'section'     => 'half_map_resume_options',
				'default'     => 'left-side',
				'priority'    => 10,
				'choices'     => [
					'left-side'  => esc_attr__( 'Left Side', 'cariera' ),
					'right-side' => esc_attr__( 'Right Side', 'cariera' ),
				],
			],

			'cariera_half_map_single_resume_layout'        => [
				'type'        => 'select',
				'label'       => esc_html__( 'Resumes Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the layout for the resumes that will be displayed in the half map page.', 'cariera' ),
				'section'     => 'half_map_resume_options',
				'default'     => 'list1',
				'priority'    => 10,
				'choices'     => [
					'list1' => esc_attr__( 'List Layout 1', 'cariera' ),
					'list2' => esc_attr__( 'List Layout 2', 'cariera' ),
					'grid1' => esc_attr__( 'Grid Layout 1', 'cariera' ),
					'grid2' => esc_attr__( 'Grid Layout 2', 'cariera' ),
				],
			],

			// SINGLE RESUME OPTIONS.
			'cariera_resume_share'                         => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Sharing Options', 'cariera' ),
				'description' => esc_html__( 'Display social sharing on single resume page.', 'cariera' ),
				'section'     => 'single_resume_options',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_resume_map'                           => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Resume Map', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the map.', 'cariera' ),
				'section'     => 'single_resume_options',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_related_resumes'                      => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Related Resumes', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable related resumes.', 'cariera' ),
				'section'     => 'single_resume_options',
				'default'     => true,
				'priority'    => 10,
			],

			// PAGE HEADER BLOG OPTIONS.
			'cariera_blog_page_header'                     => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Page Header', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the page header section on the blog pages.', 'cariera' ),
				'section'     => 'page_header_blog',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_blog_title'                           => [
				'type'            => 'text',
				'label'           => esc_html__( 'Blog Title', 'cariera' ),
				'default'         => esc_html__( 'Our Blog', 'cariera' ),
				'section'         => 'page_header_blog',
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_blog_page_header',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

			// GENERAL BLOG OPTIONS.
			'cariera_blog_layout'                          => [
				'type'        => 'select',
				'label'       => esc_html__( 'Blog Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the sidebar side for your blog.', 'cariera' ),
				'section'     => 'blog',
				'default'     => 'right-sidebar',
				'priority'    => 10,
				'choices'     => [
					'left-sidebar'  => esc_attr__( 'Left Sidebar', 'cariera' ),
					'right-sidebar' => esc_attr__( 'Right Sidebar', 'cariera' ),
					'fullwidth'     => esc_attr__( 'No Sidebar', 'cariera' ),
				],
			],

			'cariera_blog_meta'                            => [
				'type'        => 'multicheck',
				'label'       => esc_html__( 'Meta Informations on Blog Posts', 'cariera' ),
				'description' => esc_html__( 'Set which elements of posts meta data you want to display on blog and archive pages.', 'cariera' ),
				'section'     => 'blog',
				'default'     => [ 'author', 'date', 'cat' ],
				'priority'    => 10,
				'choices'     => [
					'author' => esc_html__( 'Author', 'cariera' ),
					'date'   => esc_html__( 'Date', 'cariera' ),
					'cat'    => esc_html__( 'Categories', 'cariera' ),
					'com'    => esc_html__( 'Comments', 'cariera' ),
				],
			],

			'cariera_blog_pagination'                      => [
				'type'        => 'select',
				'label'       => esc_html__( 'Blog Pagination', 'cariera' ),
				'description' => esc_html__( 'Choose pagination for your blog.', 'cariera' ),
				'section'     => 'blog',
				'default'     => 'numeric',
				'priority'    => 10,
				'choices'     => [
					'numeric' => esc_attr__( 'Numeric', 'cariera' ),
					'plain'   => esc_attr__( 'Newer/Older', 'cariera' ),
				],
			],

			// SINGLE BLOG POST OPTIONS.
			'cariera_meta_single'                          => [
				'type'        => 'multicheck',
				'label'       => esc_html__( 'Meta Informations', 'cariera' ),
				'description' => esc_html__( 'Set which elements of posts meta data you want to display on a single post.', 'cariera' ),
				'section'     => 'single_blog_post',
				'default'     => [ 'author', 'date', 'cat' ],
				'priority'    => 10,
				'choices'     => [
					'author' => esc_html__( 'Author', 'cariera' ),
					'date'   => esc_html__( 'Date', 'cariera' ),
					'cat'    => esc_html__( 'Categories', 'cariera' ),
					'com'    => esc_html__( 'Comments', 'cariera' ),
				],
			],

			'cariera_blog_post_nav'                        => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Blog Post Navigation', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the blog post navigation from any blog post.', 'cariera' ),
				'section'     => 'single_blog_post',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_post_share'                           => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Show Sharing Icons', 'cariera' ),
				'description' => esc_html__( 'Display social sharing icons on single post', 'cariera' ),
				'section'     => 'single_blog_post',
				'default'     => true,
				'priority'    => 10,
			],

			// WOOCOMMERCE OPTIONS.
			'cariera_shop_layout'                          => [
				'type'        => 'select',
				'label'       => esc_html__( 'Shop Layout', 'cariera' ),
				'description' => esc_html__( 'Choose the sidebar side for your shop.', 'cariera' ),
				'section'     => 'woocommerce_options',
				'default'     => 'right-sidebar',
				'priority'    => 10,
				'choices'     => [
					'left-sidebar'  => esc_attr__( 'Left Sidebar', 'cariera' ),
					'right-sidebar' => esc_attr__( 'Right Sidebar', 'cariera' ),
					'fullwidth'     => esc_attr__( 'No Sidebar', 'cariera' ),
				],
			],

			'cariera_product_share'                        => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Show Sharing Icons', 'cariera' ),
				'description' => esc_html__( 'Display social sharing icons on single product', 'cariera' ),
				'section'     => 'woocommerce_options',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_related_products'                     => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Related Products', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the related products on single product page.', 'cariera' ),
				'section'     => 'woocommerce_options',
				'default'     => true,
				'priority'    => 10,
			],

			// FOOTER OPTIONS.
			'cariera_footer_style'                         => [
				'type'        => 'select',
				'settings'    => 'cariera_footer_style',
				'label'       => esc_html__( 'Layout Style', 'cariera' ),
				'description' => esc_html__( 'Choose your footer layout.', 'cariera' ),
				'section'     => 'footer',
				'default'     => 'footer-default',
				'priority'    => 10,
				'choices'     => [
					'footer-default' => esc_html__( 'Default Layout', 'cariera' ),
					'footer-fixed'   => esc_html__( 'Fixed Footer', 'cariera' ),
				],
			],

			'cariera_footer_custom'                        => [
				'type'     => 'custom',
				'section'  => 'footer',
				'default'  => '<hr>',
				'priority' => 10,
			],

			'cariera_footer_bg'                            => [
				'type'        => 'color',
				'label'       => esc_html__( 'Footer Background Color', 'cariera' ),
				'description' => esc_html__( 'Select the background color for your footer.', 'cariera' ),
				'section'     => 'footer',
				'default'     => '#1e1f21',
				'priority'    => 10,
			],

			'cariera_footer_title_color'                   => [
				'type'        => 'color',
				'label'       => esc_html__( 'Footer Widget Title Color', 'cariera' ),
				'description' => esc_html__( 'Select the color for the widget titles in the footer.', 'cariera' ),
				'section'     => 'footer',
				'default'     => '#fff',
				'priority'    => 10,
			],

			'cariera_footer_text_color'                    => [
				'type'        => 'color',
				'label'       => esc_html__( 'Footer Text Color', 'cariera' ),
				'description' => esc_html__( 'Select the color for the text in the footer.', 'cariera' ),
				'section'     => 'footer',
				'default'     => '#948a99',
				'priority'    => 10,
			],

			'cariera_footer_custom2'                       => [
				'type'     => 'custom',
				'section'  => 'footer',
				'default'  => '<hr>',
				'priority' => 10,
			],

			'cariera_footer_info'                          => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Footer Info Section', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the footer info section. ', 'cariera' ),
				'section'     => 'footer',
				'default'     => true,
				'priority'    => 10,
			],

			'cariera_footer_sidebar_1'                     => [
				'type'            => 'select',
				'label'           => esc_html__( 'Footer Sidebar Column 1', 'cariera' ),
				'description'     => '',
				'section'         => 'footer',
				'default'         => 'col-md-2 col-sm-6 col-xs-6',
				'priority'        => 10,
				'choices'         => [
					'col-sm-12 col-xs-12'        => esc_html__( 'Full Width', 'cariera' ),
					'col-sm-9 col-xs-9'          => esc_html__( '3/4', 'cariera' ),
					'col-sm-8 col-xs-8'          => esc_html__( '2/3', 'cariera' ),
					'col-sm-6 col-xs-6'          => esc_html__( '1/2', 'cariera' ),
					'col-sm-4 col-xs-4'          => esc_html__( '1/3', 'cariera' ),
					'col-md-3 col-sm-6 col-xs-6' => esc_html__( '1/4', 'cariera' ),
					'col-md-2 col-sm-6 col-xs-6' => esc_html__( '1/6', 'cariera' ),
					'disabled'                   => esc_html__( 'disabled', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_footer_info',
						'value'    => true,
						'operator' => '==',
					],
				],
			],

			'cariera_footer_sidebar_2'                     => [
				'type'            => 'select',
				'label'           => esc_html__( 'Footer Sidebar Column 2', 'cariera' ),
				'description'     => '',
				'section'         => 'footer',
				'default'         => 'col-md-2 col-sm-6 col-xs-6',
				'priority'        => 10,
				'choices'         => [
					'col-sm-12 col-xs-12'        => esc_html__( 'Full Width', 'cariera' ),
					'col-sm-9 col-xs-9'          => esc_html__( '3/4', 'cariera' ),
					'col-sm-8 col-xs-8'          => esc_html__( '2/3', 'cariera' ),
					'col-sm-6 col-xs-6'          => esc_html__( '1/2', 'cariera' ),
					'col-sm-4 col-xs-4'          => esc_html__( '1/3', 'cariera' ),
					'col-md-3 col-sm-6 col-xs-6' => esc_html__( '1/4', 'cariera' ),
					'col-md-2 col-sm-6 col-xs-6' => esc_html__( '1/6', 'cariera' ),
					'disabled'                   => esc_html__( 'disabled', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_footer_info',
						'value'    => true,
						'operator' => '==',
					],
				],
			],

			'cariera_footer_sidebar_3'                     => [
				'type'            => 'select',
				'label'           => esc_html__( 'Footer Sidebar Column 3', 'cariera' ),
				'description'     => '',
				'section'         => 'footer',
				'default'         => 'col-md-2 col-sm-6 col-xs-6',
				'priority'        => 10,
				'choices'         => [
					'col-sm-12 col-xs-12'        => esc_html__( 'Full Width', 'cariera' ),
					'col-sm-9 col-xs-9'          => esc_html__( '3/4', 'cariera' ),
					'col-sm-8 col-xs-8'          => esc_html__( '2/3', 'cariera' ),
					'col-sm-6 col-xs-6'          => esc_html__( '1/2', 'cariera' ),
					'col-sm-4 col-xs-4'          => esc_html__( '1/3', 'cariera' ),
					'col-md-3 col-sm-6 col-xs-6' => esc_html__( '1/4', 'cariera' ),
					'col-md-2 col-sm-6 col-xs-6' => esc_html__( '1/6', 'cariera' ),
					'disabled'                   => esc_html__( 'disabled', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_footer_info',
						'value'    => true,
						'operator' => '==',
					],
				],
			],

			'cariera_footer_sidebar_4'                     => [
				'type'            => 'select',
				'label'           => esc_html__( 'Footer Sidebar Column 4', 'cariera' ),
				'description'     => '',
				'section'         => 'footer',
				'default'         => 'col-sm-6 col-xs-6',
				'priority'        => 10,
				'choices'         => [
					'col-sm-12 col-xs-12'        => esc_html__( 'Full Width', 'cariera' ),
					'col-sm-9 col-xs-9'          => esc_html__( '3/4', 'cariera' ),
					'col-sm-8 col-xs-8'          => esc_html__( '2/3', 'cariera' ),
					'col-sm-6 col-xs-6'          => esc_html__( '1/2', 'cariera' ),
					'col-sm-4 col-xs-4'          => esc_html__( '1/3', 'cariera' ),
					'col-md-3 col-sm-6 col-xs-6' => esc_html__( '1/4', 'cariera' ),
					'col-md-2 col-sm-6 col-xs-6' => esc_html__( '1/6', 'cariera' ),
					'disabled'                   => esc_html__( 'disabled', 'cariera' ),
				],
				'active_callback' => [
					[
						'setting'  => 'cariera_footer_info',
						'value'    => true,
						'operator' => '==',
					],
				],
			],

			'cariera_footer_custom3'                       => [
				'type'     => 'custom',
				'section'  => 'footer',
				'default'  => '<hr>',
				'priority' => 10,
			],

			'cariera_copyrights'                           => [
				'type'        => 'textarea',
				'label'       => esc_html__( 'Copyrights text', 'cariera' ),
				'description' => esc_html__( 'Enter your Copyright Text (HTML allowed).', 'cariera' ),
				'default'     => 'Copyright &copy; Cariera. Developed by <a href="https://1.envato.market/MOKEn" target="_blank">Gnodesign</a>',
				'section'     => 'footer',
				'priority'    => 10,
			],

			'cariera_footer_socials'                       => [
				'type'        => 'repeater',
				'label'       => esc_html__( 'Social Media', 'cariera' ),
				'description' => esc_html__( 'Choose the social media that you want to be displayed in the footer.', 'cariera' ),
				'section'     => 'footer',
				'priority'    => 10,
				'default'     => '',
				'fields'      => [
					'social_type' => [
						'type'        => 'select',
						'label'       => esc_html__( 'Social Media Type', 'cariera' ),
						'description' => esc_html__( 'Choose your social media type.', 'cariera' ),
						'default'     => '',
						'priority'    => 10,
						'choices'     => [
							''            => '-',
							'facebook'    => 'Facebook',
							'twitter'     => 'Twitter',
							'twitter-x'   => 'X (Twitter)',
							'google-plus' => 'Google Plus',
							'instagram'   => 'Instagram',
							'linkedin'    => 'LinkedIN',
							'pinterest'   => 'Pinterest',
							'tumblr'      => 'Tumblr',
							'github'      => 'GitHub',
							'dribbble'    => 'Dribbble',
							'wordpress'   => 'WordPress',
							'amazon'      => 'Amazon',
							'dropbox'     => 'Dropbox',
							'paypal'      => 'PayPal',
							'yahoo'       => 'Yahoo',
							'flickr'      => 'Flickr',
							'reddit'      => 'Reddit',
							'vimeo'       => 'Vimeo',
							'spotify'     => 'Spotify',
							'youtube'     => 'YouTube',
							'telegram'    => 'Telegram',
							'vk'          => 'Vkontakte',
							'tiktok'      => 'TikTok',
						],
					],
					'link_url'    => [
						'type'        => 'text',
						'label'       => esc_html__( 'Social URL', 'cariera' ),
						'description' => esc_html__( 'Enter the URL for this social', 'cariera' ),
						'default'     => '',
					],
				],
			],

			// DASHBOARD OPTIONS.
			'cariera_dashboard_page_enable'                => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Dashboard Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Dashboard" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_job_alerts_page_enable'     => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Job Alerts Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Job Alerts" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_bookmark_page_enable'       => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Bookmark Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Bookmark" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_applied_jobs_page_enable'   => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Applied Jobs Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Applied Jobs" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_listing_reports_page_enable' => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Listing Reports Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Listing Reports" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_user_packages_page_enable'  => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable User Packages Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Packages" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_orders_page_enable'         => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Orders Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Orders" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_job_submission_page_enable' => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Post Job Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Post Job" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_company_submission_page_enable' => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Submit Company Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Submit Company" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_resume_submission_page_enable' => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Submit Resume Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Submit Resume" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_profile_page_enable'        => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Enable Profile Page', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "off" to hide the "Profile" page.', 'cariera' ),
				'section'     => 'dashboard',
				'default'     => 1,
				'priority'    => 10,
			],

			'cariera_dashboard_custom6'                    => [
				'type'     => 'custom',
				'section'  => 'dashboard',
				'default'  => '<hr>',
				'priority' => 10,
			],

			'cariera_dashboard_views_statistics'           => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Monthly Views Statistics', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to disable the Graph stats.', 'cariera' ),
				'section'     => 'dashboard',
				'priority'    => 10,
				'default'     => true,
			],

			'cariera_dashboard_statistics_border'          => [
				'type'            => 'color',
				'label'           => esc_html__( 'Statistics Border Color', 'cariera' ),
				'description'     => esc_html__( 'Change the border color of the statistics chart.', 'cariera' ),
				'section'         => 'dashboard',
				'priority'        => 10,
				'default'         => '#2346f7',
				'active_callback' => [
					[
						'setting'  => 'cariera_dashboard_views_statistics',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

			'cariera_dashboard_statistics_background'      => [
				'type'            => 'color',
				'label'           => esc_html__( 'Statistics Background Color', 'cariera' ),
				'description'     => esc_html__( 'Change the background color of the statistics chart.', 'cariera' ),
				'section'         => 'dashboard',
				'priority'        => 10,
				'default'         => 'rgba(35, 70, 247, .1)',
				'active_callback' => [
					[
						'setting'  => 'cariera_dashboard_views_statistics',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

			// HOME PAGE - SEARCH BAR VER 1 OPTIONS.
			'home_page_image'                              => [
				'type'        => 'image',
				'label'       => esc_html__( 'Home Page Background Image', 'cariera' ),
				'description' => esc_html__( 'Background image for the job search section', 'cariera' ),
				'section'     => 'home_page',
				'priority'    => 60,
				'default'     => '',
			],

			'home_page_text'                               => [
				'type'        => 'textarea',
				'label'       => esc_html__( 'Home Page Intro Text', 'cariera' ),
				'description' => esc_html__( 'Edit the text that will be shown on top of the search.', 'cariera' ),
				'section'     => 'home_page',
				'priority'    => 60,
				'default'     => 'Your career starts now',
			],

			'home_job_counter'                             => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Job Counter', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" to remove the job counter. ', 'cariera' ),
				'section'     => 'home_page',
				'priority'    => 60,
				'default'     => true,
			],

			// LOGIN & REGISTER PAGE OPTIONS.
			'login_page_image'                             => [
				'type'        => 'image',
				'label'       => esc_html__( 'Login Page Background Image', 'cariera' ),
				'description' => esc_html__( 'Background image for the Login page', 'cariera' ),
				'section'     => 'login_page',
				'priority'    => 60,
				'default'     => '',
			],

			'login_page_text'                              => [
				'type'        => 'textarea',
				'label'       => esc_html__( 'Login Page Intro Text', 'cariera' ),
				'description' => esc_html__( 'Edit the text that will be shown on the login page at the left section.', 'cariera' ),
				'section'     => 'login_page',
				'priority'    => 60,
				'default'     => 'Welcome to Cariera',
			],

			// MAP OPTIONS.
			'cariera_map_provider'                         => [
				'type'        => 'select',
				'label'       => esc_html__( 'Maps Provider', 'cariera' ),
				'description' => esc_html__( 'Choose the map provider that you want to use for your maps.', 'cariera' ),
				'section'     => 'map_options',
				'default'     => 'osm',
				'choices'     => [
					'none'   => 'None',
					'osm'    => 'Open Street Maps',
					'mapbox' => 'MapBox',
					'google' => 'Google Maps',
				],
				'priority'    => 10,
			],

			'cariera_mapbox_access_token'                  => [
				'type'            => 'text',
				'label'           => esc_html__( 'MapBox Access Token', 'cariera' ),
				'description'     => esc_html__( 'Add your Mapbox access token here', 'cariera' ),
				'section'         => 'map_options',
				'priority'        => 10,
				'default'         => '',
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '==',
						'value'    => 'mapbox',
					],
				],
			],

			'cariera_gmap_api_key'                         => [
				'type'            => 'text',
				'label'           => esc_html__( 'API Key', 'cariera' ),
				'description'     => esc_html__( 'Add your Google Map API Key here', 'cariera' ),
				'section'         => 'map_options',
				'priority'        => 10,
				'default'         => '',
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '==',
						'value'    => 'google',
					],
				],
			],

			'cariera_gmap_language'                        => [
				'type'            => 'text',
				'label'           => esc_html__( 'Language', 'cariera' ),
				'description'     => esc_html__( 'The language to use for Google Maps services.', 'cariera' ),
				'section'         => 'map_options',
				'priority'        => 10,
				'default'         => 'en',
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '==',
						'value'    => 'google',
					],
				],
			],

			'cariera_gmap_custom1'                         => [
				'type'     => 'custom',
				'section'  => 'map_options',
				'default'  => '<hr>',
				'priority' => 10,
			],

			'cariera_job_location_autocomplete'            => [
				'type'            => 'switch',
				'label'           => esc_html__( 'Location Autocomplete', 'cariera' ),
				'description'     => esc_html__( 'Google will show locations to autocomplete your search as you write. Turn the switch "off" if you want to disable location autocomplete.', 'cariera' ),
				'section'         => 'map_options',
				'default'         => true,
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
				],
			],

			'cariera_map_restriction'                      => [
				'type'            => 'text',
				'label'           => esc_html__( 'Restrict Search Result', 'cariera' ),
				'description'     => esc_html__( 'If you want to restrict results to only specific countries, enter the two-character ISO 3166-1 Alpha-2 compatible country code.  Max is 5 countries, separated with a comma for Google Maps. For example: de,uk.', 'cariera' ),
				'section'         => 'map_options',
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
					[
						'setting'  => 'cariera_job_location_autocomplete',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

			'cariera_job_auto_location'                    => [
				'type'            => 'switch',
				'label'           => esc_html__( 'GeoLocation', 'cariera' ),
				'description'     => esc_html__( 'Use your location with just one click. Turn the switch "off" if you want to disable geolocation.', 'cariera' ),
				'section'         => 'map_options',
				'default'         => false,
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
				],
			],

			'cariera_search_radius'                        => [
				'type'            => 'switch',
				'label'           => esc_html__( 'Search Radius', 'cariera' ),
				'description'     => esc_html__( 'Enable the switch if you want to enable search radius in your searches. (works only if google api key has been added)', 'cariera' ),
				'section'         => 'map_options',
				'default'         => false,
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
				],
			],

			'cariera_radius_unit'                          => [
				'type'            => 'select',
				'label'           => esc_html__( 'Radius Search Unit', 'cariera' ),
				'section'         => 'map_options',
				'default'         => 'km',
				'choices'         => [
					'km'    => 'KM',
					'miles' => 'MILES',
				],
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
					[
						'setting'  => 'cariera_search_radius',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

			'cariera_max_radius_search_value'              => [
				'type'            => 'text',
				'label'           => esc_html__( 'Max Radius Search Value', 'cariera' ),
				'section'         => 'map_options',
				'default'         => '100',
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
					[
						'setting'  => 'cariera_search_radius',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

			'cariera_map_autofit'                          => [
				'type'            => 'switch',
				'label'           => esc_html__( 'Autofit Markers in the Map', 'cariera' ),
				'description'     => esc_html__( 'If enabled all markers will autofit in the map when search results are loaded.', 'cariera' ),
				'section'         => 'map_options',
				'default'         => true,
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
				],
			],

			'cariera_map_center'                           => [
				'type'            => 'text',
				'label'           => esc_html__( 'Custom Center point', 'cariera' ),
				'description'     => esc_html__( 'Write latitude and longitude separated, for example: 37.9838, 23.7275', 'cariera' ),
				'section'         => 'map_options',
				'default'         => '37.9838, 23.7275',
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
				],
			],

			'cariera_maps_type'                            => [
				'type'            => 'select',
				'label'           => esc_html__( 'Map type', 'cariera' ),
				'section'         => 'map_options',
				'default'         => 'roadmap',
				'choices'         => [
					'roadmap'   => 'ROADMAP',
					'hybrid'    => 'HYBRID',
					'satellite' => 'SATELLITE',
					'terrain'   => 'TERRAIN',
				],
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '==',
						'value'    => 'google',
					],
				],
			],

			'cariera_map_height'                           => [
				'type'            => 'text',
				'label'           => esc_html__( 'Map Height for Archive Pages', 'cariera' ),
				'description'     => esc_html__( 'Enter your map height in px. For example: "500px"', 'cariera' ),
				'section'         => 'map_options',
				'default'         => '450px',
				'priority'        => 10,
				'active_callback' => [
					[
						'setting'  => 'cariera_map_provider',
						'operator' => '!=',
						'value'    => 'none',
					],
				],
			],

			// COOKIE NOTICE OPTIONS.
			'cariera_cookie_notice'                        => [
				'type'        => 'switch',
				'label'       => esc_html__( 'Cookie Notice', 'cariera' ),
				'description' => esc_html__( 'Turn the switch "OFF" if you want to disable the cookie notice.', 'cariera' ),
				'section'     => 'cookie_bar',
				'default'     => 0,
				'priority'    => 20,
			],

			'cariera_notice_message'                       => [
				'type'            => 'textarea',
				'label'           => esc_html__( 'Cookie Text Message', 'cariera' ),
				'description'     => esc_html__( 'Write the message that you want to show up in the Cookie Notice.', 'cariera' ),
				'section'         => 'cookie_bar',
				'default'         => esc_html__( 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies.', 'cariera' ),
				'priority'        => 20,
				'active_callback' => [
					[
						'setting'  => 'cariera_cookie_notice',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

			'cariera_policy_page'                          => [
				'type'            => 'dropdown-pages',
				'label'           => esc_html__( 'Cookie Details Page', 'cariera' ),
				'description'     => esc_html__( 'Choose page that will contain detailed information about your Privacy Policy.', 'cariera' ),
				'section'         => 'cookie_bar',
				'default'         => '',
				'priority'        => 20,
				'active_callback' => [
					[
						'setting'  => 'cariera_cookie_notice',
						'operator' => '==',
						'value'    => 1,
					],
				],
			],

		], // End of fields array.
	]; // End of return array.
} // End of function cariera_customize_settings.


function cariera_customize_init() {
	$cariera_customize = new Cariera_Customize( cariera_customize_settings() );
}


if ( class_exists( 'Kirki' ) ) {
	add_action( 'init', 'cariera_customize_init', 5 );
} else {
	$cariera_customize = new Cariera_Customize( cariera_customize_settings() );
}

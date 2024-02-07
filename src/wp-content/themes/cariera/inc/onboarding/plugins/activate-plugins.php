<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once get_template_directory() . '/inc/onboarding/plugins/class-tgm-plugin-activation.php';

/**
 * Register the required plugins for this theme.
 */
function cariera_register_required_plugins() {

	$plugins = [
		[
			'name'             => 'Cariera Core',
			'slug'             => 'cariera-plugin',
			'source'           => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/cariera-plugin.zip',
			'required'         => true,
			'version'          => '1.7.2',
			'force_activation' => false,
		],
		[
			'name'     => 'Elementor',
			'slug'     => 'elementor',
			'required' => true,
		],
		[
			'name'     => 'Kirki Framework',
			'slug'     => 'kirki',
			'required' => true,
		],
		[
			'name'     => 'WP Job Manager',
			'slug'     => 'wp-job-manager',
			'required' => true,
		],
		[
			'name'     => 'WP Job Manager - Alerts',
			'slug'     => 'wp-job-manager-alerts',
			'source'   => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/wp-job-manager-alerts.zip',
			'required' => false,
			'version'  => '2.1.0',
		],
		[
			'name'     => 'WP Job Manager - Applications',
			'slug'     => 'wp-job-manager-applications',
			'source'   => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/wp-job-manager-applications.zip',
			'required' => false,
			'version'  => '3.0.1',
		],
		[
			'name'     => 'WP Job Manager - Application Deadline',
			'slug'     => 'wp-job-manager-application-deadline',
			'source'   => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/wp-job-manager-application-deadline.zip',
			'required' => false,
			'version'  => '1.2.7',
		],
		[
			'name'     => 'WP Job Manager - Bookmarks',
			'slug'     => 'wp-job-manager-bookmarks',
			'source'   => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/wp-job-manager-bookmarks.zip',
			'required' => false,
			'version'  => '1.4.3',
		],
		[
			'name'     => 'WP Job Manager - Resumes',
			'slug'     => 'wp-job-manager-resumes',
			'source'   => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/wp-job-manager-resumes.zip',
			'required' => false,
			'version'  => '2.0.0',
		],
		[
			'name'     => 'WP Job Manager - Tags',
			'slug'     => 'wp-job-manager-tags',
			'source'   => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/wp-job-manager-tags.zip',
			'required' => false,
			'version'  => '1.4.4',
		],
		[
			'name'     => 'WP Job Manager - WC Paid Listings',
			'slug'     => 'wp-job-manager-wc-paid-listings',
			'source'   => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/wp-job-manager-wc-paid-listings.zip',
			'required' => false,
			'version'  => '3.0.1',
		],
		[
			'name'     => 'WooCommerce',
			'slug'     => 'woocommerce',
			'required' => true,
		],
		[
			'name'             => 'Revolution Slider',
			'slug'             => 'revslider',
			'source'           => 'https://cariera.co/plugins/' . CARIERA_PLUGINS_URL . '/revslider.zip',
			'required'         => false,
			'version'          => '6.6.18',
			'force_activation' => false,
		],
		[
			'name'             => 'Envato Market',
			'slug'             => 'envato-market',
			'source'           => 'https://cariera.co/plugins/envato-market.zip',
			'required'         => true,
			'version'          => '2.0.10',
			'force_activation' => false,
		],
		[
			'name'     => 'Classic Editor',
			'slug'     => 'classic-editor',
			'required' => false,
		],
		[
			'name'     => 'Contact Form 7',
			'slug'     => 'contact-form-7',
			'required' => false,
		],
	];

	/*
	 * Array of configuration settings. Amend each line as needed.
	 *
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 * Only uncomment the strings in the config array if you want to customize the strings.
	 */
	$config = [
		'id'           => 'cariera',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'install-required-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
	];

	tgmpa( $plugins, $config );
}

add_action( 'tgmpa_register', 'cariera_register_required_plugins' );

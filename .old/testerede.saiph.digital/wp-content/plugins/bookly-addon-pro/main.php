<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
/*
Plugin Name: Bookly Pro (Add-on)
Plugin URI: https://www.booking-wp-plugin.com/?utm_source=bookly_admin&utm_medium=plugins_page&utm_campaign=plugins_page
Description: Bookly Pro add-on allows you to use additional features and settings, and install other add-ons for Bookly plugin.
Version: 7.2
Author: Bookly
Author URI: https://www.booking-wp-plugin.com/?utm_source=bookly_admin&utm_medium=plugins_page&utm_campaign=plugins_page
Text Domain: bookly
Domain Path: /languages
License: Commercial
*/

if ( ! function_exists( 'bookly_pro_loader' ) ) {
    include_once __DIR__ . '/autoload.php';

    BooklyPro\Lib\Boot::up();
}
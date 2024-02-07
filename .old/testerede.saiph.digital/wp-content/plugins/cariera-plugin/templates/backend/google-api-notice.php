<?php
/**
 * Google API Notice
 * This will be shown if no google api key has been added in the wpjm settings.
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/google-api-notice.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="error notice">
	<p><?php esc_html_e( 'Please add an unrestricted Google Maps API key in the "Job Listings->Settings" in order to be able to geocode your listings and show them in the maps.', 'cariera' ); ?> <a href="https://wpjobmanager.com/document/geolocation-with-googles-maps-api/" target="_blank"><?php esc_html_e( 'Learn More', 'cariera' ); ?></a></p>
</div>

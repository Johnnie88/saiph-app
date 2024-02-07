<?php
/**
 * User signin template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/account/account-signin.php.
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

<p class="job-manager-message generic">
	<?php esc_html_e( 'You need to be signed in to access this page.', 'cariera' ); ?>
</p>

<?php
/**
 * Dashboard copyright.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/copyright.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$copyright = cariera_get_option( 'cariera_copyrights' );
?>

<div class="row">
	<div class="col-md-12">
		<div class="copyrights">
			<?php echo wp_kses_post( $copyright ); ?>
		</div>
	</div>
</div>

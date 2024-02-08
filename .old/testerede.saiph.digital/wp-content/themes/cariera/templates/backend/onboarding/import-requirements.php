<?php
/**
 * Onboarding: Import Requirements
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/import-requirements.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$php_min_ver            = 7.4;
$php_cur_ver            = PHP_VERSION;
$max_execution_time_cur = ini_get( 'max_execution_time' );
$max_execution_time_sug = 300;
$memory_limit_cur       = \WP_Site_Health::get_instance()->php_memory_limit;
$memory_limit_sug       = 256;
?>

<div class="cariera-requirements-container">
	<table class="requirements">
		<thead>
			<tr>
				<td colspan="4">
					<p><?php esc_html_e( 'In order to successfully import the demo, please ensure that your server meets the following requirements. If the requirements are not met please contact your hosting.', 'cariera' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Directive', 'cariera' ); ?></th>
				<th><?php esc_html_e( 'Priority', 'cariera' ); ?></th>
				<th><?php esc_html_e( 'Least Suggested Value', 'cariera' ); ?></th>
				<th><?php esc_html_e( 'Current Value', 'cariera' ); ?></th>
			</tr>
			<tr class="spacer"></tr>
		</thead>

		<tbody>
			<tr>
				<td><?php echo esc_html( 'PHP Version' ); ?></td>
				<td><?php echo esc_html( 'High' ); ?></td>
				<td class="bold"><?php echo esc_html( $php_min_ver ); ?></td>
				<td class="bold 
				<?php
				if ( $php_cur_ver >= $php_min_ver ) {
					echo esc_attr( 'ok' );
				} else {
					echo esc_attr( 'notok' ); }
				?>
				"><?php echo esc_html( $php_cur_ver ); ?></td>
			</tr>
			<tr>
				<td><?php echo esc_html( 'memory_limit' ); ?></td>
				<td><?php echo esc_html( 'High' ); ?></td>
				<td class="bold"><?php echo esc_html( $memory_limit_sug ); ?>M</td>
				<td class="bold <?php echo intval( $memory_limit_cur ) >= $memory_limit_sug ? esc_attr( 'ok' ) : esc_attr( 'notok' ); ?>"><?php echo esc_html( $memory_limit_cur ); ?></td>
			</tr>
			<tr>
				<td><?php echo esc_html( 'max_execution_time*' ); ?></td>
				<td><?php esc_html_e( 'Medium', 'cariera' ); ?></td>
				<td class="bold"><?php echo esc_html( $max_execution_time_sug ); ?></td>                    
				<td class="bold 
				<?php
				if ( $max_execution_time_cur >= $max_execution_time_sug ) {
					echo esc_attr( 'ok' );
				} else {
					echo esc_attr( 'notok' ); }
				?>
				"><?php echo esc_html( $max_execution_time_cur ); ?></td>
			</tr>
		</tbody>

		<?php if ( intval( $memory_limit_cur ) < $memory_limit_sug || $max_execution_time_cur < $max_execution_time_sug ) { ?>
			<tfoot>
				<tr class="spacer"></tr>
				<tr>
					<td colspan="4" class="small">
						<br>
						<small><?php esc_html_e( 'Your "max execution time" is lower than recommended. Please contact your hosting provider to increase it to the recommended value in order to fully import the demo.', 'cariera' ); ?></small>
					</td>
				</tr>
			</tfoot>
		<?php } ?>
	</table>
</div>

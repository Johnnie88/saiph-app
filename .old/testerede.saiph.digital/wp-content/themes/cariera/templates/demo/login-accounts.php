<?php
/**
 * Demo login credentials
 *
 * This template can be overridden by copying it to cariera-child/templates/demo/login-accounts.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.1
 * @version     1.7.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$employer_username  = 'employer';
$candidate_username = 'candidate';
$password           = 'demo';
?>

<div class="job-manager-message account-info">
	<p><?php echo wp_kses_post( sprintf( __( 'Username: <strong>%s</strong> or <strong>%s</strong>' ), $employer_username, $candidate_username ) ); ?></p>
	<p><?php echo wp_kses_post( sprintf( __( 'Password: <strong>%s</strong> ' ), $password ) ); ?></p>
</div>

<?php
/**
 * Title bar in the dashboard.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/titlebar.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$current_user   = wp_get_current_user();
$dashboard_page = get_option( 'cariera_dashboard_page' );

if ( ! empty( $current_user->user_firstname ) ) {
	$name = $current_user->user_firstname;
} else {
	$name = $current_user->display_name;
}
?>

<div class="title-bar">
	<div class="row">
		<div class="col-md-12">
			<?php if ( $dashboard_page == $post->ID ) { ?>
				<h2><?php printf( esc_html__( 'Welcome, %s!', 'cariera' ), esc_html( $name ) ); ?></h2>
			<?php } else { ?>
				<h1><?php the_title(); ?></h1>
			<?php } ?>
		</div>
	</div>
</div>

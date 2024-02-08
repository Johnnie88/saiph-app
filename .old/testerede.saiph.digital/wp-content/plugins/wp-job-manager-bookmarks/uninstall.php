<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( is_multisite() ) {

	global $wpdb;
	foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $blog_id ) {
		switch_to_blog( $blog_id );
		uninstall();
		restore_current_blog();
	}
} else {
	uninstall();
}

function uninstall() {
	global $wpdb;
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'job_manager_bookmarks' );
}

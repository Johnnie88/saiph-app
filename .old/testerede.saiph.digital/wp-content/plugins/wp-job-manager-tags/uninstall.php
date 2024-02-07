<?php
/**
 * Uninstall file for the plugin. Runs when plugin is deleted in WordPress Admin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Cleanup all data.
require 'includes/class-job-manager-job-tags-data-cleaner.php';

// Only do deletion if the setting is true.
$do_deletion = get_option( 'job_manager_delete_data_on_uninstall' );
if ( $do_deletion ) {
	WP_Job_Manager_Job_Tags_Data_Cleaner::cleanup_all();
}

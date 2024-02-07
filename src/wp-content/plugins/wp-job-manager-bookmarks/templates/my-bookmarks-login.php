<?php
/**
 * Lists a users bookmarks.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-bookmarks/my-bookmarks.php.
 *
 * @see      https://wpjobmanager.com/document/template-overrides/
 * @author   Automattic
 * @package  WP Job Manager - Bookmarks
 * @category Template
 * @version  1.4.3
 */

if (! defined('ABSPATH') ) {
    exit;
}
?>

<div id="job-manager-job-bookmarks">
    <p class="account-sign-in"><?php esc_html_e('You need to be signed in to manage your bookmarks.', 'wp-job-manager-bookmarks'); ?> <a class="button" href="<?php echo esc_url(apply_filters('job_manager_bookmarks_login_url', wp_login_url(get_permalink()))); ?>"><?php esc_html_e('Sign in', 'wp-job-manager-bookmarks'); ?></a></p>
</div>

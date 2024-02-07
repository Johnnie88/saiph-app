<?php
	global $pagenow;
	if( $pagenow !== 'index.php' ):
?>
<div class="updated">
	<p class="smylesv2-updater-dismiss" style="float:right;"><a href="<?php echo esc_url( add_query_arg( 'dismiss-' . sanitize_title( $this->plugin_slug ), '1' ) ); ?>"><?php _e( 'Hide notice', 'wp-job-manager-field-editor' ); ?></a></p>
	<p><?php printf( __( '<a href="%1$s">Please activate your license key</a> in order to receive updates and support for "%2$s".', 'wp-job-manager-field-editor' ), admin_url( 'index.php?page=smyles-licenses' ), esc_html( $this->plugin_data['Name'] ) ); ?></p>
</div>
<?php endif; ?>
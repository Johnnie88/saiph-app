<tr id="<?php echo esc_attr( sanitize_title( $this->plugin_slug . '_licence_key_row' ) ); ?>" class="active plugin-update-tr smylesv2-updater-licence-key-tr">
	<td class="plugin-update" colspan="3">
		<?php $this->error_notices(); ?>
		<div class="smylesv2-updater-licence-key">
			<span><?php printf( __( '<a href="%1$s">Click here to activate your license key</a> in order to receive updates and support for this plugin.', 'wp-job-manager-field-editor' ), admin_url( 'index.php?page=smyles-licenses' ) ); ?></span>
			<span class="description"><?php printf( 'Lost your key? <a href="%s">Retrieve it here</a>.', esc_url( 'https://plugins.smyl.es/lost-api-key/' ) ); ?></span>
		</div>
	</td>
	<script>
		jQuery(function(){
			jQuery('tr#<?php echo esc_attr( $this->plugin_slug ); ?>_licence_key_row').prev().addClass('smylesv2-updater-licenced');
		});
	</script>
</tr>

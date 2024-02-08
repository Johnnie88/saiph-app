<?php
$classes            = array( 'input-text' );
$allowed_mime_types = array_keys( ! empty( $field['allowed_mime_types'] ) ? $field['allowed_mime_types'] : get_allowed_mime_types() );
$field_name         = isset( $field['name'] ) ? $field['name'] : $key;
$field_name         .= ! empty( $field['multiple'] ) ? '[]' : '';
$classes[]          = 'file-' . esc_attr( $key );
$use_ajax           = ! empty( $field['ajax'] ) && field_editor_user_can_upload_file_via_ajax();

if ( $use_ajax ) {
	wp_enqueue_script( 'wp-job-manager-ajax-file-upload' );
	$classes[] = 'wp-job-manager-file-upload';
	$classes[] = 'ajax-file-' . esc_attr( $key );
} else {
	$classes[] = 'listify-file-upload';
}
?>

<?php if ( ! empty( $field[ 'multiple' ] ) && ! empty( $field[ 'max_uploads' ] ) ): wp_enqueue_script( 'jmfe-file-upload' ); ?>
	<script type="text/javascript">
		jQuery( function ( $ ) {
			var max_uploads = <?php echo $field[ 'max_uploads' ]; ?>;
			var max_alert = "<?php printf( __( 'The max allowed files is %s', 'wp-job-manager-field-editor' ), $field[ 'max_uploads' ] ); ?>";
			<?php if( $use_ajax ){ ?>
			$( '#<?php echo $key; ?>' )
				.bind( 'fileuploadchange', {alert: max_alert, max: max_uploads}, jmfe_upload.ajax.checkMax )
				.bind( 'fileuploaddrop', {alert: max_alert, max: max_uploads}, jmfe_upload.ajax.checkMax )
				.on( 'click', {alert: max_alert, max: max_uploads - 1}, jmfe_upload.ajax.checkMax );
			<?php } else { ?>
			$( '#<?php echo $key; ?>' ).on( 'change', {alert: max_alert, max: max_uploads}, jmfe_upload.checkMax );
			<?php } ?>
		} );
	</script>

<?php endif; ?>

<label for="<?php echo esc_attr( $key ); ?>" class="file-field-label">
	<div class="job-manager-uploaded-files">
	<?php if ( ! empty( $field['value'] ) ) : ?>
		<?php if ( is_array( $field['value'] ) ) : ?>
			<?php foreach ( $field['value'] as $value ) : ?>
				<?php get_job_manager_template( 'form-fields/uploaded-file-html.php', array( 'key' => $key, 'name' => 'current_' . $field_name, 'value' => $value, 'field' => $field ) ); ?>
			<?php endforeach; ?>
		<?php elseif ( $value = $field['value'] ) : ?>
			<?php get_job_manager_template( 'form-fields/uploaded-file-html.php', array( 'key' => $key, 'name' => 'current_' . $field_name, 'value' => $value, 'field' => $field ) ); ?>
		<?php endif; ?>
	<?php endif; ?>
	</div>

	<input type="file" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-file_types="<?php echo esc_attr( implode( '|', $allowed_mime_types ) ); ?>" <?php if ( ! empty( $field['multiple'] ) ) echo 'multiple'; ?> name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?><?php if ( ! empty( $field['multiple'] ) ) echo '[]'; ?>" <?php if ( ! empty( $field[ 'multiple' ] ) ) : ?>data-multiple-caption="<?php esc_attr_e( '%d files selected', 'listify', 'wp-job-manager-field-editor' ); ?>"<?php endif; ?> id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo empty( $field['placeholder'] ) ? '' : esc_attr( $field['placeholder'] ); ?>" />

	<span class="button button--size-medium">
		<?php if ( in_array( $key, array( 'featured_image', 'company_logo' ) ) ) : ?>
			<?php _e( 'Choose Image', 'jobify', 'wp-job-manager-field-editor' ); ?>
		<?php else : ?>
			<?php _e( 'Choose File', 'jobify', 'wp-job-manager-field-editor' ); ?>
		<?php endif; ?>
	</span>
</label>

<small class="description file-field-description">
	<?php if ( ! empty( $field['description'] ) ) : ?>
		<?php echo $field['description']; ?>
	<?php else : ?>
		<?php
			if ( ! empty( $field[ 'multiple' ] ) && ! empty( $field[ 'max_uploads' ] ) ) {
				printf( __( 'Maximum files: %s.', 'wp-job-manager-field-editor' ), $field[ 'max_uploads' ] );
				echo "<br />";
			}

			// Output maximum filesize from config if set, otherwise output WordPress defaults
			$max_file_size = array_key_exists( 'max_upload_size', $field ) && ! empty( $field['max_upload_size'] ) ? job_manager_field_editor_size_to_bytes( $field['max_upload_size'] ) : wp_max_upload_size();
			printf( __( 'Maximum file size: %s.', 'wp-job-manager-field-editor' ), size_format( $max_file_size ) );
		?>
		<?php
			// Output maximum image dimensions if configured in field config
			$max_width  = array_key_exists( 'max_upload_width', $field ) && ! empty( $field['max_upload_width'] ) ? (int) $field['max_upload_width'] : false;
			$max_height = array_key_exists( 'max_upload_height', $field ) && ! empty( $field['max_upload_height'] ) ? (int) $field['max_upload_height'] : false;

			if ( $max_width && $max_height ) {
				echo '<br />';
				printf( __( 'Maximum image dimensions: %1$s x %2$s (in pixels)', 'wp-job-manager-field-editor' ), $max_width, $max_height );
			} elseif ( $max_width && ! $max_height ) {
				echo '<br />';
				printf( __( 'Maximum image width: %1$s (in pixels)', 'wp-job-manager-field-editor' ), $max_width );
			} elseif ( ! $max_width && $max_height ) {
				echo '<br />';
				printf( __( 'Maximum image height: %1$s (in pixels)', 'wp-job-manager-field-editor' ), $max_height );
			}
		?>
	<?php endif; ?>
</small>
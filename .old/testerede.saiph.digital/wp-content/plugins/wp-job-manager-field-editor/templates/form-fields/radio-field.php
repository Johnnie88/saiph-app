<?php
	if( ! isset( $admin ) || ! $admin ){
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'jmfe-radio-field' );
	}

$key_class = "radio-" . esc_attr( $key );
$classes   = array( 'jmfe-radio-field', 'jmfe-radio', 'input-radio' );
$classes[] = $key_class;
$classes[] = "jmfe-radio-" . esc_attr( $field['meta_key'] );
$maybe_required = ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', true ) ? 'required' : '';
do_action( 'field_editor_before_output_template_radio-field', $field, $key, $args );
?>
<?php
	if( $key ) $field[ 'meta_key' ] = $key;

	$has_selection = false;
	foreach ( $field[ 'options' ] as $key => $value ) :
		$key = str_replace( '*', '', $key, $replace_default );
		$key = str_replace( '~', '', $key, $replace_disabled );
		// Only set default if it's not disabled as well
		if ( $replace_default > 0 && $replace_disabled < 1 ) {
			$args['field'][ 'default' ] = $key;
		}

		$disabled_option = $replace_disabled > 0 ? 'disabled="disabled"' : '';
		$field_value = job_manager_field_editor_get_template_value( $args );

		$maybe_checked = checked( $field_value, $key, false );
		if( ! empty( $maybe_checked ) ){
			$has_selection = true;
		}
?>
		<label><input type="radio" style="margin-left: 5px; margin-right: 5px; width: auto;" data-meta_key="<?php echo $field[ 'meta_key' ]; ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field[ 'name' ] ) ? $field[ 'name' ] : $field['meta_key'] ); ?>" id="<?php echo $field['meta_key'] . '-' . esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $maybe_checked ); ?> <?php echo $disabled_option; ?> <?php echo $maybe_required; ?>/><?php echo esc_html( $value ); ?></label>
<?php
	endforeach;
	if( ! isset( $admin ) || ! $admin ):
?>
	<small data-meta_key="<?php echo $field['meta_key']; ?>" alt="<?php _e( 'Clear Selection', 'wp-job-manager-field-editor' ); ?>" class="jmfe-clear-radio jmfe-clear-radio-<?php echo $field[ 'meta_key' ]; ?>" style="<?php if( ! $has_selection ) echo 'display: none;'; ?>margin-left: 5px; cursor: pointer;">
		<span class="dashicons dashicons-dismiss" style="vertical-align: middle;"></span>
	</small>
<?php
	endif;
	if ( ! empty( $field[ 'description' ] ) ) : ?><small class="description <?php echo $key_class; ?>-description"><?php echo $field[ 'description' ]; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_radio-field', $field, $key, $args ); ?>

<?php
$enable_chosen = WP_Job_Manager_Field_Editor::enable_chosen();
$key_class = "select-" . esc_attr( $key );
$classes   = array( 'jmfe-select-field', 'jmfe-input-select', 'input-select' );
$classes[] = $key_class;
// Chosen.JS has issues with HTML5 required attributes, so only use if filter used to return true value
// @see https://github.com/harvesthq/chosen/issues/515
$maybe_required = ! empty( $field['required'] ) && apply_filters( 'job_manager_field_editor_select_use_html5_required', get_option( 'jmfe_fields_html5_required', true ) ) ? 'required' : '';
$selected_value = job_manager_field_editor_get_template_value( $args );

$enable_enhanced = get_option( 'jmfe_enable_enhanced_select_fields', false );

if( ! empty( $maybe_required ) ) {
	if( $enable_chosen ){
		$classes[] = 'jmfe-required-chosen';
	} else {
		$classes[] = 'jmfe-required-select2';
	}
}

if( ! empty( $enable_enhanced ) ){
	if( $enable_chosen ){
		wp_enqueue_script( 'wp-job-manager-select-legacy' );
	} else {
		wp_enqueue_script( 'wp-job-manager-select' );
		wp_enqueue_style( 'select2' );
	}
}

do_action( 'field_editor_before_output_template_select-field', $field, $key, $args );
?>
<select class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php echo $maybe_required; ?>>
	<?php
	foreach ( $field['options'] as $key => $value ) :
		$key = str_replace( '*', '', $key, $replace_default );
		$key = str_replace( '~', '', $key, $replace_disabled );

		if( $replace_default > 0 && empty( $selected_value ) ) $selected_value = $key;
		$disabled_option = $replace_disabled > 0 ? 'disabled="disabled"' : '';
	?>

		<option value="<?php echo $key === ' ' ? '' : esc_attr( $key ); ?>" <?php if ( $selected_value ) selected( $selected_value, $key ); ?> <?php echo $disabled_option; ?>><?php echo esc_html( $value ); ?></option>
	<?php endforeach; ?>
</select>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description <?php echo $key_class; ?>-description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_select-field', $field, $key, $args ); ?>
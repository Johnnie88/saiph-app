<?php
	wp_enqueue_script( 'jmfe-date-field' );
	wp_enqueue_style( 'jquery-ui' );

	// In case $class is not defined already
	$class = isset( $args['class'] ) ? $args['class'] : FALSE;
	// In case $field['name'] is not defined or set
	$field_name = isset( $field['name'] ) ? $field['name'] : $key;

	// Is this a repeatable field?
	$is_repeatable = strpos( $field_name, '%%repeated-row-index%%' ) !== FALSE ? TRUE : FALSE;
	$maybe_required = ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', true ) ? 'required' : '';
	/**
	 * Filter whether or not to output ID with date field
	 *
	 * By default jQuery UI date picker uses the ID field when setting a value from the date picker.
	 * If for some reason, multiple fields have the same ID and are used with date picker, will cause
	 * the first field value to be set when any other with same ID has dates selected.  We omit the ID
	 * to allow jQuery UI to generate it's own unique ID
	 */
	$include_id = apply_filters( 'job_manager_date_picker_include_ID', ! $is_repeatable, $field, $args, $class );

	$key_class = "date-picker-" . esc_attr( $key );
	$classes   = array( 'jmfe-date-field', 'jmfe-input-date', 'input-date', 'input-text', 'jmfe-date-picker' );
	$classes[] = $key_class;
	$value = job_manager_field_editor_get_template_value( $args, false );
	do_action( 'field_editor_before_output_template_date-field', $field, $key, $args );
?>
<input type="text" <?php if( $include_id ) echo 'id="' . esc_attr( $key ) .'"'; ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo $value; ?>" maxlength="<?php echo ! empty( $field['maxlength'] ) ? $field['maxlength'] : ''; ?>" <?php echo $maybe_required; ?>/>
<?php if ( ! empty( $field['description'] ) ) : ?><span class="description <?php echo $key_class; ?>-description"><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_date-field', $field, $key, $args ); ?>
<?php
	wp_enqueue_script( 'jmfe-range-field' );
	$name = esc_attr( isset($field['name']) ? $field['name'] : $key );
	$min = isset( $field['min'] ) && is_numeric( $field['min'] ) ? floatval($field['min']) : 0;
	$max = isset( $field['max'] ) && is_numeric( $field['max'] ) ? floatval($field['max']) : 10;
	$step = isset( $field['step'] ) && is_numeric( $field['step'] ) ? floatval($field['step']) : 1;
	$prepend = isset( $field['prepend'] ) ? esc_attr( $field['prepend'] ) : '';
	$append = isset( $field['append'] ) ? esc_attr( $field['append'] ) : '';
	$value = job_manager_field_editor_get_template_value( $args, false );

	// Set value to default if there is no value, or if the value is not a number
	if( isset( $field['default'] ) && ! is_numeric( $value ) ) {
		$value = floatval( $field['default'] );
	}

	// If you want to show min/max before and after slider, need to use this filter to do so
	$show_min_max = apply_filters( 'field_editor_range_input_show_min_max', FALSE );

	$key_class = 'range-' . esc_attr( $key );
	$classes   = array( 'jmfe-range-field', 'jmfe-input-range', 'input-range' );
	$classes[] = $key_class;

	$maybe_required = ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', TRUE ) ? 'required' : '';
	do_action( 'field_editor_before_output_template_range-field', $field, $key, $args );
?>
<?php if( ! isset( $admin ) ): ?>
<div class="jmfe-input-range-wrapper  <?php echo $key_class; ?>-wrapper">
<?php endif; ?>

	<?php if( $show_min_max ): ?><span id="<?php echo esc_attr( $key ); ?>-min" class="jmfe-input-range-value-min <?php echo $key_class; ?>-value-min"><?php echo $min; ?></span><?php endif; ?>
	<input type="range" data-prepend="<?php echo $prepend; ?>" data-append="<?php echo $append; ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo $name; ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo floatval($value); ?>" title="<?php echo isset($field['title']) ? esc_attr( $field['title'] ) : ''; ?>" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" <?php echo $maybe_required; ?> />
	<?php if($show_min_max): ?><span class="jmfe-input-range-value-max <?php echo $key_class; ?>-value-max" id="<?php echo esc_attr( $key ); ?>-max"><?php echo $max; ?></span><?php endif; ?>

	<output for="<?php echo $name; ?>" id="<?php echo esc_attr( $key ); ?>-output" class="jmfe-input-range-value  <?php echo $key_class; ?>-value" style="position: relative; display: inline-block; margin-left: 10px; vertical-align: top;">
	</output>
<?php if ( ! isset( $admin ) ): ?>
</div>
<?php endif; ?>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description <?php echo $key_class; ?>-description range-description" style="display: initial;"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_range-field', $field, $key, $args ); ?>

<?php
wp_enqueue_script( 'jmfe-fpdate-field' );
wp_enqueue_style( 'jmfe-flatpickr-style' );

// Allow using Flatpickr for date picker on job/app deadline field (job_deadline on frontend, application_deadline in admin area)
// Application Deadline v1.2.1+ has switched to using core WPJM 1.30.0+ date handling
if( $key === 'job_deadline' || $key === 'application_deadline' || $key === '_application_deadline' ) {
	if ( wp_script_is( 'wp-job-manager-deadline', 'enqueued' ) ) {
		wp_dequeue_script( 'wp-job-manager-deadline' );
	}
}

$key_class = 'fpdate-' . esc_attr( $key );
$classes   = array( 'jmfe-fpdate-field', 'jmfe-input-fpdate', 'input-fpdate', 'jmfe-fpdate-picker' );
$classes[] = $key_class;
$maybe_required = ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', true ) ? 'required' : '';

if( ! empty( $maybe_required ) ) $classes[] = 'jmfe-fpdate-required';
// Build data attributes from field configuration, this allows for customization of the field type
// as values as pulled in JS from data attributes and override filter or default configs.
$data_atts = '';
$data_vals = apply_filters( 'job_manager_field_editor_fpdate_field_data_fields', array( 'picker_mode', 'picker_max_date', 'picker_min_date', 'default' ) );
foreach( $data_vals as $data_val ){

	if( ! array_key_exists( $data_val, $field ) ) {
		continue;
	}
	$data_key = str_replace( 'picker_', '', $data_val );
	$data_atts .= " data-{$data_key}=\"{$field[ $data_val ]}\"";
}
$value = job_manager_field_editor_get_template_value( $args, false );
do_action( 'field_editor_before_output_template_fpdate-field', $field, $key, $args );
?>
<input type="text" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" title="<?php echo isset($field['title']) ? esc_attr( $field['title'] ) : ''; ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo $value; ?>" <?php echo $data_atts;?> <?php echo $maybe_required; ?>/>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description <?php echo $key_class; ?>-description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_fpdate-field', $field, $key, $args ); ?>
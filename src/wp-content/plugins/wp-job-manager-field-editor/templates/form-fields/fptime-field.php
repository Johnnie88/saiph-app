<?php
wp_enqueue_script( 'jmfe-fptime-field' );
wp_enqueue_style( 'jmfe-flatpickr-style' );

// Build data attributes from field configuration, this allows for customization of the field type
// as values as pulled in JS from data attributes and override filter or default configs.
$data_atts = '';
$data_vals = apply_filters( 'job_manager_field_editor_fptime_field_data_fields', array( 'picker_increment' ) );
foreach ( $data_vals as $data_val ) {

	if ( ! array_key_exists( $data_val, $field ) ) {
		continue;
	}
	$data_key  = str_replace( 'picker_', '', $data_val );
	$data_atts .= " data-{$data_key}=\"{$field[ $data_val ]}\"";
}

$key_class = 'fptime-' . esc_attr( $key );
$classes   = array( 'jmfe-fptime-field', 'jmfe-input-fptime', 'input-fptime', 'jmfe-fptime-picker' );
$classes[] = $key_class;
$maybe_required = ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', TRUE ) ? 'required' : '';
if( ! empty( $maybe_required ) ) $classes[] = 'jmfe-fptime-required';

$value = job_manager_field_editor_get_template_value( $args );
$detect = new WP_Job_Manager_Field_Editor_Mobile_Detect();

// Mobile specific handling, to display placeholder in "time" field types
if ( $detect->isMobile() ) {
	$classes[] = 'jmfe-mobile-time-ph';

	// Any iOS device, Firefox (Android or iOS), & Safari require specific styling
	if ( $detect->isIOS() || $detect->is( 'Safari' ) || $detect->is( 'Firefox' ) ) {
		echo '<style>.jmfe-mobile-time-ph:after { content: attr(placeholder); } .jmfe-fptime-field { width: 100%; -webkit-appearance: textfield; -moz-appearance: textfield; display: block; border: 1px solid #ccc; }</style>';
	} else {
		echo '<style>.jmfe-mobile-time-ph:after { content: attr(placeholder); } .jmfe-fptime-field { width: 100%; }</style>';
	}
}
do_action( 'field_editor_before_output_template_fptime-field', $field, $key, $args );
?>
<input type="text" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" title="<?php echo isset($field['title']) ? esc_attr( $field['title'] ) : ''; ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo $value; ?>" <?php echo $data_atts; ?> <?php echo $maybe_required; ?>/>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description <?php echo $key_class; ?>-description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_fptime-field', $field, $key, $args ); ?>
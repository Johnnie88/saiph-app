<?php
/**
 * WP Job Manager Field Editor Checklist Field Template
 *
 * @author  Myles McNamara
 * @since   1.8.15
 *
 * @version 1.0.0
 */
$key_class = "jmfe-checklist-" . esc_attr( $key );
$classes   = array( 'jmfe-checklist-field', 'jmfe-checklist', 'input-checklist' );
$classes[] = $key_class;
// Default should be empty string, only when max selected is defined should it have a custom string
$max_selected       = array_key_exists( 'max_selected', $field ) && ! empty( $field['max_selected'] ) ? $field['max_selected'] : '';
$max_selected_class = array_key_exists( 'max_selected', $field ) && ! empty( $field['max_selected'] ) ? 'jmfe-checklist-max-checked' : '';
$esc_key            = esc_attr( $key );
$maybe_required = ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', true ) ? 'required' : '';

//$classes[] = "jmfe-checklist-" . esc_attr( $field['meta_key'] );
do_action( 'field_editor_before_output_template_checklist-field', $field, $key, $args );
if ( ! empty( $max_selected ) ) {
	wp_enqueue_script( 'jmfe-checklist-field' );
}
?>
<div class="jmfe-checklist-wrapper <?php echo $max_selected_class; ?>" data-max-selected="<?php echo $max_selected; ?>" data-meta-key="<?php echo $esc_key ?>">
<?php
if ( $key ) {
	$field['meta_key'] = $key;
}
$field_values = job_manager_field_editor_get_template_value( $args );
if ( ! is_array( $field_values ) ) {
	$field_values = array();
}

foreach ( $field['options'] as $key => $value ) :
	$key = str_replace( '*', '', $key, $replace_default );
	$key      = str_replace( '~', '', $key, $replace_disabled );

	// Only set default if it's not disabled as well
	if ( $replace_default > 0 && $replace_disabled < 1 ) {
		$args['field']['default'] = $key;
	}

	$disabled_option = $replace_disabled > 0 ? 'disabled="disabled"' : '';
	$in_field_values = in_array( $key, $field_values ) || ( empty( $field_values ) && $args['field']['default'] === $key );
	$maybe_checked   = checked( $in_field_values, true, false );
	?>
	<label class="jmfe-checklist-label"><input type="checkbox" style="margin-left: 5px; margin-right: 5px; width: auto;" data-meta_key="<?php echo $field['meta_key']; ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $field['meta_key'] ); ?>[]" id="<?php echo $field['meta_key'] . '-' . esc_attr( $key ); ?>" value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $maybe_checked ); ?> <?php echo $disabled_option; ?>/><?php echo esc_html( $value ); ?></label>
<?php
endforeach;
// Max selections handling for args
if ( ! empty( $max_selected ) && empty( $field['description'] ) ) {
	// Set description showing max selections (if custom one not specified)
	if ( empty( $field['description'] ) ) {
		$field['description'] = sprintf( __( 'Maximum selections: %s', 'wp-job-manager-field-editor' ), $max_selected );
	}

}
// Add closing div and reopen div to wrap description in separate div to show below checkboxes
if ( ! empty( $field['description'] ) ) : ?></div><div class="jmfe-checklist-desc-wrap" style="float: left;"><small class="description <?php echo $key_class; ?>-description"><?php echo $field['description']; ?></small><?php endif; ?>
	<?php do_action( 'field_editor_after_output_template_checklist-field', $field, $key, $args ); ?>
</div>

<?php
/**
 * Shows a Google Maps Places AutoComplete field
 *
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Myles McNamara
 * @package     WP Job Manager Field Editor
 * @category    Template
 * @version     1.8.3
 */

// Type in capitalization, left for backwards compatibility sake
$options = apply_filters( 'job_manager_Field_editor_autocomplete_field_options', array(), $key, $args, $field );
$options = apply_filters( 'job_manager_field_editor_autocomplete_field_options', array(), $key, $args, $field );

$script_handle = 'google-maps-places-ac';

// Check for other themes/plugins that already enqueued/registered Maps API JS
if ( wp_script_is( 'google-maps', 'enqueued' ) || wp_script_is( 'google-maps', 'registered' ) ) {
	$script_handle = 'google-maps';
} else if ( wp_script_is( 'google-maps-api', 'enqueued' ) || wp_script_is( 'google-maps-api', 'registered' ) ) {
	$script_handle = 'google-maps-api';
}

wp_enqueue_script( $script_handle );

if( ! empty( $options ) ){
	wp_localize_script( 'jmfe-autocomplete-field', "jmfe_ac_{$key}", $options );
}

wp_enqueue_script( 'jmfe-autocomplete-field' );

$key_class = "autocomplete-" . esc_attr( $key );
$classes   = array( 'jmfe-autocomplete-field', 'jmfe-input-autocomplete', 'input-autocomplete' );
$classes[] = $key_class;
$maybe_required = ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', TRUE ) ? 'required' : '';
$value = job_manager_field_editor_get_template_value( $args );
$placeholder = array_key_exists( 'placeholder', $field ) ? esc_attr( $field['placeholder'] ) : '';
do_action( 'field_editor_before_output_template_autocomplete-field', $field, $key, $args );
?>
<input type="text" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" title="<?php echo isset($field['title']) ? esc_attr( $field['title'] ) : ''; ?>" <?php echo ! empty($field['pattern']) ? "pattern=\"" . esc_attr($field['pattern']) . "\"" : ''; ?> placeholder="<?php echo $placeholder; ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo ! empty( $field['maxlength'] ) ? "maxlength=\"" . esc_attr( $field['maxlength'] ) . "\"" : ''; ?> <?php echo $maybe_required; ?> />
<?php if ( ! empty( $field['description'] ) && ( ! isset( $admin ) || ! $admin ) ) : ?><small class="description <?php echo $key_class; ?>-description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_autocomplete-field', $field, $key, $args ); ?>

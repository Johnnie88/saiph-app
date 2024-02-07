<?php
$key_class = "hidden-" . esc_attr( $key );
$classes   = array( 'jmfe-hidden-field', 'jmfe-input-hidden', 'input-hidden' );
$classes[] = $key_class;
$value = job_manager_field_editor_get_template_value( $args );
do_action( 'field_editor_before_output_template_hidden-field', $field, $key, $args );
?>
<style>.fieldset-<?php echo esc_attr( $key ) ?>{ display: none !important; }</style>
<input type="hidden" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
<?php do_action( 'field_editor_after_output_template_hidden-field', $field, $key, $args ); ?>
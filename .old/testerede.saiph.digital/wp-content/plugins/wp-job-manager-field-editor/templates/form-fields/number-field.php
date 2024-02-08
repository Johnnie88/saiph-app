<?php
$key_class = 'number-' . esc_attr( $key );
$classes = array( 'jmfe-number-field', 'input-number' );
$classes[] = $key_class;
$maybe_required = ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', TRUE ) ? 'required' : '';

$value = job_manager_field_editor_get_template_value( $args );
do_action( 'field_editor_before_output_template_number-field', $field, $key, $args );
?>
<input type="number" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>"
	   id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo $value; ?>"
	   step="<?php echo ! empty( $field['step'] ) ? $field['step'] : '1'; ?>"
		<?php echo isset( $field['maxlength'] ) && $field['maxlength'] !== '' ? "maxlength=\"" . esc_attr( $field['maxlength'] ) . "\"" : ''; ?>
		<?php echo isset( $field['min'] ) && $field['min'] !== '' ? "min=\"" . esc_attr( $field['min'] ) . "\"" : ''; ?>
		<?php echo isset( $field['max'] ) && $field['max'] !== '' ? "max=\"" . esc_attr( $field['max'] ) . "\"" : ''; ?>
		<?php echo ! empty( $field['pattern'] ) ? "pattern=\"" . esc_attr( $field['pattern'] ) . "\"" : ''; ?> <?php echo $maybe_required; ?> />

<?php if ( ! empty( $field['description'] ) ) : ?><small class="description <?php echo $key_class; ?>-description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_number-field', $field, $key, $args ); ?>

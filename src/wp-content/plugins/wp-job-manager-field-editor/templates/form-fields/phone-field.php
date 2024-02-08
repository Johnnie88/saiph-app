<?php
wp_enqueue_script( 'jmfe-phone-field' );
wp_enqueue_style( 'jmfe-phone-field-style' );
$classes   = array( 'jmfe-phone-field', 'jmfe-phone', 'input-phone' );
$classes[] = "phone-" . esc_attr( $key );
do_action( 'field_editor_before_output_template_phone-field', $field, $key, $args );
?>
<input type="tel" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo isset( $field['value'] ) ? esc_attr( $field['value'] ) : ''; ?>" />
<?php if ( ! empty( $field['description'] ) ) : ?><span class="description"><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_phone-field', $field, $key, $args ); ?>
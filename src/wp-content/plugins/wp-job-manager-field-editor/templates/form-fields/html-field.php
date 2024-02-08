<?php do_action( 'field_editor_before_output_template_html-field', $field, $key, $args ); ?>
<?php if ( ! empty( $field['description'] ) ) echo $field[ 'description' ]; ?>
<?php do_action( 'field_editor_after_output_template_html-field', $field, $key, $args ); ?>

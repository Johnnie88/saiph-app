<?php
$key_class = "header-" . esc_attr( $key );
$classes = array( 'jmfe-header-field' );
wp_enqueue_script('jmfe-header-field');
$classes[] = $key_class;
do_action( 'field_editor_before_output_template_header-field', $field, $key, $args );
?>
<h2 class="<?php echo esc_attr( implode( ' ', $classes ) ); ?> fieldset-<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php if ( ! empty( $field[ 'description' ] ) ) : ?> style="" <?php endif; ?> /><?php echo $field['label']; ?></h2>
<?php if ( ! empty( $field['description'] ) ) : ?><div id="description-<?php echo esc_attr( $key ); ?>" class="fieldset-<?php echo esc_attr( $key ); ?>"><small class="description <?php echo $key_class; ?>-description"><?php echo $field['description']; ?></small></div><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_header-field', $field, $key, $args ); ?>
<?php
/**
 * Shows the right `textarea` form field with WP Editor on job listing forms.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/form-fields/wp-editor-field.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic & Myles McNamara
 * @package     WP Job Manager
 * @category    Template
 * @version     1.8.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$maybe_required = array_key_exists( 'required', $field ) && ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', true );
/**
 * Filter using HTML5 Required on WP Editor
 *
 * Due to possible issues this extra filter is available to disable HTML5 required on WP Editor fields (and still retain it on other fields)
 */
$maybe_required = apply_filters( 'field_editor_wp_editor_html5_required', $maybe_required, $field );
$value = job_manager_field_editor_get_template_value( $args );

$editor = apply_filters( 'submit_job_form_wp_editor_args', array(
	'textarea_name' => isset( $field['name'] ) ? $field['name'] : $key,
	'media_buttons' => false,
	'textarea_rows' => 8,
	'quicktags'     => false,
	'tinymce'       => array(
		'plugins'                       => 'lists,paste,tabfocus,wplink,wordpress',
		'paste_as_text'                 => true,
		'paste_auto_cleanup_on_paste'   => true,
		'paste_remove_spans'            => true,
		'paste_remove_styles'           => true,
		'paste_remove_styles_if_webkit' => true,
		'paste_strip_class_attributes'  => true,
		'toolbar1'                      => 'bold,italic,|,bullist,numlist,|,link,unlink,|,undo,redo',
		'toolbar2'                      => '',
		'toolbar3'                      => '',
		'toolbar4'                      => ''
	),
	'editor_class' => $maybe_required ? 'jmfe-add-required-attr jmfe-wp-editor' : 'jmfe-wp-editor'
) );
do_action( 'field_editor_before_output_template_wp-editor-field', $field, $key, $args );
wp_editor( isset( $value ) ? wpautop( wp_kses_post( $value ) ) : '', $key, $editor );
if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo wp_kses_post( $field['description'] ); ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_wp-editor-field', $field, $key, $args ); ?>
<?php
/**
 * Shows term `select` form field on job listing forms.
 *
 * This template can be overridden by copying it to yourtheme/field_editor/form-fields/term-select-field.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic, Myles McNamara
 * @package     WP Job Manager Field Editor
 * @category    Template
 * @version     1.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$enable_chosen = WP_Job_Manager_Field_Editor::enable_chosen();

$selected = job_manager_field_editor_get_template_value( $args );

$maybe_required = array_key_exists( 'required', $field ) && ! empty( $field['required'] ) && get_option( 'jmfe_fields_html5_required', true );

$placeholder = array_key_exists( 'placeholder', $field ) && ! empty( $field['placeholder'] ) ? $field['placeholder'] : __( 'Select an Option...', 'wp-job-manager-field-editor' );

// Select only supports 1 value.
if ( is_array( $selected ) ) {

	foreach( (array) $selected as $maybechild_id ){

		$term = get_term( $maybechild_id, $field['taxonomy'] );

		if( $term && $term->parent === 0 ){
			$selected = $maybechild_id;
			break;
		}
	}

	if( is_array( $selected ) ){
		$selected = current( $selected );
	}
}

$args = array(
	'taxonomy'         => $field['taxonomy'],
	'hierarchical'     => 1,
	'class'            => 'job-manager-category-dropdown',
	'show_option_all'  => false,
	'option_none_value' => '',
	'show_option_none' => $placeholder,
	'name'             => isset( $field['name'] ) ? $field['name'] : $key,
	'orderby'          => 'name',
	'selected'         => $selected,
	'hide_empty'       => false,
	'required'		   => $maybe_required,
);

if ( $enable_chosen ) {
	wp_enqueue_script( 'wp-job-manager-term-multiselect-legacy' );
	// Required for custom CSS that hides Select2 boxes that may be init by other plugins/themes, as we are init on that field with chosen
	$args['class'] .= ' jmfe-chosen-select-field';
} else {
	wp_enqueue_script( 'wp-job-manager-term-multiselect' );
	wp_enqueue_style( 'select2' );
	$args['class'] .= ' jmfe-select2-select-field';
}

if ( $maybe_required ) {
	if( $enable_chosen ){
		$args['class'] .= ' jmfe-required-chosen';
	} else {
		$args['class'] .= ' jmfe-required-select2';
	}
}

// Add specific term IDs to exclude from showing
if ( array_key_exists( 'tax_exclude_terms', $field ) && ! empty( $field['tax_exclude_terms'] ) ) {
	// Use exclude tree to prevent children from showing as well
	$args['exclude'] = $field['tax_exclude_terms'];
}

if ( $field['required'] && array_key_exists( 'placeholder', $field ) && ! empty( $field['placeholder'] ) ) {
	$args['show_option_none'] = $field['placeholder'];
}

// Dynamic Taxonomy fields
if ( array_key_exists( 'tax_show_child', $field ) && ! empty( $field['tax_show_child'] ) ) {
	// Set parent to 0 to only display top-level terms
	$args['parent'] = 0;
	// First check if class is already set and use that value, otherwise set to default so we can add our own
	$args['class'] .= ' dynamic-single-select jmfe-dynamic-tax ' . ( is_rtl() ? 'chosen-rtl' : '' );

	wp_enqueue_script( 'jmfe-dynamic-tax' );
	wp_enqueue_style( 'jmfe-dynamic-tax' );
}
do_action( 'field_editor_before_output_template_term-select-field', $field, $key, $args );
$dropdown_args = apply_filters( 'job_manager_term_select_field_wp_dropdown_categories_args', $args, $key, $field );

/**
 * Prevent Astoundify Regions from overriding the placeholder custom configuration
 */
if( $key === 'job_region' && array_key_exists( 'placeholder', $field ) && ! empty( $field['placeholder'] ) ){
	$dropdown_args['show_option_none'] = $placeholder;
}

wp_dropdown_categories( $dropdown_args );

if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo wp_kses_post( $field['description'] ); ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_term-select-field', $field, $key, $args ); ?>
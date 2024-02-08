<?php
/**
 * Shows term `multiselect` form field on submit listing forms.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/form-fields/term-multiselect-field.php.
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

$args = array(
	'taxonomy'     => $field['taxonomy'],
	'hierarchical' => 1,
	'name'         => isset( $field['name'] ) ? $field['name'] : $key,
	'orderby'      => 'name',
	'selected'     => $selected,
	'hide_empty'   => false,
	'class'        => is_rtl() ? 'chosen-rtl' : ''
);

if ( array_key_exists( 'required', $field ) && ! empty( $field['required'] ) && apply_filters( 'job_manager_field_editor_select_use_html5_required', get_option( 'jmfe_fields_html5_required', true ) ) ) {
	$select_class = $enable_chosen ? 'jmfe-required-chosen' : 'jmfe-required-select2';
	$args['class'] .= " {$select_class} jmfe-add-required-attr";
	wp_enqueue_script('jmfe-html5-required'); // make sure it's loaded (should be) as this also adds the required flag
}

if( $enable_chosen ){
	wp_enqueue_script( 'wp-job-manager-term-multiselect-legacy' );
	// Required for custom CSS that hides Select2 boxes that may be init by other plugins/themes, as we are init on that field with chosen
	$args['class'] .= ' jmfe-chosen-select-field';
} else {
	wp_enqueue_script( 'wp-job-manager-term-multiselect' );
	wp_enqueue_style( 'select2' );
	$args['class'] .= ' jmfe-select2-select-field';
}

// Check for custom configurations (that require separate jQuery initalization)
if ( array_key_exists( 'max_selected', $field ) && ! empty( $field[ 'max_selected' ] ) ) {
	// Set class to value that would be set by job_manager_dropdown_categories()
	// This prevents job-manager-category-dropdown class from being added
	$args[ 'class' ] .= ' jmfe-multiselect-field-check-max';
	$max_selected    = $field[ 'max_selected' ];
	$esc_key         = esc_attr( $key );

	// Add this to $args array to specify that we specifically did not add job-manager-category-dropdown class
	// so we could initialize it ourselves with our own javascript
	$args['fe_max_selected'] = $max_selected;

	// If value has more than allowed selected, set selected to max amount of values
	if( is_array( $args['selected'] ) && ! empty( $args['selected'] ) ){
		$args['selected'] = array_slice( $args['selected'], 0, $max_selected );
	}

	if( $enable_chosen ){
		// Generate custom jQuery code to initialize chosen element
		$multi_script = "var {$esc_key}_max = {$max_selected}; jQuery(function($){ jQuery('#{$esc_key}').chosen({ max_selected_options: {$max_selected}, search_contains: true }); });";
		wp_add_inline_script( 'wp-job-manager-term-multiselect-legacy', $multi_script );
	} else {
		// Generate custom jQuery code to initialize Select2 element
		$multi_script = "var {$esc_key}_max = {$max_selected}; jQuery(function($){ jQuery('#{$esc_key}').select2({ maximumSelectionLength: {$max_selected}, minimumResultsForSearch: 10, width: '100%' }); });";
		wp_add_inline_script( 'wp-job-manager-term-multiselect', $multi_script );
	}

	wp_enqueue_script( 'jmfe-compatibility' );

	if( empty( $field[ 'description' ] ) ){
		$field['description'] = sprintf( __( 'Maximum selections: %s', 'wp-job-manager-field-editor' ), $max_selected );
	}
} else {
	// Add default chosen init class if this isn't a custom init field
	$args['class'] .= ' job-manager-category-dropdown';
}

// Add specific term IDs to exclude from showing
if ( array_key_exists( 'tax_exclude_terms', $field ) && ! empty( $field['tax_exclude_terms'] ) ) {
	// Use exclude tree to prevent children from showing as well
	$args['exclude'] = $field['tax_exclude_terms'];
}

// Dynamic Taxonomy fields
if( array_key_exists( 'tax_show_child', $field ) && ! empty( $field['tax_show_child'] ) ){
	// Set parent to 0 to only display top-level terms
	$args['parent'] = 0;
	$args['class'] .= ' jmfe-dynamic-tax';

	wp_enqueue_script( 'jmfe-dynamic-tax' );
	wp_enqueue_style( 'jmfe-dynamic-tax' );
}

if ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) $args['placeholder'] = $field['placeholder'];

do_action( 'field_editor_before_output_template_term-multiselect-field', $field, $key, $args );

job_manager_dropdown_categories( apply_filters( 'job_manager_term_multiselect_field_args', $args ) );

if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_term-multiselect-field', $field, $key, $args ); ?>
<?php
/**
 * Shows `checkbox` form fields in a list from a list on job listing forms.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/form-fields/term-checklist-field.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic/Myles McNamara
 * @package     WP Job Manager Field Editor
 * @category    Template
 * @version     1.7.10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Default should be empty string, only when max selected is defined should it have a custom string
$max_selected_class = '';
$max_selected = '';
$esc_key = esc_attr( $key );

// Check for custom configurations (that require separate jQuery initalization)
if ( array_key_exists( 'max_selected', $field ) && ! empty( $field['max_selected'] ) ) {

	$max_selected = $field['max_selected'];
	// Set to specific class to allow max selected jQuery handling
	$max_selected_class = 'jmfe-term-checklist-max-checked';
}
do_action( 'field_editor_before_output_template_term-checklist-field', $field, $key, $args );
?>
<ul class="job-manager-term-checklist job-manager-term-checklist-<?php echo $key; ?> <?php echo $max_selected_class; ?>" data-meta-key="<?php echo $esc_key ?>" data-max-selected="<?php echo $max_selected; ?>" data-taxonomy="<?php echo esc_attr( $field['taxonomy'] ); ?>">
<?php

	require_once( ABSPATH . '/wp-admin/includes/template.php' );

	if ( empty( $field['default'] ) ) {
		$field['default'] = '';
	}

	$args = array(
		'descendants_and_self'  => 0,
		'selected_cats'         => isset( $field['value'] ) ? $field['value'] : ( is_array( $field['default'] ) ? $field['default'] : array( $field['default'] ) ),
		'popular_cats'          => false,
		'taxonomy'              => $field['taxonomy'],
		'checked_ontop'         => false
	);

	// Max selections handling for args
	if( isset( $max_selected ) && ! empty( $max_selected ) ){

		// If value has more than allowed selected, set selected_cats to max amount of values
		if ( is_array( $args['selected_cats'] ) && ! empty( $args['selected_cats'] ) ) {
			$args['selected_cats'] = array_slice( $args['selected_cats'], 0, $max_selected );
		}

		// Set description showing max selections (if custom one not specified)
		if ( empty( $field['description'] ) ) {
			$field['description'] = sprintf( __( 'Maximum selections: %s', 'wp-job-manager-field-editor' ), $max_selected );
		}

	}

	// $field['post_id'] needs to be passed via the args so we can get the existing terms
	ob_start();
	wp_terms_checklist( 0, $args );
	$checklist = ob_get_clean();
	echo str_replace( "disabled='disabled'", '', $checklist );
?>
</ul>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
<?php do_action( 'field_editor_after_output_template_term-checklist-field', $field, $key, $args ); ?>

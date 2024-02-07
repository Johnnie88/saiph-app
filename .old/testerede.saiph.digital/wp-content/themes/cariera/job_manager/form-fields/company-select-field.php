<?php
/**
 * Custom: Field to select all companies
 *
 * This template can be overridden by copying it to yourtheme/job_manager/form-fields/ccompany-select-field.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.0
 * @version     1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Get selected value.
$maybe_value = isset( $field['value'] ) || isset( $field['default'] ) ? isset( $field['value'] ) ? $field['value'] : $field['default'] : '';
$job_id      = ! empty( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0;
$job         = get_post( $job_id );

// Companies.
$user_companies = get_option( 'cariera_user_specific_company' );
$companies      = cariera_get_user_companies( [ 'post_status' => 'any' ], $user_companies );

if ( empty( $maybe_value ) ) {
	$maybe_value = $job->_company_manager_id;
}

$maybe_required = apply_filters( 'cariera_job_submit_company_manager_required', false ) ? 'required' : '';
?>

<select name="<?php echo isset( $field['name'] ) ? esc_attr( $field['name'] ) : esc_attr( $key ); ?>" class="cariera-select2-search" id="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $maybe_required ); ?>>
	<option value="" <?php echo empty( $maybe_value ) ? esc_attr( 'selected="selected"' ) : ''; ?>><?php esc_html_e( 'Select Company', 'cariera' ); ?></option>

	<?php
	foreach ( (array) $companies as $company ) {
		$company_title = $company->post_title;

		if ( 'pending' === $company->post_status ) {
			$company_title .= ' ' . esc_html__( '(Pending)', 'cariera' );
		}
		?>

		<option value="<?php echo esc_attr( $company->ID ); ?>" <?php echo ! empty( $maybe_value ) ? selected( $maybe_value, $company->ID ) : ''; ?>><?php echo esc_html( $company_title ); ?></option>
		<?php
	}
	?>
</select>

<?php if ( ! empty( $field['description'] ) ) { ?>
	<small class="description"><?php echo wp_kses_post( $field['description'] ); ?></small>
	<?php
}

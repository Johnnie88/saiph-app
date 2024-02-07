<?php
/**
 * Custom: Company - Company Submit Fields
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-submit-fields.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.4
 * @version     1.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

wp_enqueue_script( 'cariera-company-manager-submission' );

do_action( 'submit_company_form_company_fields_start' );

foreach ( $company_fields as $key => $field ) { ?>
	<fieldset class="fieldset-<?php echo esc_attr( $key ); ?> fieldset-type-<?php echo esc_attr( $field['type'] ); ?> cariera-company-manager-fieldset">
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . wp_kses_post( apply_filters( 'cariera_submit_company_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'cariera' ) . '</small>', $field ) ); ?></label>
		<div class="field">
			<?php $class->get_field_template( $key, $field ); ?>
		</div>
	</fieldset>
	<?php
}

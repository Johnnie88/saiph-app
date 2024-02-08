<?php
/**
 * Filter in `[jobs]` shortcode for job types.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-filter-job-types.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.31.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php if ( ! is_tax( 'job_listing_type' ) && empty( $job_types ) ) { ?>
	<ul class="job_types">
		<?php foreach ( get_job_listing_types() as $type ) { ?>
			<li>
				<div class="checkbox">
					<input type="checkbox" name="filter_job_type[]" value="<?php echo esc_attr( $type->slug ); ?>" <?php checked( in_array( $type->slug, $selected_job_types ), true ); ?> id="job_type_<?php echo esc_attr( $type->slug ); ?>" />
					<label for="job_type_<?php echo esc_attr( $type->slug ); ?>" class="job_type_label <?php echo sanitize_title( $type->name ); ?>"><?php echo esc_html( $type->name ); ?></label>
				</div>
			</li>
		<?php } ?>
	</ul>
	<input type="hidden" name="filter_job_type[]" value="" />
<?php } elseif ( $job_types ) { ?>
	<?php foreach ( $job_types as $job_type ) { ?>
		<input type="hidden" name="filter_job_type[]" value="<?php echo esc_attr( sanitize_title( $job_type ) ); ?>" />
	<?php } ?>
<?php } ?>

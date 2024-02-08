<?php
/**
* Form used when creating a new job listing alert.
*
* This template can be overridden by copying it to yourtheme/wp-job-manager-alerts/alert-form.php.
*
* @see         https://wpjobmanager.com/document/template-overrides/
* @author      Automattic
* @package     WP Job Manager - Alerts
* @category    Template
* @version     1.5.2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form method="post" class="job-manager-form submit-page">
	<fieldset>
		<label for="alert_name"><?php esc_html_e( 'Alert Name', 'cariera' ); ?></label>
		<div class="field">
			<input type="text" name="alert_name" value="<?php echo esc_attr( $alert_name ); ?>" id="alert_name" class="input-text" placeholder="<?php esc_html_e( 'Enter a name for your alert', 'cariera' ); ?>" />
		</div>
	</fieldset>
	<fieldset>
		<label for="alert_keyword"><?php esc_html_e( 'Keyword', 'cariera' ); ?></label>
		<div class="field">
			<input type="text" name="alert_keyword" value="<?php echo esc_attr( $alert_keyword ); ?>" id="alert_keyword" class="input-text" placeholder="<?php esc_html_e( 'Optionally add a keyword to match jobs against', 'cariera' ); ?>" />
		</div>
	</fieldset>
	<?php if ( taxonomy_exists( 'job_listing_region' ) && wp_count_terms( 'job_listing_region' ) > 0 ) : ?>
		<fieldset>
			<label for="alert_regions"><?php esc_html_e( 'Job Region', 'cariera' ); ?></label>
			<div class="field">
				<?php
				job_manager_dropdown_categories( array(
					'show_option_all' => false,
					'hierarchical'    => true,
					'orderby'         => 'name',
					'taxonomy'        => 'job_listing_region',
					'name'            => 'alert_regions',
					'class'           => 'alert_regions cariera-select2',
					'hide_empty'      => 0,
					'selected'        => $alert_regions,
					'placeholder'     => esc_html__( 'Any region', 'cariera' )
				) );
				?>
			</div>
		</fieldset>
	<?php else : ?>
		<fieldset>
			<label for="alert_location"><?php esc_html_e( 'Location', 'cariera' ); ?></label>
			<div class="field">
				<input type="text" name="alert_location" value="<?php echo esc_attr( $alert_location ); ?>" id="alert_location" class="input-text" placeholder="<?php esc_html_e( 'Optionally define a location to search against', 'cariera' ); ?>" />
			</div>
		</fieldset>
	<?php endif; ?>
	<?php if ( get_option( 'job_manager_enable_categories' ) && wp_count_terms( 'job_listing_category' ) > 0 ) : ?>
		<fieldset class="form">
			<label for="alert_cats"><?php esc_html_e( 'Categories', 'cariera' ); ?></label>
			<div class="field">
				<?php
					wp_enqueue_script( 'wp-job-manager-term-multiselect' );

					job_manager_dropdown_categories( array(
						'taxonomy'     => 'job_listing_category',
						'hierarchical' => 1,
						'name'         => 'alert_cats',
						'orderby'      => 'name',
						'selected'     => $alert_cats,
						'hide_empty'   => false,
						'placeholder'  => esc_html__( 'Any category', 'cariera' ),
					) );
				?>
			</div>
		</fieldset>
	<?php endif; ?>

	<?php if ( taxonomy_exists( 'job_listing_tag' ) && wp_count_terms( 'job_listing_tag' ) > 0 ) : ?>
		<fieldset>
			<label for="alert_tags"><?php esc_html_e( 'Tags', 'cariera' ); ?></label>
			<div class="field">
				<?php
					wp_enqueue_script( 'wp-job-manager-term-multiselect' );

					job_manager_dropdown_categories( array(
						'taxonomy'     => 'job_listing_tag',
						'hierarchical' => 0,
						'name'         => 'alert_tags',
						'orderby'      => 'name',
						'selected'     => $alert_tags,
						'hide_empty'   => false,
						'placeholder'  => esc_html__( 'Any tag', 'cariera' )
					) );
				?>
			</div>
		</fieldset>
	<?php endif; ?>

	<?php
	if ( get_option( 'job_manager_enable_types' ) && wp_count_terms( 'job_listing_type' ) > 0 ) : ?>
		<fieldset>
			<label for="alert_job_type"><?php esc_html_e( 'Job Type', 'cariera' ); ?></label>
			<div class="field">
				<select name="alert_job_type[]" data-placeholder="<?php esc_html_e( 'Any job type', 'cariera' ); ?>" id="alert_job_type" multiple="multiple" class="cariera-select2">
					<?php
						$terms = get_job_listing_types();
						foreach ( $terms as $term )
							echo '<option value="' . esc_attr( $term->term_id ) . '" ' . selected( in_array( $term->term_id, $alert_job_type ), true, false ) . '>' . esc_html( $term->name ) . '</option>';
					?>
				</select>
			</div>
		</fieldset>
	<?php endif; ?>

	<fieldset>
		<label for="alert_frequency"><?php esc_html_e( 'Email Frequency', 'cariera' ); ?></label>
		<div class="field">
			<select name="alert_frequency" class="cariera-select2" id="alert_frequency">
				<?php foreach ( WP_Job_Manager_Alerts_Notifier::get_alert_schedules() as $key => $schedule ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $alert_frequency, $key ); ?>><?php echo esc_html( $schedule['display'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</fieldset>
	<?php if ( '1' === get_option( 'job_manager_permission_checkbox' ) && 'add_alert' === $_REQUEST[ 'action' ] ) : ?>
		<fieldset class="fieldset-agreement-checkbox">
			<div class="field full-line-checkbox-field required-field checkbox">
				<input type="checkbox" class="input-checkbox" name="alert_permission" id="alert_permission" value="1" required />
				<label for="alert_permission">
					<?php
						echo apply_filters(
							'job_manager_alerts_permission_checkbox_label',
							esc_html__( 'I would like to receive emails for this alert.', 'cariera' )
						);
					?>
				</label>
			</div>
		</fieldset>
	<?php endif; ?>
	<p>
		<?php wp_nonce_field( 'job_manager_alert_actions' ); ?>
		<input type="hidden" name="alert_id" value="<?php echo absint( $alert_id ); ?>" />
		<input type="submit" name="submit-job-alert" class="btn btn-main btn-effect" value="<?php esc_html_e( 'Save alert', 'cariera' ); ?>" />
	</p>
</form>
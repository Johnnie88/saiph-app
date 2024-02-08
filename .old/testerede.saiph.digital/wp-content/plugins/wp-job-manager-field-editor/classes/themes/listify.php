<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Themes_Listify
 *
 * @since 1.8.0
 *
 */
class WP_Job_Manager_Field_Editor_Themes_Listify {

	/**
	 * @var
	 */
	public        $card_actions;
	/**
	 * @var string
	 */
	public static $COMPAT_GIT_COMMIT = "5saC4US/D/Sj4BeHTGlQVQEE";
	/**
	 * @var array
	 */
	public $auto_outputs = array();

	/**
	 * WP_Job_Manager_Field_Editor_Themes_Listify constructor.
	 */
	function __construct() {

		add_filter( 'field_editor_output_options', array( $this, 'auto_output' ), 10, 2 );
		add_filter( 'job_manager_field_editor_admin_skip_fields', array( $this, 'admin_fields' ) );
		add_action( 'admin_notices', array( $this, 'check_directory_fields' ) );
		add_action( 'wp_ajax_jmfe_listify_dfd', array( $this, 'dismiss_directory_fields' ) );
		add_filter( 'job_manager_field_editor_package_remove_old_meta', array($this, 'package_change') );
		add_filter( 'field_editor_auto_output_li_actions', array( $this, 'set_no_li_actions' ) );
		add_filter( 'submit_job_form_start', array( $this, 'custom_css' ) );
		add_filter( 'submit_resume_form_start', array( $this, 'custom_css' ) );
		add_action( 'job_manager_field_editor_field_config_changed', array( $this, 'flush_transients' ) );
		add_filter( 'job_manager_term_multiselect_field_args', array( $this, 'multiselect_add_avoid_class' ), 9999 );

		// Listify 2.0+ Card template output handling
		if( WP_Job_Manager_Field_Editor_Integration::check_theme( 'listify', '2.0.0' ) ){
			// Set output values inside data array
			add_filter( 'listify_get_listing_to_array', array( $this, 'card_template_data' ), 20, 2 );
			// Set template variables in card template
			add_filter( 'field_editor_auto_output_do_auto_output', array( $this, 'output_template_tags' ), 10, 5 );
			add_filter( 'field_editor_auto_output_skip_field_output_no_metadata', array( $this, 'auto_output_check_card_action' ), 10, 5 );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 999999 );
	}

	/**
	 * Check Auto Output Skip Field (when no metadata)
	 *
	 * We use this method to check if the auto output method is for a Listify card, and prevent it from "skipping" the output due to
	 * "no meta" found on the listing, as that is used mainly for optimizations (where output function will still not output if no value),
	 * but this needs to be done to progress forward to the field_editor_auto_output_do_auto_output filter where we output the template
	 * tags to use.
	 *
	 *
	 * @param $meta_key
	 * @param $metadata
	 * @param $action
	 * @param $post_id
	 * @param $fields
	 *
	 * @return bool
	 * @since 1.10.0
	 *
	 */
	public function auto_output_check_card_action( $meta_key, $metadata, $action, $post_id, $fields ) {
		return $this->is_card_action( $action ) ? false : $meta_key;
	}

	/**
	 * Dequeue WPJM Frontend and Chosen CSS
	 *
	 * Jobify has this same exact code located at
	 *
	 * @see listify/inc/integrations/wp-job-manager/class-wp-job-manager.php
	 *
	 * But the problem is that code is ran at the standard priority of 10, whereas WPJM <= 1.31.3 enqueues it at the same
	 * priority, and as such, I have to add this code to run at later priority to make sure these styles are actually dequeued
	 *
	 * @since 1.8.9
	 *
	 */
	public function wp_enqueue_scripts() {
		wp_dequeue_style( 'wp-job-manager-frontend' );
		wp_dequeue_style( 'chosen' );
	}

	/**
	 * Add Class to Avoid Wrapping Multiselect in <span>
	 *
	 * Listify and Jobify both use javascript to wrap any select elements in a <span> wrapper, causing display issues
	 * when we remove an "avoid" class, so this filter adds a class from the javascript that will avoid adding the span wrapper.
	 *
	 * @see wp-content/themes/listify/js/source/app.js
	 *
	 * @since 1.8.5
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public function multiselect_add_avoid_class( $args ){
		$classes = isset( $args['class'] ) ? $args['class'] : '';

		// Empty value will cause core to automatically add job-manager-category-dropdown, which is already in the list of classes to "avoid"
		if( empty( $classes ) || strpos( $classes, 'job-manager-category-dropdown' ) !== false ){
			return $args;
		}

		// It does have dynamic tax class, let's add a Listify one to skip wrapping in <span> element
		if( strpos( $classes, 'jmfe-dynamic-tax' ) !== false && strpos( $classes, 'feedFormField' ) === false ){
			$args['class'] = $classes . ' feedFormField';
		}

		return $args;
	}

	/**
	 * Remove Listify transients when field config is updated
	 *
	 * @since 1.8.0
	 */
	public function flush_transients( $that ){
		delete_transient( 'wpjm_field_editor_listify_auto_outputs' );
	}

	/**
	 * Output Card Action Template Values
	 *
	 * Listify 2.0+ uses templates for cards, and as such, the action is only called once initially when generating
	 * the template to use.  This method is called when that template is being built, and instead of outputting the
	 * value like normal, we output the template variable and set the associated actions in a transient, for when the
	 * `card_template_data` method is called, to prevent excessive queries and only set data values for templates as
	 * required (to prevent having to value for every single card action)
	 *
	 *
	 * @since 1.7.4
	 *
	 * @param $do_output
	 * @param $action
	 * @param $meta_key
	 * @param $config
	 * @param $fields
	 *
	 * @return bool
	 */
	public function output_template_tags( $do_output, $action, $meta_key, $config, $fields ){

		// Only output if the action is a card specific action (requires using templates)
		if( $this->is_card_action( $action ) ){

			// Possible that previous pull from transient returned false, set back to array to rebuild
			if( $this->auto_outputs === false ){
				$this->auto_outputs = array();
			}

			/**
			 * Echo out template handling
			 *
			 * Because this method is called by auto output handling @see WP_Job_Manager_Field_Editor_Auto_Output->do_output() we don't need
			 * to worry about priority, as that is already sorted before this filter is even called.
			 */
			echo '<# if ( data.feoutput && data.feoutput.' . $meta_key . ' ) { #>';
			echo '{{{data.feoutput.' . $meta_key . '}}}';
			echo '<# } #>';

			/**
			 * If configuration not already set for this meta key, set it and update the transient.
			 * $this->auto_outputs should only be empty on first initial action call, so no need to pull value from transient and merge.
			 */
			if ( ! array_key_exists( $meta_key, $this->auto_outputs ) ) {
				$this->auto_outputs[ $meta_key ] = $config;
				set_transient( 'wpjm_field_editor_listify_auto_outputs', $this->auto_outputs );
			}

			// Return false to prevent default output
			return false;
		}

		// Normally returned should be true
		return $do_output;
	}

	/**
	 * Add Auto Output values to data array
	 *
	 * Listify 2.0.0+ now uses templates for generating the cards for listings, and as such, we have
	 * to manually add the data/value to output into the data array, which will then be used when replacing
	 * the template vars {{{data.item}}}
	 *
	 *
	 * @since 1.7.4
	 *
	 * @param array $data
	 * @param WP_Post $listing
	 *
	 * @return array
	 */
	function card_template_data( $data, $listing ){

		// If auto outputs equal to false, means was set by pulling transient without a value
		if( $this->auto_outputs === false ){
			return $data;
		}

		// If values not already set in object, means should be first listing in query (empty array)
		if( empty( $this->auto_outputs ) ){

			/**
			 * Attempt to pull from transient, should only be required on first initial listing called, each sequential listing $this->auto_outputs
			 * should already be set. If transient value is false, means nothing is set and no card actions require auto output.
			 */
			if( false === ( $this->auto_outputs = get_transient( 'wpjm_field_editor_listify_auto_outputs' ) ) ){
				return $data;
			}
		}

		// Make sure `feoutput` key is set in array
		if( ! array_key_exists( 'feoutput', $data ) ){
			$data['feoutput'] = array();
		}

		// Loop through each auto output, and add the output to the data array
		foreach( (array) $this->auto_outputs as $meta_key => $config ){

			$config = apply_filters( "field_editor_auto_output_config_{$config['meta_key']}", $config );
			ob_start();
			the_custom_field( $meta_key, $data['id'], $config );
			$data['feoutput'][ $meta_key ] = ob_get_clean();
		}

		return $data;
	}

	/**
	 * Output Custom CSS for Submit Page
	 *
	 *
	 * @since 1.7.0
	 *
	 */
	function custom_css(){
		echo '<style>.job-manager-term-checklist ul.children > li { list-style: none;width: 100%; } .jmfe-logic-hide { display: none !important; } .job-manager-form > fieldset > div.field > span.select { width: 100%; display: block; } .dynamic-single-select-wrapper::before { display: none !important; } </style>';
	}

	/**
	 * Return empty array for <li> auto output actions
	 *
	 * Listify customizes the templates from the core ones, and removes the <ul> from the default
	 * core template hooks.  Because of this, we need to return an empty array in this filter to
	 * prevent auto output from wrapping the output in <li> elements.
	 *
	 *
	 * @since 1.6.3
	 *
	 * @param $actions
	 *
	 * @return array
	 */
	function set_no_li_actions( $actions ){
		return array();
	}

	/**
	 * Package Upgraded/Downgraded
	 *
	 *
	 * @since 1.5.0
	 *
	 * @param $metakeys
	 *
	 * @return mixed
	 */
	function package_change( $metakeys ){

		$metakeys['gallery_images'] = 'gallery';

		return $metakeys;
	}

	/**
	 * Fields to skip output in admin section
	 *
	 *
	 * @since 1.5.0
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function admin_fields( $fields ){

		// Prevent output of gallery_images in admin
		$fields[] = 'gallery_images';

		// Search for company_logo in skip fields
		$key = array_search( 'company_logo', $fields );

		// Remove company_logo so it shows in admin section (only for Listify)
		if ( $key !== FALSE ) unset( $fields[ $key ] );

		return $fields;

	}

	/**
	 * Handle dismiss directory fields notice
	 *
	 *
	 * @since 1.5.0
	 *
	 */
	function dismiss_directory_fields(){
		check_ajax_referer( 'jmfe-listify-dfd', 'nonce' );
		update_option( 'jmfe_listify_directory_fields_notice', true );
		die;
	}

	/**
	 * Check if directory fields are enabled
	 *
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	function check_directory_fields(){

		if( get_theme_mod( 'custom-submission', true ) || get_option( 'jmfe_listify_directory_fields_notice' ) ) return false;
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function ( $ ) {
				$('.jmfe-listify-dfd.notice.is-dismissible' ).on('click', '.notice-dismiss', function(e){
					$.post( ajaxurl, {
						'action': 'jmfe_listify_dfd',
						'nonce' : '<?php echo wp_create_nonce( "jmfe-listify-dfd" ); ?>',
					}, function () {} );
				});
			} );
		</script>
		<div class="jmfe-listify-dfd notice is-dismissible update-nag">
            <?php echo sprintf(__( 'When using <em>WP Job Manager Field Editor</em> with the <em>Listify</em> theme it is <strong>strongly</strong> recommended that you use/check/enable the <a href="%s" target="_blank">Directory Submission Fields</a>', 'wp-job-manager-field-editor' ), 'http://listify.astoundify.com/article/238-enable-job-manager-submission-fields'); ?>
        </div>
		<?php
	}

	/**
	 * Listify Theme custom action output areas
	 *
	 * Requires Listify 1.0.2 or newer
	 *
	 * @since @@since
	 *
	 * @param $current_options
	 * @param $type
	 *
	 * @return array|bool
	 */
	function auto_output( $current_options, $type ) {

		if( $type === 'company' ) $type = "job";
		if( $type === 'resume_fields' ) $type = "resume";

		$field_groups = ! empty( $type ) ? array( $type ) : array( 'job', 'resume' );

		$theme_version = WP_Job_Manager_Field_Editor_Integration::check_theme( 'listify', '1.0.2', 'version' );
		if( ! $theme_version ) {
			return $current_options;
		}

		$listify_options_job = array(
				'1.0.2' => array(
						'job_listing_listify_list_page'                    => array(
							'label' => '---' . __( "Listify Listing List", 'wp-job-manager-field-editor' ),
							'listify_content_job_listing_header_before' => __( 'List Before Header', 'wp-job-manager-field-editor' ),
							'listify_content_job_listing_meta'          => __( 'List Meta', 'wp-job-manager-field-editor' ),
							'listify_content_job_listing_header_after'  => __( 'List After Header', 'wp-job-manager-field-editor' ),
							'listify_content_job_listing_footer'        => __( 'List Footer', 'wp-job-manager-field-editor' ),
						),
						'single_job_listing_listify'                       => array(
							'label'									=> '---' . __( 'Listify Single Listing', 'wp-job-manager-field-editor' ),
							'listify_single_job_listing_meta'       => __( 'Single Listing Meta', 'wp-job-manager-field-editor' ),
							'listify_single_job_listing_actions'    => __( 'Single Listing Actions', 'wp-job-manager-field-editor' ),
							'single_job_listing_below_location_map' => __( 'Single Listing Below Location Map', 'wp-job-manager-field-editor' ),
						),
						'single_job_listing_listify_widgets'               => array(
							'label' => '---' . __( 'Listify Theme Widgets', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_map_before'            => __( 'Single Listing Top of Map Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_map_after'             => __( 'Single Listing Bottom of Map Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_hours_before'          => __( 'Single Listing Top of Hours Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_hours_after'           => __( 'Single Listing Bottom of Hours Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_author_after'          => __( 'Single Listing Bottom of Author Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_content_before'        => __( 'Single Listing Top of Main Content Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_content_after'         => __( 'Single Listing Bottom of Main Content Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_gallery_slider_before' => __( 'Single Listing Top of Gallery Slider Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_gallery_slider_after'  => __( 'Single Listing Bottom of Gallery Slider Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_gallery_before' 	   => __( 'Single Listing Top of Gallery Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_gallery_after'         => __( 'Single Listing Bottom of Gallery Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_tags_before'           => __( 'Single Listing Top of Tags Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_tags_after'            => __( 'Single Listing Bottom of Tags Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_video_before'          => __( 'Single Listing Top of Video Widget', 'wp-job-manager-field-editor' ),
							'listify_widget_job_listing_video_after'           => __( 'Single Listing Bottom of Video Widget', 'wp-job-manager-field-editor' ),
						),
				),
				'1.4.0' => array(
						'job_listing_listify_list_page' => array(
							'listify_content_job_listing_before'       => __( 'List Before', 'wp-job-manager-field-editor' ),
							'listify_content_job_listing_after'        => __( 'List After', 'wp-job-manager-field-editor' ),
							'listify_content_job_listing_header_start' => __( 'List Header Start', 'wp-job-manager-field-editor' ),
							'listify_content_job_listing_header_end'   => __( 'List Header End', 'wp-job-manager-field-editor' ),
						),
						'single_job_listing_listify' => array(
							'listify_single_job_listing_cover_start' => __( 'Single Listing Cover Start', 'wp-job-manager-field-editor' ),
							'listify_single_job_listing_cover_end'   => __( 'Single Listing Cover End', 'wp-job-manager-field-editor' ),
						),
				)
		);

		$build_options = array();

		foreach( $field_groups as $group ){

			if( ! isset( ${"listify_options_$group"} ) ) continue;

			foreach( ${"listify_options_$group"} as $version => $options ) {

				if( version_compare( $theme_version, $version, 'ge' ) ) {
					$build_options = array_merge_recursive( $build_options, $options );
				}

			}
		}

		// Loop through all built options (separated by groups) and rebuild non multi-dimensional array
		foreach( $build_options as $option_group => $option_options ){
			$current_options[ $option_group ] = $option_options[ 'label' ];
			unset( $option_options ['label'] );
			$current_options += $option_options;
		}

		return $current_options;

	}

	/**
	 * Return Listify 2.0+ Card Actions
	 *
	 *
	 * @since 1.7.4
	 *
	 * @return mixed|void
	 */
	public function get_card_actions(){

		if( ! empty( $this->card_actions ) ){
			return $this->card_actions;
		}

		$this->card_actions = apply_filters( 'field_editor_themes_listify_get_card_actions', array(
			'listify_listing_card_before',
			'listify_content_job_listing_before',
			'listify_content_job_listing_header_before',
			'listify_content_job_listing_header_start',
			'listify_content_job_listing_meta',
			'listify_content_job_listing_header_end',
			'listify_content_job_listing_header_after',
			'listify_content_job_listing_footer',
			'listify_content_job_listing_after',
			'listify_listing_card_after'
		));

		return $this->card_actions;
	}

	/**
	 * Check if specific action is a Listify 2.x+ Card Action
	 *
	 *
	 * @since 1.7.4
	 *
	 * @param $action
	 *
	 * @return bool
	 */
	public function is_card_action( $action ){

		if( in_array( $action, $this->get_card_actions()) ){
			return true;
		}

		return false;
	}
}
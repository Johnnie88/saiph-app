<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Admin_JS
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Admin_JS {

	private $hooks;

	/**
	 * WP_Job_Manager_Field_Editor_Admin_JS constructor.
	 */
	function __construct() {

		add_action( 'admin_enqueue_scripts', array($this, 'build_conf'), 100 );

	}

	/**
	 * Build All Configuration to Convert To JS
	 *
	 *
	 * @since 1.4.5
	 *
	 */
	function build_conf(){
		$auto_out_any_origin = get_option( 'jmfe_output_enable_auto_output_all_origin', false );

		$conf = apply_filters( 'job_manager_field_editor_js_conf', array(
				'meta_keys'  => $this->meta_keys(),
				'outputs'    => $this->outputs(),
				'types'      => $this->types(),
				'checkbox'   => $this->checkbox(),
				'query_vars' => $this->query_vars(),
//				'multi_types' => array( 'file', 'multiselect', 'term-multiselect', 'term-checklist' ),
				'output_all_origin' => empty( $auto_out_any_origin ) ? 0 : 1
			)
		);

		wp_localize_script( 'jmfe-scripts', 'jmfephpconf', $conf );
	}

	/**
	 * Return Meta Key Configuration
	 *
	 *
	 * @since 1.4.5
	 *
	 * @return mixed|void
	 */
	function meta_keys(){

		return apply_filters( 'job_manager_field_editor_js_conf_meta_keys', array(
				"resume_category"      => array(
					"type_disabled_by" => array('select'),
					"disable_types"    => array(
						'text',
						'email',
						'url',
						'tel',
						'textarea',
						'wp-editor',
						'select',
						'file',
						'password',
						'multiselect',
						'radio',
						'checkbox',
						'date',
						'phone',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime',
						'autocomplete',
						'checklist'
					),
					"taxonomy"         => 'resume_category&post_type=resume',
					"not_required"     => array('options'),
				),
				"resume_skills"        => array(
					"type_disabled_by" => array('select'),
					"disable_types"    => array(
						'textarea',
						'wp-editor',
						'select',
						'file',
						'password',
						'multiselect',
						'radio',
						'checkbox',
						'date',
						'phone',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
					    'fpdate',
					    'fptime',
						'autocomplete',
						'checklist'
					),
					"taxonomy"         => 'resume_skill&post_type=resume',
					"not_required"     => array('options'),
				),
				"job_region"           => array(
					"type_disabled_by" => array('select'),
					"disable_types"    => array(
						'text',
						'email',
						'url',
						'tel',
						'textarea',
						'wp-editor',
						'select',
						'file',
						'password',
						'multiselect',
						'radio',
						'checkbox',
						'date',
						'phone',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime',
						'autocomplete',
						'checklist'
					),
					"taxonomy"         => 'job_listing_region&post_type=job_listing',
					"not_required"     => array('options'),
					"disable_field_notice" => __( 'Make sure you disable the Regions plugin (if you are using it) to prevent issues when disabling this field.', 'wp-job-manager-field-editor' )
				),
				"job_category"         => array(
					"type_disabled_by" => array('select'),
					"disable_types"    => array(
						'text',
						'email',
						'url',
						'tel',
						'textarea',
						'wp-editor',
						'select',
						'file',
						'password',
						'job-category',
						'multiselect',
						'radio',
						'checkbox',
						'date',
						'phone',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime',
						'autocomplete',
						'checklist'
					),
					"taxonomy"         => 'job_listing_category&post_type=job_listing',
					"not_required"     => array('options'),
				),
				"job_type"             => array(
					"type_disabled_by" => array('select'),
					"disable_types"    => array(
						'text',
						'email',
						'url',
						'tel',
						'textarea',
						'wp-editor',
						'select',
						'file',
						'password',
						'multiselect',
						'radio',
						'checkbox',
						'date',
						'phone',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime',
						'autocomplete',
						'checklist'
					),
					"taxonomy"         => 'job_listing_type&post_type=job_listing',
					"not_required"     => array('options'),
				),
				"job_tags"             => array(
					"disable_types" => array(
						'textarea',
						'wp-editor',
						'select',
						'file',
						'password',
						'radio',
						'date',
						'phone',
						'checkbox',
						'multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime',
						'url',
						'email',
						'tel',
						'autocomplete',
						'checklist'
					),
					"taxonomy"      => 'job_listing_tag&post_type=job_listing',
				),
				"job_title"            => array(
					"disable_fields" => array('required_0', 'admin_only_0'),
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'date',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime'
					),
					'hidden_fields'  => array( 'show_in_rest' ),
					"hidden_tabs"    => array('packages'),
					"disable_field_notice" => __( 'This field is used for the title of the listing, and can not be disabled, as it is required by the core WP Job Manager plugin to function correctly.', 'wp-job-manager-field-editor' )
				),
				"job_location" => array(
					"disable_types" => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'date',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime'
					),
					"disable_field_notice" => __( 'Disabling this field will disable Geo location data, and could potentially cause problems, do so at your own risk!', 'wp-job-manager-field-editor' ),
					'hidden_fields' => array( 'show_in_rest' )
				),
				"job_description" => array(
					"disable_field_notice" => __( 'Disabling this field is NOT recommended as it is the main post content! You have been WARNED! DISABLE AT YOUR OWN RISK!', 'wp-job-manager-field-editor' )
				),
				"candidate_name"       => array(
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'date',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime'
					),
					"disable_fields" => array('required_0', 'admin_only_0'),
					"hidden_tabs"    => array('packages'),
					"disable_field_notice" => __( 'This field is used for the title of the listing, and can not be disabled, as it is required by the core WP Job Manager plugin to function correctly.', 'wp-job-manager-field-editor' )
				),
				"candidate_title"       => array(
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'date',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime'
					),
					'hidden_fields' => array( 'show_in_rest' )
				),
				"candidate_location"       => array(
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'date',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime'
					),
					'hidden_fields' => array( 'show_in_rest' )
				),
				"candidate_email"       => array(
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'date',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime',
						'tel',
						'url',
						'autocomplete'
					),
					'hidden_fields' => array( 'show_in_rest' )
				),
				"resume_expires"       => array(
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fptime',
						'tel',
						'url',
						'email',
						'radio',
						'checklist',
						'select',
						'textarea',
						'text',
						'autocomplete'
					),
					'disable_fields' => array( 'picker_mode' ),
					'hidden_input' => array(
						'picker_mode' => 'single'
					)
				),
				"job_expires"       => array(
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fptime',
						'tel',
						'url',
						'email',
						'radio',
						'checklist',
						'select',
						'textarea',
						'text',
						'autocomplete'
					),
					'disable_fields' => array( 'picker_mode' ),
					'hidden_input' => array(
						'picker_mode' => 'single'
					)
				),
				"application_deadline"       => array(
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fptime',
						'tel',
						'url',
						'email',
						'radio',
						'checklist',
						'select',
						'textarea',
						'text',
						'autocomplete'
					),
					'disable_fields' => array( 'picker_mode' ),
					'hidden_input' => array(
						'picker_mode' => 'single'
					)
				),
				"job_deadline"       => array(
					"disable_types"  => array(
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'checkbox',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fptime',
						'tel',
						'url',
						'email',
						'radio',
						'checklist',
						'select',
						'textarea',
						'text',
						'autocomplete'
					),
					'disable_fields' => array( 'picker_mode' ),
					'hidden_input' => array(
						'picker_mode' => 'single'
					)
				),
				"allow_linkedin"       => array(
					"disable_types" => array(
						'textarea',
						'select',
						'radio',
						'checklist',
						'wp-editor',
						'file',
						'password',
						'multiselect',
						'text',
						'email',
						'tel',
						'url',
						'date',
						'phone',
						'term-checklist',
						'term-multiselect',
						'term-select',
						'header',
						'html',
						'actionhook',
						'number',
						'range',
						'fpdate',
						'fptime',
						'autocomplete'
					),
					"hidden_fields" => array('required', 'admin_only', 'placeholder'),
					"hidden_tabs"   => array('packages'),
				),
				"candidate_education"  => array( "disable_fields" => array( 'meta_key' ), 'hidden_fields' => array( 'populate_from_get', 'populate_default' ) ),
				"candidate_experience" => array( "disable_fields" => array( 'meta_key' ), 'hidden_fields' => array( 'populate_from_get', 'populate_default' ) ),
				"links"                => array( "disable_fields" => array( 'meta_key' ), 'hidden_fields' => array( 'populate_from_get', 'populate_default' ) ),
				"resume_file"          => array( "disable_fields" => array( 'multiple_0' ), 'hidden_fields' => array( 'show_in_rest' ) ),
				"company_logo"         => array( "disable_fields" => array( 'multiple_0' ) ),
				'company_name'         => array(
					'hidden_fields' => array( 'show_in_rest' )
				),
				'company_website'         => array(
					'hidden_fields' => array( 'show_in_rest' )
				),
				'company_tagline'         => array(
					'hidden_fields' => array( 'show_in_rest' )
				),
				"featured_image"       => array( "disable_fields" => array( 'multiple_0', 'admin_only_0' ) ),
				'application'          => array(
					"disable_field_notice" => __( 'Disable this field and ... The APPLY NOW button will no longer show on listing, and the GeoMyWP plugin will not function correctly!', 'wp-job-manager-field-editor' ),
					'hidden_fields'        => array( 'show_in_rest' )
				),
				'resume_content'       => array( "disable_field_notice" => __( 'Disabling this field is NOT recommended as it is the main post content! You have been WARNED! DISABLE AT YOUR OWN RISK!', 'wp-job-manager-field-editor' ) )
			)
		);

	}

	/**
	 * Return WordPress Public Query Vars
	 *
	 *
	 * @since 1.4.5
	 *
	 * @return mixed|void
	 */
	function query_vars(){
		global $wp;

		if( isset( $wp, $wp->public_query_vars ) ){
			$query_vars = apply_filters( 'query_vars', $wp->public_query_vars );
		} else {
			$query_vars = array('m', 'p', 'posts', 'w', 'cat', 'withcomments', 'withoutcomments', 's', 'search', 'exact', 'sentence', 'calendar', 'page', 'paged', 'more', 'tb', 'pb', 'author', 'order', 'orderby', 'year', 'monthnum', 'day', 'hour', 'minute', 'second', 'name', 'category_name', 'tag', 'feed', 'author_name', 'static', 'pagename', 'page_id', 'error', 'attachment', 'attachment_id', 'subpost', 'subpost_id', 'preview', 'robots', 'taxonomy', 'term', 'cpage', 'post_type', 'embed' );
		}

		return apply_filters( 'job_manager_field_editor_js_conf_query_vars', $query_vars );
	}

	/**
	 * Return Checkbox Configuration
	 *
	 *
	 * @since 1.4.5
	 *
	 * @return mixed|void
	 */
	function checkbox(){

		return apply_filters( 'job_manager_field_editor_js_conf_checkbox', array(
				"checked"   => array(
					"output_enable_fw"  => array(
						"show" => array('output_full_wrap', 'output_fw_atts'),
						"hide" => array()
					),
					"output_enable_vw"  => array(
						"show" => array('output_value_wrap', 'output_vw_atts')
					),
					"output_show_label" => array(
						"show" => array('output_label_wrap', 'output_lw_atts', 'output_disable_multi_label_wrap')
					),
					"multiple" => array(
						"show" => array( 'max_uploads' )
					)
				),
				"unchecked" => array(
					"output_enable_fw"  => array(
						"hide" => array('output_full_wrap', 'output_fw_atts')
					),
					"output_enable_vw"  => array(
						"hide" => array('output_value_wrap', 'output_vw_atts')
					),
					"output_show_label" => array(
						"hide" => array('output_label_wrap', 'output_lw_atts', 'output_disable_multi_label_wrap')
					),
					"multiple" => array(
						"hide" => array( 'max_uploads' )
					)
				)
			)
		);

	}

	/**
	 * Return Outputs Configuration
	 *
	 *
	 * @since 1.4.5
	 *
	 * @return mixed|void
	 */
	function outputs(){

		return apply_filters( 'job_manager_field_editor_js_conf_outputs', array(
				"all" => array(
					"enable_fields"  => array(
						'output_classes',
						'output_multiple',
						'output_show_label',
						'output_enable_fw',
						'output_enable_vw',
						'output_priority'
					),
					"disable_fields" => array(
						'output_caption',
						'output_oembed_height',
						'output_oembed_width',
						'output_check_true',
						'output_check_false',
						'output_video_allowdl',
						'output_video_poster',
						'output_video_height',
						'output_video_width',
						'image_link',
						'output_loop',
						'output_preload',
						'output_autoplay'
					)
				),
				"text"        => array(),
				"email"        => array(),
				"url"        => array(),
				"tel"        => array(),
				"fpcalendar" => array(),
				"link"        => array(
					"enable_fields" => array( 'output_caption' )
				),
				"image"       => array(
					"enable_fields" => array( 'image_link' )
				),
				"oembed"      => array(
					"enable_fields" => array(
						'output_oembed_height',
						'output_oembed_width'
					)
				),
				"checkcustom" => array(
					"enable_fields" => array(
						'output_check_true',
						'output_check_false'
					)
				),
				"video" => array(
					"enable_fields" => array(
						'output_video_allowdl',
						'output_video_poster',
						'output_video_height',
						'output_video_width'
					)
				),
				"video_sc" => array(
					"enable_fields" => array(
						'output_autoplay',
						'output_loop',
						'output_preload',
						'output_video_poster',
						'output_video_height',
						'output_video_width'
					)
				),
				"audio_sc" => array(
					"enable_fields" => array(
						'output_autoplay',
						'output_loop',
						'output_preload'
					)
				)
			)
		);

	}

	/**
	 * Return Field Type Configurations
	 *
	 *
	 * @since 1.4.5
	 *
	 * @return mixed|void
	 */
	function types(){

		return apply_filters( 'job_manager_field_editor_js_conf_types', array(
				"text" => array(
					"show" => array( 'title', 'default', 'maxlength', 'pattern' )
				),
				"hidden" => array(
					"show" => array( 'default' ),
					'hide' => array( 'priority', 'admin_only', 'required', 'placeholder' )
				),
				"email" => array(
					"show" => array( 'title', 'default', 'maxlength', 'pattern' )
				),
				"url" => array(
					"show" => array( 'title', 'default', 'maxlength', 'pattern' )
				),
				"tel" => array(
					"show" => array( 'title', 'default', 'maxlength', 'pattern' )
				),
				"number" => array(
				  "show" => array(
				      'default',
				      'maxlength',
				      'min',
				      'max',
				      'size',
				      'pattern',
				      'step',
				      'title'
				  )
				),
				"range" => array(
				  "show"     => array('default', 'min', 'max', 'step', 'title', 'prepend', 'append'),
				  "hide"     => array('placeholder'),
				  "required" => array('max')
				),
				"checklist" => array(
				  "show"     => array( 'label_over_value', 'max_selected', 'output_csv' ),
				  "hide"     => array('placeholder', 'default'),
				  "show_tabs" => array( 'options' ),
				  "required" => array( 'options' )
				),
				"password"         => array( "show" => array('maxlength') ),
				"textarea"         => array( "show" => array('maxlength', 'default') ),
				"file"             => array(
				  "show_tabs"       => array('options'),
				  "hide"            => array('placeholder'),
				  "show"            => array( 'multiple', 'ajax', 'max_upload_size', 'max_upload_width', 'max_upload_height' ),
				  "option_ph_label" => 'image/jpeg',
				  "option_ph_value" => 'jpg',
				  "option_label"    => __( 'Type', 'wp-job-manager-field-editor' ),
				  "option_value"    => __( 'Extension', 'wp-job-manager-field-editor' ),
				  "option_hide"     => array('option_default', 'option_disabled')
				),
				"select"           => array(
				  "show_tabs"       => array('options'),
				  "show"            => array('label_over_value'),
				  "hide"            => array('placeholder'),
				  "required"        => array('options'),
				  "option_ph_label" => __( 'Caption', 'wp-job-manager-field-editor' ),
				  "option_ph_value" => __( 'value', 'wp-job-manager-field-editor' ),
				  "option_label"    => __( 'Label', 'wp-job-manager-field-editor' ),
				  "option_value"    => __( 'Value', 'wp-job-manager-field-editor' )
				),
				"multiselect"      => array(
				  "show_tabs"       => array('options'),
				  "show"            => array( 'max_selected', 'label_over_value', 'output_csv' ),
				  "hide"            => array(),
				  "required"        => array('options'),
				  "option_ph_label" => __( 'Caption', 'wp-job-manager-field-editor' ),
				  "option_ph_value" => __( 'value', 'wp-job-manager-field-editor' ),
				  "option_label"    => __( 'Label', 'wp-job-manager-field-editor' ),
				  "option_value"    => __( 'Value', 'wp-job-manager-field-editor' )
				),
				"radio"            => array(
				  "show_tabs"       => array('options'),
				  "hide"            => array('placeholder'),
				  'show'            => array( 'label_over_value' ),
				  "required"        => array('options'),
				  "option_ph_label" => __( 'Caption', 'wp-job-manager-field-editor' ),
				  "option_ph_value" => __( 'value', 'wp-job-manager-field-editor' ),
				  "option_label"    => __( 'Label', 'wp-job-manager-field-editor' ),
				  "option_value"    => __( 'Value', 'wp-job-manager-field-editor' )
				),
				"wp-editor"        => array("hide" => array('placeholder'), "show" => array( 'default' ) ),
				"term-checklist"   => array(
				  "show"     => array('taxonomy', 'default', 'max_selected', 'output_csv'),
				  "hide"     => array('placeholder', 'show_in_rest'),
				  "required" => array('taxonomy')
				),
				"term-multiselect" => array(
					"show"     => array('taxonomy', 'default', 'max_selected', 'tax_show_child', 'tax_exclude_terms', 'output_csv'),
					"hide"     => array( 'show_in_rest' ),
					"required" => array('taxonomy')
				),
				"term-select" => array(
					"show"     => array('taxonomy', 'default', 'tax_show_child', 'tax_exclude_terms'),
					"hide"     => array( 'show_in_rest' ),
					"required" => array('taxonomy')
				),
				"header" => array(
					'show'      => array('hide_in_admin'),
					"hide"      => array('placeholder', 'required', 'admin_only', 'show_in_rest'),
					"hide_tabs" => array( 'populate')
				),
				"html" => array(
					'show'      => array( 'hide_in_admin' ),
					"hide"      => array('placeholder', 'required', 'show_in_rest'),
					"hide_tabs" => array( 'populate' )
				),
				"actionhook" => array(
					'show'      => array('hide_in_admin'),
					"hide"      => array('placeholder', 'required'),
					"hide_tabs" => array('populate')
				),
				"checkbox" => array(
					"hide"      => array('placeholder')
				),
		        'fpdate' => array(
		        	'show' => array( 'default', 'picker_mode', 'picker_min_date', 'picker_max_date' ),
		        ),
		        'fptime' => array(
		        	'show' => array( 'picker_increment' ),
		        ),
				'autocomplete' => array(
					'show' => array( 'title', 'default', 'maxlength', 'pattern' )
				)
			)
		);

	}
}
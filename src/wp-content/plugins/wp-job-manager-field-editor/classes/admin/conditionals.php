<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Admin_Conditionals
 *
 * @since 1.7.10
 *
 */
class WP_Job_Manager_Field_Editor_Admin_Conditionals {

	/**
	 * @var \WP_Job_Manager_Field_Editor_Admin
	 */
	public $admin;

	/**
	 * @var string Slug based on post type (should be either 'job' or 'resume')
	 */
	public $slug;

	/**
	 * @var array Comparisons used for conditional logic
	 */
	public $comparisons;

	/**
	 * @var array Checks or "if" statements/meta keys used for logic
	 */
	public $checks;

	/**
	 * WP_Job_Manager_Field_Editor_Admin_Conditionals constructor.
	 *
	 * @param $admin \WP_Job_Manager_Field_Editor_Admin
	 */
	public function __construct( $admin ) {
		$this->admin = $admin;
		$this->slug = $admin->post_type === 'resume' ? 'resume' : 'job';

		wp_enqueue_script( 'jmfe-semantic-ui' );
		wp_enqueue_style( 'jmfe-semantic-ui' );
		wp_enqueue_script( 'jmfe-admin-conditionals' );

		wp_localize_script( 'jmfe-admin-conditionals', 'jmfe_conditional_logic', $this->get_groups() );

		$meta_keys = $this->get_meta_keys();

		wp_localize_script( 'jmfe-admin-conditionals', 'jmfe_meta_keys', $meta_keys );
		wp_localize_script( 'jmfe-admin-conditionals', 'jmfe_checks', $this->get_checks() );

		$this->output();
	}

	/**
	 * Get all frontend logic meta keys
	 *
	 *
	 * @since 1.8.1
	 *
	 * @return array
	 */
	public function get_meta_keys(){

		$all_meta_keys = $this->get_fields();

		// Initially remvoe all admin only fields from array
		$meta_keys = wp_list_filter( $all_meta_keys, array( 'admin_only' => '1' ), 'NOT' );

		// Enable using all admin only fields
		if ( apply_filters( 'field_editor_conditional_logic_enable_admin_logic_fields', false, $all_meta_keys, $this->slug, $this ) ) {

			// Get ONLY admin only fields
			$admin_only_meta_keys = wp_list_filter( $all_meta_keys, array( 'admin_only' => '1' ), 'AND' );

			// Allow filter to specify only the meta key in the array
			$frontend_output_admin_fields = apply_filters( "field_editor_conditional_logic_custom_value_{$this->slug}_admin_fields", $admin_only_meta_keys, $this );

			// Check if multi-dimensional was not passed back (meaning single array was)
			if( ! empty( $frontend_output_admin_fields ) && ! is_array( current( $frontend_output_admin_fields ) ) ){
				// If single array was passed, ie array( 'admin_metakey_1', 'admin_metakey_2' )
				// We need to flip those to be the keys in the array, so the intersect below works correctly
				$frontend_output_admin_fields = array_flip( $frontend_output_admin_fields );
			}

			// Remove any array entries (meta keys) that are NOT present in the filtered $frontend_output_admin_fields
			$admin_only_meta_keys = array_intersect_key( $admin_only_meta_keys, $frontend_output_admin_fields );

			// Allow filtering on all admin only fields (to only include specific ones)
			$admin_only_meta_keys = apply_filters( 'field_editor_conditional_logic_admin_only_meta_keys', $admin_only_meta_keys, $this->slug, $this );

			// Merge array without admin only, with filtered admin only fields
			$meta_keys = array_merge( $meta_keys, $admin_only_meta_keys );
		}

		return apply_filters( 'field_editor_conditional_logic_get_meta_keys', $meta_keys, $all_meta_keys, $this->slug, $this );;
	}

	/**
	 * Checks or "IF" Values/Keys used for Logic
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return array
	 */
	public function get_checks(){

		if( ! empty( $this->checks ) ){
			return $this->checks;
		}

		$checks = array();
		$meta_keys = $this->get_meta_keys();

		foreach ( (array) $meta_keys as $meta_key => $config ) {

			$checks[] = array(
				'label'  => "{$meta_key} ({$config['label']})",
				'value' => $meta_key,
				'is_meta_key' => true,
				'config' => $config
			);

		}

		// For now it's just meta keys, but will soon add other "IF" checks to use
		$this->checks = apply_filters( 'field_editor_condtionals_get_checks', $checks, $this );

		return $this->checks;
	}

	/**
	 * Get Semantic UI Formatted Checks
	 *
	 * DEPRECATED 1.8.1
	 *
	 * @since 1.7.10
	 *
	 */
	public function get_ui_checks(){

		$ui_checks = array();

		foreach( $this->get_checks() as $check => $config ){

			$ui_checks[] = array(
				'name'  => "{$config['label']} ({$check})",
				'value' => $check,
				//				'selected' => true
			);

		}

		$ui_checks = apply_filters( 'field_editor_conditionals_get_ui_checks', $ui_checks, $this );

		return $ui_checks;
	}

	/**
	 * Is Job Logic?
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return bool
	 */
	public function is_job(){

		return $this->slug === 'job';

	}

	/**
	 * Is Resume Logic?
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return bool
	 */
	public function is_resume(){

		return $this->slug === 'resume';

	}

	/**
	 * Get Logic Comparisons
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return array
	 */
	public function get_comparisons(){

		if( ! empty( $this->comparisons ) ){
			return $this->comparisons;
		}

		$this->comparisons = apply_filters( 'field_editor_conditionals_comparisons', array(
			'is'           => array(
				'label' => __( 'is', 'wp-job-manager-field-editor' )
			),
			'is_not'       => array(
				'label' => __( 'is not', 'wp-job-manager-field-editor' ),
			),
			'greater_than' => array(
				'label' => __( 'is greater than', 'wp-job-manager-field-editor' )
			),
			'less_than'    => array(
				'label' => __( 'is less than', 'wp-job-manager-field-editor' )
			),
			'starts_with'   => array(
				'label' => __( 'starts with', 'wp-job-manager-field-editor' ),
			),
			'starts_with_not'   => array(
				'label' => __( 'does not start with', 'wp-job-manager-field-editor' ),
			),
			'ends_with'    => array(
				'label' => __( 'ends with', 'wp-job-manager-field-editor' )
			),
			'ends_with_not'    => array(
				'label' => __( 'does not end with', 'wp-job-manager-field-editor' )
			),
			'contains'     => array(
				'label' => __( 'contains', 'wp-job-manager-field-editor' )
			),
			'contains_not'     => array(
				'label' => __( 'does not contain', 'wp-job-manager-field-editor' )
			),

		), $this );

		return $this->comparisons;
	}

	/**
	 * Get Groups Conditional Logic
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return mixed|void
	 */
	public function get_groups(){

		//delete_option( "field_editor_{$this->admin->post_type}_conditional_logic" );

		$conditional_logic = get_option( "field_editor_{$this->admin->post_type}_conditional_logic", array() );

		return apply_filters( "field_editor_{$this->admin->post_type}_conditional_logic", $conditional_logic, $this );
	}

	/**
	 * Output All Conditional Content
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function output(){

		$label = $this->is_job() ? WP_Job_Manager_Field_Editor::get_job_post_label() : __( 'Resume', 'wp-job-manager-field-editor' );

		$right_menus = apply_filters( 'field_editor_conditionals_right_menu_items', array(
				array(
					'class' => 'logic_add_section',
					'icon' => 'add',
					'label' => __( 'Add Conditional Section', 'wp-job-manager-field-editor' )
				)
		));
		?>

		<div id="conditionals_wrap" class="wrap">

			<h1><span id="conditionals_title"><?php printf( __( '%s Conditional Fields', 'wp-job-manager-field-editor' ), $label ); ?></span>
				<div class="ui large orange label" style="vertical-align: text-top;"><?php _e( 'BETA', 'wp-job-manager-field-editor' ); ?></div>
			</h1>

			<div class="ui stackable two column grid">

				<div class="row">

					<div class="four wide column">
						<button id="add_group" class="ui fluid basic button"><i class="add circle icon"></i><?php _e( 'Conditional Group', 'wp-job-manager-field-editor' ); ?></button>

						<div id="groups_list" class="ui fluid pointing vertical menu">
							<?php //tmpl-groups-list ?>
						</div>
					</div>

					<div class="twelve wide column">

						<div id="success_message" class="ui icon success message hidden"><i class="checkmark icon"></i><div class="content"><div class="header"><?php _e( 'Success', 'wp-job-manager-field-editor' ); ?></div><p id="success_content"></p></div><i id="success_close" class="con_message close icon"></i></div>
						<div id="error_message" class="ui icon error message hidden"><i class="checkmark icon"></i><div class="content"><div class="header"><?php _e( 'Error!', 'wp-job-manager-field-editor' ); ?></div><p id="error_content"></p></div><i id="error_close" class="con_message close icon"></i></div>

						<form action="#" id="conditionals_form" class="conditionals_form ui form">

							<div id="group_config">
								<?php // tmpl-group-config ?>
							</div>

							<div id="group_logic" style="margin-top: 1rem;">

								<div id="logic_placeholder" class="ui disabled padded center aligned segment" style="margin-top: 15px;"><?php _e( 'Select a group, or save group configuration to show conditional logic.', 'wp-job-manager-field-editor' ); ?></div>

								<div id="logic_menu" style="display: none;">

									<h3 class="ui center aligned header">
										<div class="content">
											<?php _e( 'Group Conditional Logic', 'wp-job-manager-field-editor' ); ?>
											<div class="sub header"><?php _e( 'Below you will define the conditional logic that will trigger the configuration above.', 'wp-job-manager-field-editor' ); ?></div>
										</div>
									</h3>

									<div class="ui pointing menu">
										<a class="active item">
											<?php _e( 'Conditional Logic', 'wp-job-manager-field-editor' ); ?>
										</a>

										<div class="right menu">

											<?php
												foreach( $right_menus as $right_menu ){
													echo '<a class="item ' . esc_attr( $right_menu['class'] ) . '"><i class="' . esc_attr( $right_menu['icon'] ) . ' icon"></i>' . $right_menu['label'] . '</a>';
												}
											?>

										</div>
									</div>
								</div>

								<div id="logic_content" style="margin-top: 1rem;">
									<?php // $this->conditions(); ?>
								</div>

								<div id="logic_footer" class="ui basic segment" style="display: none;">
									<div class="ui float right inverted blue circular button icon large logic_add_section"><i class="plus icon"></i></div>
								</div>

							</div>

						</form>

					</div>

				</div>
			</div>
		</div>
		<?php

		$this->tmpl_group_config();
		$this->tmpl_groups_list();
		$this->tmpl_logic_row();
		$this->tmpl_logic_sections();
		$this->tmpl_logic_add_section();
		$this->modals();
	}

	/**
	 * Output Group Config Template
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function tmpl_group_config(){

		$group_types = WP_Job_Manager_Field_Editor_Conditionals::get_group_types();

		?>
			<script type="text/html" id="tmpl-group-config">

				<div class="ui segments" style="margin-top: 0px;">

					<div class="ui secondary grey clearing segment">
						<h3 class="ui left floated header">
							<?php _e( 'Conditional Group', 'wp-job-manager-field-editor' ); ?>
							<div class="sub header">
								<?php _e( 'Conditional groups are used for setting up conditional statements for groups of fields.', 'wp-job-manager-field-editor' ); ?>
							</div>
						</h3>
					</div>

					<div id="group_config_segment" class="ui attached fluid clearing segment">

						<form action="#" id="group_config_form" class="group_config_form ui form">

							<?php wp_nonce_field( 'jmfe_conditionals_nonce' ); ?>
							<input type="hidden" class="group_slug_hidden_input" id="group_slug" name="group_slug" value="{{group.slug}}">

							<?php //need to have two group_slug fields, so we can know which key to use in ajax request (since slug needs to be set in array) ?>
							<input type="hidden" class="group_slug_hidden_form_input" name="{{group.slug}}[slug]" value="{{group.slug}}">
							<input type="hidden" class="post_type_hidden_input" name="post_type" value="<?php echo $this->admin->post_type; ?>">

						    <div id="group_config_error" class="ui error message"></div>

							<div class="three fields">

								<div class="ui ten wide field required">
									<label><?php _e( 'Group Name:', 'wp-job-manager-field-editor' ); ?></label>
									<div class="ui left icon input">
										<input id="group_name_input" class="group_name_input" type="text" name="{{group.slug}}[name]" placeholder="<?php _e( 'Group Name', 'wp-job-manager-field-editor' ); ?>" value="{{group.name}}" data-validate="group_name_input" required>
										<i class="tag icon"></i>
									</div>
								</div>

								<div class="ui three wide field required">
									<label><?php _e('Group Type:', 'wp-job-manager-field-editor'); ?></label>
									<div id="group_type" class="ui selection icon fluid dropdown">
										<input type="hidden" class="group_type_hidden_input" name="{{group.slug}}[type]" value="{{group.type}}" data-validate="group">
										<i class="dropdown icon"></i>
										<div class="default text"><?php _e('Select a type', 'wp-job-manager-field-editor'); ?></div>
										<div class="menu">
											<?php
											foreach( $group_types as $group_type => $tcfg ){
												echo "<div class=\"item\" data-value=\"{$group_type}\"><i class=\"icon {$tcfg['icon']}\"></i>{$tcfg['label']}</div>";
											}
											?>
										</div>
									</div>
								</div>
								<div class="ui three wide field required">
									<label><?php _e('Status:', 'wp-job-manager-field-editor'); ?></label>
									<div id="group_status" class="ui selection icon fluid dropdown">
										<input type="hidden" class="group_status_hidden_input" name="{{group.slug}}[status]" value="{{group.status}}" data-validate="group">
										<i class="dropdown icon"></i>
										<div class="text"></div>
										<div class="menu">
											<div class="item" data-value="enabled"><i class="toggle on icon"></i><?php _e('Enabled', 'wp-job-manager-field-editor'); ?></div>
											<div class="item" data-value="disabled"><i class="toggle off icon"></i><?php _e('Disabled', 'wp-job-manager-field-editor'); ?></div>
										</div>
									</div>
								</div>
							</div>

							<div class="ui field">

								<label><?php _e('Group Applied Fields:', 'wp-job-manager-field-editor'); ?></label>

								<div id="group_fields" class="ui fluid search multiple selection dropdown fluid_auto group_fields_dropdown">
									<input id="group_fields_hidden_input" class="meta_keys_input group_fields_hidden_input" type="hidden" name="{{group.slug}}[fields]" value="" data-validate="checks">
									<i class="dropdown icon"></i>
									<div class="default text"><?php _e( 'Select fields to apply this group conditional to...', 'wp-job-manager-field-editor' ); ?></div>
									<div class="menu">
										{{#each meta_keys}}
											<div class="item" data-value="{{@key}}">{{@key}}</div>
										{{/each}}
									</div>
								</div>

							</div>

							{{#ifNotNewGroup group.slug}}
							<div id="remove_group" class="ui inverted red button"><i class="remove circle icon"></i><?php _e( 'Remove', 'wp-job-manager-field-editor' ); ?></div>
							{{/ifNotNewGroup}}

							<button id="save_group_config" class="ui right floated submit labeled icon blue button"><i class="save icon"></i><?php _e( 'Save Configuration', 'wp-job-manager-field-editor' ); ?></button>

						</form>

					</div>

				</div>

			</script>
		<?php

	}

	/**
	 * Output Groups List Template
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function tmpl_groups_list(){
		?>

		<script type="text/html" id="tmpl-groups-list">
			{{#if groups}}
				{{#each groups}}
					{{#ifDisabled status}}
						<a class="red item show_group_config" data-group="{{@key}}" style="color: red;"><i class="red toggle off icon"></i>{{name}}</a>
					{{else}}
						<a class="blue item show_group_config" data-group="{{@key}}"><i class="icon blue chevron right"></i>{{name}}</a>
					{{/ifDisabled}}
				{{/each}}
			{{else}}
				<div class="item disabled"><?php _e('No groups found.', 'wp-job-manager-field-editor'); ?></div>
			{{/if}}
		</script>

		<?php
	}

	/**
	 * Output Logic Add Section Template
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function tmpl_logic_add_section(){

		?>
		<script type="text/html" id="tmpl-logic-add-section">

			<div class="logic_section_wrap" data-secid="{{secId}}">

				{{#unless no_or}}<div class="ui horizontal section divider logic_row_or"><?php _e( 'OR', 'wp-job-manager-field-editor' ); ?></div>{{/unless}}

				<div class="ui form fluid clearing segment logic_row_form">

					<div class="logic_row_content">
						{{>logicRow this first=no_or}}
					</div>

					<a class="add_logic_row ui right blue inverted floated mini button" data-group="{{group}}" data-secid="{{secId}}"><i class="add circle icon"></i><?php _e( 'AND', 'wp-job-manager-field-editor' ); ?></a>
					<div class="ui bottom left attached label compact"><i class="logic_section_remove link red close circular icon" style="cursor: pointer; margin-left: 0px;"></i></div>

				</div>

			</div>

		</script>
		<?php
	}

	/**
	 * Output Logic Sections Template
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function tmpl_logic_sections(){

		?>
		<script type="text/html" id="tmpl-logic-section">

			{{#each group.logic as |logic secId|}}

				<div class="logic_section_wrap" data-secid="{{secId}}">

					{{#unless @first}}
						<div class="ui horizontal section divider logic_row_or"><?php _e( 'OR', 'wp-job-manager-field-editor' ); ?></div>
					{{/unless}}

					<div class="ui form fluid clearing segment logic_row_form">

						<div class="logic_row_content">
							{{#each logic as |row row_id|}}

								{{>logicRow row rowId=row_id secId=secId checks=../../checks group=../../group.slug first=@first }}

							{{/each}}
						</div>

						<a class="add_logic_row ui right blue inverted floated mini button" data-group="{{../group.slug}}" data-secid="{{secId}}"><i class="add circle icon"></i><?php _e( 'AND', 'wp-job-manager-field-editor' ); ?></a>
						<div class="ui bottom left attached label compact"><i class="logic_section_remove link red close circular icon" style="cursor: pointer; margin-left: 0px;"></i></div>

					</div>

				</div>
			{{/each}}

		</script>
		<?php
	}

	/**
	 * Output Logic Row Template
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function tmpl_logic_row(){

		$comparisons = $this->get_comparisons();

		?>

		<script type="text/html" id="tmpl-logic-row">

			<div class="inline fields logic_row" data-rowid="{{rowId}}">

				<div class="seven wide field">

					<div class="ui right pointing label">{{#if first}}<?php _e( 'IF', 'wp-job-manager-field-editor' ); ?>{{else}}<?php _e( 'AND', 'wp-job-manager-field-editor' ); ?>{{/if}}</div>

					<div class="ui fluid search selection dropdown fluid_auto logic_row_checks meta_keys_dropdown" style="width: 100%;">
						<input class="meta_keys_input logic_row_hidden_input" type="hidden" name="{{group}}[logic][{{secId}}][{{rowId}}][check]" value="{{check}}" data-validate="checks">
						<i class="dropdown icon"></i>
						<div class="default text"><?php _e( 'Select a logic check', 'wp-job-manager-field-editor' ); ?></div>
						<div class="menu">
							{{#each checks}}
								<?php //<div class="item" data-value="{{@key}}">{{@key}} ({{striptags this.label}})</div> ?>
								<div class="item" data-value="{{this.value}}">{{striptags this.label}}</div>
							{{/each}}
						</div>
					</div>

				</div>

				<div class="nine wide field">

					<div class="ui labeled right icon input">

						<div class="ui dropdown label logic_row_comparison">
							<input class="compare_hidden_input" type="hidden" name="{{group}}[logic][{{secId}}][{{rowId}}][compare]" value="{{compare}}" data-validate="comparison">
							<div class="text"><?php _e( 'is', 'wp-job-manager-field-editor' ); ?></div>
							<i class="dropdown icon"></i>
							<div class="menu">
								<?php foreach ( (array) $comparisons as $comparison => $ccfg ): ?>
									<div class="item" data-value="<?php echo $comparison; ?>"><?php echo $ccfg['label']; ?></div>
								<?php endforeach; ?>
							</div>
						</div>

						<input type="text" class="value_logic_input" name="{{group}}[logic][{{secId}}][{{rowId}}][value]" placeholder="<?php _e( 'Value', 'wp-job-manager-field-editor' ); ?>" value="{{value}}" data-validate="value">
						{{#unless first}}<i class="logic_row_remove red remove circle link icon" data-group="{{group}}" data-secid="{{secId}}" data-rowid="{{rowId}}"></i>{{/unless}}

					</div>

				</div>
			</div>

		</script>
		<?php
	}

	/**
	 * Output Modals
	 *
	 *
	 * @since 1.7.10
	 *
	 */
	public function modals(){

		?>

		<div id="confirm_remove_group" class="ui basic modal">
			<div class="ui red icon header">
				<i class="red remove icon"></i>
				<?php _e('Remove Group and Group Logic?', 'wp-job-manager-field-editor'); ?>
			</div>
			<div class="content align center">
				<p><?php _e('Are you sure you want to remove this group, and all associated logic created with it?  Once it\'s removed, it\'s gone forever!', 'wp-job-manager-field-editor'); ?></p>
			</div>
			<div class="actions">
				<div class="ui red basic cancel inverted button"><i class="remove icon"></i><?php _e('No', 'wp-job-manager-field-editor'); ?></div>
				<div class="ui green ok inverted button"><i class="checkmark icon"></i><?php _e('Yes', 'wp-job-manager-field-editor'); ?></div>
			</div>
		</div>

		<?php
	}

	/**
	 * Get Fields Placeholder
	 *
	 *
	 * @since 1.7.10
	 *
	 * @return array
	 */
	public function get_fields(){
		return array();
	}
}
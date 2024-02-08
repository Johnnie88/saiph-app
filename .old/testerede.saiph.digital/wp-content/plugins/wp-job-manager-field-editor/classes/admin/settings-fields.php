<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Job_Manager_Field_Editor_Settings_Fields {

	/**
	 * CheckBox Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function checkbox_field( $a ) {

		$o       = $a[ 'option' ];
		$checked = checked( $a[ 'value' ], 1, FALSE );

		echo "<label><input id=\"{$o['name']}\" type=\"checkbox\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" value=\"1\"  {$a['attributes']} {$checked} /> {$o['cb_label']} </label>";
		$this->description( $o );

		if( ! array_key_exists( 'child_fields', $o ) ) return;
		?>

			<script type="text/javascript">
				jQuery(function( $ ){

					var check_selector = $( '#<?php echo $o[ 'name' ]; ?>' );

					function <?php echo $o['name']; ?>_check_selector( elem ){
						if ( elem.is( ':checked' ) ) {

							<?php
							foreach( $o[ 'child_fields' ] as $child_field ) {
								echo "$('.{$child_field}-row').show();";
							}
							?>

						} else {

							<?php
							foreach( $o[ 'child_fields' ] as $child_field ) {
								echo "$('.{$child_field}-row').hide();";
							}
							?>

						}
					}

					// Check on click
					check_selector.click( function ( e ) {
						<?php echo $o['name']; ?>_check_selector( $(this) );
					} );

					// Check when page is first loaded
					<?php echo $o['name']; ?>_check_selector( check_selector );
				});
			</script>
		<?php
	}

	/**
	 * CheckBoxes Field
	 *
	 *
	 * @since 1.3.6
	 *
	 * @param $a
	 */
	function checkboxes_field( $a ) {

		$o       = $a['option'];

		$boxnum = 0;
		foreach( $o['options'] as $key => $config ){
			$default_checked = isset( $config['std'] ) && $a['value'] === FALSE ? ! empty( $config['std'] ) : false;
			$checked = is_array( $a['value'] ) ? checked( in_array( $key, $a['value'] ), TRUE, FALSE ) : checked( $default_checked, TRUE, FALSE );
			echo "<label style=\"margin-right: 5px;\"><input id=\"{$o['name']}_{$key}\" type=\"checkbox\" class=\"{$a['field_class']}\" name=\"{$o['name']}[]\" value=\"{$key}\"  {$a['attributes']} {$checked} /> {$config['label']} </label>";
			$boxnum++;
		}

		$this->description( $o );

	}

	/**
	 * Dump Debug Data
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function debug_dump_field( $a ) {

		$this->fields()->dump_array( $this->field_data() );

	}

	/**
	 * Default Header
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $args
	 */
	function default_header( $args ) {

	}

	/**
	 * About Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function about_field( $a ){
	?>

		<p><strong>Version:</strong> <?php echo WPJM_FIELD_EDITOR_VERSION; ?></p>
		<p><strong>Author:</strong> Myles McNamara</p>

	<?php
	}

	/**
	 * Support Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function support_field( $a ) {

		?>

		<p>
			Currently the best way to report any issues you are having or get support with this plugin is to submit a support ticket via<br />
			your <a href="https://plugins.smyl.es/" target="_blank">sMyles Plugins</a> account.  This will get you the quickest support possible and will allow me to track any support issues.
			<br /><br />
			You can submit a new support ticket here:<br />
			<a href="https://plugins.smyl.es/support/new/" target="_blank">https://plugins.smyl.es/support/new/</a> <small>( opens in new window )</small>
		</p>
		<br />
		<p>
			You can also view any documentation available for this plugin on my website as well:<br />
			<a href="https://plugins.smyl.es/docs/" target="_blank">https://plugins.smyl.es/docs/</a>
		</p>

	<?php
	}

	/**
	 * Backup Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function backup_field( $a ) {

		$o = $a[ 'option' ];
		$url = get_admin_url();

		echo "<input type=\"hidden\" name=\"content\" value=\"jmfe_custom_fields\" />";
		echo "<input type=\"hidden\" name=\"download\" value=\"true\" />";
		echo "<button formmethod=\"GET\" formaction=\"{$url}export.php\" id=\"{$o['name']}\" name=\"button_submit\" value=\"{$o['action']}\" type=\"submit\" class=\"button {$a['field_class']}\" {$a['attributes']}>{$o['caption']}</button>";
		$this->description( $o );

	}

	/**
	 * Button Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function button_field( $a ) {

		$o = $a[ 'option' ];
		if ( ! $this->field_data() && $o['name'] == "jmfe_remove_all" ){
			_e( 'No fields to remove!  A button will appear here when there is field data you can remove.', 'wp-job-manager-field-editor' );
			return;
		}

		echo "<button id=\"{$o['name']}\" name=\"button_submit\" value=\"{$o['action']}\" type=\"submit\" class=\"button {$a['field_class']}\" {$a['attributes']}>{$o['caption']}</button>";
		$this->description( $o );

	}

	/**
	 * AJAX Button Field
	 *
	 *
	 * @since @@since
	 *
	 * @param $a
	 */
	function ajax_field( $a ) {

		$o = $a[ 'option' ];
		$ajax_nonce = wp_create_nonce( $o['action'] );

		echo "<a id=\"{$o['name']}\" name=\"button_ajax\" href=\"#\" data-action=\"{$o['action']}\" class=\"button {$a['field_class']}\" {$a['attributes']}>{$o['caption']}</a>";
		$this->description( $o );
		echo "<div id=\"{$o['name']}-data\"></div>";
		?>
		<script type="text/javascript">
			jQuery( function ( $ ) {

				var data = {action:'<?php echo $o['action']; ?>', nonce :'<?php echo $ajax_nonce; ?>'};

				$( '#<?php echo $o['name']; ?>' ).click( function( e ){
					var output_data = ''; var data_area = $( '#<?php echo $o[ 'name' ]; ?>-data' );

					$.ajax( ajaxurl,
							{
								type      : 'POST',
								dataType  : 'JSON',
								data      : data,
								beforeSend: function () {
									console.log( 'before send ajax' );
									data_area.html( '<br /><strong><em><?php _e( 'Loading ...', 'wp-job-manager-field-editor' ); ?></em></strong>' );
								},
								error: function ( request, status, error ) {
									if ( request.responseText ) {
										output_data = request.responseText;
										// Check if error response was xDebug to only return summary of error
										var xDebugCheck = $( '.xdebug-error th:first', request.responseText ).html();
										if ( xDebugCheck ) output_data = xDebugCheck;
									} else {
										output_data = '<?php _e( 'Error!', 'wp-job-manager-field-editor' ); ?><br />' + error;
									}
								},
								success   : function ( data ) {
									if ( data === '0' ) {
										output_data = '<?php _e( 'Unknown Error', 'wp-job-manager-field-editor' ); ?>';
									} else if ( data.body ) {
										output_data = data.body;
									}
								},
								complete  : function () {
									console.log( 'ajax complete' );
									data_area.html( output_data );
									$( '#<?php echo $o[ 'name' ]; ?>' ).html( '<?php echo __( 'Refresh', 'wp-job-manager-field-editor' ) . ' ' . $o[ 'caption' ]; ?>' );
								}
							}
					);
					e.preventDefault();
				});

			} );
		</script>
		<?php
	}

	/**
	 * Link Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function link_field( $a ) {

		$o = $a[ 'option' ];

		echo "<a id=\"{$o['name']}\" href=\"{$o['href']}\" class=\"{$a['field_class']}\" {$a['attributes']}>{$o['caption']}</a>";
		$this->description( $o );

	}

	/**
	 * Select Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function select_field( $a ) {

		$o = $a[ 'option' ];

		echo "<select id=\"{$o['name']}\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" {$a['attributes']}>";

		foreach ( $o[ 'options' ] as $key => $name ) {
			$value    = esc_attr( $key );
			$label    = esc_attr( $name );
			$selected = isset($a['value']) ? selected( $a[ 'value' ], $key, FALSE ) : false;

			echo "<option value=\"{$value}\" {$selected}> {$label} </option>";
		}

		echo "</select>";
		$this->description( $o );

	}

	/**
	 * Textarea Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function textarea_field( $a ) {

		$o = $a[ 'option' ];

		echo "<textarea cols=\"50\" rows=\"3\" id=\"{$o['name']}\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" {$a['attributes']}>";
		echo esc_textarea( $o[ 'value' ] );
		echo "</textarea>";
		$this->description( $o );

	}

	/**
	 * Textbox Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $a
	 */
	function textbox_field( $a ) {

		$o = $a[ 'option' ];

		echo "<input id=\"{$o['name']}\" type=\"text\" class=\"{$a['field_class']} regular-text\" name=\"{$o['name']}\" value=\"{$a['value']}\" {$a['placeholder']} {$a['attributes']} />";
		$this->description( $o );

	}

	/**
	 * License Key
	 *
	 *
	 * @since @@since
	 *
	 * @param $a
	 */
	function license_email_field( $a ) {

		$o = $a[ 'option' ];

		//delete_option( 'wp-job-manager-field-editor_email' );

		if( empty( $a['value'] ) ){
			echo "<input id=\"{$o['name']}\" type=\"text\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" value=\"{$a['value']}\" {$a['placeholder']} {$a['attributes']} />";
			$this->description( $o );
		} else {
			echo $a['value'];
		}

	}

	/**
	 * Notice on license tab
	 *
	 *
	 * @since 1.5.0
	 *
	 * @param $a
	 */
	function license_page_notice_field( $a ){

		echo '<strong><em>';
		echo __( 'Please use the', 'wp-job-manager-field-editor' ) . " <a href='" . admin_url( 'index.php?page=smyles-licenses' ) . "' target='_blank'>sMyles Licensing</a> " . __( 'page to activate or deactivate your licenses.', 'wp-job-manager-field-editor' );
		echo '</strong></em>';
		echo '<br /><small>' . __( 'This tab is kept here for compatibility purposes, and as a backup method for activation', 'wp-job-manager-field-editor' ) . '</small>';
	}

	/**
	 * License Key
	 *
	 *
	 * @since @@since
	 *
	 * @param $a
	 */
	function license_key_field( $a ) {

		$o = $a['option'];

		if( empty($a['value']) ) {
			echo "<input id=\"{$o['name']}\" type=\"text\" class=\"{$a['field_class']}\" name=\"{$o['name']}\" value=\"{$a['value']}\" {$a['placeholder']} {$a['attributes']} />";
			$this->description( $o );
		} else {
			$url = get_admin_url();
			echo "<button formmethod=\"GET\" formaction=\"{$url}edit.php?post_type=job_listing&page=field-editor-settings\" id=\"wp-job-manager-field-editor_deactivate_licence\" name=\"wp-job-manager-field-editor_deactivate_licence\" value=\"true\" type=\"submit\" class=\"button {$a['field_class']}\" {$a['attributes']}>" . __('Deactivate License', 'wp-job-manager-field-editor') . "</button>";
		}
	}

	/**
	 * Description Field
	 *
	 *
	 * @since 1.1.9
	 *
	 * @param $o
	 */
	function description( $o ) {

		if ( ! empty( $o[ 'desc' ] ) ) echo "<p class=\"description\">{$o['desc']}</p>";

	}

	/**
	 * Cache Button Field
	 *
	 *
	 * @since 1.1.0
	 *
	 * @param $a
	 */
	function cache_button_field( $a ) {

		$o = $a[ 'option' ];

		$btn_caption = "{$o[ 'caption' ]}";

		$cache_enabled = get_option( 'jmfe_enable_cache' );

		if ( isset( $o[ 'cache_count' ] ) && ! empty( $cache_enabled ) ) {
			$count_method = $o[ 'cache_count' ];

			$cache = WP_Job_Manager_Field_Editor_Transients::get_instance();

			if( method_exists( $cache, $count_method ) ){
				if ( $count_method == 'count' ) {
					$cache_count = $cache->$count_method( false );
				} else {
					$cache_count = $cache->$count_method();
				}

				$btn_caption .= " ({$cache_count})";
			}

		}

		if( ! empty( $cache_enabled ) ){
			echo "<button id=\"{$o['name']}\" name=\"button_submit\" value=\"{$o['action']}\" type=\"submit\" class=\"button {$a['field_class']}\" {$a['attributes']}>{$btn_caption}</button>";
		} else {
			_e( 'Cache Disabled', 'wp-job-manager-field-editor' );
		}

		$this->description( $o );

	}
}
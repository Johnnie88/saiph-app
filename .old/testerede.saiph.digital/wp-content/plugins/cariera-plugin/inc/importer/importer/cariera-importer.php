<?php
/**
 *
 * @package Cariera
 *
 * @since    1.2.4
 * @version  1.5.4
 *
 * ========================
 * CLASS CarieraThemeImporter
 * ========================
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CarieraThemeImporter {

	public $theme_options_file;

	public $widgets;

	public $content_demo;

	public $flag_as_imported = [];

	public $customizer_data;

	public $demo_settings;

	private static $demo_names;

	private static $instance;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 */
	public function __construct() {
		if ( isset( $_POST['demo'] ) && ! empty( $_POST['demo'] ) ) {
			$demo = $_POST['demo'] . '/';
		} else {
			$demo = '';
		}
		self::$instance           = $this;
		$this->demo_files_path    = $this->demo_files_path . $demo;
		$this->theme_options_file = $this->demo_files_path . $this->theme_options_file_name;
		$this->widgets            = $this->demo_files_path . $this->widgets_file_name;
		$this->content_demo       = $this->demo_files_path . $this->content_demo_file_name;
		$this->customizer_data    = $this->demo_files_path . $this->customizer_data_name;
		$this->demo_settings      = $this->get_demo_settings( $demo );
		self::$demo_names         = [
			'main' => 'Cariera Demo',
		];
	}

	/**
	 * [demo_installer description]
	 */
	public function demo_installer() { ?>

		<form id="cariera_import_form" method="post">
			<!-- <input type="hidden" name="demononce" value="<?php // echo wp_create_nonce( 'radium-demo-code' ); ?>" /> -->

			<div class="content">
				<h3><?php esc_html_e( 'Choose the contents', 'cariera' ); ?></h3>

				<ul class="radio-list cariera_demo_content">
					<li class="demo_content" data-value="set_demo_content" class="click">
						<div class="loader"><span class="circle"></span></div>
						<div class="radio-option">
							<span class="checkmark">
								<div class="checkmark_stem"></div>
								<div class="checkmark_kick"></div>
							</span>
						</div>
						<?php esc_html_e( 'Content', 'cariera' ); ?>
					</li>

					<li class="theme_options" data-value="import_theme_options" class="click" style="display: none;">
						<div class="loader"><span class="circle"></span></div>
						<div class="radio-option">
							<span class="checkmark">
								<div class="checkmark_stem"></div>
								<div class="checkmark_kick"></div>
							</span>
						</div>
						<?php esc_html_e( 'Options Panel Settings', 'cariera' ); ?>
					</li>

					<li class="widgets" data-value="import_theme_widgets" class="click">
						<div class="loader"><span class="circle"></span></div>
						<div class="radio-option">
							<span class="checkmark">
								<div class="checkmark_stem"></div>
								<div class="checkmark_kick"></div>
							</span>
						</div>
						<?php esc_html_e( 'Widgets', 'cariera' ); ?>
					</li>

					<li class="slider" data-value="import_slider" class="click">
						<div class="loader"><span class="circle"></span></div>
						<div class="radio-option">
							<span class="checkmark">
								<div class="checkmark_stem"></div>
								<div class="checkmark_kick"></div>
							</span>
						</div>
						<?php esc_html_e( 'Slider Data', 'cariera' ); ?>
					</li>

					<li class="after_import" data-value="after_import" class="click">
						<div class="loader"><span class="circle"></span></div>
						<div class="radio-option">
							<span class="checkmark">
								<div class="checkmark_stem"></div>
								<div class="checkmark_kick"></div>
							</span>
						</div>
						<?php esc_html_e( 'After Import', 'cariera' ); ?>
					</li>
				</ul>

				<button class="panel-save button-primary" type="submit"><?php esc_html_e( 'Import data', 'cariera' ); ?></button>
			</div>


			<div class="demo-picker">
				<select class="image-picker" name="cariera_demo_file" style="width: 100px;">
					<?php
					$dir = $this->demos_folder();

					foreach ( self::$demo_names as $folder_name => $label ) {
						$src = CARIERA_URL . '/inc/importer/demo-files/' . $folder_name . '/src.jpg';

						echo '<option data-img-label="' . esc_attr( $label ) . '" data-img-src="' . $src . '"';
						echo CarieraDemoImporter::check_settings( $folder_name );
						echo 'value="' . $folder_name . '">' . $folder_name . '</option>';
					}
					?>
				</select>
			</div>

			<input type="hidden" name="action" value="demo-data" />
		</form>
		<?php
	}

	public function demos_folder( $lookinside = '' ) {
		$demos_main_fo = CARIERA_CORE_PATH . '/inc/importer/demo-files';

		if ( $lookinside != '' ) {
			$demos_dir = $demos_main_fo . '/' . $lookinside;
		} else {
			$demos_dir = $demos_main_fo;
		}
		$dir = new DirectoryIterator( $demos_dir );

		if ( ! isset( $dir ) || empty( $dir ) ) {
			return;
		}

		return $dir;
	}

	public function get_demo_settings( $demo ) {
		$folder_path = $this->demos_folder( $demo );

		return $folder_path;
	}

	/**
	 * Add_widget_to_sidebar Import sidebars
	 */
	public function add_widget_to_sidebar( $sidebar_slug, $widget_slug, $count_mod, $widget_settings = [] ) {

		$sidebars_widgets = get_option( 'sidebars_widgets' );

		if ( ! isset( $sidebars_widgets[ $sidebar_slug ] ) ) {
			$sidebars_widgets[ $sidebar_slug ] = [ '_multiwidget' => 1 ];
		}

		$newWidget = get_option( 'widget_' . $widget_slug );

		if ( ! is_array( $newWidget ) ) {
			$newWidget = [];
		}

		$count                               = count( $newWidget ) + 1 + $count_mod;
		$sidebars_widgets[ $sidebar_slug ][] = $widget_slug . '-' . $count;

		$newWidget[ $count ] = $widget_settings;

		update_option( 'sidebars_widgets', $sidebars_widgets );
		update_option( 'widget_' . $widget_slug, $newWidget );

	}

	public function _import_customizer( $customize_data = '' ) {
		if ( empty( $customizer_data ) ) {
			$customizer_data = $this->customizer_data;
		}
		if ( ! class_exists( 'WP_Customize_Manager' ) ) {
			require_once ABSPATH . 'wp-includes/class-wp-customize-manager.php';
		}
		global $wp_customize;
		$import_core = new CarieraSquaresCustomizeImport();
		$import_core->init( $wp_customize, $customize_data );
	}

	public function set_demo_data( $file = '' ) {
		if ( empty( $file ) ) {
			$file = $this->content_demo;
		}
		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		require_once ABSPATH . 'wp-admin/includes/import.php';

		$importer_error = false;

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

			if ( file_exists( $class_wp_importer ) ) {
				require_once $class_wp_importer;
			} else {
				$importer_error = true;
			}
		}

		if ( ! class_exists( 'WP_Import' ) ) {

			$class_wp_import = dirname( __FILE__ ) . '/wordpress-importer.php';

			if ( file_exists( $class_wp_import ) ) {
				require_once $class_wp_import;
			} else {
				$importer_error = true;
			}
		}

		if ( $importer_error ) {
			die( 'Error on import' );
		} else {
			if ( ! is_file( $file ) ) {
				echo "The XML file containing the dummy content is not available or could not be read .. You might want to try to set the file permission to chmod 755.<br/>If this doesn't work please use the WordPress importer and import the XML file (should be located in your download .zip: Sample Content folder) manually ";
			} else {
				$wp_import                    = new WP_Import();
				$wp_import->fetch_attachments = true;
				$wp_import->import( $file );
				echo ( 'Contents Imported Successfully. ' );
			}
		}
	}

	public function set_demo_theme_options( $file = '' ) {
		if ( empty( $file ) ) {
			$file = $this->theme_options_file;
		}
		// File exists?
		if ( ! file_exists( $file ) ) {
			wp_die(
				// __( 'Theme options Import file could not be found. ', 'cariera' ),
				'',
				[ 'back_link' => true ]
			);
		}

		// Get file contents and decode.
		$data = file_get_contents( $file );
		$data = trim( $data, '###' );

		$data = json_decode( $data, true );
		// var_dump($data);
		// Have valid data?
		// If no data or could not decode.

		if ( empty( $data ) || ! is_array( $data ) ) {
			wp_die(
				__( 'Theme options import data could not be read. Please try a different file.', 'cariera' ),
				'',
				[ 'back_link' => true ]
			);
		} else {
			echo( '' );
			echo( 'Theme Options Imported successfully' );
		}

		// Hook before import.
		$data = apply_filters( 'cariera_import_theme_options', $data );
		update_option( $this->theme_option_name, $data );
	}

	/**
	 * Available widgets
	 *
	 * Gather site's widgets into array with ID base, name, etc.
	 * Used by export and import functions.
	 */
	function available_widgets() {
		global $wp_registered_widget_controls;
		$widget_controls   = $wp_registered_widget_controls;
		$available_widgets = [];
		foreach ( $widget_controls as $widget ) {
			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) { // no dupes.
				$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
				$available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
			}
		}

		return apply_filters( 'cariera_import_widget_available_widgets', $available_widgets );
	}

	/**
	 * Process import file
	 *
	 * This parses a file and triggers importation of its widgets.
	 */
	function process_widget_import_file( $file = '' ) {
		if ( empty( $file ) ) {
			$file = $this->widgets;
		}
		// File exists?
		if ( ! file_exists( $file ) ) {
			wp_die(
				// __( 'Widget Import file could not be found. ', 'cariera' ),
				'',
				[ 'back_link' => true ]
			);
		}

		// Get file contents and decode.
		$data = file_get_contents( $file );
		$data = json_decode( $data );

		// Delete import file.
		// unlink( $file );

		// Import the widget data.
		// Make results available for display on import/export page.
		$this->widget_import_results = $this->import_widgets( $data );

	}

	/**
	 * Import widget JSON data
	 */
	public function import_widgets( $data ) {

		global $wp_registered_sidebars;

		// Have valid data?
		// If no data or could not decode.
		if ( empty( $data ) || ! is_object( $data ) ) {
			wp_die(
				__( 'Widget import data could not be read. Please try a different file.', 'cariera' ),
				'',
				[ 'back_link' => true ]
			);
		}

		// Hook before import.
		$data = apply_filters( 'cariera_import_widget_data', $data );

		// Get all available widgets site supports.
		$available_widgets = $this->available_widgets();

		// Get all existing widget instances.
		$widget_instances = [];
		foreach ( $available_widgets as $widget_data ) {
			$widget_instances[ $widget_data['id_base'] ] = get_option( 'widget_' . $widget_data['id_base'] );
		}

		// Begin results.
		$results = [];

		// Loop import data's sidebars.
		foreach ( $data as $sidebar_id => $widgets ) {

			// Skip inactive widgets.
			// (should not be in export file).
			if ( 'wp_inactive_widgets' == $sidebar_id ) {
				continue;
			}

			// Check if sidebar is available on this site.
			// Otherwise add widgets to inactive, and say so.
			if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
				$sidebar_available    = true;
				$use_sidebar_id       = $sidebar_id;
				$sidebar_message_type = 'success';
				$sidebar_message      = '';
			} else {
				$sidebar_available    = false;
				$use_sidebar_id       = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme.
				$sidebar_message_type = 'error';
				$sidebar_message      = __( 'Sidebar does not exist in theme (using Inactive)', 'cariera' );
			}

			// Result for sidebar
			$results[ $sidebar_id ]['name']         = ! empty( $wp_registered_sidebars[ $sidebar_id ]['name'] ) ? $wp_registered_sidebars[ $sidebar_id ]['name'] : $sidebar_id; // sidebar name if theme supports it; otherwise ID
			$results[ $sidebar_id ]['message_type'] = $sidebar_message_type;
			$results[ $sidebar_id ]['message']      = $sidebar_message;
			$results[ $sidebar_id ]['widgets']      = [];

			// Loop widgets.
			foreach ( $widgets as $widget_instance_id => $widget ) {

				$fail = false;

				// Get id_base (remove -# from end) and instance ID number.
				$id_base            = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );
				$instance_id_number = str_replace( $id_base . '-', '', $widget_instance_id );

				// Does site support this widget?
				if ( ! $fail && ! isset( $available_widgets[ $id_base ] ) ) {
					$fail                = true;
					$widget_message_type = 'error';
					$widget_message      = __( 'Site does not support widget', 'cariera' ); // explain why widget not imported
				}

				// Filter to modify settings before import.
				// Do before identical check because changes may make it identical to end result (such as URL replacements).
				$widget = apply_filters( 'cariera_import_widget_settings', $widget );

				// Does widget with identical settings already exist in same sidebar?
				if ( ! $fail && isset( $widget_instances[ $id_base ] ) ) {

					// Get existing widgets in this sidebar.
					$sidebars_widgets = get_option( 'sidebars_widgets' );
					$sidebar_widgets  = isset( $sidebars_widgets[ $use_sidebar_id ] ) ? $sidebars_widgets[ $use_sidebar_id ] : []; // check Inactive if that's where will go.

					// Loop widgets with ID base.
					$single_widget_instances = ! empty( $widget_instances[ $id_base ] ) ? $widget_instances[ $id_base ] : [];
					foreach ( $single_widget_instances as $check_id => $check_widget ) {

						// Is widget in same sidebar and has identical settings?
						if ( in_array( "$id_base-$check_id", $sidebar_widgets ) && (array) $widget == $check_widget ) {

							$fail                = true;
							$widget_message_type = 'warning';
							$widget_message      = __( 'Widget already exists', 'cariera' ); // explain why widget not imported.

							break;

						}
					}
				}

				// No failure.
				if ( ! $fail ) {

					// Add widget instance.
					$single_widget_instances   = get_option( 'widget_' . $id_base ); // All instances for that widget ID base, get fresh every time.
					$single_widget_instances   = ! empty( $single_widget_instances ) ? $single_widget_instances : [ '_multiwidget' => 1 ]; // Start fresh if have to.
					$single_widget_instances[] = (array) $widget; // Add it.

						// Get the key it was given.
						end( $single_widget_instances );
						$new_instance_id_number = key( $single_widget_instances );

						// If key is 0, make it 1.
						// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it).
					if ( '0' === strval( $new_instance_id_number ) ) {
						$new_instance_id_number                             = 1;
						$single_widget_instances[ $new_instance_id_number ] = $single_widget_instances[0];
						unset( $single_widget_instances[0] );
					}

						// Move _multiwidget to end of array for uniformity.
					if ( isset( $single_widget_instances['_multiwidget'] ) ) {
						$multiwidget = $single_widget_instances['_multiwidget'];
						unset( $single_widget_instances['_multiwidget'] );
						$single_widget_instances['_multiwidget'] = $multiwidget;
					}

						// Update option with new widget.
						update_option( 'widget_' . $id_base, $single_widget_instances );

					// Assign widget instance to sidebar.
					$sidebars_widgets                      = get_option( 'sidebars_widgets' ); // which sidebars have which widgets, get fresh every time.
					$new_instance_id                       = $id_base . '-' . $new_instance_id_number; // use ID number from new widget instance.
					$sidebars_widgets[ $use_sidebar_id ][] = $new_instance_id; // add new instance to sidebar.
					update_option( 'sidebars_widgets', $sidebars_widgets ); // save the amended data.

					// Success message.
					if ( $sidebar_available ) {
						$widget_message_type = 'success';
						$widget_message      = __( 'Widgets Imported', 'cariera' );
					} else {
						$widget_message_type = 'warning';
						$widget_message      = __( 'Imported to Inactive', 'cariera' );
					}
				}

				// Result for widget instance.
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['name']         = isset( $available_widgets[ $id_base ]['name'] ) ? $available_widgets[ $id_base ]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['title']        = $widget->title ? $widget->title : __( 'No Title', 'cariera' ); // show "No Title" if widget instance is untitled
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message_type'] = $widget_message_type;
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message']      = $widget_message;

			}
		}
		echo $widget_message;

		// Hook after import.
		do_action( 'cariera_import_widget_after_import' );

		// Return results.
		return apply_filters( 'cariera_import_widget_results', $results );

	}

}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Admin_Conditionals_Backup
 *
 * @since 1.8.0
 *
 */
class WP_Job_Manager_Field_Editor_Admin_Conditionals_Backup {

	/**
	 * @var
	 */
	private static $instance;

	/**
	 * @var string
	 */
	private $job_option    = 'field_editor_job_listing_conditional_logic';
	/**
	 * @var string
	 */
	private $resume_option = 'field_editor_resume_conditional_logic';
	/**
	 * @var string
	 */
	private $page_slug = 'jmfe-logic-backup';

	/**
	 * WP_Job_Manager_Field_Editor_Admin_Conditionals_Backup constructor.
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_code_editor' ) );
		add_action( 'wp_ajax_jmfe_logic_backup_save', array( $this, 'save_logic' ) );

	}

	/**
	 * Save Logic Configuration via AJAX
	 *
	 *
	 * @since 1.8.0
	 *
	 */
	public function save_logic(){

		check_ajax_referer( 'jmfe_logic_backup_save', 'nonce' );

		$result = false;

		if ( ! array_key_exists( 'logic_type', $_POST ) || empty( $_POST['logic_type'] ) ) {
			wp_send_json_error( array( 'error' => __( 'Logic type not specified in AJAX call!', 'wp-job-manager-field-editor' ) ) );
		}

		$type = isset( $_POST['logic_type'] ) && $_POST['logic_type'] === 'resume' ? 'resume' : 'job';
		$option = "{$type}_option";

		if( array_key_exists( 'logic', $_POST ) ){

			if( $_POST['logic'] == 'CONFIRM_RESET' ||  empty( $_POST['logic'] ) ){
				$result = delete_option( $this->$option );
			} else {
				$result = update_option( $this->$option, $_POST['logic'] );
			}
		}

		ob_clean();

		if ( $result ) {
			wp_send_json_success( array( 'message' => sprintf( __( 'Successfully saved %s logic configuration!', 'wp-job-manager-field-editor' ), ucfirst( $type ) ) ) );
		} else {
			wp_send_json_error( array( 'error' => sprintf( __( 'Unable to update %s logic configuration, are you sure you made changes? You will receive this error if nothing has changed in the configuration, or if you are trying to save an empty JSON object.  To reset all data in configuration, set the value to CONFIRM_RESET and save.', 'wp-job-manager-field-editor' ), ucfirst( $type ) ) ) );
		}

	}

	/**
	 * Maybe Use CodeMirror Editor (if using WP 4.9+)
	 *
	 *
	 * @since 1.8.0
	 *
	 */
	public function maybe_code_editor(){

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== $this->page_slug ) {
			return;
		}

		wp_register_script( 'jmfe-js-beautify', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/beautify.min.js', array(
			'jquery',
		), WPJM_FIELD_EDITOR_VERSION, true );

		wp_register_script( 'jmfe-logic-backup', WPJM_FIELD_EDITOR_PLUGIN_URL . '/assets/js/logic-backup.min.js', array(
			'jquery',
			'code-editor',
			'jmfe-js-beautify'
		), WPJM_FIELD_EDITOR_VERSION, true );

		// Enqueue code editor and settings for manipulating HTML.
		$settings = wp_enqueue_code_editor( array( 'type' => 'application/json' ) );

		// Bail if user disabled CodeMirror.
		if ( false === $settings ) {
			return;
		}

		wp_localize_script( 'jmfe-logic-backup', 'jmfe_logic_editor_config', $settings );

		wp_enqueue_script( 'jmfe-logic-backup' );

	}

	/**
	 * Add Hidden Page
	 *
	 *
	 * @since 1.8.0
	 *
	 */
	public function add_page() {

		$page = add_submenu_page(
			'options.php',
			__( 'Field Editor Conditional Logic Backup/Restore', 'wp-job-manager-field-editor' ),
			__( 'Field Editor Conditional Logic Backup/Restore', 'wp-job-manager-field-editor' ),
			'manage_options',
			$this->page_slug,
			array( $this, 'output' )
		);

		add_action( "load-{$page}", array( &$this, 'maybe_download' ) );

	}

	/**
	 * Maybe Download Configuration File
	 *
	 *
	 * @since 1.8.0
	 *
	 */
	public function maybe_download() {

		$type = isset( $_REQUEST['logic_type'] ) && $_REQUEST['logic_type'] === 'resume' ? 'resume' : 'job';

		if ( isset( $_GET['action'] ) && ( $_GET['action'] === 'download' ) ) {
			header( 'Cache-Control: public, must-revalidate' );
			header( 'Pragma: hack' );
			header( 'Content-Type: text/json; charset=' . get_option( 'blog_charset' ) );
			header( 'Content-Disposition: attachment; filename="job_manager-' . $type . '-logic-' . date( 'dMy' ) . '.json"' );
			echo json_encode( $this->get_logic_configuration( $type ) );
			die();
		}

	}

	/**
	 * Output
	 *
	 *
	 * @since 1.8.0
	 *
	 */
	public function output() {

		$job_label = WP_Job_Manager_Field_Editor::get_job_post_label();
		if ( function_exists( 'wp_nonce_field' ) ) {
			wp_nonce_field( 'jmfe_logic_backup_save', 'jmfe_logic_backup_save' );
		}
		?>
		<div class="wrap">
			<h2><?php _e('Field Editor Conditional Logic Editor (Backup/Restore)', 'wp-job-manager-field-editor'); ?></h2>
			<form action="" method="POST" enctype="multipart/form-data">
				<style>
					#backup-options td {
						display: block;
						margin-bottom: 20px;
					}
					.minify-code {
						border-radius: 5px;
						cursor: pointer;
					}
					.beautify-code {
						cursor: pointer;
					}
					.disabled-wrap {
						pointer-events: none;
						opacity: 0.4;
					}
				</style>
				<div id="logic_notice" class="notice" style="display: none;">
					<p id="logic_notice_p"></p>
				</div>
				<table id="backup-options" width="100%">
					<tr>
						<td>
							<h3><?php echo $job_label; ?> <?php _e('Logic Configuration', 'wp-job-manager-field-editor'); ?></h3>
							<p><textarea id="job_logic_textarea" class="widefat code" width="100%" style="width: 100%;"><?php echo json_encode( $this->get_logic_configuration( 'job' ) ); ?></textarea></p>
							<div style="float: right; clear: both;">
								<a data-type="job" id="job_logic_beautify" class="dashicons dashicons-editor-code beautify-code" title="<?php _e('Beautify JSON Code', 'wp-job-manager-field-editor'); ?>"></a>
								<a data-type="job" id="job_logic_minify" class="dashicons dashicons-editor-justify minify-code" title="<?php _e('Minify JSON Code', 'wp-job-manager-field-editor'); ?>"></a>
							</div>
							<p style="margin-top: 30px;">
								<input type="hidden" name="logic_type" value="job"/>
								<a data-type="job" class="save_logic button button-primary"><?php printf( __( 'Save %s Logic JSON', 'wp-job-manager-field-editor' ), ucfirst( $job_label ) ); ?></a>
								<a href="?page=<?php echo $this->page_slug; ?>&action=download&logic_type=job" class="button button-primary"><?php _e('Download as file', 'wp-job-manager-field-editor'); ?></a>
							</p>
						</td>
					</tr>
				</table>
			</form>

			<?php if( WP_Job_Manager_Field_Editor::resumes_active() ): ?>
				<form action="" method="POST" enctype="multipart/form-data">
				<table id="backup-options" width="100%">
					<tr>
						<td>
							<h3><?php _e( 'Resume Logic Configuration', 'wp-job-manager-field-editor' ); ?></h3>
							<p><textarea id="resume_logic_textarea" class="widefat code" width="100%" style="width: 100%;"><?php echo json_encode( $this->get_logic_configuration( 'resume' ) ); ?></textarea></p>
							<div style="float: right; clear: both;">
								<a data-type="resume" class="dashicons dashicons-editor-code beautify-code" title="<?php _e( 'Beautify JSON Code', 'wp-job-manager-field-editor' ); ?>"></a>
								<a data-type="resume" class="dashicons dashicons-editor-justify minify-code" title="<?php _e( 'Minify JSON Code', 'wp-job-manager-field-editor' ); ?>"></a>
							</div>
							<p style="margin-top: 30px;">
								<input type="hidden" name="logic_type" value="resume"/>
								<a data-type="resume" class="save_logic button button-primary"><?php _e( 'Save Resume Logic JSON', 'wp-job-manager-field-editor' ); ?></a>
								<a href="?page=<?php echo $this->page_slug; ?>&action=download&logic_type=resume" class="button button-primary"><?php _e( 'Download as file', 'wp-job-manager-field-editor' ); ?></a>
							</p>
						</td>
					</tr>
				</table>
			</form>
			<?php endif; ?>
		</div>

	<?php }

	/**
	 * Return Logic Configuration
	 *
	 *
	 * @since 1.8.0
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	private function get_logic_configuration( $type = 'job' ) {

		$variable = "{$type}_option";

		return get_option( $this->$variable );

	}

	/**
	 * Singleton Instance
	 *
	 *
	 * @since 1.8.0
	 *
	 * @return \WP_Job_Manager_Field_Editor_Admin_Conditionals_Backup
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
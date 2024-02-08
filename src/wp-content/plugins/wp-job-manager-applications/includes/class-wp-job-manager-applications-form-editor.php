<?php
/**
 * File containing the class WP_Job_Manager_Applications_Form_Editor.
 *
 * @package wp-job-manager-applications
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Editors for form fields and e-mail templates of job application forms.
 */
class WP_Job_Manager_Applications_Form_Editor {

	/**
	 * Set up hooks for the editors on the job_application_form post type edit page.
	 */
	public function __construct() {

		add_action( 'edit_form_after_title', [ $this, 'add_form_editor' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 11 );
		add_filter( 'get_user_option_screen_layout_job_application_form', [ $this, 'set_screen_layout' ] );
		add_filter( 'save_post_job_application_form', [ $this, 'save_actions' ] );
		add_filter( 'admin_action_job_application_form_reset', [ $this, 'reset_actions' ] );
		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );

	}

	/**
	 * Render the form fields and e-mail editors.
	 */
	public function add_form_editor() {
		if ( 'job_application_form' === get_post_type() ) {
			$post_id = get_the_ID();

			$form_data = WP_Job_Manager_Applications_Application_Form::get_form_data( $post_id );

			$this->output( $form_data );
		}
	}

	/**
	 * Set screen to one column layout.
	 *
	 * @return int Number of columns to use.
	 */
	public function set_screen_layout() {
		return 1;
	}

	/**
	 * Handle save actions.
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function save_actions( $post_id ) {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce check.
		if ( empty( $post_id ) || empty( $_POST['_wpnonce_job_application_form'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce_job_application_form'] ), 'save-application-form' ) ) {
			return;
		}

		remove_filter( 'save_post_job_application_form', [ $this, 'save_actions' ] );

		$form_data = [
			'form_fields'              => $this->form_editor_save(),
			'candidate_email_template' => $this->email_save( 'candidate' ),
			'employer_email_template'  => $this->email_save( 'employer' ),
		];

		$form_data = apply_filters( 'job_manager_job_application_save_form_data', $form_data, $post_id );

		$form = new WP_Job_Manager_Applications_Application_Form( $post_id );

		$form->set( $form_data );
		$form->save();
	}

	/**
	 * Handle reset actions.
	 *
	 * @return void
	 */
	public function reset_actions() {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce check.
		if ( empty( $_GET['post'] ) || empty( $_GET['reset-action'] ) || empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'job_application_form_reset' ) ) {
			return;
		}

		$post_id = absint( $_GET['post'] );
		$post    = get_post( $post_id );

		if ( empty( $post ) || 'job_application_form' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$message = '';

		$form = new WP_Job_Manager_Applications_Application_Form( $post_id );

		$defaults = WP_Job_Manager_Applications_Application_Form::get_default_data();

		switch ( sanitize_text_field( wp_unslash( $_GET['reset-action'] ) ) ) {
			case 'fields':
				$form->set( [ 'form_fields' => $defaults['form_fields'] ] );
				$message = 21;
				break;
			case 'employer-email':
				$form->set(
					[
						'employer_email_template' => $defaults['employer_email_template'],
					]
				);
				$message = 22;
				break;
			case 'candidate-email':
				$form->set(
					[
						'candidate_email_template' => $defaults['candidate_email_template'],
					]
				);
				$message = 22;
				break;
		}

		$form->save();

		wp_safe_redirect(
			add_query_arg(
				[
					'action'       => 'edit',
					'reset-action' => false,
					'_wpnonce'     => false,
					'message'      => $message,
				]
			)
		);
		exit;

	}

	/**
	 * Add feedback messages to be used after reset actions.
	 *
	 * @param array $messages Default messages.
	 *
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		$messages['job_application_form'][21] = __( 'The fields were successfully reset.', 'wp-job-manager-applications' );
		$messages['job_application_form'][22] = __( 'The email was successfully reset.', 'wp-job-manager-applications' );
		return $messages;
	}


	/**
	 * Register scripts
	 */
	public function admin_enqueue_scripts() {
		$current_screen = get_current_screen();

		if ( 'job_application_form' !== $current_screen->id ) {
			return;
		}

		add_screen_option(
			'layout_columns',
			array(
				'max'     => 1,
				'default' => 1,
			)
		);

		$form_editor_deps = array( 'jquery', 'jquery-ui-sortable' );
		if ( wp_script_is( 'select2', 'registered' ) ) {
			$form_editor_deps[] = 'select2';
			wp_enqueue_style( 'select2' );
		} elseif ( file_exists( JOB_MANAGER_PLUGIN_DIR . '/assets/js/jquery-chosen/chosen.jquery.min.js' )
		&& file_exists( JOB_MANAGER_PLUGIN_DIR . '/assets/css/chosen.css' )
		) {
			wp_register_script( 'chosen', JOB_MANAGER_PLUGIN_URL . '/assets/js/jquery-chosen/chosen.jquery.min.js', array( 'jquery' ), '1.1.0', true );
			wp_enqueue_style( 'chosen', JOB_MANAGER_PLUGIN_URL . '/assets/css/chosen.css', [], JOB_MANAGER_VERSION );
			$form_editor_deps[] = 'chosen';
		} elseif ( file_exists( JOB_MANAGER_PLUGIN_DIR . '/assets/lib/jquery-chosen/chosen.jquery.min.js' )
		&& file_exists( JOB_MANAGER_PLUGIN_DIR . '/assets/lib/jquery-chosen/chosen.css' )
		) {
			wp_register_script( 'chosen', JOB_MANAGER_PLUGIN_URL . '/assets/lib/jquery-chosen/chosen.jquery.min.js', array( 'jquery' ), '1.1.0', true );
			wp_enqueue_style( 'chosen', JOB_MANAGER_PLUGIN_URL . '/assets/lib/jquery-chosen/chosen.css', [], JOB_MANAGER_VERSION );
			$form_editor_deps[] = 'chosen';
		}

		wp_register_script( 'wp-job-manager-applications-form-editor', plugins_url( '/assets/dist/js/form-editor.js', JOB_MANAGER_APPLICATIONS_FILE ), $form_editor_deps, JOB_MANAGER_APPLICATIONS_VERSION, true );
		wp_localize_script(
			'wp-job-manager-applications-form-editor',
			'wp_job_manager_applications_form_editor',
			array(
				'confirm_delete_i18n' => __( 'Are you sure you want to delete this row?', 'wp-job-manager-applications' ),
				'confirm_reset_i18n'  => __( 'Are you sure you want to reset your changes? This cannot be undone.', 'wp-job-manager-applications' ),
				'is_rtl'              => is_rtl() ? 1 : 0,
			)
		);
	}

	/**
	 * Output form and email editors.
	 *
	 * @param array $form_data
	 */
	public function output( $form_data ) {
		wp_enqueue_script( 'wp-job-manager-applications-form-editor' );

		/**
		 * Filters tabs that can extend the default tabs in the job application form page.
		 *
		 * @since 3.0.0
		 *
		 * @param array $tabs List of tabs to be displayed in the admin edit page. The format is: ID => label where ID is the same id of the element when outputing the markup.
		 */
		$tabs = apply_filters(
			'job_application_form_editor_tabs',
			array(
				'tab-fields'                 => __( 'Form Fields', 'wp-job-manager-applications' ),
				'tab-employer-notification'  => __( 'Employer Notification', 'wp-job-manager-applications' ),
				'tab-candidate-notification' => __( 'Candidate Notification', 'wp-job-manager-applications' ),
			)
		);

		?>
		<div class="wp-job-manager-applications-form-editor">
			<div class="wp-filter">
				<ul class="filter-links wp-job-manager-applications-form-editor-tabs">
		<?php
		$tab_first = true;
		foreach ( $tabs as $key => $value ) {
			$active    = $tab_first ? 'current' : '';
			$tab_first = false;
			echo '<li><a class="tab-link ' . esc_attr( $active ) . '" href="#' . esc_attr( $key ) . '">' . esc_html( $value ) . '</a></li>';
		}
		?>
				</ul>
			</div>

		<div class="tab-content" id="tab-fields">
		<?php
		$this->form_editor( $form_data['form_fields'] );

		?>
		</div>

		<div class="tab-content" id="tab-employer-notification">
			<p><?php esc_html_e( 'Below you will find the email that is sent to an employer after a candidate submits an application.', 'wp-job-manager-applications' ); ?></p>
			<?php

			$this->email_editor( 'employer', $form_data );

			?>
		</div>

		<div class="tab-content" id="tab-candidate-notification">
		<p><?php esc_html_e( 'Below you will find the email that is sent to a candidate after submitting an application. Leave blank to disable.', 'wp-job-manager-applications' ); ?></p>
		<?php
		$this->email_editor( 'candidate', $form_data );

		?>
		</div>
		<?php
		/**
		 * Perform action when we are outputting tab content.
		 * Note that tab content must be wrapped in `<div class="tab-content" id="tab-ID"></div>`.
		 *
		 * @since 3.0.0
		 */
		do_action( 'job_application_form_editor_tab_content' );
		wp_nonce_field( 'save-application-form', '_wpnonce_job_application_form' );

		?>
		</div>
		<?php
	}

	/**
	 * Show a reset link for the tab, unless it's the 'Add Form' page.
	 *
	 * @param string $action Reset action - which tab to reset.
	 *
	 * @return string
	 */
	private function reset_link( $action ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Check only.
		if ( ! isset( $_GET['post'] ) ) {
			return '';
		}

		$reset_link = wp_nonce_url(
			add_query_arg(
				[
					'action'       => 'job_application_form_reset',
					'reset-action' => $action,
				]
			),
			'job_application_form_reset'
		);

		return '<a href="' . esc_url( $reset_link ) . '" class="reset">' . esc_html__( 'Reset to defaults', 'wp-job-manager-applications' ) . '</a>';
	}

	/**
	 * Output the form editor
	 */
	private function form_editor( $fields ) {

		/**
		 * Filters rules that can only be used once per form.
		 *
		 * @since 2.2.4
		 *
		 * @param array $unique_rules Rule key values that should be unique.
		 */
		$unique_rules = apply_filters(
			'job_application_form_unique_field_rules',
			array(
				'from_name',
				'from_email',
				'message',
			)
		);

		/**
		 * Returns the rules that can be used on application form fields.
		 *
		 * @since 2.0.1
		 *
		 * @param array $rules
		 */
		$field_rules = apply_filters(
			'job_application_form_field_rules',
			array(
				__( 'Validation', 'wp-job-manager-applications' )    => array(
					'required' => __( 'Required', 'wp-job-manager-applications' ),
					'email'    => __( 'Email', 'wp-job-manager-applications' ),
					'numeric'  => __( 'Numeric', 'wp-job-manager-applications' ),
				),
				__( 'Data Handling', 'wp-job-manager-applications' ) => array(
					'from_name'  => __( 'From Name', 'wp-job-manager-applications' ),
					'from_email' => __( 'From Email', 'wp-job-manager-applications' ),
					'message'    => __( 'Message', 'wp-job-manager-applications' ),
					'attachment' => __( 'Attachment', 'wp-job-manager-applications' ),
				),
			)
		);

		/**
		 * Returns the field types that can be used on application forms.
		 *
		 * @sicne 2.0.1
		 *
		 * @param array $field_types
		 */
		$field_types = apply_filters(
			'job_application_form_field_types',
			array(
				'text'           => __( 'Text', 'wp-job-manager-applications' ),
				'textarea'       => __( 'Textarea', 'wp-job-manager-applications' ),
				'file'           => __( 'File', 'wp-job-manager-applications' ),
				'select'         => __( 'Select', 'wp-job-manager-applications' ),
				'multiselect'    => __( 'Multiselect', 'wp-job-manager-applications' ),
				'checkbox'       => __( 'Checkbox', 'wp-job-manager-applications' ),
				'date'           => __( 'Date', 'wp-job-manager-applications' ),
				'resumes'        => __( 'Resume', 'wp-job-manager-applications' ),
				'output-content' => __( 'Output content', 'wp-job-manager-applications' ),
			)
		);

		if ( ! function_exists( 'get_resume_share_link' ) ) {
			unset( $field_types['resumes'] );
		}

		?>

		<table class="widefat">
			<thead>
			<tr>
				<th width="1%">&nbsp;</th>
				<th><?php esc_html_e( 'Field Label', 'wp-job-manager-applications' ); ?></th>
				<th width="1%"><?php esc_html_e( 'Type', 'wp-job-manager-applications' ); ?></th>
				<th><?php esc_html_e( 'Description', 'wp-job-manager-applications' ); ?></th>
				<th><?php esc_html_e( 'Placeholder / Options', 'wp-job-manager-applications' ); ?></th>
				<th width="1%"><?php esc_html_e( 'Validation / Rules', 'wp-job-manager-applications' ); ?></th>
				<th width="1%" class="field-actions">&nbsp;</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="7">
					<div class="form-actions">
						<a class="button add-field" href="#"><?php esc_html_e( 'Add field', 'wp-job-manager-applications' ); ?></a>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in method.
					echo $this->reset_link( 'fields' );
					?>
					</div>
				</th>
			</tr>
			</tfoot>
			<tbody id="form-fields" data-field="
			<?php
			ob_start();
			$index     = - 1;
			$field_key = '';
			$field     = array(
				'type'        => 'text',
				'label'       => '',
				'placeholder' => '',
			);
			include 'views/html-form-field-editor-row.php';
			echo esc_attr( ob_get_clean() );
			?>
			">
			<?php
			foreach ( $fields as $field_key => $field ) {
				$index ++;
				include 'views/html-form-field-editor-row.php';
			}
			?>
			</tbody>
		</table>
			<?php
	}

	/**
	 * Save the form fields
	 */
	private function form_editor_save() {

		//phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce already verified in save_actions().

		$field_types          = ! empty( $_POST['field_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_type'] ) ) : array();
		$field_labels         = ! empty( $_POST['field_label'] ) ? array_map( 'wp_kses_post', wp_unslash( $_POST['field_label'] ) ) : array();
		$field_descriptions   = ! empty( $_POST['field_description'] ) ? array_map( 'wp_kses_post', wp_unslash( $_POST['field_description'] ) ) : array();
		$field_placeholder    = ! empty( $_POST['field_placeholder'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_placeholder'] ) ) : array();
		$field_options        = ! empty( $_POST['field_options'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_options'] ) ) : array();
		$field_multiple_files = ! empty( $_POST['field_multiple_files'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_multiple_files'] ) ) : array();
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized in sanitize_array().
		$field_rules = ! empty( $_POST['field_rules'] ) ? $this->sanitize_array( wp_unslash( $_POST['field_rules'] ) ) : array();
		$new_fields  = array();
		$index       = 0;

		//phpcs:enable WordPress.Security.NonceVerification.Missing

		foreach ( $field_labels as $key => $field ) {
			if ( empty( $field ) ) {
				continue;
			}
			$field_name = sanitize_title( $field );
			$options    = ! empty( $field_options[ $key ] ) ? array_map( 'sanitize_text_field', explode( '|', $field_options[ $key ] ) ) : array();

			$new_field                = array();
			$new_field['label']       = $field;
			$new_field['type']        = $field_types[ $key ];
			$new_field['required']    = ! empty( $field_rules[ $key ] ) && in_array( 'required', $field_rules[ $key ], true );
			$new_field['options']     = $options ? array_combine( $options, $options ) : array();
			$new_field['placeholder'] = $field_placeholder[ $key ];
			$new_field['description'] = $field_descriptions[ $key ];
			$new_field['priority']    = $index ++;
			$new_field['multiple']    = isset( $field_multiple_files[ $key ] );
			$new_field['rules']       = ! empty( $field_rules[ $key ] ) ? $field_rules[ $key ] : array();
			if ( isset( $new_fields[ $field_name ] ) ) {
				// Generate a unique field name by appending a number to the existing field name.
				// Assumes no more than 100 fields with the same name would be needed? Otherwise it will override the field.
				$counter = 1;
				while ( $counter <= 100 ) {
					$candidate = $field_name . '-' . $counter;
					if ( ! isset( $new_fields[ $candidate ] ) ) {
						$field_name = $candidate;
						break;
					}
					$counter ++;
				}
			}
			$new_fields[ $field_name ] = $new_field;
		}

		return $new_fields;
	}

	/**
	 * Sanitize a string or array of text fields.
	 *
	 * @param array|string $input Input array.
	 *
	 * @return array|string
	 */
	private function sanitize_array( $input ) {
		if ( is_array( $input ) ) {
			foreach ( $input as $k => $v ) {
				$input[ $k ] = $this->sanitize_array( $v );
			}

			return $input;
		} else {
			return sanitize_text_field( $input );
		}
	}

	/**
	 * Employer notification email editor.
	 *
	 * @param array $form
	 */
	private function employer_notification_editor( $form ) {

	}

	/**
	 * Email template editor.
	 *
	 * @param string $email_type
	 * @param array  $form
	 *
	 * @return void
	 */
	private function email_editor( $email_type, $form ) {

		$email   = $form[ $email_type . '_email_template' ];
		$form_id = $form['ID'] ?? null;

		$subject = $email['subject'];
		$content = $email['content'];

		$subject_input = $email_type . '-email-subject';
		$content_input = $email_type . '-email-content';

		?>
		<div class="wp-job-applications-email-content-wrapper">
			<div class="wp-job-applications-email-content">
				<p>
					<input type="text" name="<?php echo esc_attr( $subject_input ); ?>"
						value="<?php echo esc_attr( $subject ); ?>"
						placeholder="<?php echo esc_attr( __( 'Subject', 'wp-job-manager-applications' ) ); ?>" />
				</p>
				<p>
					<textarea name="<?php echo esc_attr( $content_input ); ?>" cols="71"
						rows="10"><?php echo esc_textarea( $content ); ?></textarea>
				</p>
			</div>
			<div class="wp-job-applications-email-content-tags">
				<p><?php esc_html_e( 'The following tags can be used to add content dynamically:', 'wp-job-manager-applications' ); ?></p>
				<ul>
		<?php foreach ( get_job_application_email_tags( $form_id ) as $tag => $name ) : ?>
						<li><code>[<?php echo esc_html( $tag ); ?>]</code> - <?php echo wp_kses_post( $name ); ?></li>
		<?php endforeach; ?>
				</ul>
				<p><?php esc_html_e( 'All tags can be passed a prefix and a suffix which is only output when the value is set e.g. <code>[job_title prefix="Job Title: " suffix="."]</code>', 'wp-job-manager-applications' ); ?></p>
			</div>
		</div>
		<div class="form-actions">
		<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in method.
		echo $this->reset_link( $email_type . '-email' );
		?>
		</div>
		<?php
	}

	/**
	 * Save employer notification email.
	 *
	 * @param string $email_type employer|candidate.
	 *
	 * @return array|null
	 */
	private function email_save( $email_type ) {

		//phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce already verified in save_actions().

		$subject_input = $email_type . '-email-subject';
		$content_input = $email_type . '-email-content';

		if ( ! isset( $_POST[ $subject_input ] ) || ! isset( $_POST[ $content_input ] ) ) {
			return null;
		}

		$email_content = wp_kses_post( wp_unslash( $_POST[ $content_input ] ) );
		$email_subject = sanitize_text_field( wp_unslash( $_POST[ $subject_input ] ) );

		//phpcs:enable WordPress.Security.NonceVerification.Missing

		return [
			'content' => $email_content,
			'subject' => $email_subject,
		];
	}

	/**
	 * Candidate notification email editor.
	 *
	 * @param array $form
	 */
	private function candidate_notification_editor( $form ) {

	}
}

new WP_Job_Manager_Applications_Form_Editor();

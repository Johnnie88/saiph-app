<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPJM {

	private $values          = [];
	private $submit_instance = null;

	/**
	 * Constructor.
	 *
	 * @since   1.4.4
	 * @version 1.5.5
	 */
	public function __construct() {

		// Backend Fields & Integration.
		add_filter( 'job_manager_job_listing_data_fields', [ $this, 'job_admin_fields' ], 9999 );

		// Frontend Fields & Integration.
		add_action( 'submit_job_form_company_fields_start', [ $this, 'company_selection' ], 99 );
		add_filter( 'submit_job_form_fields', [ $this, 'company_fields' ] );
		add_action( 'submit_job_form_company_fields_end', [ $this, 'add_company_fields' ] );

		// Submission form fields validation.
		add_filter( 'submit_job_form_validate_fields', [ $this, 'validate_fields' ], 99, 3 );

		// Metakey Migration.
		if ( ! get_option( 'cariera_company_metakey_migration_init' ) ) {
			add_action( 'admin_init', [ $this, 'company_metakey_migration' ] );
		}

		if ( $this->integration_enabled() ) {
			// Company Search.
			add_filter( 'job_manager_get_listings', [ $this, 'job_manager_get_listings' ], 9999, 2 );

			// Meta Mappings.
			add_filter( 'the_company_name', [ $this, 'the_company_name' ], 10, 2 );
			add_filter( 'the_company_website', [ $this, 'the_company_website' ], 10, 2 );
			add_filter( 'the_company_twitter', [ $this, 'the_company_twitter' ], 10, 2 );
			add_filter( 'the_company_tagline', [ $this, 'the_company_tagline' ], 10, 2 );
			add_filter( 'the_company_video', [ $this, 'the_company_video' ], 10, 2 );
		}
	}

	/**
	 * Check if the Cariera Company Manager integrations is enabled
	 *
	 * @since 1.4.4
	 */
	protected function integration_enabled() {
		return get_option( 'cariera_company_manager_integration', false );
	}

	/**
	 * Check if Company is required on job submission
	 *
	 * @since 1.7.0
	 * @return boolean
	 */
	protected function is_required() {
		return get_option( 'cariera_job_submit_company_required', true );
	}

	/**
	 * Removing default wpjm company meta fields
	 *
	 * @since 1.4.4
	 */
	public function job_admin_fields( $fields ) {

		if ( ! $this->integration_enabled() ) {
			return $fields;
		}

		if ( isset( $fields['_company_name'] ) ) {
			unset( $fields['_company_name'] );
		}

		if ( isset( $fields['_company_website'] ) ) {
			unset( $fields['_company_website'] );
		}

		if ( isset( $fields['_company_tagline'] ) ) {
			unset( $fields['_company_tagline'] );
		}

		if ( isset( $fields['_company_twitter'] ) ) {
			unset( $fields['_company_twitter'] );
		}

		if ( isset( $fields['_company_video'] ) ) {
			unset( $fields['_company_video'] );
		}

		$fields['_company_manager_id'] = [
			'label'        => esc_html__( 'Company', 'cariera' ),
			'type'         => 'company_select',
			'priority'     => 0.1,
			'options'      => [],
			'show_in_rest' => true,
		];

		return $fields;
	}

	/**
	 * Company Selection
	 *
	 * @since    1.4.0
	 * @version  1.5.5
	 */
	public function company_selection() {

		if ( ! $this->integration_enabled() || isset( $_GET['action'] ) ) {
			return;
		}

		wp_enqueue_script( 'cariera-company-manager-submission' );

		if ( get_option( 'cariera_user_specific_company' ) ) {
			$user_companies = cariera_get_user_companies( [ 'post_status' => 'any' ], true );
			$status_class   = $user_companies ? [ 'has-companies' ] : [ 'no-companies' ];
		} else {
			$status_class = [ 'has-companies' ];
		}

		$add_new_company  = get_option( 'cariera_add_new_company' );
		$submission_limit = get_option( 'cariera_company_submission_limit' );
		$total_companies  = cariera_count_user_companies();
		$checked          = ( $add_new_company && ( $total_companies < $submission_limit || ! $submission_limit ) ) ? '' : 'checked';

		if ( $add_new_company && ( $total_companies < $submission_limit || ! $submission_limit ) ) {
			$status_class[] = '';
		} else {
			$status_class[] = 'disable-add-company';
		}
		?>

		<div id="company-selection" class="<?php echo esc_attr( join( ' ', $status_class ) ); ?>">
			<?php if ( $add_new_company && ( $total_companies < $submission_limit || ! $submission_limit ) ) { ?>
				<div class="fieldset new-company">
					<input type="radio" name="company_submission" id="new-company" value="new_company" class="company-selection-radio" checked>
					<label for="new-company">
						<span class="icon"><i class="icon-plus"></i></span>
						<span class="text"><?php esc_html_e( 'New Company', 'cariera' ); ?></span>
					</label>
				</div>
			<?php } ?>

			<div class="fieldset existing-company">
				<input type="radio" name="company_submission" id="existing-company" value="existing_company" class="company-selection-radio" <?php echo esc_attr( $checked ); ?>>
				<label for="existing-company">
					<span class="icon"><i class="far fa-building"></i></span>
					<span class="text"><?php esc_html_e( 'Existing Company', 'cariera' ); ?></span>
				</label>
			</div>
		</div>

		<fieldset class="no-companies-message hidden">
			<p class="job-manager-error">
				<?php esc_html_e( 'You either have not logged in or you don\'t have any companies with this account.', 'cariera' ); ?>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Removing default company fields
	 *
	 * @since   1.4.4
	 * @version 1.5.5
	 */
	public function company_fields( $fields ) {

		if ( ! $this->integration_enabled() ) {
			return $fields;
		}

		if ( isset( $fields['company'] ) ) {
			unset( $fields['company'] );
		}

		$fields['company']['company_manager_id'] = [
			'label'       => esc_html__( 'Select Company', 'cariera' ),
			'type'        => 'company-select',
			'required'    => false,
			'description' => '',
			'priority'    => '0.1',
			// 'default'     => -1,
			'options'     => [],
		];

		return $fields;
	}

	/**
	 * Getting all the company fields
	 *
	 * @since 1.3.0
	 */
	public function submit_company_form_fields() {
		$fields = \Cariera_Core\Core\Company_Manager\Forms\Submit_Company::get_company_fields();

		return apply_filters( 'cariera_submit_job_form_company_fields', $fields );
	}

	/**
	 * Adding company fields
	 *
	 * @since 1.3.0
	 */
	public function add_company_fields() {

		if ( ! $this->integration_enabled() || isset( $_GET['action'] ) ) {
			return;
		}

		$company_fields = $this->submit_company_form_fields();

		$job_id     = ! empty( $_REQUEST['job_id'] ) ? absint( $_REQUEST['job_id'] ) : 0;
		$company_id = 0;

		if ( ! job_manager_user_can_edit_job( $job_id ) ) {
			$job_id = 0;
		}

		if ( $job_id ) {
			$company_id = get_post_meta( $job_id, '_company_manager_id', true );
			if ( ! empty( $company_id ) ) {
				$company = get_post( $company_id );
			}
		}

		foreach ( $company_fields as $key => $field ) {

			// If company subission is not require make all company fields not required.
			if ( ! $this->is_required() ) {
				$field['required'] = false;
			}

			if ( $company_id ) {
				if ( ! isset( $field['value'] ) ) {
					if ( 'company_name' === $key ) {
						$field['value'] = $company->post_title;
					} elseif ( 'company_content' === $key ) {
						$field['value'] = $company->post_content;
					} elseif ( ! empty( $field['taxonomy'] ) ) {
						$field['value'] = wp_get_object_terms( $company->ID, $field['taxonomy'], [ 'fields' => 'ids' ] );
					} else {
						$field['value'] = get_post_meta( $company->ID, '_' . $key, true );
					}
				}
			}
			?>
			<fieldset class="fieldset-<?php echo esc_attr( $key ); ?> fieldset-type-<?php echo esc_attr( $field['type'] ); ?> cariera-company-manager-fieldset">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . wp_kses_post( apply_filters( 'submit_job_form_required_label', $field['required'] ? '' : ' <small>' . esc_html__( '(optional)', 'cariera' ) . '</small>', $field ) ); ?></label>
				<div class="field <?php echo esc_attr( $field['required'] ? 'required-field' : '' ); ?>">
					<?php
					get_job_manager_template(
						'form-fields/' . $field['type'] . '-field.php',
						[
							'key'   => $key,
							'field' => $field,
						]
					);
					?>
				</div>
			</fieldset>
			<?php
		}
	}

	/**
	 * Validate Fields
	 *
	 * @since   1.4.7
	 * @version 1.6.5
	 */
	public function validate_fields( $valid, $fields, $values ) {

		if ( ! $this->integration_enabled() ) {
			return;
		}

		if ( ! isset( $_POST['company_submission'] ) || is_wp_error( $valid ) || ! $valid ) {
			return $valid;
		}

		$values = $this->get_posted_values();

		if ( $_POST['company_submission'] === 'new_company' ) {
			add_action( 'job_manager_update_job_data', [ $this, 'update_job_form_fields' ], 99, 2 );

			if ( $this->is_required() ) {
				try {
					return $this->get_submit_form()->validate_fields( $values );
				} catch ( \Exception $e ) {
					return new \WP_Error( 'thrown-error', $e->getMessage() );
				}
			}
		} else {
			add_action( 'job_manager_update_job_data', [ $this, 'update_job_form_fields' ], 99, 2 );

			if ( empty( $_POST['company_manager_id'] ) && apply_filters( 'cariera_company_manager_id_required', true ) && $this->is_required() ) {
				return new \WP_Error( 'validation-error', esc_html__( 'Selecting a company is required.', 'cariera' ) );
			}
		}

		return $valid;
	}

	/**
	 * Updating custom fields
	 *
	 * @since  1.3.0
	 */
	public function update_job_form_fields( $job_id, $values ) {

		if ( ! isset( $_POST['company_submission'] ) ) {
			return;
		}

		// Post the values.
		if ( $_POST['company_submission'] === 'new_company' ) {

			$values = $this->get_posted_values();

			if ( empty( $values['company_fields']['company_name'] ) && ! $this->is_required() ) {
				return;
			}

			if ( ! empty( $values ) ) {
				$post_id    = get_post_meta( $job_id, '_company_manager_id', true );
				$company_id = ! empty( $post_id ) ? $post_id : 0;

				if ( $company_id == 0 ) {
					$company_id = $this->get_submit_form()->save_company( $values['company_fields']['company_name'], $values['company_fields']['company_content'], get_option( 'cariera_company_submission_requires_approval' ) ? 'pending' : 'publish', $values );
					$this->get_submit_form()->update_company_data( $values );
				} else {
					$company_id = $this->get_submit_form()->save_company( $values['company_fields']['company_name'], $values['company_fields']['company_content'], get_option( 'cariera_company_submission_requires_approval' ) ? 'pending' : 'publish', $values, $company_id );
					$this->get_submit_form()->update_company_data( $values );
				}
			}

			update_post_meta( $job_id, '_company_manager_id', $company_id );
		} else {
			if ( ! empty( $_POST['company_manager_id'] ) ) {
				$company_id = absint( $_POST['company_manager_id'] );

				update_post_meta( $job_id, '_company_manager_id', $company_id );
			}
		}
	}

	/**
	 * Init the "\Cariera_Core\Core\Company_Manager\Forms\Submit_Company" class
	 *
	 * @since 1.3.0
	 */
	public function get_submit_form() {
		if ( ! $this->submit_instance ) {
			$this->submit_instance = \Cariera_Core\Core\Company_Manager\Forms\Submit_Company::instance();
		}

		return $this->submit_instance;
	}

	/**
	 * Get posted company values
	 *
	 * @since 1.4.7
	 */
	public function get_posted_values() {

		if ( empty( $this->values ) ) {
			// Init fields.
			$this->get_submit_form()->init_fields();

			// Get posted values.
			$this->values = $this->get_submit_form()->job_submit_get_posted_fields();
		}

		return $this->values;
	}

	/**
	 * "_company_name" meta migration to "_company_manager_id"
	 *
	 * @since 1.4.7
	 */
	public function company_metakey_migration() {
		// Get all job posts.
		$job_listing = get_posts(
			[
				'numberposts' => -1,
				'post_type'   => 'job_listing',
			]
		);

		// Stop the migration if there are no job listings.
		if ( ! $this->integration_enabled() || empty( $job_listing ) ) {
			return;
		}

		foreach ( $job_listing as $job ) {
			setup_postdata( $job );

			// Get company_name meta.
			$meta = get_post_meta( $job->ID, '_company_name', true );

			if ( ! empty( $meta ) ) {
				$company    = cariera_get_page_by_title( $meta, 'company' );
				$company_id = $company->ID;

				// Update new Meta.
				update_post_meta( $job->ID, '_company_manager_id', $company_id );

				// Delete old Meta.
				// delete_post_meta( $job->ID, '_company_name' );
			}
		}

		if ( ! empty( $job_listing ) ) {
			update_option( 'cariera_company_metakey_migration_init', 1 );
		}
	}

	/**
	 * Get the Company Name from the job's company_manager_id and filter it.
	 *
	 * @since   1.4.7
	 * @version 1.5.4
	 */
	public function the_company_name( $company_name, $post ) {
		$company_id = cariera_get_the_company( $post );

		if ( ! empty( $company_id ) ) {
			$company_name = get_the_title( $company_id );
		} else {
			$company_name = '';
		}

		return $company_name;
	}

	/**
	 * Gets the company website from the job's company_manager_id and filter it.
	 *
	 * @since 1.4.7
	 * @version 1.5.4
	 */
	public function the_company_website( $website, $post ) {
		$company_id = cariera_get_the_company( $post );

		return get_post_meta( $company_id, '_company_website', true );
	}

	/**
	 * Gets the company twitter from the job's company_manager_id and filter it.
	 *
	 * @since   1.4.7
	 * @version 1.5.4
	 */
	public function the_company_twitter( $twitter, $post ) {
		$company_id = cariera_get_the_company( $post );

		return get_post_meta( $company_id, '_company_twitter', true );
	}

	/**
	 * Gets the company tagline from the job's company_manager_id and filter it.
	 *
	 * @since   1.4.7
	 * @version 1.5.4
	 */
	public function the_company_tagline( $tagline, $post ) {
		$company_id = cariera_get_the_company( $post );

		return get_post_meta( $company_id, '_company_tagline', true );
	}

	/**
	 * Gets the company video from the job's company_manager_id and filter it.
	 *
	 * @since   1.4.7
	 * @version 1.5.4
	 */
	public function the_company_video( $video, $post ) {
		$company_id = cariera_get_the_company( $post );

		return get_post_meta( $company_id, '_company_video', true );
	}

	/**
	 * Get Job Listings Query Args
	 *
	 * This is a temporary workaround for passing company_id_XXX in search_keywords query argument,
	 * to allow showing specific company listings. Copied from wp-company-manager
	 *
	 * @param $query_args
	 * @param $args
	 *
	 * @return mixed
	 * @since 1.7.0
	 */
	public function job_manager_get_listings( $query_args, $args ) {
		if ( ! array_key_exists( 's', $query_args ) || strpos( $query_args['s'], 'company_id_' ) === false ) {
			return $query_args;
		}

		$search_company_id = str_replace( 'company_id_', '', $query_args['s'] );
		if ( empty( $search_company_id ) ) {
			return $query_args;
		}

		$query_args['s'] = '';
		remove_filter( 'posts_search', 'get_job_listings_keyword_search' );
		$query_args['meta_query'] = [
			[
				'key'   => '_company_manager_id',
				'value' => absint( $search_company_id ),
			],
		];

		$query_args['tax_query'] = [];

		return $query_args;
	}
}

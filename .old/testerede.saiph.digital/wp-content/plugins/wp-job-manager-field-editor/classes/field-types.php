<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Job_Manager_Field_Editor_Field_Types
 *
 * @since 1.1.9
 *
 */
class WP_Job_Manager_Field_Editor_Field_Types extends WP_Job_Manager_Field_Editor_Fields {

	/**
	 * @var
	 */
	private static $instance;
	/**
	 * @var array
	 */
	private       $available_types    = array();
	/**
	 * @var bool
	 */
	public static $show_req_opt_label = false;
	/**
	 * @var bool
	 */
	public static $desc_below_field = false;

	/**
	 * WP_Job_Manager_Field_Editor_Field_Types constructor.
	 */
	function __construct() {

		add_action( 'job_manager_input_text', array( $this, 'admin_input_text' ), 10, 2 );
		add_action( 'job_manager_input_radio', array( $this, 'admin_input_radio' ), 10, 2 );
		add_action( 'job_manager_input_date', array( $this, 'admin_input_date' ), 10, 2 );
		add_action( 'job_manager_input_fpdate', array( $this, 'admin_input_fpdate' ), 10, 2 );
		add_action( 'job_manager_input_fptime', array( $this, 'admin_input_fptime' ), 10, 2 );
		add_action( 'job_manager_input_phone', array( $this, 'admin_input_phone' ), 10, 2 );
		add_action( 'job_manager_input_select', array( $this, 'admin_input_select' ), 10, 2 );
		add_action( 'job_manager_input_multiselect', array( $this, 'admin_input_multiselect' ), 10, 2 );
		add_action( 'job_manager_input_header', array( $this, 'admin_input_header' ), 10, 2 );
		add_action( 'job_manager_input_html', array( $this, 'admin_input_html' ), 10, 2 );
		add_action( 'job_manager_input_actionhook', array( $this, 'admin_input_actionhook' ), 10, 2 );
		add_action( 'job_manager_input_number', array( $this, 'admin_input_number' ), 10, 2 );
		add_action( 'job_manager_input_range', array( $this, 'admin_input_range' ), 10, 2 );
		add_action( 'job_manager_input_email', array( $this, 'admin_input_email' ), 10, 2 );
		add_action( 'job_manager_input_url', array( $this, 'admin_input_url' ), 10, 2 );
		add_action( 'job_manager_input_tel', array( $this, 'admin_input_tel' ), 10, 2 );
		add_action( 'job_manager_input_autocomplete', array( $this, 'admin_input_autocomplete' ), 10, 2 );
		add_action( 'job_manager_input_checklist', array( $this, 'admin_input_checklist' ), 10, 2 );

		add_action( 'resume_manager_input_text', array( $this, 'admin_input_text' ), 10, 2 );
		add_action( 'resume_manager_input_radio', array( $this, 'admin_input_radio' ), 10, 2 );
		add_action( 'resume_manager_input_date', array( $this, 'admin_input_date' ), 10, 2 );
		add_action( 'resume_manager_input_fpdate', array( $this, 'admin_input_fpdate' ), 10, 2 );
		add_action( 'resume_manager_input_fptime', array( $this, 'admin_input_fptime' ), 10, 2 );
		add_action( 'resume_manager_input_phone', array( $this, 'admin_input_phone' ), 10, 2 );
		add_action( 'resume_manager_input_select', array( $this, 'admin_input_select' ), 10, 2 );
		add_action( 'resume_manager_input_multiselect', array( $this, 'admin_input_multiselect' ), 10, 2 );
		add_action( 'resume_manager_input_header', array( $this, 'admin_input_header' ), 10, 2 );
		add_action( 'resume_manager_input_html', array( $this, 'admin_input_html' ), 10, 2 );
		add_action( 'resume_manager_input_actionhook', array( $this, 'admin_input_actionhook' ), 10, 2 );
		add_action( 'resume_manager_input_number', array( $this, 'admin_input_number' ), 10, 2 );
		add_action( 'resume_manager_input_range', array( $this, 'admin_input_range' ), 10, 2 );
		add_action( 'resume_manager_input_email', array( $this, 'admin_input_email' ), 10, 2 );
		add_action( 'resume_manager_input_url', array( $this, 'admin_input_url' ), 10, 2 );
		add_action( 'resume_manager_input_tel', array( $this, 'admin_input_tel' ), 10, 2 );
		add_action( 'resume_manager_input_autocomplete', array( $this, 'admin_input_autocomplete' ), 10, 2 );
		add_action( 'resume_manager_input_checklist', array( $this, 'admin_input_checklist' ), 10, 2 );

		add_action( 'company_manager_input_text', array( $this, 'admin_input_text' ), 10, 2 );
		add_action( 'company_manager_input_radio', array( $this, 'admin_input_radio' ), 10, 2 );
		add_action( 'company_manager_input_date', array( $this, 'admin_input_date' ), 10, 2 );
		add_action( 'company_manager_input_fpdate', array( $this, 'admin_input_fpdate' ), 10, 2 );
		add_action( 'company_manager_input_fptime', array( $this, 'admin_input_fptime' ), 10, 2 );
		add_action( 'company_manager_input_phone', array( $this, 'admin_input_phone' ), 10, 2 );
		add_action( 'company_manager_input_select', array( $this, 'admin_input_select' ), 10, 2 );
		add_action( 'company_manager_input_multiselect', array( $this, 'admin_input_multiselect' ), 10, 2 );
		add_action( 'company_manager_input_header', array( $this, 'admin_input_header' ), 10, 2 );
		add_action( 'company_manager_input_html', array( $this, 'admin_input_html' ), 10, 2 );
		add_action( 'company_manager_input_actionhook', array( $this, 'admin_input_actionhook' ), 10, 2 );
		add_action( 'company_manager_input_number', array( $this, 'admin_input_number' ), 10, 2 );
		add_action( 'company_manager_input_range', array( $this, 'admin_input_range' ), 10, 2 );
		add_action( 'company_manager_input_email', array( $this, 'admin_input_email' ), 10, 2 );
		add_action( 'company_manager_input_url', array( $this, 'admin_input_url' ), 10, 2 );
		add_action( 'company_manager_input_tel', array( $this, 'admin_input_tel' ), 10, 2 );
		add_action( 'company_manager_input_autocomplete', array( $this, 'admin_input_autocomplete' ), 10, 2 );
		add_action( 'company_manager_input_checklist', array( $this, 'admin_input_checklist' ), 10, 2 );

		self::$show_req_opt_label = get_option( 'jmfe_admin_show_opt_req_label', false );
		self::$desc_below_field = get_option( 'jmfe_admin_show_desc_below_field', false );
	}

	/**
	 * Displays label and text input field.
	 *
	 * @param string $key
	 * @param array  $field
	 */
	public static function admin_input_text( $key, $field ) {

		global $thepostid;

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		if ( ! empty( $field['classes'] ) ) {
			$classes = implode( ' ', is_array( $field['classes'] ) ? $field['classes'] : array( $field['classes'] ) );
		} else {
			$classes = '';
		}
		?>
		<p class="form-field">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
				<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
					endif;
				?>
			</label>
			<?php
				get_job_manager_template( 'form-fields/text-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) );
			?>
		</p>
		<?php
	}

	/**
	 * Get (optional) or (required) label if enabled
	 *
	 * This method returns the custom (or default) optional/required label to output and show in admin area,
	 * if enabled in settings.  Otherwise it will return an empty string.
	 *
	 *
	 * @param $field
	 *
	 * @return string
	 * @since 1.8.19
	 *
	 */
	public static function maybe_get_opt_req_label( $field ){
		return self::$show_req_opt_label ? wp_kses_post( apply_filters( 'submit_job_form_required_label', isset( $field['required'] ) && ! empty( $field['required'] ) ? '' : ' <small>' . __( '(optional)', 'wp-job-manager-field-editor' ) . '</small>', $field ) ) : '';
	}

	/**
	 * Output Checklist Inputs
	 *
	 *
	 * @since 1.8.3
	 *
	 * @param $key
	 * @param $field
	 */
	public static function admin_input_checklist( $key, $field ) {

		global $thepostid;

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		if ( ! empty( $field['classes'] ) ) {
			$classes = implode( ' ', is_array( $field['classes'] ) ? $field['classes'] : array( $field['classes'] ) );
		} else {
			$classes = '';
		}
		?>
		<div class="form-field form-field-checklist">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
				<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
					endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/checklist-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) ); ?>
		</div>
		<?php
	}

	/**
	 * Output AutoComplete Input
	 *
	 *
	 * @since 1.8.3
	 *
	 * @param $key
	 * @param $field
	 */
	public static function admin_input_autocomplete( $key, $field ) {

		global $thepostid;

		if ( ! isset( $field['value'] ) ) {
			$field['value'] = get_post_meta( $thepostid, $key, true );
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		if ( ! empty( $field['classes'] ) ) {
			$classes = implode( ' ', is_array( $field['classes'] ) ? $field['classes'] : array( $field['classes'] ) );
		} else {
			$classes = '';
		}
		?>
		<p class="form-field form-field-autocomplete">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
				<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
					endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/autocomplete-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) ); ?>
		</p>
		<?php
	}

	/**
	 * input_actionhook function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 *
	 * @since 1.3.0
	 *
	 */
	public function admin_input_actionhook( $key, $field ) {

		global $thepostid;
		?>
		<p class="form-field form-field-actionhook">
			<label for="<?php echo esc_attr( $key ); ?>"><?php if ( ! empty( $field[ 'label' ] ) ) echo esc_html( $field[ 'label' ] ) . ':'; ?></label>
			<?php get_job_manager_template( 'form-fields/actionhook-field.php', array('key' => $key, 'field' => $field, 'admin' => TRUE) ); ?>
		</p>
		<?php
	}

	/**
	 * input_html function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 * @since 1.3.0
	 *
	 */
	public function admin_input_html( $key, $field ) {

		global $thepostid;
		?>
		<p class="form-field form-field-html">
			<label for="<?php echo esc_attr( $key ); ?>"><?php if ( ! empty( $field[ 'label' ] ) ) echo esc_html( $field[ 'label' ] ) . ':'; ?></label>
			<?php get_job_manager_template( 'form-fields/html-field.php', array('key' => $key, 'field' => $field, 'admin' => TRUE) ); ?>
		</p>
		<?php
	}

	/**
	 * input_header function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 * @since 1.3.0
	 *
	 */
	public function admin_input_header( $key, $field ) {

		global $thepostid;
		?>
		<div class="form-field form-field-header">
			<?php get_job_manager_template( 'form-fields/header-field.php', array('key' => $key, 'field' => $field, 'admin' => TRUE) ); ?>
		</div>
		<?php
	}

	/**
	 * input_select function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function admin_input_multiselect( $key, $field ) {

		global $thepostid;

		if ( empty( $field[ 'value' ] ) ) {
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		}
		?>
		<p class="form-field form-field-multiselect">
			<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
				<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
					endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/multiselect-field.php', array( 'key' => $key, 'field' => $field, 'admin' => TRUE ) ); ?>
		</p>
	<?php
	}

	/**
	 * input_select function.
	 *
	 * @param mixed $key
	 * @param mixed $field
	 */
	public function admin_input_select( $key, $field ) {

		global $thepostid;

		if ( empty( $field[ 'value' ] ) ) {
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		}
		?>
		<p class="form-field form-field-select">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
				<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
					endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/select-field.php', array( 'key' => $key, 'field' => $field, 'admin' => TRUE ) ); ?>
		</p>
	<?php
	}

	/**
	 * HTML5 Email Input
	 *
	 *
	 * @since 1.6.4
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_email( $key, $field ) {

		global $thepostid;

		if ( ! isset( $field[ 'value' ] ) ) {
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		}
		if ( ! empty( $field[ 'name' ] ) ) {
			$name = $field[ 'name' ];
		} else {
			$name = $key;
		}
		if ( ! empty( $field[ 'classes' ] ) ) {
			$classes = implode( ' ', is_array( $field[ 'classes' ] ) ? $field[ 'classes' ] : array($field[ 'classes' ]) );
		} else {
			$classes = '';
		}
		?>
		<p class="form-field form-field-email">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
				<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
					endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/email-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) ); ?>
		</p>
		<?php
	}

	/**
	 * HTML5 URL Input
	 *
	 *
	 * @since 1.6.4
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_url( $key, $field ) {

		global $thepostid;

		if ( ! isset( $field[ 'value' ] ) ) {
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		}
		if ( ! empty( $field[ 'name' ] ) ) {
			$name = $field[ 'name' ];
		} else {
			$name = $key;
		}
		if ( ! empty( $field[ 'classes' ] ) ) {
			$classes = implode( ' ', is_array( $field[ 'classes' ] ) ? $field[ 'classes' ] : array($field[ 'classes' ]) );
		} else {
			$classes = '';
		}
		?>
		<p class="form-field form-field-url">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/url-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) ); ?>
		</p>
		<?php
	}

	/**
	 * HTML5 TEL (Telephone) Input
	 *
	 *
	 * @since 1.6.4
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_tel( $key, $field ) {

		global $thepostid;

		if ( ! isset( $field[ 'value' ] ) ) {
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		}
		if ( ! empty( $field[ 'name' ] ) ) {
			$name = $field[ 'name' ];
		} else {
			$name = $key;
		}
		if ( ! empty( $field[ 'classes' ] ) ) {
			$classes = implode( ' ', is_array( $field[ 'classes' ] ) ? $field[ 'classes' ] : array($field[ 'classes' ]) );
		} else {
			$classes = '';
		}
		?>
		<p class="form-field form-field-tel">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/tel-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) ); ?>
		</p>
		<?php
	}

	/**
	 * Output Number Field Type for Admin WritePanel
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_number( $key, $field ){

		global $thepostid;

		if( empty($field['value']) ) {
			$field['value'] = get_post_meta( $thepostid, $key, TRUE );
		}

		$name = ! empty( $field['name']) ? $field['name'] : $key;

		?>
		<p class="form-field form-field-number">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/number-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) ); ?>
		</p>
		<?php
	}

	/**
	 * Output Range Field Type for Admin WritePanel
	 *
	 *
	 * @since 1.4.0
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_range( $key, $field ) {

		global $thepostid;

		wp_enqueue_script( 'jmfe-range-field' );
		$name    = esc_attr( ! empty($field['name']) ? $field['name'] : $key );
		$min     = isset($field['min']) && is_numeric( $field['min'] ) ? (int) $field['min'] : 0;
		$max     = isset($field['max']) && is_numeric( $field['max'] ) ? (int) $field['max'] : 10;
		$step    = isset($field['step']) && is_numeric( $field['step'] ) ? (int) $field['step'] : 1;
		$prepend = isset($field['prepend']) ? esc_attr( $field['prepend'] ) : '';
		$append  = isset($field['append']) ? esc_attr( $field['append'] ) : '';
		$value   = isset($field['value']) ? $field['value'] : get_post_meta( $thepostid, $key, TRUE );

		// Set value to default if there is no value, or if the value is not a number
		if( isset($field['default']) && ! is_numeric( $value ) ) $value = $field['default'];

		?>
		<p class="form-field form-field-range">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/range-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) ); ?>
		</p>
		<?php
	}

	/**
	 * Output Phone Field Type for Admin WritePanel
	 *
	 *
	 * @since 1.2.1
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_phone( $key, $field ){

		global $thepostid;

		if ( empty( $field[ 'value' ] ) ) {
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		}
		?>
		<p class="form-field form-field-phone">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/phone-field.php', array( 'key' => $key, 'field' => $field, 'admin' => TRUE ) ); ?>
		</p>
	<?php
	}

	/**
	 * Output Radio Input on Admin WritePanel
	 *
	 *
	 * @since 1.1.10
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_radio( $key, $field ) {
		global $thepostid;

		$meta_key = esc_attr( $key );
		if ( empty( $field[ 'value' ] ) )
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
			// Hack for admin section to prevent errors on save for null fields
			if( $field[ 'value' ] === '' ) {
				$field['value'] = 'none';
			}
		?>
		<p class="form-field form-field-radio">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<input type="radio" style="margin-left: 5px; margin-right: 5px; width: auto;" data-meta_key="<?php echo $key; ?>" class="jmfe-radio jmfe-radio-<?php echo $key; ?> input-radio" name="<?php echo esc_attr( isset( $field[ 'name' ] ) ? $field[ 'name' ] : $key ); ?>" id="<?php echo $key . '-none'; ?>" value="" <?php if ( isset( $field[ 'value' ] ) || isset( $field[ 'default' ] ) ) checked( isset( $field[ 'value' ] ) ? $field[ 'value' ] : $field[ 'default' ], 'none', TRUE ); ?> />
			<strong><?php _e( 'None', 'wp-job-manager-field-editor' ); ?></strong>
			<?php get_job_manager_template( 'form-fields/radio-field.php', array( 'key' => $key, 'field' => $field, 'admin' => true ) ); ?>
		</p>
	<?php
	}

	/**
	 * Output Date Picker Field Type for Admin WritePanel
	 *
	 *
	 * @since 1.1.14
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_date( $key, $field ){

		global $thepostid;

		if ( empty( $field[ 'value' ] ) )
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		?>
		<p class="form-field form-field-date">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/date-field.php', array( 'key' => $key, 'field' => $field, 'admin' => TRUE ) ); ?>
		</p>
		<?php

	}

	/**
	 * Output Flatpickr Date Picker Field Type for Admin WritePanel
	 *
	 *
	 * @since @@since
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_fpdate( $key, $field ){

		global $thepostid;

		if ( empty( $field[ 'value' ] ) )
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		?>
		<p class="form-field form-field-date form-field-fpdate">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/fpdate-field.php', array( 'key' => $key, 'field' => $field, 'admin' => TRUE ) ); ?>
		</p>
		<?php

	}

	/**
	 * Output Flatpickr Time Picker Field Type for Admin WritePanel
	 *
	 *
	 * @since @@since
	 *
	 * @param $key
	 * @param $field
	 */
	public function admin_input_fptime( $key, $field ){

		global $thepostid;

		if ( empty( $field[ 'value' ] ) )
			$field[ 'value' ] = get_post_meta( $thepostid, $key, TRUE );
		?>
		<p class="form-field form-field-date form-field-fptime">
			<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $field['label'] ) . self::maybe_get_opt_req_label( $field ); ?>:
				<?php if ( ! self::$desc_below_field && ! empty( $field['description'] ) ) : ?>
					<span class="tips" data-tip="<?php echo esc_attr( $field['description'] ); ?>">[?]</span>
					<?php
					unset( $field['description'] ); // Unset as we are showing in native WPJM [?]
				endif;
				?>
			</label>
			<?php get_job_manager_template( 'form-fields/fptime-field.php', array( 'key' => $key, 'field' => $field, 'admin' => TRUE ) ); ?>
		</p>
		<?php

	}

	/**
	 * Check if Field Type exists
	 *
	 * @since 1.1.9
	 *
	 * @param $field_type
	 *
	 * @return bool
	 */
	function is_valid_type( $field_type ) {

		if ( array_key_exists( $field_type, $this->get_field_types( true ) ) ) return true;

		return false;
	}

	/**
	 * Get Available Field Types
	 *
	 * Based on available templates, and WPJM version, will
	 * return the possible field types that are available.
	 *
	 * @since 1.1.9
	 *
	 * @param bool $as_array Return field types as array
	 *
	 * @param null $list_field_group
	 *
	 * @return string
	 */
	function get_field_types( $as_array = false, $list_field_group = null ) {

		$field_types = array(
			'section_html5'    => '---' . __( 'HTML5 Field Types', 'wp-job-manager-field-editor' ),
			'number'           => __( 'HTML5 - Number Spinner', 'wp-job-manager-field-editor' ),
			'range'            => __( 'HTML5 - Range Slider', 'wp-job-manager-field-editor' ),
			'email'            => __( 'HTML5 - Email', 'wp-job-manager-field-editor' ),
			'url'              => __( 'HTML5 - URL', 'wp-job-manager-field-editor' ),
			'tel'              => __( 'HTML5 - Telephone', 'wp-job-manager-field-editor' ),
			'section_standard' => '---' . __( 'Standard Field Types', 'wp-job-manager-field-editor' ),
			'text'             => __( 'Text Box', 'wp-job-manager-field-editor' ),
			'textarea'         => __( 'Text Area', 'wp-job-manager-field-editor' ),
			'wp-editor'        => __( 'WP Editor', 'wp-job-manager-field-editor' ),
			'select'           => __( 'Dropdown', 'wp-job-manager-field-editor' ),
			'file'             => __( 'File Upload', 'wp-job-manager-field-editor' ),
			'password'         => __( 'Password Text Box', 'wp-job-manager-field-editor' ),
			'radio'            => __( 'Radio Buttons', 'wp-job-manager-field-editor' ),
			'checklist'        => __( 'Checklist (Multiple Checkboxes)', 'wp-job-manager-field-editor' ),
			'date'             => __( 'Date Picker (jQuery UI)', 'wp-job-manager-field-editor' ),
			'fpdate'           => __( 'Date Picker (flatpickr)', 'wp-job-manager-field-editor' ),
			'fptime'           => __( 'Time Picker (flatpickr)', 'wp-job-manager-field-editor' ),
			'phone'            => __( 'Phone Number (intl-tel-input.com plugin)', 'wp-job-manager-field-editor' ),
			'hidden'           => __( 'Frontend Hidden Input (text box in admin)', 'wp-job-manager-field-editor' ),
			'autocomplete'     => __( 'Google Maps - Places Auto Complete', 'wp-job-manager-field-editor' )
		);

		$field_types = $this->add_other_field_types( $field_types, $list_field_group );

		$field_types = apply_filters( 'field_editor_field_types', $field_types );

		if ( ! $as_array ) $field_types = $this->options()->convert( $field_types );

		return $field_types;
	}

	/**
	 * Add field types that do not save values
	 *
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	function add_no_value_field_types(){

		$no_value_types = array(
			'section_novalue' => '---' . __( 'No Value Field Types', 'wp-job-manager-field-editor' ),
			'header'          => __( 'Section Header', 'wp-job-manager-field-editor' ),
			'html'            => __( 'HTML', 'wp-job-manager-field-editor' ),
			'actionhook'      => __( 'WordPress Action Hook', 'wp-job-manager-field-editor' ),
		);

		$this->available_types = array_merge( $this->available_types, $no_value_types );

		return $this->available_types;
	}

	/**
	 * Add version specific field types
	 *
	 * @since 1.1.9
	 *
	 * @param $field_types
	 *
	 * @return array
	 */
	function add_other_field_types( $field_types, $list_field_group = null) {

		if( $list_field_group ){

			switch( $list_field_group ){

				case 'job':
					$this->wpjm();
					break;

				case 'company':
					$this->wpjm();
					break;
				case 'company_fields':
					$this->wpjm();
					break;
				case 'resume_fields':
					$this->wpjm();
					$this->wprm();
					break;

			}

		}

		$this->add_no_value_field_types();

		return array_merge( $field_types, $this->available_types );

	}

	/**
	 * WP Job Manager Field Types
	 *
	 * Will return the available field types based on the
	 * currently installed version of WP Job Manager.
	 *
	 * @since 1.1.9
	 *
	 * @return array
	 */
	function wpjm() {

		$wpjm_types = array(
			'1.15.0' => array(
				'checkbox' => __( 'Checkbox (Single Checkbox)', 'wp-job-manager-field-editor' )
			),
			'1.14.0' => array(
				'multiselect' => __( 'Multi-Select', 'wp-job-manager-field-editor' ),
				'taxonomy_field_type' => '---' . __( 'Taxonomy Field Types', 'wp-job-manager-field-editor' ),
				'term-checklist'   => __( 'Taxonomy Checklist', 'wp-job-manager-field-editor' ),
				'term-select'      => __( 'Taxonomy Dropdown', 'wp-job-manager-field-editor' ),
				'term-multiselect' => __( 'Taxonomy Multi-Select Dropdown', 'wp-job-manager-field-editor' )
			)
		);

		foreach ( $wpjm_types as $version => $types ) {

			if ( version_compare( JOB_MANAGER_VERSION, $version, 'ge' ) ) {
				$this->available_types = array_merge( $this->available_types, $types );
			}

		}

		return $this->available_types;

	}

	/**
	 * WP Job Manager Resumes Field Types
	 *
	 * Will return the available field types based on the
	 * currently installed version of WP Job Manager.
	 *
	 * @since 1.1.9
	 *
	 * @return array
	 */
	function wprm() {

		$wprm_types = array(
			'1.7.0' => array(
				'taxonomy_field_type' => '---' . __( 'Taxonomy Field Types', 'wp-job-manager-field-editor' ),
				'term-checklist'   => __( 'Taxonomy Checklist', 'wp-job-manager-field-editor' ),
				'term-select'      => __( 'Taxonomy Dropdown', 'wp-job-manager-field-editor' ),
				'term-multiselect' => __( 'Taxonomy Multi-Select Dropdown', 'wp-job-manager-field-editor' )
			),
		);

		foreach ( $wprm_types as $version => $types ) {

			if ( version_compare( RESUME_MANAGER_VERSION, $version, 'ge' ) && version_compare( JOB_MANAGER_VERSION, '1.14.0', 'ge' ) ) {
				$this->available_types = array_merge( $this->available_types, $types );
			}

		}

		return $this->available_types;

	}

	/**
	 * Singleton Instance
	 *
	 * @since 1.0.0
	 *
	 * @return wp_job_manager_field_editor
	 */
	static function get_instance() {

		if ( null == self::$instance ) self::$instance = new self;

		return self::$instance;
	}

}

WP_Job_Manager_Field_Editor_Field_Types::get_instance();
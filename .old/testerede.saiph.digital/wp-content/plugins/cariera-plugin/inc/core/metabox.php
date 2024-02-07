<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Metabox {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 *
	 * @since 1.5.3
	 */
	public function __construct() {
		// Register Metaboxes.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		// Save Data.
		add_action( 'save_post', [ $this, 'save_page' ], 10, 2 );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
		add_action( 'save_post', [ $this, 'save_testimonial' ], 10, 2 );
	}

	/**
	 * Register Metaboxes
	 *
	 * @since 1.5.3
	 */
	public function add_meta_boxes() {
		// Pages.
		add_meta_box(
			'cariera_page_data',
			_x( 'Page Settings', 'Pages data options in wp-admin', 'cariera' ),
			[ $this, 'meta_boxes_page' ],
			'page',
			'normal',
			'high'
		);

		// Posts.
		add_meta_box(
			'cariera_post_data',
			_x( 'Post Settings', 'Posts data options in wp-admin', 'cariera' ),
			[ $this, 'meta_boxes_post' ],
			'post',
			'normal',
			'high'
		);

		// Testimonials.
		add_meta_box(
			'cariera_testimonial_data',
			_x( 'Testimonial Settings', 'Testimonials data options in wp-admin', 'cariera' ),
			[ $this, 'meta_boxes_testimonial' ],
			'testimonial',
			'normal',
			'high'
		);
	}

	/**
	 * Displays metadata fields for Pages.
	 *
	 * @since 1.5.3
	 */
	public function meta_boxes_page( $post ) {
		global $post, $thepostid, $wp_post_types;

		$thepostid = $post->ID;

		echo '<div class="cariera_meta_data">';

		wp_nonce_field( 'save_meta_data', 'cariera_meta_nonce' );

		foreach ( $this->page_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			// Fix not saving fields.
			if ( ! isset( $field['value'] ) && metadata_exists( 'post', $thepostid, $key ) ) {
				$field['value'] = get_post_meta( $thepostid, $key, true );
			}

			if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
				$field['value'] = $field['default'];
			} elseif ( ! isset( $field['value'] ) ) {
				$field['value'] = '';
			}

			if ( has_action( 'cariera_input_' . $type ) ) {
				do_action( 'cariera_input_' . $type, $key, $field );
			} elseif ( method_exists( $this, 'input_' . $type ) ) {
				call_user_func( [ $this, 'input_' . $type ], $key, $field );
			}
		}

		echo '</div>';
	}

	/**
	 * Displays metadata fields for Posts.
	 *
	 * @since 1.5.3
	 */
	public function meta_boxes_post( $post ) {
		global $post, $thepostid, $wp_post_types;

		$thepostid = $post->ID;

		echo '<div class="cariera_meta_data">';

		wp_nonce_field( 'save_meta_data', 'cariera_meta_nonce' );

		foreach ( $this->post_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			// Fix not saving fields.
			if ( ! isset( $field['value'] ) && metadata_exists( 'post', $thepostid, $key ) ) {
				$field['value'] = get_post_meta( $thepostid, $key, true );
			}

			if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
				$field['value'] = $field['default'];
			} elseif ( ! isset( $field['value'] ) ) {
				$field['value'] = '';
			}

			if ( has_action( 'cariera_input_' . $type ) ) {
				do_action( 'cariera_input_' . $type, $key, $field );
			} elseif ( method_exists( $this, 'input_' . $type ) ) {
				call_user_func( [ $this, 'input_' . $type ], $key, $field );
			}
		}

		echo '</div>';
	}

	/**
	 * Displays metadata fields for Testimonials.
	 *
	 * @since 1.5.3
	 */
	public function meta_boxes_testimonial( $post ) {
		global $post, $thepostid, $wp_post_types;

		$thepostid = $post->ID;

		echo '<div class="cariera_meta_data">';

		wp_nonce_field( 'save_meta_data', 'cariera_meta_nonce' );

		foreach ( $this->testimonial_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			// Fix not saving fields.
			if ( ! isset( $field['value'] ) && metadata_exists( 'post', $thepostid, $key ) ) {
				$field['value'] = get_post_meta( $thepostid, $key, true );
			}

			if ( ! isset( $field['value'] ) && isset( $field['default'] ) ) {
				$field['value'] = $field['default'];
			} elseif ( ! isset( $field['value'] ) ) {
				$field['value'] = '';
			}

			if ( has_action( 'cariera_input_' . $type ) ) {
				do_action( 'cariera_input_' . $type, $key, $field );
			} elseif ( method_exists( $this, 'input_' . $type ) ) {
				call_user_func( [ $this, 'input_' . $type ], $key, $field );
			}
		}

		echo '</div>';
	}

	/**
	 * Fields for page meta
	 *
	 * @since 1.5.3
	 */
	public function page_fields() {

		/* get the registered sidebars */
		global $wp_registered_sidebars;

		$sidebars = [];
		foreach ( $wp_registered_sidebars as $id => $sidebar ) {
			$sidebars[ $id ] = $sidebar['name'];
		}

		$fields = apply_filters(
			'cariera_page_meta_fields',
			[
				// MAIN OPTIONS.
				'cariera_main_heading'        => [
					'label'       => esc_html__( 'Main Options', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_show_page_title'     => [
					'label'       => esc_html__( 'Page Header', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Set to "Disable" if you want to hide the Page Header on this Page.', 'cariera' ),
					'type'        => 'select',
					'options'     => [
						'show' => 'Enable',
						'hide' => 'Disable',
					],
					'default'     => 'show',
				],
				'cariera_page_header_bg'      => [
					'label'       => esc_html__( 'Page Header Cover Image', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'The header image size should be at least 1600x200px', 'cariera' ),
					'type'        => 'file',
					'multiple'    => 0,
				],
				'cariera_page_layout'         => [
					'label'       => esc_html__( 'Page Layout', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Choose the layout of your page.', 'cariera' ),
					'type'        => 'select',
					'options'     => [
						'fullwidth' => esc_html__( 'Fullwidth', 'cariera' ),
						'sidebar'   => esc_html__( 'With Sidebar', 'cariera' ),
					],
					'default'     => 'fullwidth',
				],
				'cariera_select_page_sidebar' => [
					'label'       => esc_html__( 'Select Sidebar', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'The sidebar will be shown only if you have chose a sidebar layout for the page in the "Page Layout" option.', 'cariera' ),
					'type'        => 'select',
					'options'     => $sidebars,
					'default'     => 'sidebar-1',
				],

				// HEADER OPTIONS.
				'cariera_header_heading'      => [
					'label'       => esc_html__( 'Header Options', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_show_header'         => [
					'label'       => esc_html__( 'Header', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Set to "Disable" if you want to hide the Header on this Page.', 'cariera' ),
					'type'        => 'select',
					'options'     => [
						'show' => 'Enable',
						'hide' => 'Disable',
					],
					'default'     => 'show',
				],

				'cariera_header1_fixed_top'   => [
					'label'       => esc_html__( 'Header 1 - Fixed Top', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'switch',
					'default'     => 0,
				],
				'cariera_header1_transparent' => [
					'label'       => esc_html__( 'Header 1 - Transparent', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'switch',
					'default'     => 0,
				],
				'cariera_header1_white'       => [
					'label'       => esc_html__( 'Header 1 - White Text', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'switch',
					'default'     => 0,
				],

				// FOOTER OPTIONS.
				'cariera_footer_heading'      => [
					'label'       => esc_html__( 'Footer Options', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_show_footer'         => [
					'label'       => esc_html__( 'Footer', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Set to "Disable" if you want to hide the Footer on this Page.', 'cariera' ),
					'type'        => 'select',
					'options'     => [
						'show' => 'Enable',
						'hide' => 'Disable',
					],
					'default'     => 'show',
				],
				'cariera_show_footer_widgets' => [
					'label'       => esc_html__( 'Footer Widget Area', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Set to "Disable" if you want to hide the Footer Widget Area on this Page.', 'cariera' ),
					'type'        => 'select',
					'options'     => [
						'show' => 'Enable',
						'hide' => 'Disable',
					],
					'default'     => 'show',
				],
			]
		);

		return $fields;
	}

	/**
	 * Fields for posts meta
	 *
	 * @since 1.5.3
	 */
	public function post_fields() {

		$fields = apply_filters(
			'cariera_post_meta_fields',
			[
				// Audio Post.
				'cariera_post_audio_heading'   => [
					'label'       => esc_html__( 'Audio Post Options', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_blog_audio'           => [
					'label'       => esc_html__( 'Audio Embed Code', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Please enter the Audio Embed Code here.', 'cariera' ),
					'type'        => 'textarea',
				],

				// Gallery Test.
				'cariera_post_gallery_heading' => [
					'label'       => esc_html__( 'Gallery Post Options', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_blog_gallery'         => [
					'label'       => esc_html__( 'Gallery Images', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'You can upload multiple gallery images for a slideshow', 'cariera' ),
					'type'        => 'file',
					'multiple'    => 1,
				],

				// Quote Post.
				'cariera_post_quote_heading'   => [
					'label'       => esc_html__( 'Quote Post Options', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_blog_quote_author'    => [
					'label'       => esc_html__( 'Quote Author', 'cariera' ),
					'placeholder' => '',
					'description' => '',
				],
				'cariera_blog_quote_source'    => [
					'label'       => esc_html__( 'Quote Source', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Please enter the source (URL) of the quote here.', 'cariera' ),
				],
				'cariera_blog_quote_content'   => [
					'label'       => esc_html__( 'Quote Content', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'textarea',
				],

				// Video Post.
				'cariera_post_video_heading'   => [
					'label'       => esc_html__( 'Video Post Options', 'cariera' ),
					'placeholder' => '',
					'description' => '',
					'type'        => 'heading',
				],
				'cariera_blog_video_embed'     => [
					'label'       => esc_html__( 'Video Embed Code', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Add the full embed code here or the URL of a WordPress supported video site.', 'cariera' ),
					'type'        => 'textarea',
				],
			]
		);

		return $fields;
	}

	/**
	 * Fields for testimonial meta
	 *
	 * @since 1.5.3
	 */
	public function testimonial_fields() {

		$fields = apply_filters(
			'cariera_testimonial_meta_fields',
			[
				'cariera_testimonial_gravatar' => [
					'label'       => esc_html__( 'Gravatar E-mail Address', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Enter in an e-mail address, to use a Gravatar, instead of using the "Featured Image".', 'cariera' ),
				],
				'cariera_testimonial_byline'   => [
					'label'       => esc_html__( 'Byline', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Enter a byline for the customer giving this testimonial (for example: "CEO of Cariera").', 'cariera' ),
				],
				'cariera_testimonial_url'      => [
					'label'       => esc_html__( 'URL', 'cariera' ),
					'placeholder' => '',
					'description' => esc_html__( 'Enter a URL that applies to this customer (for example: http://cariera.co/).', 'cariera' ),
				],
			]
		);

		return $fields;
	}

	/**
	 * Save Page Meta Data
	 *
	 * @since   1.5.3
	 */
	public function save_page( $post_id, $post ) {

		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( empty( $_POST['cariera_meta_nonce'] ) || ! wp_verify_nonce( $_POST['cariera_meta_nonce'], 'save_meta_data' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( 'page' !== $post->post_type ) {
			return;
		}

		// Save Page meta data.
		foreach ( $this->page_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : '';

			switch ( $type ) {
				case 'textarea':
				case 'wp_editor':
				case 'wp-editor':
					update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) );
					break;
				case 'checkbox':
				case 'switch':
					if ( isset( $_POST[ $key ] ) ) {
						update_post_meta( $post_id, $key, 1 );
					} else {
						update_post_meta( $post_id, $key, 0 );
					}
					break;
				case 'heading':
					// nothing.
					break;
				default:
					if ( is_array( $_POST[ $key ] ) ) {
						update_post_meta( $post_id, $key, array_filter( array_map( 'sanitize_text_field', $_POST[ $key ] ) ) );
					} else {
						update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
					}
					break;
			}
		}
	}

	/**
	 * Save Post Meta Data
	 *
	 * @since   1.5.3
	 */
	public function save_post( $post_id, $post ) {

		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( empty( $_POST['cariera_meta_nonce'] ) || ! wp_verify_nonce( $_POST['cariera_meta_nonce'], 'save_meta_data' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( 'post' !== $post->post_type ) {
			return;
		}

		// Save Page meta data.
		foreach ( $this->post_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : '';

			switch ( $type ) {
				case 'textarea':
				case 'wp_editor':
				case 'wp-editor':
					update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) );
					break;
				case 'checkbox':
				case 'switch':
					if ( isset( $_POST[ $key ] ) ) {
						update_post_meta( $post_id, $key, 1 );
					} else {
						update_post_meta( $post_id, $key, 0 );
					}
					break;
				case 'heading':
					// nothing.
					break;
				default:
					if ( is_array( $_POST[ $key ] ) ) {
						update_post_meta( $post_id, $key, array_filter( array_map( 'sanitize_text_field', $_POST[ $key ] ) ) );
					} else {
						update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
					}
					break;
			}
		}
	}

	/**
	 * Save Testimonial Meta Data
	 *
	 * @since   1.5.3
	 */
	public function save_testimonial( $post_id, $post ) {

		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( empty( $_POST['cariera_meta_nonce'] ) || ! wp_verify_nonce( $_POST['cariera_meta_nonce'], 'save_meta_data' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( 'testimonial' !== $post->post_type ) {
			return;
		}

		// Save Page meta data.
		foreach ( $this->testimonial_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : '';

			switch ( $type ) {
				case 'textarea':
				case 'wp_editor':
				case 'wp-editor':
					update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) );
					break;
				case 'checkbox':
				case 'switch':
					if ( isset( $_POST[ $key ] ) ) {
						update_post_meta( $post_id, $key, 1 );
					} else {
						update_post_meta( $post_id, $key, 0 );
					}
					break;
				case 'heading':
					// nothing.
					break;
				default:
					if ( is_array( $_POST[ $key ] ) ) {
						update_post_meta( $post_id, $key, array_filter( array_map( 'sanitize_text_field', $_POST[ $key ] ) ) );
					} else {
						update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
					}
					break;
			}
		}
	}

	/**
	 * Field Type: Heading
	 *
	 * @since 1.5.3
	 */
	public static function input_heading( $key, $field ) {
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		} ?>

		<div class="cariera-form-group field-heading">
			<div class="heading">
				<span class="meta-name"><?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Field Type: Text
	 *
	 * @since 1.5.3
	 */
	public static function input_text( $key, $field ) {
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		if ( ! empty( $field['classes'] ) ) {
			$classes = implode( ' ', is_array( $field['classes'] ) ? $field['classes'] : [ $field['classes'] ] );
		} else {
			$classes = '';
		}
		?>

		<div class="cariera-form-group field-text">
			<div class="meta-heading">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
				</label>
			</div>
			<div class="meta-field">
				<input type="text" autocomplete="off" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />            
			</div>
			<?php if ( ! empty( $field['description'] ) ) { ?>
				<div class="meta-description">
					<p><?php echo esc_attr( $field['description'] ); ?></p>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Field Type: Select
	 *
	 * @since 1.5.3
	 */
	public static function input_select( $key, $field ) {
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>

		<div class="cariera-form-group field-select">
			<div class="meta-heading">
				<label for="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
				</label>
			</div>
			<div class="meta-field">
				<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>">
					<?php foreach ( $field['options'] as $key => $value ) : ?>
						<option
							value="<?php echo esc_attr( $key ); ?>"
							<?php
							if ( isset( $field['value'] ) ) {
								selected( $field['value'], $key );
							}
							?>
						><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php if ( ! empty( $field['description'] ) ) { ?>
				<div class="meta-description">
					<p><?php echo esc_attr( $field['description'] ); ?></p>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Field Type: Switch
	 *
	 * @since 1.5.3
	 */
	public static function input_switch( $key, $field ) {
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>

		<div class="cariera-form-group field-switch">
			<div class="meta-heading">
				<label for="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
				</label>
			</div>
			<div class="meta-field">
				<div class="switch-container">
					<label class="switch">
						<input id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" type="checkbox" value="1" <?php checked( $field['value'], 1 ); ?>/>
						<span class="switch-btn">
							<span data-on="<?php esc_html_e( 'on', 'cariera' ); ?>" data-off="<?php esc_html_e( 'off', 'cariera' ); ?>"></span>
						</span>   
					</label>   
					<?php if ( ! empty( $field['description'] ) ) { ?>
						<p class="description"><?php echo esc_attr( $field['description'] ); ?></p>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Field Type: Textarea
	 *
	 * @since 1.5.3
	 */
	public static function input_textarea( $key, $field ) {
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>

		<div class="cariera-form-group field-switch">
			<div class="meta-heading">
				<label for="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
				</label>
			</div>
			<div class="meta-field">
				<textarea name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"><?php echo esc_html( $field['value'] ); ?></textarea>
			</div>
			<?php if ( ! empty( $field['description'] ) ) { ?>
				<div class="meta-description">
					<p><?php echo esc_attr( $field['description'] ); ?></p>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * File input field
	 *
	 * @since 1.5.3
	 */
	private static function file_url_field( $key, $name, $placeholder, $value, $multiple ) {
		$name = esc_attr( $name );
		if ( $multiple ) {
			$name = $name . '[]';
		}
		?>

		<span class="file_url">
			<input type="text" name="<?php echo esc_attr( $name ); ?>" <?php if ( ! $multiple ) { echo 'id="' . esc_attr( $key ) . '"'; } ?> placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			<button class="button button-small cariera_upload_file_button" data-uploader_button_text="<?php esc_attr_e( 'Use file', 'cariera' ); ?>"><?php esc_html_e( 'Upload', 'cariera' ); ?></button>
			<button class="button button-small cariera_view_file_button"><?php esc_html_e( 'View', 'cariera' ); ?></button>
		</span>
		<?php
	}

	/**
	 * Field Type: File
	 *
	 * @since 1.5.3
	 */
	public static function input_file( $key, $field ) {
		global $post;

		if ( empty( $field['placeholder'] ) ) {
			$field['placeholder'] = 'https://';
		}
		if ( ! empty( $field['name'] ) ) {
			$name = $field['name'];
		} else {
			$name = $key;
		}
		?>

		<div class="cariera-form-group field-file">
			<div class="meta-heading">
				<label for="<?php echo esc_attr( $key ); ?>">
					<?php echo esc_html( wp_strip_all_tags( $field['label'] ) ); ?>:
				</label>
			</div>
			<div class="meta-field">
				<?php
				if ( ! empty( $field['multiple'] ) ) {
					foreach ( (array) $field['value'] as $k => $value ) {
						self::file_url_field( $key, $name, $field['placeholder'], $value, true );
					}
				} else {
					self::file_url_field( $key, $name, $field['placeholder'], $field['value'], false );
				}

				if ( ! empty( $field['multiple'] ) ) {
					?>
					<button class="button button-small cariera_add_another_file_button" data-field_name="<?php echo esc_attr( $key ); ?>" data-field_placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" data-uploader_button_text="<?php esc_attr_e( 'Use file', 'cariera' ); ?>" data-uploader_button="<?php esc_attr_e( 'Upload', 'cariera' ); ?>" data-view_button="<?php esc_attr_e( 'View', 'cariera' ); ?>"><?php esc_html_e( 'Add file', 'cariera' ); ?></button>
				<?php } ?>
			</div>
			<?php if ( ! empty( $field['description'] ) ) { ?>
				<div class="meta-description">
					<p><?php echo esc_attr( $field['description'] ); ?></p>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}

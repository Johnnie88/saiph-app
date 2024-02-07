<?php

namespace Cariera_Core\Core\Resume_Manager;

use Cariera_Core\Core\Resume_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Taxonomy extends Resume_Manager {

	/**
	 * Constructor
	 *
	 * @since   1.4.5
	 * @version 1.7.2
	 */
	public function __construct() {
		// Register resume taxonomies.
		add_action( 'init', [ $this, 'register_taxonomies' ] );

		// Add field.
		add_action( 'resume_category_add_form_fields', [ $this, 'category_add_new_meta_field' ], 10, 2 );

		// Edit Field.
		add_action( 'resume_category_edit_form_fields', [ $this, 'category_edit_meta_field' ], 10, 2 );

		// Save field.
		add_action( 'edited_resume_category', [ $this, 'save_taxonomy_custom_meta' ], 10, 2 );
		add_action( 'create_resume_category', [ $this, 'save_taxonomy_custom_meta' ], 10, 2 );
	}

	/**
	 * Register Resume taxonomies
	 *
	 * @since   1.4.6
	 * @version 1.6.2
	 */
	public function register_taxonomies() {
		if ( ! post_type_exists( 'resume' ) ) {
			return;
		}

		$admin_capability = 'manage_resumes';

		// Taxonomies.
		$taxonomies_args = apply_filters(
			'cariera_resume_taxonomies_list',
			[
				'resume_education_level' => [
					'singular' => esc_html__( 'Candidate Education', 'cariera' ),
					'plural'   => esc_html__( 'Candidate Education', 'cariera' ),
					'slug'     => esc_html_x( 'resume-education', 'Candidate education permalink - resave permalinks after changing this', 'cariera' ),
					'enable'   => get_option( 'cariera_resume_manager_enable_education', true ),
				],
				'resume_experience'      => [
					'singular' => esc_html__( 'Candidate Experience', 'cariera' ),
					'plural'   => esc_html__( 'Candidate Experience', 'cariera' ),
					'slug'     => esc_html_x( 'resume-experience', 'Candidate experience permalink - resave permalinks after changing this', 'cariera' ),
					'enable'   => get_option( 'cariera_resume_manager_enable_experience', true ),
				],
			]
		);

		foreach ( $taxonomies_args as $taxonomy_name => $taxonomy_args ) {
			if ( $taxonomy_args['enable'] ) {
				$singular = $taxonomy_args['singular'];
				$plural   = $taxonomy_args['plural'];
				$slug     = $taxonomy_args['slug'];

				$args = apply_filters(
					"register_taxonomy_{$taxonomy_name}_args",
					[
						'hierarchical'          => true,
						'update_count_callback' => '_update_post_term_count',
						'label'                 => $plural,
						'labels'                => [
							'name'              => $plural,
							'singular_name'     => $singular,
							'menu_name'         => ucwords( $plural ),
							'search_items'      => sprintf( esc_html__( 'Search %s', 'cariera' ), $plural ),
							'all_items'         => sprintf( esc_html__( 'All %s', 'cariera' ), $plural ),
							'parent_item'       => sprintf( esc_html__( 'Parent %s', 'cariera' ), $singular ),
							'parent_item_colon' => sprintf( esc_html__( 'Parent %s:', 'cariera' ), $singular ),
							'edit_item'         => sprintf( esc_html__( 'Edit %s', 'cariera' ), $singular ),
							'update_item'       => sprintf( esc_html__( 'Update %s', 'cariera' ), $singular ),
							'add_new_item'      => sprintf( esc_html__( 'Add New %s', 'cariera' ), $singular ),
							'new_item_name'     => sprintf( esc_html__( 'New %s Name', 'cariera' ), $singular ),
						],
						'show_ui'               => true,
						'show_in_rest'          => true,
						'show_tagcloud'         => false,
						'public'                => true,
						'capabilities'          => [
							'manage_terms' => $admin_capability,
							'edit_terms'   => $admin_capability,
							'delete_terms' => $admin_capability,
							'assign_terms' => $admin_capability,
						],
						'rewrite'               => [
							'slug'         => $slug,
							'with_front'   => false,
							'hierarchical' => true,
						],
					]
				);

				register_taxonomy( $taxonomy_name, 'resume', $args );
			}
		}
	}

	/**
	 * Custom Icon field for Job & Resume Categories taxonomy
	 *
	 * @since 1.2.0
	 */
	public function category_add_new_meta_field() {
		wp_enqueue_media();
		// This will add the custom meta field to the add new term page.
		?>

		<div class="form-field">
			<label for="term_meta[background_image]"><?php esc_html_e( 'Category Background Image', 'cariera' ); ?></label>
			<input type="text" name="term_meta[background_image]" id="term_meta[background_image]" value="" style="margin-bottom: 10px;">
			<a type="button" class="button cariera-upload-btn"><?php esc_html_e( 'upload image', 'cariera' ); ?></a>
			<a href="#" class="button cariera_remove_image_button"><?php esc_html_e( 'Remove image', 'cariera' ); ?></a>
			<p class="description"><?php esc_html_e( 'Upload or select a background image.', 'cariera' ); ?></p>
		</div>

		<div class="form-field">
			<label for="term_meta[image_icon]"><?php esc_html_e( 'Custom Image Icon', 'cariera' ); ?></label>
			<input type="text" name="term_meta[image_icon]" id="term_meta[image_icon]" value="" style="margin-bottom: 10px;">
			<a type="button" class="button cariera-upload-btn"><?php esc_html_e( 'upload image', 'cariera' ); ?></a>
			<a href="#" class="button cariera_remove_image_button"><?php esc_html_e( 'Remove image', 'cariera' ); ?></a>
			<p class="description"><?php esc_html_e( 'Upload or select a custom image icon.', 'cariera' ); ?></p>
		</div>

		<div class="form-field">
			<label for="term_meta[font_icon]"><?php esc_html_e( 'Category Font Icon', 'cariera' ); ?></label>
			<select class="cariera-icon-select" name="term_meta[font_icon]" id="term_meta[font_icon]" id="">
				<?php
				// Fontawesome icons.
				$fa_icons = cariera_fontawesome_icons_list();
				foreach ( $fa_icons as $key => $value ) {
					echo '<option value="' . $key . '">' . $value . '</option>';
				}

				// Simpleline icons.
				$sl_icons = cariera_simpleline_icons_list();
				foreach ( $sl_icons as $key => $value ) {
					echo '<option value="icon-' . $key . '">' . $value . '</option>';
				}

				// Iconsmind icons.
				if ( get_option( 'cariera_font_iconsmind' ) ) {
					$im_icons = cariera_iconsmind_list();
					foreach ( $im_icons as $key ) {
						echo '<option value="iconsmind-' . $key . '">' . $key . '</option>';
					}
				}
				?>
			</select>
			<p class="description"><?php esc_html_e( 'Icon will be displayed in categories grid view', 'cariera' ); ?></p>
		</div>

		<?php
	}

	/**
	 * Edit Term Page
	 *
	 * @since 1.2.0
	 */
	public function category_edit_meta_field( $term ) {
		wp_enqueue_media();

		// Put the term ID into a variable.
		$t_id = $term->term_id;

		// Retrieve the existing value(s) for this meta field. This returns an array.
		$term_meta = get_option( "taxonomy_$t_id" );
		?>

		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[background_image]"><?php esc_html_e( 'Category Background Image', 'cariera' ); ?></label></th>
			<td>
				<input type="text" name="term_meta[background_image]" id="term_meta[background_image]" value="<?php echo ! empty( $term_meta['background_image'] ) ? esc_attr( $term_meta['background_image'] ) : ''; ?>" style="margin-bottom: 10px;">
				<a type="button" class="button cariera-upload-btn"><?php esc_html_e( 'Upload image', 'cariera' ); ?></a>
				<a href="#" class="button cariera_remove_image_button"><?php esc_html_e( 'Remove image', 'cariera' ); ?></a>
				<p class="description"><?php esc_html_e( 'Upload or change the categories background image.', 'cariera' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[image_icon]"><?php esc_html_e( 'Custom Image Icon', 'cariera' ); ?></label></th>
			<td>
				<input type="text" name="term_meta[image_icon]" id="term_meta[image_icon]" value="<?php echo ! empty( $term_meta['image_icon'] ) ? esc_attr( $term_meta['image_icon'] ) : ''; ?>" style="margin-bottom: 10px;">
				<a type="button" class="button cariera-upload-btn"><?php esc_html_e( 'Upload image', 'cariera' ); ?></a>
				<a href="#" class="button cariera_remove_image_button"><?php esc_html_e( 'Remove image', 'cariera' ); ?></a>
				<p class="description"><?php esc_html_e( 'Upload or change the categories icon image.', 'cariera' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top"><label for="term_meta[font_icon]"><?php esc_html_e( 'Category Font Icon', 'cariera' ); ?></label></th>
			<td>
				<select class="cariera-icon-select" name="term_meta[font_icon]" id="term_meta[font_icon]">
					<?php
					$fa_icons = cariera_fontawesome_icons_list();
					foreach ( $fa_icons as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '" ';
						if ( isset( $term_meta['font_icon'] ) && $term_meta['font_icon'] == $key ) {
							echo ' selected="selected"';
						}
						echo '>' . $value . '</option>';
					}

					$sl_icons = cariera_simpleline_icons_list();
					foreach ( $sl_icons as $key => $value ) {
						echo '<option value="icon-' . esc_attr( $key ) . '" ';
						if ( isset( $term_meta['font_icon'] ) && $term_meta['font_icon'] == 'icon-' . $key ) {
							echo ' selected="selected"';
						}
						echo '>' . $value . '</option>';
					}

					if ( get_option( 'cariera_font_iconsmind' ) ) {
						$im_icons = cariera_iconsmind_list();
						foreach ( $im_icons as $key ) {
							echo '<option value="iconsmind-' . esc_attr( $key ) . '" ';
							if ( isset( $term_meta['font_icon'] ) && $term_meta['font_icon'] == 'iconsmind-' . $key ) {
								echo ' selected="selected"';
							}
							echo '>' . $key . '</option>';
						}
					}
					?>
				</select>
				<p class="description"><?php esc_html_e( 'Icon will be displayed in categories grid view', 'cariera' ); ?></p>
			</td>
		</tr>

		<?php
	}

	/**
	 * Save extra taxonomy meta fields callback function.
	 *
	 * @param  mixed $term_id
	 * @return void
	 */
	public function save_taxonomy_custom_meta( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {
			$t_id      = $term_id;
			$term_meta = get_option( "taxonomy_$t_id" );
			$cat_keys  = array_keys( $_POST['term_meta'] );
			foreach ( $cat_keys as $key ) {
				if ( isset( $_POST['term_meta'][ $key ] ) ) {
					$term_meta[ $key ] = $_POST['term_meta'][ $key ];
				}
			}
			// Save the option array.
			update_option( "taxonomy_$t_id", $term_meta );
		}
	}
}

<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPT {

	const PERMALINK_OPTION_NAME = 'cariera_company_core_permalinks';

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register listing post type and custom post statuses.
		add_action( 'init', [ $this, 'register_post_types' ], 0 );

		// Register listing taxonomies.
		add_action( 'init', [ $this, 'register_taxonomies' ], 0 );

		// Register listing meta.
		add_action( 'init', [ $this, 'register_meta_fields' ] );

		// Add screens for wpjm scripts and enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 20 );
		add_filter( 'job_manager_admin_screen_ids', [ $this, 'add_screen_ids' ] );

		add_filter( 'manage_company_posts_columns', [ $this, 'custom_company_columns' ] );
		add_action( 'manage_company_posts_custom_column', [ $this, 'custom_company_column' ], 10, 2 );
		add_filter( 'admin_head', [ $this, 'admin_head' ] );
		add_action( 'admin_init', [ $this, 'approve_company' ] );
		if ( cariera_discourage_company_search_indexing() ) {
			add_filter( 'wp_head', [ $this, 'add_no_robots' ] );
		}

		add_action( 'update_post_meta', [ $this, 'maybe_update_menu_order' ], 10, 4 );

		// Add settings link to plugins page.
		add_action( 'current_screen', [ $this, 'conditional_includes' ] );

		// Admin Post Statuses.
		foreach ( [ 'post', 'post-new' ] as $hook ) {
			add_action( "admin_footer-{$hook}.php", [ $this, 'extend_submitdiv_post_status' ] );
		}

		// Flush Cache.
		add_action( 'save_post', [ $this, 'flush_get_company_listings_cache' ] );
		add_action( 'delete_post', [ $this, 'flush_get_company_listings_cache' ] );
		add_action( 'trash_post', [ $this, 'flush_get_company_listings_cache' ] );

		add_action( 'cariera_my_company_do_action', [ $this, 'cariera_my_company_do_action' ] );

		// Remove listings when a user get's deleted.
		add_filter( 'post_types_to_delete_with_user', [ $this, 'delete_listings_with_user' ], 10 );
	}

	/**
	 * Flush the cache
	 *
	 * @since 1.5.0
	 */
	public function flush_get_company_listings_cache( $post_id ) {
		if ( 'company' === get_post_type( $post_id ) ) {
			\WP_Job_Manager_Cache_Helper::get_transient_version( 'cariera_get_company_listings', true );
		}
	}

	/**
	 * Flush the cache
	 *
	 * @since 1.5.0
	 */
	public function cariera_my_company_do_action( $action ) {
		\WP_Job_Manager_Cache_Helper::get_transient_version( 'cariera_get_company_listings', true );
	}

	/**
	 * When a user gets deleted, also remove his listings.
	 *
	 * @since 1.5.1
	 */
	public function delete_listings_with_user( $types ) {
		$types[] = 'company';

		return $types;
	}

	/**
	 * Enqueue admin files.
	 *
	 * @since   1.3.0
	 * @version 1.5.4
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( in_array( $screen->id, apply_filters( 'cariera_company_admin_screen_ids', [ 'edit-company', 'plugins', 'company' ] ), true ) ) {
			wp_enqueue_style( 'job_manager_admin_css', JOB_MANAGER_PLUGIN_URL . '/assets/css/admin.css', [], JOB_MANAGER_VERSION );
			wp_enqueue_script( 'job_manager_admin_js', JOB_MANAGER_PLUGIN_URL . '/assets/js/admin.min.js', [ 'jquery', 'jquery-tiptip' ], JOB_MANAGER_VERSION, true );
		}
	}

	/**
	 * Add screen ids
	 *
	 * @since   1.4.4
	 */
	public function add_screen_ids( $screen_ids ) {
		$screen_ids[] = 'edit-company';
		$screen_ids[] = 'company';
		$screen_ids[] = 'company_page_cariera_company_manager_settings';

		return $screen_ids;
	}

	/**
	 * Add custom columns for the company post type.
	 *
	 * @since   1.3.0
	 * @since   1.6.0
	 */
	public function custom_company_columns( $columns ) {
		unset( $columns['title'], $columns['date'], $columns['author'] );

		$columns['company_image']    = '';
		$columns['title']            = esc_html__( 'Company Name', 'cariera' );
		$columns['company_location'] = esc_html__( 'Location', 'cariera' );
		$columns['company_category'] = esc_html__( 'Categories', 'cariera' );
		$columns['featured_company'] = '<span class="tips" data-tip="' . esc_html__( 'Featured?', 'cariera' ) . '">' . esc_html__( 'Featured?', 'cariera' ) . '</span>';
		$columns['company_posted']   = esc_html__( 'Posted', 'cariera' );
		$columns['company_jobs']     = esc_html__( 'Posted Jobs', 'cariera' );
		$columns['company_actions']  = esc_html__( 'Actions', 'cariera' );

		if ( ! get_option( 'cariera_company_category' ) ) {
			unset( $columns['company_category'] );
		}

		echo '<style type="text/css">';
		echo '.column-company_image { width:60px; box-sizing:border-box } .column-company_image img { max-width:100%; } @media (max-width: 768px) { .column-title,.column-company_image { display: table-cell !important; } .wp-list-table .is-expanded,.wp-list-table .column-primary .toggle-row { display:none !important } .wp-list-table td.column-primary { padding-right: 10px; } }.widefat .column-company_actions{text-align:right;width:128px}.widefat .column-company_actions .actions{padding-top:2px}.widefat .column-company_actions a.button{display:inline-block;margin:0 0 2px 4px;cursor:pointer;padding:0 6px!important;font-size:1em!important;line-height:2em!important;overflow:hidden}.widefat .column-company_actions a.button-icon{width:2em!important;padding:0!important}.widefat .column-company_actions a.button-icon:before{font-family:job-manager!important;font-style:normal;font-weight:400;speak:none;display:inline-block;text-decoration:inherit;width:1em;text-align:center;font-variant:normal;text-transform:none;line-height:1em;float:left;width:2em!important;line-height:2em}.widefat .column-company_actions .icon-approve:before{content:"\e802"}.widefat .column-company_actions .icon-view:before{content:"\e805"}.widefat .column-company_actions .icon-edit:before{content:"\e804"}.widefat .column-company_actions .icon-delete:before{content:"\e82b"}';
		echo '</style>';

		return $columns;
	}

	/**
	 * Add the data to the custom columns for the company post type.
	 *
	 * @since  1.3.0
	 */
	public function custom_company_column( $column ) {
		global $post;

		switch ( $column ) {
			case 'company_image':
				echo cariera_the_company_logo();
				break;

			case 'company_location':
				cariera_the_company_location_output();
				break;

			case 'company_category':
				$terms = get_the_term_list( $post->ID, $column, '', ', ', '' );
				if ( ! $terms ) {
					echo '<span class="na">&ndash;</span>';
				} else {
					echo wp_kses_post( $terms );
				}
				break;

			case 'featured_company':
				if ( is_position_featured( $post ) ) {
					echo '&#10004;';
				} else {
					echo '&ndash;';
				}
				break;

			case 'company_posted':
				echo '<div><strong>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) ) . '</strong></div><span>';
				// translators: %s placeholder is the username of the user.
				echo ( empty( $post->post_author ) ? esc_html__( 'by a guest', 'cariera' ) : sprintf( esc_html__( 'by %s', 'cariera' ), '<a href="' . esc_url( add_query_arg( 'author', $post->post_author ) ) . '">' . esc_html( get_the_author() ) . '</a>' ) ) . '</span>';
				break;

			case 'company_jobs':
				echo esc_html( sprintf( _n( '%s Job', '%s Jobs', cariera_get_the_company_job_listing_active_count( $post->ID ), 'cariera' ), cariera_get_the_company_job_listing_active_count( $post->ID ) ) );
				break;

			case 'company_actions':
				echo '<div class="actions">';

				$admin_actions = [];

				if ( in_array( $post->post_status, [ 'pending', 'pending_payment' ], true ) && current_user_can( 'publish_post', $post->ID ) ) {
					$admin_actions['approve'] = [
						'action' => 'approve',
						'name'   => esc_html__( 'Approve', 'cariera' ),
						'url'    => wp_nonce_url( add_query_arg( 'approve_company', $post->ID ), 'approve_company' ),
					];
				}

				if ( $post->post_status !== 'trash' ) {
					if ( current_user_can( 'read_post', $post->ID ) ) {
						$admin_actions['view'] = [
							'action' => 'view',
							'name'   => esc_html__( 'View', 'cariera' ),
							'url'    => get_permalink( $post->ID ),
						];
					}
					if ( current_user_can( 'edit_post', $post->ID ) ) {
						$admin_actions['edit'] = [
							'action' => 'edit',
							'name'   => esc_html__( 'Edit', 'cariera' ),
							'url'    => get_edit_post_link( $post->ID ),
						];
					}
					if ( current_user_can( 'delete_post', $post->ID ) ) {
						$admin_actions['delete'] = [
							'action' => 'delete',
							'name'   => esc_html__( 'Delete', 'cariera' ),
							'url'    => get_delete_post_link( $post->ID ),
						];
					}
				}

				$admin_actions = apply_filters( 'cariera_company_admin_actions', $admin_actions, $post );

				foreach ( $admin_actions as $action ) {
					if ( is_array( $action ) ) {
						printf( '<a class="button button-icon tips icon-%1$s" href="%2$s" data-tip="%3$s">%4$s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_html( $action['name'] ) );
					} else {
						echo wp_kses_post( str_replace( 'class="', 'class="button ', $action ) );
					}
				}

				echo '</div>';
				break;

		}
	}

	/**
	 * Function to approve companies
	 *
	 * @since  1.3.5
	 */
	public function approve_company() {
		if ( ! empty( $_GET['approve_company'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'approve_company' ) && current_user_can( 'publish_post', $_GET['approve_company'] ) ) {
			$post_id      = absint( $_GET['approve_company'] );
			$company_data = [
				'ID'          => $post_id,
				'post_status' => 'publish',
			];
			wp_update_post( $company_data );
			wp_safe_redirect( remove_query_arg( 'approve_company', add_query_arg( 'handled_companies', $post_id, add_query_arg( 'action_performed', 'approve_company', admin_url( 'edit.php?post_type=company' ) ) ) ) );
			exit;
		}
	}

	/**
	 * Register Custom Post Type
	 *
	 * @since  1.3.0
	 */
	public function register_post_types() {
		if ( post_type_exists( 'company' ) ) {
			return;
		}

		$admin_capability    = 'manage_job_listings';
		$permalink_structure = self::get_permalink_structure();

		/**
		 * Main Post types
		 */
		$singular = cariera_get_company_manager_singular_label();
		$plural   = cariera_get_company_manager_plural_label();

		$labels = [
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'all_items'             => sprintf( esc_html__( 'All %s', 'cariera' ), $plural ),
			'add_new'               => esc_html__( 'Add New', 'cariera' ),
			'add_new_item'          => sprintf( esc_html__( 'Add %s', 'cariera' ), $singular ),
			'edit'                  => esc_html__( 'Edit', 'cariera' ),
			'edit_item'             => sprintf( esc_html__( 'Edit %s', 'cariera' ), $singular ),
			'new_item'              => sprintf( esc_html__( 'New %s', 'cariera' ), $singular ),
			'view'                  => sprintf( esc_html__( 'View %s', 'cariera' ), $singular ),
			'view_item'             => sprintf( esc_html__( 'View %s', 'cariera' ), $singular ),
			'search_items'          => sprintf( esc_html__( 'Search %s', 'cariera' ), $plural ),
			'not_found'             => sprintf( esc_html__( 'No %s found', 'cariera' ), $plural ),
			'not_found_in_trash'    => sprintf( esc_html__( 'No %s found in trash', 'cariera' ), $plural ),
			'parent'                => sprintf( esc_html__( 'Parent %s', 'cariera' ), $singular ),
			'featured_image'        => esc_html__( 'Company Logo', 'cariera' ),
			'set_featured_image'    => esc_html__( 'Set company logo', 'cariera' ),
			'remove_featured_image' => esc_html__( 'Remove company logo', 'cariera' ),
			'use_featured_image'    => esc_html__( 'Use as company logo', 'cariera' ),
		];

		$args = [
			'labels'                => $labels,
			'description'           => sprintf( esc_html__( 'This is where you can create and manage %s.', 'cariera' ), $plural ),
			'public'                => true,
			'show_ui'               => class_exists( 'WP_Job_Manager' ),
			'menu_icon'             => 'dashicons-building',
			'capability_type'       => 'post',
			'capabilities'          => [
				'publish_posts'       => $admin_capability,
				'edit_posts'          => $admin_capability,
				'edit_others_posts'   => $admin_capability,
				'delete_posts'        => $admin_capability,
				'delete_others_posts' => $admin_capability,
				'read_private_posts'  => $admin_capability,
				'edit_post'           => $admin_capability,
				'delete_post'         => $admin_capability,
				'read_post'           => $admin_capability,
			],
			'publicly_queryable'    => true,
			'exclude_from_search'   => false,
			'hierarchical'          => true,
			'rewrite'               => [
				'slug'       => $permalink_structure['company_rewrite_slug'],
				'with_front' => false,
				'feeds'      => true,
				'pages'      => false,
			],
			'query_var'             => true,
			'supports'              => [ 'title', 'editor', 'custom-fields', 'publicize', 'thumbnail', 'author' ],
			'has_archive'           => $permalink_structure['companies_archive_rewrite_slug'],
			'show_in_nav_menus'     => false,
			'menu_position'         => 30,
			'show_in_rest'          => true,
			'rest_base'             => 'companies',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		];

		register_post_type( 'company', $args );
	}

	/**
	 * Register listing taxonomies.
	 *
	 * @since 1.4.4
	 */
	public function register_taxonomies() {

		$admin_capability    = 'manage_job_listings';
		$permalink_structure = self::get_permalink_structure();

		/**
		 * Company Taxonomy: Categories
		 */
		$singular = sprintf( esc_html__( '%s Category', 'cariera' ), cariera_get_company_manager_singular_label() );
		$plural   = sprintf( esc_html__( '%s Categories', 'cariera' ), cariera_get_company_manager_singular_label() );

		$rewrite = [
			'slug'         => $permalink_structure['company_category_rewrite_slug'],
			'with_front'   => false,
			'hierarchical' => true,
		];

		$args = apply_filters(
			'register_taxonomy_company_category_args',
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
				'show_tagcloud'         => false,
				'public'                => true,
				'capabilities'          => [
					'manage_terms' => $admin_capability,
					'edit_terms'   => $admin_capability,
					'delete_terms' => $admin_capability,
					'assign_terms' => $admin_capability,
				],
				'rewrite'               => $rewrite,
				'show_in_rest'          => true,
				'rest_base'             => 'company-categories',
			]
		);

		if ( get_option( 'cariera_company_category' ) ) {
			register_taxonomy( 'company_category', 'company', $args );
		}

		/**
		 * Company Taxonomy: Team Size
		 */
		$singular = esc_html__( 'Team size', 'cariera' );
		$plural   = esc_html__( 'Team sizes', 'cariera' );

		$rewrite = [
			'slug'         => esc_html_x( 'company-team-size', 'Company permalink - resave permalinks after changing this', 'cariera' ),
			'with_front'   => false,
			'hierarchical' => true,
		];

		$args = apply_filters(
			'register_taxonomy_company_team_size_args',
			[
				'hierarchical'  => true,
				'label'         => $plural,
				'labels'        => [
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
				'show_ui'       => true,
				'show_tagcloud' => false,
				'public'        => true,
				'capabilities'  => [
					'manage_terms' => $admin_capability,
					'edit_terms'   => $admin_capability,
					'delete_terms' => $admin_capability,
					'assign_terms' => $admin_capability,
				],
				'rewrite'       => $rewrite,
				'show_in_rest'  => true,
				'rest_base'     => 'company-team-size',
			]
		);

		if ( get_option( 'cariera_company_team_size' ) ) {
			register_taxonomy( 'company_team_size', 'company', $args );
		}
	}

	/**
	 * Registers company meta fields.
	 *
	 * @since 1.5.6
	 */
	public function register_meta_fields() {
		$fields = self::get_company_fields();

		foreach ( $fields as $meta_key => $field ) {
			register_meta(
				'post',
				$meta_key,
				[
					'type'              => $field['data_type'],
					'show_in_rest'      => $field['show_in_rest'],
					'description'       => $field['label'],
					'sanitize_callback' => $field['sanitize_callback'],
					'auth_callback'     => $field['auth_edit_callback'],
					'single'            => true,
					'object_subtype'    => 'company',
				]
			);
		}
	}

	/**
	 * Company Fields
	 *
	 * @since  1.5.6
	 */
	public static function get_company_fields() {
		$default_field = [
			'label'              => null,
			'placeholder'        => null,
			'description'        => null,
			'priority'           => 10,
			'value'              => null,
			'default'            => null,
			'classes'            => [],
			'type'               => 'text',
			'data_type'          => 'string',
			'show_in_admin'      => true,
			'show_in_rest'       => false,
			'auth_edit_callback' => [ __CLASS__, 'auth_check_can_edit_companies' ],
			'auth_view_callback' => null,
			'sanitize_callback'  => [ __CLASS__, 'sanitize_meta_field_based_on_input_type' ],
		];

		$fields = [
			'_company_tagline'      => [
				'label'         => esc_html__( 'Tagline', 'cariera' ),
				'placeholder'   => esc_html__( 'Brief description about the company', 'cariera' ),
				'priority'      => 10,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_location'     => [
				'label'         => esc_html__( 'Location', 'cariera' ),
				'placeholder'   => esc_html__( 'e.g. "London"', 'cariera' ),
				'priority'      => 11,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_email'        => [
				'label'         => esc_html__( 'Email', 'cariera' ),
				'placeholder'   => esc_html__( 'you@yourdomain.com', 'cariera' ),
				'priority'      => 12,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_website'      => [
				'label'         => esc_html__( 'Website', 'cariera' ),
				'priority'      => 13,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_phone'        => [
				'label'         => esc_html__( 'Phone', 'cariera' ),
				'placeholder'   => esc_html__( 'Enter the company\'s phone number', 'cariera' ),
				'priority'      => 14,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_facebook'     => [
				'label'         => esc_html__( 'Facebook', 'cariera' ),
				'placeholder'   => esc_html__( 'Company Facebook page link', 'cariera' ),
				'priority'      => 15,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_twitter'      => [
				'label'         => esc_html__( 'Twitter', 'cariera' ),
				'placeholder'   => esc_html__( 'Company Twitter page link', 'cariera' ),
				'priority'      => 16,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_linkedin'     => [
				'label'         => esc_html__( 'LinkedIn', 'cariera' ),
				'placeholder'   => esc_html__( 'Company LinkedIn page link', 'cariera' ),
				'priority'      => 17,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_instagram'    => [
				'label'         => esc_html__( 'Instagram', 'cariera' ),
				'placeholder'   => esc_html__( 'Company Instagram page link', 'cariera' ),
				'priority'      => 18,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_company_video'        => [
				'label'             => esc_html__( 'Video', 'cariera' ),
				'placeholder'       => esc_html__( 'URL to the company video', 'cariera' ),
				'priority'          => 19,
				'data_type'         => 'string',
				'show_in_admin'     => true,
				'show_in_rest'      => true,
				'sanitize_callback' => [ 'WP_Job_Manager_Post_Types', 'sanitize_meta_field_url' ],
			],
			'_company_since'        => [
				'label'             => esc_html__( 'Since', 'cariera' ),
				'placeholder'       => esc_html__( 'company established', 'cariera' ),
				'priority'          => 20,
				'data_type'         => 'string',
				'show_in_admin'     => true,
				'show_in_rest'      => true,
				'classes'           => [ 'job-manager-datepicker' ],
				'sanitize_callback' => [ 'WP_Job_Manager_Post_Types', 'sanitize_meta_field_date' ],
			],
			'_company_header_image' => [
				'label'         => esc_html__( 'Company Cover Image', 'cariera' ),
				'type'          => 'file',
				'priority'      => 21,
				'data_type'     => 'string',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
			'_featured'             => [
				'label'         => esc_html__( 'Feature this Company?', 'cariera' ),
				'description'   => esc_html__( 'Featured companies will be styled differently.', 'cariera' ),
				'type'          => 'checkbox',
				'priority'      => 22,
				'data_type'     => 'integer',
				'show_in_admin' => true,
				'show_in_rest'  => true,
			],
		];

		$fields = apply_filters( 'cariera_company_manager_fields', $fields );

		// Ensure default fields are set.
		foreach ( $fields as $key => $field ) {
			$fields[ $key ] = array_merge( $default_field, $field );
		}

		return $fields;
	}

	/**
	 * Checks if user can manage companies.
	 *
	 * @since 1.5.6
	 */
	public static function auth_check_can_manage_companies( $allowed, $meta_key, $post_id, $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return false;
		}

		return $user->has_cap( 'manage_companies' );
	}

	/**
	 * Checks if user can edit companies.
	 *
	 * @since 1.5.6
	 */
	public static function auth_check_can_edit_companies( $allowed, $meta_key, $post_id, $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return false;
		}

		if ( empty( $post_id ) ) {
			return current_user_can( 'edit_posts' );
		}

		return cariera_user_can_edit_company( $post_id );
	}

	/**
	 * Checks if user can edit other's companies.
	 *
	 * @since 1.5.6
	 */
	public static function auth_check_can_edit_others_companies( $allowed, $meta_key, $post_id, $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return false;
		}

		return $user->has_cap( 'edit_others_posts' );
	}

	/**
	 * Sanitize meta fields based on input type.
	 *
	 * @since 1.5.6
	 */
	public static function sanitize_meta_field_based_on_input_type( $meta_value, $meta_key ) {
		$fields = self::get_company_fields();

		if ( is_string( $meta_value ) ) {
			$meta_value = trim( $meta_value );
		}

		$type = 'text';
		if ( isset( $fields[ $meta_key ] ) ) {
			$type = $fields[ $meta_key ]['type'];
		}

		if ( 'textarea' === $type || 'wp_editor' === $type ) {
			return wp_kses_post( wp_unslash( $meta_value ) );
		}

		if ( 'checkbox' === $type ) {
			if ( $meta_value && '0' !== $meta_value ) {
				return 1;
			}

			return 0;
		}

		if ( is_array( $meta_value ) ) {
			return array_filter( array_map( 'sanitize_text_field', $meta_value ) );
		}

		return sanitize_text_field( $meta_value );
	}

	/**
	 * Adding a pending number of companies
	 *
	 * @since   1.3.0.1
	 * @version 1.7.2
	 */
	public function admin_head() {
		global $menu;

		$plural          = esc_html__( 'Companies', 'cariera' );
		$count_companies = wp_count_posts( 'company', 'readable' );
		$company_count   = $count_companies->pending;

		foreach ( $menu as $key => $menu_item ) {
			if ( strpos( $menu_item[0], $plural ) === 0 ) {
				if ( $company_count ) {
					$menu[ $key ][0] .= " <span class='awaiting-mod update-plugins count-$company_count'><span class='pending-count'>" . number_format_i18n( $count_companies->pending ) . '</span></span>';
				}
				break;
			}
		}
	}

	/**
	 * Check if a company is editable.
	 *
	 * @since 1.7.0
	 *
	 * @param int $company_id Company ID to check.
	 * @return bool
	 */
	public static function company_is_editable( $company_id ) {
		$company_is_editable = true;
		$post_status         = get_post_status( $company_id );

		if (
			( 'publish' === $post_status && ! cariera_company_manager_user_can_edit_published_submissions() )
			|| ( 'publish' !== $post_status && ! cariera_company_manager_user_can_edit_pending_submissions() )
		) {
			$company_is_editable = false;
		}

		/**
		 * Allows filtering on whether a company can be edited after it has gone past the `preview` stage.
		 *
		 * @since 1.7.0
		 *
		 * @param bool $company_is_editable If the company is editable.
		 * @param int  $company_id          Company ID to check.
		 */
		return apply_filters( 'cariera_company_manager_company_is_editable', $company_is_editable, $company_id );
	}

	/**
	 * Get the permalink settings directly from the option.
	 *
	 * @since 1.3.0
	 */
	public static function get_raw_permalink_settings() {

		$legacy_permalink_settings = '[]';
		if ( false !== get_option( 'cariera_company_permalinks', false ) ) {
			$legacy_permalink_settings = wp_json_encode( get_option( 'cariera_company_permalinks', [] ) );
			delete_option( 'cariera_company_permalinks' );
		}

		return (array) json_decode( get_option( self::PERMALINK_OPTION_NAME, $legacy_permalink_settings ), true );
	}

	/**
	 * Retrieves permalink settings.
	 *
	 * @since 1.3.0
	 */
	public static function get_permalink_structure() {
		// Switch to the site's default locale, bypassing the active user's locale.
		if ( function_exists( 'switch_to_locale' ) && did_action( 'admin_init' ) ) {
			switch_to_locale( get_locale() );
		}

		$permalink_settings = self::get_raw_permalink_settings();

		// First-time activations will get this cleared on activation.
		if ( ! array_key_exists( 'companies_archive', $permalink_settings ) ) {
			// Create entry to prevent future checks.
			$permalink_settings['companies_archive'] = '';

			// This isn't the first activation and the theme supports it. Set the default to legacy value.
			$permalink_settings['companies_archive'] = _x( 'companies', 'Post type archive slug - resave permalinks after changing this', 'cariera' );

			update_option( self::PERMALINK_OPTION_NAME, wp_json_encode( $permalink_settings ) );
		}

		$permalinks = wp_parse_args(
			$permalink_settings,
			[
				'company_base'      => '',
				'company_category'  => '',
				'companies_archive' => '',
			]
		);

		// Ensure rewrite slugs are set. Use legacy translation options if not.
		$permalinks['company_rewrite_slug']           = untrailingslashit( empty( $permalinks['company_base'] ) ? _x( 'company', 'Company permalink - resave permalinks after changing this', 'cariera' ) : $permalinks['company_base'] );
		$permalinks['company_category_rewrite_slug']  = untrailingslashit( empty( $permalinks['company_category'] ) ? _x( 'company-category', 'Company category permalink - resave permalinks after changing this', 'cariera' ) : $permalinks['company_category'] );
		$permalinks['companies_archive_rewrite_slug'] = untrailingslashit( empty( $permalinks['companies_archive'] ) ? 'companies' : $permalinks['companies_archive'] );

		// Restore the original locale.
		if ( function_exists( 'restore_current_locale' ) && did_action( 'admin_init' ) ) {
			restore_current_locale();
		}

		return $permalinks;
	}

	/**
	 * Include admin files conditionally.
	 *
	 * @since 1.4.4
	 */
	public function conditional_includes() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		switch ( $screen->id ) {
			case 'options-permalink':
				include 'permalinks.php';
				break;
		}
	}

	/**
	 * Adds post status to the "submitdiv" Meta Box and post type WP List Table screens. Based on https://gist.github.com/franz-josef-kaiser/2930190
	 *
	 * @since 1.4.7
	 */
	public function extend_submitdiv_post_status() {
		global $post, $post_type;

		// Abort if we're on the wrong post type, but only if we got a restriction.
		if ( 'company' !== $post_type ) {
			return;
		}

		// Get all non-builtin post status and add them as <option>.
		$options = '';
		$display = '';
		foreach ( cariera_get_company_post_statuses() as $status => $name ) {
			$selected = selected( $post->post_status, $status, false );

			// If we one of our custom post status is selected, remember it.
			if ( $selected ) {
				$display = $name;
			}

			// Build the options.
			$options .= "<option{$selected} value='{$status}'>" . esc_html( $name ) . '</option>';
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				<?php if ( ! empty( $display ) ) : ?>
					jQuery( '#post-status-display' ).html( decodeURIComponent( '<?php echo rawurlencode( (string) wp_specialchars_decode( $display ) ); ?>' ) );
				<?php endif; ?>

				var select = jQuery( '#post-status-select' ).find( 'select' );
				jQuery( select ).html( decodeURIComponent( '<?php echo rawurlencode( (string) wp_specialchars_decode( $options ) ); ?>' ) );
			} );
		</script>
		<?php
	}

	/**
	 * Adds robots `noindex` meta tag to discourage search indexing.
	 *
	 * @since 1.4.7
	 */
	public function add_no_robots() {
		if ( ! is_single() ) {
			return;
		}

		$post = get_post();
		if ( ! $post || 'company' !== $post->post_type ) {
			return;
		}

		wp_robots_no_robots();
	}

	/**
	 * Maybe set menu_order if the featured status of a company is changed
	 *
	 * @since 1.5.0
	 */
	public function maybe_update_menu_order( $meta_id, $object_id, $meta_key, $_meta_value ) {
		if ( '_featured' !== $meta_key || 'company' !== get_post_type( $object_id ) ) {
			return;
		}
		global $wpdb;

		if ( '1' == $_meta_value ) {
			$wpdb->update( $wpdb->posts, [ 'menu_order' => -1 ], [ 'ID' => $object_id ] );
		} else {
			$wpdb->update(
				$wpdb->posts,
				[ 'menu_order' => 0 ],
				[
					'ID'         => $object_id,
					'menu_order' => -1,
				]
			);
		}

		clean_post_cache( $object_id );
	}
}

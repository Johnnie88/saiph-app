<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Core_Promotion_Package {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Construct
	 *
	 * @since  1.5.0
	 */
	public function __construct() {

		// Register promotion package post type.
		add_action( 'init', [ $this, 'register_post_type' ] );

		// // Add this menu to Users.
		add_action( 'admin_menu', [ $this, 'set_menu_location' ], 55 );

		// // Register custom post statuses.
		add_action( 'init', [ $this, 'register_post_statuses' ] );
		foreach ( [ 'post', 'post-new', 'edit' ] as $hook ) {
			add_action( "admin_footer-{$hook}.php", [ $this, 'display_custom_post_statuses' ] );
		}

		// Add title.
		add_filter( 'the_title', [ $this, 'promotion_package_title' ], 10, 2 );
		add_action( 'edit_form_after_title', [ $this, 'display_package_id_edit_screen' ] );

		// Admin columns.
		add_filter( 'manage_cariera_promotion_posts_columns', [ $this, 'promotion_posts_columns' ] );
		add_action( 'manage_cariera_promotion_posts_custom_column', [ $this, 'promotions_custom_column' ], 5, 2 );
		add_filter( 'post_row_actions', [ $this, 'remove_promotion_quick_edit' ], 10, 2 );
		add_filter( 'bulk_actions-edit-cariera_promotion', [ $this, 'remove_promotion_bulk_action_edit' ] );

		// Delete packages with user.
		add_action( 'deleted_user', [ $this, 'delete_promotions_with_user' ], 10, 2 );

		// Save post action.
		add_action( 'save_post', [ $this, 'save_package' ], 99, 2 );
	}

	/**
	 * Register Post Type for Promotion Packages.
	 *
	 * @since 1.5.0
	 */
	public function register_post_type() {

		$labels = [
			'name'               => esc_html__( 'Promotion Packages', 'cariera' ),
			'singular_name'      => esc_html__( 'Promotion Package', 'cariera' ),
			'add_new'            => esc_html__( 'Promote a Listing', 'cariera' ),
			'add_new_item'       => esc_html__( 'Add New Package', 'cariera' ),
			'edit_item'          => esc_html__( 'Edit Package', 'cariera' ),
			'new_item'           => esc_html__( 'New Package', 'cariera' ),
			'all_items'          => esc_html__( 'All Packages', 'cariera' ),
			'view_item'          => esc_html__( 'View Package', 'cariera' ),
			'search_items'       => esc_html__( 'Search Packages', 'cariera' ),
			'not_found'          => esc_html__( 'Not Found', 'cariera' ),
			'not_found_in_trash' => esc_html__( 'Not Found in Trash', 'cariera' ),
			'menu_name'          => esc_html__( 'Promotion Packages', 'cariera' ),
		];

		$args = [
			'labels'              => $labels,
			'description'         => '',
			'public'              => false,
			'publicly_queryable'  => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'exclude_from_search' => true, // Need this for WP_Query.
			'show_ui'             => true,
			'show_in_menu'        => false,
			'menu_position'       => 3,
			'menu_icon'           => 'dashicons-screenoptions',
			'can_export'          => true,
			'delete_with_user'    => false,
			'hierarchical'        => false,
			'has_archive'         => false,
			'query_var'           => true,
			'rewrite'             => false,
			'capability_type'     => 'page',
			'supports'            => [ '' ],
		];

		register_post_type( 'cariera_promotion', apply_filters( 'cariera_promo_package_cpt_args', $args ) );
	}

	/**
	 * Add Listing Packages as Listings Submenu.
	 *
	 * @since 1.5.0
	 */
	public function set_menu_location() {
		$cpt_obj = get_post_type_object( 'cariera_promotion' );
		add_submenu_page(
			'users.php',                              // Parent slug.
			'Promotion Packages',                   // Page title.
			$cpt_obj->labels->menu_name,              // Menu title.
			$cpt_obj->cap->edit_posts,                // Capability.
			'edit.php?post_type=cariera_promotion'    // Menu slug.
		);
	}

	/**
	 * Register Promotion Package Statuses
	 *
	 * @since 1.5.0
	 */
	public function register_post_statuses() {
		register_post_status(
			'promotion_cancelled',
			[
				'label'                     => esc_html__( 'Cancelled', 'cariera' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => false,
				'show_in_admin_status_list' => true,
				// translators: %s is label count.
				'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'cariera' ),
			]
		);
	}

	/**
	 * Get possible post statuses for promotion packages.
	 *
	 * @since 1.5.0
	 */
	public function get_statuses() {
		return [
			'publish'             => esc_html__( 'Active', 'cariera' ),
			'draft'               => esc_html__( 'Inactive', 'cariera' ),
			'trash'               => esc_html__( 'Expired', 'cariera' ), // Fully Used.
			'promotion_cancelled' => esc_html__( 'Cancelled', 'cariera' ),
		];
	}

	/**
	 * Get proper package status.
	 *
	 * @since  1.5.0
	 */
	function get_proper_status( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || 'cariera_promotion' !== $post->post_type ) {
			return false;
		}

		// Get post status.
		$status = $post->post_status;
		if ( 'trash' === $status ) {
			return $status;
		}

		// Check order.
		if ( $post->_order_id && ( $order = wc_get_order( $post->_order_id ) ) ) {
			if ( $order->get_status() === 'cancelled' ) {
				return 'promotion_cancelled';
			} elseif ( 'promotion_cancelled' === $post->post_status ) {
				$status = 'publish';
			}
		}

		// Check if listing has expired.
		if ( ( $expiry = get_post_meta( $post->ID, '_expires', true ) ) && ( $expiry_time = strtotime( $expiry, current_time( 'timestamp' ) ) ) ) {
			if ( $expiry_time < current_time( 'timestamp' ) ) {
				$status = 'trash';
			} elseif ( $post->post_status === 'trash' ) {
				$status = 'publish';
			}
		}

		return $status;
	}

	/**
	 * Adds post status to the "submitdiv" Meta Box and post type WP List Table screens.
	 *
	 * @since 1.5.0
	 */
	public function display_custom_post_statuses() {
		global $post, $post_type;

		// Abort if we're on the wrong post type, but only if we got a restriction.
		if ( 'cariera_promotion' !== $post_type ) {
			return;
		}

		$statuses = $this->get_statuses();

		// Get all non-builtin post status and add them as <option>.
		$options = $display = '';
		if ( $post instanceof WP_Post ) {
			foreach ( $statuses as $status => $name ) {
				$selected = selected( $post->post_status, $status, false );

				// If one of our custom post statuses is selected, remember it.
				$selected and $display = $name;

				// Build the options.
				$options .= "<option{$selected} value='{$status}'>{$name}</option>";
			}
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				<?php if ( ! empty( $display ) ) : ?>
					jQuery( '#post-status-display' ).html( '<?php echo $display; ?>' );
				<?php endif; ?>

				var select = jQuery( '#post-status-select' ).find( 'select' );
				jQuery( select ).html( "<?php echo $options; ?>" );

				if ( $('body.post-type-cariera_promo_package .subsubsub .trash a').length ) {
					var counter = $('body.post-type-cariera_promo_package .subsubsub .trash a span').detach();
					$('body.post-type-cariera_promo_package .subsubsub .trash a').html(
						'<?php echo esc_attr( _x( 'Expired', 'Admin view promotions - Expired Packages', 'cariera' ) ); ?> '
					).append( counter );
				}
			} );
		</script>
		<?php
	}

	/**
	 * Genearate a promotion package title.
	 *
	 * @since 1.5.0
	 */
	public function promotion_package_title( $title, $id = null ) {
		if ( ! $id || 'cariera_promotion' !== get_post_type( $id ) ) {
			return $title;
		}

		$title = sprintf( '#%s', $id );

		return $title;
	}

	/**
	 * Display Package ID in Edit Screen
	 *
	 * @since 1.5.0
	 */
	public function display_package_id_edit_screen( $post ) {
		if ( ! ( $post && $post->ID && $post->post_type === 'cariera_promotion' ) ) {
			return;
		}

		if ( empty( $_GET['action'] ) || $_GET['action'] !== 'edit' ) {
			return;
		}
		?>

		<h1 class="wp-heading-inline-package">
			<?php printf( esc_html__( 'Edit Package #%d', 'cariera' ), $post->ID ); ?>
		</h1>
		<style>.wrap h1.wp-heading-inline {display:none;} .wrap > .page-title-action {display:none;} #poststuff {margin-top: 30px;}</style>
		<?php
	}

	/**
	 * Package columns.
	 *
	 * @since  1.5.0
	 */
	public function promotion_posts_columns( $columns ) {
		unset( $columns['date'] );
		$columns['title']    = esc_html__( 'Package ID', 'cariera' );
		$columns['listing']  = esc_html__( 'Listing', 'cariera' );
		$columns['user']     = esc_html__( 'User', 'cariera' );
		$columns['duration'] = esc_html__( 'Promoted Until', 'cariera' );
		$columns['product']  = esc_html__( 'Product', 'cariera' );
		$columns['order']    = esc_html__( 'Order ID', 'cariera' );

		return $columns;
	}

	/**
	 * Cutom package columns.
	 *
	 * @since 1.5.0
	 */
	public function promotions_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'listing':
				$listing    = esc_html( '-' );
				$listing_id = get_post_meta( $post_id, '_listing_id', true );

				// Append listing name to promotion package title.
				if ( absint( $listing_id ) && ( $listing_title = get_the_title( $listing_id ) ) ) {
					$listing = $listing_title;
				}

				echo esc_html( $listing );
				break;

			case 'user':
				$title   = esc_html__( 'n/a', 'cariera' );
				$user_id = absint( get_post_meta( $post_id, '_user_id', true ) );
				if ( $user_id ) {
					$user = get_userdata( $user_id );
					if ( $user ) {
						$title  = '<a target="_blank" href="' . esc_url( get_edit_user_link( $user_id ) ) . '">';
						$title .= $user->user_login;
						$title .= '</a>';
					}
				}

				echo $title;
				break;

			case 'duration':
				$expires     = get_post_meta( $post_id, '_expires', true );
				$expiry_time = strtotime( $expires, current_time( 'timestamp' ) );
				echo $expiry_time ? esc_html( date_i18n( 'F j, Y g:i a', $expiry_time ) ) : '&ndash;';
				break;

			case 'product':
				$link       = esc_html__( 'n/a', 'cariera' );
				$product_id = get_post_meta( $post_id, '_product_id', true );
				if ( $product_id ) {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						$link = '<a target="_blank" href="' . esc_url( get_edit_post_link( $product_id ) ) . '">' . $product->get_name() . '</a>';
					}
				}
				echo $link;
				break;

			case 'order':
				$link     = esc_html__( 'n/a', 'cariera' );
				$order_id = absint( get_post_meta( $post_id, '_order_id', true ) );
				if ( $order_id ) {
					$link = '<a target="_blank" href="' . esc_url( get_edit_post_link( $order_id ) ) . '">#' . $order_id . '</a>';
				}
				echo $link;
				break;
		}
	}

	/**
	 * Remove quick edit link
	 *
	 * @since  1.5.0
	 */
	public function remove_promotion_quick_edit( $actions, $post ) {
		if ( 'cariera_promotion' === $post->post_type ) {
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $post ), _x( 'Edit Package', 'Promotions list in wp-admin', 'cariera' ) );

			if ( $listing_id = absint( $post->_listing_id ) ) {
				$actions['inline hide-if-no-js'] = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $listing_id ), _x( 'Edit Listing', 'Promotions list in wp-admin', 'cariera' ) );
			} else {
				unset( $actions['inline hide-if-no-js'] );
			}
		}

		return $actions;
	}

	/**
	 * Remove Promo Packages Edit Bulk Actions
	 *
	 * @since  1.5.0
	 */
	public function remove_promotion_bulk_action_edit( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}

	/**
	 * Check whether the given listing is promoted, and return the promotion package if it is
	 *
	 * @since  1.5.0
	 */
	public function get_listing_package( $listing_id, $return_format = 'ids' ) {
		$listing = get_post( $listing_id );

		if ( ! $listing->id ) {
			return;
		}

		if ( ! ( $package_id = $listing->_promo_package_id ) ) {
			return false;
		}

		$package = get_posts(
			[
				'post_type'      => 'cariera_promotion',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'post__in'       => [ absint( $package_id ) ],
				'fields'         => $return_format,
				'meta_query'     => [
					[
						'key'   => '_listing_id',
						'value' => $listing_id,
					],
				],
			]
		);

		return ! empty( $package ) ? reset( $package ) : false;
	}

	/**
	 * Delete promotions when user is deleted.
	 *
	 * @since 1.5.0
	 */
	public function delete_promotions_with_user( $id, $reassign ) {
		// Get packages.
		$packages = get_posts(
			[
				'post_type'        => 'cariera_promotion',
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'post__in'         => [],
				'order'            => 'asc',
				'orderby'          => 'post__in',
				'suppress_filters' => false,
				'fields'           => 'ids',
				'meta_query'       => [
					'relation' => 'AND',
					[
						'key'     => '_user_id',
						'value'   => $id,
						'compare' => 'IN',
					],
				],
			]
		);

		// Delete packages.
		$deleted = [];
		foreach ( $packages as $package_id ) {
			$post = wp_delete_post( $package_id, false ); // Move to trash.
			if ( $post ) {
				$deleted[ $package_id ] = $post;
			}
		}

		return $deleted;
	}

	/**
	 * Get promotion packages.
	 *
	 * @since 1.5.0
	 */
	public function get_packages( $args = [] ) {
		$args = array_replace_recursive(
			[
				'post_type'        => 'cariera_promotion',
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'suppress_filters' => false,
				'meta_query'       => [
					'relation'   => 'AND',
					'user_query' => [
						'key'   => '_user_id',
						'value' => get_current_user_id(),
					],
				],
			],
			$args
		);

		return get_posts( $args );
	}

	/**
	 * Save package action.
	 *
	 * @since 1.5.0
	 */
	public function save_package( $post_id, $post = null ) {
		if ( ! ( $post && $post->post_type === 'cariera_promotion' ) || defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		$promotions = new Cariera_Core_Promotions();

		// Current listing expiry date.
		$expiry_date = get_post_meta( $post_id, '_expires', true );

		// Get proper post status.
		$status     = $this->get_proper_status( $post );
		$old_status = ! empty( $_POST['original_post_status'] ) ? $_POST['original_post_status'] : false;
		$action     = ! empty( $_GET['action'] ) ? $_GET['action'] : false;

		// On status change handle activating/expiring the package.
		if ( $status !== $old_status ) {
			if ( $status === 'publish' || $action === 'untrash' ) {
				$promotions->activate_package( $post->ID );
			}

			if ( $status === 'trash' || $action === 'trash' ) {
				$promotions->expire_package( $post->ID );
			}
		}

		// Re-apply the custom expiry date set by the admin ("Promoted Until" setting) since it's reset by the `activate_package` function call.
		if ( $expiry_date && strtotime( $expiry_date, current_time( 'timestamp' ) ) ) {
			update_post_meta( $post->ID, '_expires', $expiry_date );
		}

		// Handle priority change.
		if ( $status === 'publish' ) {
			$listing_id       = get_post_meta( $post_id, '_listing_id', true );
			$listing_priority = get_post_meta( $listing_id, '_featured', true );

			if ( $listing_id && $listing_priority ) {
				update_post_meta( $listing_id, '_featured', true );
			}
		}
	}

	/**
	 * Get all 'Promotion Package' products.
	 *
	 * @since 1.5.0
	 */
	public function get_products( $package ) {
		static $packages;
		if ( ! is_null( $packages ) ) {
			return $packages;
		}

		$packages = wc_get_products(
			[
				'post_type'        => 'product',
				'posts_per_page'   => -1,
				'order'            => 'ASC',
				'orderby'          => 'meta_value_num',
				'meta_key'         => '_price',
				'suppress_filters' => false,
				'tax_query'        => [
					'relation' => 'AND',
					[
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => [ $package ],
						'operator' => 'IN',
					],
				],
			]
		);

		return $packages;
	}

	/**
	 * Get promotion packages belonging to current user.
	 *
	 * @since 1.5.0
	 */
	public function get_available_packages_for_current_user() {
		$packages = get_posts(
			[
				'post_type'      => 'cariera_promotion',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'author'         => get_current_user_id(),
				'meta_query'     => [
					[
						'relation' => 'OR',
						[
							'key'   => '_listing_id',
							'value' => '',
						],
						[
							'key'     => '_listing_id',
							'compare' => 'NOT EXISTS',
						],
					],
				],
			]
		);

		if ( is_wp_error( $packages ) ) {
			return [];
		}

		return $packages;
	}

	/**
	 * Buy package
	 *
	 * @since 1.5.0
	 */
	public function buy_package( $product_id, $listing_id ) {
		$product = wc_get_product( absint( $product_id ) );

		// Validate product.
		if ( ! ( $product && $product->is_type( [ 'job_promotion_package', 'company_promotion_package', 'resume_promotion_package' ] ) && $product->is_purchasable() ) ) {
			throw new \Exception( __( 'Could not process request.', 'cariera' ) );
		}

		// Remove old promotion packages for this listing from the cart, if any.
		if ( is_array( WC()->cart->cart_contents ) ) {
			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( empty( $cart_item['listing_id'] ) || empty( $cart_item['data'] ) ) {
					continue;
				}

				if ( ! in_array( $cart_item['data']->get_type(), [ 'job_promotion_package', 'company_promotion_package', 'resume_promotion_package' ] ) ) {
					continue;
				}

				// Remove promotion package if it belongs to the listing currently being promoted.
				if ( absint( $cart_item['listing_id'] ) === absint( $listing_id ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
		}

		// Add product to cart with listing_id provided in the cart item data.
		WC()->cart->add_to_cart(
			$product->get_id(),
			1,
			'',
			'',
			[
				'listing_id' => $listing_id,
			]
		);
	}

}

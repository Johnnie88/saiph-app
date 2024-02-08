<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Core_Promotions_WC {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Promotion packages
	 */
	public $packages;    // Promotion packages.

	/**
	 * Instance of Cariera_Core_Promotion_Package
	 */
	public $package;

	/**
	 * Instance of Cariera_Core_Promotions
	 */
	public $promotions;

	/**
	 * Construct
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->promotions = new Cariera_Core_Promotions();
		$this->package    = $this->promotions->package();

		// Add "promotion package" custom woocommerce product type.
		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'promotion_settings' ] );
		add_action( 'woocommerce_process_product_meta_job_promotion_package', [ $this, 'save_product_settings' ] );
		add_action( 'woocommerce_process_product_meta_company_promotion_package', [ $this, 'save_product_settings' ] );
		add_action( 'woocommerce_process_product_meta_resume_promotion_package', [ $this, 'save_product_settings' ] );

		// Adding _listing_id meta to the order.
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'add_listing_id_to_order_meta' ], 10, 4 );

		// Title in the Cart and Thank you page.
		add_filter( 'woocommerce_get_item_data', [ $this, 'display_listing_name_in_cart' ], 10, 2 );
		add_action( 'woocommerce_thankyou', [ $this, 'woocommerce_thankyou' ], 5 );

		// Force reg during checkout process.
		add_filter( 'option_woocommerce_enable_signup_and_login_from_checkout', [ $this, 'require_account_on_checkout' ] );
		add_filter( 'option_woocommerce_enable_guest_checkout', [ $this, 'disable_guest_checkout' ] );

		// Process order.
		add_action( 'woocommerce_order_status_processing', [ $this, 'order_paid' ] );
		add_action( 'woocommerce_order_status_completed', [ $this, 'order_paid' ] );
		add_action( 'woocommerce_order_status_cancelled', [ $this, 'order_cancelled' ] );

		// Dashboard Action.
		add_action( 'cariera_job_dashboard_action_start', [ $this, 'job_dashboard_action' ], 10 );
		add_action( 'cariera_company_dashboard_action_start', [ $this, 'company_dashboard_action' ], 10 );
		add_action( 'cariera_resume_dashboard_action_start', [ $this, 'resume_dashboard_action' ], 10 );

		// Include 'promote-listing' modal in the footer.
		add_action( 'wp_footer', [ $this, 'get_job_promotions_modal' ] );
		add_action( 'wp_footer', [ $this, 'get_company_promotions_modal' ] );
		add_action( 'wp_footer', [ $this, 'get_resume_promotions_modal' ] );
	}

	/**
	 * Add promotion product settings.
	 *
	 * @since 1.5.0
	 */
	public function promotion_settings() {
		global $post;
		$post_id = $post->ID;

		echo '<div class="options_group show_if_job_promotion_package show_if_company_promotion_package show_if_resume_promotion_package">';
			woocommerce_wp_text_input(
				[
					'id'                => '_promotion_duration',
					'label'             => esc_html__( 'Promotion duration', 'cariera' ),
					'description'       => esc_html__( 'The number of days that the listing will be promoted.', 'cariera' ),
					'value'             => get_post_meta( $post_id, '_promotion_duration', true ),
					'placeholder'       => 7,
					'type'              => 'number',
					'desc_tip'          => true,
					'custom_attributes' => [
						'min'  => '',
						'step' => '1',
					],
				]
			) ?>

			<script type="text/javascript">
				jQuery( function() {
					jQuery('.pricing').addClass('show_if_job_promotion_package show_if_company_promotion_package show_if_resume_promotion_package');
					jQuery('._tax_status_field').closest('div').addClass('show_if_job_promotion_package show_if_company_promotion_package show_if_resume_promotion_package');
					jQuery('#product-type').change();
				} );
			</script>
		</div>
		<?php
	}

	/**
	 * Save Promotion Package data for the product
	 *
	 * @since 1.5.0
	 */
	public function save_product_settings( $post_id ) {
		delete_post_meta( $post_id, '_promotion_duration' );

		// Duration.
		if ( ! empty( $_POST['_promotion_duration'] ) ) {
			update_post_meta( $post_id, '_promotion_duration', absint( $_POST['_promotion_duration'] ) );
		}
	}

	/**
	 * Add listing_id meta to the order
	 *
	 * @since 1.5.0
	 */
	public function add_listing_id_to_order_meta( $order_item, $cart_item_key, $cart_item_data, $order ) {
		if ( isset( $cart_item_data['listing_id'] ) ) {
			$order_item->update_meta_data( '_listing_id', $cart_item_data['listing_id'] );
		}
	}

	/**
	 * Add listing title to the cart
	 *
	 * @since 1.5.0
	 */
	public function display_listing_name_in_cart( $data, $cart_item ) {
		if ( isset( $cart_item['listing_id'] ) ) {
			$data[] = [
				'name'  => esc_html__( 'Listing', 'cariera' ),
				'value' => get_the_title( absint( $cart_item['listing_id'] ) ),
			];
		}

		return $data;
	}

	/**
	 * WooCommerce thank you page
	 *
	 * @since 1.5.0
	 */
	public function woocommerce_thankyou( $order_id ) {
		global $wp_post_types;
		$order   = wc_get_order( $order_id );
		$is_paid = in_array( $order->get_status(), [ 'completed', 'processing' ] );

		foreach ( $order->get_items() as $item ) {
			if ( ! isset( $item['listing_id'] ) ) {
				continue;
			}

			$listing_status = get_post_status( $item['listing_id'] );
			if ( $is_paid ) {
				echo wpautop( sprintf( esc_html__( '"%s" has been promoted successfully.', 'cariera' ), get_the_title( $item['listing_id'] ) ) );
			} else {
				echo wpautop( sprintf( esc_html__( '"%s" will be promoted once the order is verified and completed.', 'cariera' ), get_the_title( $item['listing_id'] ) ) );
			}
		}
	}

	/**
	 * When cart contains a promotion package make sure this is always set to "yes".
	 *
	 * @since 1.5.0
	 */
	public function require_account_on_checkout( $value ) {
		global $woocommerce;

		if ( ! empty( $woocommerce->cart->cart_contents ) ) {
			foreach ( $woocommerce->cart->cart_contents as $cart_item ) {
				$product = $cart_item['data'];
				if ( $product instanceof WC_Product && $product->is_type( [ 'job_promotion_package', 'company_promotion_package', 'resume_promotion_package' ] ) ) {
					return 'yes';
				}
			}
		}

		return $value;
	}

	/**
	 * When cart contains a promotion package, always set to "no".
	 *
	 * @since 1.5.0
	 */
	public function disable_guest_checkout( $value ) {
		global $woocommerce;
		if ( ! empty( $woocommerce->cart->cart_contents ) ) {
			foreach ( $woocommerce->cart->cart_contents as $cart_item ) {
				$product = $cart_item['data'];
				if ( $product instanceof WC_Product && $product->is_type( [ 'job_promotion_package', 'company_promotion_package', 'resume_promotion_package' ] ) ) {
					return 'no';
				}
			}
		}

		return $value;
	}

	/**
	 * Triggered when an order is paid
	 *
	 * @since 1.5.0
	 */
	public function order_paid( $order_id ) {
		// Get the order.
		$order      = wc_get_order( $order_id );
		$promotions = $this->promotions;

		// Bail if already processed.
		if ( get_post_meta( $order_id, 'promotion_packages_processed', true ) ) {
			return false;
		}

		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item['product_id'] );

			if ( ! $product->is_type( [ 'job_promotion_package', 'company_promotion_package', 'resume_promotion_package' ] ) || ! $order->get_customer_id() ) {
				continue;
			}

			// Give packages to user.
			$package_id = false;
			for ( $i = 0; $i < $item['qty']; $i++ ) {
				$package_id = wp_insert_post(
					[
						'post_type'   => 'cariera_promotion',
						'post_status' => 'publish',
						'meta_input'  => [
							'_user_id'    => $order->get_customer_id(),
							'_product_id' => $product->get_id(),
							'_order_id'   => $order_id,
							'_duration'   => $product->get_duration(),
						],
					]
				);

				if ( ! $package_id || is_wp_error( $package_id ) || empty( $item['listing_id'] ) ) {
					continue;
				}

				$promotions->activate_package( $package_id, $item['listing_id'] );
			}
		}

		// Mark that this order already processed.
		update_post_meta( $order_id, 'promotion_packages_processed', true );
	}

	/**
	 * When order get's cancelled looks for promotion packages in order and deletes the package if found.
	 *
	 * @since 1.5.0
	 */
	public function order_cancelled( $order_id ) {
		$promotions = $this->promotions;

		// Get packages.
		$packages = get_posts(
			[
				'post_type'        => 'cariera_promotion',
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'suppress_filters' => false,
				'fields'           => 'ids',
				'meta_query'       => [
					'relation' => 'AND',
					[
						'key'     => '_order_id',
						'value'   => $order_id,
						'compare' => 'IN',
					],
				],
			]
		);

		if ( $packages && is_array( $packages ) ) {
			foreach ( $packages as $package_id ) {
				$promotions->expire_package( $package_id );
			}
		}
	}

	/**
	 * Add "Promote" action to the Job Dashboard
	 *
	 * @since 1.5.0
	 */
	public function job_dashboard_action( $listing_id ) {
		$listing = get_post( $listing_id );

		if ( ! get_option( 'cariera_job_promotions' ) ) {
			return;
		}

		if ( $listing->post_status != 'publish' ) {
			return;
		}

		if ( get_post_meta( $listing_id, '_featured', true ) ) {
			return;
		}

		echo '<a href="#promotions-modal" class="job-dashboard-action-promote popup-with-zoom-anim" data-listing-id="' . esc_attr( $listing_id ) . '"><i class="icon-energy"></i>' . esc_html__( 'Promote', 'cariera' ) . '</a>';
	}

	/**
	 * Add "Promote" action to the Company Dashboard
	 *
	 * @since 1.5.0
	 */
	public function company_dashboard_action( $listing_id ) {
		$listing = get_post( $listing_id );

		if ( ! get_option( 'cariera_company_promotions' ) ) {
			return;
		}

		if ( $listing->post_status != 'publish' ) {
			return;
		}

		if ( get_post_meta( $listing_id, '_featured', true ) ) {
			return;
		}

		echo '<a href="#promotions-modal" class="company-dashboard-action-promote popup-with-zoom-anim" data-listing-id="' . esc_attr( $listing_id ) . '"><i class="icon-energy"></i>' . esc_html__( 'Promote', 'cariera' ) . '</a>';
	}

	/**
	 * Add "Promote" action to the Resume Dashboard
	 *
	 * @since 1.5.0
	 */
	public function resume_dashboard_action( $listing_id ) {
		$listing = get_post( $listing_id );

		if ( ! get_option( 'cariera_resume_promotions' ) ) {
			return;
		}

		if ( $listing->post_status != 'publish' ) {
			return;
		}

		if ( get_post_meta( $listing_id, '_featured', true ) ) {
			return;
		}

		echo '<a href="#promotions-modal" class="candidate-dashboard-action-promote popup-with-zoom-anim" data-listing-id="' . esc_attr( $listing_id ) . '"><i class="icon-energy"></i>' . esc_html__( 'Promote', 'cariera' ) . '</a>';
	}

	/**
	 * Output 'Choose Promotion' modal in Job Dashboard
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 */
	public function get_job_promotions_modal() {
		global $post;

		$job_dashboard = get_option( 'job_manager_job_dashboard_page_id' );

		if ( ! get_option( 'cariera_job_promotions' ) ) {
			return;
		}

		if ( ! $post || $post->ID != $job_dashboard ) {
			return;
		}

		// Init packages class and get products and existing packages.
		$package  = new Cariera_Core_Promotion_Package();
		$products = $package->get_products( 'job_promotion_package' );

		// Load template.
		cariera_get_template(
			'account/promotions/choose-promotion.php',
			[
				'title'    => esc_html__( 'Job Promotion', 'cariera' ),
				'products' => $products,
				'type'     => 'job_promotion_package',
			]
		);
	}

	/**
	 * Output 'Choose Promotion' modal in Company Dashboard
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 */
	public function get_company_promotions_modal() {
		global $post;

		$company_dashboard = get_option( 'cariera_company_dashboard_page' );

		if ( ! get_option( 'cariera_company_promotions' ) ) {
			return;
		}

		if ( ! $post || $post->ID != $company_dashboard ) {
			return;
		}

		// Init packages class and get products and existing packages.
		$package  = new Cariera_Core_Promotion_Package();
		$products = $package->get_products( 'company_promotion_package' );

		// Load template.
		cariera_get_template(
			'account/promotions/choose-promotion.php',
			[
				'title'    => esc_html__( 'Company Promotion', 'cariera' ),
				'products' => $products,
				'type'     => 'company_promotion_package',
			]
		);
	}

	/**
	 * Output 'Choose Promotion' modal in Candidate Dashboard
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 */
	public function get_resume_promotions_modal() {
		global $post;

		$resume_dashboard = get_option( 'resume_manager_candidate_dashboard_page_id' );

		if ( ! get_option( 'cariera_resume_promotions' ) ) {
			return;
		}

		if ( ! $post || $post->ID != $resume_dashboard ) {
			return;
		}

		// Init packages class and get products and existing packages.
		$package  = new Cariera_Core_Promotion_Package();
		$products = $package->get_products( 'resume_promotion_package' );

		// Load template.
		cariera_get_template(
			'account/promotions/choose-promotion.php',
			[
				'title'    => esc_html__( 'Resume Promotion', 'cariera' ),
				'products' => $products,
				'type'     => 'resume_promotion_package',
			]
		);
	}

}

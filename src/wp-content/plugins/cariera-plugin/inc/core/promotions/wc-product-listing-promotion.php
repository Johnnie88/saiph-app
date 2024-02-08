<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create Job Promo Package product type
 *
 * @since 1.5.0
 */
function cariera_create_job_package_product_type() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	class Cariera_WC_Product_Job_Promotion_Package extends WC_Product {

		public $product_type;

		public function __construct( $product ) {
			$this->product_type = 'job_promotion_package';
			parent::__construct( $product );
		}

		public function get_type() {
			return 'job_promotion_package';
		}

		public function is_sold_individually() {
			return true;
		}

		public function is_purchasable() {
			return true;
		}

		public function is_virtual() {
			return true;
		}

		public function is_visible() {
			return false;
		}

		public function get_product_meta( $key ) {
			return $this->get_meta( '_' . $key );
		}

		public function get_duration() {
			$duration = $this->get_product_meta( 'promotion_duration' );
			return absint( $duration ?: 7 );
		}

	}

}

add_action( 'init', 'cariera_create_job_package_product_type', 10 );

/**
 * Create Company Promo Package product type
 *
 * @since 1.5.0
 */
function cariera_create_company_package_product_type() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	class Cariera_WC_Product_Company_Promotion_Package extends WC_Product {

		public function __construct( $product ) {
			$this->product_type = 'company_promotion_package';
			parent::__construct( $product );
		}

		public function get_type() {
			return 'company_promotion_package';
		}

		public function is_sold_individually() {
			return true;
		}

		public function is_purchasable() {
			return true;
		}

		public function is_virtual() {
			return true;
		}

		public function is_visible() {
			return false;
		}

		public function get_product_meta( $key ) {
			return $this->get_meta( '_' . $key );
		}

		public function get_duration() {
			$duration = $this->get_product_meta( 'promotion_duration' );
			return absint( $duration ?: 7 );
		}
	}

}

add_action( 'init', 'cariera_create_company_package_product_type', 11 );

/**
 * Create Resume Promo Package product type
 *
 * @since 1.5.0
 */
function cariera_create_resume_package_product_type() {
	if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WP_Resume_Manager' ) ) {
		return;
	}

	class Cariera_WC_Product_Resume_Promotion_Package extends WC_Product {

		public function __construct( $product ) {
			$this->product_type = 'resume_promotion_package';
			parent::__construct( $product );
		}

		public function get_type() {
			return 'resume_promotion_package';
		}

		public function is_sold_individually() {
			return true;
		}

		public function is_purchasable() {
			return true;
		}

		public function is_virtual() {
			return true;
		}

		public function is_visible() {
			return false;
		}

		public function get_product_meta( $key ) {
			return $this->get_meta( '_' . $key );
		}

		public function get_duration() {
			$duration = $this->get_product_meta( 'promotion_duration' );
			return absint( $duration ?: 7 );
		}
	}

}

add_action( 'init', 'cariera_create_resume_package_product_type', 12 );

/**
 * Add the product type
 *
 * @since 1.5.0
 */
function cariera_listing_promotion_package_product_type( $types ) {

	if ( class_exists( 'WP_Job_Manager' ) ) {
		$types['job_promotion_package'] = esc_html__( 'Job Promotion Package', 'cariera' );
	}

	$types['company_promotion_package'] = esc_html__( 'Company Promotion Package', 'cariera' );

	if ( class_exists( 'WP_Resume_Manager' ) ) {
		$types['resume_promotion_package'] = esc_html__( 'Resume Promotion Package', 'cariera' );
	}

	return $types;
}

add_filter( 'product_type_selector', 'cariera_listing_promotion_package_product_type', 10, 2 );

/**
 * Add the product class
 *
 * @since 1.5.0
 */
function cariera_job_promotions_wc_product_class( $classname, $product_type ) {

	if ( $product_type == 'job_promotion_package' ) {
		$classname = 'Cariera_WC_Product_Job_Promotion_Package';
	}

	if ( $product_type == 'company_promotion_package' ) {
		$classname = 'Cariera_WC_Product_Company_Promotion_Package';
	}

	if ( $product_type == 'resume_promotion_package' ) {
		$classname = 'Cariera_WC_Product_Resume_Promotion_Package';
	}

	return $classname;
}

add_filter( 'woocommerce_product_class', 'cariera_job_promotions_wc_product_class', 10, 2 );

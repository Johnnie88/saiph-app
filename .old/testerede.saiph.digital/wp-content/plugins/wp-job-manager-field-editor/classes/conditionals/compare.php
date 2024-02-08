<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Job_Manager_Field_Editor_Conditionals_Compare
 *
 * @since 1.8.5
 *
 */
class WP_Job_Manager_Field_Editor_Conditionals_Compare {

	/**
	 * @var \WP_Job_Manager_Field_Editor_Conditionals_Job|\WP_Job_Manager_Field_Editor_Conditionals_Resume
	 */
	public $logic;

	/**
	 * @var bool
	 */
	public $case_sensitive;

	/**
	 * WP_Job_Manager_Field_Editor_Conditionals_Compare constructor.
	 *
	 * @param $logic \WP_Job_Manager_Field_Editor_Conditionals_Job|\WP_Job_Manager_Field_Editor_Conditionals_Resume
	 */
	public function __construct( $logic ) {
		$this->logic = $logic;
		$this->case_sensitive = get_option( 'jmfe_logic_case_sensitive', false ) == 1 ? true : false;
	}

	public function is( $mk, $expected ) {

		if( ! $actual = $this->get_post_value( $mk ) ){
			return false;
		}

		if( is_array( $actual ) ){
			return count( $actual ) === 1 && $actual[0] == $expected;
		}

		return $actual == $expected;
	}

	public function is_not( $mk, $expected ) {

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if ( is_array( $actual ) ) {
			return count( $actual ) === 1 && $actual[0] != $expected;
		}

		return $actual != $expected;
	}

	public function greater_than( $mk, $expected ) {

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if( is_array( $actual ) ){
			$actual = count( $actual );
		}

		return floatval( $actual ) > floatval( $expected );
	}

	public function less_than( $mk, $expected ) {

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if ( is_array( $actual ) ) {
			$actual = count( $actual );
		}

		return floatval( $actual ) < floatval( $expected );
	}

	public function starts_with( $mk, $expected ) {

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if( is_array( $actual ) ){
			return false;
		}

		return strpos( $actual, $expected ) === 0;
	}

	public function starts_with_not( $mk, $expected ) {

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if ( is_array( $actual ) ) {
			return false;
		}

		return strpos( $actual, $expected ) !== 0;
	}

	public function ends_with( $mk, $expected ) {

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if ( is_array( $actual ) || strlen( $actual ) < strlen( $expected ) ) {
			return false;
		}

		$expected_length = strlen( $expected );

		// Get substring using negative for length of expected, from actual, and compare
		return ( substr( $actual, -$expected_length ) == $expected );
	}

	public function ends_with_not( $mk, $expected ) {

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if ( is_array( $actual ) || strlen( $actual ) < strlen( $expected ) ) {
			return false;
		}

		$expected_length = strlen( $expected );

		// Get substring using negative for length of expected, from actual, and compare
		return ( substr( $actual, -$expected_length ) != $expected );
	}

	public function contains( $mk, $expected ){

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if( is_array( $actual ) ){
			$result = in_array( $expected, $actual, false );
		} else {
			$result = strpos( $actual, $expected ) !== FALSE;
		}

		return $result;
	}

	public function contains_not( $mk, $expected ){

		if ( ! $actual = $this->get_post_value( $mk ) ) {
			return false;
		}

		if ( is_array( $actual ) ) {
			$result = ! in_array( $expected, $actual, false );
		} else {
			$result = strpos( $actual, $expected ) === false;
		}

		return $result;
	}

	public function get_post_value( $mk ){

		// File fields will be in $_FILES not $_POST
		// TODO: check format and values in array
		if ( is_array( $_FILES ) && array_key_exists( $mk, $_FILES ) && ! array_key_exists( $mk, $_POST ) ) {
			$actual = $_FILES[ $mk ];
		} elseif ( ! array_key_exists( $mk, $_POST ) ) {
			return false;
		} else {
			$actual = sanitize_text_field( $_POST[ $mk ] );
		}

		if( $this->case_sensitive ){

			if( is_array( $actual ) ){
				$actual = array_map( 'strtolower', $actual );
			} else {
				$actual = strtolower( $actual );
			}

		}

		return apply_filters( 'field_editor_conditional_logic_compare_get_post_value', $actual, $mk, $this );
	}

	/**
	 * Main Conditional Logic class
	 *
	 *
	 * @since 1.8.5
	 *
	 * @return \WP_Job_Manager_Field_Editor_Conditionals_Job|\WP_Job_Manager_Field_Editor_Conditionals_Resume
	 */
	public function logic(){
		return $this->logic;
	}
}
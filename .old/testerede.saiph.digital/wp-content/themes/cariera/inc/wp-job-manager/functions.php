<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newly Job Posted
 *
 * @since   1.1.0
 * @version 1.5.3
 */
if ( ! function_exists( 'cariera_newly_posted' ) ) {
	function cariera_newly_posted() {
		global $post;

		$now       = date( 'U' );
		$published = get_the_time( 'U' );
		$new       = false;

		// set to 48 hours in seconds.
		if ( $now - $published <= 2 * 24 * 60 * 60 ) {
			$new = true;
		}

		return $new;
	}
}

/**
 * Rating class function
 *
 * @since 1.1.0
 */
if ( ! function_exists( 'cariera_get_rating_class' ) ) {
	function cariera_get_rating_class( $average ) {
		if ( ! $average ) {
				$class = 'no-stars';
		} else {
			switch ( $average ) {
				case $average >= 1 && $average < 1.5:
					$class = 'one-stars';
					break;
				case $average >= 1.5 && $average < 2:
					$class = 'one-and-half-stars';
					break;
				case $average >= 2 && $average < 2.5:
					$class = 'two-stars';
					break;
				case $average >= 2.5 && $average < 3:
					$class = 'two-and-half-stars';
					break;
				case $average >= 3 && $average < 3.5:
					$class = 'three-stars';
					break;
				case $average >= 3.5 && $average < 4:
					$class = 'three-and-half-stars';
					break;
				case $average >= 4 && $average < 4.5:
					$class = 'four-stars';
					break;
				case $average >= 4.5 && $average < 5:
					$class = 'four-and-half-stars';
					break;
				case $average >= 5:
					$class = 'five-stars';
					break;

				default:
					$class = 'no-stars';
					break;
			}
		}
		return $class;
	}
}

/**
 * Show Number of Job Applications
 *
 * @since 1.2.5
 */
if ( ! function_exists( 'cariera_job_applications' ) ) {
	function cariera_job_applications() {
		if ( ! class_exists( 'WP_Job_Manager_Applications' ) ) {
			return;
		}

		global $post;

		$count = get_job_application_count( $post->ID );

		echo '<span>' . esc_html( $count ) . ' ' . esc_html__( 'Application(s)', 'cariera' ) . '</span>';
	}
}

/**
 * Company Logos
 *
 * @since   1.3.2
 * @version 1.6.6
 */
if ( ! function_exists( 'cariera_the_company_logo' ) ) {
	function cariera_the_company_logo( $args = [] ) {
		$defaults = apply_filters(
			'cariera_the_company_logo_args',
			[
				'size'    => 'thumbnail',
				'default' => null,
				'post'    => null,
			]
		);

		$args = wp_parse_args( $defaults, $args );
		$size = apply_filters( 'cariera_company_logo_size', $args['size'] );

		the_company_logo( $size, $args['default'], $args['post'] );
	}
}

/**
 * Output the job's min & max rate if there is any
 *
 * @since 1.4.1
 */
function cariera_job_rate() {
	global $post;

	$currency_position = get_option( 'cariera_currency_position', 'before' );
	$rate_min          = get_post_meta( $post->ID, '_rate_min', true );

	if ( $rate_min ) {
		$rate_max = get_post_meta( $post->ID, '_rate_max', true );

		// Currency Symbol Before.
		if ( 'before' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}
		echo esc_html( $rate_min );
		// Currency Symbol After.
		if ( 'after' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}

		// MAX Rate if there is any.
		if ( ! empty( $rate_max ) ) {
			echo esc_html( ' - ' );

			// Currency Symbol Before.
			if ( 'before' === $currency_position ) {
				echo \Cariera\currency_symbol();
			}
			echo esc_html( $rate_max );
			// Currency Symbol After.
			if ( 'after' === $currency_position ) {
				echo \Cariera\currency_symbol();
			}
		}
		esc_html_e( '/hour', 'cariera' );
	}
}

/**
 * Output the job's min & max salary if there is any
 *
 * @since 1.4.1
 */
function cariera_job_salary() {
	global $post;

	$currency_position = get_option( 'cariera_currency_position', 'before' );
	$salary_min        = get_post_meta( $post->ID, '_salary_min', true );

	if ( $salary_min ) {
		$salary_max = get_post_meta( $post->ID, '_salary_max', true );

		// Currency Symbol Before.
		if ( 'before' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}
		echo esc_html( $salary_min );
		// Currency Symbol After.
		if ( 'after' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}

		// MAX Salary if there is any.
		if ( ! empty( $salary_max ) ) {
			echo esc_html( ' - ' );

			// Currency Symbol Before.
			if ( 'before' === $currency_position ) {
				echo \Cariera\currency_symbol();
			}
			echo esc_html( $salary_max );
			// Currency Symbol After.
			if ( 'after' === $currency_position ) {
				echo \Cariera\currency_symbol();
			}
		}
	}
}

/**
 * Outputting a job listing in the map
 *
 * @since   1.5.5
 * @version 1.6.0
 */
function cariera_job_map() {
	global $post;

	$job_map = cariera_get_option( 'cariera_job_map' );
	$lng     = $post->geolocation_long;

	if ( ! $job_map || empty( $lng ) ) {
		return;
	}

	if ( \Cariera\cariera_core_is_activated() ) {
		$company = get_post( cariera_get_the_company() );
	} else {
		$company = '';
	}

	if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
		$logo = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
	} else {
		$logo = get_the_company_logo();
	}

	if ( ! empty( $logo ) ) {
		$logo_img = $logo;
	} else {
		$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
	}

	echo '<div id="job-map" data-longitude="' . esc_attr( $post->geolocation_long ) . '" data-latitude="' . esc_attr( $post->geolocation_lat ) . '" data-thumbnail="' . esc_attr( $logo_img ) . '" data-id="listing-id-' . get_the_ID() . '"></div>';
}

/**
 * Returning the single job layout option
 *
 * @since   1.5.5
 * @version 1.5.5
 */
function cariera_single_job_layout() {
	$layout = apply_filters( 'cariera_job_manager_single_job_layout', get_option( 'cariera_job_manager_single_job_layout' ) );

	if ( empty( $layout ) ) {
		$layout = 'v1';
	}

	return $layout;
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adding data to the Database for the views
 *
 * @since  1.3.4
 */
if ( ! function_exists( 'cariera_set_this_stats_for_chart' ) ) {
	function cariera_set_this_stats_for_chart( $author_id, $listing_id, $type ) {

		$table = 'cariera_listing_stats_views';

		$current_time = date( 'Y-m-d' );
		$current_time = strtotime( $current_time );

		// main function
		// cariera_create_stats_table_views();

		$listing_title = get_the_title( $listing_id );
		$allCounts     = '';

		// Check if already have.
		$ndatDta     = [];
		$condition   = "listing_id ='$listing_id' AND action_type='$type'";
		$data_exists = cariera_get_data_from_db( $table, '*', $condition );

		if ( ! empty( $data_exists ) ) {
			// Already exists.
			$hasData = false;
			foreach ( $data_exists as $indx => $val ) {
				$datDta = $val->month;
				$datDta = unserialize( $datDta );

				$hasData  = false;
				$resCount = '';

				if ( ! empty( $datDta ) ) {
					foreach ( $datDta as $ind => $singleData ) {
						$savedDate  = $singleData['date'];
						$savedcount = $singleData['count'];

						// New.
						$ndatDta[ $ind ]['date']  = $savedDate;
						$ndatDta[ $ind ]['count'] = $savedcount;
						$allCounts                = $val->count;

						if ( $savedDate == "$current_time" ) {
							$hasData                  = true;
							$ndatDta[ $ind ]['count'] = $savedcount + 1;
						}
						$resCount = $ind;
					}

					if ( empty( $hasData ) ) {
						$resCount++;
						$ndatDta[ $resCount ]['date']  = $current_time;
						$ndatDta[ $resCount ]['count'] = 1;
					}
				}
			}

			if ( ! empty( $ndatDta ) ) {
				$allCounts = $allCounts + 1;
				$ndatDta   = serialize( $ndatDta );

				$where = [
					'listing_id' => $listing_id,
				];

				$dataArray = [
					'month' => $ndatDta,
					'count' => $allCounts,
				];

				cariera_update_data_in_db( $table, $dataArray, $where );
			}
		} else {
			// new record
			$log_record = [
				[
					'date'  => $current_time,
					'count' => 1,
				],
			];
			$log_record = serialize( $log_record );

			$dataArray = [
				'user_id'       => $author_id,
				'listing_id'    => $listing_id,
				'listing_title' => $listing_title,
				'post_type'     => get_post_type( $listing_id ),
				'action_type'   => $type,
				'month'         => $log_record,
				'count'         => 1,
			];

			cariera_insert_data_in_db( $table, $dataArray );
		}

	}
}

/**
 * Update Post views for when single pages are visited
 *
 * @since  1.3.4
 */
function cariera_post_view_count() {
	global $post;

	$listing_id     = $post->ID;
	$listing_status = get_post_status( $listing_id );
	$author_id      = $post->post_author;

	// cariera_create_stats_table_views();

	if ( is_singular( 'job_listing' ) || is_singular( 'resume' ) || is_singular( 'company' ) ) {
		if ( $listing_status == 'publish' ) {
			$table = 'cariera_listing_stats_views';
			$data  = [ 'user_id' => $author_id ];
			$where = [ 'listing_id' => $listing_id ];

			cariera_update_data_in_db( $table, $data, $where );

			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();

				if ( $user_id != $author_id ) {
					cariera_set_this_stats_for_chart( $author_id, $listing_id, 'view' );
				}
			} else {
				cariera_set_this_stats_for_chart( $author_id, $listing_id, 'view' );
			}
		}
	}
}

add_action( 'cariera_single_listing_data', 'cariera_post_view_count' );

/**
 * Send the data via AJAX
 *
 * @since  1.3.4
 */
if ( ! function_exists( 'cariera_views_chart_ajax_data' ) ) {
	function cariera_views_chart_ajax_data() {
		$data_response_send  = [];
		$data_response       = [];
		$current_user_id     = get_current_user_id();
		$current_date        = date( 'Y-m-d' );
		$current_time        = strtotime( $current_date );
		$data_over_all_count = 0;
		$month_nr            = date( 'm' );
		$year_nr             = date( 'Y' );

		$table         = 'cariera_listing_stats_views';
		$condition     = "user_id='$current_user_id'";
		$get_row       = cariera_get_data_from_db( $table, '*', $condition );
		$days_of_month = cariera_get_days_of_month( $month_nr, $year_nr );

		if ( ! empty( $days_of_month ) ) {
			$count = 1;

			foreach ( $days_of_month as $single_day ) {
				$data_count = 0;

				if ( ! empty( $get_row ) ) {
					foreach ( $get_row as $indx => $val ) {
						$datDta  = $val->month;
						$datDta  = unserialize( $datDta );
						$ndatDta = $datDta;

						if ( ! empty( $datDta ) ) {
							foreach ( $datDta as $ind => $single_data ) {
								$saved_date  = $single_data['date'];
								$saved_count = $single_data['count'];

								if ( $saved_date == "$single_day" ) {
									$data_count = $data_count + $saved_count;
								}
							}
						}
					}

					if ( ! empty( $data_count ) ) {
						$data_over_all_count = $data_over_all_count + $data_count;
					}
				}

				$data_response[] = [
					'x' => $count,
					'y' => $data_count,
				];

				$count++;
			}
		}

		$data_response_send['counts'] = $data_over_all_count;
		$data_response_send['data']   = $data_response;

		exit( wp_json_encode( $data_response_send ) );
	}
}

add_action( 'wp_ajax_cariera_views_chart_ajax_data', 'cariera_views_chart_ajax_data' );
add_action( 'wp_ajax_nopriv_cariera_views_chart_ajax_data', 'cariera_views_chart_ajax_data' );

/**
 * Monthly user views
 *
 * @since  1.6.2
 */
if ( ! function_exists( 'cariera_user_monthly_views' ) ) {
	function cariera_user_monthly_views( $user = null ) {
		if ( $user == null ) {
			return;
		}

		$current_user_id     = $user;
		$current_date        = date( 'Y-m-d' );
		$current_time        = strtotime( $current_date );
		$data_over_all_count = 0;
		$month_nr            = date( 'm' );
		$year_nr             = date( 'Y' );

		$table         = 'cariera_listing_stats_views';
		$condition     = "user_id='$current_user_id'";
		$get_row       = cariera_get_data_from_db( $table, '*', $condition );
		$days_of_month = cariera_get_days_of_month( $month_nr, $year_nr );

		if ( ! empty( $days_of_month ) ) {
			$count = 1;

			foreach ( $days_of_month as $single_day ) {
				$data_count = 0;

				if ( ! empty( $get_row ) ) {
					foreach ( $get_row as $indx => $val ) {
						$datDta  = $val->month;
						$datDta  = unserialize( $datDta );
						$ndatDta = $datDta;

						if ( ! empty( $datDta ) ) {
							foreach ( $datDta as $ind => $single_data ) {
								$saved_date  = $single_data['date'];
								$saved_count = $single_data['count'];

								if ( $saved_date == "$single_day" ) {
									$data_count = $data_count + $saved_count;
								}
							}
						}
					}

					if ( ! empty( $data_count ) ) {
						$data_over_all_count = $data_over_all_count + $data_count;
					}
				}

				$count++;
			}
		}

		return $data_over_all_count;
	}
}

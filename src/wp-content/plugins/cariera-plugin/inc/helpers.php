<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TODO: Check this on how this will be added later on before total rework. version: 1.7.3
/**
 * Initializing Cariera Core
 *
 * @since  1.2.4
 */
function cariera_init() {
	global $CarieraCore;

	// TODO: This needs to be removed for the new Importer handling.
	$CarieraCore                        = new \CarieraCore();
	$CarieraCore['path']                = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
	$CarieraCore['url']                 = plugin_dir_url( __FILE__ );
	$CarieraCore['CarieraDemoImporter'] = new \CarieraDemoImporter();
	apply_filters( 'cariera/config', $CarieraCore );
	$CarieraCore->run();
}

add_action( 'init', 'cariera_init', 10, 1 );

/**
 * Gets and includes template files.
 *
 * @since 1.7.2
 * @param mixed  $template_name
 * @param array  $args (default: array()).
 * @param string $template_path (default: '').
 * @param string $default_path (default: '').
 */
function cariera_get_template( $template_name, $args = [], $template_path = 'cariera_core', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Please, forgive us.
		extract( $args );
	}
	include cariera_locate_template( $template_name, $template_path, $default_path );
}

/**
 * Locates a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *      yourtheme       /   $template_path  /   $template_name
 *      yourtheme       /   $template_name
 *      $default_path   /   $template_name
 *
 * @since 1.7.2
 * @param string      $template_name
 * @param string      $template_path (default: 'cariera_core').
 * @param string|bool $default_path (default: '') False to not load a default.
 * @return string
 */
function cariera_locate_template( $template_name, $template_path = 'cariera_core', $default_path = '' ) {
	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		[
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		]
	);

	// Get default template.
	if ( ! $template && false !== $default_path ) {
		$default_path = $default_path ? $default_path : CARIERA_PLUGIN_DIR . '/templates/';
		if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
			$template = trailingslashit( $default_path ) . $template_name;
		}
	}

	// Return what we found.
	return apply_filters( 'cariera_core_locate_template', $template, $template_name, $template_path );
}

/**
 * Gets template part (for templates in loops).
 *
 * @since 1.7.2
 * @param string      $slug
 * @param string      $name (default: '').
 * @param string      $template_path (default: 'cariera_core').
 * @param string|bool $default_path (default: '') False to not load a default.
 */
function cariera_get_template_part( $slug, $name = '', $template_path = 'cariera_core', $default_path = '' ) {
	$template = '';

	if ( $name ) {
		$template = cariera_locate_template( "{$slug}-{$name}.php", $template_path, $default_path );
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/cariera_core/slug.php.
	if ( ! $template ) {
		$template = cariera_locate_template( "{$slug}.php", $template_path, $default_path );
	}

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 *  Limits number of words from string.
 *
 * @since  1.0
 */
if ( ! function_exists( 'cariera_string_limit_words' ) ) {
	function cariera_string_limit_words( $string, $word_limit ) {
		$words = explode( ' ', $string, ( $word_limit + 1 ) );
		if ( count( $words ) > $word_limit ) {
			array_pop( $words );
			return implode( ' ', $words );
		} else {
			return implode( ' ', $words );
		}
	}
}

/**
 * Custom Job Manager Category Dropdown Function
 *
 * @since  1.2.7
 */
function cariera_job_manager_dropdown_category( $args = '' ) {
	if ( ! class_exists( 'WP_Job_Manager' ) ) {
		return;
	}

	$defaults = [
		'orderby'         => 'id',
		'order'           => 'ASC',
		'show_count'      => 0,
		'hide_empty'      => apply_filters( 'cariera_job_manager_dropdown_category_hide_empty', 1 ),
		'parent'          => '',
		'child_of'        => 0,
		'exclude'         => '',
		'echo'            => 1,
		'selected'        => 0,
		'hierarchical'    => 0,
		'name'            => 'cat',
		'id'              => '',
		'class'           => 'job-manager-category-dropdown cariera-select2-search ' . ( is_rtl() ? 'chosen-rtl' : '' ),
		'depth'           => 0,
		'taxonomy'        => 'job_listing_category',
		'value'           => 'id',
		'multiple'        => true,
		'show_option_all' => false,
		'placeholder'     => esc_html__( 'Choose a category&hellip;', 'cariera' ),
		'no_results_text' => esc_html__( 'No results match', 'cariera' ),
		'multiple_text'   => esc_html__( 'Select Some Options', 'cariera' ),
	];

	$r = wp_parse_args( $args, $defaults );

	if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	if ( ! isset( $r['search_category_slugs'] ) ) {
		$r['search_category_slugs'] = wpjm_get_category_slugs_from_search_query_string();
	}

	/** This filter is documented in wp-job-manager.php */
	$r['lang'] = apply_filters( 'wpjm_lang', null );

	// Store in a transient to help sites with many cats.
	$categories_hash = 'jm_cats_' . md5( wp_json_encode( $r ) . WP_Job_Manager_Cache_Helper::get_transient_version( 'jm_get_' . $r['taxonomy'] ) );
	$categories      = get_transient( $categories_hash );

	if ( empty( $categories ) ) {
		$args = [
			'taxonomy'     => $r['taxonomy'],
			'orderby'      => $r['orderby'],
			'order'        => $r['order'],
			'hide_empty'   => $r['hide_empty'],
			'parent'       => $r['parent'],
			'child_of'     => $r['child_of'],
			'exclude'      => $r['exclude'],
			'hierarchical' => $r['hierarchical'],
		];

		$categories = get_terms( $args );

		if ( ! empty( $r['search_category_slugs'] ) ) {
			$categories = array_merge(
				$categories,
				wpjm_get_categories_by_slug( $r['search_category_slugs'], $args, $categories )
			);
		}

		set_transient( $categories_hash, $categories, DAY_IN_SECONDS * 7 );
	}

	// $name       = esc_attr( $name );
	// $class      = esc_attr( $class );
	$id = $r['id'] ? $r['id'] : $r['name'];

	$output = "<select name='" . esc_attr( $r['name'] ) . "' id='" . esc_attr( $id ) . "' class='" . esc_attr( $r['class'] ) . "' " . ( $r['multiple'] ? "multiple='multiple'" : '' ) . " data-placeholder='" . esc_attr( $r['placeholder'] ) . "' data-no_results_text='" . esc_attr( $r['no_results_text'] ) . "' data-multiple_text='" . esc_attr( $r['multiple_text'] ) . "'>\n";

	if ( $r['show_option_all'] ) {
		$output .= '<option value="">' . esc_html( $r['show_option_all'] ) . '</option>';
	}

	if ( ! empty( $categories ) ) {
		include_once JOB_MANAGER_PLUGIN_DIR . '/includes/class-wp-job-manager-category-walker.php';

		$walker = new WP_Job_Manager_Category_Walker();

		if ( $r['hierarchical'] ) {
			$depth = $r['depth'];  // Walk the full depth.
		} else {
			$depth = -1; // Flat.
		}

		$output .= $walker->walk( $categories, $depth, $r );
	}

	$output .= "</select>\n";

	if ( $r['echo'] ) {
		echo $output;
	}

	return $output;
}

// TODO: check this for 1.7.3. after import this function might not be needed because taxonomy use metas now.
/**
 * Getting partitions
 *
 * @since  1.3.0
 */
function cariera_partition( $list, $p ) {
	$listlen   = count( $list );
	$partlen   = floor( $listlen / $p );
	$partrem   = $listlen % $p;
	$partition = [];
	$mark      = 0;
	for ( $px = 0; $px < $p; $px++ ) {
		$incr             = ( $px < $partrem ) ? $partlen + 1 : $partlen;
		$partition[ $px ] = array_slice( $list, $mark, $incr );
		$mark            += $incr;
	}
	return $partition;
}

/**
 * Count Posts based on their status
 *
 * @since  1.3.4
 */
function cariera_count_user_posts_by_status( $post_author = null, $post_type = [], $post_status = [] ) {
	global $wpdb;

	if ( empty( $post_author ) ) {
		return 0;
	}

	$post_status = (array) $post_status;
	$post_type   = (array) $post_type;

	$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = %d AND ", $post_author );

	// Post status.
	if ( ! empty( $post_status ) ) {
		$argtype = array_fill( 0, count( $post_status ), '%s' );
		$where   = '(post_status=' . implode( ' OR post_status=', $argtype ) . ') AND ';
		$sql    .= $wpdb->prepare( $where, $post_status );
	}

	// Post type.
	if ( ! empty( $post_type ) ) {
		$argtype = array_fill( 0, count( $post_type ), '%s' );
		$where   = '(post_type=' . implode( ' OR post_type=', $argtype ) . ') AND ';
		$sql    .= $wpdb->prepare( $where, $post_type );
	}

	$sql  .= '1=1';
	$count = $wpdb->get_var( $sql );

	return $count;
}

/**
 * Get data in Database
 *
 * @since  1.3.4
 */
if ( ! function_exists( 'cariera_get_data_from_db' ) ) {
	function cariera_get_data_from_db( $table, $data, $condition ) {
		// TODO: needs to be removed when the statistics update will start.
		global $wpdb;

		$dbprefix = $wpdb->prefix;

		$table = $dbprefix . $table;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) == $table ) {
			$query  = '';
			$query  = "SELECT $data from $table WHERE $condition ORDER BY main_id DESC";
			$result = $wpdb->get_results( $query );
			return $result;
		}

		return;
	}
}

/**
 * Insert data in Database
 *
 * @since  1.3.4
 */
if ( ! function_exists( 'cariera_insert_data_in_db' ) ) {
	function cariera_insert_data_in_db( $table, $dataArray ) {
		// TODO: needs to be removed when the statistics update will start.
		global $wpdb;

		$dbprefix = $wpdb->prefix;
		$table    = $dbprefix . $table;
		$result   = $wpdb->insert( $table, $dataArray, $format = null );

		if ( ! empty( $result ) && $result > 0 ) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Update data in Database
 *
 * @since  1.3.4
 */
if ( ! function_exists( 'cariera_update_data_in_db' ) ) {
	function cariera_update_data_in_db( $table, $data, $where ) {
		// TODO: needs to be removed when the statistics update will start.
		global $wpdb;

		$dbprefix = $wpdb->prefix;
		$table    = $dbprefix . $table;

		$result = $wpdb->update( $table, $data, $where, $format = null, $where_format = null );
		if ( ! empty( $result ) && $result > 0 ) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * Get days of the Month
 *
 * @since  1.3.4
 */
if ( ! function_exists( 'cariera_get_days_of_month' ) ) {
	function cariera_get_days_of_month( $month, $year ) {
		// TODO: needs to be removed when the statistics update will start.
		$num         = cal_days_in_month( CAL_GREGORIAN, $month, $year );
		$dates_month = [];

		for ( $i = 1; $i <= $num; $i++ ) {
			$mktime            = mktime( 0, 0, 0, $month, $i, $year );
			$date              = date( 'Y-m-d', $mktime );
			$date              = strtotime( $date );
			$dates_month[ $i ] = $date;
		}

		return $dates_month;
	}
}

/**
 * Get Currency Symbol
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'cariera_currency_symbol' ) ) {
	function cariera_currency_symbol( $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_option( 'cariera_currency_setting' );
		}

		switch ( $currency ) {
			case 'BHD':
				$currency_symbol = esc_html( '.د.ب' );
				break;
			case 'AED':
				$currency_symbol = esc_html( 'د.إ' );
				break;
			case 'AUD':
			case 'ARS':
			case 'CAD':
			case 'CLP':
			case 'COP':
			case 'HKD':
			case 'MXN':
			case 'NZD':
			case 'SGD':
			case 'USD':
				$currency_symbol = esc_html( '&#36;' );
				break;
			case 'BDT':
				$currency_symbol = esc_html( '&#2547;&nbsp;' );
				break;
			case 'LKR':
				$currency_symbol = esc_html( '&#3515;&#3540;&nbsp;' );
				break;
			case 'BGN':
				$currency_symbol = esc_html( '&#1083;&#1074;.' );
				break;
			case 'BRL':
				$currency_symbol = esc_html( '&#82;&#36;' );
				break;
			case 'CHF':
				$currency_symbol = esc_html( '&#67;&#72;&#70;' );
				break;
			case 'CNY':
			case 'JPY':
			case 'RMB':
				$currency_symbol = esc_html( '&yen;' );
				break;
			case 'CZK':
				$currency_symbol = esc_html( '&#75;&#269;' );
				break;
			case 'DKK':
				$currency_symbol = esc_html( 'DKK' );
				break;
			case 'DOP':
				$currency_symbol = esc_html( 'RD&#36;' );
				break;
			case 'EGP':
				$currency_symbol = esc_html( 'EGP' );
				break;
			case 'EUR':
				$currency_symbol = esc_html( '&euro;' );
				break;
			case 'GBP':
				$currency_symbol = esc_html( '&pound;' );
				break;
			case 'HRK':
				$currency_symbol = esc_html( 'Kn' );
				break;
			case 'HUF':
				$currency_symbol = esc_html( '&#70;&#116;' );
				break;
			case 'IDR':
				$currency_symbol = esc_html( 'Rp' );
				break;
			case 'ILS':
				$currency_symbol = esc_html( '&#8362;' );
				break;
			case 'INR':
				$currency_symbol = esc_html( 'Rs.' );
				break;
			case 'ISK':
				$currency_symbol = esc_html( 'Kr.' );
				break;
			case 'KIP':
				$currency_symbol = esc_html( '&#8365;' );
				break;
			case 'KRW':
				$currency_symbol = esc_html( '&#8361;' );
				break;
			case 'MYR':
				$currency_symbol = esc_html( '&#82;&#77;' );
				break;
			case 'NGN':
				$currency_symbol = esc_html( '&#8358;' );
				break;
			case 'NOK':
				$currency_symbol = esc_html( '&#107;&#114;' );
				break;
			case 'NPR':
				$currency_symbol = esc_html( 'Rs.' );
				break;
			case 'PHP':
				$currency_symbol = esc_html( '&#8369;' );
				break;
			case 'PLN':
				$currency_symbol = esc_html( '&#122;&#322;' );
				break;
			case 'PYG':
				$currency_symbol = esc_html( '&#8370;' );
				break;
			case 'RON':
				$currency_symbol = esc_html( 'lei' );
				break;
			case 'RUB':
				$currency_symbol = esc_html( '&#1088;&#1091;&#1073;.' );
				break;
			case 'SEK':
				$currency_symbol = esc_html( '&#107;&#114;' );
				break;
			case 'THB':
				$currency_symbol = esc_html( '&#3647;' );
				break;
			case 'TRY':
				$currency_symbol = esc_html( '&#8378;' );
				break;
			case 'TWD':
				$currency_symbol = esc_html( '&#78;&#84;&#36;' );
				break;
			case 'UAH':
				$currency_symbol = esc_html( '&#8372;' );
				break;
			case 'VND':
				$currency_symbol = esc_html( '&#8363;' );
				break;
			case 'ZAR':
				$currency_symbol = esc_html( '&#82;' );
				break;
			default:
				$currency_symbol = esc_html( '' );
				break;
		}

		return apply_filters( 'cariera_currency_symbol', $currency_symbol, $currency );
	}
}

/**
 * Cariera get option function to avoid site error when another theme has been activated
 *
 * @since  1.4.3
 */
if ( ! function_exists( 'cariera_get_option' ) ) {
	function cariera_get_option( $name ) {
		global $cariera_customize;

		$value = false;

		if ( class_exists( 'Kirki' ) ) {
			$value = Kirki::get_option( 'cariera', $name );
		} elseif ( ! empty( $cariera_customize ) ) {
			$value = $cariera_customize->get_option( $name );
		}

		return apply_filters( 'cariera_get_option', $value, $name );
	}
}

/**
 * Get Contact Form 7 forms
 *
 * @since  1.4.3
 */
if ( ! function_exists( 'cariera_get_forms' ) ) {
	function cariera_get_forms() {
		$forms = [ 0 => esc_html__( 'Please select a form', 'cariera' ) ];

		if ( function_exists( 'wpcf7' ) ) {
			$_forms = get_posts(
				[
					'numberposts' => -1,
					'post_type'   => 'wpcf7_contact_form',
				]
			);

			if ( ! empty( $_forms ) ) {
				foreach ( $_forms as $_form ) {
					$forms[ $_form->ID ] = $_form->post_title;
				}
			}
		}

		return $forms;
	}
}

/**
 * Gecoding addresses
 *
 * @since   1.4.3
 * @version 1.5.4
 */
function cariera_geocode( $address ) {

	// URL encode the address.
	$address = urlencode( $address );

	// WPJM Google API Key.
	$api_key = get_option( 'job_manager_google_maps_api_key' );

	// Cariera Google API Key if WPJM Google API Key doesn't exist.
	if ( empty( $api_key ) ) {
		$api_key = cariera_get_option( 'cariera_gmap_api_key' );
	}

	// Country Restrictions.
	$limit_country = cariera_get_option( 'cariera_map_restriction' );

	if ( $limit_country ) {
		$url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key={$api_key}&components=country:" . $limit_country;
	} else {
		$url = "https://maps.google.com/maps/api/geocode/json?address={$address}&key={$api_key}";
	}

	// JSON Response.
	$resp_json = wp_remote_get( $url );
	$file      = 'wp-content/geocode.txt';
	$resp      = json_decode( wp_remote_retrieve_body( $resp_json ), true );

	if ( $resp['status'] == 'OK' ) {

		// Get the important data.
		$lat               = $resp['results'][0]['geometry']['location']['lat'];
		$long              = $resp['results'][0]['geometry']['location']['lng'];
		$formatted_address = $resp['results'][0]['formatted_address'];

		// Verify if data is complete.
		if ( $lat && $long && $formatted_address ) {

			// Put the data in the array.
			$data_arr = [];

			array_push(
				$data_arr,
				$lat,
				$long,
				$formatted_address
			);

			return $data_arr;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Get nearby listings based on the location
 *
 * @since  1.4.3
 */
function cariera_get_nearby_listings( $lat, $lng, $distance, $radius_type ) {
	global $wpdb;

	if ( $radius_type == 'km' ) {
		$ratio = 6371;
	} else {
		$ratio = 3959;
	}

	$post_ids =
			$wpdb->get_results(
				$wpdb->prepare(
					"
            SELECT DISTINCT
                    geolocation_lat.post_id,
                    geolocation_lat.meta_key,
                    geolocation_lat.meta_value as jobLat,
                    geolocation_long.meta_value as jobLong,
                    ( %d * acos( cos( radians( %f ) ) * cos( radians( geolocation_lat.meta_value ) ) * cos( radians( geolocation_long.meta_value ) - radians( %f ) ) + sin( radians( %f ) ) * sin( radians( geolocation_lat.meta_value ) ) ) ) AS distance 
            
                FROM 
                    $wpdb->postmeta AS geolocation_lat
                    LEFT JOIN $wpdb->postmeta as geolocation_long ON geolocation_lat.post_id = geolocation_long.post_id
                    WHERE geolocation_lat.meta_key = 'geolocation_lat' AND geolocation_long.meta_key = 'geolocation_long'
                    HAVING distance < %d
            ",
					$ratio,
					$lat,
					$lng,
					$lat,
					$distance
				),
				ARRAY_A
			);

	return $post_ids;
}

/**
 * Sort by column
 *
 * @since  1.4.3
 */
function cariera_array_sort_by_column( &$arr, $col, $dir = SORT_ASC ) {
	$sort_col = [];
	foreach ( $arr as $key => $row ) {
		$sort_col[ $key ] = $row[ $col ];
	}

	array_multisort( $sort_col, $dir, $arr );
}

/**
 * Gets a number of posts and displays them as options
 *
 * @since 1.4.8
 */
function cariera_get_post_options( $query_args ) {

	$args = wp_parse_args(
		$query_args,
		[
			'post_type'   => 'post',
			'numberposts' => -1,
		]
	);

	$posts           = get_posts( $args );
	$post_options    = [];
	$post_options[0] = esc_html__( '--Choose page--', 'cariera' );
	if ( $posts ) {
		foreach ( $posts as $post ) {
			$post_options[ $post->ID ] = $post->post_title;
		}
	}

	return $post_options;
}

/**
 * Get Pages
 *
 * @since  1.4.8
 */
function cariera_get_pages_options() {
	return cariera_get_post_options( [ 'post_type' => 'page' ] );
}

/**
 * Generate a random key, used for security reasons
 *
 * @since  1.5.0
 */
if ( ! function_exists( 'cariera_random_key' ) ) {
	function cariera_random_key( $length = 8 ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$return     = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$return .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}
		return $return;
	}
}

/**
 * Get page by title
 *
 * @param [type] $title
 * @param string $post_type
 *
 * @since   1.6.6
 */
if ( ! function_exists( 'cariera_get_page_by_title' ) ) {
	function cariera_get_page_by_title( $title, $post_type = 'page' ) {
		$posts = get_posts(
			[
				'post_type'              => $post_type,
				'title'                  => $title,
				'post_status'            => 'all',
				'numberposts'            => 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			]
		);

		if ( ! empty( $posts ) ) {
			$page = $posts[0];
		} else {
			$page = null;
		}

		return $page;
	}
}

/**
 * Get theme status if activated or not
 *
 * @since   1.5.5
 * @version 1.7.2
 */
function cariera_core_theme_status() {
	if ( ! class_exists( '\Cariera\Onboarding\Onboarding' ) ) {
		return;
	}

	$status = \Cariera\Onboarding\Onboarding::activation_status();

	return $status;
}
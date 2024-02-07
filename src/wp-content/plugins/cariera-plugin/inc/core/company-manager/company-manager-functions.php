<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queries companies with certain criteria and returns them.
 *
 * @since   1.3.0
 * @version 1.6.3
 */
if ( ! function_exists( 'cariera_get_companies' ) ) {
	function cariera_get_companies( $args = [] ) {
		global $cariera_company_keyword;

		$args = wp_parse_args(
			$args,
			[
				'search_keywords'   => '',
				'search_location'   => '',
				'search_categories' => [],
				'offset'            => '',
				'posts_per_page'    => '-1',
				'orderby'           => 'date',
				'order'             => 'DESC',
				'featured'          => null,
				'fields'            => 'all',
			]
		);

		// Query args.
		$query_args = [
			'post_type'              => 'company',
			'post_status'            => 'publish',
			'ignore_sticky_posts'    => 1,
			'offset'                 => absint( $args['offset'] ),
			'posts_per_page'         => intval( $args['posts_per_page'] ),
			'orderby'                => $args['orderby'],
			'order'                  => $args['order'],
			'tax_query'              => [],
			'meta_query'             => [],
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false,
			'fields'                 => $args['fields'],
		];

		if ( $args['posts_per_page'] < 0 ) {
			$query_args['no_found_rows'] = true;
		}

		if ( ! empty( $args['search_location'] ) ) {
			$location_meta_keys = [ 'geolocation_formatted_address', '_company_location', 'geolocation_state_long' ];
			$location_search    = [ 'relation' => 'OR' ];
			foreach ( $location_meta_keys as $meta_key ) {
				$location_search[] = [
					'key'     => $meta_key,
					'value'   => $args['search_location'],
					'compare' => 'like',
				];
			}
			$query_args['meta_query'][] = $location_search;
		}

		if ( ! is_null( $args['featured'] ) ) {
			$query_args['meta_query'][] = [
				'key'     => '_featured',
				'value'   => '1',
				'compare' => $args['featured'] ? '=' : '!=',
			];
		}

		if ( ! empty( $args['search_categories'] ) ) {
			$field                     = is_numeric( $args['search_categories'][0] ) ? 'term_id' : 'slug';
			$operator                  = 'all' === get_option( 'job_manager_category_filter_type', 'all' ) && count( $args['search_categories'] ) > 1 ? 'AND' : 'IN';
			$query_args['tax_query'][] = [
				'taxonomy'         => 'company_category',
				'field'            => $field,
				'terms'            => array_values( $args['search_categories'] ),
				'include_children' => 'AND' !== $operator,
				'operator'         => $operator,
			];
		}

		if ( 'featured' === $args['orderby'] ) {
			$query_args['orderby'] = [
				'menu_order' => 'ASC',
				'date'       => 'DESC',
				'ID'         => 'DESC',
			];
		}

		if ( 'rand_featured' === $args['orderby'] ) {
			$query_args['orderby'] = [
				'menu_order' => 'ASC',
				'rand'       => 'ASC',
			];
		}

		$cariera_company_keyword = sanitize_text_field( $args['search_keywords'] );

		if ( $cariera_company_keyword ) {
			$query_args['s'] = $cariera_company_keyword;
			add_filter( 'posts_search', 'cariera_get_company_keyword_search' );
		}

		$query_args = apply_filters( 'cariera_get_companies', $query_args, $args );

		if ( empty( $query_args['meta_query'] ) ) {
			unset( $query_args['meta_query'] );
		}

		if ( empty( $query_args['tax_query'] ) ) {
			unset( $query_args['tax_query'] );
		}

		/** This filter is documented in wp-job-manager.php */
		// $query_args['lang'] = apply_filters( 'cariera_company_lang', null );

		// Filter args.
		$query_args = apply_filters( 'cariera_get_companies_query_args', $query_args, $args );

		// Generate hash.
		$to_hash         = wp_json_encode( $query_args );
		$query_args_hash = 'jm_' . md5( $to_hash ) . WP_Job_Manager_Cache_Helper::get_transient_version( 'cariera_get_company_listings' );

		do_action( 'before_get_companies', $query_args, $args );

		$cached_query = true;
		if ( false === ( $result = get_transient( $query_args_hash ) ) ) {
			$cached_query = false;
			$result       = new WP_Query( $query_args );
			set_transient( $query_args_hash, $result, DAY_IN_SECONDS );
		}
		if ( $cached_query ) {
			// Random order is cached so shuffle them.
			if ( 'rand_featured' === $args['orderby'] ) {
				usort( $result->posts, 'cariera_companies_shuffle_featured_post_results_helper' );
			} elseif ( 'rand' === $args['orderby'] ) {
				shuffle( $result->posts );
			}
		}

		do_action( 'after_get_companies', $query_args, $args );

		remove_filter( 'posts_search', 'cariera_get_company_keyword_search' );

		return $result;
	}
}

/**
 * Helper function to maintain featured status when shuffling results.
 *
 * @since 1.5.0
 */
if ( ! function_exists( 'cariera_companies_shuffle_featured_post_results_helper' ) ) {
	function cariera_companies_shuffle_featured_post_results_helper( $a, $b ) {
		if ( -1 === $a->menu_order || -1 === $b->menu_order ) {
			// Left is featured.
			if ( 0 === $b->menu_order ) {
				return -1;
			}
			// Right is featured.
			if ( 0 === $a->menu_order ) {
				return 1;
			}
		}
		return rand( -1, 1 );
	}
}

/**
 * Search based on Company keywords
 *
 * @since   1.3.0
 * @version 1.5.3
 */
if ( ! function_exists( 'cariera_get_company_keyword_search' ) ) {
	function cariera_get_company_keyword_search( $search ) {
		global $wpdb, $cariera_company_keyword;

		// Searchable Meta Keys: set to empty to search all meta keys.
		$searchable_meta_keys = [
			'_company_tagline',
			'_company_location',
		];

		$searchable_meta_keys = apply_filters( 'cariera_company_searchable_meta_keys', $searchable_meta_keys );

		// Set Search DB Conditions.
		$conditions = [];

		// Search Post Meta.
		if ( apply_filters( 'cariera_company_search_post_meta', true ) ) {

			// Only selected meta keys.
			if ( $searchable_meta_keys ) {
				$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ( '" . implode( "','", array_map( 'esc_sql', $searchable_meta_keys ) ) . "' ) AND meta_value LIKE '%" . esc_sql( $cariera_company_keyword ) . "%' )";
			} else {
				// No meta keys defined, search all post meta value.
				$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%" . esc_sql( $cariera_company_keyword ) . "%' )";
			}
		}

		// Search taxonomy.
		$conditions[] = "{$wpdb->posts}.ID IN ( SELECT object_id FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id WHERE t.name LIKE '%" . esc_sql( $cariera_company_keyword ) . "%' )";

		$conditions = apply_filters( 'cariera_company_search_conditions', $conditions, $cariera_company_keyword );
		if ( empty( $conditions ) ) {
			return $search;
		}

		$conditions_str = implode( ' OR ', $conditions );

		if ( ! empty( $search ) ) {
			$search = preg_replace( '/^ AND /', '', $search );
			$search = " AND ( {$search} OR ( {$conditions_str} ) )";
		} else {
			$search = " AND ( {$conditions_str} )";
		}

		return $search;
	}
}

/**
 * Order featured companies
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_order_featured_company' ) ) {
	function cariera_order_featured_company( $args ) {
		global $wpdb;

		$args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_title ASC";

		return $args;
	}
}

/**
 * Get current company page URL.
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_current_company_page_url' ) ) {
	function cariera_get_current_company_page_url() {
		if ( defined( 'COMPANIES_IS_ON_FRONT' ) ) {
			$link = home_url( '/' );
		} elseif ( cariera_is_company_taxonomy() ) {
			$queried_object = get_queried_object();
			$link           = get_term_link( $queried_object->slug, $queried_object->taxonomy );
		} else {
			$link = get_permalink( cariera_get_company_page_id( 'companies' ) );
		}

		return $link;
	}
}

/**
 * Output Company Class
 *
 * @since  1.3.0
 */
function cariera_company_class( $class = '', $post_id = null ) {
	echo 'class="' . esc_attr( join( ' ', cariera_get_company_class( $class, $post_id ) ) ) . '"';
}

/**
 * Get Company Class
 *
 * @since  1.3.0
 */
function cariera_get_company_class( $class = '', $post_id = null ) {
	$post = get_post( $post_id );

	if ( empty( $post ) || 'company' !== $post->post_type ) {
		return [];
	}

	$classes = [];

	if ( ! empty( $class ) ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = array_merge( $classes, $class );
	}

	return get_post_class( $classes, $post->ID );
}

/**
 * Check if company is featured
 *
 * @since  1.3.0
 */
function cariera_is_company_featured( $post = null ) {
	$post = get_post( $post );

	return $post->_featured ? true : false;
}

/**
 * Get the company status
 *
 * @since  1.4.4
 */
function cariera_company_status( $post = null ) {
	$post   = get_post( $post );
	$status = $post->post_status;

	if ( 'publish' === $status ) {
		$status = esc_html__( 'Published', 'cariera' );
	} elseif ( 'expired' === $status ) {
		$status = esc_html__( 'Expired', 'cariera' );
	} elseif ( 'pending' === $status ) {
		$status = esc_html__( 'Pending Review', 'cariera' );
	} elseif ( 'hidden' === $status ) {
		$status = esc_html__( 'Hidden', 'cariera' );
	} elseif ( 'preview' === $status ) {
		$status = esc_html__( 'Preview', 'cariera' );
	} else {
		$status = esc_html__( 'Inactive', 'cariera' );
	}

	return apply_filters( 'cariera_the_company_status', $status, $post );
}

/**
 * Check if it is a Company Taxonomy
 *
 * @since  1.3.0
 */
function cariera_is_company_taxonomy() {
	return is_tax( get_object_taxonomies( 'company' ) );
}

/**
 * Gets and includes template files.
 *
 * @since 1.3.0
 */
function get_company_template( $template_name, $args = [], $template_path = 'wp-job-manager-companies', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		// Please, forgive us.
		extract( $args ); // phpcs:ignore WordPress.Functions.DontExtract.extract_extract
	}
	include locate_company_template( $template_name, $template_path, $default_path );
}

/**
 * Locates a template and return the path for inclusion.
 *
 * @since 1.3.0
 */
function locate_company_template( $template_name, $template_path = 'wp-job-manager-companies', $default_path = '' ) {
	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		[
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		]
	);

	// Return what we found.
	return apply_filters( 'company_locate_template', $template, $template_name, $template_path );
}

/**
 * Get the number of Companies a user has submitted
 *
 * @since 1.4.4
 */
function cariera_count_user_companies( $user_id = 0 ) {
	global $wpdb;

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'company' AND post_status IN ( 'publish', 'pending', 'expired', 'hidden' );", $user_id ) );
}

/**
 * Get Job Listing Post Type Label
 *
 * @since 1.4.4
 */
if ( ! function_exists( 'cariera_get_job_post_label' ) ) {
	function cariera_get_job_post_label( $plural = false ) {

		$job_object = get_post_type_object( 'job_listing' );

		if ( ! $plural ) {
			$post_label = is_object( $job_object ) ? $job_object->labels->singular_name : esc_html__( 'Job', 'cariera' );
			if ( ! $post_label ) {
				$post_label = esc_html__( 'Job', 'cariera' );
			}
		} else {
			$post_label = is_object( $job_object ) ? $job_object->labels->name : esc_html__( 'Jobs', 'cariera' );
			if ( ! $post_label ) {
				$post_label = esc_html__( 'Jobs', 'cariera' );
			}
		}

		return $post_label;
	}
}

/**
 * True if an the user can post a company. By default, you must be logged in.
 *
 * @since 1.4.4
 */
function cariera_user_can_post_company() {
	$can_post = true;

	if ( ! is_user_logged_in() ) {
		if ( cariera_company_manager_user_requires_account() && ! cariera_enable_registration() ) {
			$can_post = false;
		}
	}

	return apply_filters( 'cariera_user_can_post_company', $can_post );
}

/**
 * True if an the user can edit a company.
 *
 * @since 1.4.4
 */
function cariera_user_can_edit_company( $company_id ) {
	$can_edit = true;

	if ( ! $company_id || ! is_user_logged_in() ) {
		$can_edit = false;
		if ( $company_id
			&& ! cariera_company_manager_user_requires_account()
			&& isset( $_COOKIE[ 'wp-job-manager-submitting-company-key-' . $company_id ] )
			&& $_COOKIE[ 'wp-job-manager-submitting-company-key-' . $company_id ] === get_post_meta( $company_id, '_submitting_key', true )
		) {
			$can_edit = true;
		}
	} else {
		$company = get_post( $company_id );

		if ( ! $company || ( absint( $company->post_author ) !== get_current_user_id() && ! current_user_can( 'edit_post', $company_id ) ) ) {
			$can_edit = false;
		}
	}

	return apply_filters( 'cariera_user_can_edit_company', $can_edit, $company_id );
}

/**
 * True if registration is enabled.
 *
 * @since 1.4.4
 */
function cariera_enable_registration() {
	return apply_filters( 'cariera_enable_registration', get_option( 'cariera_enable_company_registration' ) == 1 ? true : false );
}

/**
 * True if an account is required to post.
 *
 * @since 1.4.4
 */
function cariera_company_manager_user_requires_account() {
	return apply_filters( 'cariera_company_user_requires_account', get_option( 'cariera_company_user_requires_account' ) == 1 ? true : false );
}

/**
 * Checks if users are allowed to edit submissions that are pending approval.
 *
 * @since 1.7.0
 * @return bool
 */
function cariera_company_manager_user_can_edit_pending_submissions() {
	return apply_filters( 'cariera_company_manager_user_can_edit_pending_submissions', 1 === intval( get_option( 'cariera_company_user_can_edit_pending_submissions' ) ) );
}

/**
 * Checks if users are allowed to edit published submissions.
 *
 * @since 1.7.0
 * @return bool
 */
function cariera_company_manager_user_can_edit_published_submissions() {
	/**
	 * Override the setting for allowing a user to edit published company listings.
	 *
	 * @since 1.7.0
	 *
	 * @param bool $can_edit_published_submissions
	 */
	return apply_filters( 'cariera_company_manager_user_can_edit_published_submissions', in_array( get_option( 'cariera_company_user_edit_published_submissions' ), [ 'yes', 'yes_moderated' ], true ) );
}

/**
 * Checks if moderation is required when users edit published submissions.
 *
 * @since 1.7.0
 * @return bool
 */
function cariera_company_published_submission_edits_require_moderation() {
	$require_moderation = 'yes_moderated' === get_option( 'cariera_company_user_edit_published_submissions' );

	/**
	 * Override the setting for user edits to company listings requiring moderation.
	 *
	 * @since 1.7.0
	 *
	 * @param bool $require_moderation
	 */
	return apply_filters( 'cariera_company_published_submission_edits_require_moderation', $require_moderation );
}

/**
 * Whether to create attachments for files that are uploaded with a Company.
 *
 * @since 1.4.4
 */
function cariera_company_attach_uploaded_files() {
	return apply_filters( 'cariera_company_attach_uploaded_files', false );
}

/**
 * Get all Company Taxonomies
 *
 * @since  1.3.0
 */
function cariera_get_all_company_taxonomies() {
	$taxonomies = [];

	$taxonomy_objects = get_object_taxonomies( 'company', 'objects' );
	foreach ( $taxonomy_objects as $taxonomy_object ) {
		$taxonomies[] = [
			'taxonomy' => $taxonomy_object->name,
			'name'     => $taxonomy_object->label,
		];
	}

	return $taxonomies;
}

/**
 * Get Singular "Company" Label
 *
 * @since 1.4.4
 */
function cariera_get_company_manager_singular_label( $lowercase = false ) {

	$singular = get_option( 'cariera_company_manager_cpt_singular_label', 'Company' );
	// In case user saves with empty value.
	if ( empty( $singular ) ) {
		$singular = 'Company';
	}

	$singular = esc_html__( $singular, 'cariera' );

	return apply_filters( 'cariera_company_manager_get_singular_label', $lowercase ? strtolower( $singular ) : $singular );
}

/**
 * Get Plural "Company" Label
 *
 * @since 1.4.4
 */
function cariera_get_company_manager_plural_label( $lowercase = false ) {

	$plural = get_option( 'cariera_company_manager_cpt_plural_label', 'Companies' );
	// In case user saves with empty value.
	if ( empty( $plural ) ) {
		$plural = 'Companies';
	}

	$plural = esc_html__( $plural, 'cariera' );

	return apply_filters( 'cariera_company_manager_get_plural_label', $lowercase ? strtolower( $plural ) : $plural );
}

/**
 * Gets the company id.
 *
 * @since 1.4.7
 */
function cariera_get_the_company( $post = null ) {
	$post = get_post( $post );

	if ( ! $post || 'job_listing' !== $post->post_type ) {
		return '';
	}

	$company_id = get_post_meta( $post->ID, '_company_manager_id', true );

	return apply_filters( 'cariera_get_the_company', $company_id, $post );
}

/**
 * True if an the user can browse companies.
 *
 * @since 1.4.7
 */
function cariera_user_can_browse_companies() {
	$can_browse = true;
	$caps       = get_option( 'cariera_company_manager_browse_company_capability' );

	if ( $caps ) {
		$can_browse = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_browse = true;
				break;
			}
		}
	}

	return apply_filters( 'cariera_company_manager_user_can_browse_companies', $can_browse );
}

/**
 * True if an the user can view a company.
 *
 * @since 1.4.7
 */
function cariera_user_can_view_company( $company_id ) {
	$can_view = true;
	$company  = get_post( $company_id );

	// Allow previews.
	if ( 'preview' === $company->post_status ) {
		return true;
	}

	$caps = get_option( 'cariera_company_manager_view_company_capability' );

	if ( $caps ) {
		$can_view = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_view = true;
				break;
			}
		}
	}

	if ( $company->post_status === 'expired' ) {
		$can_view = false;
	}

	if ( $company->post_author > 0 && absint( $company->post_author ) === get_current_user_id() ) {
		$can_view = true;
	}

	$key = get_post_meta( $company_id, 'share_link_key', true );
	if ( $key && ! empty( $_GET['key'] ) && $key == $_GET['key'] ) {
		$can_view = true;
	}

	return apply_filters( 'cariera_company_manager_user_can_view_company', $can_view, $company_id );
}

/**
 * True if an the user can view a company.
 *
 * @since 1.5.5
 */
function cariera_user_can_view_contact_details( $company_id ) {
	$can_view = true;
	$company  = get_post( $company_id );
	$caps     = get_option( 'cariera_company_manager_contact_company_capability' );

	if ( $caps ) {
		$can_view = false;
		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$can_view = true;
				break;
			}
		}
	}

	if ( $company->post_author > 0 && absint( $company->post_author ) === get_current_user_id() ) {
		$can_view = true;
	}

	$key = get_post_meta( $company_id, 'share_link_key', true );
	if ( $key && ! empty( $_GET['key'] ) && $key == $_GET['key'] ) {
		$can_view = true;
	}

	return apply_filters( 'cariera_user_can_view_contact_details', $can_view, $company_id );
}

/**
 * Check if the option to discourage company search indexing is enabled.
 *
 * @since 1.4.7
 */
function cariera_discourage_company_search_indexing() {
	// Allows overriding the option to discourage search indexing.
	return apply_filters( 'cariera_company_manager_discourage_company_search_indexing', 1 == get_option( 'cariera_company_manager_discourage_company_search_indexing' ) );
}

/**
 * Outputting a company listing in the map
 *
 * @since   1.5.5
 * @version 1.6.6
 */
function cariera_company_map() {
	global $post;

	$company_map = cariera_get_option( 'cariera_company_map' );
	$lng         = $post->geolocation_long;
	$logo        = get_the_company_logo( $post->ID, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );

	if ( ! $company_map || empty( $lng ) ) {
		return;
	}

	if ( ! empty( $logo ) ) {
		$logo_img = $logo;
	} else {
		$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
	}

	echo '<div id="company-map" data-longitude="' . esc_attr( $post->geolocation_long ) . '" data-latitude="' . esc_attr( $post->geolocation_lat ) . '" data-thumbnail="' . esc_attr( $logo_img ) . '" data-id="listing-id-' . esc_attr( get_the_ID() ) . '"></div>';
}

/**
 * Returning the single company layout option
 *
 * @since   1.5.5
 * @version 1.5.5
 */
function cariera_single_company_layout() {
	$layout = apply_filters( 'cariera_single_company_layout', get_option( 'cariera_single_company_layout' ) );

	if ( empty( $layout ) ) {
		$layout = 'v1';
	}

	return $layout;
}

/**
 * Displays or retrieves the current company name with optional content.
 *
 * @since 1.4.4
 */
if ( ! function_exists( 'cariera_company_name' ) ) {
	function cariera_company_name( $before = '', $after = '', $echo = true, $post = null ) {

		$company_name = cariera_get_company_name( $post );

		if ( 0 === strlen( $company_name ) ) {
			return null;
		}

		$company_name = esc_attr( wp_strip_all_tags( $company_name ) );
		$company_name = $before . $company_name . $after;

		if ( $echo ) {
			echo wp_kses_post( $company_name );
		} else {
			return $company_name;
		}
	}
}

/**
 * Gets the company name.
 *
 * @since 1.4.4
 */
if ( ! function_exists( 'cariera_get_company_name' ) ) {
	function cariera_get_company_name( $post = null ) {

		$post = get_post( $post );
		if ( ! $post || 'company' !== $post->post_type ) {
			return '';
		}

		return apply_filters( 'cariera_company_name', $post->_company_name, $post );
	}
}

/**
 * Get company pagination for [companies] shortcode.
 *
 * @since 1.3.0
 */
if ( ! function_exists( 'cariera_get_company_pagination' ) ) {
	function cariera_get_company_pagination( $max_num_pages, $current_page = 1 ) {
		ob_start();

		get_company_template(
			'company-pagination.php',
			[
				'max_num_pages' => $max_num_pages,
				'current_page'  => absint( $current_page ),
			]
		);

		return ob_get_clean();
	}
}

/**
 * Get the Company Permalinks
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_the_company_permalink' ) ) {
	function cariera_get_the_company_permalink( $post = null ) {
		$post = get_post( $post );
		$link = get_permalink( $post );

		return apply_filters( 'cariera_the_company_permalink', $link, $post );
	}
}

// Output the permalink.
if ( ! function_exists( 'cariera_the_company_permalink' ) ) {
	function cariera_the_company_permalink( $post = null ) {
		echo esc_url( cariera_get_the_company_permalink( $post ) );
	}
}

/**
 * Get the category of the company
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_the_company_category' ) ) {
	function cariera_get_the_company_category( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return '';
		}

		if ( ! get_option( 'cariera_company_category' ) ) {
			return '';
		}

		$categories = wp_get_object_terms( $post->ID, 'company_category' );

		if ( is_wp_error( $categories ) ) {
			return '';
		}

		return apply_filters( 'cariera_the_company_category_output', $categories, $post );
	}
}

// Output the category of the company.
if ( ! function_exists( 'cariera_the_company_category_output' ) ) {
	function cariera_the_company_category_output( $post = null ) {
		$categories = cariera_get_the_company_category( $post );

		if ( ! empty( $categories ) ) {
			echo '<ul class="categories">';
			foreach ( $categories as $category ) {
				echo '<li><a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
			}
			echo '</ul>';
		}
	}
}

/**
 * Get an array of categories of the company.
 *
 * @param [type] $post
 */
function cariera_get_the_company_category_array( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'company' ) {
		return '';
	}

	if ( ! get_option( 'cariera_company_category' ) ) {
		return '';
	}

	$categories = wp_get_object_terms( $post->ID, 'company_category', [ 'fields' => 'names' ] );

	if ( is_wp_error( $categories ) ) {
		return '';
	}

	return implode( ', ', $categories );
}

/**
 * Output the array category of the company.
 *
 * @param [type] $post
 */
function cariera_the_company_category_array( $post = null ) {
	echo cariera_get_the_company_category_array( $post );
}

/**
 * Get the team size of the company
 *
 * @since  1.3.0
 */
function cariera_get_the_company_team_size( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'company' ) {
		return '';
	}

	if ( ! get_option( 'cariera_company_team_size' ) ) {
		return '';
	}

	$teams = wp_get_object_terms( $post->ID, 'company_team_size', [ 'fields' => 'names' ] );

	if ( is_wp_error( $teams ) ) {
		return '';
	}

	return implode( ', ', $teams );
}

// Output.
function cariera_the_company_team_size_output( $post = null ) {
	echo cariera_get_the_company_team_size( $post );
}

/**
 * Returns the registration fields used when an account is required.
 *
 * @since 1.4.4
 */
function cariera_get_registration_fields() {
	$account_required = cariera_company_manager_user_requires_account();

	$registration_fields = [];
	if ( cariera_enable_registration() ) {
		$registration_fields['create_account_username'] = [
			'type'     => 'text',
			'label'    => esc_html__( 'Username', 'cariera' ),
			'required' => $account_required,
			'value'    => isset( $_POST['create_account_username'] ) ? sanitize_text_field( wp_unslash( $_POST['create_account_username'] ) ) : '',
		];
		$registration_fields['create_account_password'] = [
			'type'         => 'password',
			'label'        => esc_html__( 'Password', 'cariera' ),
			'autocomplete' => false,
			'required'     => $account_required,
		];
		$password_hint                                  = wpjm_get_password_rules_hint();
		if ( $password_hint ) {
			$registration_fields['create_account_password']['description'] = $password_hint;
		}
		$registration_fields['create_account_password_verify'] = [
			'type'         => 'password',
			'label'        => esc_html__( 'Verify Password', 'cariera' ),
			'autocomplete' => false,
			'required'     => $account_required,
		];
	}

	// Filters the fields used at registration.
	return apply_filters( 'cariera_get_registration_fields', $registration_fields );
}

/**
 * Get the website of the company
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_the_company_website_link' ) ) {
	function cariera_get_the_company_website_link( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		$website = $post->_company_website;

		if ( $website && ! strstr( $website, 'http:' ) && ! strstr( $website, 'https:' ) ) {
			$website = 'http://' . $website;
		}

		return apply_filters( 'the_company_website_link', $website, $post );
	}
}

/**
 * Get the email of the company
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_the_company_email' ) ) {
	function cariera_get_the_company_email( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		return apply_filters( 'cariera_the_company_email', $post->_company_email, $post );
	}
}

/**
 * Get the phone number of the company
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_the_company_phone' ) ) {
	function cariera_get_the_company_phone( $post = null, $post_type = 'company' ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		return apply_filters( 'cariera_the_company_phone', $post->_company_phone, $post );
	}
}

/**
 * Get the company job listings
 *
 * @since   1.3.0
 * @version 1.5.5
 */
if ( ! function_exists( 'cariera_get_the_company_job_listing' ) ) {
	function cariera_get_the_company_job_listing( $company_id, $custom_args = [] ) {
		if ( ! $company_id ) {
			return [];
		}

		$default_args = [
			'post_type'           => 'job_listing',
			'post_status'         => [ 'publish', 'expired', 'pending', 'hidden' ],
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => - 1,
			'orderby'             => 'date',
			'order'               => 'desc',
			'meta_key'            => '_company_manager_id',
			'meta_value'          => $company_id,
		];

		$args = apply_filters( 'company_manager_get_company_job_listings_query_args', array_merge( $default_args, $custom_args ) );

		return new WP_Query( $args );
	}
}

/**
 * Get the company's job listings
 *
 * @since   1.3.0
 * @version 1.5.5
 */
if ( ! function_exists( 'cariera_get_the_company_job_listing_count' ) ) {
	function cariera_get_the_company_job_listing_count( $company_id, $active_only = false ) {
		if ( ! $company_id ) {
			return 0;
		}

		$args = [];
		if ( $active_only ) {
			$args['post_status'] = [ 'publish' ];
		}

		$company_job_listings_query = cariera_get_the_company_job_listing( $company_id, $args );
		$active_job_listings        = $company_job_listings_query->found_posts;

		return apply_filters( 'cariera_get_company_job_listings', $active_job_listings, $company_id, $company_job_listings_query );
	}
}

/**
 * Get the company's active job listings
 *
 * @since   1.3.0
 * @version 1.5.5
 */
if ( ! function_exists( 'cariera_get_the_company_job_listing_active_count' ) ) {
	function cariera_get_the_company_job_listing_active_count( $company_id ) {
		if ( ! $company_id ) {
			$company_id = get_the_ID();
		}

		return cariera_get_the_company_job_listing_count( $company_id, true );
	}
}

/**
 * Get the company location
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_the_company_location' ) ) {
	function cariera_get_the_company_location( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		return apply_filters( 'cariera_the_company_location', $post->_company_location, $post );
	}
}

// Output.
if ( ! function_exists( 'cariera_the_company_location_output' ) ) {
	function cariera_the_company_location_output( $map_link = true, $post = null ) {
		$location = cariera_get_the_company_location( $post );

		if ( $location ) {
			if ( $map_link ) {
				echo apply_filters( 'cariera_the_company_location_map_link', '<a class="google_map_link company-location" href="https://maps.google.com/maps?q=' . urlencode( $location ) . '&zoom=14&size=512x512&maptype=roadmap&sensor=false" target="_blank">' . $location . '</a>', $location, $post );
			} else {
				echo '<span class="company-location">' . $location . '</span>';
			}
		}
	}
}

/**
 * Company created since
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_company_since' ) ) {
	function cariera_get_company_since( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		return apply_filters( 'cariera_get_company_since', $post->_company_since, $post );
	}
}

/**
 * Company Social
 *
 * @since   1.3.0
 * @version 1.5.3
 */

// Facebook.
if ( ! function_exists( 'cariera_get_the_company_fb' ) ) {
	function cariera_get_the_company_fb( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		if ( $post->_company_facebook && ! strstr( $post->_company_facebook, 'http:' ) && ! strstr( $post->_company_facebook, 'https:' ) ) {
			$post->_company_facebook = 'https://' . $post->_company_facebook;
		}

		return apply_filters( 'cariera_company_fb_output', $post->_company_facebook, $post );
	}
}

if ( ! function_exists( 'cariera_company_fb_output' ) ) {
	function cariera_company_fb_output( $post = null ) {
		if ( ! empty( cariera_get_the_company_fb( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_company_fb( $post ) ) . '" class="company-facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>';
		}
	}
}

// Twitter.
if ( ! function_exists( 'cariera_get_the_company_twitter' ) ) {
	function cariera_get_the_company_twitter( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		if ( $post->_company_twitter && ! strstr( $post->_company_twitter, 'http:' ) && ! strstr( $post->_company_twitter, 'https:' ) ) {
			$post->_company_twitter = 'https://' . $post->_company_twitter;
		}

		return apply_filters( 'cariera_company_twitter_output', $post->_company_twitter, $post );
	}
}

if ( ! function_exists( 'cariera_company_twitter_output' ) ) {
	function cariera_company_twitter_output( $post = null ) {
		if ( ! empty( cariera_get_the_company_twitter( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_company_twitter( $post ) ) . '" class="company-twitter-x" target="_blank"><svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg></a>';
		}
	}
}

// Linkedin.
if ( ! function_exists( 'cariera_get_the_company_linkedin' ) ) {
	function cariera_get_the_company_linkedin( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		if ( $post->_company_linkedin && ! strstr( $post->_company_linkedin, 'http:' ) && ! strstr( $post->_company_linkedin, 'https:' ) ) {
			$post->_company_linkedin = 'https://' . $post->_company_linkedin;
		}

		return apply_filters( 'cariera_company_linkedin_output', $post->_company_linkedin, $post );
	}
}

if ( ! function_exists( 'cariera_company_linkedin_output' ) ) {
	function cariera_company_linkedin_output( $post = null ) {
		if ( ! empty( cariera_get_the_company_linkedin( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_company_linkedin( $post ) ) . '" class="company-linkedin" target="_blank"><i class="fab fa-linkedin-in"></i></a>';
		}
	}
}

// Instagram.
if ( ! function_exists( 'cariera_get_the_company_instagram' ) ) {
	function cariera_get_the_company_instagram( $post = null ) {
		$post = get_post( $post );
		if ( $post->post_type !== 'company' ) {
			return;
		}

		if ( $post->_company_instagram && ! strstr( $post->_company_instagram, 'http:' ) && ! strstr( $post->_company_instagram, 'https:' ) ) {
			$post->_company_instagram = 'https://' . $post->_company_instagram;
		}

		return apply_filters( 'cariera_company_instagram_output', $post->_company_instagram, $post );
	}
}

if ( ! function_exists( 'cariera_company_instagram_output' ) ) {
	function cariera_company_instagram_output( $post = null ) {
		if ( ! empty( cariera_get_the_company_instagram( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_company_instagram( $post ) ) . '" class="company-instagram" target="_blank"><i class="fab fa-instagram"></i></a>';
		}
	}
}

/**
 * Company Video
 *
 * @since  1.3.0
 */
if ( ! function_exists( 'cariera_get_the_company_video' ) ) {
	function cariera_get_the_company_video( $post = null ) {
		$post = get_post( $post );
		if ( ! $post || 'company' !== $post->post_type ) {
			return null;
		}
		return apply_filters( 'cariera_the_company_video', $post->_company_video, $post );
	}
}

// Output.
if ( ! function_exists( 'cariera_the_company_video_output' ) ) {
	function cariera_the_company_video_output( $post = null ) {
		$video_embed = false;
		$video       = cariera_get_the_company_video( $post );
		$filetype    = wp_check_filetype( $video );

		if ( ! empty( $video ) ) {
			// FV WordPress Flowplayer Support for advanced video formats.
			if ( shortcode_exists( 'flowplayer' ) ) {
				$video_embed = '[flowplayer src="' . esc_url( $video ) . '"]';
			} elseif ( ! empty( $filetype['ext'] ) ) {
				$video_embed = wp_video_shortcode( [ 'src' => $video ] );
			} else {
				$video_embed = wp_oembed_get( $video );
			}
		}

		$video_embed = apply_filters( 'the_company_video_embed', $video_embed, $post );

		if ( $video_embed ) { ?>
			<div id="company-video" class="company-video">
				<h5><?php esc_html_e( 'Company Video', 'cariera' ); ?></h5>
				<?php echo $video_embed; ?>
			</div>
			<?php
		}
	}
}

/*
==================================================================================
	COMPANY SELECTION FUNCTIONS
==================================================================================
*/

/**
 * Get Companies based on the User ID
 *
 * @since   1.4.0
 * @version 1.5.5
 */
function cariera_get_user_companies( $args = [], $author_companies = false ) {
	$defaults = [
		'post_type'           => 'company',
		'post_status'         => [ 'publish', 'expired', 'pending', 'hidden' ],
		'ignore_sticky_posts' => 1,
		'posts_per_page'      => - 1,
		'orderby'             => 'date',
		'order'               => 'desc',
	];

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'cariera_company_manager_company_select_get_companies_args_before', $args, $author_companies );

	if ( $author_companies && ! current_user_can( 'administrator' ) ) {
		$args['author'] = get_current_user_id();

		if ( ! isset( $args['author'] ) || empty( $args['author'] ) ) {
			return [];
		}
	}

	$args = apply_filters( 'cariera_company_manager_company_select_get_companies_args', $args, $author_companies );

	$companies      = new WP_Query();
	$user_companies = $companies->query( $args );

	return apply_filters( 'cariera_company_manager_get_current_user_companies', $user_companies, $args );
}

/**
 * Gets post statuses used for companies.
 *
 * @since 1.0.0
 */
if ( ! function_exists( 'cariera_get_company_post_statuses' ) ) {
	function cariera_get_company_post_statuses() {
		return apply_filters(
			'cariera_company_post_statuses',
			[
				'draft'   => _x( 'Draft', 'post status', 'cariera' ),
				// 'expired'         => _x( 'Expired', 'post status', 'cariera' ),
				// 'hidden'          => _x( 'Hidden', 'post status', 'cariera' ),
				'preview' => _x( 'Preview', 'post status', 'cariera' ),
				'pending' => _x( 'Pending approval', 'post status', 'cariera' ),
				// 'pending_payment' => _x( 'Pending payment', 'post status', 'cariera' ),
				'publish' => _x( 'Active', 'post status', 'cariera' ),
			]
		);
	}
}

/**
 * Shows links after filtering companies
 *
 * @since 1.5.6
 */
if ( ! function_exists( 'cariera_get_companies_filtered_links' ) ) {
	function cariera_get_companies_filtered_links( $args = [] ) {

		$links = apply_filters(
			'cariera_company_filters_showing_companies_links',
			[
				'reset' => [
					'name' => esc_html__( 'Reset', 'cariera' ),
					'url'  => '#',
				],
			],
			$args
		);

		$return = '';

		foreach ( $links as $key => $link ) {
			$return .= '<a href="' . esc_url( $link['url'] ) . '" class="' . esc_attr( $key ) . '">' . $link['name'] . '</a>';
		}

		return $return;
	}
}

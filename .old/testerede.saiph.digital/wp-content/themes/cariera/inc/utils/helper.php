<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get post terms from the given taxonomy.
 *
 * @param int    $post_id
 * @param string $taxonomy
 *
 * @since 1.7.0
 */
function get_terms( $post_id, $taxonomy = 'category' ) {
	$raw_terms = (array) wp_get_post_terms( $post_id, $taxonomy );

	$terms = [];
	if ( ! empty( $raw_terms['errors'] ) ) {
		return $terms;
	}

	foreach ( $raw_terms as $raw_term ) {
		$terms[] = [
			'name' => $raw_term->name,
			'link' => get_term_link( $raw_term ),
		];
	}

	return $terms;
}

/**
 * Searches for term parents' IDs of hierarchical taxonomies, including current term.
 * This function is similar to the WordPress function get_category_parents() but handles any type of taxonomy.
 *
 * @since  1.2.7
 */
function get_term_parents( $term_id = '', $taxonomy = 'category' ) {
	// Set up some default arrays.
	$list = [];

	// If no term ID or taxonomy is given, return an empty array.
	if ( empty( $term_id ) || empty( $taxonomy ) ) {
		return $list;
	}

	do {
		$list[] = $term_id;

		// Get next parent term.
		$term    = get_term( $term_id, $taxonomy );
		$term_id = $term->parent;
	} while ( $term_id );

	// Reverse the array to put them in the proper order for the trail.
	$list = array_reverse( $list );
	array_pop( $list );

	return $list;
}

/**
 * Gets parent posts' IDs of any post type, include current post
 *
 * @since  1.2.7
 */
function get_post_parents( $post_id = '' ) {
	// Set up some default array.
	$list = [];

	// If no post ID is given, return an empty array.
	if ( empty( $post_id ) ) {
		return $list;
	}

	do {
		$list[] = $post_id;

		// Get next parent post.
		$post    = get_post( $post_id );
		$post_id = $post->post_parent;
	} while ( $post_id );

	// Reverse the array to put them in the proper order for the trail.
	$list = array_reverse( $list );
	array_pop( $list );

	return $list;
}

/**
 * Get page by title
 *
 * @param string $title
 * @param string $post_type
 *
 * @since   1.6.6
 */
function get_page_by_title( $title, $post_type = 'page' ) {
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

/**
 * Fetching the pages titles.
 *
 * @since 1.3.3
 */
function get_the_title() {

	// Blog Page.
	if ( is_home() ) {
		$blog_title = cariera_get_option( 'cariera_blog_title' );

		return $blog_title;
	}

	// WooCommerce Page.
	if ( \Cariera\wc_is_activated() && is_woocommerce() ) {
		if ( is_single() && ! is_attachment() ) {
			echo \get_the_title();
		} elseif ( ! is_single() ) {
			woocommerce_page_title();
		}

		return;
	}

	// 404 Page.
	if ( is_404() ) {
		return esc_html__( 'Error 404', 'cariera' );
	}

	// Homepage and Single Page.
	if ( is_home() || is_single() || is_404() ) {
		return \get_the_title();
	}

	// Search Page.
	if ( is_search() ) {
		return sprintf( esc_html__( 'Search Results for: %s', 'cariera' ), '<span>' . get_search_query() . '</span>' );
	}

	// Archive Pages.
	if ( is_archive() ) {
		if ( is_author() ) {
			return sprintf( esc_html__( 'All posts by %s', 'cariera' ), get_the_author() );
		} elseif ( is_day() ) {
			return sprintf( esc_html__( 'Day: %s', 'cariera' ), get_the_date() );
		} elseif ( is_month() ) {
			return sprintf( esc_html__( 'Month: %s', 'cariera' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'cariera' ) ) );
		} elseif ( is_year() ) {
			return sprintf( esc_html__( 'Year: %s', 'cariera' ), get_the_date( _x( 'Y', 'yearly archives date format', 'cariera' ) ) );
		} elseif ( is_tag() ) {
			return sprintf( esc_html__( 'Tag: %s', 'cariera' ), single_tag_title( '', false ) );
		} elseif ( is_category() ) {
			return sprintf( esc_html__( 'Category: %s', 'cariera' ), single_cat_title( '', false ) );
		} elseif ( is_tax( 'post_format', 'post-format-aside' ) ) {
			return esc_html__( 'Asides', 'cariera' );
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			return esc_html__( 'Videos', 'cariera' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			return esc_html__( 'Audio', 'cariera' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			return esc_html__( 'Quotes', 'cariera' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			return esc_html__( 'Galleries', 'cariera' );
		} else {
			return esc_html__( 'Archives', 'cariera' );
		}
	}

	return \get_the_title();
}

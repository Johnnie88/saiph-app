<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main menu fallback function when no menu exists
 *
 * @since  1.0.0
 */
function menu_fallback() {
	if ( current_user_can( 'administrator' ) ) {
		echo( '
        <ul id="menu-main-menu" class="main-menu main-nav">
        <li class="menu-item"><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Add a menu', 'cariera' ) . '</a></li>
        </ul>' );
	} else {
		echo( '
        <ul id="menu-main-menu" class="main-menu main-nav">
        <li class="menu-item"></li>
        </ul>' );
	}
}

/**
 *  Display navigation to next/previous set of posts when applicable.
 *
 * @since  1.0.0
 * @version 1.7.0
 */
function get_post_navigation() {
	get_template_part( 'templates/extra/post-nav' );
}

/**
 *  Display comment navigation.
 *
 * @since  1.0.0
 * @version 1.7.0
 */
function get_comment_navigation() {
	if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) {
		get_template_part( 'templates/comments/comment-nav' );
	}
}

/**
 * Comment Callback Function.
 *
 * @since 1.0.0
 */
function comments( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback':
		case 'trackback':
			?>


	<li class="post pingback">
		<p><?php esc_html_e( 'Pingback:', 'cariera' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( esc_html__( '(Edit)', 'cariera' ), ' ' ); ?></p>
			<?php
			break;
		default:
			$allowed_tags = wp_kses_allowed_html( 'post' );
			?>

	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>" class="comment clearfix">
			<div class="commenter">
				<?php echo get_avatar( $comment, 70 ); ?>

				<div class="commenter-details">
					<?php printf( '<h6 class="commenter-name">%s</h6>', get_comment_author_link() ); ?>
					<span class="date"> <?php printf( esc_html__( '%1$s at %2$s', 'cariera' ), get_comment_date(), get_comment_time() ); ?></span>
				</div>
			</div>

			<div class="comment-content">
				<div class="arrow-comment"></div>
				<?php comment_text(); ?>

				<?php
				$myclass = 'btn btn-small btn-main btn-effect';
				echo preg_replace(
					'/comment-reply-link/',
					'comment-reply-link ' . $myclass,
					get_comment_reply_link(
						array_merge(
							$args,
							[
								'reply_text' => wp_kses( __( 'Reply', 'cariera' ), $allowed_tags ),
								'depth'      => $depth,
								'max_depth'  => $args['max_depth'],
							]
						)
					),
					1
				);
				?>
			</div>
		</div>
			<?php
			break;
	endswitch;
}

/**
 * Post Thumbnail
 *
 * @since   1.3.3
 * @version 1.5.4
 */
function post_thumbnail( $args = [] ) {
	global $post;

	$defaults = [
		'size'  => 'large',
		'class' => 'post-image',
	];

	$args        = wp_parse_args( $args, $defaults );
	$post_format = get_post_format();

	// Standard or Image Post.
	if ( false === $post_format || 'standard' === $post_format || 'image' === $post_format ) {
		if ( has_post_thumbnail() ) {
			?>
			<div class="blog-thumbnail mb40">
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
					<?php the_post_thumbnail(); ?>
				</a>
			</div>
			<?php
		}
	}

	// Gallery Post.
	if ( 'gallery' === $post_format ) {
		$images = get_post_meta( $post->ID, 'cariera_blog_gallery', true );

		if ( ! empty( $images ) ) {
			?>
			<div class="gallery-post-wrapper">
				<div class="gallery-post mb40">
					<?php
					foreach ( $images as $image ) {
						echo '<div class="item"><img src="' . esc_url( $image ) . '"/></div>';
					}
					?>
				</div>
			</div>
			<?php
		}
	}

	// Video Post.
	if ( 'video' === $post_format ) {
		$video = get_post_meta( $post->ID, 'cariera_blog_video_embed', true );
		if ( ! empty( $video ) ) {
			?>
			<div class="embed-responsive embed-responsive-16by9 mb40">
				<?php
				if ( wp_oembed_get( $video ) ) {
					echo wp_oembed_get( $video );
				} else {
					$allowed_tags = wp_kses_allowed_html( 'post' );
					echo wp_kses( $video, $allowed_tags );
				}
				?>
			</div>
			<?php
		}
	}

	// Audio Post.
	if ( 'audio' === $post_format ) {
		$audio = get_post_meta( $post->ID, 'cariera_blog_audio', true );
		if ( ! empty( $audio ) ) {
			?>
			<div class="audio-wrapper mb40">
				<?php
				if ( wp_oembed_get( $audio ) ) {
					echo wp_oembed_get( $audio );
				} else {
					$allowed_tags = wp_kses_allowed_html( 'post' );
					echo wp_kses( $audio, $allowed_tags );
				}
				?>
			</div>
			<?php
		}
	}
}

/**
 * Single Post Thumbnail
 *
 * @since   1.3.3
 * @version 1.5.3
 */
function single_post_thumbnail() {
	global $post;

	$post_format = get_post_format();

	// Standard or Image Post.
	if ( false === $post_format || 'image' === $post_format ) {
		if ( has_post_thumbnail() ) {
			?>
			<div class="blog-thumbnail">
				<?php the_post_thumbnail(); ?>
			</div>
			<?php
		}
	}

	// Gallery Post.
	if ( 'gallery' === $post_format ) {
		$images = get_post_meta( $post->ID, 'cariera_blog_gallery', true );

		if ( ! empty( $images ) ) {
			?>
			<div class="gallery-post mb40">
				<?php
				foreach ( $images as $image ) {
					echo '<div class="item"><img src="' . esc_url( $image ) . '"/></div>';
				}
				?>
			</div>
			<?php
		}
	}

	// Quote Post.
	if ( 'quote' === $post_format ) {
		$quote_content = get_post_meta( $post->ID, 'cariera_blog_quote_content', true );
		$quote_author  = get_post_meta( $post->ID, 'cariera_blog_quote_author', true );
		$quote_source  = get_post_meta( $post->ID, 'cariera_blog_quote_source', true );
		$allowed_tags  = wp_kses_allowed_html( 'post' );

		if ( ! empty( $quote_content ) && ! empty( $quote_author ) ) {
			?>
			<figure class="post-quote mb40">
				<span class="icon"></span>
				<blockquote>
					<h4><?php echo esc_html( $quote_content ); ?></h4>

					<?php if ( ! empty( $quote_source ) ) { ?>
						<a href="<?php echo esc_url( $quote_source ); ?>">
					<?php } ?>
							<h6 class="pt20">
							<?php
							echo esc_html( '- ' );
							echo wp_kses( $quote_author, $allowed_tags );
							?>
							</h6>
					<?php if ( ! empty( $quote_source ) ) { ?>
						</a> 
					<?php } ?>
				</blockquote>
			</figure>
			<?php
		}
	}

	// Audio Post.
	if ( 'audio' === $post_format ) {
		$audio = get_post_meta( $post->ID, 'cariera_blog_audio', true );
		if ( ! empty( $audio ) ) {
			?>
			<div class="audio-wrapper mb40">
				<?php
				if ( wp_oembed_get( $audio ) ) {
					echo wp_oembed_get( $audio );
				} else {
					$allowed_tags = wp_kses_allowed_html( 'post' );
					echo wp_kses( $audio, $allowed_tags );
				}
				?>
			</div>
			<?php
		}
	}

	// Video Post.
	if ( 'video' === $post_format ) {
		$video_embed = get_post_meta( $post->ID, 'cariera_blog_video_embed', true );
		if ( ! empty( $video_embed ) ) {
			?>
			<div class="embed-responsive embed-responsive-16by9 mb40">
				<?php
				if ( wp_oembed_get( $video_embed ) ) {
					echo wp_oembed_get( $video_embed );
				} else {
					$allowed_tags = wp_kses_allowed_html( 'post' );
					echo wp_kses( $video_embed, $allowed_tags );
				}
				?>
			</div>

			<?php
		}
	}
}

/**
 * Navigation function for pagination.
 *
 * @since  1.0.0
 */
function pagination_nav() {
	$pagination = cariera_get_option( 'cariera_blog_pagination' );

	if ( 'numeric' === $pagination ) {
		\Cariera\numeric_pagination();
	} else {
		\Cariera\posts_navigation(
			[
				'prev_text' => ' ',
				'next_text' => ' ',
			]
		);
	}
}

/**
 * Numeric Pagination
 *
 * @since  1.2.7
 */
function numeric_pagination() {
	global $wp_query;

	if ( $wp_query->max_num_pages < 2 ) {
		return;
	}
	?>

	<div class="col-md-12 pagination">
		<?php
		$big  = 999999999;
		$args = [
			'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'total'     => $wp_query->max_num_pages,
			'current'   => max( 1, get_query_var( 'paged' ) ),
			'prev_text' => esc_html__( 'Previous', 'cariera' ),
			'next_text' => esc_html__( 'Next', 'cariera' ),
			'type'      => 'list',
		];

		echo paginate_links( $args );
		?>
	</div>
	<?php
}

/**
 * Plain Pagination. This will show next/previous set of posts.
 *
 * @since  1.0.0
 */
function posts_navigation() {
	require_once get_template_directory() . '/templates/extra/pagination.php';
}

/**
 * Display breadcrumbs for posts, pages, archive page with the microdata that search engines understand
 *
 * @since  1.2.7
 */
function breadcrumbs( $args = '' ) {

	if ( 0 === absint( cariera_get_option( 'cariera_breadcrumbs' ) ) ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		[
			'separator'         => '',
			'home_class'        => 'home',
			'before'            => '<ul class="breadcrumb">',
			'after'             => '</ul>',
			'before_item'       => '<li>',
			'after_item'        => '</li>',
			'taxonomy'          => 'category',
			'display_last_item' => true,
			'show_on_front'     => true,
			'labels'            => [
				'home'      => esc_html__( 'Home', 'cariera' ),
				'archive'   => esc_html__( 'Archives', 'cariera' ),
				'blog'      => esc_html__( 'Blog', 'cariera' ),
				'search'    => esc_html__( 'Search results for', 'cariera' ),
				'not_found' => esc_html__( 'Not Found', 'cariera' ),
				'author'    => esc_html__( 'Author:', 'cariera' ),
				'day'       => esc_html__( 'Daily:', 'cariera' ),
				'month'     => esc_html__( 'Monthly:', 'cariera' ),
				'year'      => esc_html__( 'Yearly:', 'cariera' ),
			],
		]
	);

	$args = apply_filters( 'cariera_breadcrumbs_args', $args );

	if ( is_front_page() && ! $args['show_on_front'] ) {
		return;
	}

	$items = [];

	// HTML template for each item.
	$item_tpl      = $args['before_item'] . '<span><a href="%s">%s</a></span>' . $args['after_item'];
	$item_text_tpl = $args['before_item'] . '<span>%s</span>' . $args['after_item'];

	// Home.
	if ( ! $args['home_class'] ) {
		$items[] = sprintf( $item_tpl, get_home_url(), $args['labels']['home'] );
	} else {
		$items[] = sprintf(
			'%s<span>
				<a class="%s" href="%s"><span>%s</span></a>
			</span>%s',
			$args['before_item'],
			$args['home_class'],
			apply_filters( 'cariera_breadcrumbs_home_url', get_home_url() ),
			$args['labels']['home'],
			$args['after_item']
		);
	}

	// Front page.
	if ( is_front_page() ) {
		$items   = [];
		$items[] = sprintf( $item_text_tpl, $args['labels']['home'] );
	} // Blog
	elseif ( is_home() && ! is_front_page() ) {
		$items[] = sprintf(
			$item_text_tpl,
			\get_the_title( get_option( 'page_for_posts' ) )
		);
	}

	// Single.
	elseif ( is_single() ) {
		// Terms.
		$taxonomy = $args['taxonomy'];

		$terms = \get_the_terms( get_the_ID(), $taxonomy );
		if ( $terms ) {
			$term    = end( $terms );
			$terms   = \Cariera\get_term_parents( $term->term_id, $taxonomy );
			$terms[] = $term->term_id;

			foreach ( $terms as $term_id ) {
				$term    = get_term( $term_id, $taxonomy );
				$items[] = sprintf( $item_tpl, \get_term_link( $term, $taxonomy ), $term->name );
			}
		}

		if ( $args['display_last_item'] ) {
			$items[] = sprintf( $item_text_tpl, \get_the_title() );
		}
	}

	// Page.
	elseif ( is_page() ) {
		if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
			$page_id = get_option( 'woocommerce_shop_page_id' );
			if ( $page_id ) {
				$items[] = sprintf( $item_tpl, esc_url( get_permalink( $page_id ) ), \get_the_title( $page_id ) );
			}
		} else {
			$pages = \Cariera\get_post_parents( get_queried_object_id() );
			foreach ( $pages as $page ) {
				$items[] = sprintf( $item_tpl, esc_url( get_permalink( $page ) ), \get_the_title( $page ) );
			}
		}

		if ( $args['display_last_item'] ) {
			$items[] = sprintf( $item_text_tpl, \get_the_title() );
		}
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$title = \get_the_title( get_option( 'woocommerce_shop_page_id' ) );
		if ( $args['display_last_item'] ) {
			$items[] = sprintf( $item_text_tpl, $title );
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$current_term = get_queried_object();
		$terms        = \Cariera\get_term_parents( get_queried_object_id(), $current_term->taxonomy );

		if ( $terms ) {
			foreach ( $terms as $term_id ) {
				$term    = get_term( $term_id, $current_term->taxonomy );
				$items[] = sprintf( $item_tpl, \get_term_link( $term, $current_term->taxonomy ), $term->name );
			}
		}

		if ( $args['display_last_item'] ) {
			$items[] = sprintf( $item_text_tpl, $current_term->name );
		}
	} // Search.
	elseif ( is_search() ) {
		$items[] = sprintf( $item_text_tpl, $args['labels']['search'] . ' &quot;' . \get_search_query() . '&quot;' );

	} // 404 Page.
	elseif ( is_404() ) {
		$items[] = sprintf( $item_text_tpl, $args['labels']['not_found'] );

	} // Author archive.
	elseif ( is_author() ) {
		// Queue the first post, that way we know what author we're dealing with (if that is the case).
		the_post();
		$items[] = sprintf(
			$item_text_tpl,
			$args['labels']['author'] . ' <span class="vcard"><a class="url fn n" href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . \get_the_author() . '</a></span>'
		);
		rewind_posts();

	} // Day archive.
	elseif ( is_day() ) {
		$items[] = sprintf(
			$item_text_tpl,
			sprintf( esc_html__( '%1$s %2$s', 'cariera' ), $args['labels']['day'], \get_the_date() )
		);

	} // Month archive.
	elseif ( is_month() ) {
		$items[] = sprintf(
			$item_text_tpl,
			sprintf( esc_html__( '%1$s %2$s', 'cariera' ), $args['labels']['month'], \get_the_date( 'F Y' ) )
		);

	} // Year archive.
	elseif ( is_year() ) {
		$items[] = sprintf(
			$item_text_tpl,
			sprintf( esc_html__( '%1$s %2$s', 'cariera' ), $args['labels']['year'], \get_the_date( 'Y' ) )
		);

	} // Archive.
	else {
		$items[] = sprintf(
			$item_text_tpl,
			$args['labels']['archive']
		);
	}

	return $args['before'] . implode( $args['separator'], $items ) . $args['after'];
}

/**
 * Get Currency Symbol
 *
 * @since 1.0.0
 */
function currency_symbol( $currency = '' ) {
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

	return apply_filters( 'cariera/currency_symbol', $currency_symbol, $currency );
}

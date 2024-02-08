<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Candidate Logos
 *
 * 'cariera_candidate_photo_size' filter for changing the size of the candidate photo
 *
 * @since   1.7.0
 * @version 1.7.0
 */
if ( ! function_exists( 'cariera_the_candidate_photo' ) ) {
	function cariera_the_candidate_photo( $args = [] ) {
		$defaults = apply_filters(
			'cariera_the_candidate_photo_args',
			[
				'size'    => 'thumbnail',
				'default' => null,
				'post'    => null,
			]
		);

		$args = wp_parse_args( $defaults, $args );
		$size = apply_filters( 'cariera_candidate_photo_size', $args['size'] );

		the_candidate_photo( $size, $args['default'], $args['post'] );
	}
}

/**
 * Candidate Socials
 *
 * @since  1.3.1
 */

// Facebook.
if ( ! function_exists( 'cariera_get_the_candidate_fb' ) ) {
	function cariera_get_the_candidate_fb( $post = null ) {
		$post = get_post( $post );
		if ( 'resume' !== $post->post_type ) {
			return;
		}

		if ( $post->_facebook && ! strstr( $post->_facebook, 'http:' ) && ! strstr( $post->_facebook, 'https:' ) ) {
			$post->_facebook = 'https://' . $post->_facebook;
		}

		return apply_filters( 'cariera_candidate_fb_output', $post->_facebook, $post );
	}
}

if ( ! function_exists( 'cariera_candidate_fb_output' ) ) {
	function cariera_candidate_fb_output( $post = null ) {
		if ( ! empty( cariera_get_the_candidate_fb( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_candidate_fb( $post ) ) . '" class="candidate-facebook"><i class="fab fa-facebook-f"></i></a>';
		}
	}
}

// Twitter.
if ( ! function_exists( 'cariera_get_the_candidate_twitter' ) ) {
	function cariera_get_the_candidate_twitter( $post = null ) {
		$post = get_post( $post );
		if ( 'resume' !== $post->post_type ) {
			return;
		}

		if ( $post->_twitter && ! strstr( $post->_twitter, 'http:' ) && ! strstr( $post->_twitter, 'https:' ) ) {
			$post->_twitter = 'https://' . $post->_twitter;
		}

		return apply_filters( 'cariera_candidate_twitter_output', $post->_twitter, $post );
	}
}

if ( ! function_exists( 'cariera_candidate_twitter_output' ) ) {
	function cariera_candidate_twitter_output( $post = null ) {
		if ( ! empty( cariera_get_the_candidate_twitter( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_candidate_twitter( $post ) ) . '" class="candidate-twitter-x"><svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg></a>';
		}
	}
}

// LinkedIn.
if ( ! function_exists( 'cariera_get_the_candidate_linkedin' ) ) {
	function cariera_get_the_candidate_linkedin( $post = null ) {
		$post = get_post( $post );
		if ( 'resume' !== $post->post_type ) {
			return;
		}

		if ( $post->_linkedin && ! strstr( $post->_linkedin, 'http:' ) && ! strstr( $post->_linkedin, 'https:' ) ) {
			$post->_linkedin = 'https://' . $post->_linkedin;
		}

		return apply_filters( 'cariera_candidate_linkedin_output', $post->_linkedin, $post );
	}
}

if ( ! function_exists( 'cariera_candidate_linkedin_output' ) ) {
	function cariera_candidate_linkedin_output( $post = null ) {
		if ( ! empty( cariera_get_the_candidate_linkedin( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_candidate_linkedin( $post ) ) . '" class="candidate-linkedin"><i class="fab fa-linkedin-in"></i></a>';
		}
	}
}

// Instagram.
if ( ! function_exists( 'cariera_get_the_candidate_instagram' ) ) {
	function cariera_get_the_candidate_instagram( $post = null ) {
		$post = get_post( $post );
		if ( 'resume' !== $post->post_type ) {
			return;
		}

		if ( $post->_instagram && ! strstr( $post->_instagram, 'http:' ) && ! strstr( $post->_instagram, 'https:' ) ) {
			$post->_instagram = 'https://' . $post->_instagram;
		}

		return apply_filters( 'cariera_candidate_instagram_output', $post->_instagram, $post );
	}
}

if ( ! function_exists( 'cariera_candidate_instagram_output' ) ) {
	function cariera_candidate_instagram_output( $post = null ) {
		if ( ! empty( cariera_get_the_candidate_instagram( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_candidate_instagram( $post ) ) . '" class="candidate-instagram"><i class="fab fa-instagram"></i></a>';
		}
	}
}

// Youtube.
if ( ! function_exists( 'cariera_get_the_candidate_youtube' ) ) {
	function cariera_get_the_candidate_youtube( $post = null ) {
		$post = get_post( $post );
		if ( 'resume' !== $post->post_type ) {
			return;
		}

		if ( $post->_youtube && ! strstr( $post->_youtube, 'http:' ) && ! strstr( $post->_youtube, 'https:' ) ) {
			$post->_youtube = 'https://' . $post->_youtube;
		}

		return apply_filters( 'cariera_candidate_youtube_output', $post->_youtube, $post );
	}
}

if ( ! function_exists( 'cariera_candidate_youtube_output' ) ) {
	function cariera_candidate_youtube_output( $post = null ) {
		if ( ! empty( cariera_get_the_candidate_youtube( $post ) ) ) {
			echo '<a href="' . esc_url( cariera_get_the_candidate_youtube( $post ) ) . '" class="candidate-youtube"><i class="fab fa-youtube"></i></a>';
		}
	}
}

/**
 * Output the resume's rate if there is any
 *
 * @since 1.4.1
 */
function cariera_resume_rate() {
	global $post;

	$currency_position = get_option( 'cariera_currency_position', 'before' );
	$rate              = get_post_meta( $post->ID, '_rate', true );

	if ( ! empty( $rate ) ) {
		// Currency Symbol Before.
		if ( 'before' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}
		echo esc_html( $rate );
		// Currency Symbol After.
		if ( 'after' === $currency_position ) {
			echo \Cariera\currency_symbol();
		}
		esc_html_e( '/hour', 'cariera' );
	}
}

/**
 * Outputting a resume listing in the map
 *
 * @since   1.5.5
 * @version 1.6.0
 */
function cariera_resume_map() {
	global $post;

	$resume_map = cariera_get_option( 'cariera_resume_map' );
	$lng        = $post->geolocation_long;
	$logo       = get_the_candidate_photo();

	if ( ! $resume_map || empty( $lng ) ) {
		return;
	}

	if ( ! empty( $logo ) ) {
		$logo_img = $logo;
	} else {
		$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
	}

	echo '<div id="resume-map" data-longitude="' . esc_attr( $post->geolocation_long ) . '" data-latitude="' . esc_attr( $post->geolocation_lat ) . '" data-thumbnail="' . esc_attr( $logo_img ) . '" data-id="listing-id-' . get_the_ID() . '"></div>';
}

/**
 * Resume Class
 *
 * @since  1.4.5
 */
if ( ! function_exists( 'cariera_resume_class' ) ) {
	function cariera_resume_class( $class = '', $post_id = null ) {
		echo 'class="' . esc_attr( join( ' ', cariera_get_resume_class( $class, $post_id ) ) ) . '"';
	}
}

// Get Company Class.
if ( ! function_exists( 'cariera_get_resume_class' ) ) {
	function cariera_get_resume_class( $class = '', $post_id = null ) {
		$post = get_post( $post_id );

		if ( empty( $post ) || 'resume' !== $post->post_type ) {
			return [];
		}

		$classes   = [];
		$classes[] = 'resume';

		if ( ! empty( $class ) ) {
			if ( ! is_array( $class ) ) {
				$class = preg_split( '#\s+#', $class );
			}
			$classes = array_merge( $classes, $class );
		}

		if ( is_resume_featured( $post ) ) {
			$classes[] = 'resume_featured';
		}

		return get_post_class( $classes, $post->ID );
	}
}

/**
 * Returning the single resume layout option
 *
 * @since   1.5.5
 * @version 1.5.5
 */
function cariera_single_resume_layout() {
	$layout = apply_filters( 'cariera_resume_manager_single_resume_layout', get_option( 'cariera_resume_manager_single_resume_layout' ) );

	if ( empty( $layout ) ) {
		$layout = 'v1';
	}

	return $layout;
}

<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Theme_Support {

	use \Cariera\Src\Traits\Singleton;

	/**
	 * Constructor function.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		add_filter( 'post_thumbnail_html', [ $this, 'vertical_featured_image' ], 10, 5 );
		add_filter( 'comment_form_defaults', [ $this, 'comment_form' ] );
		add_filter( 'wp_edit_nav_menu_walker', [ $this, 'navmenu_role_nmr' ], 999999 );
		add_filter( 'woocommerce_create_pages', [ $this, 'disable_wc_page_creation' ] );
		add_action( 'wp_logout', [ $this, 'logout_redirect' ] );
		add_action( 'wp_footer', [ $this, 'cookie_bar' ] );
		add_action( 'wp_footer', [ $this, 'login_register_modal' ] );
		add_action( 'wp_footer', [ $this, 'fullscreen_search' ] );
	}

	/**
	 *  Add support for Vertical Featured Images.
	 *
	 * @since  1.0.0
	 */
	public function vertical_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		$image_data = wp_get_attachment_image_src( $post_thumbnail_id, 'large' );

		// Get the image width and height from the data provided by wp_get_attachment_image_src().
		$width  = $image_data[1];
		$height = $image_data[2];

		if ( $height > $width ) {
			$html = str_replace( 'attachment-', 'vertical-image attachment-', $html );
		}
		return $html;
	}

	/**
	 * Comment Form.
	 *
	 * @since   1.0.0
	 * @version 1.5.1
	 */
	public function comment_form( $args ) {
		$commenter    = wp_get_current_commenter();
		$current_user = wp_get_current_user();
		$req          = get_option( 'require_name_email' );
		$aria_req     = ( $req ? " aria-required='true'" : '' );

		$comment_author       = esc_attr( $commenter['comment_author'] );
		$comment_author_email = esc_attr( $commenter['comment_author_email'] );
		$comment_author_url   = esc_attr( $commenter['comment_author_url'] );

		$name    = ( $comment_author ) ? '' : esc_html__( 'Name *', 'cariera' );
		$email   = ( $comment_author_email ) ? '' : esc_html__( 'Email *', 'cariera' );
		$website = ( $comment_author_url ) ? '' : esc_html__( 'Website', 'cariera' );

		$fields = [
			'author' => '<div class="col-md-6 form-group"><input id="author" name="author" type="text" class="form-control" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="' . esc_attr__( 'Your Name', 'cariera' ) . '" required="required" /></div>',
			'email'  => '<div class="col-md-6 form-group"><input id="email" name="email" class="form-control" type="text" value="' . esc_attr( $commenter['comment_author_email'] ) . '" placeholder="' . esc_attr__( 'Your Email', 'cariera' ) . '" required="required" /></div>',
		];

		$args = [
			'class_form'           => esc_attr( 'comment-form row' ),
			'title_reply'          => esc_html__( 'Leave a Comment', 'cariera' ),
			'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'cariera' ),
			'title_reply_before'   => '<h4 id="reply-title" class="comment-reply-title nomargin">',
			'title_reply_after'    => '</h4>',
			'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
			'submit_field'         => '<div class="col-md-12">%1$s %2$s</div>',
			'class_submit'         => esc_attr( 'btn btn-main btn-effect' ),
			'label_submit'         => esc_attr__( 'send comment', 'cariera' ),
			'comment_field'        => '<div class="col-md-12 form-group"><textarea id="comment" class="form-control" name="comment" rows="8" placeholder="' . esc_attr__( 'Type your comment...', 'cariera' ) . '" required="required"></textarea></div>',
			'comment_notes_before' => '<div class="col-md-12 mb10"><p class="mtb10"><em>' . esc_html__( 'Your email address will not be published.', 'cariera' ) . '</em></p></div>',
			'logged_in_as'         => '<div class="col-md-12"><p class="logged-in-as">' .
				sprintf(
					esc_html__( 'Logged in as ', 'cariera' ) . '<a href="%1$s">%2$s</a>. <a href="%3$s" title="' . esc_html__( 'Log out of this account', 'cariera' ) . '">' . esc_html__( 'Log out?', 'cariera' ) . '</a>',
					esc_url( admin_url( 'profile.php' ) ),
					$current_user->user_login,
					wp_logout_url( apply_filters( 'the_permalink', get_permalink() ) )
				) . '</p></div>',
			'cancel_reply_link'    => esc_html__( 'Cancel Reply', 'cariera' ),
			'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
		];

		return $args;
	}

	/**
	 * Nav Menu Role workaround.
	 *
	 * @since 1.2.3
	 */
	public function navmenu_role_nmr( $walker ) {
		if ( function_exists( 'Nav_Menu_Roles' ) ) {
			$walker = 'Walker_Nav_Menu_Edit_Roles';
		}

		return $walker;
	}

	/**
	 * Redirect on logout.
	 *
	 * @since 1.2.7
	 */
	public function logout_redirect() {
		wp_safe_redirect( home_url() );

		exit;
	}

	/**
	 * Cookie Law Info
	 *
	 * @since   1.3.0
	 * @version 1.7.0
	 */
	public function cookie_bar() {
		if ( false === cariera_get_option( 'cariera_cookie_notice' ) ) {
			return;
		}

		get_template_part( 'templates/extra/cookie-bar' );
	}

	/**
	 * Disable WooCommerce page creation on first activate
	 *
	 * @since 1.5.0
	 */
	public function disable_wc_page_creation() {
		$pages = [];

		return $pages;
	}

	/**
	 * Login Register modal
	 *
	 * @since   1.4.0
	 * @version 1.7.0
	 */
	public function login_register_modal() {
		$login_registration = get_option( 'cariera_login_register_layout' );

		if ( is_user_logged_in() || 'page' === $login_registration || is_page_template( 'templates/login-register.php' ) ) {
			return;
		}

		get_template_part( 'templates/popups/login-register' );
	}

	/**
	 * General Search
	 *
	 * @since   1.4.0
	 * @version 1.7.0
	 */
	public function fullscreen_search() {
		if ( false === cariera_get_option( 'header_quick_search' ) ) {
			return;
		}

		get_template_part( 'templates/popups/fullscreen-search' );
	}
}

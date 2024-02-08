<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sharing output function
 *
 * @since  1.4.2
 */
function cariera_share_media() {
	echo '<div class="social-sharer-wrapper mt20"><a href="#social-share-modal" class="btn btn-main popup-with-zoom-anim">' . esc_html__( 'share', 'cariera' ) . '<i class="icon-share"></i></a></div>';

	add_action( 'wp_footer', 'cariera_sharing_modal' );
}

/**
 * Sharing modal
 *
 * @since  1.4.2
 */
function cariera_sharing_modal() { ?>
	<!-- ===== Sharing Modal ===== -->
	<div id="social-share-modal" class="small-dialog zoom-anim-dialog mfp-hide">
		<div class="small-dialog-headline">
			<h3 class="title"><?php esc_html_e( 'Share', 'cariera' ); ?></h3>
		</div>

		<div class="small-dialog-content">
			<?php cariera_sharing_options(); ?>
		</div>
	</div>
	<?php
}

/**
 * Sharing options
 *
 * @since  1.4.2
 */
function cariera_sharing_options() {

	$title = get_the_title();
	$link  = get_permalink();

	$socials_html = '';

	// FACEBOOK.
	$socials_html .= sprintf(
		'<li class="share-facebook">
            <a href="https://www.facebook.com/sharer.php?u=%s&title=%s" target="_blank">
                <div class="social-btn facebook">
                    <i class="social-btn-icon fab fa-facebook-f"></i>
                </div>
                <h4 class="title">%s</h4>
            </a>
        </li>',
		urlencode( $link ),
		urlencode( $title ),
		esc_html__( 'Facebook', 'cariera' )
	);

	// TWITTER.
	$socials_html .= sprintf(
		'<li class="share-twitter-x">
            <a href="http://twitter.com/share?text=%s&url=%s" target="_blank">
                <div class="social-btn twitter-x">
					<svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>
                </div>
                <h4 class="title">%s</h4>
            </a>
        </li>',
		urlencode( $title ),
		urlencode( $link ),
		esc_html__( 'X', 'cariera' )
	);

	// LINKEDIN.
	$socials_html .= sprintf(
		'<li class="share-linkedin">
            <a href="http://www.linkedin.com/shareArticle?url=%s&title=%s" target="_blank">
                <div class="social-btn linkedin">
                    <i class="social-btn-icon fab fa-linkedin-in"></i>
                </div>
                <h4 class="title">%s</h4>
            </a>
        </li>',
		urlencode( $link ),
		urlencode( $title ),
		esc_html__( 'LinkedIn', 'cariera' )
	);

	// TELEGRAM.
	$socials_html .= sprintf(
		'<li class="share-telegram">
            <a href="https://telegram.me/share/url?url=%s&text=%s" target="_blank">
                <div class="social-btn telegram">
                    <i class="social-btn-icon fab fa-telegram"></i>
                </div>
                <h4 class="title">%s</h4>
            </a>
        </li>',
		urlencode( $link ),
		urlencode( $title ),
		esc_html__( 'Telegram', 'cariera' )
	);

	// TUMBLR.
	$socials_html .= sprintf(
		'<li class="share-tumblr">
            <a href="http://www.tumblr.com/share?v=3&u=%s&t=%s" target="_blank">
                <div class="social-btn tumblr">
                    <i class="social-btn-icon fab fa-tumblr"></i>
                </div>
                <h4 class="title">%s</h4>
            </a>
        </li>',
		urlencode( $link ),
		urlencode( $title ),
		esc_html__( 'Tumblr', 'cariera' )
	);

	// WHATSAPP.
	$socials_html .=
		'<li class="share-whatsapp">
            <a href="https://api.whatsapp.com/send?text=' . urlencode( $link ) . '" target="_blank">
                <div class="social-btn whatsapp">
                    <i class="social-btn-icon fab fa-whatsapp"></i>
                </div>
                <h4 class="title">' . esc_html__( 'WhatsApp', 'cariera' ) . '</h4>
            </a>
        </li>';

	// VKontakte.
	$socials_html .= sprintf(
		'<li class="share-vk">
            <a href="http://vk.com/share.php?url=%s&title=%s" target="_blank">
                <div class="social-btn vk">
                    <i class="social-btn-icon fab fa-vk"></i>
                </div>
                <h4 class="title">%s</h4>
            </a>
        </li>',
		urlencode( $link ),
		urlencode( $title ),
		esc_html__( 'VK', 'cariera' )
	);

	// Mail.
	$socials_html .=
		'<li class="share-mail">
            <a href="mailto:?subject=' . urlencode( $link ) . '&body=' . urlencode( $title ) . ' - ' . urlencode( $link ) . '">
                <div class="social-btn mail">
                    <i class="social-btn-icon far fa-envelope"></i>
                </div>
                <h4 class="title">' . esc_html__( 'Mail', 'cariera' ) . '</h4>
            </a>
        </li>';

	if ( $socials_html ) {
		printf( '<ul class="social-btns">%s</ul>', $socials_html );
	}

}

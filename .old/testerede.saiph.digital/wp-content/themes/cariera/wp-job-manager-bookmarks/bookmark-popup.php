<?php
/**
 * Custom: Bookmark Popup
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-bookmarks/bookmark-popup.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Bookmark Popup -->
<div id="bookmark-popup-<?php echo esc_attr( get_the_ID() ); ?>" class="small-dialog zoom-anim-dialog mfp-hide">
	<div class="bookmarks-popup">
		<div class="small-dialog-headline">
			<h3 class="title"><?php esc_html_e( 'Bookmark Details', 'cariera' ); ?></h3>
		</div>

		<div class="small-dialog-content text-left">
			<?php do_action( 'cariera_bookmark_popup_form' ); ?>            
		</div>
	</div>
</div>

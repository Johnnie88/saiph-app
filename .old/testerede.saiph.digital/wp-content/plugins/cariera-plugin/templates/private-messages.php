<?php
/**
 * Private messages template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/private-messages.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.6.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="private-messages" class="cariera-private-messages zoom-anim-dialog mfp-hide">

	<!-- Conversation List -->
	<div id="conversations" class="messages-wrapper">
		<div class="header-container">
			<div class="title-wrapper">
				<h3 class="title"><?php esc_html_e( 'Messages', 'cariera' ); ?></h3>
			</div>

			<div class="actions-right">
				<?php if ( get_option( 'cariera_private_messages_compose' ) ) { ?>
					<button id="compose-message" class="btn btn-main"><?php esc_html_e( 'Compose', 'cariera' ); ?></button>
				<?php } ?>
				<button class="action-btn" title="<?php esc_attr_e( 'Close (Esc)', 'cariera' ); ?>"><i class="fas fa-times"><span class="mfp-close"></span></i></button>
			</div>
		</div>

		<div class="body-container conversation-area">
			<div class="loader"><span></span></div>

			<div class="empty-inbox d-none">
				<h5><?php esc_html_e( 'Your inbox is empty!', 'cariera' ); ?></h5>
				<p><?php esc_html_e( 'To start a conversation use the compose button.', 'cariera' ); ?></p> 
			</div>

			<ul class="conversation-list cariera-scroll"></ul>
		</div>

		<div class="load-conversations">
			<button><?php esc_html_e( 'Load more conversations', 'cariera' ); ?></button>
		</div>
	</div>

	<?php if ( get_option( 'cariera_private_messages_compose' ) ) { ?>
		<!-- Find User - Compose Message -->
		<div id="find-user" class="messages-wrapper d-none">
			<div class="header-container">
				<div class="actions-left">
					<button class="action-btn back-to-conversations"><i class="fas fa-arrow-left"></i></button>
				</div>

				<div class="title-wrapper">
					<h3 class="title"><?php esc_html_e( 'Compose Message', 'cariera' ); ?></h3>
				</div>

				<div class="actions-right">
					<button class="action-btn" title="<?php esc_attr_e( 'Close (Esc)', 'cariera' ); ?>"><i class="fas fa-times"><span class="mfp-close"></span></i></button>
				</div>
			</div>

			<div class="body-container cariera-scroll">
				<div class="compose-user-search">
					<input type="text" class="" id="user-search" placeholder="<?php esc_attr_e( 'Search user...', 'cariera' ); ?>" autocomplete="off">

					<div class="search-results cariera-scroll">
						<div class="search-loader"><span></span></div>
						<ul></ul>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>

	<!-- Chat Window -->
	<div id="chat-window" class="messages-wrapper d-none">
		<div class="header-container">
			<div class="title-wrapper">
				<button class="action-btn back-to-conversations"><i class="fas fa-arrow-left"></i></button>

				<div class="user">
					<div class="avatar">
						<img src="http://0.gravatar.com/avatar/01513f64578731fedff1a5473fcd9256?s=26&d=mm&r=g" alt="">
					</div>
					<div class="info name">
						<h3 class="title"></h3>
					</div>
				</div>
			</div>

			<div class="actions-right">
				<a href="" id="listing-url" class="action-btn d-none" target="_blank"><i class="fas fa-link"></i></a>
				<button class="action-btn" id="block-user" ><i class="fas fa-ban"></i></button>
				<button class="action-btn d-none" id="unblock-user"><i class="fas fa-solid fa-lock-open"></i></button>
				<button class="action-btn" id="delete-conversation"><i class="far fa-trash-alt"></i></button>
				<button class="action-btn" title="<?php esc_attr_e( 'Close (Esc)', 'cariera' ); ?>"><i class="fas fa-times"><span class="mfp-close"></span></i></button>
			</div>
		</div>

		<div class="body-container chat-area">
			<div class="loader"><span></span></div>

			<!-- Block alert -->
			<div class="block-alert d-none">
				<h6></h6>
				<div class="action">
					<button class="yes"><i class="fas fa-check"></i><?php esc_html_e( 'Yes', 'cariera' ); ?></button>
					<button class="no"><i class="fas fa-times"></i><?php esc_html_e( 'No', 'cariera' ); ?></button>
				</div>
			</div>

			<!-- Delete Alert -->
			<div class="delete-alert d-none">
				<h6><?php esc_html_e( 'Are you sure you want to delete this conversation?', 'cariera' ); ?></h6>
				<div class="action">
					<button class="yes"><i class="fas fa-check"></i><?php esc_html_e( 'Yes', 'cariera' ); ?></button>
					<button class="no"><i class="fas fa-times"></i><?php esc_html_e( 'No', 'cariera' ); ?></button>
				</div>
			</div>

			<!-- Messages Loading state -->
			<div class="loading-messages" style="display: none;">
				<span><?php esc_html_e( 'Loading messages...', 'cariera' ); ?></span>
			</div>

			<!-- Chat history goes here -->
			<ul class="chat-list cariera-scroll"></ul>

			<!-- Warning popup -->
			<div class="warning-alert" style="display: none;">
				<p></p>
			</div>
		</div>

		<div class="chat-box">
			<div class="user-blocked d-none"></div>

			<div class="chat-field">
				<textarea cols="30" rows="1" maxlength="2000" placeholder="<?php esc_attr_e( 'Post a reply', 'cariera' ); ?>" class="write-msg"></textarea>
			</div>
			<div class="send-action">
				<button class="action-btn send-message"><i class="far fa-paper-plane"></i></button>
			</div>
		</div>
	</div>
</div>

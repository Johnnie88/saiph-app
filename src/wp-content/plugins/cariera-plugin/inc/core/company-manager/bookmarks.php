<?php

namespace Cariera_Core\Core\Company_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bookmarks {

	/**
	 * Constructor.
	 *
	 * @since   1.4.6
	 * @version 1.7.2
	 */
	public function __construct() {
		if ( ! class_exists( 'WP_Job_Manager_Bookmarks' ) ) {
			return;
		}

		global $job_manager_bookmarks;

		add_action( 'cariera_company_bookmarks', [ $this, 'bookmark_trigger' ], 10 );
		add_action( 'cariera_company_bookmarks', [ $this, 'bookmark_popup' ], 11 );
		add_action( 'cariera_bookmark_popup_form', [ $job_manager_bookmarks, 'bookmark_form' ] );
		add_action( 'wp', [ $this, 'bookmark_handler' ] );
	}

	/**
	 * Bookmark button trigger
	 *
	 * @since   1.4.6
	 * @version 1.6.4
	 */
	public function bookmark_trigger() {
		global $company_preview;

		if ( $company_preview ) {
			return;
		}

		if ( is_user_logged_in() ) {
			echo '<a href="#bookmark-popup-' . esc_attr( get_the_ID() ) . '" class="company-bookmark popup-with-zoom-anim"><i class="fas fa-heart"></i></a>';
		} else {
			$login_registration = get_option( 'cariera_login_register_layout' );

			if ( $login_registration === 'popup' ) {
				echo '<a href="#login-register-popup" class="company-bookmark popup-with-zoom-anim">';
			} else {
				$login_registration_page     = apply_filters( 'cariera_login_register_page', get_option( 'cariera_login_register_page' ) );
				$login_registration_page_url = get_permalink( $login_registration_page );

				echo '<a href="' . esc_url( $login_registration_page_url ) . '" class="company-bookmark">';
			}

			echo '<i class="fas fa-heart"></i>';

			echo '</a>';
		}
	}

	/**
	 * Bookmark Popup
	 *
	 * @since 1.7.0
	 */
	public function bookmark_popup() {
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

		<?php
	}

	/**
	 * See if a post is bookmarked by ID
	 *
	 * @param  int post ID
	 * @return boolean
	 */
	public function is_bookmarked( $post_id ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}job_manager_bookmarks WHERE post_id = %d AND user_id = %d;", $post_id, get_current_user_id() ) ) ? true : false;
	}

	public function bookmark_handler() {
		global $wpdb;

		if ( ! is_user_logged_in() ) {
			return;
		}

		$action_data = null;

		if ( ! empty( $_POST['submit_bookmark'] ) ) {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'update_bookmark' ) ) {
				$action_data = [
					'error_code' => 400,
					'error'      => esc_html__( 'Bad request', 'cariera' ),
				];
			} else {
				$post_id = absint( $_POST['bookmark_post_id'] );
				$note    = wp_kses_post( stripslashes( $_POST['bookmark_notes'] ) );

				if ( $post_id && in_array( get_post_type( $post_id ), [ 'company' ], true ) ) {
					if ( ! $this->is_bookmarked( $post_id ) ) {
						$wpdb->insert(
							"{$wpdb->prefix}job_manager_bookmarks",
							[
								'user_id'       => get_current_user_id(),
								'post_id'       => $post_id,
								'bookmark_note' => $note,
								'date_created'  => current_time( 'mysql' ),
							]
						);
					} else {
						$wpdb->update(
							"{$wpdb->prefix}job_manager_bookmarks",
							[
								'bookmark_note' => $note,
							],
							[
								'post_id' => $post_id,
								'user_id' => get_current_user_id(),
							]
						);
					}

					delete_transient( 'bookmark_count_' . $post_id );
					$action_data = [
						'success' => true,
						'note'    => $note,
					];
				}
			}
		}

		if ( ! empty( $_GET['remove_bookmark'] ) ) {
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'remove_bookmark' ) ) {
				$action_data = [
					'error_code' => 400,
					'error'      => esc_html__( 'Bad request', 'cariera' ),
				];
			} else {
				$post_id = absint( $_GET['remove_bookmark'] );

				$wpdb->delete(
					"{$wpdb->prefix}job_manager_bookmarks",
					[
						'post_id' => $post_id,
						'user_id' => get_current_user_id(),
					]
				);

				delete_transient( 'bookmark_count_' . $post_id );
				$action_data = [ 'success' => true ];
			}
		}

		if ( null === $action_data ) {
			return;
		}
		if ( ! empty( $_REQUEST['wpjm-ajax'] ) && ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}
		if ( wp_doing_ajax() ) {
			wp_send_json( $action_data, ! empty( $action_data['error_code'] ) ? $action_data['error_code'] : 200 );
		} else {
			wp_redirect( remove_query_arg( [ 'submit_bookmark', 'remove_bookmark', '_wpnonce', 'wpjm-ajax' ] ) );
		}
	}
}

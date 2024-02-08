<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Messages {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Only trigger email notifications after a set time has
	 * passed since the last message.
	 *
	 * @var integer delay in seconds
	 */
	protected $notification_send_delay = 15 * MINUTE_IN_SECONDS;

	/**
	 * Lazy load conversation limit
	 * NOTE: if you change this value , you have to also
	 * change the value in the message.js file (@var lazy_load_conversations_limit)
	 *
	 * TODO: maybe pass this value via the localization script so that there wont be any need to change the js file
	 */
	protected $lazy_load_conversations_limit = 5;

	/**
	 * Post types that can be messaged.
	 *
	 * @var array
	 */
	protected $post_types = [ 'job_listing', 'resume', 'company' ];

	/**
	 * Message Database Table Name
	 *
	 * @var string
	 */
	private $db_message_table = null;

	/**
	 * Conversation Database Table Name
	 *
	 * @var string
	 */
	private $db_conversation_table = null;

	/**
	 * Construct
	 *
	 * @since   1.6.0
	 * @version 1.6.1
	 */
	public function __construct() {

		// Do nothing if the message system is disabled.
		if ( ! get_option( 'cariera_private_messages' ) ) {
			return;
		}

		// Set Database table names.
		$this->db_message_table      = $GLOBALS['wpdb']->prefix . 'cariera_messages';
		$this->db_conversation_table = $GLOBALS['wpdb']->prefix . 'cariera_conversations';

		// Ajax functions.
		add_action( 'wp_ajax_cariera_get_conversations', [ $this, 'get_conversations' ] );
		add_action( 'wp_ajax_cariera_get_user_message', [ $this, 'get_messages' ] );
		add_action( 'wp_ajax_cariera_send_message', [ $this, 'send_message' ] );
		add_action( 'wp_ajax_cariera_delete_conversation', [ $this, 'delete_conversation' ] );
		add_action( 'wp_ajax_cariera_block_user', [ $this, 'block_user' ] );
		add_action( 'wp_ajax_cariera_unblock_user', [ $this, 'unblock_user' ] );
		add_action( 'wp_ajax_cariera_check_block_status', [ $this, 'check_user_block_status' ] );
		add_action( 'wp_ajax_cariera_user_search', [ $this, 'user_search' ] );

		// Send alert when message has been successfully sent.
		add_action( 'cariera_private_message_sent_successfully', [ $this, '_send_alert' ] );

		// Clear messages & conversations DB Table.
		add_action( 'wp_ajax_cariera_delete_all_messages', [ $this, 'delete_all_messages' ] );

		// On user account delete.
		add_action( 'delete_user', [ $this, 'user_data_account_delete' ] );

		// Enqueue assets & load templates.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_footer', [ $this, 'load_templates' ] );
	}

	/**
	 * Load Messages assets.
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function enqueue_scripts() {
		// Do not load script files if the user is not logged in.
		if ( ! is_user_logged_in() ) {
			return;
		}

		$suffix = is_rtl() ? '.rtl' : '';

		wp_enqueue_style( 'cariera-core-messages', CARIERA_URL . '/assets/dist/css/messages' . $suffix . '.css', [], CARIERA_CORE_VERSION );

		wp_enqueue_script( 'cariera-core-messages', CARIERA_URL . '/assets/dist/js/messages.js', [ 'jquery' ], CARIERA_CORE_VERSION, true );

		$args = [
			'ajax_url'          => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
			'login_user_id'     => get_current_user_id(),
			'avatar_url'        => get_avatar_url( get_current_user_id() ),
			'autoload_interval' => ! empty( get_option( 'cariera_private_messages_autoload_interval' ) ) ? absint( get_option( 'cariera_private_messages_autoload_interval' ) ) : '10000',
			'strings'           => [
				'loading'             => esc_html__( 'Loading...', 'cariera' ),
				'sending_time'        => esc_html__( 'a few seconds ago', 'cariera' ),
				'load_conversations'  => esc_html__( 'Load more conversations', 'cariera' ),
				'delete_conversation' => esc_html__( 'Are you sure want to delete this conversation?', 'cariera' ),
				'yes'                 => esc_html__( 'Yes', 'cariera' ),
				'no'                  => esc_html__( 'No', 'cariera' ),
				'please_wait'         => esc_html__( 'Please wait...', 'cariera' ),
				'block_msg'           => esc_html__( 'Are you sure you want to block this user?', 'cariera' ),
				'unblock_msg'         => esc_html__( 'Are you sure you want to unblock this user?', 'cariera' ),
				'user_block_msg'      => esc_html__( 'This user has been blocked!', 'cariera' ),
				'user_blocked_msg'    => esc_html__( 'You have been blocked and can not message this user anymore!', 'cariera' ),
				'user_not_found'      => esc_html__( 'No users found!', 'cariera' ),
			],
		];

		wp_localize_script( 'cariera-core-messages', 'cariera_messages', $args );
	}

	/**
	 * Load Messages template.
	 *
	 * @since   1.6.0
	 * @version 1.7.2
	 */
	public function load_templates() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		cariera_get_template_part( 'private-messages' );
	}

	/**
	 * Get Conversations
	 *
	 * @since   1.6.0
	 * @version 1.6.1
	 */
	public function get_conversations() {
		if ( ! $_POST['user_id'] ) {
			return;
		}

		global $wpdb;

		$user_id         = isset( $_POST['user_id'] ) ? sanitize_text_field( $_POST['user_id'] ) : '';
		$lazy_load_limit = isset( $_POST['lazy_load_limit'] ) ? sanitize_text_field( $_POST['lazy_load_limit'] ) : '';

		// Friend list with lazy load limit.
		if ( isset( $_POST['lazy_load_limit'] ) ) {
			$friend_list = $wpdb->get_results( 'SELECT * FROM ' . $this->db_conversation_table . ' WHERE (user_id = ' . $user_id . ' AND status !=' . $user_id . ') OR (friend_id = ' . $user_id . ' AND status !=' . $user_id . ') ORDER BY created_at DESC LIMIT ' . $lazy_load_limit . ', ' . $this->lazy_load_conversations_limit . '' );
		} else {
			$friend_list = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT * FROM {$this->db_conversation_table}
					WHERE user_id = %s OR friend_id = %s
					",
					$user_id,
					$user_id
				)
			);
		}

		$newarr = [];
		foreach ( $friend_list as $friend ) {
			if ( $friend->user_id == $user_id ) {
				$users = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT id,display_name FROM {$wpdb->prefix}users
						WHERE ID = %s
						",
						$friend->friend_id
					)
				);
			} else {
				$users = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT id,display_name FROM {$wpdb->prefix}users
						WHERE ID = %s
						",
						$friend->user_id
					)
				);
			}

			foreach ( $users as $user ) {
				// Get the last message from cariera_messages of friend_id.
				if ( $friend->listing_id ) {
					$last_message = $wpdb->get_results(
						$wpdb->prepare(
							"
							SELECT * FROM {$this->db_message_table}
							WHERE (from_id = %s AND to_id = %s AND listing_id = %s)
							OR (from_id = %s AND to_id = %s AND listing_id = %s) 
							ORDER BY id DESC LIMIT 1
							",
							$user->id,
							$user_id,
							$friend->listing_id,
							$user_id,
							$user->id,
							$friend->listing_id
						)
					);
				} else {
					$last_message = $wpdb->get_results(
						$wpdb->prepare(
							"
							SELECT * FROM {$this->db_message_table}
							WHERE (from_id = %s AND to_id = %s AND listing_id = 0)
							OR (from_id = %s AND to_id = %s AND listing_id = 0) 
							ORDER BY id DESC LIMIT 1
							",
							$user->id,
							$user_id,
							$user_id,
							$user->id
						)
					);
				}

				$listing_name   = absint( $friend->listing_id ) !== 0 ? get_post( $friend->listing_id )->post_title : '';
				$listing_url    = absint( $friend->listing_id ) !== 0 ? get_permalink( $friend->listing_id ) : '';
				$listing_avatar = absint( $friend->listing_id ) !== 0 ? $this->listing_avatar( $friend->listing_id ) : '';

				foreach ( $last_message as $last ) {
					$newarr[] = [
						'message_id'         => $last->id,
						'message'            => $last->message,
						'from_id'            => $last->from_id,
						'user_id'            => $user->id,
						'display_name'       => $user->display_name,
						'seen'               => $last->seen,
						'created_at'         => human_time_diff( strtotime( $last->created_at ), current_time( 'timestamp' ) ) . esc_html__( ' ago', 'cariera' ),
						'avatar_url'         => get_avatar_url( $user->id ),
						'listing_id'         => $friend->listing_id,
						'listing_name'       => $listing_name,
						'listing_url'        => $listing_url,
						'listing_avatar_url' => $listing_avatar,
						'sender_id'          => $friend->user_id,
						'last_message_time'  => $last->created_at,
					];

				}
			}
		}

		return wp_send_json( $newarr );
	}

	/**
	 * Get Messages
	 *
	 * @since   1.6.0
	 * @version 1.6.1
	 */
	public function get_messages() {
		if ( ! isset( $_POST['from'] ) && ! isset( $_POST['to'] ) ) {
			return;
		}

		global $wpdb;

		$form       = sanitize_text_field( $_POST['from'] );
		$to         = sanitize_text_field( $_POST['to'] );
		$listing_id = sanitize_text_field( $_POST['listing_id'] );

		// Make all previous messages seen if user click on chat.
		if ( $listing_id != 0 ) {
			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE {$this->db_message_table}
					SET seen = 1 WHERE to_id = %s AND from_id = %s AND listing_id = %s
					",
					$form,
					$to,
					$listing_id
				)
			);
		} else {
			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE {$this->db_message_table} 
					SET seen = 1 WHERE to_id = %s AND from_id = %s AND listing_id = 0
					",
					$form,
					$to
				)
			);
		}

		if ( isset( $_POST['lazy_load_range'] ) ) {
			// Do lazy load.
			$range = sanitize_text_field( $_POST['lazy_load_range'] );

			if ( isset( $_POST['listing_id'] ) && $_POST['listing_id'] != 0 ) {

				$query = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT * FROM {$this->db_message_table} 
						WHERE (from_id = %d AND to_id = %d AND listing_id = %d) OR (from_id = %d AND to_id = %d AND listing_id = %d) 
						ORDER BY id DESC LIMIT %d, 10
						",
						$form,
						$to,
						$listing_id,
						$to,
						$form,
						$listing_id,
						$range
					)
				);

				// Change all created_at to human_time_diff.
				foreach ( $query as $key => $value ) {
					$query[ $key ]->created_at = human_time_diff( strtotime( $value->created_at ), current_time( 'timestamp' ) ) . esc_html__( ' ago', 'cariera' );

					if ( $value->from_id == get_current_user_id() ) {
						$query[ $key ]->avatar_url = get_avatar_url( $form );
					} else {
						$query[ $key ]->avatar_url = get_avatar_url( $to );
					}
				}
			} else {
				$query = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT * FROM {$this->db_message_table} 
						WHERE (from_id = %d AND to_id = %d AND listing_id = 0) OR (from_id = %d AND to_id = %d AND listing_id = 0) 
						ORDER BY id DESC LIMIT %d, 10
						",
						$form,
						$to,
						$to,
						$form,
						$range
					)
				);

				// Change all created_at to human_time_diff.
				foreach ( $query as $key => $value ) {
					$query[ $key ]->created_at = human_time_diff( strtotime( $value->created_at ), current_time( 'timestamp' ) ) . esc_html__( ' ago', 'cariera' );

					if ( $value->from_id == get_current_user_id() ) {
						$query[ $key ]->avatar_url = get_avatar_url( $form );
					} else {
						$query[ $key ]->avatar_url = get_avatar_url( $to );
					}
				}
			}
		} else {
			if ( isset( $_POST['listing_id'] ) && $_POST['listing_id'] != 0 ) {

					// Query with from id to_id and listing id.
					$query = $wpdb->get_results(
						$wpdb->prepare(
							"
							SELECT * FROM {$this->db_message_table} 
							WHERE (from_id = %d AND to_id = %d AND listing_id = %d) OR (from_id = %d AND to_id = %d AND listing_id = %d) 
							ORDER BY id DESC LIMIT 10
							",
							$form,
							$to,
							$listing_id,
							$to,
							$form,
							$listing_id
						)
					);

				// Change all created_at to human_time_diff.
				foreach ( $query as $key => $value ) {
					$query[ $key ]->created_at = human_time_diff( strtotime( $value->created_at ), current_time( 'timestamp' ) ) . esc_html__( ' ago', 'cariera' );

					// Add avatar_url key to array and value.
					$author_id = get_post_field( 'post_author', $listing_id );

					if ( $value->from_id == get_current_user_id() ) {
							$query[ $key ]->avatar_url = get_avatar_url( $form );
					} else {
							$query[ $key ]->avatar_url = get_avatar_url( $to );
					}
				}
			} else {

				$query = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT * FROM {$this->db_message_table} 
						WHERE (from_id = %d AND to_id = %d AND listing_id = 0) OR (from_id = %d AND to_id = %d AND listing_id = 0) 
						ORDER BY id DESC LIMIT 10
						",
						$form,
						$to,
						$to,
						$form
					)
				);

				// Change all created_at to human_time_diff.
				foreach ( $query as $key => $value ) {
					$query[ $key ]->created_at = human_time_diff( strtotime( $value->created_at ), current_time( 'timestamp' ) ) . esc_html__( ' ago', 'cariera' );

					// Add avatar_url key to array and value.
					if ( $value->from_id == get_current_user_id() ) {
							$query[ $key ]->avatar_url = get_avatar_url( $form );
					} else {
						$query[ $key ]->avatar_url = get_avatar_url( $to );
					}
				}
			}
		}

		return wp_send_json( $query );
	}

	/**
	 * Send Message function
	 *
	 * @since   1.6.0
	 * @version 1.6.1
	 */
	public function send_message() {
		if ( ! isset( $_POST['from'] ) && ! isset( $_POST['to'] ) && ! isset( $_POST['message'] ) && empty( $_POST['message'] ) ) {
			return;
		}

		global $wpdb;

		$from              = sanitize_text_field( $_POST['from'] );
		$to                = sanitize_text_field( $_POST['to'] );
		$listing_id        = sanitize_text_field( $_POST['listing_id'] );
		$message           = strip_tags( htmlspecialchars( $_POST['message'] ) );
		$message_unique_id = sanitize_text_field( $_POST['msg_uid'] );

		/*
		* we are checking if the user is already a
		* friend with the user that is trying to send a message
		* if not we are adding him to the friends list
		*/
		if ( isset( $_POST['listing_id'] ) && ! empty( $_POST['listing_id'] ) ) {
			// Check it's not author of the listing.
			$author_id = get_post_field( 'post_author', $listing_id );
			if ( $author_id == $from && $author_id == $to ) {
				return wp_send_json(
					[
						'status'  => 'error',
						'message' => esc_html__( 'You can not send a message to yourself!', 'cariera' ),
					]
				);
			}
			$check_friend = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT * FROM {$this->db_conversation_table} 
					WHERE (user_id = %d AND friend_id = %d AND listing_id = %d) OR (user_id = %d AND friend_id = %d AND listing_id = %d)
					",
					$from,
					$to,
					$listing_id,
					$to,
					$from,
					$listing_id
				)
			);

			if ( $check_friend == null ) {
				$wpdb->insert(
					$this->db_conversation_table,
					[
						'user_id'    => $from,
						'friend_id'  => $to,
						'listing_id' => $listing_id,
						'status'     => 0,
						'created_at' => current_time( 'mysql' ),
					]
				);
			}

			// Check user status.
			if ( empty( $check_chat_friend[0]->status ) || absint( $check_chat_friend[0]->status ) !== 0 ) {
				// Updating status to 0.
				$wpdb->query(
					$wpdb->prepare(
						"
						UPDATE {$this->db_conversation_table} 
						SET status = 0 
						WHERE (user_id = %d AND friend_id = %d AND listing_id = %d ) OR (user_id = %d AND friend_id = %d AND listing_id = %d)
						",
						$from,
						$to,
						$listing_id,
						$to,
						$from,
						$listing_id
					)
				);
			}
		} else {
			$check_chat_friend = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT * FROM {$this->db_conversation_table} 
					WHERE (user_id = %s AND friend_id = %s AND listing_id = 0) OR (user_id = %s AND friend_id = %s AND listing_id = 0)
					",
					$from,
					$to,
					$to,
					$from
				)
			);

			// Check user status.
			if ( empty( $check_chat_friend[0]->status ) || absint( $check_chat_friend[0]->status ) !== 0 ) {
				// Updating status to 0.
				$wpdb->query(
					$wpdb->prepare(
						"
						UPDATE {$this->db_conversation_table} 
						SET status = 0 
						WHERE (user_id = %d AND friend_id = %d AND listing_id = 0 ) OR (user_id = %d AND friend_id = %d AND listing_id = 0)
						",
						$from,
						$to,
						$to,
						$from
					)
				);
			}

			if ( count( $check_chat_friend ) == 0 ) {
				$wpdb->insert(
					$this->db_conversation_table,
					[
						'user_id'    => $from,
						'friend_id'  => $to,
						'created_at' => current_time( 'mysql' ),
					]
				);
			}
		}

		$wpdb->query(
			$wpdb->prepare(
				"
				UPDATE {$this->db_conversation_table} 
				SET created_at = %s 
				WHERE (user_id = %s AND friend_id = %s AND listing_id = %s) OR (user_id = %s AND friend_id = %s AND listing_id = %s)
				",
				current_time( 'mysql' ),
				$from,
				$to,
				$listing_id,
				$to,
				$from,
				$listing_id
			)
		);

		// Get user meta.
		$is_sender_block   = get_user_meta( $from, 'block_user', true );
		$is_receiver_block = get_user_meta( $to, 'block_user', true );
		$check             = false;

		// Block Cases.
		// Case #1 if the receiver is blocked by the sender then do not allow the messages.
		if ( ! empty( $is_sender_block ) ) {
			$block_user_ids = explode( ',', $is_sender_block );
			if ( in_array( $to, $block_user_ids, true ) ) {
				$check = true;
			}
		}
		// Case #2 Is sender is blocked in receiver list.
		// Do not display this message to receiver if the sender is blocked.
		if ( ! empty( $is_receiver_block ) ) {
			$block_user_ids = explode( ',', $is_receiver_block );
			if ( in_array( $from, $block_user_ids, true ) ) {
				$check = true;
			}
		}

		// Messages Data.
		$args = [
			'from_id'    => $from,
			'to_id'      => $to,
			'listing_id' => $listing_id,
			'message'    => $message,
			'created_at' => current_time( 'mysql' ),
		];

		// Insert message to the DB.
		if ( $check == false ) {
			$wpdb->insert(
				$this->db_message_table,
				$args
			);

			// Send email notification action.
			do_action( 'cariera_private_message_sent_successfully', $args );

			return wp_send_json(
				[
					'status'            => 'success',
					'message_unique_id' => $message_unique_id,
				]
			);
		}
	}

	/**
	 * Delete conversation ajax function
	 *
	 * @since   1.6.0
	 * @version 1.6.1
	 */
	public function delete_conversation() {
		if ( ! $_POST['delete_user_id'] && ! $_POST['user_id'] ) {
			return;
		}

		global $wpdb;

		$user_id        = sanitize_text_field( $_POST['user_id'] );
		$delete_user_id = sanitize_text_field( $_POST['delete_user_id'] );
		$listing_id     = sanitize_text_field( $_POST['listing_id'] );

		if ( empty( $listing_id ) ) {

			// Get data from database.
			$query = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT * FROM {$this->db_conversation_table}
					WHERE (user_id = %s AND friend_id = %s AND listing_id = 0 ) OR (user_id = %s AND friend_id = %s AND listing_id = 0)
					",
					$user_id,
					$delete_user_id,
					$delete_user_id,
					$user_id
				)
			);

			// Update message seen to 1 (for listings).
			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE {$this->db_message_table} 
					SET seen = 1 
					WHERE (from_id = %s AND to_id = %s AND listing_id = 0 ) OR (from_id = %s AND to_id = %s AND listing_id = 0)
					",
					$user_id,
					$delete_user_id,
					$delete_user_id,
					$user_id
				)
			);

			// Update status.
			if ( $query->status == 0 ) {
				$wpdb->query(
					$wpdb->prepare(
						"
						UPDATE {$this->db_conversation_table} 
						SET status = %s 
						WHERE (user_id = %s AND friend_id = %s AND listing_id = 0 ) OR (user_id = %s AND friend_id = %s AND listing_id = 0)
						",
						$user_id,
						$user_id,
						$delete_user_id,
						$delete_user_id,
						$user_id
					)
				);
			}

			if ( $query->status != 0 && $query->status != $user_id ) {
				$wpdb->query(
					$wpdb->prepare(
						"
						DELETE FROM {$this->db_conversation_table} 
						WHERE (user_id = %s AND friend_id = %s AND listing_id = 0 ) OR (user_id = %s AND friend_id = %s AND listing_id = 0)
						",
						$user_id,
						$delete_user_id,
						$delete_user_id,
						$user_id
					)
				);
			}

			// Delete all messages.
			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM {$this->db_message_table} 
					WHERE (from_id = %s AND to_id = %s AND listing_id = 0)
					",
					$user_id,
					$delete_user_id
				)
			);

		} else {

			// Get data from database.
			$query = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT * FROM {$this->db_conversation_table}
					WHERE (user_id = %s AND friend_id = %s AND listing_id = %s ) OR (user_id = %s AND friend_id = %s AND listing_id = %s)
					",
					$user_id,
					$delete_user_id,
					$listing_id,
					$delete_user_id,
					$user_id,
					$listing_id
				)
			);

			// Update message seen to 1 (for normal users).
			$wpdb->query(
				$wpdb->prepare(
					"
					UPDATE {$this->db_message_table} 
					SET seen = 1 
					WHERE (from_id = %s AND to_id = %s AND listing_id = %s ) OR (from_id = %s AND to_id = %s AND listing_id = %s)
					",
					$user_id,
					$delete_user_id,
					$listing_id,
					$delete_user_id,
					$user_id,
					$listing_id
				)
			);

			// Update status.
			if ( $query->status == 0 ) {
				$wpdb->query(
					$wpdb->prepare(
						"
						UPDATE {$this->db_conversation_table} 
						SET status = %s 
						WHERE (user_id = %s AND friend_id = %s AND listing_id = %s ) OR (user_id = %s AND friend_id = %s AND listing_id = %s)
						",
						$user_id,
						$user_id,
						$delete_user_id,
						$listing_id,
						$delete_user_id,
						$user_id,
						$listing_id
					)
				);
			}

			if ( $query->status != 0 && $query->status != $user_id ) {
				$wpdb->query(
					$wpdb->prepare(
						"
						DELETE FROM {$this->db_conversation_table} 
						WHERE (user_id = %s AND friend_id = %s AND listing_id = %s ) OR (user_id = %s AND friend_id = %s AND listing_id = %s)
						",
						$user_id,
						$delete_user_id,
						$listing_id,
						$delete_user_id,
						$user_id,
						$listing_id
					)
				);
			}

			// Delete all messages.
			$wpdb->query(
				$wpdb->prepare(
					"
					DELETE FROM {$this->db_message_table} 
					WHERE (from_id = %s AND to_id = %s AND listing_id = %s)
					",
					$user_id,
					$delete_user_id,
					$listing_id
				)
			);
		}

		return wp_send_json(
			[
				'status' => 'success',
			]
		);
	}

	/**
	 * Block user ajax function
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function block_user() {
		if ( ! isset( $_POST['block_user_id'] ) && ! isset( $_POST['user_id'] ) ) {
			return;
		}

		$user_id       = sanitize_text_field( $_POST['user_id'] );
		$block_user_id = sanitize_text_field( $_POST['block_user_id'] );

		// Check if user is already blocked.
		$check_user_meta = get_user_meta( $user_id, 'block_user', true );

		// If user is not blocked their userid will be added te the usermeta as blocked.
		if ( empty( $check_user_meta ) ) {
			update_user_meta( $user_id, 'block_user', $block_user_id );
			return true;
		} else {
			$block_user_ids = explode( ',', $check_user_meta );
			if ( ! in_array( $block_user_id, $block_user_ids, true ) ) {
				$block_user_ids[] = $block_user_id;
				update_user_meta( $user_id, 'block_user', implode( ',', $block_user_ids ) );
				return true;
			}
		}
	}

	/**
	 * Unblock user ajax function
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function unblock_user() {
		if ( ! isset( $_POST['unblock_user_id'] ) && ! isset( $_POST['user_id'] ) ) {
			return;
		}

		$user_id         = sanitize_text_field( $_POST['user_id'] );
		$unblock_user_id = sanitize_text_field( $_POST['unblock_user_id'] );

		// Check if user is already blocked.
		$check_user_meta = get_user_meta( $user_id, 'block_user', true );

		// If user is blocked they will be unblocked.
		if ( empty( $check_user_meta ) ) {
			return true;
		} else {
			$block_user_ids = explode( ',', $check_user_meta );

			if ( in_array( $unblock_user_id, $block_user_ids, true ) ) {
				$key = array_search( $unblock_user_id, $block_user_ids, true );
				unset( $block_user_ids[ $key ] );
				update_user_meta( $user_id, 'block_user', implode( ',', $block_user_ids ) );
				return true;
			}
		}
	}

	/**
	 * Check user block status ajax function
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function check_user_block_status() {
		if ( ! isset( $_POST['login_user'] ) && ! isset( $_POST['selected_user'] ) ) {
			return;
		}

		$login_user    = sanitize_text_field( $_POST['login_user'] );
		$selected_user = sanitize_text_field( $_POST['selected_user'] );

		// Check if user is blocked.
		$is_sender_block   = get_user_meta( $login_user, 'block_user', true );
		$is_receiver_block = get_user_meta( $selected_user, 'block_user', true );

		if ( ! empty( $is_sender_block ) ) {
			$block_user_ids = explode( ',', $is_sender_block );
			if ( in_array( $selected_user, $block_user_ids, true ) ) {
				return wp_send_json(
					[
						'status' => 'true',
						'id'     => $selected_user,
					]
				);
			}
		}

		if ( ! empty( $is_receiver_block ) ) {
			$block_user_ids = explode( ',', $is_receiver_block );
			if ( in_array( $login_user, $block_user_ids, true ) ) {
				return wp_send_json(
					[
						'status' => 'true',
						'id'     => $login_user,
					]
				);
			}
		}
	}

	/**
	 * Check users ajax function
	 *
	 * @since   1.6.0
	 * @version 1.6.0
	 */
	public function user_search() {
		if ( ! isset( $_POST['user_search'] ) ) {
			return;
		}

		global $wpdb;

		$search = sanitize_text_field( $_POST['user_search'] );

		$users = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT * FROM {$wpdb->prefix}users
					WHERE display_name LIKE '%{$search}%' AND ID != %s
				",
				get_current_user_id()
			)
		);

		if ( ! $users ) {
			return wp_send_json(
				[
					'status' => 'empty',
				]
			);
		}

		$return_list = [];

		foreach ( $users as $user ) {

			$wp_user = get_user_by( 'ID', $user->ID );

			$return_list[ $user->ID ] = [
				'id'     => $user->ID,
				'login'  => $wp_user->user_login,
				'avatar' => get_avatar_url( $user->ID ) ?: '',
			];
		}

		return wp_send_json( $return_list );
	}

	/**
	 * Delete all user related messages & conversations when user deletes account
	 *
	 * @since   1.6.0
	 * @version 1.6.1
	 */
	public function user_data_account_delete( $user_id ) {
		global $wpdb;

		// Delete all messages.
		$wpdb->query(
			$wpdb->prepare(
				"
					DELETE FROM {$this->db_message_table}
					WHERE from_id = %s OR to_id = %s
				",
				$user_id,
				$user_id
			)
		);

		// Delete all conversations.
		$wpdb->query(
			$wpdb->prepare(
				"
					DELETE FROM {$this->db_conversation_table}
					WHERE user_id = %s OR friend_id = %s
				",
				$user_id,
				$user_id
			)
		);
	}

	/**
	 * Delete all messages and conversations from the DB Table via AJAX
	 *
	 * @since   1.6.0
	 * @version 1.6.1
	 */
	public function delete_all_messages() {
		global $wpdb;

		$wpdb->query( "TRUNCATE TABLE {$this->db_message_table}" );
		$wpdb->query( "TRUNCATE TABLE {$this->db_conversation_table}" );

		wp_send_json( [ 'success' => true ] );
	}

	/**
	 * Generating the listings avatar url
	 *
	 * @since   1.6.0
	 * @version 1.7.0
	 */
	private function listing_avatar( $post_id = null ) {
		if ( $post_id == null ) {
			return;
		}

		$post_type = get_post_type( $post_id );

		if ( function_exists( 'get_the_candidate_photo' ) && 'resume' === $post_type ) {
			$logo = get_the_candidate_photo( $post_id, 'thumbnail' );
		} elseif ( function_exists( 'get_the_company_logo' ) && 'job_listing' === $post_type ) {
			if ( get_option( 'cariera_company_manager_integration', false ) ) {
				$company = cariera_get_the_company( $post_id );
				$logo    = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
			} else {
				$logo = get_the_company_logo( $post_id, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
			}
		} elseif ( 'company' === $post_type ) {
			$logo = get_the_company_logo( $post_id, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
		}

		if ( ! empty( $logo ) ) {
			$logo_img = $logo;
		} else {
			if ( 'job_listing' === $post_type || 'company' === $post_type ) {
				$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
			} elseif ( 'resume' === $post_type ) {
				$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
			}
		}

		return $logo_img;
	}

	/**
	 * Init action to use for email notification
	 *
	 * @since   1.6.0
	 * @version 1.7.0
	 */
	public function _send_alert( $args ) {
		global $wpdb;

		// Return if email notification has been disabled.
		if ( ! get_option( 'cariera_private_messages_email_notification' ) ) {
			return;
		}

		do_action( 'cariera_private_messages_email_notification', $args );
	}

}

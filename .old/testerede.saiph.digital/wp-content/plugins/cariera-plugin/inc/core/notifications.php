<?php

namespace Cariera_Core\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Notifications {

	use \Cariera_Core\Src\Traits\Singleton;

	/**
	 * Constructor
	 *
	 * @since   1.5.0
	 * @version 1.5.5
	 */
	public function __construct() {
		// Mark Notifications as read.
		add_action( 'wp_ajax_cariera_notification_marked_read', [ $this, 'mark_read' ] );

		// Delete Notifications.
		add_action( 'cariera_delete_notifications', [ $this, 'delete_notifications' ] );

		// Notifications.
		add_action( 'transition_post_status', [ $this, 'listing_post_status' ], 10, 3 );
		add_action( 'job_manager_applications_new_job_application', [ $this, 'application_notification' ], 10 );
		add_action( 'cariera_listing_promotion_started', [ $this, 'listing_promoted_notification' ], 10 );
		add_action( 'cariera_listing_promotion_ended', [ $this, 'promotion_expired_notification' ], 10 );

		// Webhook Test Trigger AJAX.
		add_action( 'wp_ajax_cariera_webhook_trigger', [ $this, 'webhook_test_trigger' ] );

		// Clear notification DB Table.
		add_action( 'wp_ajax_cariera_delete_all_notifications', [ $this, 'delete_all_notifications' ] );
	}

	/**
	 * Insert data to database
	 *
	 * @since 1.5.0
	 */
	public function insert_notification( $args ) {
		global $wpdb;

		// Get Current User.
		$user = get_user_by( 'id', get_current_user_id() );
		if ( $user ) {
			if ( empty( $args['user_id'] ) ) {
				$args['user_id'] = $user->ID;
			}
		} else {
			if ( empty( $args['user_id'] ) ) {
				$args['user_id'] = 0;
			}
		}

		// Duplication check.
		$exists = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}cariera_notifications
					WHERE action = %s
						AND owner_id    = %d
						AND user_id     = %d
						AND post_id     = %d
						AND active      = 1
				;",
				$args['action'],
				$args['owner_id'],
				$args['user_id'],
				$args['post_id']
			)
		);

		// Return if the insert already exists.
		if ( $exists ) {
			return;
		}

		// Insert into the database.
		$wpdb->insert(
			$wpdb->prefix . 'cariera_notifications',
			[
				'action'   => $args['action'],     // action name.
				'owner_id' => $args['owner_id'],   // The ID of the owner.
				'user_id'  => $args['user_id'],    // The ID of the user that did the action.
				'post_id'  => $args['post_id'],    // Post ID.
				'active'   => '1',
			]
		);
	}

	/**
	 * Active notifications that haven't been read
	 *
	 * @since 1.5.0
	 */
	public function active( $user_id = null ) {
		global $wpdb;

		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Check if user exists.
		if ( get_userdata( $user_id ) === false ) {
			return;
		}

		$results = $wpdb->get_var(
			"
            SELECT COUNT(*)
            FROM {$wpdb->prefix}cariera_notifications
            WHERE owner_id = {$user_id}
            AND active = 1
        "
		);

		return $results;
	}

	/**
	 * Latest notifications
	 *
	 * @since 1.5.0
	 */
	public function latest( $user_id = null, $num = 10, $active = false ) {
		global $wpdb;

		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Check if user exists.
		if ( get_userdata( $user_id ) === false ) {
			return [];
		}

		$active_sql = $active ? 'AND active = 1' : '';

		$sql = "
            SELECT *
            FROM {$wpdb->prefix}cariera_notifications
            WHERE owner_id = {$user_id}
            $active_sql
            ORDER BY created_at DESC
            LIMIT $num
        ";

		$results = $wpdb->get_results( $sql, OBJECT );

		return $results;
	}

	/**
	 * Mark active notifications as read
	 *
	 * @since 1.5.0
	 */
	public function mark_read() {
		global $wpdb;

		$user_id = get_current_user_id();

		// Check if user exists.
		if ( get_userdata( $user_id ) === false ) {
			return;
		}

		$wpdb->query(
			$wpdb->prepare(
				"
                    UPDATE {$wpdb->prefix}cariera_notifications 
                    SET active = 0 
                    WHERE owner_id = %d
                ",
				$user_id
			)
		);

		wp_send_json_success(
			[
				'success' => true,
			]
		);

		die();
	}

	/**
	 * Delete old notifications
	 *
	 * @since 1.5.0
	 */
	public function delete_notifications() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}cariera_notifications WHERE active = 0" );
	}

	/**
	 * Create the notifications output
	 *
	 * @since   1.5.0
	 * @version 1.5.1
	 */
	public function output() {

		$results = $this->latest();

		if ( ! $results ) {
			return;
		}

		ob_start();

		echo '<ul class="cariera-notifications">';
		foreach ( $results as $result ) {
			$post       = get_post( $result->post_id );
			$post_title = get_the_title( $result->post_id );
			$post_url   = get_permalink( $result->post_id );

			if ( ! empty( $result->user_id ) ) {
				$user = get_user_by( 'id', $result->user_id );
				// $user_name  = !empty( $user->user_firstname ) ? $user->user_firstname : $user->user_login;
			}

			$user_url = get_author_posts_url( $result->user_id );
			$active   = $result->active ? 'notification-active' : '';
			$time     = date_i18n( get_option( 'date_format' ), strtotime( $result->created_at ) );

			switch ( $result->action ) {

				// When a listing has been been created and is automatically approved.
				case 'listing_created':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="<?php echo esc_url( $post_url ); ?>">
							<div class="notification-icon">
								<i class="icon-layers"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Listing %s has been published.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When a listing has been submitted and it's pending for approval.
				case 'listing_pending':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="#">
							<div class="notification-icon">
								<i class="icon-layers"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Your listing %s is pending for approval.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When a listing has been submitted and it's pending for payment approval.
				case 'listing_pending_payment':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="<?php echo esc_url( $post_url ); ?>">
							<div class="notification-icon">
								<i class="icon-layers"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Your listing %s has been created, payment approval might be required.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When a listing get's approved.
				case 'listing_approved':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="<?php echo esc_url( $post_url ); ?>">
							<div class="notification-icon">
								<i class="icon-check"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Your listing %s has been approved.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When a listing get's deleted by admin.
				case 'listing_expired':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="#">
							<div class="notification-icon">
								<i class="icon-clock"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Your listing %s has expired.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When a listing get's relisted.
				case 'listing_relisted':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="<?php echo esc_url( $post_url ); ?>">
							<div class="notification-icon">
								<i class="icon-reload"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Your listing %s has been relisted.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When a listing get's deleted by admin.
				case 'listing_deleted':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="#">
							<div class="notification-icon">
								<i class="icon-trash"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Your listing %s has been deleted.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When someone applies to a job.
				case 'job_application':
					if ( ! $post ) {
						continue 2;
					}

					$job_id          = $post->post_parent;
					$job_title       = get_the_title( $job_id );
					$application_url = add_query_arg(
						[
							'action' => 'show_applications',
							'job_id' => $job_id,
						],
						get_permalink( get_option( 'job_manager_job_dashboard_page_id' ) )
					);
					?>

					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="<?php echo esc_url( $application_url ); ?>">
							<div class="notification-icon">
								<i class="icon-pencil"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( '%1$s applied to your job %2$s.', 'cariera' ), '<strong>' . $post_title . '</strong>', '<strong>' . $job_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When listing gets promoted.
				case 'listing_promoted':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="<?php echo esc_url( $post_url ); ?>">
							<div class="notification-icon">
								<i class="icon-energy"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Your listing %s has been promoted.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

				// When promotion expires.
				case 'promotion_expired':
					?>
					<li class="<?php echo esc_attr( $active ); ?>">
						<a href="<?php echo esc_url( $post_url ); ?>">
							<div class="notification-icon">
								<i class="icon-clock"></i>
							</div>
							<div class="notification-content">
								<span class="action"><?php printf( esc_html__( 'Your promotion for %s has expired.', 'cariera' ), '<strong>' . $post_title . '</strong>' ); ?></span>
								<span class="time"><?php echo esc_html( $time ); ?></span>
							</div>
						</a>
					</li>
					<?php
					break;

			}
		}
		echo '</ul>';

		return ob_get_clean();
	}

	/**
	 * Listing Statuses
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 */
	public function listing_post_status( $new_status, $old_status, $post ) {
		if ( ! get_option( 'cariera_notifications' ) ) {
			return;
		}

		if ( ! get_option( 'cariera_notification_listing_status' ) ) {
			return;
		}

		$post_types = [ 'job_listing', 'company', 'resume' ];

		// Return if the "post type" is not in array.
		if ( ! in_array( get_post_type( $post->ID ), $post_types ) ) {
			return;
		}

		$action = '';

		// Notification action based on the listing's post status.
		if ( 'preview' === $old_status && 'publish' == $new_status ) {
			$action = 'listing_created';
		} elseif ( 'preview' === $old_status && 'pending_payment' == $new_status ) {
			$action = 'listing_pending_payment';
		} elseif ( ( 'preview' === $old_status || 'pending_payment' === $old_status || 'expired' === $old_status ) && 'pending' == $new_status ) {
			$action = 'listing_pending';
		} elseif ( ( 'pending' === $old_status || 'pending_payment' === $old_status ) && 'publish' == $new_status ) {
			$action = 'listing_approved';
		} elseif ( 'trash' === $new_status ) {
			$action = 'listing_deleted';
		} elseif ( 'expired' === $new_status ) {
			$action = 'listing_expired';
		} elseif ( 'expired' === $old_status && 'publish' == $new_status ) {
			$action = 'listing_relisted';
		} else {
			$action = '';
		}

		if ( wp_is_post_revision( $post->ID ) ) {
			return;
		}

		if ( $action == '' ) {
			return;
		}

		$owner = get_post_field( 'post_author', $post->ID );

		// Insert the taken action into the database as a notification.
		$this->insert_notification(
			[
				'action'   => $action,
				'owner_id' => $owner,
				'user_id'  => '',
				'post_id'  => $post->ID,
			]
		);

		// Check if webhooks are enabled.
		if ( get_option( 'cariera_notification_listing_status_webhook' ) ) {
			$this->send_webhook( $action, $owner, $post->ID );
		}
	}

	/**
	 * Add Application Notification
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 */
	public function application_notification( $post_id ) {
		if ( ! get_option( 'cariera_notifications' ) ) {
			return;
		}

		if ( ! get_option( 'cariera_notification_application' ) ) {
			return;
		}

		$owner = get_post_field( 'post_author', $post_id );

		$this->insert_notification(
			[
				'action'   => 'job_application',
				'owner_id' => $owner,
				'user_id'  => get_current_user_id(),
				'post_id'  => $post_id,
			]
		);

		// Check if webhooks are enabled.
		if ( get_option( 'cariera_notification_application_webhook' ) ) {
			$this->send_webhook( 'job_application', $owner, $post_id );
		}
	}

	/**
	 * Add Promotion Notification
	 *
	 * @since   1.5.0
	 * @version 1.5.2
	 */
	public function listing_promoted_notification( $post_id ) {
		if ( ! get_option( 'cariera_notifications' ) ) {
			return;
		}

		if ( ! get_option( 'cariera_notification_listing_promotion' ) ) {
			return;
		}

		$this->insert_notification(
			[
				'action'   => 'listing_promoted',
				'owner_id' => get_post_field( 'post_author', $post_id ),
				'user_id'  => '',
				'post_id'  => $post_id,
			]
		);

		// $this->send_webhook( 'listing_promoted', $post_id );
	}

	/**
	 * Add Promotion Notification
	 *
	 * @since   1.5.0
	 * @version 1.6.0
	 */
	public function promotion_expired_notification( $post_id ) {
		if ( ! get_option( 'cariera_notifications' ) ) {
			return;
		}

		if ( ! get_option( 'cariera_notification_listing_promotion_ended' ) ) {
			return;
		}

		$owner = get_post_field( 'post_author', $post_id );

		$this->insert_notification(
			[
				'action'   => 'promotion_expired',
				'owner_id' => $owner,
				'user_id'  => '',
				'post_id'  => $post_id,
			]
		);

		// Check if webhooks are enabled.
		if ( get_option( 'cariera_notification_listing_promotion_webhook' ) ) {
			$this->send_webhook( 'promotion_expired', $owner, $post_id );
		}
	}

	/**
	 * Send webhook
	 *
	 * @since   1.5.2
	 * @version 1.6.0
	 */
	public function send_webhook( $action, $user_id = '', $post_id = '' ) {

		$webhook_url = get_option( sprintf( 'cariera_notification_webhook_url_%s', $action ) );

		// Check url.
		if ( empty( $webhook_url ) or filter_var( $webhook_url, FILTER_VALIDATE_URL ) === false ) {
			return;
		}

		$user          = get_userdata( $user_id );
		$listing_title = ! empty( $post_id ) ? get_the_title( $post_id ) : '';
		$listing_url   = ! empty( $post_id ) ? get_permalink( $post_id ) : '';

		$data = [
			// User Data.
			'email'              => $user->user_email,
			'name'               => $user->display_name,
			'first_name'         => $user->first_name,
			'last_name'          => $user->last_name,
			'phone'              => $user->phone,
			'billing_first_name' => $user->billing_first_name,
			'billing_last_name'  => $user->billing_last_name,
			'billing_email'      => $user->billing_email,
			'billing_phone'      => $user->billing_phone,
			'billing_country'    => $user->billing_country,
			'billing_city'       => $user->billing_city,
			'billing_postcode'   => $user->billing_postcode,
			// Listing.
			'listing_id'         => $post_id,
			'listing_title'      => $listing_title,
			'listing_url'        => $listing_url,
		];

		$ch = curl_init( $webhook_url );
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			[
				'Content-type: application/json',
			]
		);
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $ch );

		curl_close( $ch );
	}

	/**
	 * Webhook test trigger AJAX
	 *
	 * @since   1.5.2
	 * @version 1.6.0
	 */
	public function webhook_test_trigger() {

		$webhook_id  = sanitize_text_field( $_POST['webhook_id'] );
		$webhook_url = esc_url_raw( $_POST['webhook_url'] );

		// Check url.
		if ( empty( $webhook_url ) or filter_var( $webhook_url, FILTER_VALIDATE_URL ) === false ) {
			return;
		}

		// Test Data.
		$data = [
			// User Data.
			'email'              => 'example@cariera.co',
			'name'               => 'John Doe',
			'first_name'         => 'John',
			'last_name'          => 'Doe',
			'phone'              => '999 999 999',
			'billing_first_name' => 'John',
			'billing_last_name'  => 'Doe',
			'billing_email'      => 'example@cariera.co',
			'billing_phone'      => '999 999 999',
			'billing_country'    => 'DE',
			'billing_city'       => 'Berlin',
			'billing_postcode'   => '10115',
			// Listing.
			'listing_id'         => '1',
			'listing_title'      => 'Web Designer Listing',
			'listing_url'        => 'https://example.com',
		];

		$ch = curl_init( $webhook_url );
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			[
				'Content-type: application/json',
			]
		);
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $ch );

		curl_close( $ch );

		wp_send_json(
			[
				'success' => true,
				'output'  => $response,
			]
		);
	}

	/**
	 * Delete all notifications from the DB Table via AJAX
	 *
	 * @since   1.5.6
	 * @version 1.5.6
	 */
	public function delete_all_notifications() {
		global $wpdb;

		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}cariera_notifications" );

		wp_send_json( [ 'success' => true ] );
	}
}

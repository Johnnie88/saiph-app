<?php
/**
 * Lists a users bookmarks.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-bookmarks/my-bookmarks.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Bookmarks
 * @category    Template
 * @version     1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'cariera-wpjm-dashboards' );
?>

<div id="job-manager-bookmarks">
	<table class="cariera-wpjm-dashboard job-manager-bookmarks">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Bookmark', 'cariera' ); ?></th>
				<th><?php esc_html_e( 'Notes', 'cariera' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $bookmarks as $bookmark ) : ?>
				<tr>
					<td width="50%">
						<a href="<?php the_permalink( $bookmark->post_id ); ?>">
							<?php
							$post_type = get_post_type( $bookmark->post_id );
							if ( function_exists( 'the_candidate_photo' ) && 'resume' === $post_type ) {
								the_candidate_photo( 'thumbnail', null, $bookmark->post_id );
							} elseif ( function_exists( 'the_company_logo' ) && 'job_listing' === $post_type ) {
								// the_company_logo( 'thumbnail', null, $bookmark->post_id );

								// Custom condition: when cariera companies are enabled.
								if ( get_option( 'cariera_company_manager_integration', false ) ) {
									$company = cariera_get_the_company( $bookmark->post_id );
									the_company_logo( 'thumbnail', null, $company );
								} else {
									the_company_logo( 'thumbnail', null, $bookmark->post_id );
								}
							} elseif ( \Cariera\cariera_core_is_activated() && 'company' === $post_type ) {
								// Custom condition: when a cariera company has been bookmarked.
								the_company_logo( 'thumbnail', null, $bookmark->post_id );
							}
							?>
							<?php echo get_the_title( $bookmark->post_id ); ?>
						</a>
						<ul class="job-manager-bookmark-actions">
							<?php
								$actions = apply_filters( 'job_manager_bookmark_actions', array(
									'delete' => array(
										'label' => esc_html__( 'Delete', 'cariera' ),
										'url'   => wp_nonce_url( add_query_arg( 'remove_bookmark', $bookmark->post_id ), 'remove_bookmark' )
									)
								), $bookmark );

								foreach ( $actions as $action => $value ) {
									echo '<li><a href="' . esc_url( $value['url'] ) . '" class="job-manager-bookmark-action-' . $action . '">' . $value['label'] . '</a></li>';
								}
							?>
						</ul>
					</td>
					<td width="50%">
						<?php echo wpautop( wp_kses_post( $bookmark->bookmark_note ) ); ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<tr class="no-bookmarks-notice">
				<td colspan="2" ><?php esc_html_e( 'You currently have no bookmarks', 'cariera' ); ?></td>
			</tr>
		</tbody>
	</table>
	<?php get_job_manager_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages ) ); ?>
</div>

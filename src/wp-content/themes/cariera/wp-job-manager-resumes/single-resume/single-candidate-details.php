<?php
/**
 * Custom: Single Resume - Candidate Details
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/single-candidate-details.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="candidate-details">
	<div class="candidate">
		<div class="candidate-photo">
			<?php cariera_the_candidate_photo(); ?>
		</div>

		<h1 class="title"><?php the_title(); ?></h1>
	</div>

	<div class="details">
		<div class="candidate-detail location">
			<i class="icon-location-pin"></i><?php the_candidate_location( false ); ?>
		</div>

		<div class="candidate-detail published-date">
			<i class="icon-clock"></i><?php printf( '%s %s', esc_html__( 'Member Since ', 'cariera' ), get_the_date( 'Y' ) ); ?>
		</div>

		<?php
		if ( resume_manager_user_can_view_contact_details( $post->ID ) ) {
			do_action( 'single_resume_contact_start' );

			foreach ( get_resume_links() as $link ) {
				$parsed_url = parse_url( $link['url'] );
				$host       = isset( $parsed_url['host'] ) ? current( explode( '.', $parsed_url['host'] ) ) : '';
				?>
				<div class="candidate-detail links">
					<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank"><i class="fas fa-link"></i> <?php echo esc_html( $link['name'] ); ?></a>
				</div>
				<?php
			}

			$email = get_post_meta( $post->ID, '_candidate_email', true );
			if ( $email ) {
				?>
				<div class="candidate-detail candidate-email">
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><i class="icon-envelope"></i><?php echo esc_html( $email ); ?></a>
				</div>
				<?php
			}

			do_action( 'single_resume_contact_end' );
		} else {
			get_job_manager_template_part( 'access-denied', 'contact-details', 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
		}
		?>
	</div>

	<div class="actions">
		<?php do_action( 'cariera_candidate_socials' ); ?>

		<?php
		if ( get_option( 'cariera_private_messages' ) && get_option( 'cariera_private_messages_resumes' ) ) {
			get_job_manager_template_part( 'single-resume/private', 'message', 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
		} else {
			get_job_manager_template( 'contact-details.php', [ 'post' => $post ], 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
		}
		?>

		<?php do_action( 'cariera_candidate_detail_actions' ); ?>
	</div>
</div>

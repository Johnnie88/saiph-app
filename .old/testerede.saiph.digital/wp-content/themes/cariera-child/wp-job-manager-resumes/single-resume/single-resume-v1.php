<?php
/**
 * Custom: Single Resume - Layout Version 1
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/single-resume-v1.php.
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

$image = get_post_meta( $post->ID, '_featured_image', true );
?>

<main id="post-<?php the_ID(); ?>" class="single-resume-page single-resume-v1">
	<?php do_action( 'cariera_single_resume_before' ); ?>
	<section class="page-header resume-header overlay-gradient" <?php echo ! empty( $image ) ? 'style="background: url(' . esc_attr( $image ) . ')"' : ''; ?>></section>

	<section class="single-resume-content">
		<div class="container">
			<div class="candidate-main-resume">
				<div class="candidate-extra-info">
					<?php
					if ( get_option( 'resume_manager_enable_categories' ) ) {
						$categories = wp_get_object_terms( $post->ID, 'resume_category' );

						if ( is_wp_error( $categories ) ) {
							return '';
						}

						echo '<div class="left-side"><ul class="candidate-categories">';
						foreach ( $categories as $category ) {
							echo '<li><a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
						}
						echo '</ul></div>';
					}
					?>

					<div class="right-side">
						<div class="location">
							<i class="icon-location-pin"></i>
							<?php the_candidate_location( false ); ?>
						</div>

						<div class="published-date">
							<i class="icon-clock"></i>
							<?php printf( '%s %s', esc_html__( 'Member Since ', 'cariera' ), get_the_date( 'Y' ) ); ?>
						</div>

						<?php
						if ( resume_has_file() ) {
							if ( ( $resume_files = get_resume_files() ) && apply_filters( 'resume_manager_user_can_download_resume_file', true, $post->ID ) ) {
								foreach ( $resume_files as $key => $resume_file ) {
									?>
									<div class="candidate-resume">
										<a href="<?php echo esc_url( get_resume_file_download_url( null, $key ) ); ?>"><?php esc_html_e( 'Download CV', 'cariera' ); ?></a>
									</div>
									<?php
								}
							}
						}
						?>
					</div>
				</div>

				<!-- Candidate Info Wrapper -->
				<div class="candidate-info-wrapper">
					<div class="candidate-photo">
						<?php cariera_the_candidate_photo(); ?>
					</div>

					<div class="candidate">
						<h1 class="title"><?php the_title(); ?></h1>

						<?php
						if ( resume_manager_user_can_view_contact_details( $post->ID ) ) {
							do_action( 'single_resume_contact_start' );
							?>

							<div class="candidate-links">
								<?php
								foreach ( get_resume_links() as $link ) {
									$parsed_url = parse_url( $link['url'] );
									$host       = isset( $parsed_url['host'] ) ? current( explode( '.', $parsed_url['host'] ) ) : '';
									?>
									<span class="links">
										<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank"><i class="fas fa-link"></i> <?php echo esc_html( $link['name'] ); ?></a>
									</span>
									<?php
								}

								$email = get_post_meta( $post->ID, '_candidate_email', true );
								if ( $email ) {
									?>
									<span class="candidate-email">
										<a href="mailto:<?php echo esc_attr( $email ); ?>"><i class="icon-envelope"></i><?php echo esc_html( $email ); ?></a>
									</span>
								<?php } ?>
							</div>

							<?php
							do_action( 'single_resume_contact_end' );
						} else {
							get_job_manager_template_part( 'access-denied', 'contact-details', 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
						}
						?>

						<?php do_action( 'cariera_candidate_socials' ); ?>
					</div>

					<!-- Bookmark -->
					<div class="bookmark-wrapper">
						<?php
						if ( get_option( 'cariera_private_messages' ) && get_option( 'cariera_private_messages_resumes' ) ) {
							get_job_manager_template_part( 'single-resume/private', 'message', 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
						} else {
							get_job_manager_template( 'contact-details.php', [ 'post' => $post ], 'wp-job-manager-resumes', RESUME_MANAGER_PLUGIN_DIR . '/templates/' );
						}

						do_action( 'cariera_bookmark_hook' );
						?>
					</div>
				</div>
			</div>

			<!-- Main Candidate Content -->
			<div class="resume-main row pb80">
				<div class="col-md-8 col-xs-12">
					<div class="grafico-container">
						<canvas id="graficoPorcentagens" width="400" height="200"></canvas>
					</div>
					<?php
					do_action( 'single_resume_start' );
					do_action( 'single_resume_content' );
					do_action( 'single_resume_end' );
					?>
				</div>

				<div class="col-md-4 col-xs-12 resume-sidebar">
					<?php do_action( 'cariera_single_resume_sidebar' ); ?>
				</div>
			</div>
		</div>
	</section>

	<?php do_action( 'cariera_single_resume_after' ); ?>
</main>

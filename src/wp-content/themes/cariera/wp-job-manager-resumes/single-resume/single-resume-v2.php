<?php
/**
 * Custom: Single Resume - Layout Version 2
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/single-resume-v2.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image = get_post_meta( $post->ID, '_featured_image', true );
?>

<main id="post-<?php the_ID(); ?>" class="single-resume-page single-resume-v2">
	<?php do_action( 'cariera_single_resume_before' ); ?>

	<section class="page-header resume-header" <?php echo ! empty( $image ) ? 'style="background-image: url(' . esc_attr( $image ) . ');"' : ''; ?>>
		<div class="container">
			<div class="row">
				<div class="resume-info">
					<div class="title">
						<?php
						if ( get_option( 'resume_manager_enable_categories' ) ) {
							$categories = wp_get_object_terms( $post->ID, 'resume_category' );

							if ( is_wp_error( $categories ) ) {
								return '';
							}

							echo '<ul class="candidate-categories">';
							foreach ( $categories as $category ) {
								echo '<li><a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
							}
							echo '</ul>';
						}
						?>
						<h3 class="resume-title"><?php the_candidate_title(); ?></h3>
					</div>

					<div class="listing-actions">
						<?php do_action( 'cariera_bookmark_hook' ); ?>
						<?php do_action( 'cariera_resume_actions' ); ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="single-resume-content">
		<div class="container">
			<div class="row">
				<div class="col-lg-8 col-md-12">
					<div class="single-resume">
						<?php
						do_action( 'single_resume_start' );
						do_action( 'single_resume_content' );
						do_action( 'single_resume_end' );
						?>
					</div>
				</div>

				<div class="col-lg-4 col-md-12 candidate-details-wrapper">
					<?php get_job_manager_template_part( 'single-resume/single', 'candidate-details', 'wp-job-manager-resumes' ); ?>
				</div>
			</div>
		</div>
	</section>

	<?php do_action( 'cariera_single_resume_after' ); ?>
</main>

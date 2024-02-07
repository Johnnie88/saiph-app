<?php
/**
 * Custom: Resume Listing - Grid Version 3
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/resume-templates/content-resume_grid3.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.5
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$resume_class = 'resume-grid single_resume_3 col-lg-4 col-md-6 col-xs-12';
$category     = get_the_resume_category();
$featured     = get_post_meta( get_the_ID(), '_featured', true ) == 1 ? 'featured' : '';
$logo         = get_the_candidate_photo();

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
} ?>

<li <?php cariera_resume_class( esc_attr( $resume_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>" data-featured="<?php echo esc_attr( $featured ); ?>">
	<a href="<?php the_resume_permalink(); ?>">
		<div class="resume-info-wrapper">
			<div class="candidate-photo">
				<?php cariera_the_candidate_photo(); ?>
			</div>

			<!-- Resume Details -->
			<div class="resume-details">
				<h5 class="candidate-title"><?php the_title(); ?></h5>

				<ul>
					<li class="location"><i class="icon-location-pin"></i><?php the_candidate_location( false ); ?></li>

					<?php if ( get_post_meta( $post->ID, '_salary_min', true ) ) { ?>
						<li class="salary"><i class="far fa-money-bill-alt"></i><?php cariera_job_salary(); ?></li>
					<?php } ?>

				</ul>
			</div>
		</div>

		<!-- Resume Extras -->
		<div class="resume-extras">
			<div class="resume-title-icon"></div>
			<span class="professional-title"><?php the_candidate_title(); ?></span>
		</div>
	</a>
</li>

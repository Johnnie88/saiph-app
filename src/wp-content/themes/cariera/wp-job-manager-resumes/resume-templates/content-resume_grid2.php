<?php
/**
 * Custom: Resume Listing - Grid Version 2
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/resume-templates/content-resume_grid2.php.
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

$resume_class = 'resume-grid single_resume_2 col-lg-4 col-md-6 col-xs-12';
$category     = get_the_resume_category();
$featured     = get_post_meta( get_the_ID(), '_featured', true ) == 1 ? 'featured' : '';
$logo         = get_the_candidate_photo();

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
} ?>

<li <?php cariera_resume_class( esc_attr( $resume_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>" data-featured="<?php echo esc_attr( $featured ); ?>">
	<div class="resume-content-wrapper">
		<a href="<?php the_resume_permalink(); ?>">
			<div class="candidate-photo-wrapper">
				<div class="candidate-photo">
					<?php cariera_the_candidate_photo(); ?>
				</div>
			</div>

			<!-- resume Details -->
			<div class="resume-details">
				<div class="resume-title">
					<h5><?php the_title(); ?></h5>
					<span><?php the_candidate_title(); ?></span>
				</div>

				<?php if ( ! empty( get_the_candidate_location() ) ) { ?>
					<div class="resume-location">
						<span><i class="icon-location-pin"></i><?php echo the_candidate_location( false ); ?></span>
					</div>
				<?php } ?>
			</div>
		</a>
	</div>
</li>

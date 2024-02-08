<?php
/**
 * Custom: Resume Listing - Grid Version 1
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/resume-templates/content-resume_grid1.php.
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

$resume_class = 'resume-grid single_resume_1 col-lg-4 col-md-6 col-xs-12';
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

		<!-- Resume Content Body -->
		<div class="resume-content-body">
			<div class="candidate-photo">
				<?php cariera_the_candidate_photo(); ?>
			</div>

			<div class="resume-info">
				<div class="resume-title">
					<a href="<?php the_resume_permalink(); ?>">
						<h4 class="title"><?php the_title(); ?></h4>
					</a>
					<span><?php the_candidate_title(); ?></span>
				</div>
			</div>
		</div>

		<!-- Resume Content Footer -->
		<div class="resume-content-footer">
			<div class="resume-details">
				<div class="location">
					<h5 class="title"><?php esc_html_e( 'Location', 'cariera' ); ?></h5>
					<span><?php echo the_candidate_location( false ); ?></span>
				</div>

				<div class="published">
					<h5 class="title"><?php esc_html_e( 'Published', 'cariera' ); ?></h5>
					<span>
						<?php $display_date = sprintf( esc_html__( 'Posted %s ago', 'cariera' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
						<time datetime="<?php echo esc_attr( get_post_time( 'Y-m-d' ) ); ?>"><?php echo wp_kses_post( $display_date ); ?></time>
					</span>
				</div>
			</div>
		</div>
	</div>
</li>

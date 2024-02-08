<?php
/**
 * Template for resume content inside a list of resumes.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/content-resume.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     WP Job Manager - Resume Manager
 * @category    Template
 * @version     1.18.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$resume_class = 'resume-list single_resume_1';
$category     = get_the_resume_category();
$logo         = get_the_candidate_photo();

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'resume_manager_default_candidate_photo', get_template_directory_uri() . '/assets/images/candidate.png' );
} ?>



<li <?php cariera_resume_class( esc_attr( $resume_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo get_the_ID(); ?>">
	<a href="<?php the_resume_permalink(); ?>" class="resume-link">
		<div class="candidate-photo-wrapper">
			<div class="candidate-photo">
				<?php cariera_the_candidate_photo(); ?>
			</div>
		</div>

		<!-- Candidate Title & Info -->
		<div class="candidate-content-main">
			<div class="candidate-title">
				<h5><?php the_title(); ?></h5>
			</div>

			<div class="candidate-info">
				<?php
				// Action to add support for WPJM Field Editor.
				ob_start();
					do_action( 'resume_listing_meta_start' );
					$resume_listing_meta_start = ob_get_contents();
				ob_end_clean();

				if ( ! empty( $resume_listing_meta_start ) ) {
					do_action( 'resume_listing_meta_start' );
				}
				?>

				<span class="location">
					<i class="icon-location-pin"></i>
					<?php the_candidate_location( false ); ?>
				</span>

				<span class="occupation">
					<i class="fas fa-briefcase"></i>
					<?php the_candidate_title(); ?>
				</span> 

				<?php
				$rate = get_post_meta( $post->ID, '_rate', true );
				if ( ! empty( $rate ) ) {
					?>
					<span class="rate">
						<i class="far fa-money-bill-alt"></i>
						<?php cariera_resume_rate(); ?>
					</span>
				<?php } ?>

				<?php
				// Action to add support for WPJM Field Editor.
				ob_start();
					do_action( 'resume_listing_meta_end' );
					$resume_listing_meta_end = ob_get_contents();
				ob_end_clean();

				if ( ! empty( $resume_listing_meta_end ) ) {
					do_action( 'resume_listing_meta_end' );
				}
				?>
			</div>            
		</div>

		<!-- Resume Posted & Category -->    
		<div class="resume-posted 
		<?php
		if ( $category ) :
			?>
			resume-meta<?php endif; ?>">
			<date><?php printf( esc_html__( '%s ago', 'cariera' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date>

			<?php if ( $category ) : ?>
				<div class="resume-category">
					<?php echo esc_html( $category ); ?>
				</div>
			<?php endif; ?>
		</div>        
	</a>
</li>

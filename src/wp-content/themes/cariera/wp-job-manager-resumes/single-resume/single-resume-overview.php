<?php
/**
 * Custom: Single Resume - Resume Overview
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-resumes/single-resume/single-resume-overview.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.4.6
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post; ?>

<h5><?php esc_html_e( 'Candidate Overview', 'cariera' ); ?></h5>
<aside class="widget widget-candidate-overview">

	<?php do_action( 'single_resume_meta_start' ); ?>

	<div class="single-resume-overview-detail single-resume-overview-occupation">
		<div class="icon">
			<i class="icon-briefcase"></i>
		</div>

		<div class="content">
			<h6><?php esc_html_e( 'Occupation', 'cariera' ); ?></h6>
			<span><?php the_candidate_title(); ?></span>
		</div>
	</div>

	<?php
	$rate = get_post_meta( $post->ID, '_rate', true );
	if ( ! empty( $rate ) ) {
		?>
		<div class="single-resume-overview-detail single-resume-overview-rate">
			<div class="icon">
				<i class="icon-hourglass"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Rate', 'cariera' ); ?></h6>
				<span><?php cariera_resume_rate(); ?></span>
			</div>
		</div>
	<?php } ?>

	<?php
	$education = get_the_terms( $post->ID, 'resume_education_level' );
	if ( taxonomy_exists( 'resume_education_level' ) && ! empty( $education ) ) {
		?>
		<div class="single-resume-overview-detail single-resume-overview-education">
			<div class="icon">
				<i class="icon-graduation"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Education Level', 'cariera' ); ?></h6>
				<span>
					<?php
					foreach ( $education as $value ) {
						$output_education[] = $value->name;
					}

					echo esc_html( join( ', ', $output_education ) );
					?>
				</span>
			</div>
		</div>
	<?php } ?>

	<?php
	$languages = get_post_meta( $post->ID, '_languages', true );
	if ( ! empty( $languages ) ) {
		?>
		<div class="single-resume-overview-detail single-resume-overview-languages">
			<div class="icon">
				<i class="icon-bubbles"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Languages', 'cariera' ); ?></h6>
				<span>
					<?php
					if ( is_array( $languages ) ) {
						foreach ( $languages as $value ) {
							$output_lang[] = $value;
						}

						echo esc_html( join( ', ', $output_lang ) );
					} else {
						echo esc_html( $languages );
					}
					?>
				</span>
			</div>
		</div>
	<?php } ?>

	<?php
	$experience = get_the_terms( $post->ID, 'resume_experience' );
	if ( taxonomy_exists( 'resume_experience' ) && ! empty( $experience ) ) {
		?>
		<div class="single-resume-overview-detail single-resume-overview-experience">
			<div class="icon">
				<i class="icon-graph"></i>
			</div>

			<div class="content">
				<h6><?php esc_html_e( 'Experience', 'cariera' ); ?></h6>
				<span>
					<?php
					foreach ( $experience as $value ) {
						$output_experience[] = $value->name;
					}

					echo esc_html( join( ', ', $output_experience ) );
					?>
				</span>
			</div>
		</div>
	<?php } ?>

	<?php do_action( 'single_resume_meta_end' ); ?>
</aside>

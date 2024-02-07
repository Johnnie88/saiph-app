<?php
/**
 * Header CTA template
 *
 * This template can be overridden by copying it to cariera-child/templates/header/header-cta.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.5.0
 * @version     1.6.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! cariera_get_option( 'header_cta' ) ) {
	return;
}

$current_user = wp_get_current_user();
$main_cta     = apply_filters( 'cariera_header_job_link', get_option( 'cariera_header_emp_cta_link' ) );
$candi_cta    = apply_filters( 'cariera_header_resume_link', get_option( 'cariera_header_candidate_cta_link' ) );
?>

<div class="extra-menu-item extra-add-listing">
	<?php if ( ! is_user_logged_in() ) { ?>
		<a href="<?php echo esc_url( get_permalink( $main_cta ) ); ?>" class="header-cta header-cta-job btn btn-main btn-effect btn-small">
			<?php echo apply_filters( 'cariera_header_job_cta', esc_html__( 'Post a Job', 'cariera' ) ); ?>
			<i class="icon-plus"></i>
		</a>
		<?php
	} else {
		if ( in_array( 'employer', (array) $current_user->roles, true ) || in_array( 'administrator', (array) $current_user->roles, true ) ) {
			?>
			<a href="<?php echo esc_url( get_permalink( $main_cta ) ); ?>" class="header-cta header-cta-job btn btn-main btn-effect btn-small">
				<?php echo apply_filters( 'cariera_header_job_cta', esc_html__( 'Post a Job', 'cariera' ) ); ?>
				<i class="icon-plus"></i>
			</a>
			<?php
		}

		if ( in_array( 'candidate', (array) $current_user->roles, true ) ) {
			?>
			<a href="<?php echo esc_url( get_permalink( $candi_cta ) ); ?>" class="header-cta header-cta-resume btn btn-main btn-effect btn-small">
				<?php echo apply_filters( 'cariera_header_resume_cta', esc_html__( 'Post a Resume', 'cariera' ) ); ?>
				<i class="icon-plus"></i>
			</a>
			<?php
		}
	}
	?>
</div>

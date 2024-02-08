<?php
/**
 * Custom: Job Listing - Carousel Content
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-template/job-carousel.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.6.2
 * @version     1.6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$types   = wpjm_get_the_job_types();
$excerpt = get_the_excerpt();

if ( \Cariera\cariera_core_is_activated() && get_option( 'cariera_company_manager_integration', false ) ) {
	$company = get_post( cariera_get_the_company() );
} else {
	$company = '';
}
?>

<div class="single-job">
	<div class="company">
		<?php
		// Company Logo.
		if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
			$logo = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
			echo '<img class="company_logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_company_name( $company ) ) . '" />';
		} else {
			cariera_the_company_logo();
		}
		?>
	</div>

	<div class="job-info">
		<div class="job-title">
			<h5 class="title">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</h5>

			<?php
			if ( ! empty( $types ) ) {
				foreach ( $types as $type ) {
					?>
					<span class="job-type term-<?php echo esc_attr( $type->term_id ); ?> <?php echo esc_attr( sanitize_title( $type->slug ) ); ?>"><?php echo esc_html( $type->name ); ?></span>
					<?php
				}
			}
			?>
		</div>
		<div class="job-meta">
			<span class="company-name"><i class="far fa-building"></i><?php the_company_name(); ?></span>
			<span class="location"><i class="icon-location-pin"></i><?php the_job_location(); ?></span>
		</div>

		<div class="job-description">
			<?php echo cariera_string_limit_words( $excerpt, 20 ); ?>...
		</div>

		<div class="text-center mt20">
			<a href="<?php the_permalink(); ?>" class="btn btn-main"><?php esc_html_e( 'Apply For This Job', 'cariera' ); ?></a>
		</div>
	</div>
</div>

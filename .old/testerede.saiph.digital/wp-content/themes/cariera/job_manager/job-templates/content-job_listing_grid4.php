<?php
/**
 * Custom: Job Listing - Grid Version 4
 *
 * This template can be overridden by copying it to yourtheme/job_manager/job-template/content-job_listing_grid4.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.3
 * @version     1.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

$job_class = 'job-grid single_job_listing_4 col-lg-4 col-md-6 col-xs-12';

$job_id   = get_the_ID();
$company  = '';
$logo     = get_the_company_logo();
$featured = get_post_meta( $job_id, '_featured', true ) == 1 ? 'featured' : '';

// If Cariera Company manager exists and company integration check.
if ( \Cariera\cariera_core_is_activated() && get_option( 'cariera_company_manager_integration', false ) && function_exists( 'cariera_get_the_company' ) ) {
	$company = get_post( cariera_get_the_company() );
}

// Logo if there is an active company.
if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
	$logo = get_the_company_logo( $company, apply_filters( 'cariera_company_logo_size', 'thumbnail' ) );
}

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
} ?>


<li <?php job_listing_class( esc_attr( $job_class ) ); ?> data-latitude="<?php echo esc_attr( $post->geolocation_lat ); ?>" data-longitude="<?php echo esc_attr( $post->geolocation_long ); ?>" data-thumbnail="<?php echo esc_attr( $logo_img ); ?>" data-id="listing-id-<?php echo esc_attr( get_the_ID() ); ?>" data-featured="<?php echo esc_attr( $featured ); ?>">
	<a href="<?php the_permalink(); ?>">

		<!-- Job Info Wrapper -->
		<div class="job-info-wrapper">
			<div class="logo-wrapper">
				<?php
				// Company Logo.
				if ( ! empty( $company ) && has_post_thumbnail( $company ) ) {
					echo '<img class="company_logo" src="' . esc_url( $logo ) . '" alt="' . esc_attr( get_the_company_name( $company ) ) . '" />';
				} else {
					cariera_the_company_logo();
				}
				?>
			</div>

			<div class="job-info">
				<h5 class="title">
					<?php the_title(); ?>
					<?php do_action( 'cariera_job_listing_status' ); ?>    
				</h5>

				<ul>
					<li class="location"><i class="icon-location-pin"></i><?php the_job_location( false ); ?></li>

					<?php if ( get_post_meta( $post->ID, '_salary_min', true ) ) { ?>
						<li class="salary"><i class="far fa-money-bill-alt"></i><?php cariera_job_salary(); ?></li>
					<?php } ?>

					<?php
					if ( empty( get_post_meta( $post->ID, '_salary_min', true ) ) ) {
						if ( get_post_meta( $post->ID, '_rate_min', true ) ) {
							?>
							<li class="rate"><i class="far fa-money-bill-alt"></i><?php cariera_job_rate(); ?></li>
							<?php
						}
					}
					?>
				</ul>
			</div>
		</div>

		<!-- Job Extras -->
		<div class="job-extras">
			<?php
			$job_types = [];
			$types     = wpjm_get_the_job_types();

			if ( ! empty( $types ) ) {
				foreach ( $types as $type ) {
					$job_types[] = $type->name;
				}
			}
			?>

			<div class="job-type-icon"></div>
			<span class="job-types"><?php echo esc_html( implode( ', ', $job_types ) ); ?></span>
		</div>
	</a>
</li>

<?php
/**
 * Single job listing widget content.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-widget-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.31.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Cariera custom code. 1.7.0
global $post;

$job_id  = get_the_ID();
$company = '';

if ( $job_id ) {
	$post_title = get_post_meta( $job_id, '_company_name', true );
	if ( ! empty( $post_title ) ) {
		$company = \Cariera\get_page_by_title( $post_title, 'company' );
	}
}

$logo = get_the_company_logo();

if ( ! empty( $logo ) ) {
	$logo_img = $logo;
} else {
	$logo_img = apply_filters( 'job_manager_default_company_logo', get_template_directory_uri() . '/assets/images/company.png' );
}
// custom code end.
?>

<li <?php job_listing_class(); ?>>
	<a href="<?php the_job_permalink(); ?>">
		<?php if ( isset( $show_logo ) && $show_logo ) { ?>
		<div class="image">
			<?php 
			// Custom condition: when cariera companies are enabled.
			if ( get_option( 'cariera_company_manager_integration', false ) ) {
				$company = cariera_get_the_company( $post->post_id );
				the_company_logo( 'thumbnail', null, $company );
			} else {
				the_company_logo( 'thumbnail', null, $post->post_id );
			}
			?>
		</div>
		<?php } ?>
		<div class="content">
			<div class="position">
				<h3><?php wpjm_the_job_title(); ?></h3>
			</div>
			<ul class="meta">
				<li class="location"><?php the_job_location( false ); ?></li>
				<li class="company"><?php the_company_name(); ?></li>
				<?php if ( get_option( 'job_manager_enable_types' ) ) { ?>
					<?php $types = wpjm_get_the_job_types(); ?>
					<?php if ( ! empty( $types ) ) : foreach ( $types as $type ) : ?>
						<li class="job-type <?php echo esc_attr( sanitize_title( $type->slug ) ); ?>"><?php echo esc_html( $type->name ); ?></li>
					<?php endforeach; endif; ?>
				<?php } ?>
			</ul>
		</div>
	</a>
</li>

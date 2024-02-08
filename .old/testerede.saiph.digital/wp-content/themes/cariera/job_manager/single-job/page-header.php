<?php
/**
 * Custom: Single Job Page - Page Header
 *
 * This template can be overridden by copying it to yourtheme/job_manager/single-job/page-header.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.5.5
 * @version     1.6.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image = get_post_meta( $post->ID, '_job_cover_image', true );
$types = wpjm_get_the_job_types();
?>

<section class="page-header job-header <?php echo ! empty( $image ) ? esc_attr( 'page-header-bg' ) : ''; ?>" <?php echo ! empty( $image ) ? 'style="background: url(' . esc_attr( $image ) . ');"' : ''; ?>>
	<div class="container">
		<div class="row">
			<div class="col-md-6 col-xs-12">
				<h1 class="title pb15"><?php wpjm_the_job_title(); ?></h1>

				<?php
				if ( ! empty( $types ) ) {
					foreach ( $types as $type ) {
						?>
						<span class="job-type term-<?php echo esc_attr( $type->term_id ); ?> <?php echo esc_attr( sanitize_title( $type->slug ) ); ?>"><?php echo esc_html( $type->name ); ?></span>
						<?php
					}
				}

				// If job is new than show the new tag.
				if ( cariera_newly_posted() ) {
					echo '<span class="job-type new-job-tag">' . esc_html__( 'New', 'cariera' ) . '</span>';
				}
				?>
			</div>

			<!-- Bookmark -->
			<div class="col-md-6 col-xs-12 bookmark-wrapper">
				<?php 
				do_action( 'cariera_bookmark_hook' );

				if ( get_option( 'cariera_private_messages' ) && get_option( 'cariera_private_messages_job_listings' ) ) {
					get_job_manager_template_part( 'single-job/private', 'message' );
				}
				?>
			</div>
		</div>
	</div>
</section>

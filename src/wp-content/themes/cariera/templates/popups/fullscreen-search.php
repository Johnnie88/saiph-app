<?php
/**
 * Fullscreen search
 *
 * This template can be overridden by copying it to cariera-child/templates/popups/fullscreen-search.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$jobs_page_url = get_permalink( get_option( 'job_manager_jobs_page_id' ) );
?>

<div id="quick-search-modal" class="small-dialog zoom-anim-dialog mfp-hide">
	<div class="small-dialog-headline">
		<h4 class="title"><?php esc_html_e( 'Job Quick Search', 'cariera' ); ?></h4>
	</div>

	<div class="small-dialog-content">
		<form method="GET" action="<?php echo esc_url( $jobs_page_url ); ?>" class="job-search-form">
			<div class="col-md-12 quick-search-keywords">
				<input type="text" id="quick_search_keywords" name="search_keywords" placeholder="<?php esc_html_e( 'Keywords', 'cariera' ); ?>" autocomplete="off">
			</div>
			<div class="col-md-12 quick-search-location mt25">
				<input type="text" id="quick_search_location" name="search_location" placeholder="<?php esc_html_e( 'Location', 'cariera' ); ?>" autocomplete="off">
			</div>
			<div class="col-md-12">
				<input type="submit" class="btn btn-main btn-effect" value="<?php esc_html_e( 'Search', 'cariera' ); ?>">
			</div>
		</form>
	</div>
</div>

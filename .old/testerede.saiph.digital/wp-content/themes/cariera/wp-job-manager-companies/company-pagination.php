<?php
/**
 * Custom: Company - Company Pagination
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-pagination.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.3.0
 * @version     1.5.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $max_num_pages <= 1 ) {
	return;
}

// Calculate pages to output.
$end_size    = 3;
$mid_size    = 3;
$start_pages = range( 1, $end_size );
$end_pages   = range( $max_num_pages - $end_size + 1, $max_num_pages );
$mid_pages   = range( $current_page - $mid_size, $current_page + $mid_size );
$pages       = array_intersect( range( 1, $max_num_pages ), array_merge( $start_pages, $end_pages, $mid_pages ) );
$prev_page   = 0; ?>

<nav class="company-manager-pagination pagination col-md-12">
	<ul>
		<?php if ( $current_page && $current_page > 1 ) { ?>
			<li><a href="#" data-page="<?php echo esc_attr( $current_page - 1 ); ?>">&larr;</a></li>
			<?php
		}

		foreach ( $pages as $page ) {
			if ( $prev_page != $page - 1 ) {
				echo '<li><span class="gap">...</span></li>';
			}
			if ( $current_page == $page ) {
				echo '<li><span class="current" data-page="' . esc_attr( $page ) . '">' . esc_html( $page ) . '</span></li>';
			} else {
				echo '<li><a href="#" data-page="' . esc_attr( $page ) . '">' . esc_html( $page ) . '</a></li>';
			}
			$prev_page = $page;
		}

		if ( $current_page && $current_page < $max_num_pages ) {
			?>
			<li><a href="#" data-page="<?php echo esc_attr( $current_page + 1 ); ?>">&rarr;</a></li>
		<?php } ?>
	</ul>
</nav>

<?php
/**
 * Custom: Company - Company List Shortcode Output
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-companies/company-list.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @package     Cariera
 * @category    Template
 * @since       1.6.0
 * @version     1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="companies-listing-a-z">
	<?php if ( $show_letters ) { ?>
		<div class="company-letters">
			<ul>
				<li><a href="#all"  data-target="#all" class="all chosen"><?php esc_html_e( 'All', 'cariera' ); ?></a></li>
				<?php

				if ( ! isset( $companies['numeric'] ) ) {
					?>
					<li><span><?php echo esc_html( '#' ); ?></span></li>
				<?php } else { ?>
					<li><a href="#numeric" data-target="#numeric"><?php echo esc_html( '#' ); ?></a></li>
				<?php } ?>

				<?php
				foreach ( range( 'A', 'Z' ) as $letter ) {
					if ( ! isset( $companies[ $letter ] ) ) {
						?>
						<li><span><?php echo esc_html( $letter ); ?></span></li>
					<?php } else { ?>
						<li><a href="#<?php echo esc_attr( $letter ); ?>"  data-target="#<?php echo esc_attr( $letter ); ?>"><?php echo esc_html( $letter ); ?></a></li>
						<?php
					}
				}
				?>
			</ul>
		</div>
	<?php } ?>

	<ul class="companies-overview">
		<?php if ( isset( $companies['numeric'] ) ) { ?>
			<li class="company-group"><div class="company-group-inner">
				<div id="numeric" class="company-letter"><?php echo esc_html( '#' ); ?></div>
				<ul>
					<?php foreach ( $companies['numeric'] as $company ) { ?>
						<li class="company-name"><a href="<?php echo esc_url( get_permalink( $company ) ); ?>"><?php echo esc_html( $company->post_title ); ?></a></li>
					<?php } ?>
				</ul>
			</li>
			<?php
		}

		foreach ( range( 'A', 'Z' ) as $letter ) {
			if ( ! isset( $companies[ $letter ] ) ) {
				continue;
			}
			?>

			<li class="company-group"><div class="company-group-inner">
				<div id="<?php echo esc_attr( $letter ); ?>" class="company-letter"><?php echo esc_html( $letter ); ?></div>
				<ul>
					<?php foreach ( $companies[ $letter ] as $company ) { ?>
						<li class="company-name"><a href="<?php echo esc_url( get_permalink( $company ) ); ?>"><?php echo esc_html( $company->post_title ); ?></a></li>
					<?php } ?>
				</ul>
			</li>
		<?php } ?>
	</ul>
</div>

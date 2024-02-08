<?php
/**
 * Onboarding: Compatible Plugins
 *
 * This template can be overridden by copying it to cariera-child/templates/backend/onboarding/addons.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$products = [
	'wpjm-search-filtering' => [
		'title'  => esc_html( 'S&F for WPJM' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-sf.jpg',
		'link'   => 'https://plugins.smyl.es/wp-job-manager-search-and-filtering',
	],
	'wpjm-field-editor'     => [
		'title'  => esc_html( 'WPJM Field Editor' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-field-editor.jpg',
		'link'   => 'https://plugins.smyl.es/wp-job-manager-field-editor/',
	],
	'wpjm-packages'         => [
		'title'  => esc_html( 'WPJM Packages' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-packages.jpg',
		'link'   => 'https://plugins.smyl.es/wp-job-manager-packages/',
	],
	'wpjm-resume-alerts'    => [
		'title'  => esc_html( 'WPJM Resumes Alerts' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-resume-alerts.jpg',
		'link'   => 'https://plugins.smyl.es/wp-job-manager-resume-alerts/',
	],
	'wpjm-visibility'       => [
		'title'  => esc_html( 'WPJM Visibility' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-visibility.jpg',
		'link'   => 'https://plugins.smyl.es/wp-job-manager-visibility/',
	],
	'wpjm-emails'           => [
		'title'  => esc_html( 'WPJM Emails' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-emails.jpg',
		'link'   => 'https://plugins.smyl.es/wp-job-manager-emails/',
	],

	'wpjm-linkedin'         => [
		'title'  => esc_html( 'Linkedin for WPJM' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-linkedin.jpg',
		'link'   => 'https://1.envato.market/Linkedin-wpjm',
	],
	'wpjm-essentials'       => [
		'title'  => esc_html( 'Essentials for WPJM' ),
		'bg-img' => get_template_directory_uri() . '/assets/images/plugins/wpjm-essentials.jpg',
		'link'   => 'https://1.envato.market/wpjm-essentials',
	],
];
?>

<div id="addons" class="content-page">
	<h2 class="title"><?php esc_html_e( 'Fully Compatible Third Party Plugins', 'cariera' ); ?></h2>
	<div class="onboarding-notice success">
		<p><?php esc_html_e( 'Please contact the author of the plugins regarding any presale or support related questions.', 'cariera' ); ?></p>
		<p><strong><?php echo esc_html( '-5% Coupon for all sMyles products & licenses:' ); ?></strong><span class="coupon-code"><?php echo esc_html( '4carieratheme' ); ?></span></p>
	</div>

	<div class="onboarding-products">
		<?php foreach ( $products as $product ) { ?>
			<div class="product-item">
				<a href="<?php echo esc_url( $product['link'] ); ?>" target="_blank">
					<div class="theme-img" style="background-image: url('<?php echo esc_url( $product['bg-img'] ); ?>');"></div>
					<div class="title"><?php echo esc_html( $product['title'] ); ?></div>
				</a>
			</div>
		<?php } ?>
	</div>
</div>

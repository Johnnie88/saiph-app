<?php
/**
 * Widget: Social media widget template
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/widgets/social-media.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.2
 * @version     1.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


echo wp_kses_post( $args['before_widget'] );

$title = apply_filters( 'widget_title', $instance['title'], $instance, $id_base );
if ( $title ) {
	echo esc_html( $title );
}
?>

<ul class="social-btns">

<?php
foreach ( $socials as $social => $label ) {
	$social_title = $instance[ $social . '_title' ];
	$social_url   = $instance[ $social . '_url' ];

	if ( empty( $social_url ) ) {
		continue;
	}
	?>

	<li class="list-inline-item">
		<a href="<?php echo esc_url( $social_url ); ?>" class="social-btn-roll <?php echo esc_attr( $social ); ?>" target="_blank">
			<div class="social-btn-roll-icons">
				<?php if ( 'twitter-x' === $social ) { ?>
					<svg class="social-btn-roll-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>
					<svg class="social-btn-roll-icon" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>
				<?php } else { ?>                    
					<i class="social-btn-roll-icon fab fa-<?php echo esc_attr( $social ); ?>"></i>
					<i class="social-btn-roll-icon fab fa-<?php echo esc_attr( $social ); ?>"></i>
				<?php } ?>
			</div>
		</a>
	</li>
		
<?php } ?>

</ul>

<?php
echo wp_kses_post( $args['after_widget'] );

<?php

$shop_layout  = cariera_get_option( 'cariera_shop_layout' );
$sidebar_side = ( 'left-sidebar' === $shop_layout ) ? 'sidebar-left' : ''; ?>

<div class="col-md-4 col-xs-12 <?php echo esc_attr( $sidebar_side ); ?>"">
	<?php if ( is_active_sidebar( 'sidebar-shop' ) ) { ?>
		<div class="sidebar">
			<?php dynamic_sidebar( 'sidebar-shop' ); ?>
		</div>
	<?php } ?>
</div>

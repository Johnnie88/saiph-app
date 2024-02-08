<?php

$blog_layout  = cariera_get_option( 'cariera_blog_layout' );
$sidebar_side = 'left-sidebar' === $blog_layout ? 'sidebar-left' : ''; ?>

<div class="col-lg-4 <?php echo esc_attr( $sidebar_side ); ?>">
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) { ?>
		<div class="sidebar">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div>
	<?php } ?>
</div>

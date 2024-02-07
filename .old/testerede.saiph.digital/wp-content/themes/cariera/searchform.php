<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="search" name="s" class="form-control" placeholder="<?php esc_attr_e( 'To search hit enter', 'cariera' ); ?>" value="<?php echo get_search_query(); ?>" />
</form>

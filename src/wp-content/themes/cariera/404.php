<?php

get_header(); ?>

<main class="page-not-found">    
	<div class="container">
		<div class="col-md-12">
			<h2><?php esc_html_e( 'Page not found!', 'cariera' ); ?></h2>
			<p><?php esc_attr_e( 'We\'re sorry, but the page you were looking for doesn\'t exist.', 'cariera' ); ?></p>
			<?php get_search_form(); ?>

			<a href="<?php echo esc_url( home_url() ); ?>" class="btn btn-main btn-effect mt20"><?php esc_attr_e( 'back home', 'cariera' ); ?></a>
		</div>
	</div>
</main>

<?php
get_footer();

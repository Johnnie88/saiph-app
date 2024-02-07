<?php

get_header();

$blog_layout = cariera_get_option( 'cariera_blog_layout' );

if ( 'fullwidth' === $blog_layout ) {
	$layout = 'col-md-12';
} else {
	$layout = 'col-md-8 col-xs-12';
}

if ( cariera_get_option( 'cariera_blog_page_header', 'true' ) ) { ?>
	<section class="page-header">
		<div class="container">
			<div class="row">
				<div class="col-md-12 text-center">
					<h1 class="title"><?php echo \Cariera\get_the_title(); ?></h1>
				</div>
			</div>
		</div>
	</section>
<?php } ?>

<main class="ptb80">
	<div class="container">
		<div class="row">
			<div class="<?php echo esc_attr( $layout ); ?>">
				<?php
				if ( have_posts() ) {
					while ( have_posts() ) :
						the_post();
						get_template_part( 'templates/content/content', get_post_format() );
					endwhile;

					\Cariera\pagination_nav();
				} else {
					get_template_part( 'templates/content/content', 'none' );
				}
				?>
			</div>

			<?php
			if ( 'fullwidth' !== $blog_layout ) {
				get_sidebar();
			}
			?>
		</div>
	</div>
</main>

<?php
get_footer();

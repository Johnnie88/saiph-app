<?php

$taxonomy = get_taxonomy( get_queried_object()->taxonomy );

get_header(); ?>

<section class="page-header company-taxonomy-header">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center">
				<h1 class="title">
					<?php
					echo $taxonomy ? esc_attr( $taxonomy->labels->singular_name ) . ': ' : '';
					single_term_title();
					?>
				</h1>
			</div>
		</div>
	</div>
</section>

<main class="ptb80">
	<div class="container">
		<?php echo do_shortcode( '[companies show_filters="false" categories=' . get_query_var( 'company_category' ) . ']' ); ?>        
	</div>
</main>

<?php
get_footer();

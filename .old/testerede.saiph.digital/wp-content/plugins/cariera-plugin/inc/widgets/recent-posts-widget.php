<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Widget_Recent_Posts extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_ops = [
			'classname'   => 'widget_recent_entries clearfix',
			'description' => esc_html__( 'Your site&#8217;s most recent Posts.', 'cariera' ),
		];

		parent::__construct( 'recent-posts', esc_html__( 'Recent Posts', 'cariera' ), $widget_ops, 'cariera' );

		add_action( 'save_post', [ $this, 'flush_widget_cache' ] );
		add_action( 'deleted_post', [ $this, 'flush_widget_cache' ] );
		add_action( 'switch_theme', [ $this, 'flush_widget_cache' ] );
	}

	/**
	 * Front-End Display of the Widget
	 *
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {

		$cache = [];

		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'widget_recent_posts', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = [];
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}
		ob_start();

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts', 'cariera' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 3;
		if ( ! $number ) {
			$number = 3;
		}

		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		/* Filters the arguments for the Recent Posts widget. */
		$r = new WP_Query(
			apply_filters(
				'widget_posts_args',
				[
					'posts_per_page'      => $number,
					'no_found_rows'       => true,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
				]
			)
		);

		if ( $r->have_posts() ) :
			?>
			<?php echo $args['before_widget']; ?>
			<?php
			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}
			?>

			<?php
			while ( $r->have_posts() ) :
				$r->the_post();
				?>
			<div class="widget-blog-post">

				<div class="post-thumbnail">
					<a href="<?php the_permalink(); ?>">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail();
						} else {
							echo '<img src="' . get_template_directory_uri() . '/assets/images/default-thumbnail.jpg" class="post-no-thumb" />';
						}
						?>
					</a>
				</div>

				<div class="post-info">
					<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
					<?php if ( $show_date ) { ?>
						<span class="post-date"><?php echo get_the_date(); ?></span>
					<?php } ?>
				</div>

			</div>
		<?php endwhile; ?>

			<?php echo $args['after_widget']; ?>
			<?php
			// Reset the global $the_post as this query will have stomped on it.
			wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'widget_recent_posts', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

	/**
	 * Handles updating the settings for the widget instance.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = sanitize_text_field( $new_instance['title'] );
		$instance['number']    = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recent_entries'] ) ) {
			delete_option( 'widget_recent_entries' );
		}

		return $instance;
	}

	/**
	 * Flush widget cache
	 */
	public function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_posts', 'widget' );
	}

	/**
	 * Back-End display of the Widget
	 *
	 * @param array $instance
	 * @return void
	 *
	 * @since 1.4.0
	 */
	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 3;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		?>

		<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'cariera' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'cariera' ); ?></label>
		<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $number ); ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $show_date ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" />
		<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php esc_html_e( 'Display post date?', 'cariera' ); ?></label></p>

		<?php
	}
}

register_widget( 'Cariera_Widget_Recent_Posts' );

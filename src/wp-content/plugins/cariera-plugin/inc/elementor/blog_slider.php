<?php
/**
 * ELEMENTOR WIDGET - BLOG SLIDER
 *
 * @since    1.4.5
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Blog_Slider extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'blog_slider';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Blog Post Slider', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-post-slider';
	}

	/**
	 * Get widget's categories.
	 */
	public function get_categories() {
		return [ 'cariera-elements' ];
	}

	/**
	 * Register the controls for the widget
	 */
	protected function register_controls() {

		// POST LAYOUT SECTION.
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Post Layout', 'cariera' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'post_layout',
			[
				'label'   => esc_html__( 'Blog Post Layout', 'cariera' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'layout1' => esc_html__( 'Layout 1', 'cariera' ),
					'layout2' => esc_html__( 'Layout 2', 'cariera' ),
					'layout3' => esc_html__( 'Layout 3', 'cariera' ),
				],
				'default' => 'layout1',
			]
		);
		$this->add_control(
			'show_thumb',
			[
				'label'        => esc_html__( 'Show Post Thumbnail', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
				'condition'    => [
					'post_layout' => 'layout1',
				],
			]
		);
		$this->add_control(
			'show_avatar',
			[
				'label'        => esc_html__( 'Show Author Avatar', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
				'condition'    => [
					'post_layout' => 'layout1',
					'show_thumb'  => 'show',
				],
			]
		);
		$this->add_control(
			'show_date',
			[
				'label'        => esc_html__( 'Show Post Date', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
			]
		);
		$this->add_control(
			'show_cats',
			[
				'label'        => esc_html__( 'Show Categories', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'yes',
				'condition'    => [
					'post_layout' => 'layout3',
				],
			]
		);

		$this->end_controls_section();

		// POST QUERY SECTION.
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Post Query', 'cariera' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'cat_ids',
			[
				'label'       => esc_html__( 'Post Category IDs to include', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter post category ids to include, separated by a comma. Leave empty to get posts from all categories.', 'cariera' ),
			]
		);
		$this->add_control(
			'ids',
			[
				'label'       => esc_html__( 'Enter Post IDs', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter Post ids to show, separated by a comma. Leave empty to show all.', 'cariera' ),
			]
		);
		$this->add_control(
			'ids_not',
			[
				'label'       => esc_html__( 'Or Post IDs to Exclude', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Enter post ids to exclude, separated by a comma (,). Use if the field above is empty.', 'cariera' ),

			]
		);
		$this->add_control(
			'order_by',
			[
				'label'       => esc_html__( 'Order by', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'date'          => esc_html__( 'Date', 'cariera' ),
					'ID'            => esc_html__( 'ID', 'cariera' ),
					'author'        => esc_html__( 'Author', 'cariera' ),
					'title'         => esc_html__( 'Title', 'cariera' ),
					'modified'      => esc_html__( 'Modified', 'cariera' ),
					'rand'          => esc_html__( 'Random', 'cariera' ),
					'comment_count' => esc_html__( 'Comment Count', 'cariera' ),
					'menu_order'    => esc_html__( 'Menu Order', 'cariera' ),
					'post__in'      => esc_html__( 'ID order given (post__in)', 'cariera' ),
				],
				'default'     => 'date',
				'separator'   => 'before',
				'description' => esc_html__( 'Select how to sort retrieved posts. More at ', 'cariera' ) . '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex</a>.',
			]
		);
		$this->add_control(
			'order',
			[
				'label'       => esc_html__( 'Sort Order', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'ASC'  => esc_html__( 'Ascending', 'cariera' ),
					'DESC' => esc_html__( 'Descending', 'cariera' ),
				],
				'default'     => 'DESC',
				'separator'   => 'before',
				'description' => esc_html__( 'Select Ascending or Descending order. More at', 'cariera' ) . '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex</a>.',
			]
		);
		$this->add_control(
			'posts_per_page',
			[
				'label'       => esc_html__( 'Posts to show', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '6',
				'description' => esc_html__( 'Number of posts to show (-1 for all).', 'cariera' ),
			]
		);

		$this->end_controls_section();
		// END OF THE FIRST SECTION.
	}

	/**
	 * Get Style Dependency
	 *
	 * @since 1.7.0
	 */
	public function get_style_depends() {
		return [ 'cariera-blog-element' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		wp_enqueue_style( 'cariera-blog-element' );

		$settings = $this->get_settings();

		if ( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		// ARGS for retrieving the Posts.
		if ( ! empty( $settings['ids'] ) ) {
			$ids       = explode( ',', $settings['ids'] );
			$post_args = [
				'post_type'      => 'post',
				'paged'          => $paged,
				'posts_per_page' => $settings['posts_per_page'],
				'post__in'       => $ids,
				'orderby'        => $settings['order_by'],
				'order'          => $settings['order'],
				'post_status'    => 'publish',
			];
		} elseif ( ! empty( $settings['ids_not'] ) ) {
			$ids_not   = explode( ',', $settings['ids_not'] );
			$post_args = [
				'post_type'      => 'post',
				'paged'          => $paged,
				'posts_per_page' => $settings['posts_per_page'],
				'post__not_in'   => $ids_not,
				'orderby'        => $settings['order_by'],
				'order'          => $settings['order'],
				'post_status'    => 'publish',
			];
		} else {
			$post_args = [
				'post_type'      => 'post',
				'paged'          => $paged,
				'posts_per_page' => $settings['posts_per_page'],
				'orderby'        => $settings['order_by'],
				'order'          => $settings['order'],
				'post_status'    => 'publish',
			];
		}

		// Post ARGS if category id is selected.
		if ( ! empty( $settings['cat_ids'] ) ) {
			$post_args['cat'] = $settings['cat_ids'];
		} ?>

		<!-- START OF THE POSTS -->
		<div class="blog-post-slider">
			<?php
			$posts_query = new \WP_Query( $post_args );
			if ( $posts_query->have_posts() ) {
				while ( $posts_query->have_posts() ) :
					$posts_query->the_post();

					// BLOG POST LAYOUT 1.
					if ( $settings['post_layout'] === 'layout1' ) {
						?>

					<div class="blog-post-layout shadow-hover" id="post-<?php the_ID(); ?>">
							<?php
							if ( $settings['show_thumb'] === 'show' ) {
								$blog_thumbnail = get_the_post_thumbnail_url();
								if ( empty( $blog_thumbnail ) ) {
									$blog_thumbnail = get_template_directory_uri() . '/assets/images/default-thumbnail.jpg';
								} else {
									$blog_thumbnail = get_the_post_thumbnail_url();
								}
								?>

							<a href="<?php echo esc_url( get_permalink() ); ?>" class="bloglist-thumb-link hover-link">
								<div class="bloglist-post-thumbnail" style="background: url(<?php echo esc_attr( $blog_thumbnail ); ?>)"></div>
							</a>
							<?php } ?>

						<div class="bloglist-text-wrapper">
							<?php
							if ( $settings['show_thumb'] === 'show' && $settings['show_avatar'] === 'show' ) {
								?>
							<span class="bloglist-avatar"><?php echo get_avatar( get_the_author_meta( 'user_email' ), $size = '50' ); ?></span>
							<?php } ?>

							<h4 class="bloglist-title">
								<a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
							</h4>


							<?php if ( $settings['show_date'] == 'show' ) { ?>
								<div class="bloglist-meta">
									<i class="far fa-calendar-alt"></i> <?php echo get_the_time( get_option( 'date_format' ) ); ?>
								</div>
							<?php } ?>

							<div class="bloglist-excerpt">
								<p><?php echo cariera_string_limit_words( get_the_excerpt(), '23' ); ?>...</p>
								<a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-main btn-effect"><?php esc_html_e( 'read more', 'cariera' ); ?></a>
							</div>
						</div>
					</div>

						<?php
					}

					// BLOG POST LAYOUT 2.
					elseif ( $settings['post_layout'] === 'layout2' ) {
						?>
					<div class="blog-post-layout2" id="post-<?php the_ID(); ?>">
						<?php
						$blog_thumbnail = get_the_post_thumbnail_url();
						if ( empty( $blog_thumbnail ) ) {
							$blog_thumbnail = get_template_directory_uri() . '/assets/images/default-thumbnail.jpg';
						} else {
							$blog_thumbnail = get_the_post_thumbnail_url();
						}
						?>

						<div class="bloglist-post-thumbnail" style="background: url(<?php echo esc_attr( $blog_thumbnail ); ?>)"></div>

						<div class="bloglist-text-wrapper">
							<?php
							if ( has_category() ) {
								$post_cat = get_the_category();
								?>
								<span class="post-category"><a href="<?php echo esc_url( get_category_link( get_cat_id( $post_cat[0]->name ) ) ); ?>"><?php echo esc_html( $post_cat[0]->name ); ?></a></span>
							<?php } ?>

							<h4 class="bloglist-title">
								<a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
							</h4>

								<?php if ( $settings['show_date'] === 'show' ) { ?>
								<div class="bloglist-meta">
									<i class="far fa-calendar-alt"></i> <?php echo esc_html( get_the_time( get_option( 'date_format' ) ) ); ?>
								</div>
							<?php } ?>

							<div class="bloglist-excerpt">
								<p><?php echo cariera_string_limit_words( get_the_excerpt(), '23' ); ?>...</p>
								<a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-main btn-effect"><?php esc_html_e( 'read more', 'cariera' ); ?></a>
							</div>
						</div>
					</div>
						<?php
					}

					// BLOG POST LAYOUT 3.
					elseif ( $settings['post_layout'] === 'layout3' ) {
						?>
					<a href="<?php the_permalink(); ?>" class="blog-post-layout3" id="post-<?php the_ID(); ?>">
						<div class="blog-grid-item">
							<?php
							if ( ! post_password_required() ) {
								if ( has_post_thumbnail() ) {
									the_post_thumbnail();
								}
							}

							if ( $settings['show_cats'] === 'yes' ) {
								if ( has_category() ) {
									?>
								<span class="item-cat">
										<?php
										$post_cat = get_the_category();
										echo esc_html( $post_cat[0]->name );
										?>
								</span>
									<?php
								}
							}
							?>

							<div class="blog-grid-item-content">
								<?php if ( $settings['show_date'] === 'show' ) { ?>
									<ul class="post-meta">
										<li><?php the_date(); ?></li>
									</ul>
								<?php } ?>

								<h3 class="title"><?php the_title(); ?></h3>
							</div>
						</div>
					</a>
						<?php
					}

				endwhile;
			}
			?>
		</div>

		<?php
		wp_reset_postdata();
	}
}

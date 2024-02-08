<?php
/**
 * ELEMENTOR WIDGET - COMPANY SLIDER
 *
 * @since    1.4.0
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Company_Slider extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'company_slider';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Company Slider', 'cariera' );
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

		// SECTION.
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'cariera' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'version',
			[
				'label'       => esc_html__( 'Layout Version', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'1' => esc_html__( 'Version 1', 'cariera' ),
					'2' => esc_html__( 'Version 2', 'cariera' ),
				],
				'default'     => '1',
				'description' => '',
			]
		);
		$this->add_control(
			'per_page',
			[
				'label'       => esc_html__( 'Total Companies', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Leave it blank to display all companies.', 'cariera' ),
			]
		);
		$this->add_control(
			'columns',
			[
				'label'       => esc_html__( 'Visible Companies', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '1',
				'min'         => '1',
				'max'         => '10',
				'description' => esc_html__( 'This will change how many jobs will be visible per slide.', 'cariera' ),
			]
		);
		$this->add_control(
			'autoplay',
			[
				'label'        => esc_html__( 'Autoplay', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera' ),
				'label_off'    => esc_html__( 'Disable', 'cariera' ),
				'return_value' => 'enable',
				'default'      => '',
			]
		);
		$this->add_control(
			'orderby',
			[
				'label'       => esc_html__( 'Order by', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'featured' => esc_html__( 'Featured', 'cariera' ),
					'date'     => esc_html__( 'Date', 'cariera' ),
					'ID'       => esc_html__( 'ID', 'cariera' ),
					'author'   => esc_html__( 'Author', 'cariera' ),
					'title'    => esc_html__( 'Title', 'cariera' ),
					'modified' => esc_html__( 'Modified', 'cariera' ),
					'rand'     => esc_html__( 'Random', 'cariera' ),
				],
				'default'     => 'featured',
				'description' => '',
			]
		);
		$this->add_control(
			'order',
			[
				'label'       => esc_html__( 'Order', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'DESC' => esc_html__( 'Descending', 'cariera' ),
					'ASC'  => esc_html__( 'Ascending', 'cariera' ),
				],
				'default'     => 'DESC',
				'description' => '',
			]
		);
		$this->add_control(
			'custom_class',
			[
				'label'       => esc_html__( 'Custom Class', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Style Dependency
	 *
	 * @since 1.7.0
	 */
	public function get_style_depends() {
		return [ 'cariera-company-listings' ];
	}

	/**
	 * Widget output
	 */
	protected function render() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		wp_enqueue_style( 'cariera-company-listings' );

		$settings = $this->get_settings();
		global $post;

		$companies = cariera_get_companies(
			[
				'orderby'        => $settings['orderby'],
				'order'          => $settings['order'],
				'posts_per_page' => $settings['per_page'],
			]
		);

		if ( 'enable' === $settings['autoplay'] ) {
			$autoplay = '1';
		} else {
			$autoplay = '0';
		}

		if ( $companies->have_posts() ) { ?>

			<div class="company-carousel company-carousel-<?php echo esc_attr( $settings['version'] ) . ' ' . esc_attr( $settings['custom_class'] ); ?>" data-columns="<?php echo esc_attr( $settings['columns'] ); ?>" data-autoplay="<?php echo esc_attr( $autoplay ); ?>">

				<?php
				while ( $companies->have_posts() ) :
					$companies->the_post();
					?>
					<div class="single-company">

						<a href="<?php cariera_the_company_permalink(); ?>" class="company-link" aria-label="<?php the_title(); ?>">
							<!-- Company Logo -->
							<?php if ( '1' === $settings['version'] ) { ?>
								<div class="company-logo-wrapper">
								<?php
							} else {
								$image = get_post_meta( $post->ID, '_company_header_image', true );
								?>
								<div class="company-logo-wrapper" style="background-image: url(<?php echo esc_attr( $image ); ?>);">
							<?php } ?>

								<div class="company-logo">
									<?php cariera_the_company_logo(); ?>
								</div>
							</div>

							<!-- Company Details -->
							<div class="company-details">
								<div class="company-title">
									<h5><?php the_title(); ?></h5>
								</div>

								<?php if ( ! empty( cariera_get_the_company_location() ) ) { ?>
									<div class="company-location">
										<span><i class="icon-location-pin"></i><?php echo cariera_get_the_company_location(); ?></span>
									</div>
								<?php } ?>

								<div class="company-jobs">
									<span>
										<?php echo apply_filters( 'cariera_company_open_positions_info', esc_html( sprintf( _n( '%s Job', '%s Jobs', cariera_get_the_company_job_listing_active_count( $post->ID ), 'cariera' ), cariera_get_the_company_job_listing_active_count( $post->ID ) ) ) ); ?>
									</span>
								</div>
							</div>
						</a>
					</div>  

				<?php endwhile; ?>
			</div>
			<?php
		}

		wp_reset_postdata();
	}
}

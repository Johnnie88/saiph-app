<?php
/**
 * ELEMENTOR WIDGET - JOB BOARD
 *
 * @since    1.4.0
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Board extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_board';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Job Board', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-posts-justified';
	}

	/**
	 * Get widget's categories.
	 */
	public function get_categories() {
		return [ 'cariera-elements' ];
	}

	/**
	 * Get job categories. Retrieve the list of categories that the Jobs widget should fetch by default.
	 */
	public function get_wpjm_job_categories() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		$wpjm_categories_options = [];
		$wpjm_categories         = [];

		if ( ! get_option( 'job_manager_enable_categories' ) ) {
			return $wpjm_categories;
		}

		$terms = get_terms(
			[
				'taxonomy'   => 'job_listing_category',
				'hide_empty' => false,
			]
		);

		foreach ( $terms as $term ) {
			$wpjm_categories_options[] = [ $term->slug => $term->name ];
		}

		foreach ( $wpjm_categories_options as $value ) {
			$wpjm_categories += $value;
		}

		return $wpjm_categories;
	}

	/**
	 * Get job categories. Retrieve the list of categories that the Jobs widget should fetch by default.
	 */
	public function get_wpjm_job_types() {
		if ( ! class_exists( 'WP_Job_Manager' ) ) {
			return;
		}

		$wpjm_jobtype_options = [];
		$wpjm_types           = [];

		if ( ! get_option( 'job_manager_enable_types' ) ) {
			return $wpjm_types;
		}

		$terms = get_terms(
			[
				'taxonomy'   => 'job_listing_type',
				'hide_empty' => false,
			]
		);

		foreach ( $terms as $term ) {
			$wpjm_jobtype_options[] = [ $term->slug => $term->name ];
		}

		foreach ( $wpjm_jobtype_options as $value ) {
			$wpjm_types += $value;
		}

		return $wpjm_types;
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
			'layout',
			[
				'label'       => esc_html__( 'Job Layout', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'list' => esc_html__( 'List', 'cariera' ),
					'grid' => esc_html__( 'Grid', 'cariera' ),
				],
				'default'     => 'list',
				'description' => esc_html__( 'Choose the layout style for your jobs.', 'cariera' ),
			]
		);
		$this->add_control(
			'list_layout',
			[
				'label'       => esc_html__( 'Job List Styles', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'1' => esc_html__( 'Version 1', 'cariera' ),
					'2' => esc_html__( 'Version 2', 'cariera' ),
					'3' => esc_html__( 'Version 3', 'cariera' ),
					'4' => esc_html__( 'Version 4', 'cariera' ),
					'5' => esc_html__( 'Version 5', 'cariera' ),
				],
				'default'     => '1',
				'description' => '',
				'condition'   => [
					'layout' => 'list',
				],
			]
		);
		$this->add_control(
			'grid_layout',
			[
				'label'       => esc_html__( 'Job Grid Styles', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'1' => esc_html__( 'Version 1', 'cariera' ),
					'2' => esc_html__( 'Version 2', 'cariera' ),
					'3' => esc_html__( 'Version 3', 'cariera' ),
					'4' => esc_html__( 'Version 4', 'cariera' ),
				],
				'default'     => '1',
				'description' => '',
				'condition'   => [
					'layout' => 'grid',
				],
			]
		);
		$this->add_control(
			'per_page',
			[
				'label'       => esc_html__( 'Items per Page', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => '10',
				'description' => esc_html__( 'How many items to show in the job board.', 'cariera' ),
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
			'filters',
			[
				'label'        => esc_html__( 'Show Filters', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'cariera' ),
				'label_off'    => esc_html__( 'Hide', 'cariera' ),
				'return_value' => 'show',
				'default'      => 'show',
				'description'  => '',
			]
		);
		$this->add_control(
			'sidebar_search',
			[
				'label'        => esc_html__( 'Sidebar Search', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'cariera' ),
				'label_off'    => esc_html__( 'Off', 'cariera' ),
				'return_value' => 'enable',
				'default'      => '',
				'description'  => esc_html__( 'Make sure to activate this option if the "show filters" is deactivated and you are using a sidebar search.', 'cariera' ),
			]
		);
		$this->add_control(
			'hide_pagination',
			[
				'label'        => esc_html__( 'Hide Pagination', 'cariera' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Hide', 'cariera' ),
				'label_off'    => esc_html__( 'Show', 'cariera' ),
				'return_value' => 'true',
				'default'      => '',
				'description'  => '',
				'selectors'    => [
					'{{WRAPPER}} .job_listings nav.job-manager-pagination, {{WRAPPER}} .job_listings .load_more_jobs'    => 'display: none !important',
				],
			]
		);
		$this->add_control(
			'pagination',
			[
				'label'       => esc_html__( 'Pagination Style', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'false' => esc_html__( 'Load More', 'cariera' ),
					'true'  => esc_html__( 'Numeric', 'cariera' ),
				],
				'default'     => 'false',
				'description' => '',
				'condition'   => [
					'hide_pagination' => '',
				],
			]
		);
		$this->add_control(
			'featured',
			[
				'label'       => esc_html__( 'Featured', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'default' => esc_html__( 'Default', 'cariera' ),
					'show'    => esc_html__( 'Show', 'cariera' ),
					'hide'    => esc_html__( 'Hide', 'cariera' ),
				],
				'default'     => 'default',
				'description' => esc_html__( 'Set to "Show" to show only featured jobs, "Hide" to hide the featured jobs, or default show both (featured first).', 'cariera' ),
			]
		);
		$this->add_control(
			'filled',
			[
				'label'       => esc_html__( 'Filled', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'default' => esc_html__( 'Default', 'cariera' ),
					'show'    => esc_html__( 'Show', 'cariera' ),
					'hide'    => esc_html__( 'Hide', 'cariera' ),
				],
				'default'     => 'default',
				'description' => esc_html__( 'Set to "Show" to show only filled jobs, "Hide" to hide the filled jobs, or default show everything.', 'cariera' ),
			]
		);
		$this->add_control(
			'keyword',
			[
				'label'       => esc_html__( 'Keyword', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Enter a default keyword to search', 'cariera' ),
			]
		);
		$this->add_control(
			'location',
			[
				'label'       => esc_html__( 'Location', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'description' => esc_html__( 'Enter default location to search', 'cariera' ),
			]
		);
		$this->add_control(
			'remote_position',
			[
				'label'       => esc_html__( 'Remote Position', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'default' => esc_html__( 'Default', 'cariera' ),
					'show'    => esc_html__( 'Show', 'cariera' ),
					'hide'    => esc_html__( 'Hide', 'cariera' ),
				],
				'default'     => 'default',
				'description' => esc_html__( 'Set to "Show" to show only remote jobs, "Hide" to hide the filled jobs, or default show everything.', 'cariera' ),
			]
		);
		$this->add_control(
			'categories',
			[
				'label'       => esc_html__( 'Categories', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => [],
				'multiple'    => true,
				'options'     => self::get_wpjm_job_categories(),
				'description' => esc_html__( 'Limit the jobs to certain categories', 'cariera' ),
			]
		);
		$this->add_control(
			'job_types',
			[
				'label'       => esc_html__( 'Job Types', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => [],
				'multiple'    => true,
				'options'     => self::get_wpjm_job_types(),
				'description' => esc_html__( 'Limit the jobs to certain job types', 'cariera' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();
		$attrs    = '';

		if ( $settings['layout'] === 'list' ) {
			$layout = '';
			if ( $settings['list_layout'] !== '1' ) {
				$layout_ver = 'jobs_list_version="' . $settings['list_layout'] . '"';
			} else {
				$layout_ver = '';
			}
		}

		if ( $settings['layout'] === 'grid' ) {
			$layout = 'jobs_layout="grid"';
			if ( $settings['grid_layout'] !== '1' ) {
				$layout_ver = 'jobs_grid_version="' . $settings['grid_layout'] . '"';
			} else {
				$layout_ver = '';
			}
		}

		if ( ! empty( $settings['per_page'] ) ) {
			$per_page = 'per_page="' . $settings['per_page'] . '"';
		}

		if ( ! empty( $settings['orderby'] ) ) {
			$orderby = 'orderby="' . $settings['orderby'] . '"';
		}

		if ( ! empty( $settings['order'] ) ) {
			$order = 'order="' . $settings['order'] . '"';
		}

		if ( $settings['filters'] !== 'show' ) {
			$show_filters = 'show_filters="false"';
		} else {
			$show_filters = 'show_filters="true"';
		}

		if ( $settings['sidebar_search'] === 'enable' ) {
			$sidebar_search = 'sidebar_search="true"';
		} else {
			$sidebar_search = '';
		}

		if ( ! empty( $settings['pagination'] ) ) {
			$pagination = 'show_pagination="' . $settings['pagination'] . '"';
		}

		if ( $settings['featured'] === 'default' ) {
			$featured = '';
		} elseif ( $settings['featured'] === 'show' ) {
			$featured = 'featured="true"';
		} else {
			$featured = 'featured="false"';
		}

		if ( $settings['filled'] === 'default' ) {
			$filled = '';
		} elseif ( $settings['filled'] === 'show' ) {
			$filled = 'filled="true"';
		} else {
			$filled = 'filled="false"';
		}

		if ( $settings['remote_position'] === 'default' ) {
			$remote_position = '';
		} elseif ( $settings['remote_position'] === 'show' ) {
			$remote_position = 'remote_position="true"';
		} else {
			$remote_position = 'remote_position="false"';
		}

		if ( ! empty( $settings['keyword'] ) ) {
			$keyword = 'keywords="' . $settings['keyword'] . '"';
		} else {
			$keyword = '';
		}

		if ( ! empty( $settings['location'] ) ) {
			$location = 'location="' . $settings['location'] . '"';
		} else {
			$location = '';
		}

		if ( ! empty( $settings['categories'] ) ) {
			$selected_category = '';
			foreach ( $settings['categories'] as $category ) {
				if ( empty( $category ) ) {
					continue;
				}
				$selected_category .= $category . ', ';
			}
			$categories = 'categories="' . $selected_category . '"';
		} else {
			$categories = '';
		}

		if ( ! empty( $settings['job_types'] ) ) {
			$selected_job_type = '';
			foreach ( $settings['job_types'] as $job_type ) {
				if ( empty( $job_type ) ) {
					continue;
				}
				$selected_job_type .= $job_type . ', ';
			}
			$job_types = 'job_types="' . $selected_job_type . '"';
		} else {
			$job_types = '';
		}

		$job_attr = [ $layout, $layout_ver, $per_page, $orderby, $order, $show_filters, $sidebar_search, $pagination, $featured, $filled, $remote_position, $keyword, $location, $categories, $job_types ];

		$output = '[jobs ' . join( ' ', $job_attr ) . ']';

		echo do_shortcode( $output );
	}
}

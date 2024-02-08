<?php
/**
 * ELEMENTOR WIDGET - JOB RESUME TAB SEARCH
 *
 * @since    1.4.0
 * @version  1.7.2
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Job_Resume_Search extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'job_resume_search';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Job & Resume Tab Search', 'cariera' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-search';
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
			'layout',
			[
				'label'       => esc_html__( 'Layout Color', 'cariera' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'light-skin' => esc_html__( 'Light', 'cariera' ),
					'dark-skin'  => esc_html__( 'Dark', 'cariera' ),
				],
				'default'     => 'light-skin',
				'description' => '',
			]
		);
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

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();
		$attrs    = '';

		if ( 'light' === $settings['layout'] ) {
			$layout = 'light-skin';
		} else {
			$layout = 'dark-skin';
		}
		?>


		<div class="job-resume-tab-search version-<?php echo esc_attr( $settings['version'] ) . ' ' . esc_attr( $settings['layout'] ); ?>">
			<ul class="tabs-nav job-resume-search">
				<li class="active">
					<a href="#search-form-tab-jobs">
						<i class="icon-briefcase"></i><?php esc_html_e( 'Jobs', 'cariera' ); ?>
					</a>
				</li>

				<?php if ( class_exists( 'WP_Resume_Manager' ) ) { ?>
					<li>
						<a href="#search-form-tab-resumes">
							<i class="icon-graduation"></i><?php esc_html_e( 'Resumes', 'cariera' ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>


			<div class="tab-container">
				<div class="tab-content" id="search-form-tab-jobs" style="display: none;">
					<?php get_job_manager_template( 'job-resume-search-job-form.php' ); ?>
				</div>

				<?php if ( class_exists( 'WP_Resume_Manager' ) ) { ?>
					<div class="tab-content" id="search-form-tab-resumes" style="display: none;">
						<?php get_job_manager_template( 'job-resume-search-resume-form.php' ); ?>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php
	}
}

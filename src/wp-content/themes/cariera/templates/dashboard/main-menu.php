<?php
/**
 * Main menu of the dashboard menu.
 *
 * This template can be overridden by copying it to cariera-child/templates/dashboard/main-menu.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.0
 * @version     1.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$user = wp_get_current_user();

// Pages for the Dashboard Main Menu.
$dashboard_page      = apply_filters( 'cariera_dashboard_main_dashboard_page', get_option( 'cariera_dashboard_page' ) );
$employer_dashboard  = apply_filters( 'cariera_dashboard_employer_dashboard_page', get_option( 'job_manager_job_dashboard_page_id' ) );
$company_dashboard   = apply_filters( 'cariera_dashboard_company_dashboard_page', get_option( 'cariera_company_dashboard_page' ) );
$candidate_dashboard = apply_filters( 'cariera_dashboard_candidate_dashboard_page', get_option( 'resume_manager_candidate_dashboard_page_id' ) );
$job_alerts          = apply_filters( 'cariera_dashboard_job_alerts_page', get_option( 'job_manager_alerts_page_id' ) );
$resume_alerts       = apply_filters( 'cariera_dashboard_resume_alerts_page', get_option( 'job_manager_resume_alerts_page_id' ) );
$bookmarks           = apply_filters( 'cariera_dashboard_bookmarks_page', get_option( 'cariera_bookmarks_page' ) );
$applied_jobs        = apply_filters( 'cariera_dashboard_past_applications_page', get_option( 'cariera_past_applications_page' ) );
$listing_reports     = apply_filters( 'cariera_dashboard_listing_reports_page', get_option( 'cariera_listing_reports_page' ) );
$user_packages       = apply_filters( 'cariera_dashboard_user_packages_page', get_option( 'cariera_user_packages_page' ) );

if ( \Cariera\wc_is_activated() ) {
	$orders = wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) );
}
?>

<ul class="dashboard-nav-main" data-submenu-title="<?php esc_html_e( 'Main', 'cariera' ); ?>">    
	<?php if ( cariera_get_option( 'cariera_dashboard_page_enable' ) ) { ?>
		<li class="dashboard-menu-item_dashboard <?php echo $post->ID == $dashboard_page ? esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo esc_url( get_permalink( $dashboard_page ) ); ?>">
				<i class="icon-settings"></i><?php esc_html_e( 'Dashboard', 'cariera' ); ?>
			</a>
		</li>
	<?php } ?>

	<?php
	// Employer Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() ) {
		if ( in_array( 'employer', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_jobs <?php echo $post->ID == $employer_dashboard ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $employer_dashboard ) ); ?>">
					<i class="icon-briefcase"></i><?php esc_html_e( 'My Jobs', 'cariera' ); ?>
				</a>
			</li>
			<?php
		}
	}

	// Company Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() && \Cariera\company_manager_is_activated() ) {
		if ( in_array( 'employer', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_companies <?php echo $post->ID == $company_dashboard ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $company_dashboard ) ); ?>">
					<i class="far fa-building"></i><?php esc_html_e( 'My Companies', 'cariera' ); ?>
				</a>
			</li>
			<?php
		}
	}

	// Candidate Dashboard Link.
	if ( \Cariera\wp_job_manager_is_activated() && \Cariera\wp_resume_manager_is_activated() ) {
		if ( in_array( 'candidate', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_resumes <?php echo $post->ID == $candidate_dashboard ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $candidate_dashboard ) ); ?>">
					<i class="icon-layers"></i><?php esc_html_e( 'My Resumes', 'cariera' ); ?>
				</a>
			</li>
			<?php
		}
	}

	// Job Alerts Link.
	if ( \Cariera\wp_job_manager_is_activated() && class_exists( 'WP_Job_Manager_Alerts' ) ) {
		if ( in_array( 'candidate', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			if ( cariera_get_option( 'cariera_dashboard_job_alerts_page_enable' ) ) {
				?>
				<li class="dashboard-menu-item_job-alerts <?php echo $post->ID == $job_alerts ? esc_attr( 'active' ) : ''; ?>">
					<a href="<?php echo esc_url( get_permalink( $job_alerts ) ); ?>">
						<i class="icon-bell"></i><?php esc_html_e( 'Job Alerts', 'cariera' ); ?>
					</a>
				</li>
				<?php
			}
		}
	}

	// Resume Alerts Link.
	if ( \Cariera\wp_job_manager_is_activated() && class_exists( 'WP_Job_Manager_Resume_Alerts' ) ) {
		if ( in_array( 'employer', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			?>
			<li class="dashboard-menu-item_resume-alerts <?php echo $post->ID == $resume_alerts ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $resume_alerts ) ); ?>">
					<i class="icon-bell"></i><?php esc_html_e( 'Resume Alerts', 'cariera' ); ?>
				</a>
			</li>
			<?php
		}
	}

	// Bookmarks Link.
	if ( \Cariera\wp_job_manager_is_activated() && class_exists( 'WP_Job_Manager_Bookmarks' ) ) {
		if ( cariera_get_option( 'cariera_dashboard_bookmark_page_enable' ) ) {
			?>
			<li class="dashboard-menu-item_bookmarks <?php echo $post->ID == $bookmarks ? esc_attr( 'active' ) : ''; ?>">
				<a href="<?php echo esc_url( get_permalink( $bookmarks ) ); ?>">
					<i class="icon-heart"></i><?php esc_html_e( 'My Bookmarks', 'cariera' ); ?>
				</a>
			</li>
			<?php
		}
	}

	// Applied Jobs Link.
	if ( \Cariera\wp_job_manager_is_activated() && class_exists( 'WP_Job_Manager_Applications' ) ) {
		if ( in_array( 'candidate', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true ) ) {
			if ( cariera_get_option( 'cariera_dashboard_applied_jobs_page_enable' ) ) {
				?>
				<li class="dashboard-menu-item_applied-jobs <?php echo $post->ID == $applied_jobs ? esc_attr( 'active' ) : ''; ?>">
					<a href="<?php echo esc_url( get_permalink( $applied_jobs ) ); ?>">
						<i class="icon-pencil"></i><?php esc_html_e( 'Applied Jobs', 'cariera' ); ?>
					</a>
				</li>
				<?php
			}
		}
	}

	// Reports.
	if ( \Cariera\wp_job_manager_is_activated() && cariera_get_option( 'cariera_dashboard_listing_reports_page_enable' ) ) {
		?>
		<li class="dashboard-menu-item_listing-reports <?php echo $post->ID == $listing_reports ? esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo esc_url( get_permalink( $listing_reports ) ); ?>">
				<i class="icon-chart"></i><?php esc_html_e( 'Listing Reports', 'cariera' ); ?>
			</a>
		</li>
		<?php
	}

	// User Packages.
	if ( \Cariera\wp_job_manager_is_activated() && class_exists( 'WC_Paid_Listings' ) && cariera_get_option( 'cariera_dashboard_user_packages_page_enable' ) ) {
		?>
		<li class="dashboard-menu-item_user-packages <?php echo $post->ID == $user_packages ? esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo esc_url( get_permalink( $user_packages ) ); ?>">
				<i class="icon-social-dropbox"></i><?php esc_html_e( 'Packages', 'cariera' ); ?>
			</a>
		</li>
		<?php
	}

	// Orders Link.
	if ( \Cariera\wc_is_activated() && cariera_get_option( 'cariera_dashboard_orders_page_enable' ) ) {
		?>
		<li class="dashboard-menu-item_orders <?php echo is_wc_endpoint_url( 'orders' ) ? esc_attr( 'active' ) : ''; ?>">
			<a href="<?php echo esc_url( $orders ); ?>">
				<i class="icon-credit-card"></i><?php esc_html_e( 'Orders', 'cariera' ); ?>
			</a>
		</li>
	<?php } ?>

	<?php do_action( 'cariera_dashboard_main_nav_end' ); ?>
</ul>

<?php
// Extra Dashboard Menu for Employers.
if ( in_array( 'employer', (array) $user->roles, true ) ) {
	wp_nav_menu(
		[
			'theme_location' => 'employer-dash',
			'container'      => false,
			'menu_class'     => 'dashboard-nav-employer-extra',
			'walker'         => new Cariera_Mega_Menu_Walker(),
			'fallback_cb'    => '__return_false',
		]
	);
}

// Extra Dashboard Menu for Candidates.
if ( in_array( 'candidate', (array) $user->roles, true ) ) {
	wp_nav_menu(
		[
			'theme_location' => 'candidate-dash',
			'container'      => false,
			'menu_class'     => 'dashboard-nav-candidate-extra',
			'walker'         => new Cariera_Mega_Menu_Walker(),
			'fallback_cb'    => '__return_false',
		]
	);
}

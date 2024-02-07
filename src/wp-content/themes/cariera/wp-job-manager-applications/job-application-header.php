<?php
/**
 * Header shown below a job application.
 *
 * This template can be overridden by copying it to yourtheme/wp-job-manager-applications/job-application-header.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager-applications
 * @category    Template
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$resume_id         = get_job_application_resume_id( $application->ID );
$resume_share_link = $resume_id && function_exists( 'get_resume_share_link' ) ? get_resume_share_link( $resume_id ) : null;
$attachments       = get_job_application_attachments( $application->ID );
$email             = get_job_application_email( $application->ID );

echo get_job_application_avatar( $application->ID, 90 ) ?>

<h3>
	<?php if ( ( $resume_id = get_job_application_resume_id( $application->ID ) ) && 'publish' === get_post_status( $resume_id ) && function_exists( 'get_resume_share_link' ) && ( $share_link = get_resume_share_link( $resume_id ) ) ) : ?>
		<a href="<?php echo esc_attr( $share_link ); ?>"><?php echo esc_html( $application->post_title ); ?></a>
		<?php
	else :
		echo esc_html( $application->post_title );
	endif;
	?>
</h3>


<ul class="meta">
	<?php if ( $attachments ) : ?>
		<?php foreach ( $attachments as $attachment ) : ?>
			<li class="attachment"><a href="<?php echo esc_url( $attachment ); ?>" title="<?php echo esc_attr( get_job_application_attachment_name( $attachment ) ); ?>" class=" job-application-attachment"><i class="far fa-file-alt"></i><?php echo esc_html( get_job_application_attachment_name( $attachment, 15 ) ); ?></a></li>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( $email ) { ?>
		<?php
			// translators: Placeholder %s is the job title.
			$email_subject = sprintf( __( 'Your job application for %s', 'cariera' ), wp_strip_all_tags( get_the_title( $job_id ) ) );
			// translators: Placeholder %s is the applicant name.
			$email_body = sprintf( __( 'Hello %s', 'cariera' ), get_the_title( $application->ID ) );
		?>

		<li class="email"><a href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo esc_attr( $email_subject ); ?>&amp;body=<?php echo esc_attr( $email_body ); ?>" title="<?php esc_html_e( 'Email', 'cariera' ); ?>" class="job-application-contact"><i class="far fa-envelope"></i> <?php esc_html_e( 'Email', 'cariera' ); ?></a></li>
	<?php } ?>                                

	<?php
	if ( $resume_id && 'publish' === get_post_status( $resume_id ) && $resume_share_link ) {
		?>
		<li class="resume"><a href="<?php echo esc_url( $share_link ); ?>" target="_blank" class="job-application-resume">
		<i class="fas fa-download" aria-hidden="true"></i><?php esc_html_e( 'View Resume', 'cariera' ); ?></a></li>
	<?php } ?>
</ul>

<!-- Application actions -->
<ul class="actions">
	<?php do_action( 'job_application_actions_start' ); ?>
	<li class="edit">
		<a href="#" class="job-application-toggle-edit button"><i class="far fa-edit"></i><?php esc_html_e( 'Edit', 'cariera' ); ?></a>
	</li>
	<li class="notes <?php echo get_comments_number( $application->ID ) ? 'has-notes' : ''; ?>">
		<a href="#" class="job-application-toggle-notes button"><i class="fas fa-sticky-note"></i><?php esc_html_e( 'Notes', 'cariera' ); ?></a>
	</li>
	<li class="content">
		<a href="#" class="job-application-toggle-content button"><i class="fas fa-plus-circle"></i><?php esc_html_e( 'Details', 'cariera' ); ?></a>
	</li>
	<?php do_action( 'job_application_actions_end' ); ?>
</ul>

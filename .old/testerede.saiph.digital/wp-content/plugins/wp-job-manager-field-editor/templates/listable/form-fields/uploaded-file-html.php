<?php
/**
 * Shows info for an uploaded file on job listing forms.
 *
 * I had to include this to override Listable template file -- as they are incorrectly handling URLs in the original
 * template, causing them to not show on the edit listing page correctly (and causing them to be removed on save)
 *
 * This template can be overridden by copying it to yourtheme/job_manager/form-fields/uploaded-file-html.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 *              Myles McNamara
 * @package     WP Job Manager
 *              WP Job Manager Field Editor
 * @category    Template
 * @version     1.31.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="job-manager-uploaded-file">
	<?php
	// Because listable_get_attachment_id_from_url does not return the original value, we store it for use below
	$original_value = $value;
	// We have received a URL. We need to determine the attachment ID so we can keep things consistent.
	if ( ! is_numeric( $value ) ) {
		$value = listable_get_attachment_id_from_url( $value );
	}

	if ( is_numeric( $value ) ) {
		$thumbnail_src = wp_get_attachment_image_src( absint( $value ), 'full' );
		$thumbnail_src = $thumbnail_src ? $thumbnail_src[0] : $original_value;
		$image_src     = wp_get_attachment_image_src( absint( $value ), 'full' );
		$image_src     = $image_src ? $image_src[0] : $original_value;
	} else {
		$image_src = $thumbnail_src = $original_value;
	}

	$extension = ! empty( $extension ) ? $extension : substr( strrchr( $image_src, '.' ), 1 );
	if ( 'image' === wp_ext2type( $extension ) ) : ?>
		<span class="job-manager-uploaded-file-preview"><img src="<?php echo esc_url( $thumbnail_src ); ?>"/> <a class="job-manager-remove-uploaded-file"
																												 href="#">[<?php _e( 'remove', 'wp-job-manager', 'wp-job-manager-field-editor' ); ?>]</a></span>
	<?php else : ?>
		<span class="job-manager-uploaded-file-name"><code><?php echo esc_html( basename( $thumbnail_src ) ); ?></code> <a class="job-manager-remove-uploaded-file"
																														   href="#">[<?php _e( 'remove', 'wp-job-manager', 'wp-job-manager-field-editor' ); ?>]</a></span>
	<?php endif; ?>

	<input type="hidden" class="input-text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $image_src ); ?>"/>
</div>
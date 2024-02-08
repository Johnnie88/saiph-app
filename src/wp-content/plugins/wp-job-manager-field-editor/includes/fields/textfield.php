<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	$groupid = ! empty( $groupid ) ? $groupid : 'general';
?>
<input placeholder="<?php echo $settings['placeholder']; ?>" name="<?php echo $name; ?>" class="widefat <?php echo $groupid; ?>-group-input" type="text" ref="<?php echo $groupid; ?>" id="<?php echo $id; ?>" value="<?php echo htmlentities( $value ); ?>" <?php if ( in_array( $field, $required_fields ) ) echo 'required="required"'; ?>/>

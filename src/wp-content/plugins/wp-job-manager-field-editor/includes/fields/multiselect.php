<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_array( $settings['default'] ) ) {
	$settings['default'] = WP_Job_Manager_Field_Editor::get_instance()->options()->convert( $settings['default'] );
}

ob_start();
$structure     = explode( ',', $settings['default'] );
$defaultSelect = null;
$options       = array();

foreach ( $structure as & $option ) {
	if ( false !== strpos( $option, '||' ) ) {
		$parts                = explode( '||', $option );
		$options[ $parts[0] ] = $parts[1];
		if ( false !== stripos( $option, '*' ) ) {
			$defaultSelect = $parts[0];
		}
	} else {
		$options[ $option ] = ucwords( $option );
		if ( false !== stripos( $option, '*' ) ) {
			$defaultSelect = $option;
		}
	}
}

echo '<select multiple="multiple" data-multiple_text="' . $settings["placeholder"] .'" class="widefat" name="' . $name . '[]" id="' . $id . '" >';
if ( ! empty( $settings['placeholder'] ) ) {
	//echo ' <option value="none" disabled selected>' . $settings['placeholder'] . '</option>';
}
if ( $settings['default'] === $value && ! empty( $defaultSelect ) ) {
	$value = $defaultSelect;
} else if ( empty( $defaultSelect ) ) {
	?>


	<?php
}

$dropdownindex = 0;
$dropdownLength = count( $options );
$optgroup_open = false;

foreach ( $options as $dropdownValue => $dropdownLabel ) {
	$dropdownValue = str_replace( '*', '', $dropdownValue );

	if ( false !== strpos( $dropdownLabel, '---' ) ) {

		$dropdownLabel = str_replace( '---', '', $dropdownLabel );

		// If already have open optgroup, need to close it first
		if( $optgroup_open ){
			echo "</optgroup>";
		} else {
			// optgroup not open yet, set bool variable to set that we have opened an optgroup (so we can add closing tag)
			$optgroup_open = true;
		}

		echo "<optgroup label='{$dropdownLabel}'>";

	} else {
		?>
		<option <?php if ( $value == $dropdownValue ) { echo 'selected="selected"'; }; ?> value="<?php echo $dropdownValue; ?>"> <?php echo str_replace( '*', '', $dropdownLabel ); ?></option>
		<?php
	}

	$dropdownindex++;

	if( ( $dropdownindex ) >= $dropdownLength && $optgroup_open ){
		echo "</optgroup>";
	}
}
?>
	</select>

<?php ob_end_flush(); ?>
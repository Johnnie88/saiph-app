<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
?>
<div class="col-md-3 my-2">
    <?php Inputs::renderCheckBox( __( 'Show gift cards', 'bookly' ), null, get_option( 'bookly_cloud_gift_enabled' ), array( 'id' => 'bookly-show-gift-cards' ) ) ?>
</div>
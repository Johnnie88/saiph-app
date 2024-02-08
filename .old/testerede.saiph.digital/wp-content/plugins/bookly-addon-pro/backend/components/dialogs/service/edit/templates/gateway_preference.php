<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls;
/**
 * @var array $gateways
 * @var array $service
 */
$accepted_gateways = $service['gateways'] ? json_decode( $service['gateways'], true ) : null;
?>
<div class='form-group'>
    <?php Controls\Inputs::renderRadioGroup( __( 'Available payment methods', 'bookly' ), null,
        array(
            'default' => array( 'title' => __( 'Default', 'bookly' ) ),
            'custom' => array( 'title' => __( 'Custom', 'bookly' ) ),
        ),
        $accepted_gateways === null ? 'default' : 'custom', array( 'name' => 'gateways' ) ) ?>
</div>
<div class="form-group border-left ml-4 pl-3">
    <ul id="bookly-js-gateways-list"
        data-icon-class='fas fa-hand-holding-usd'
        data-txt-select-all="<?php esc_attr_e( 'All methods', 'bookly' ) ?>"
        data-txt-all-selected="<?php esc_attr_e( 'All methods', 'bookly' ) ?>"
        data-txt-nothing-selected="<?php esc_attr_e( 'No methods selected', 'bookly' ) ?>"
    >
        <?php foreach ( $gateways as $gateway => $title ): ?>
            <li data-input-name="gateways_list[]" data-value="<?php echo $gateway ?>" data-selected="<?php echo (int) ( $accepted_gateways ? in_array( $gateway, $accepted_gateways ) : true ) ?>">
                <?php echo esc_html( $title ) ?>
            </li>
        <?php endforeach ?>
    </ul>
</div>
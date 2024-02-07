<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls;
use Bookly\Backend\Components\Dialogs;
use BooklyPro\Backend\Components\Dialogs\GiftCard;

/** @var array $datatables */
$datatable = $datatables[ Bookly\Lib\Utils\Tables::GIFT_CARD_TYPES ]
?>
<div class="form-row justify-content-end">
    <div class="col-auto">
        <?php Controls\Buttons::renderAdd( 'bookly-add-gift-card-type', 'w-100 mb-3', __( 'Add gift card type', 'bookly' ) ) ?>
    </div>
    <div class="col-auto">
        <?php Controls\Buttons::render( null, 'btn-default w-100 mb-3', __( 'Settings', 'bookly' ), array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-gift-cards-settings-modal' ), '{caption}…', '<i class="fas fa-fw fa-cog mr-lg-1"></i>', true ) ?>
    </div>
    <?php Dialogs\TableSettings\Dialog::renderButton( 'gift_card_types', 'BooklyGiftCardsL10n' ) ?>
</div>

<div class="form-row align-items-center">
    <div class="col-md-4">
        <div class="form-group">
            <input class="form-control" type="text" id="bookly-filter-gct-title" placeholder="<?php esc_attr_e( 'Title', 'bookly' ) ?>"/>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="form-group">
            <select class="form-control bookly-js-select" id="bookly-filter-gct-service" data-placeholder="<?php esc_attr_e( 'Service', 'bookly' ) ?>">
                <?php foreach ( $services as $service ): ?>
                    <option value="<?php echo $service['id'] ?>"><?php echo esc_html( $service['title'] ) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="form-group">
            <select class="form-control bookly-js-select" id="bookly-filter-gct-staff" data-placeholder="<?php esc_attr_e( 'Staff', 'bookly' ) ?>">
                <?php foreach ( $staff_members as $staff ): ?>
                    <option value="<?php echo $staff['id'] ?>"><?php echo esc_html( $staff['title'] ) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="form-group">
            <?php Controls\Inputs::renderCheckBox( __( 'Show only active', 'bookly' ), null, null, array( 'id' => 'bookly-filter-gct-active' ) ) ?>
        </div>
    </div>
</div>

<table id="bookly-gift-card-types-list" class="table table-striped w-100">
    <thead>
    <tr>
        <?php foreach ( $datatable['settings']['columns'] as $column => $show ) : ?>
            <?php if ( $show ) : ?>
                <th><?php echo $datatable['titles'][ $column ] ?></th>
            <?php endif ?>
        <?php endforeach ?>
        <th width="200"></th>
        <th width="16"><?php Controls\Inputs::renderCheckBox( null, null, null, array( 'id' => 'bookly-check-gct-all' ) ) ?></th>
    </tr>
    </thead>
</table>

<div class="text-right mt-3">
    <?php Controls\Buttons::renderDelete( 'bookly-js-gift-card-types-delete' ) ?>
</div>
<?php GiftCard\Type\Dialog::render() ?>
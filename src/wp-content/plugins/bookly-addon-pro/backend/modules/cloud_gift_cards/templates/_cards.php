<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls;
use Bookly\Backend\Components\Dialogs;
use BooklyPro\Backend\Components\Dialogs\GiftCard;
use BooklyPro\Backend\Components\Dialogs\Appointment;

/** @var array $datatables */
$datatable = $datatables[ Bookly\Lib\Utils\Tables::GIFT_CARDS ]
?>
<div class="form-row justify-content-end">
    <div class="col-auto">
        <?php Controls\Buttons::render( null, 'btn-default w-100 mb-3', __( 'Export to CSV', 'bookly' ), array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-export-gift-cards-dialog' ), '{caption}…', '<i class="far fa-fw fa-share-square mr-lg-1"></i>', true ) ?>
    </div>
    <div class="col-auto">
        <?php Controls\Buttons::renderAdd( 'bookly-add-gift-card', 'w-100 mb-3', __( 'Add gift card', 'bookly' ) ) ?>
    </div>
    <div class="col-auto">
        <?php Controls\Buttons::render( null, 'btn-default w-100 mb-3', __( 'Settings', 'bookly' ), array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-gift-cards-settings-modal' ), '{caption}…', '<i class="fas fa-fw fa-cog mr-lg-1"></i>', true ) ?>
    </div>
    <?php Dialogs\TableSettings\Dialog::renderButton( 'gift_cards', 'BooklyGiftCardsL10n' ) ?>
</div>

<div class="form-row align-items-center">
    <div class="col-md-4">
        <div class="form-group">
            <input class="form-control" type="text" id="bookly-filter-gc-code" placeholder="<?php esc_attr_e( 'Gift card code', 'bookly' ) ?>"/>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="form-group">
            <select class="form-control bookly-js-select" id="bookly-filter-gc-type" data-placeholder="<?php esc_attr_e( 'Type', 'bookly' ) ?>">
                <?php foreach ( $card_types as $type ): ?>
                    <option value="<?php echo $type['id'] ?>"><?php echo esc_html( $type['title'] ) ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="form-group">
            <select class="form-control <?php echo $remote ? 'bookly-js-select-ajax' : 'bookly-js-select' ?>" id="bookly-filter-gc-customer"
                    data-placeholder="<?php esc_attr_e( 'Customer', 'bookly' ) ?>" <?php echo $remote ? 'data-ajax--action' : 'data-action' ?>="bookly_get_customers_list">
            <?php foreach ( $customers as $customer ) : ?>
                <option value="<?php echo $customer['id'] ?>" data-search='<?php echo esc_attr( json_encode( array_values( $customer ) ) ) ?>'><?php echo esc_html( $customer['full_name'] ) ?></option>
            <?php endforeach ?>
            </select>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="form-group">
            <?php Controls\Inputs::renderCheckBox( __( 'Show only active', 'bookly' ), null, null, array( 'id' => 'bookly-filter-gc-active' ) ) ?>
        </div>
    </div>
</div>
<table id="bookly-gift-cards-list" class="table table-striped w-100">
    <thead>
    <tr>
        <?php foreach ( $datatable['settings']['columns'] as $column => $show ) : ?>
            <?php if ( $show ) : ?>
                <th><?php echo $datatable['titles'][ $column ] ?></th>
            <?php endif ?>
        <?php endforeach ?>
        <th width="200"></th>
        <th width="16"><?php Controls\Inputs::renderCheckBox( null, null, null, array( 'id' => 'bookly-check-gc-all' ) ) ?></th>
    </tr>
    </thead>
</table>

<div class="text-right mt-3">
    <?php Controls\Buttons::renderDelete( 'bookly-js-gift-cards-delete' ) ?>
</div>
<?php Appointment\AttachPayment\Dialog::render() ?>
<?php Dialogs\Payment\Dialog::render() ?>
<?php Dialogs\Customer\Edit\Dialog::render() ?>
<?php Dialogs\Queue\Dialog::render() ?>
<?php GiftCard\Card\Dialog::render() ?>
<?php $self::renderTemplate( 'export', compact( 'datatable' ) ) ?>

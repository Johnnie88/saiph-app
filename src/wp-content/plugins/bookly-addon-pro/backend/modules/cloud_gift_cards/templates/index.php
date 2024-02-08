<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Support;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Components\Cloud;
use BooklyPro\Backend\Components\Dialogs\GiftCard\Settings;

/** @var array $datatable */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Gift Cards', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row">
                <div class="col">
                </div>
                <div class="col-auto">
                    <?php Cloud\Account\Panel::render() ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ( ! get_user_meta( get_current_user_id(), 'bookly_dismiss_gift_card_local_payment_notice', true ) ) : ?>
        <div id="bookly-gift-card-notice" class="alert alert-info mb-4" data-action="bookly_pro_dismiss_gift_card_local_payment_notice">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div class="form-row">
                <div class="col">
                    <?php esc_html_e( 'Please note that Local payments not allowed for gift cards.', 'bookly' ) ?>
                    <?php printf( '<a href="%s" target="_blank">%s</a>', 'https://api.booking-wp-plugin.com/go/bookly-cloud-gift-cards', __( 'Read more', 'bookly' ) ) ?>.
                </div>
            </div>
        </div>
    <?php endif ?>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs flex-column flex-lg-row bookly-nav-tabs-md" role="tablist" id="gift_cards_tabs">
                <li class="nav-item"><a class="nav-link active" data-toggle="bookly-tab" href="#cards"><?php esc_html_e( 'Cards', 'bookly' ) ?></a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#card-types"><?php esc_html_e( 'Card types', 'bookly' ) ?></a></li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane active" id="cards"><?php $self::renderTemplate( '_cards', compact( 'card_types', 'customers', 'remote', 'datatables' ) ) ?></div>
                <div class="tab-pane" id="card-types"><?php $self::renderTemplate( '_types', compact( 'services', 'staff_members', 'datatables' ) ) ?></div>
            </div>
        </div>
    </div>
    <?php Dialogs\TableSettings\Dialog::render() ?>
    <?php Settings\Dialog::render() ?>
</div>
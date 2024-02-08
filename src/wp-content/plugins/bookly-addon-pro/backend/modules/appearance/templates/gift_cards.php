<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Backend\Components\Editable\Elements;
?>
<div class="bookly-js-payment-gift-cards"<?php if ( ! get_option( 'bookly_cloud_gift_enabled' ) ) : ?> style="display: none;"<?php endif ?>>
    <div class="bookly-box bookly-list">
        <?php Elements::renderString( array( 'bookly_l10n_label_cloud_gift', 'bookly_l10n_cloud_gift_error_expired', 'bookly_l10n_cloud_gift_error_invalid', 'bookly_l10n_cloud_gift_error_low_balance', 'bookly_l10n_cloud_gift_error_not_found' ) ) ?>
        <div class="bookly-inline-block">
            <input class="bookly-user-gift" type="text"/>
            <div class="bookly-btn bookly-inline-block">
                <?php Elements::renderString( array( 'bookly_l10n_cloud_gift_apply_button', 'bookly_l10n_cloud_gift_applied_button' ) ) ?>
            </div>
        </div>
    </div>
</div>
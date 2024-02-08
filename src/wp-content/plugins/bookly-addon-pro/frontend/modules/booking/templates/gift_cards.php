<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
/** @var BooklyLib\UserBookingData $userData */
?>
<div class="bookly-box bookly-list">
    <?php echo BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_cloud_gift' ) ?>
    <input class="bookly-user-gift" name="bookly-gift-card" type="text" value="<?php echo esc_attr( $userData->getGiftCode() ) ?>" min="0"/>
    <button class="bookly-btn ladda-button bookly-js-apply-gift-card" data-style="zoom-in" data-spinner-size="40" style="<?php if ( $userData->getTips() !== null ) : ?>display: none;<?php else : ?>display: inline-block;<?php endif ?>">
        <span class="ladda-label"><?php echo BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_cloud_gift_apply_button' ) ?></span><span class="spinner"></span>
    </button>
    <button class="bookly-btn ladda-button bookly-js-applied-gift-card" data-style="zoom-in" data-spinner-size="40" style="<?php if ( $userData->getTips() === null ) : ?>display: none;<?php else : ?>display: inline-block;<?php endif ?>">
        <span class="ladda-label"><?php echo BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_cloud_gift_applied_button' ) ?></span><span class="spinner"></span>
    </button>
</div>
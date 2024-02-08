<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;

$color = get_option( 'bookly_app_color', '#f4662f' );
/** @var array $appearance */
/** @var array $attributes */
/** @var string $token */
/** @var string|bool $error */
?>
<?php if ( isset( $appearance['custom_css'] ) && $appearance['custom_css'] !== '' ) : ?>
    <style>
        <?php echo Common::css( $appearance['custom_css'] ) ?>
    </style>
<?php endif ?>
<div id="bookly-tbs" class="bookly-js-cancellation-confirmation">
    <?php if ( $error !== false ) : ?>
        <div class="text-center">
            <div>
                <i class="fa fa-fw fas fa-exclamation-triangle fa-2x text-danger"></i> <?php echo Common::html( $appearance['l10n']['error_header'] ) ?>
            </div>
            <div><?php echo Common::html( $error ) ?></div>
        </div>
    <?php else : ?>
        <div<?php if ( isset( $appearance['token'] ) ): ?> id="bookly-cancellation-confirmation-<?php echo Common::html( $appearance['token'] ) ?>"<?php endif ?>>
            <div class="bookly-js-cancellation-confirmation-buttons">
                <?php if ( $attributes && ( ( ! isset( $attributes['reason'] ) && $appearance['show_reason'] ) || ( isset( $attributes['reason'] ) && $attributes['reason'] ) ) ): ?>
                    <div class="mb-2"><?php echo Common::html( $appearance['l10n']['text_cancellation'] ) ?></div>
                    <?php if ( $appearance['show_reason'] ) : ?>
                        <div class="bookly-cancellation-reason form-group">
                            <textarea type="text" id="bookly-cancellation-reason" class="bookly-js-cancellation-confirmation-reason form-control"></textarea>
                        </div>
                    <?php endif ?>
                <?php endif ?>
                <div>
                    <a href="<?php echo admin_url( 'admin-ajax.php?action=bookly_cancel_appointment&token=' . $token ) ?>" class="btn btn-default bookly-js-cancellation-confirmation-yes" style="margin-right: 12px">
                        <span><?php echo Common::html( $appearance['l10n']['confirm'] ) ?></span>
                    </a>
                    <a href="#" class="btn btn-default bookly-js-cancellation-confirmation-no" style="color: <?php echo Common::html( $appearance['main_color'] ) ?>; border-color: <?php echo Common::html( $appearance['main_color'] ) ?>;">
                        <span><?php echo Common::html( $appearance['l10n']['cancel'] ) ?></span>
                    </a>
                </div>
            </div>
            <div class="bookly-js-cancellation-confirmation-message bookly-row" style="display: none">
                <p class="bookly-bold">
                    <?php echo Common::html( $appearance['l10n']['text_do_not_cancel'] ) ?>
                </p>
            </div>
        </div>
    <?php endif ?>
</div>
<script type="text/javascript">
    // Click on 'Do not cancel' button
    var links = document.getElementsByClassName('bookly-js-cancellation-confirmation-no');
    for (var i = 0; i < links.length; i++) {
        if (links[i].onclick == undefined) {
            links[i].onclick = function (e) {
                e.preventDefault();
                var container = this.closest('.bookly-js-cancellation-confirmation'),
                    buttons = container.getElementsByClassName('bookly-js-cancellation-confirmation-buttons')[0],
                    message = container.getElementsByClassName('bookly-js-cancellation-confirmation-message')[0];
                buttons.style.display = 'none';
                message.style.display = 'inline';
            };
        }
    }
    // Click on 'Confirm cancellation' button
    links = document.getElementsByClassName('bookly-js-cancellation-confirmation-yes');
    for (i = 0; i < links.length; i++) {
        if (links[i].onclick == undefined) {
            links[i].onclick = function(e) {
                e.preventDefault();
                var url = this.href,
                    $reason = this.closest('.bookly-js-cancellation-confirmation-buttons').querySelector('.bookly-js-cancellation-confirmation-reason');
                if ($reason !== null) {
                    url += String.fromCharCode(38) + 'reason=' + encodeURIComponent($reason.value)
                }
                window.location.replace(url)
            };
        }
    }
</script>
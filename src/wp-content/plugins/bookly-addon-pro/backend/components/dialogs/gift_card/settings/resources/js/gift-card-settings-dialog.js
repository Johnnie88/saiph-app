jQuery(function ($) {
    'use strict';
    let $modal = $('#bookly-gift-cards-settings-modal');
    $('.bookly-js-save', $modal).on('click', function (e) {
        e.preventDefault();
        let $mask = $('[name="bookly_gift_card_mask"]'),
            $error = $mask.closest('.form-group').find('.alert-danger'),
            mask = $mask.val();
        if (mask.split("*").length - 1 < 4) {
            $mask.addClass('is-invalid');
            $error.addClass('bookly-show');
        } else {
            let ladda = Ladda.create(this);
            ladda.start();
            $mask.removeClass('is-invalid');
            $error.removeClass('bookly-show');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookly_pro_save_gift_cards_settings',
                    partial_payment: $('[name="bookly_gift_card_partial_payment"]:checked').val(),
                    mask: mask,
                    csrf_token: BooklyL10nGlobal.csrf_token
                },
                dataType: 'json',
                success: function (response) {
                    ladda.stop();
                    $modal.booklyModal('hide');
                }
            })
        }
    });
});
jQuery(function ($) {
    'use strict';

    let zoom = {
            $authentication: $('#bookly_zoom_authentication'),
            $credentials: $('.bookly-js-zoom-credentials')
        },
        $wc_tab = $('#bookly_settings_woo_commerce'),
        wc = {
            $enabled: $('#bookly_wc_enabled', $wc_tab),
            $product: $('#bookly_wc_product', $wc_tab),
            $save: $('#bookly-save', $wc_tab)
        },
        hash = window.location.href.split('#')
    ;

    if (hash.length > 1) {
        if (hash[1] === 'zoom-failed') {
            booklyAlert({error: [BooklyProSettings10n.zoomFailed]});
            if ('pushState' in history) {
                history.pushState('', document.title, hash[0]);
            }
        }
    }

    $('#bookly-zoom-connect').on('click', function () {
        let ladda = Ladda.create(this);
        ladda.start();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bookly_pro_get_zoom_authorization_url',
                csrf_token: BooklyL10nGlobal.csrf_token
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.href = response.data.authorization_url;
                }
                ladda.stop();
            }
        });
    });

    $('#bookly-zoom-disconnect').on('click', function () {
        let ladda = Ladda.create(this);
        ladda.start();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bookly_pro_disconnect_zoom',
                csrf_token: BooklyL10nGlobal.csrf_token
            },
            dataType: 'json',
            success: function () {
                ladda.stop();
                window.location.reload();
            }
        });
    });

    zoom.$authentication.on('change', function () {
        zoom.$credentials.hide();
        $('#bookly-zoom-' + $(this).val()).show();
    });

    zoom.$authentication.trigger('change');

    wc.$save.on('click', function(e) {
        if (wc.$enabled.val() == '1' && wc.$product.val() == '0') {
            e.preventDefault();
            booklyAlert({error: [BooklyProSettings10n.wcSelectBookingProduct]});
        }
    })
});
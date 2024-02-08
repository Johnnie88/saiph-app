jQuery(function($) {
    $('.bookly-js-board').on('click', '[data-trigger]', function() {
        switch ($(this).data('trigger')) {
            case 'temporary-hide':
                var $dialog = $(this).closest('.bookly-modal-backdrop');
                $.get(BooklyL10nGlobal.ajax_url_backend, {action: 'bookly_pro_hide_grace_notice', csrf_token: BooklyL10nGlobal.csrf_token})
                    .done(function() {
                        $dialog.remove();
                    });
                break;
            case 'request_code':
                var $body = $(this).closest('.bookly-js-board');
                $.post(BooklyL10nGlobal.ajax_url_backend, {action: 'bookly_pro_verify_purchase_code_form', csrf_token: BooklyL10nGlobal.csrf_token}, function(response) {
                    $body.html(response.data.html);
                    $body.on('click', 'button[type="button"]', function(e) {
                        let $button = $(this),
                            $input = $button.closest('.input-group').find('input'),
                            ladda = Ladda.create(this);
                        if ($input.val().length == 36) {
                            ladda.start();
                            $input.removeClass('is-invalid');
                            $.post(BooklyL10nGlobal.ajax_url_backend, {action: 'bookly_pro_verify_purchase_code', plugin: $input.attr('id'), purchase_code: $input.val(), csrf_token: BooklyL10nGlobal.csrf_token}, function(response) {
                                if (response.success) {
                                    $input.addClass('is-valid');
                                    $button.hide().closest('.input-group').removeClass('input-group');
                                    if ($body.find('input:not(.is-valid)').length === 0) {
                                        $.post(BooklyL10nGlobal.ajax_url_backend, {action: 'bookly_pro_verification_succeeded', csrf_token: BooklyL10nGlobal.csrf_token}, function(response) {
                                            $body.closest('.bookly-modal-backdrop').html(response.data.html);
                                        });
                                    }
                                } else {
                                    if (response.data.message) {
                                        booklyAlert({error: [response.data.message]});
                                    }
                                    $input.addClass('is-invalid');
                                }
                                ladda.stop();
                            });
                        } else {
                            $input.addClass('is-invalid');
                        }
                    });
                });
                break;
        }
    });

    // Deactivate add-on Bookly Pro from *_grace_ended
    $('.bookly-js-deactivate-pro').on('click', function() {
        var $button = $(this);
        $.post(ajaxurl, {action: 'bookly_pro_deactivate', csrf_token: BooklyL10nGlobal.csrf_token}, function(response) {
            if (response.success) {
                if ($button.data('redirect')) {
                    window.location.href = response.data.target;
                } else {
                    $button.closest('.is-dismissible').remove();
                }
            }
        });
    });

    $('#bookly-tbs .is-dismissible').on('click', '[data-trigger=temporary-hide]', function() {
        $.get(BooklyL10nGlobal.ajax_url_backend, {action: 'bookly_pro_hide_grace_notice', csrf_token: BooklyL10nGlobal.csrf_token});
        $(this).closest('.is-dismissible').remove();
    });
});

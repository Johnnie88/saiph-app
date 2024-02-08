jQuery(function ($) {

    let $cardTypesTab = $('#card-types'),
        $cardsTab = $('#cards'),
        $exportDialog = $('#bookly-export-gift-cards-dialog', $cardsTab),
        card = {
            $table: $('#bookly-gift-cards-list'),
            dt: {
                obj: null,
                columns: [],
                order: [],
                action: $('<button type="button" class="btn btn-default mr-1" data-action="edit"></button>').append($('<i class="far fa-fw fa-edit mr-lg-1" />'), '<span class="d-none d-lg-inline">' + BooklyGiftCardsL10n.edit + '…</span>').get(0).outerHTML,
                row: null,
            },
            filter: {
                $code: $('#bookly-filter-gc-code', $cardsTab),
                $type: $('#bookly-filter-gc-type', $cardsTab),
                $customer: $('#bookly-filter-gc-customer', $cardsTab),
                $onlyActive: $('#bookly-filter-gc-active', $cardsTab),
            },
            buttons: {
                $add: $('#bookly-add-gift-card', $cardsTab),
                $delete: $('#bookly-js-gift-cards-delete', $cardsTab)
            },
            $checkAllRows: $('#bookly-check-gc-all', $cardsTab),
            export: {
                $dialog: $exportDialog,
                $selectAll: $('#bookly-js-export-select-all', $exportDialog),
            },
            onChangeFilter: function() {
                card.dt.obj.ajax.reload();
            }
        },
        cardType = {
            $table: $('#bookly-gift-card-types-list', $cardTypesTab),
            dt: {
                obj: null,
                columns: [],
                order: [],
                action: $('<button type="button" class="btn btn-default mr-1" data-action="edit"></button>').append($('<i class="far fa-fw fa-edit mr-lg-1" />'), '<span class="d-none d-lg-inline">' + BooklyGiftCardsL10n.edit + '…</span>').get(0).outerHTML,
                row: null
            },
            filter: {
                $title: $('#bookly-filter-gct-title', $cardTypesTab),
                $service: $('#bookly-filter-gct-service', $cardTypesTab),
                $staff: $('#bookly-filter-gct-staff', $cardTypesTab),
                $onlyActive: $('#bookly-filter-gct-active', $cardTypesTab),
            },
            buttons: {
                $add: $('#bookly-add-gift-card-type', $cardTypesTab),
                $delete: $('#bookly-js-gift-card-types-delete', $cardTypesTab)
            },
            $checkAllRows: $('#bookly-check-gct-all', $cardTypesTab),
            onChangeFilter: function() {
                cardType.dt.obj.ajax.reload();
            }
        };

    $('.bookly-js-select').val(null);

    /*************************************************************************
     * Tab Card                                                              *
     *************************************************************************/

    $.each(BooklyGiftCardsL10n.datatables.gift_cards.settings.filter, function (field, value) {
        if (value != '') {
            let $elem = $('#bookly-filter-gc-' + field);
            if ($elem.is(':checkbox')) {
                $elem.prop('checked', value == '1');
            } else {
                $elem.val(value);
            }
        }
        // check if select has correct values
        if ($('#bookly-filter-gc-' + field).prop('type') == 'select-one') {
            if ($('#bookly-filter-gc-' + field + ' option[value="' + value + '"]').length == 0) {
                $('#bookly-filter-gc-' + field).val(null);
            }
        }
    });

    $.each(BooklyGiftCardsL10n.datatables.gift_cards.settings.columns, function (column, show) {
        if (show) {
            switch (column) {
                case 'customer':
                    card.dt.columns.push({
                        data: column,
                        render: function (data, type, row, meta) {
                            return data === null
                                ? BooklyGiftCardsL10n.customers.nothingSelected
                                : $.fn.dataTable.render.text().display(data);
                        }
                    });
                    break;
                case 'balance':
                    card.dt.columns.push({
                        data: column,
                        render: function (data, type, row, meta) {
                            return row.balance;
                        }
                    });
                    break;
                case 'payment':
                    card.dt.columns.push({
                        data: 'payment',
                        render: function(data, type, row, meta) {
                            if (row.payment_id) {
                                return '<a type="button" data-action="show-payment" class="text-primary" data-payment_id="' + row.payment_id + '">' + data + '</a>';
                            }
                            return '';
                        }
                    });
                    break;
                default:
                    card.dt.columns.push({data: column, render: $.fn.dataTable.render.text()});
                    break;
            }
        }
    });

    card.dt.columns.push({
        data: null,
        responsivePriority: 1,
        orderable: false,
        width: 180,
        render: function (data, type, row, meta) {
            return card.dt.action;
        }
    });

    card.dt.columns.push({
        data: null,
        responsivePriority: 1,
        orderable: false,
        render: function (data, type, row, meta) {
            return '<div class="custom-control custom-checkbox">' +
                '<input value="' + row.id + '" id="bookly-gc-' + row.id + '" type="checkbox" class="custom-control-input">' +
                '<label for="bookly-gc-' + row.id + '" class="custom-control-label"></label>' +
                '</div>';
        }
    });

    card.dt.columns[0].responsivePriority = 0;

    $.each(BooklyGiftCardsL10n.datatables.gift_cards.settings.order, function (_, value) {
        const index = card.dt.columns.findIndex(function (c) { return c.data === value.column; });
        if (index !== -1) {
            card.dt.order.push([index, value.order]);
        }
    });

    card.dt.obj = card.$table.DataTable({
        order: card.dt.order,
        info: false,
        searching: false,
        lengthChange: false,
        processing: true,
        responsive: true,
        pageLength: 25,
        pagingType: 'numbers',
        serverSide: true,
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({action: 'bookly_pro_get_gift_cards', csrf_token: BooklyL10nGlobal.csrf_token}, {
                    filter: {
                        code: card.filter.$code.val(),
                        type: card.filter.$type.val(),
                        customer: card.filter.$customer.val(),
                        active: card.filter.$onlyActive.prop('checked') ? 1 : 0
                    }
                }, d);
            }
        },
        columns: card.dt.columns,
        dom: "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
        language: {
            zeroGiftCardsRecords: BooklyGiftCardsL10n.zeroGiftCardsRecords,
            processing: BooklyGiftCardsL10n.processing
        }
    });
    card.$table
    .on('click', '[data-action]', function () {
        card.dt.row = card.dt.obj.row($(this).closest('td'));
        switch ($(this).data('action')) {
            case 'edit':
                BooklyGiftCardDialog.showDialog(card.dt.row.data().id, function() {
                    card.dt.obj.ajax.reload(null, false);
                });
                break;
            case 'show-payment':
                BooklyPaymentDetailsDialog.showDialog({
                    payment_id: card.dt.row.data().payment_id,
                    target: 'gift_cards',
                    done: function(event) {
                        card.dt.obj.ajax.reload(null, false);
                    }
                });
                break;
        }
    })
    .on('change', 'tbody input:checkbox', function () {
        card.$checkAllRows.prop('checked', $('tbody input:not(:checked)', card.$table).length == 0);
    })

    $('.bookly-js-select', $cardsTab)
    .on('change', function () {
        card.dt.obj.ajax.reload(null, false);
    })
    .booklySelect2({
        width: '100%',
        theme: 'bootstrap4',
        dropdownParent: '#bookly-tbs',
        allowClear: true,
        placeholder: '',
        language: {
            noResults: function () {
                return BooklyGiftCardsL10n.noResultFound;
            },
            removeAllItems: function () {
                return BooklyGiftCardsL10n.clearField;
            }
        },
        matcher: function (params, data) {
            const term = $.trim(params.term).toLowerCase();
            if (term === '' || data.text.toLowerCase().indexOf(term) !== -1) {
                return data;
            }

            let result = null;
            const search = $(data.element).data('search');
            search &&
            search.find(function (text) {
                if (result === null && text.toLowerCase().indexOf(term) !== -1) {
                    result = data;
                }
            });

            return result;
        }
    });

    card.buttons.$delete.on('click', function () {
        booklyModal(BooklyGiftCardsL10n.areYouSure, null, BooklyGiftCardsL10n.cancel, BooklyGiftCardsL10n.yes)
        .on('bs.click.main.button', function (event, modal, mainButton) {
            let ladda = Ladda.create(mainButton),
                data = [],
                $checkboxes = $('tbody input:checked', card.$table)
            ;
            ladda.start();
            $checkboxes.each(function () {
                data.push(this.value);
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookly_pro_delete_gift_cards',
                    csrf_token: BooklyL10nGlobal.csrf_token,
                    data: data
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        card.dt.obj.ajax.reload(null, false);
                    } else {
                        booklyAlert({error: [response.data.message]});
                    }
                    ladda.stop();
                    modal.booklyModal('hide');
                }
            });
        });
    });

    if (BooklyGiftCardsL10n.customers.remote) {
        card.filter.$customer
        .val(null)
        .on('change', function () {
            card.dt.obj.ajax.reload(null, false);
        })
        .booklySelect2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: '#bookly-tbs',
            allowClear: true,
            placeholder: '',
            language: {
                noResults: function () {
                    return BooklyGiftCardsL10n.noResultFound;
                },
                searching: function () {
                    return BooklyGiftCardsL10n.searching;
                }
            },
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    params.page = params.page || 1;
                    return {
                        action: this.action === undefined ? $(this).data('ajax--action') : this.action,
                        filter: params.term,
                        page: params.page,
                        csrf_token: BooklyL10nGlobal.csrf_token
                    };
                }
            },
        });
    }
    card.filter.$onlyActive.on('change', card.onChangeFilter);
    card.filter.$code.on('keyup', card.onChangeFilter);
    card.buttons.$add.on('click', function () {
        BooklyGiftCardDialog.showDialog(null, function () { card.dt.obj.ajax.reload(null, false) });
    });
    card.$checkAllRows.on('change', function () {
        $('tbody input:checkbox', card.$table).prop('checked', this.checked);
    });

    card.export.$selectAll.on('click', function () {
        let checked = this.checked;
        $('.bookly-js-columns input', card.export.$dialog).each(function () {
            $(this).prop('checked', checked);
        });
    });

    $('.bookly-js-columns input', card.export.$dialog)
    .on('change', function () {
        card.export.$selectAll.prop('checked', $('.bookly-js-columns input:checked', card.export.$dialog).length == $('.bookly-js-columns input', card.export.$dialog).length);
    });

    /*************************************************************************
     * Tab Card Types                                                        *
     *************************************************************************/

    $('[href="#card-types"]').one('click', function () {
        $.each(BooklyGiftCardsL10n.datatables.gift_card_types.settings.columns, function (column, show) {
            if (show) {
                switch (column) {
                    case 'services':
                        cardType.dt.columns.push({
                            data: column,
                            render: function (data, type, row, meta) {
                                if (data == 0) {
                                    return BooklyGiftCardsL10n.services.nothingSelected;
                                } else if (data == 1) {
                                    return $.fn.dataTable.render.text().display(BooklyGiftCardsL10n.services.collection[row.service_id].title);
                                } else {
                                    if (data == BooklyGiftCardsL10n.services.count) {
                                        return BooklyGiftCardsL10n.services.allSelected;
                                    } else {
                                        return data + '/' + BooklyGiftCardsL10n.services.count;
                                    }
                                }
                            }
                        });
                        break;
                    case 'staff':
                        cardType.dt.columns.push({
                            data: column,
                            render: function (data, type, row, meta) {
                                if (data == 0) {
                                    return BooklyGiftCardsL10n.staff.nothingSelected;
                                } else if (data == 1) {
                                    if (typeof BooklyGiftCardsL10n.staff.collection[row.staff_id] === 'undefined') {
                                        return BooklyGiftCardsL10n.staff.nothingSelected;
                                    } else {
                                        return $.fn.dataTable.render.text().display(BooklyGiftCardsL10n.staff.collection[row.staff_id].title);
                                    }
                                } else {
                                    if (data == BooklyGiftCardsL10n.staff.count) {
                                        return BooklyGiftCardsL10n.staff.allSelected;
                                    } else {
                                        return data + '/' + BooklyGiftCardsL10n.staff.count;
                                    }
                                }
                            }
                        });
                        break;
                    case 'start_date':
                        cardType.dt.columns.push({
                            data: column,
                            render: function (data, type, row, meta) {
                                return row.start_date_formatted;
                            }
                        });
                        break;
                    case 'end_date':
                        cardType.dt.columns.push({
                            data: column,
                            render: function (data, type, row, meta) {
                                return row.end_date_formatted;
                            }
                        });
                        break;
                    case 'link_with_buyer':
                        cardType.dt.columns.push({
                            data: column,
                            className: 'text-center',
                            render: function (data, type, row, meta) {
                                return data == '1'
                                    ? '<i class="fas fa-user"></i>'
                                    : '<i class="fas fa-users"></i>';
                            }
                        });
                        break;
                    default:
                        cardType.dt.columns.push({data: column, render: $.fn.dataTable.render.text()});
                        break;
                }
            }
        });

        cardType.dt.columns.push({
            data: null,
            responsivePriority: 1,
            orderable: false,
            width: 180,
            render: function (data, type, row, meta) {
                return cardType.dt.action;
            }
        });

        cardType.dt.columns.push({
            data: null,
            responsivePriority: 1,
            orderable: false,
            render: function (data, type, row, meta) {
                return '<div class="custom-control custom-checkbox">' +
                    '<input value="' + row.id + '" id="bookly-gct-' + row.id + '" type="checkbox" class="custom-control-input">' +
                    '<label for="bookly-gct-' + row.id + '" class="custom-control-label"></label>' +
                    '</div>';
            }
        });

        cardType.dt.columns[0].responsivePriority = 0;

        $.each(BooklyGiftCardsL10n.datatables.gift_card_types.settings.order, function (_, value) {
            const index = cardType.dt.columns.findIndex(function (c) { return c.data === value.column; });
            if (index !== -1) {
                cardType.dt.order.push([index, value.order]);
            }
        });

        cardType.dt.obj = cardType.$table.DataTable({
            order: cardType.dt.order,
            info: false,
            searching: false,
            lengthChange: false,
            processing: true,
            responsive: true,
            pageLength: 25,
            pagingType: 'numbers',
            serverSide: true,
            ajax: {
                url: ajaxurl,
                type: 'POST',
                data: function (d) {
                    return $.extend({action: 'bookly_pro_get_gift_card_types', csrf_token: BooklyL10nGlobal.csrf_token}, {
                        filter: {
                            title: cardType.filter.$title.val(),
                            service: cardType.filter.$service.val(),
                            staff: cardType.filter.$staff.val(),
                            active: cardType.filter.$onlyActive.prop('checked') ? 1 : 0
                        }
                    }, d);
                }
            },
            columns: cardType.dt.columns,
            dom: "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
            language: {
                zeroGiftCardsRecords: BooklyGiftCardsL10n.zeroGiftCardTypesRecords,
                processing: BooklyGiftCardsL10n.processing
            }
        });
        cardType.$table
        .on('click', '[data-action=edit]', function () {
            cardType.dt.row = cardType.dt.obj.row($(this).closest('td'));
            BooklyGiftCardTypeDialog.showDialog(cardType.dt.row.data().id, function () {
                cardType.dt.obj.ajax.reload(null, false);
            });
        })
        .on('change', 'tbody input:checkbox', function () {
            cardType.$checkAllRows.prop('checked', $('tbody input:not(:checked)', cardType.$table).length == 0);
        })


        cardType.buttons.$delete.on('click', function () {
            booklyModal(BooklyGiftCardsL10n.areYouSure, BooklyGiftCardsL10n.deletingGiftCardTypeInfo, BooklyGiftCardsL10n.cancel, BooklyGiftCardsL10n.delete)
            .on('bs.click.main.button', function (event, modal, mainButton) {
                let ladda = Ladda.create(mainButton),
                    data = [],
                    $checkboxes = $('tbody input:checked', cardType.$table)
                ;
                ladda.start();
                $checkboxes.each(function () {
                    data.push(this.value);
                });

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bookly_pro_delete_gift_card_types',
                        csrf_token: BooklyL10nGlobal.csrf_token,
                        data: data
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            cardType.dt.obj.ajax.reload(null, false);
                            card.dt.obj.ajax.reload(null, false);
                        } else {
                            booklyAlert({error: [response.data.message]});
                        }
                        ladda.stop();
                        modal.booklyModal('hide');
                    }
                });
            });
        });

        cardType.filter.$onlyActive.on('change', cardType.onChangeFilter);
        cardType.filter.$title.on('keyup', cardType.onChangeFilter);
        $('.bookly-js-select', $cardTypesTab)
        .on('change', cardType.onChangeFilter)
        .booklySelect2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: '#bookly-tbs',
            allowClear: true,
            placeholder: '',
            language: {
                noResults: function () {
                    return BooklyGiftCardsL10n.noResultFound;
                },
                removeAllItems: function () {
                    return BooklyGiftCardsL10n.clearField;
                }
            },
            matcher: function (params, data) {
                const term = $.trim(params.term).toLowerCase();
                if (term === '' || data.text.toLowerCase().indexOf(term) !== -1) {
                    return data;
                }

                let result = null;
                const search = $(data.element).data('search');
                search &&
                search.find(function (text) {
                    if (result === null && text.toLowerCase().indexOf(term) !== -1) {
                        result = data;
                    }
                });

                return result;
            }
        });

        cardType.buttons.$add.on('click', function () {
            BooklyGiftCardTypeDialog.showDialog(null, function () { cardType.dt.obj.ajax.reload(null, false) });
        });
        cardType.$checkAllRows.on('change', function () {
            $('tbody input:checkbox', cardType.$table).prop('checked', this.checked);
        });
    });

    $('#gift_cards_tabs a[href="#' + BooklyGiftCardsL10n.tab + '"]').trigger('click');

    let $notice = $('#bookly-gift-card-notice');
    $notice.on('close.bs.alert', function () {
        $.post(ajaxurl, {action: $notice.data('action'), csrf_token: BooklyL10nGlobal.csrf_token});
    });
});

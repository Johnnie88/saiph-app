jQuery(function ($) {

    let
        $table = $('#bookly-analytics-table'),
        $dateFilter = $('#bookly-filter-date'),
        $staffFilter = $('#bookly-js-filter-staff'),
        $servicesFilter = $('#bookly-js-filter-services'),
        $printDialog = $('#bookly-print-dialog'),
        $printSelectAll = $('#bookly-js-print-select-all', $printDialog),
        $printButton = $(':submit', $printDialog),
        $exportDialog = $('#bookly-export-dialog'),
        $exportButton = $(':submit', $exportDialog),
        $exportSelectAll = $('#bookly-js-export-select-all', $exportDialog)
    ;

    /**
     * Staff drop-down.
     */
    function onChangeFilter() {
        dt.ajax.reload();
    }
    $staffFilter.booklyDropdown({
        onChange: function () {
            setTimeout(onChangeFilter);
        }
    });

    /**
     * Services drop-down.
     */
    $servicesFilter.booklyDropdown({onChange: onChangeFilter});

    $.each(BooklyAnalyticsL10n.filter, function (field, value) {
        let $filter = $('#bookly-js-filter-' + field);
        if ($filter.hasClass('bookly-dropdown-menu')) {
            if (value === 'all') {
                $filter.booklyDropdown('selectAll');
            } else {
                $filter.booklyDropdown('setSelected', value);
            }
        }
    });

    /**
     * Init DataTables.
     */
    let dt = $table.DataTable({
        order: [[0, 'desc']],
        info: false,
        paging: false,
        searching: false,
        lengthChange: false,
        processing: true,
        responsive: true,
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function () {
                let data = {
                    date: $dateFilter.data('date'),
                    filter: {
                        services: $servicesFilter.booklyDropdown('getSelected'),
                        staff: $staffFilter.booklyDropdown('getSelectedAllState')
                            ? 'all'
                            : $staffFilter.booklyDropdown('getSelected')
                    }
                };
                return booklySerialize.buildRequestData('bookly_pro_get_analytics', data);
            },
            dataSrc: function (json) {
                let $ths = $table.find('tfoot th');
                $ths.eq(1).html(json.total.appointments.total);
                $ths.eq(2).html(json.total.appointments.approved);
                $ths.eq(3).html(json.total.appointments.pending);
                $ths.eq(4).html(json.total.appointments.rejected);
                $ths.eq(5).html(json.total.appointments.cancelled);
                $ths.eq(6).html(json.total.customers.total);
                $ths.eq(7).html(json.total.customers.new);
                $ths.eq(8).html(json.total.revenue.total_formatted);

                return json.data;
            }
        },
        columns: [
            {data: 'staff', render: $.fn.dataTable.render.text()},
            {data: 'service', render: $.fn.dataTable.render.text()},
            {data: 'appointments.total'},
            {data: 'appointments.approved'},
            {data: 'appointments.pending'},
            {data: 'appointments.rejected'},
            {data: 'appointments.cancelled'},
            {data: 'customers.total'},
            {data: 'customers.new'},
            {data: 'revenue.total_formatted'}
        ],
        dom: "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
        language: {
            zeroRecords: BooklyAnalyticsL10n.zeroRecords,
            processing: BooklyAnalyticsL10n.processing
        }
    });

    $dateFilter.on('apply.daterangepicker', onChangeFilter);

    /**
     * Export.
     */
    $exportButton.on('click', function () {
        let columns = [];
        $('.bookly-js-columns input:checked', $exportDialog).each(function () {
            columns.push(this.value);
        });
        let config = {
            autoPrint: false,
            fieldSeparator: $('#bookly-csv-delimiter', $exportDialog).val(),
            exportOptions: {
                columns: columns
            },
            filename: 'Analytics'
        };
        $.fn.dataTable.ext.buttons.csvHtml5.action.call({processing: function () {}}, null, dt, null, $.extend({}, $.fn.dataTable.ext.buttons.csvHtml5, config));
    });

    $exportSelectAll
        .on('click', function () {
            let checked = this.checked;
            $('.bookly-js-columns input', $exportDialog).each(function () {
                $(this).prop('checked', checked);
            });
        });

    $('.bookly-js-columns input', $exportDialog)
        .on('change', function () {
            $exportSelectAll.prop('checked', $('.bookly-js-columns input:checked', $exportDialog).length == $('.bookly-js-columns input', $exportDialog).length);
        });

    $printSelectAll
        .on('click', function () {
            let checked = this.checked;
            $('.bookly-js-columns input', $printDialog).each(function () {
                $(this).prop('checked', checked);
            });
        });

    $('.bookly-js-columns input', $printDialog)
        .on('change', function () {
            $printSelectAll.prop('checked', $('.bookly-js-columns input:checked', $printDialog).length == $('.bookly-js-columns input', $printDialog).length);
        });

    /**
     * Print.
     */
    $printButton.on('click', function () {
        let columns = [];
        $('input:checked', $printDialog).each(function () {
            columns.push(this.value);
        });
        let config = {
            title: '&nbsp;',
            exportOptions: {
                columns: columns
            },
            customize: function (win) {
                win.document.firstChild.style.backgroundColor = '#fff';
                win.document.body.id = 'bookly-tbs';
                $(win.document.body).find('table').removeClass('bookly-collapsed');
                $(win.document.head).append('<style>@page{size: auto;}</style>');
            }
        };
        $.fn.dataTable.ext.buttons.print.action(null, dt, null, $.extend({}, $.fn.dataTable.ext.buttons.print, config));
    });
});
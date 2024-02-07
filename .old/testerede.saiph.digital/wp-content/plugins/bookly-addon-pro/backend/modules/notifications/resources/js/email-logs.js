jQuery(function ($) {
    $(document.body).on('bookly.init_email_logs', {},
        function (event) {
            let $table = $('#bookly-email-logs'),
                $modal = $('#bookly-email-logs-dialog'),
                $to = $('#bookly-email-to', $modal),
                $subject = $('#bookly-email-subject', $modal),
                $body = $('#bookly-email-body', $modal),
                $headers = $('#bookly-email-headers', $modal),
                $attachments = $('#bookly-email-attachments', $modal),
                $date = $('#bookly-email-date', $modal),
                $checkAllButton = $('#bookly-check-all'),
                $deleteButton = $('#bookly-email-log-delete'),
                $date_range = $('#bookly-email-logs-date-range'),
                $search = $('#bookly-email-log-search'),
                pickers = {
                    dateFormat: 'YYYY-MM-DD',
                    creationDate: {
                        startDate: moment().subtract(30, 'days'),
                        endDate: moment(),
                    },
                },
                picker_ranges = {},
                dt_order = [],
                dt_columns = [],
                dt
            ;
            $checkAllButton.on('change', function () {
                $table.find('tbody input:checkbox').prop('checked', this.checked);
            });
            $table.on('change', 'tbody input:checkbox', function () {
                $checkAllButton.prop('checked', $table.find('tbody input:not(:checked)').length == 0);
            })
            $deleteButton.on('click', function () {
                if (confirm(BooklyL10n.areYouSure)) {
                    var ladda = Ladda.create(this);
                    ladda.start();

                    let data = [];
                    $table.find('tbody input:checked').each(function () {
                        data.push(this.value);
                    });
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'bookly_pro_delete_email_logs',
                            csrf_token: BooklyL10nGlobal.csrf_token,
                            data: data
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                dt.ajax.reload(null, false);
                            } else {
                                alert(response.data.message);
                            }
                            ladda.stop();
                        }
                    });
                }
            });
            // Date range picker options.
            picker_ranges[BooklyEmailLogsL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
            picker_ranges[BooklyEmailLogsL10n.dateRange.today] = [moment(), moment()];
            picker_ranges[BooklyEmailLogsL10n.dateRange.last_7] = [moment().subtract(7, 'days'), moment()];
            picker_ranges[BooklyEmailLogsL10n.dateRange.last_30] = [moment().subtract(30, 'days'), moment()];
            picker_ranges[BooklyEmailLogsL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
            picker_ranges[BooklyEmailLogsL10n.dateRange.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
            // Init date range picker.
            $date_range.daterangepicker(
                {
                    parentEl: $date_range.parent(),
                    startDate: pickers.creationDate.startDate,
                    endDate: pickers.creationDate.endDate,
                    ranges: picker_ranges,
                    showDropdowns: true,
                    linkedCalendars: false,
                    autoUpdateInput: false,
                    locale: $.extend({}, BooklyEmailLogsL10n.dateRange, BooklyEmailLogsL10n.datePicker)
                },
                function (start, end) {
                    let format = 'YYYY-MM-DD';
                    $date_range
                        .data('date', start.format(format) + ' - ' + end.format(format))
                        .find('span')
                        .html(start.format(BooklyEmailLogsL10n.dateRange.format) + ' - ' + end.format(BooklyEmailLogsL10n.dateRange.format));
                }
            );
            // Init datatable columns.
            $.each(BooklyEmailLogsL10n.datatables.email_logs.settings.columns, function (column, show) {
                if (show) {
                    dt_columns.push({data: column, render: $.fn.dataTable.render.text()});
                }
            });
            $.each(BooklyEmailLogsL10n.datatables.email_logs.settings.order, function (_, value) {
                const index = dt_columns.findIndex(function (c) {
                    return c.data === value.column;
                });
                if (index !== -1) {
                    dt_order.push([index, value.order]);
                }
            });
            dt_columns.push({
                data: null,
                className: 'text-right',
                orderable: false,
                responsivePriority: 1,
                render: function (data, type, row, meta) {
                    return ' <button type="button" class="btn btn-default ladda-button" data-action="edit" data-spinner-size="40" data-style="zoom-in" data-spinner-color="#666666"><i class="far fa-fw fa-file-alt mr-lg-1"></i><span class="ladda-label"><span class="d-none d-lg-inline">' + BooklyEmailLogsL10n.details + '…</span></span></button>';
                }
            });
            dt_columns.push({
                data: null,
                orderable: false,
                responsivePriority: 1,
                render: function (data, type, row, meta) {
                    return '<div class="custom-control custom-checkbox">' +
                        '<input value="' + row.id + '" id="bookly-dt-' + row.id + '" type="checkbox" class="custom-control-input">' +
                        '<label for="bookly-dt-' + row.id + '" class="custom-control-label"></label>' +
                        '</div>';
                }
            });

            if (dt_columns.length) {
                dt = $table.DataTable({
                    order: dt_order,
                    ordering: true,
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
                            return $.extend({
                                action: 'bookly_pro_get_email_logs',
                                csrf_token: BooklyL10nGlobal.csrf_token
                            }, {
                                filter: {
                                    range: $date_range.data('date'),
                                    search: $search.val()
                                }
                            }, d);
                        },
                    },
                    columns: dt_columns,
                    dom: "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
                    language: {
                        zeroRecords: BooklyEmailLogsL10n.zeroRecords,
                        processing: BooklyEmailLogsL10n.processing
                    }
                });

                $table.on('click', 'button', function (e) {
                    let rowData = getDTRowData(this);
                    $to.val(rowData.to);
                    $body.val(rowData.body);
                    $subject.val(rowData.subject);
                    $headers.val(JSON.stringify(rowData.headers, null, 4));
                    $attachments.val(rowData.attach.join("\n"));
                    $date.val(rowData.created_at);
                    $modal.booklyModal('show');
                });

                function getDTRowData(element) {
                    let $el = $(element).closest('td');
                    if ($el.hasClass('child')) {
                        $el = $el.closest('tr').prev();
                    }
                    return dt.row($el).data();
                }
            }
            function onChangeFilter() {
                dt.ajax.reload();
            }
            $date_range.on('apply.daterangepicker', onChangeFilter);
            $search.on('keyup', onChangeFilter);

            $('#bookly_email_logs_expire').change(function (e) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bookly_pro_set_email_logs_expire',
                        expire: $(this).val(),
                        csrf_token: BooklyL10nGlobal.csrf_token,
                    },
                    dataType: 'json'
                });
            });
        }
    );
});

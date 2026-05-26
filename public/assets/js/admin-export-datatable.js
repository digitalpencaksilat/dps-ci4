/**
 * DataTable Export Helper — shared foundation for all exportable tables.
 *
 * Usage:
 *   window.initAdminExportTable('#myTable', {
 *     title: 'My Report',
 *     filename: 'My Report',
 *     orientation: 'landscape',
 *     preset: 'wide-report',
 *     printHeader: { title: 'MY REPORT', subtitle: 'Event Name' },
 *     printHeaderHtml: '<div>...</div>',
 *     excel: { columnWidths: { A: 8, B: 45 } },
 *     printCustomize: function(win) { ... },
 *     dataTable: { pageLength: 25 }
 *   });
 */

;(function () {
    'use strict';

    if (!window.jQuery) return;

    var $ = window.jQuery;

    // --- Auto-detect which base DataTable init function to use ---
    function getBaseInitFn() {
        if (typeof window.initAdminDataTable === 'function') {
            return window.initAdminDataTable;
        }
        if (typeof window.initKontingenDataTable === 'function') {
            return window.initKontingenDataTable;
        }
        // Fallback: direct DataTable init with reasonable defaults
        return function (selector, options) {
            return $(selector).DataTable(Object.assign({
                responsive: false,
                autoWidth: false,
                pageLength: 10,
                language: {
                    search: '',
                    searchPlaceholder: 'Cari data...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Belum ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
                }
            }, options));
        };
    }

    // --- Presets ---
    var presets = {
        'simple-list': { fontSize: '10px', tableWidth: '100%', orientation: 'portrait' },
        'wide-report': { fontSize: '9px', tableWidth: '95%', orientation: 'landscape' },
        'summary-table': { fontSize: '10px', tableWidth: '100%', orientation: 'portrait' }
    };

    // --- Main export helper ---
    window.initAdminExportTable = function (selector, config) {
        config = config || {};

        if (!$(selector).length || !$.fn.DataTable) return null;

        var preset = presets[config.preset] || presets['simple-list'];
        var title = config.title || document.title || 'Data Export';
        var filename = config.filename || title;
        var orientation = config.orientation || preset.orientation;
        var exportColumns = config.exportColumns || ':visible:not(' + (config.excludeColumns || '.no-export') + ')';
        var printHeader = Object.assign({ title: title, subtitle: '' }, config.printHeader || {});
        var buttons = [];

        if ($.fn.dataTable.Buttons) {
            // ColVis
            buttons.push(Object.assign({
                extend: 'colvis',
                className: 'btn btn-outline-secondary btn-sm',
                text: '<i class="fas fa-columns me-1"></i> Pilih Kolom'
            }, config.colvis || {}));

            // Excel
            if ($.fn.dataTable.ext.buttons.excelHtml5) {
                var excelCfg = config.excel || {};
                var numTextCols = excelCfg.numericTextColumns || [];

                buttons.push(Object.assign({
                    extend: 'excelHtml5',
                    title: title,
                    filename: filename,
                    className: 'btn btn-success btn-sm',
                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                    exportOptions: {
                        columns: exportColumns,
                        format: {
                            body: function (data, row, column, node) {
                                // Force numeric-text columns (NIK, KK, etc.) as text
                                if (numTextCols.indexOf(column) !== -1) {
                                    var stripped = data ? String(data).replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() : '';
                                    // Prepend zero-width space to prevent Excel numeric conversion
                                    if (stripped && /^\d{10,}$/.test(stripped)) {
                                        return '\u200B' + stripped;
                                    }
                                    return stripped;
                                }
                                return data;
                            }
                        }
                    },
                    customize: function (xlsx) {
                        var columnWidths = (config.excel && config.excel.columnWidths) ? config.excel.columnWidths : {};
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var colElement = sheet.getElementsByTagName('cols')[0];

                        if (!colElement && Object.keys(columnWidths).length) {
                            colElement = sheet.createElementNS(
                                'http://schemas.openxmlformats.org/spreadsheetml/2006/main',
                                'cols'
                            );
                            sheet.insertBefore(colElement, sheet.getElementsByTagName('sheetData')[0]);
                        }

                        if (colElement && Object.keys(columnWidths).length) {
                            $(colElement).empty();
                            Object.keys(columnWidths).forEach(function (colName) {
                                var colIndex = colName.toUpperCase().charCodeAt(0) - 64;
                                $(colElement).append(
                                    '<col min="' + colIndex + '" max="' + colIndex +
                                    '" width="' + columnWidths[colName] + '" customWidth="1"/>'
                                );
                            });
                        }

                        if (config.excel && typeof config.excel.customize === 'function') {
                            config.excel.customize(xlsx);
                        }
                    }
                }, config.excelButton || {}));
            }

            // Print
            if ($.fn.dataTable.ext.buttons.print) {
                buttons.push(Object.assign({
                    extend: 'print',
                    title: title,
                    filename: filename,
                    orientation: orientation,
                    className: 'btn btn-info btn-sm',
                    text: '<i class="fas fa-print me-1"></i> Cetak',
                    exportOptions: { columns: exportColumns },
                    customize: function (win) {
                        var $body = $(win.document.body);
                        var $head = $(win.document.head);
                        var headerHtml = config.printHeaderHtml ||
                            '<div class="export-print-header">' +
                            '<h2>' + printHeader.title + '</h2>' +
                            (printHeader.subtitle ? '<p>' + printHeader.subtitle + '</p>' : '') +
                            '</div>';

                        $body.prepend(headerHtml);
                        $body.find('h1').remove();
                        $head.append(
                            '<style>' +
                            '@page{size:' + orientation + ';margin:0.5cm;}' +
                            'body{font-family:Helvetica,Arial,sans-serif;font-size:' + preset.fontSize + ';}' +
                            '.export-print-header{text-align:center;margin-bottom:14px;}' +
                            '.export-print-header h2{font-size:18px;margin:0 0 4px;text-transform:uppercase;}' +
                            '.export-print-header p{margin:0;font-size:12px;}' +
                            'table{border-collapse:collapse!important;width:' + preset.tableWidth + '!important;margin-left:auto!important;margin-right:auto!important;}' +
                            'th{background:#f2f2f2!important;font-weight:700;text-align:center!important;}' +
                            'th,td{border:0.3pt solid #555!important;padding:4px!important;vertical-align:middle!important;white-space:normal!important;word-break:break-word!important;}' +
                            'tbody tr:nth-child(even){background:#f9f9f9!important;}' +
                            '.text-center{text-align:center!important;}' +
                            '.text-end{text-align:right!important;}' +
                            '</style>'
                        );

                        if (typeof config.printCustomize === 'function') {
                            config.printCustomize(win);
                        }
                    }
                }, config.printButton || {}));
            }
        }

        var baseInitFn = getBaseInitFn();

        // Auto-detect layout context for correct DOM class names
        var isKontingen = typeof window.initKontingenDataTable === 'function' && typeof window.initAdminDataTable !== 'function';
        var toolbarClass = isKontingen ? 'kontingen-table-toolbar' : 'admin-table-toolbar';
        var searchClass = isKontingen ? 'kontingen-search' : 'admin-search';

        return baseInitFn(selector, Object.assign({
            dom: "<'" + toolbarClass + " d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3'<'d-flex flex-wrap align-items-center gap-2'Bl><'" + searchClass + "'f>>" +
                "<'table-responsive'tr>" +
                "<'d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3'<'small text-muted'i><'p-0'p>>",
            buttons: config.buttons || buttons
        }, config.dataTable || {}));
    };
})();

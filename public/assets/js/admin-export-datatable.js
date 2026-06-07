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

    // --- Shared print customizer for medal tables (tanding & seni) ---
    // Renders medal badges + auto numbering in the print window, styled with the
    // DPS project theme (white header, red underline, Poppins/Oswald fonts).
    // medalColIndex is 1-based AFTER the auto "No." column is prepended.
    // opts: { watermark: { logo: '<url>', text: 'Powered by ... © 2026' } }
    window.dpsMedalPrintCustomize = function (win, medalColIndex, opts) {
        opts = opts || {};
        var $win = $(win.document);
        $win.find('head').append(
            '<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">' +
            '<style>' +
            'body{font-family:\'Poppins\',Arial,sans-serif!important;font-size:11px;color:#212529;}' +
            'table.medal-print-header{width:100%!important;margin:0 0 16px 0!important;border:none!important;border-bottom:3px solid #c60000!important;background:#ffffff!important;box-shadow:none!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            'table.medal-print-header td{border:none!important;padding:8px 10px!important;background:#ffffff!important;}' +
            'table.dataTable, table.medal-data-table{border-collapse:collapse!important;width:100%!important;table-layout:auto!important;margin-top:8px;}' +
            'table.medal-data-table thead th{font-family:\'Oswald\',\'Poppins\',Arial,sans-serif!important;text-align:center!important;vertical-align:middle!important;' +
            'background-color:#c60000!important;color:#ffffff!important;border:0.5pt solid #c60000!important;font-weight:600;padding:9px 6px!important;' +
            'text-transform:uppercase;white-space:nowrap!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            'table.medal-data-table tbody td{border:0.5pt solid #e3c9cb!important;padding:7px 8px!important;vertical-align:middle!important;white-space:nowrap!important;}' +
            'table.medal-data-table tbody tr:nth-child(even){background-color:#fff5f5!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            'table.medal-data-table tbody tr:nth-child(odd){background-color:#ffffff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            '.badge-print{-webkit-print-color-adjust:exact;print-color-adjust:exact;color:#fff!important;padding:5px 10px!important;' +
            'border-radius:6px!important;font-size:10px!important;font-weight:700!important;display:inline-block!important;' +
            'min-width:78px!important;text-align:center!important;text-transform:uppercase!important;letter-spacing:0.5px!important;white-space:nowrap!important;}' +
            '.medal-print-watermark{margin-top:14px;margin-right:8mm;text-align:right;font-family:\'Poppins\',Arial,sans-serif;font-size:9pt;color:#777777;page-break-inside:avoid;}' +
            '.medal-print-watermark img{height:20px;width:auto;vertical-align:middle;margin-right:5px;}' +
            '.medal-print-watermark span{vertical-align:middle;white-space:nowrap;}' +
            '</style>'
        );

        // Target the data table only (exclude the logo/header banner table)
        var $table = $win.find('body table').not('.medal-print-header').first();
        $table.addClass('medal-data-table');
        $win.find('body').find('h1').remove();

        // Auto numbering column
        $table.find('thead tr').prepend('<th style="width:30px!important;">No.</th>');
        $table.find('tbody tr').each(function (index) {
            $(this).prepend('<td style="text-align:center!important;">' + (index + 1) + '</td>');
        });

        // Medal badges (column index already accounts for prepended No. column)
        var colors = { EMAS: '#d4a017', PERAK: '#8c9094', PERUNGGU: '#b06a2c' };
        $table.find('tbody tr').each(function () {
            var $cell = $(this).find('td:nth-child(' + medalColIndex + ')');
            var text = $cell.text().trim().toUpperCase();
            Object.keys(colors).forEach(function (medal) {
                if (text.indexOf(medal) !== -1) {
                    $cell.html('<span class="badge-print" style="background-color:' + colors[medal] + '!important;">' + medal + '</span>').css('text-align', 'center');
                }
            });
        });

        $table.find('thead th').css('text-align', 'center');

        // Auto-shrink font so wide tables fit one page width (no wraptext)
        var colCount = $table.find('thead th').length;
        var bodyFont = colCount > 12 ? '8px' : (colCount > 9 ? '9px' : '10px');
        var cellPad = colCount > 12 ? '4px 5px' : '7px 8px';
        $table.find('th, td').css({ 'font-size': bodyFont, 'padding': cellPad });

        // Watermark (bottom-right) — appears once after the content (last page).
        if (opts.watermark && (opts.watermark.logo || opts.watermark.text)) {
            var wmHtml = '<div class="medal-print-watermark">';
            if (opts.watermark.logo) {
                wmHtml += '<img src="' + opts.watermark.logo + '" alt="Logo">';
            }
            if (opts.watermark.text) {
                wmHtml += '<span>' + opts.watermark.text + '</span>';
            }
            wmHtml += '</div>';
            $win.find('body').append(wmHtml);
        }
    };

    // --- Shared print customizer for medal TALLY tables (akumulasi, per kategori usia, sekolah) ---
    // Numeric medal-count tables that already carry a Rank column — no badges, no
    // auto numbering. Same DPS theme (white header, red underline, Oswald/Poppins).
    // opts: { watermark: { logo: '<url>', text: '...' } }
    window.dpsMedalTallyPrintCustomize = function (win, opts) {
        opts = opts || {};
        var $win = $(win.document);
        $win.find('head').append(
            '<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">' +
            '<style>' +
            'body{font-family:\'Poppins\',Arial,sans-serif!important;font-size:11px;color:#212529;}' +
            'table.medal-print-header{width:100%!important;margin:0 0 16px 0!important;border:none!important;border-bottom:3px solid #c60000!important;background:#ffffff!important;box-shadow:none!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            'table.medal-print-header td{border:none!important;padding:8px 10px!important;background:#ffffff!important;}' +
            'table.dataTable, table.medal-data-table{border-collapse:collapse!important;width:100%!important;table-layout:auto!important;margin-top:8px;}' +
            'table.medal-data-table thead th{font-family:\'Oswald\',\'Poppins\',Arial,sans-serif!important;text-align:center!important;vertical-align:middle!important;' +
            'background-color:#c60000!important;color:#ffffff!important;border:0.5pt solid #c60000!important;font-weight:600;padding:9px 6px!important;' +
            'text-transform:uppercase;white-space:nowrap!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            'table.medal-data-table tbody td{border:0.5pt solid #e3c9cb!important;padding:7px 8px!important;vertical-align:middle!important;white-space:nowrap!important;}' +
            'table.medal-data-table tbody tr:nth-child(even){background-color:#fff5f5!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            'table.medal-data-table tbody tr:nth-child(odd){background-color:#ffffff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}' +
            '.medal-print-watermark{margin-top:14px;margin-right:8mm;text-align:right;font-family:\'Poppins\',Arial,sans-serif;font-size:9pt;color:#777777;page-break-inside:avoid;}' +
            '.medal-print-watermark img{height:20px;width:auto;vertical-align:middle;margin-right:5px;}' +
            '.medal-print-watermark span{vertical-align:middle;white-space:nowrap;}' +
            '</style>'
        );

        var $table = $win.find('body table').not('.medal-print-header').first();
        $table.addClass('medal-data-table');
        $win.find('body').find('h1').remove();
        $table.find('thead th').css('text-align', 'center');

        // Auto-shrink font so wide tables fit one page width (no wraptext)
        var colCount = $table.find('thead th').length;
        var bodyFont = colCount > 12 ? '8px' : (colCount > 9 ? '9px' : '10px');
        var cellPad = colCount > 12 ? '4px 5px' : '7px 8px';
        $table.find('th, td').css({ 'font-size': bodyFont, 'padding': cellPad });

        // Watermark (bottom-right) — appears once after the content (last page).
        if (opts.watermark && (opts.watermark.logo || opts.watermark.text)) {
            var wmHtml = '<div class="medal-print-watermark">';
            if (opts.watermark.logo) {
                wmHtml += '<img src="' + opts.watermark.logo + '" alt="Logo">';
            }
            if (opts.watermark.text) {
                wmHtml += '<span>' + opts.watermark.text + '</span>';
            }
            wmHtml += '</div>';
            $win.find('body').append(wmHtml);
        }
    };

    // --- Shared Excel customizer for medal tables (tanding & seni) ---
    // Bold centered title row, grey bordered header, bordered body, centered
    // medal column, and forced uppercase — matching the legacy CI3 export.
    // medalColLetter is the worksheet column of the Medali field (e.g. 'G').
    window.dpsMedalExcelCustomize = function (xlsx, medalColLetter) {
        var sheet = xlsx.xl.worksheets['sheet1.xml'];
        var styles = xlsx.xl['styles.xml'];

        var addStyle = function (xml, styleStr) {
            var el = xml.getElementsByTagName('cellXfs')[0];
            var newStyle = new DOMParser().parseFromString(styleStr, 'text/xml').childNodes[0];
            el.appendChild(newStyle);
            return el.childNodes.length - 1;
        };

        var fonts = styles.getElementsByTagName('fonts')[0];
        $(fonts).append('<font><sz val="14"/><name val="Calibri"/><b/><color rgb="000000"/></font>');
        var fontHdrIdx = fonts.childNodes.length - 1;
        $(fonts).append('<font><sz val="12"/><name val="Calibri"/><color rgb="000000"/></font>');
        var fontBdyIdx = fonts.childNodes.length - 1;

        var fills = styles.getElementsByTagName('fills')[0];
        $(fills).append('<fill><patternFill patternType="solid"><fgColor rgb="D3D3D3"/><bgColor indexed="64"/></patternFill></fill>');
        var fillGreyIdx = fills.childNodes.length - 1;

        var styleTitleIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontHdrIdx + '" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');
        var styleHeaderIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontHdrIdx + '" fillId="' + fillGreyIdx + '" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');
        var styleBodyIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontBdyIdx + '" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>');
        var styleBodyCenterIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontBdyIdx + '" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');

        $('row:eq(0) c', sheet).attr('s', styleTitleIdx);  // Title row
        $('row:eq(1) c', sheet).attr('s', styleHeaderIdx);  // Header row
        $('row:gt(1) c', sheet).attr('s', styleBodyIdx);    // Body default

        if (medalColLetter) {
            $('row:gt(1) c[r^="' + medalColLetter + '"]', sheet).attr('s', styleBodyCenterIdx);
        }

        // Force uppercase on all text cells
        $('row c', sheet).each(function () {
            $(this).find('v, t').each(function () {
                var text = $(this).text();
                if (isNaN(text)) {
                    $(this).text(text.toUpperCase());
                }
            });
        });
    };

    // --- Main export helper ---
    window.initAdminExportTable = function (selector, config) {
        config = config || {};

        if (!$(selector).length || !$.fn.DataTable) return null;

        // Medal-tally convenience flag (used by data-export-config JSON, which
        // cannot carry function references). Wires the themed print + excel
        // customizers so akumulasi/per-kategori/sekolah pages export consistently.
        if (config.medalTally) {
            var tallyWatermark = config.watermark || null;
            if (typeof config.printCustomize !== 'function') {
                config.printCustomize = function (win) {
                    window.dpsMedalTallyPrintCustomize(win, { watermark: tallyWatermark });
                };
            }
            config.excel = config.excel || {};
            if (typeof config.excel.customize !== 'function') {
                config.excel.customize = function (xlsx) {
                    window.dpsMedalExcelCustomize(xlsx);
                };
            }
        }

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

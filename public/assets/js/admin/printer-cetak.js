/**
 * Printer - Cetak Sertifikat
 * Dimuat di section('scripts') SETELAH jQuery + Bootstrap + DataTables.
 * Menangani: DataTables, modal cetak editable, generate nomor sertifikat single (AJAX).
 */

// Modal cetak editable (parity legacy modalPrintSertifikat)
window.printSertifikat = function (btn) {
    var d = btn.dataset;
    var form = document.getElementById('formPrintSertifikat');
    if (!form) return;
    form.setAttribute('action', d.link);
    document.getElementById('pmNomor').value     = d.nomor || '';
    document.getElementById('pmNama').value       = d.nama || '';
    document.getElementById('pmKategori').value   = d.kategori || '';
    document.getElementById('pmKontingen').value  = d.kontingen || '';
    document.getElementById('pmSekolah').value    = d.sekolah || '';
    var modalEl = document.getElementById('modalPrintSertifikat');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
};

// Init DataTable + generate-single handler untuk satu tabel
window.initPrinterCetakTable = function (selector, options) {
    options = options || {};
    var $table = jQuery(selector);
    if (!$table.length) return null;

    var defaults = {
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampil _MENU_',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_',
            infoEmpty: 'Tidak ada data',
            zeroRecords: 'Data tidak ditemukan',
            paginate: { next: '›', previous: '‹' }
        }
    };

    var dt = $table.DataTable(jQuery.extend(true, {}, defaults, options));

    return dt;

    // Generate nomor sertifikat untuk satu peserta (AJAX)
    $table.on('click', '.btn-generate-nomor', function () {
        var btn   = jQuery(this);
        var jenis = btn.data('jenis');
        var id    = btn.data('id');
        var cell  = btn.closest('td').find('.nomor-cell');
        var icon  = btn.find('i');

        btn.prop('disabled', true);
        icon.removeClass('fa-wand-magic-sparkles').addClass('fa-spinner fa-spin');

        var payload = { jenis: jenis, id: id };
        payload[window.CSRF_NAME] = window.CSRF_HASH;

        jQuery.ajax({
            url: window.PRINTER_GENERATE_URL,
            type: 'POST',
            dataType: 'json',
            data: payload
        }).done(function (res) {
            if (res && res.status && res.nomor) {
                cell.html('<code class="text-success">' + jQuery('<div>').text(res.nomor).html() + '</code>');
                btn.remove();
                if (window.CSRF_NAME && res.csrf_hash) { window.CSRF_HASH = res.csrf_hash; }
                if (window.toastr) toastr.success('Nomor sertifikat: ' + res.nomor);
            } else {
                resetBtn();
                if (window.toastr) toastr.error((res && res.message) || 'Gagal generate nomor');
            }
        }).fail(function () {
            resetBtn();
            if (window.toastr) toastr.error('Terjadi kesalahan koneksi');
        });

        function resetBtn() {
            btn.prop('disabled', false);
            icon.removeClass('fa-spinner fa-spin').addClass('fa-wand-magic-sparkles');
        }
    });
};

<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card mb-3">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="fas fa-certificate me-2"></i>Cetak Batch Sertifikat
                </h6>
                <a href="<?= base_url('admin/printer/dashboard') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard Printer
                </a>
            </div>
            <div class="card-body">
                <p class="text-sm text-muted mb-0">Pilih peserta tanding dan seni untuk dicetak sertifikatnya secara batch.</p>
            </div>
        </div>
    </div>
</div>

<!-- Quality Selector -->
<div class="row mb-3">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card">
            <div class="card-body">
                <div class="row align-items-end gap-2 gap-md-0">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-expand me-1"></i>Kualitas Render:</label>
                        <select id="qualityScale" class="form-select form-select-sm">
                            <option value="2" selected>Standar — 2× (rekomendasi batch besar)</option>
                            <option value="3">Tajam — 3× (cetak final)</option>
                            <option value="4">Ultra — 4× (batch kecil)</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Default 2× cukup untuk A4 landscape. 3–4× untuk cetak final dengan batch kecil.
                        </small>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <span class="badge bg-danger" id="badgeTanding"><?= count($dataTanding ?? []) ?> Tanding</span>
                            <span class="badge bg-info" id="badgeSeni"><?= count($dataSeni ?? []) ?> Seni</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Tanding / Seni -->
<ul class="nav nav-tabs mb-3 px-0 px-md-2" id="batchTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-tanding" data-bs-toggle="tab" data-bs-target="#panel-tanding" type="button" role="tab">
            <i class="fa-solid fa-hand-fist me-1"></i> Peserta Tanding
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-seni" data-bs-toggle="tab" data-bs-target="#panel-seni" type="button" role="tab">
            <i class="fa-solid fa-masks-theater me-1"></i> Peserta Seni
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Panel Tanding -->
    <div class="tab-pane fade show active" id="panel-tanding" role="tabpanel">
        <div class="row mb-3">
            <div class="col-12 px-0 px-md-2">
                <div class="admin-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="checkAllTandingHeader">
                                <label class="form-check-label fw-semibold" for="checkAllTandingHeader">Pilih Semua (halaman ini)</label>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="filterKontingenTanding" class="form-label mb-0 small">Kontingen:</label>
                                <select class="form-select form-select-sm" id="filterKontingenTanding" style="width:auto;">
                                    <option value="">Semua</option>
                                    <?php foreach ($kontingenRows as $k): ?>
                                        <option value="<?= esc($k->nama_kontingen) ?>"><?= esc($k->nama_kontingen) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 table-sm" id="tableBatchTanding" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center no-sort" style="width:40px;">
                                            <input type="checkbox" class="form-check-input" id="checkAllTanding">
                                        </th>
                                        <th>Nama</th>
                                        <th>Kontingen</th>
                                        <th class="d-none d-md-table-cell">Kategori</th>
                                        <th class="text-center" style="width:70px;">Medali</th>
                                        <th style="width:130px;">Nomor</th>
                                        <th class="text-center" style="width:80px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataTanding as $p) : ?>
                                        <?php
                                        $isJuara = ! empty($p->jenis_medali);
                                        $medalIcon = ['emas' => '🥇', 'perak' => '🥈', 'perunggu' => '🥉'][$p->jenis_medali ?? ''] ?? '';
                                        ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input chk-sertifikat-tanding" value="<?= esc((string) $p->id_peserta_tanding, 'attr') ?>">
                                            </td>
                                            <td class="fw-semibold text-capitalize"><?= esc($p->nama_pendaftar) ?></td>
                                            <td class="text-capitalize"><?= esc($p->nama_kontingen) ?></td>
                                            <td class="d-none d-md-table-cell text-capitalize small">
                                                <?= esc(($p->nama_kategori_usia ?? '') . ' ' . ($p->jenis_kelamin ?? '') . ($p->label ? ' Kelas ' . $p->label : '')) ?>
                                            </td>
                                            <td class="text-center"><?= $isJuara ? $medalIcon : '<span class="text-muted">—</span>' ?></td>
                                            <td>
                                                <?= $p->nomor_sertifikat ? '<code class="text-success small">' . esc($p->nomor_sertifikat) . '</code>' : '<span class="text-muted">—</span>' ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (($p->status_sertifikat ?? '') === 'sudah_dicetak') : ?>
                                                    <span class="badge bg-success">Dicetak</span>
                                                <?php else : ?>
                                                    <span class="badge bg-secondary">Belum</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Seni -->
    <div class="tab-pane fade" id="panel-seni" role="tabpanel">
        <div class="row mb-3">
            <div class="col-12 px-0 px-md-2">
                <div class="admin-card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="checkAllSeniHeader">
                                <label class="form-check-label fw-semibold" for="checkAllSeniHeader">Pilih Semua (halaman ini)</label>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label for="filterKontingenSeni" class="form-label mb-0 small">Kontingen:</label>
                                <select class="form-select form-select-sm" id="filterKontingenSeni" style="width:auto;">
                                    <option value="">Semua</option>
                                    <?php foreach ($kontingenRows as $k): ?>
                                        <option value="<?= esc($k->nama_kontingen) ?>"><?= esc($k->nama_kontingen) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 table-sm" id="tableBatchSeni" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center no-sort" style="width:40px;">
                                            <input type="checkbox" class="form-check-input" id="checkAllSeni">
                                        </th>
                                        <th>Nama</th>
                                        <th>Kontingen</th>
                                        <th class="d-none d-md-table-cell">Kategori</th>
                                        <th class="text-center" style="width:70px;">Medali</th>
                                        <th style="width:130px;">Nomor</th>
                                        <th class="text-center" style="width:80px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataSeni as $p) : ?>
                                        <?php
                                        $isJuara = ! empty($p->jenis_medali);
                                        $medalIcon = ['emas' => '🥇', 'perak' => '🥈', 'perunggu' => '🥉'][$p->jenis_medali ?? ''] ?? '';
                                        ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input chk-sertifikat-seni" value="<?= esc((string) $p->id_peserta_seni, 'attr') ?>">
                                            </td>
                                            <td class="fw-semibold text-capitalize"><?= esc($p->nama_pendaftar) ?></td>
                                            <td class="text-capitalize"><?= esc($p->nama_kontingen) ?></td>
                                            <td class="d-none d-md-table-cell text-capitalize small">
                                                <?= esc(($p->nama_kategori_usia ?? '') . ' ' . ($p->jenis_kelamin ?? '') . ' ' . ($p->nama_seni ?? '')) ?>
                                            </td>
                                            <td class="text-center"><?= $isJuara ? $medalIcon : '<span class="text-muted">—</span>' ?></td>
                                            <td>
                                                <?= $p->nomor_sertifikat ? '<code class="text-success small">' . esc($p->nomor_sertifikat) . '</code>' : '<span class="text-muted">—</span>' ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (($p->status_sertifikat ?? '') === 'sudah_dicetak') : ?>
                                                    <span class="badge bg-success">Dicetak</span>
                                                <?php else : ?>
                                                    <span class="badge bg-secondary">Belum</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Toolbar (Sticky Bottom) -->
<div id="toolbarActions" class="row bg-white border-top py-2 px-2 px-md-3 gap-2" style="display:none; position:sticky; bottom:0; z-index:99; margin:0;">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span id="infoTerpilih" class="badge bg-warning text-dark">
            Peserta Terpilih: <strong id="countSelected">0</strong>
            (<span id="countTanding">0</span> Tanding, <span id="countSeni">0</span> Seni)
        </span>
        <div class="ms-auto d-flex gap-2">
            <button id="btnPilihSemua" class="btn btn-sm btn-outline-secondary" onclick="pilihSemua()">
                <i class="fas fa-check-square me-1"></i>Pilih Semua
            </button>
            <button id="btnBersihkan" class="btn btn-sm btn-outline-secondary" onclick="bersihkanSemua()">
                <i class="fas fa-square me-1"></i>Bersihkan
            </button>
            <button id="btnCetakBrowser" class="btn btn-danger btn-sm" onclick="cetakBrowser()" disabled>
                <i class="fas fa-print me-1"></i>Cetak Browser
            </button>
            <button id="btnRenderLocal" class="btn btn-dark btn-sm" onclick="renderLocal()" disabled>
                <i class="fas fa-server me-1"></i>Render Lokal
            </button>
        </div>
    </div>
</div>

<!-- Form batch (hidden) -->
<form id="formBatch" action="<?= base_url('admin/sekretariat/sertifikat-batch/proses-cetak') ?>" method="post" target="_blank">
    <?= csrf_field() ?>
    <div id="formTandingInputs"></div>
    <div id="formSeniInputs"></div>
</form>

<!-- Modal Render Lokal -->
<div class="modal fade" id="modalLokal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-terminal me-2"></i>Render Lokal Sertifikat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>File HTML sudah dibuat. Jalankan perintah berikut di terminal:</p>
                <div class="bg-dark text-light p-3 rounded mb-3">
                    <code id="renderCommand" style="word-break:break-all;"></code>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnCopyCommand">
                        <i class="fa-solid fa-copy me-1"></i> Salin Perintah
                    </button>
                </div>
                <hr>
                <p class="small text-muted mb-0">
                    Output ZIP: <code id="renderOutputDir"></code>. Alternatif CLI: <code>php spark sertifikat:render-local</code>
                </p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/admin/printer-cetak.js') ?>"></script>
<script>
var selectedTanding = new Set();
var selectedSeni = new Set();

$(document).ready(function() {
    // Init DataTables
    var tableTanding = initPrinterCetakTable('#tableBatchTanding', {
        columnDefs: [{ orderable: false, targets: [0, -1] }]
    });
    var tableSeni = initPrinterCetakTable('#tableBatchSeni', {
        columnDefs: [{ orderable: false, targets: [0, -1] }]
    });

    // Filter kontingen
    $('#filterKontingenTanding').on('change', function() {
        tableTanding.column(2).search(this.value).draw();
    });
    $('#filterKontingenSeni').on('change', function() {
        tableSeni.column(2).search(this.value).draw();
    });

    // Check-all header (halaman ini saja)
    $('#checkAllTanding').on('change', function() {
        var checked = this.checked;
        tableTanding.rows({ page: 'current' }).every(function() {
            var node = this.node();
            var cb = $(node).find('.chk-sertifikat-tanding');
            cb.prop('checked', checked);
            var id = parseInt(cb.val());
            checked ? selectedTanding.add(id) : selectedTanding.delete(id);
        });
        $('#checkAllTandingHeader').prop('checked', checked);
        updateToolbar();
    });

    $('#checkAllSeni').on('change', function() {
        var checked = this.checked;
        tableSeni.rows({ page: 'current' }).every(function() {
            var node = this.node();
            var cb = $(node).find('.chk-sertifikat-seni');
            cb.prop('checked', checked);
            var id = parseInt(cb.val());
            checked ? selectedSeni.add(id) : selectedSeni.delete(id);
        });
        $('#checkAllSeniHeader').prop('checked', checked);
        updateToolbar();
    });

    // Individual checkbox
    $(document).on('change', '.chk-sertifikat-tanding', function() {
        var id = parseInt(this.value);
        this.checked ? selectedTanding.add(id) : selectedTanding.delete(id);
        updateToolbar();
    });

    $(document).on('change', '.chk-sertifikat-seni', function() {
        var id = parseInt(this.value);
        this.checked ? selectedSeni.add(id) : selectedSeni.delete(id);
        updateToolbar();
    });

    // Header checkbox sync
    $('#checkAllTandingHeader').on('change', function() {
        $('#checkAllTanding').prop('checked', this.checked).trigger('change');
    });

    $('#checkAllSeniHeader').on('change', function() {
        $('#checkAllSeni').prop('checked', this.checked).trigger('change');
    });

    // Persist checkbox state across DataTables redraw
    $('#tableBatchTanding').on('draw.dt', function() {
        $('.chk-sertifikat-tanding').each(function() {
            this.checked = selectedTanding.has(parseInt(this.value));
        });
    });
    $('#tableBatchSeni').on('draw.dt', function() {
        $('.chk-sertifikat-seni').each(function() {
            this.checked = selectedSeni.has(parseInt(this.value));
        });
    });
});

function updateToolbar() {
    var countT = selectedTanding.size;
    var countS = selectedSeni.size;
    var total = countT + countS;
    $('#countSelected').text(total);
    $('#countTanding').text(countT);
    $('#countSeni').text(countS);
    total > 0 ? $('#toolbarActions').show() : $('#toolbarActions').hide();
    $('#btnCetakBrowser, #btnRenderLocal').prop('disabled', total === 0);
}

function pilihSemua() {
    // Pilih semua di halaman aktif (kedua tabel)
    ['#tableBatchTanding', '#tableBatchSeni'].forEach(function(sel) {
        var dt = $(sel).DataTable();
        dt.rows({ page: 'current' }).every(function() {
            var node = this.node();
            var cb = $(node).find('input[type=checkbox]');
            cb.prop('checked', true);
            var id = parseInt(cb.val());
            if (sel === '#tableBatchTanding') selectedTanding.add(id);
            else selectedSeni.add(id);
        });
    });
    $('#checkAllTanding, #checkAllSeni, #checkAllTandingHeader, #checkAllSeniHeader').prop('checked', true);
    updateToolbar();
}

function bersihkanSemua() {
    selectedTanding.clear();
    selectedSeni.clear();
    $('.chk-sertifikat-tanding, .chk-sertifikat-seni').prop('checked', false);
    $('#checkAllTanding, #checkAllSeni, #checkAllTandingHeader, #checkAllSeniHeader').prop('checked', false);
    updateToolbar();
}

function buildFormInputs() {
    $('#formTandingInputs, #formSeniInputs').empty();
    selectedTanding.forEach(function(id) {
        $('#formTandingInputs').append('<input type="hidden" name="id_peserta_tanding[]" value="' + id + '">');
    });
    selectedSeni.forEach(function(id) {
        $('#formSeniInputs').append('<input type="hidden" name="id_peserta_seni[]" value="' + id + '">');
    });
}

function cetakBrowser() {
    buildFormInputs();
    // Refresh CSRF
    $.ajax({
        url: window.location.href,
        type: 'GET',
        dataType: 'html'
    }).done(function(html) {
        var csrf = $(html).find('input[name="<?= csrf_token() ?>"]').val();
        if (csrf) $('#formBatch input[name="<?= csrf_token() ?>"]').val(csrf);
        $('#formBatch').attr('action', '<?= base_url('admin/sekretariat/sertifikat-batch/proses-cetak') ?>');
        $('#formBatch').submit();
    });
}

function renderLocal() {
    buildFormInputs();
    var btn = $('#btnRenderLocal');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

    var formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    selectedTanding.forEach(function(id) { formData.append('id_peserta_tanding[]', id); });
    selectedSeni.forEach(function(id) { formData.append('id_peserta_seni[]', id); });

    $.ajax({
        url: '<?= base_url('admin/sekretariat/sertifikat-batch/proses-cetak-local') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
    }).done(function(data) {
        btn.prop('disabled', false).html('<i class="fas fa-server me-1"></i>Render Lokal');
        if (data.status) {
            $('#renderCommand').text(data.command);
            $('#renderOutputDir').text(data.relative_output_dir);
            new bootstrap.Modal(document.getElementById('modalLokal')).show();
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal memproses.' });
        }
    }).fail(function(err) {
        btn.prop('disabled', false).html('<i class="fas fa-server me-1"></i>Render Lokal');
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal: ' + (err.statusText || 'koneksi') });
    });
}

// Copy command
$(document).on('click', '#btnCopyCommand', function() {
    var cmd = $('#renderCommand').text();
    navigator.clipboard.writeText(cmd).then(function() {
        var btn = $('#btnCopyCommand');
        btn.html('<i class="fa-solid fa-check me-1"></i> Tersalin!');
        setTimeout(function() { btn.html('<i class="fa-solid fa-copy me-1"></i> Salin Perintah'); }, 2000);
    }).catch(function() {
        var range = document.createRange();
        range.selectNode(document.getElementById('renderCommand'));
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
    });
});
</script>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card mb-3">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="fas fa-print me-2"></i><?= lang('cetak_id_card_per_kontingen') ?>
                </h6>
                <a href="<?= base_url('users/sekretariat/pencetakan_id_card') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i><?= lang('kembali') ?>
                </a>
            </div>
            <div class="card-body">
                <p class="text-sm text-muted mb-0">Pilih kontingen untuk dicetak semua peserta ID Card-nya.</p>
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
                        <label class="form-label"><i class="fas fa-expand me-1"></i>Kualitas Skala:</label>
                        <select id="qualityScale" class="form-select form-select-sm">
                            <option value="3">3× (Standar)</option>
                            <option value="4" selected>4× (Tinggi)</option>
                            <option value="6">6× (Ultra HD)</option>
                            <option value="8">8× (Cetak)</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Skala lebih tinggi = hasil lebih tajam tapi proses lebih lambat.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Kontingen -->
<div class="row mb-3">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card">
            <div class="card-body">
                <?php if (count($dataKontingen) > 0): ?>
                <div class="table-responsive">
                    <table id="tblKontingen" class="table table-hover align-middle mb-0 table-sm admin-datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center no-sort" style="width:40px;">
                                    <input type="checkbox" class="form-check-input" id="checkAllKontingen" title="Pilih semua">
                                </th>
                                <th>Kontingen</th>
                                <th class="text-center" style="width:80px;">Tanding</th>
                                <th class="text-center" style="width:80px;">Seni</th>
                                <th class="text-center" style="width:80px;">Total</th>
                                <th class="text-center no-sort" style="width:110px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataKontingen as $k): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input chk-kontingen" value="<?= $k['id_kontingen'] ?>">
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($k['nama_kontingen']) ?></td>
                                <td class="text-center"><span class="badge bg-admin-brand"><?= $k['jml_tanding'] ?></span></td>
                                <td class="text-center"><span class="badge bg-info"><?= $k['jml_seni'] ?></span></td>
                                <td class="text-center"><span class="badge bg-secondary"><?= $k['jml_total'] ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-sm" onclick="cetakSatu(<?= $k['id_kontingen'] ?>)">
                                        <i class="fas fa-print"></i> Cetak
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-building fa-3x text-muted mb-2"></i>
                    <p class="text-muted">Belum ada data kontingen.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Action Toolbar (Sticky) -->
<div id="toolbarActions" class="row sticky-bottom bg-white border-top py-2 px-2 px-md-3 gap-2" style="display:none; z-index:99;">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span id="infoTerpilih" class="badge bg-warning text-dark">
            Kontingen Terpilih: <strong id="countSelected">0</strong>
        </span>
        <div class="ms-auto d-flex gap-2">
            <button id="btnPilihSemua" class="btn btn-sm btn-outline-secondary" onclick="pilihSemua()">
                <i class="fas fa-check-square me-1"></i>Pilih Semua
            </button>
            <button id="btnBersihkan" class="btn btn-sm btn-outline-secondary" onclick="bersihkanSemua()">
                <i class="fas fa-square me-1"></i>Bersihkan
            </button>
            <button id="btnCetakTerpilih" class="btn btn-danger btn-sm" onclick="cetakTerpilih()" disabled>
                <i class="fas fa-print me-1"></i>Cetak Terpilih
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var selectedKontingen = new Set();

$(document).ready(function() {
    // Check-all
    $('#checkAllKontingen').on('change', function() {
        var checked = this.checked;
        $('tbody .chk-kontingen').each(function() {
            $(this).prop('checked', checked);
            var id = parseInt(this.value);
            checked ? selectedKontingen.add(id) : selectedKontingen.delete(id);
        });
        updateToolbar();
    });

    $(document).on('change', '.chk-kontingen', function() {
        var id = parseInt(this.value);
        this.checked ? selectedKontingen.add(id) : selectedKontingen.delete(id);
        updateToolbar();
    });

    // Re-apply state setelah DataTables redraw
    $('#tblKontingen').on('draw.dt', function() {
        $('.chk-kontingen').each(function() {
            this.checked = selectedKontingen.has(parseInt(this.value));
        });
    });
});

function updateToolbar() {
    var count = selectedKontingen.size;
    $('#countSelected').text(count);
    count > 0 ? $('#toolbarActions').show() : $('#toolbarActions').hide();
    $('#btnCetakTerpilih').prop('disabled', count === 0);
}

function pilihSemua() {
    $('tbody .chk-kontingen').each(function() {
        this.checked = true;
        selectedKontingen.add(parseInt(this.value));
    });
    $('#checkAllKontingen').prop('checked', true);
    updateToolbar();
}

function bersihkanSemua() {
    selectedKontingen.clear();
    $('input.chk-kontingen').prop('checked', false);
    $('#checkAllKontingen').prop('checked', false);
    updateToolbar();
}

function cetakSatu(idKontingen) {
    mulaiCetakIframe({ id_kontingen: [idKontingen] });
}

function cetakTerpilih() {
    var count = selectedKontingen.size;
    Swal.fire({
        title: 'Cetak ' + count + ' Kontingen?',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Mulai Cetak!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33'
    }).then(function(result) {
        if (result.isConfirmed) {
            mulaiCetakIframe({ id_kontingen: Array.from(selectedKontingen) });
        }
    });
}

function mulaiCetakIframe(dataPost) {
    Swal.fire({ title: "Menyiapkan data...", text: "Mohon tunggu.", icon: "info", showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false });

    if (!$('#iframe_cetak_id_card').length) {
        $('body').append('<iframe name="iframe_cetak_id_card" id="iframe_cetak_id_card" style="position:fixed;top:-9999px;left:-9999px;width:1200px;height:1600px;opacity:0;z-index:-1;"></iframe>');
    }

    var form = $('<form action="<?= base_url('admin/sekretariat/id-card/proses-cetak-batch') ?>" method="POST" target="iframe_cetak_id_card" style="display:none;"></form>');
    form.append('<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">');
    form.append('<input type="hidden" name="scale" value="' + $('#qualityScale').val() + '">');
    (dataPost.id_kontingen || []).forEach(function(id) { form.append('<input type="hidden" name="id_kontingen[]" value="'+id+'">'); });
    $('body').append(form);
    form.submit();
    form.remove();
}

window.addEventListener('message', function(event) {
    var data = event.data;
    if (!data || !data.type) return;
    if (data.type === 'id-card-start') {
        Swal.fire({ title: "Memulai Proses", text: "Memproses " + data.total + " ID Card...", icon: "info", showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false });
    } else if (data.type === 'id-card-progress') {
        Swal.fire({
            title: "Sedang Berjalan",
            html: 'Memproses <b>' + data.processed + '</b> dari <b>' + data.total + '</b> kartu' + (data.failed > 0 ? ' (gagal: ' + data.failed + ')' : ''),
            icon: "info",
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    } else if (data.type === 'id-card-complete') {
        var msg = 'Berhasil memproses ' + data.processed + ' dari ' + data.total + ' kartu.';
        if (data.failed && data.failed.length) {
            msg += ' Gagal: ' + data.failed.length + ' kartu.';
        }
        Swal.fire({ title: "Selesai!", text: msg, icon: "success", confirmButtonText: "Tutup" }).then(function() { bersihkanSemua(); });
    } else if (data.type === 'id-card-error') {
        Swal.fire({ title: "Error", text: data.message, icon: "error", confirmButtonText: "Tutup" });
    }
});
</script>

<?= $this->endSection() ?>

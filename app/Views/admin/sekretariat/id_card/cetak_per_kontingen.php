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
                        <label class="form-label"><i class="fas fa-expand me-1"></i>Kualitas Generate:</label>
                        <select id="qualityScale" class="form-select form-select-sm">
                            <option value="2">Cepat — 2× (preview/cek data)</option>
                            <option value="3" selected>Standar — 3× (rekomendasi batch besar)</option>
                            <option value="4">Tajam — 4× (cetak final)</option>
                            <option value="6">Ultra — 6× (batch kecil)</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Default 3× lebih cepat dan tetap tajam untuk batch besar. 4× untuk cetak final, 6× hanya batch kecil.
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
                <i class="fas fa-print me-1"></i>Cetak Browser
            </button>
            <button id="btnRenderLocal" class="btn btn-dark btn-sm" onclick="renderLocalTerpilih()" disabled>
                <i class="fas fa-server me-1"></i>Render Lokal
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
    $('#btnCetakTerpilih, #btnRenderLocal').prop('disabled', count === 0);
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

function rekomendasiScale(total) {
    var scale = parseInt($('#qualityScale').val(), 10) || 3;
    if (total > 150 && scale > 3) {
        $('#qualityScale').val('3');
        return 3;
    }
    if (total > 50 && scale > 4) {
        $('#qualityScale').val('4');
        return 4;
    }
    return scale;
}

function teksRekomendasiBatch(total, scale) {
    if (total > 150) {
        return 'Batch besar: sistem pakai 3× dan ZIP otomatis dipecah agar browser lebih stabil.';
    }
    if (total > 50 && scale >= 4) {
        return 'Batch sedang: 4× masih aman, 3× lebih cepat jika butuh banyak kartu.';
    }
    if (scale >= 6) {
        return 'Ultra 6× cocok untuk batch kecil saja. Jika lambat, turunkan ke 4× atau 3×.';
    }
    return 'Kualitas ini aman untuk generate cepat.';
}

function progressHtml(data) {
    var total = parseInt(data.total || 0, 10);
    var processed = parseInt(data.processed || 0, 10);
    var failed = Array.isArray(data.failed) ? data.failed.length : parseInt(data.failed || 0, 10);
    var done = processed + failed;
    var pct = total > 0 ? Math.round((done / total) * 100) : 0;
    var current = data.current ? '<div class="small text-muted mt-2">Sedang: ' + $('<div>').text(data.current).html() + '</div>' : '';
    return ''
        + '<div class="text-start">'
        + '<div class="d-flex justify-content-between small mb-1"><span>Progress</span><strong>' + pct + '%</strong></div>'
        + '<div class="progress" style="height:12px"><div class="progress-bar bg-danger" style="width:' + pct + '%"></div></div>'
        + '<div class="small mt-2">Berhasil <b>' + processed + '</b> / ' + total + ' kartu' + (failed ? ' · Gagal <b>' + failed + '</b>' : '') + '</div>'
        + current
        + '</div>';
}

function cetakTerpilih() {
    var count = selectedKontingen.size;
    var totalKartu = 0;
    selectedKontingen.forEach(function(id) {
        var row = $('.chk-kontingen[value="' + id + '"]').closest('tr');
        totalKartu += parseInt(row.find('td').eq(4).text(), 10) || 0;
    });
    var scale = rekomendasiScale(totalKartu);
    Swal.fire({
        title: 'Cetak ' + count + ' Kontingen?',
        html: 'Estimasi <b>' + totalKartu + '</b> ID Card, kualitas <b>' + scale + '×</b>.<br><small class="text-muted">' + teksRekomendasiBatch(totalKartu, scale) + '</small>',
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


function renderLocalTerpilih() {
    var count = selectedKontingen.size;
    var totalKartu = 0;
    selectedKontingen.forEach(function(id) {
        var row = $('.chk-kontingen[value="' + id + '"]').closest('tr');
        totalKartu += parseInt(row.find('td').eq(4).text(), 10) || 0;
    });
    var scale = rekomendasiScale(totalKartu);
    Swal.fire({
        title: 'Render Lokal ' + count + ' Kontingen?',
        html: 'Sistem akan membuat file <code>id-card.html</code>, lalu menampilkan command CLI siap pakai.<br>Estimasi <b>' + totalKartu + '</b> ID Card, kualitas <b>' + scale + '×</b>.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Buat HTML + Command CLI',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#111827'
    }).then(function(result) {
        if (result.isConfirmed) {
            mulaiRenderLocal({ id_kontingen: Array.from(selectedKontingen) });
        }
    });
}

function escapeHtml(text) {
    return $('<div>').text(text || '').html();
}

function commandPopupHtml(resp) {
    var command = resp.command || '';
    var htmlPath = resp.relative_html_path || resp.html_path || '';
    var progressFile = resp.relative_progress_file || resp.progress_file || '';
    var outputDir = resp.relative_output_dir || resp.output_dir || '';
    return ''
        + '<div class="text-start">'
        + '<div class="alert alert-success py-2 mb-3">File <b>id-card.html</b> sudah dibuat. Render ZIP lanjut via CLI.</div>'
        + '<div class="small text-muted mb-1">HTML:</div><code class="d-block text-break mb-2">' + escapeHtml(htmlPath) + '</code>'
        + '<div class="small text-muted mb-1">Output ZIP:</div><code class="d-block text-break mb-2">' + escapeHtml(outputDir) + '</code>'
        + '<div class="small text-muted mb-1">Progress file:</div><code class="d-block text-break mb-3">' + escapeHtml(progressFile) + '</code>'
        + '<div class="small text-muted mb-1">Command CLI:</div>'
        + '<pre class="bg-dark text-white text-start p-3 rounded small" style="white-space:pre-wrap;word-break:break-word;max-height:260px;overflow:auto;">' + escapeHtml(command) + '</pre>'
        + '<div class="small text-muted">Jalankan command di Terminal. Progress bisa dilihat dari file progress.json.</div>'
        + '</div>';
}

function copyRenderCommand(command) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(command).then(function() {
            Swal.showValidationMessage('Command tersalin ke clipboard.');
            setTimeout(function() { Swal.resetValidationMessage(); }, 1200);
        }).catch(function() {});
    }
}

function mulaiRenderLocal(dataPost) {
    Swal.fire({
        title: "Membuat HTML ID Card",
        text: "Mohon tunggu. Sistem menyiapkan file id-card.html.",
        icon: "info",
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false
    });

    var payload = {
        scale: $('#qualityScale').val(),
        <?= json_encode(csrf_token()) ?>: <?= json_encode(csrf_hash()) ?>
    };
    (dataPost.id_kontingen || []).forEach(function(id, idx) { payload['id_kontingen[' + idx + ']'] = id; });
    (dataPost.id_peserta_tanding || []).forEach(function(id, idx) { payload['id_peserta_tanding[' + idx + ']'] = id; });
    (dataPost.id_peserta_seni || []).forEach(function(id, idx) { payload['id_peserta_seni[' + idx + ']'] = id; });

    $.post('<?= base_url('admin/sekretariat/id-card/proses-cetak-batch-local') ?>', payload)
        .done(function(resp) {
            if (!resp || !resp.status || !resp.command) {
                Swal.fire({ title: "Gagal Membuat HTML", text: (resp && resp.message) ? resp.message : "Respons tidak valid.", icon: "error" });
                return;
            }
            Swal.fire({
                title: "HTML Siap Dirender",
                html: commandPopupHtml(resp),
                icon: "success",
                showCancelButton: true,
                confirmButtonText: "Copy Command",
                cancelButtonText: "Tutup",
                confirmButtonColor: '#111827',
                preConfirm: function() {
                    copyRenderCommand(resp.command || '');
                    return false;
                }
            }).then(function(result) {
                if (result.dismiss && typeof bersihkanSemua === 'function') {
                    bersihkanSemua();
                }
            });
        })
        .fail(function(xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.responseText || 'Request gagal.');
            Swal.fire({ title: "Gagal Membuat HTML", text: msg, icon: "error" });
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
        Swal.fire({ title: "Memulai Proses", html: progressHtml({ total: data.total, processed: 0, failed: 0 }), icon: "info", showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false });
    } else if (data.type === 'id-card-progress') {
        Swal.fire({
            title: "Sedang Berjalan",
            html: progressHtml(data),
            icon: "info",
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    } else if (data.type === 'id-card-chunk') {
        Swal.fire({
            title: "ZIP Part " + data.chunk_index + " tersimpan",
            html: progressHtml(data) + '<div class="small text-muted mt-2">Lanjut membuat part berikutnya otomatis.</div>',
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

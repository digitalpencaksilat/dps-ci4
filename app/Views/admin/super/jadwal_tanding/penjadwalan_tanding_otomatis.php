<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$gelanggang = $gelanggang ?? [];
$kelas = $kelas ?? [];
$babakOptions = $babakOptions ?? [];
$status = session('status');
$message = session('message');
$oldIdGelanggang = old('id_gelanggang') ?: [count($gelanggang) > 0 ? (string) ($gelanggang[0]->id_gelanggang ?? '') : ''];
$oldJumlahPartai = old('jumlah_partai') ?: ['10'];
$oldBabak = old('babak_pertandingan') ?: [];
$oldUrutanKelas = old('urutan_id_kelas_tanding') ?: [];
?>

<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div>
            <p class="eyebrow mb-1">Pembuatan Jadwal</p>
            <h2 class="section-title h4 mb-1">Penjadwalan Otomatis Tanding</h2>
            <p class="muted-copy mb-0">Parity CI3 untuk flow GET form dan POST generate jadwal tanding otomatis.</p>
        </div>
    </div>

    <div class="card-body px-0">
        <?php if ($message) : ?>
            <div class="alert <?= $status ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= nl2br(esc(is_array($message) ? implode("\n", $message) : (string) $message)) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('admin/super/jadwal-tanding/buat-jadwal-tanding-otomatis') ?>">
            <?= csrf_field() ?>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= esc((string) old('tanggal', date('Y-m-d'))) ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Jam Mulai</label>
                    <input type="time" name="jam_mulai" class="form-control" value="<?= esc((string) old('jam_mulai', '08:00')) ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Jam Selesai</label>
                    <input type="time" name="jam_selesai" class="form-control" value="<?= esc((string) old('jam_selesai', '17:00')) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" value="<?= esc((string) old('keterangan')) ?>" placeholder="Opsional">
                </div>
            </div>

            <div class="admin-card mb-3">
                <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                    <h3 class="section-title h6 mb-1">Pengaturan Gelanggang</h3>
                    <p class="muted-copy small mb-0">Field parity CI3: <code>id_gelanggang[]</code> dan <code>jumlah_partai[]</code>.</p>
                </div>
                <div class="card-body px-0">
                    <div id="gelanggangRows" class="d-flex flex-column gap-2">
                        <?php foreach ($oldIdGelanggang as $i => $idGelanggang) : ?>
                            <div class="row g-2 gelanggang-row">
                                <div class="col-12 col-md-8">
                                    <select name="id_gelanggang[]" class="form-select" required>
                                        <option value="">- Pilih Gelanggang -</option>
                                        <?php foreach ($gelanggang as $g) : ?>
                                            <option value="<?= esc((string) $g->id_gelanggang) ?>" <?= (string) $idGelanggang === (string) $g->id_gelanggang ? 'selected' : '' ?>>
                                                <?= esc((string) ($g->nama_gelanggang ?? ('Gelanggang ' . $g->id_gelanggang))) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-8 col-md-3">
                                    <input type="number" min="1" name="jumlah_partai[]" class="form-control" value="<?= esc((string) ($oldJumlahPartai[$i] ?? '10')) ?>" placeholder="Jumlah partai" required>
                                </div>
                                <div class="col-4 col-md-1 d-grid">
                                    <button type="button" class="btn btn-outline-danger btn-remove-row">×</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" id="btnTambahGelanggang">Tambah Gelanggang</button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-6">
                    <div class="admin-card h-100">
                        <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                            <h3 class="section-title h6 mb-1">Babak Pertandingan</h3>
                            <p class="muted-copy small mb-0">Field parity CI3: <code>babak_pertandingan[]</code>.</p>
                        </div>
                        <div class="card-body px-0">
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($babakOptions as $babak) : ?>
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="babak_pertandingan[]" value="<?= esc((string) $babak) ?>" <?= in_array($babak, $oldBabak, true) ? 'checked' : '' ?>>
                                        <span class="form-check-label"><?= esc((string) $babak) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="admin-card h-100">
                        <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                            <h3 class="section-title h6 mb-1">Jenis Penjadwalan</h3>
                            <p class="muted-copy small mb-0">Field parity CI3: <code>jenis_penjadwalan</code>.</p>
                        </div>
                        <div class="card-body px-0">
                            <?php $jenis = (string) old('jenis_penjadwalan', 'prestasi'); ?>
                            <div class="d-flex flex-column gap-2">
                                <label class="radio-card">
                                    <input type="radio" name="jenis_penjadwalan" value="prestasi" <?= $jenis === 'prestasi' ? 'checked' : '' ?>>
                                    <span class="radio-card__title">Prestasi</span>
                                </label>
                                <label class="radio-card">
                                    <input type="radio" name="jenis_penjadwalan" value="pemasalan" <?= $jenis === 'pemasalan' ? 'checked' : '' ?>>
                                    <span class="radio-card__title">Pemasalan</span>
                                </label>
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Jumlah Selang-seling (Pemasalan)</label>
                                    <input type="number" min="1" name="jumlah_selang_seling" class="form-control" value="<?= esc((string) old('jumlah_selang_seling', '2')) ?>">
                                    <div class="form-text">Parity CI3: berapa pool awal yang diacak ulang.</div>
                                </div>
                            </div>

                            <hr>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="langsung_buat_pdf" id="langsungBuatPdf" value="1" <?= old('langsung_buat_pdf') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="langsungBuatPdf">Langsung buat PDF</label>
                            </div>
                            <div>
                                <label class="form-label">PDF Library</label>
                                <select name="pdf_library" class="form-select">
                                    <option value="">- Pilih Library -</option>
                                    <option value="mpdf" <?= old('pdf_library') === 'mpdf' ? 'selected' : '' ?>>mPDF</option>
                                    <option value="dompdf" <?= old('pdf_library') === 'dompdf' ? 'selected' : '' ?>>dompdf</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mb-3">
                <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                    <h3 class="section-title h6 mb-1">Urutan Kelas Tanding</h3>
                    <p class="muted-copy small mb-0">Field parity CI3: <code>urutan_id_kelas_tanding[]</code>. Pilih sesuai prioritas urutan scheduling.</p>
                </div>
                <div class="card-body px-0">
                    <div class="admin-table-wrap">
                        <div class="table-shell admin-table-scroller" style="max-height: 360px; overflow: auto;">
                            <table class="table admin-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 44px;">Pilih</th>
                                        <th>ID</th>
                                        <th>Label</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kelas as $row) : ?>
                                        <tr>
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="urutan_id_kelas_tanding[]" value="<?= esc((string) $row->id_kelas_tanding) ?>" <?= in_array((string) $row->id_kelas_tanding, array_map('strval', $oldUrutanKelas), true) ? 'checked' : '' ?>>
                                            </td>
                                            <td><?= esc((string) $row->id_kelas_tanding) ?></td>
                                            <td><?= esc((string) ($row->label ?? ('Kelas ' . $row->id_kelas_tanding))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-danger rounded-pill">Generate Jadwal Otomatis</button>
                <a href="<?= base_url('admin/super/jadwal-tanding') ?>" class="btn btn-outline-secondary rounded-pill">Kembali ke Jadwal Tanding</a>
            </div>
        </form>
    </div>
</div>

<template id="gelanggangRowTemplate">
    <div class="row g-2 gelanggang-row">
        <div class="col-12 col-md-8">
            <select name="id_gelanggang[]" class="form-select" required>
                <option value="">- Pilih Gelanggang -</option>
                <?php foreach ($gelanggang as $g) : ?>
                    <option value="<?= esc((string) $g->id_gelanggang) ?>"><?= esc((string) ($g->nama_gelanggang ?? ('Gelanggang ' . $g->id_gelanggang))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-8 col-md-3">
            <input type="number" min="1" name="jumlah_partai[]" class="form-control" value="10" placeholder="Jumlah partai" required>
        </div>
        <div class="col-4 col-md-1 d-grid">
            <button type="button" class="btn btn-outline-danger btn-remove-row">×</button>
        </div>
    </div>
</template>

<style>
.radio-card{display:block;border:1px solid rgba(0,0,0,.12);border-radius:14px;padding:12px 12px;cursor:pointer;background:#fff;}
.radio-card input{margin-right:8px;}
.radio-card__title{display:block;font-weight:700;}
.radio-card:has(input:checked){border-color:rgba(220,53,69,.5);box-shadow:0 0 0 .2rem rgba(220,53,69,.12);}
</style>

<script>
(function(){
    const rows = document.getElementById('gelanggangRows');
    const template = document.getElementById('gelanggangRowTemplate');
    const addBtn = document.getElementById('btnTambahGelanggang');

    function bindRemove(scope){
        scope.querySelectorAll('.btn-remove-row').forEach((btn) => {
            btn.onclick = function(){
                const list = rows.querySelectorAll('.gelanggang-row');
                if (list.length <= 1) return;
                btn.closest('.gelanggang-row').remove();
            };
        });
    }

    addBtn?.addEventListener('click', function(){
        const clone = template.content.cloneNode(true);
        rows.appendChild(clone);
        bindRemove(rows);
    });

    bindRemove(document);
})();
</script>
<?= $this->endSection() ?>
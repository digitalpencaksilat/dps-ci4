<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$gelanggang = $gelanggang ?? [];
$subKategori = $subKategori ?? [];
$babakOptions = $babakOptions ?? [];
?>

<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <p class="eyebrow mb-1">Pembuatan Jadwal</p>
                <h2 class="section-title h4 mb-1">Penjadwalan Otomatis Seni</h2>
                <p class="muted-copy mb-0">Migrasi CI4 untuk flow penjadwalan otomatis seni pool dan battle dengan payload parity ke project CI3.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/super/jadwal-seni') ?>">Daftar Jadwal Seni</a>
                <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/super/drawing-seni') ?>">Drawing Seni</a>
            </div>
        </div>
    </div>

    <div class="card-body px-0">
        <ul class="nav nav-pills mb-3 gap-2" id="seniAutoTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="btn btn-outline-danger active" id="pool-tab" data-bs-toggle="pill" data-bs-target="#pool-pane" type="button" role="tab">Sistem Pool</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="btn btn-outline-danger" id="battle-tab" data-bs-toggle="pill" data-bs-target="#battle-pane" type="button" role="tab">Sistem Battle</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="pool-pane" role="tabpanel" aria-labelledby="pool-tab">
                <div class="admin-card">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Penjadwalan Seni Otomatis Sistem Pool</h3>
                        <p class="muted-copy small mb-0">Parity CI3 `buat_jadwal_seni_sistem_pool_otomatis`: pilih urutan sub kategori, distribusikan kapasitas pool per gelanggang, lalu generate jadwal + detail + penugasan juri.</p>
                    </div>
                    <div class="card-body px-0">
                        <form method="post" action="<?= base_url('admin/super/jadwal-seni/buat-jadwal-seni-pool-otomatis') ?>" id="formPool">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-12 col-lg-4">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" value="<?= esc((string) old('tanggal')) ?>" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control" name="jam_mulai" value="<?= esc((string) old('jam_mulai')) ?>" required>
                                </div>
                                <div class="col-12 col-lg-4">
                                    <label class="form-label">Jam Selesai</label>
                                    <input type="time" class="form-control" name="jam_selesai" value="<?= esc((string) old('jam_selesai')) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" class="form-control" name="keterangan" value="<?= esc((string) old('keterangan')) ?>" placeholder="Contoh: Sesi pagi seni pool hari 1">
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-semibold">Urutan Sub Kategori Seni</div>
                                        <div class="small text-muted">Urutan checkbox dipakai sebagai `urutan_id_sub_kategori_seni` seperti di CI3.</div>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-check-all="#pool-subkategori .pool-sub">Pilih semua</button>
                                </div>
                                <div class="table-shell admin-table-scroller" style="max-height: 320px; overflow-y: auto;">
                                    <table class="table admin-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:44px;">Pilih</th>
                                                <th>Kategori Usia</th>
                                                <th>JK</th>
                                                <th>Jenis</th>
                                                <th>Nama Seni</th>
                                                <th>Sistem</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pool-subkategori">
                                            <?php foreach ($subKategori as $row) : ?>
                                                <tr>
                                                    <td><input class="form-check-input pool-sub" type="checkbox" name="urutan_id_sub_kategori_seni[]" value="<?= (int) $row->id_sub_kategori_seni ?>" <?= ($row->sistem_penampilan ?? '') !== 'pool' ? 'disabled' : '' ?>></td>
                                                    <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                                                    <td><?= esc((string) ($row->jenis_kelamin ?? '-')) ?></td>
                                                    <td><?= esc((string) ($row->jenis_seni ?? '-')) ?></td>
                                                    <td class="fw-semibold"><?= esc((string) ($row->nama_seni ?? '-')) ?></td>
                                                    <td><?= esc((string) ($row->sistem_penampilan ?? '-')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="fw-semibold mb-2">Distribusi Gelanggang & Kapasitas Pool</div>
                                <div class="row g-3">
                                    <?php foreach ($gelanggang as $g) : ?>
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <div class="venue-card">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input venue-pool-check" type="checkbox" id="pool-g-<?= (int) $g->id_gelanggang ?>" name="id_gelanggang[]" value="<?= (int) $g->id_gelanggang ?>">
                                                    <label class="form-check-label fw-semibold" for="pool-g-<?= (int) $g->id_gelanggang ?>"><?= esc((string) ($g->nama_gelanggang ?? 'Gelanggang')) ?></label>
                                                </div>
                                                <label class="form-label small text-muted">Jumlah Pool</label>
                                                <input type="number" min="0" class="form-control" name="jumlah_pool[<?= (int) $g->id_gelanggang ?>]" value="<?= esc((string) old('jumlah_pool.' . (int) $g->id_gelanggang, '0')) ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-md-6">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="langsung_buat_pdf" id="poolPdf" value="1" <?= old('langsung_buat_pdf') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="poolPdf">Langsung buat PDF setelah generate</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">PDF Library</label>
                                    <select class="form-select" name="pdf_library">
                                        <option value="mpdf">mPDF</option>
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-warning small mt-3 mb-0" role="alert">
                                Generate pool akan membuat `jadwal_seni`, `detail_jadwal_seni`, `penampilan_seni` bila belum ada, lalu menugaskan juri per gelanggang.
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="submit" class="btn btn-danger rounded-pill">Buat Jadwal Seni Pool</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="battle-pane" role="tabpanel" aria-labelledby="battle-tab">
                <div class="admin-card">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Penjadwalan Seni Otomatis Sistem Battle</h3>
                        <p class="muted-copy small mb-0">Parity CI3 `buat_jadwal_seni_battle_otomatis`: pilih jenis penjadwalan, babak battle, urutan sub kategori, kapasitas partai per gelanggang, lalu generate jadwal battle.</p>
                    </div>
                    <div class="card-body px-0">
                        <form method="post" action="<?= base_url('admin/super/jadwal-seni/buat-jadwal-seni-battle-otomatis') ?>" id="formBattle">
                            <?= csrf_field() ?>
                            <div class="row g-3">
                                <div class="col-12 col-lg-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" value="<?= esc((string) old('tanggal')) ?>" required>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control" name="jam_mulai" value="<?= esc((string) old('jam_mulai')) ?>" required>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label">Jam Selesai</label>
                                    <input type="time" class="form-control" name="jam_selesai" value="<?= esc((string) old('jam_selesai')) ?>" required>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <label class="form-label">Jenis Penjadwalan</label>
                                    <select class="form-select" name="jenis_penjadwalan" required>
                                        <?php $jenisBattle = (string) old('jenis_penjadwalan', 'prestasi'); ?>
                                        <option value="prestasi" <?= $jenisBattle === 'prestasi' ? 'selected' : '' ?>>Prestasi</option>
                                        <option value="pemasalan_seling_1" <?= $jenisBattle === 'pemasalan_seling_1' ? 'selected' : '' ?>>Pemasalan Seling 1</option>
                                        <option value="pemasalan_seling_2" <?= $jenisBattle === 'pemasalan_seling_2' ? 'selected' : '' ?>>Pemasalan Seling 2</option>
                                        <option value="pemasalan_seling_3" <?= $jenisBattle === 'pemasalan_seling_3' ? 'selected' : '' ?>>Pemasalan Seling 3</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" class="form-control" name="keterangan" value="<?= esc((string) old('keterangan')) ?>" placeholder="Contoh: Sesi battle semifinal dan final">
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="fw-semibold mb-2">Babak Battle</div>
                                <div class="row g-2">
                                    <?php foreach ($babakOptions as $babak) : ?>
                                        <div class="col-6 col-md-4 col-xl-3">
                                            <label class="check-card">
                                                <input type="checkbox" name="babak_battle_seni[]" value="<?= esc($babak) ?>" <?= in_array($babak, (array) old('babak_battle_seni', []), true) ? 'checked' : '' ?>>
                                                <span><?= esc($babak) ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-semibold">Urutan Sub Kategori Seni</div>
                                        <div class="small text-muted">Hanya sub kategori dengan sistem battle yang aktif.</div>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-check-all="#battle-subkategori .battle-sub">Pilih semua</button>
                                </div>
                                <div class="table-shell admin-table-scroller" style="max-height: 320px; overflow-y: auto;">
                                    <table class="table admin-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:44px;">Pilih</th>
                                                <th>Kategori Usia</th>
                                                <th>JK</th>
                                                <th>Jenis</th>
                                                <th>Nama Seni</th>
                                                <th>Sistem</th>
                                            </tr>
                                        </thead>
                                        <tbody id="battle-subkategori">
                                            <?php foreach ($subKategori as $row) : ?>
                                                <tr>
                                                    <td><input class="form-check-input battle-sub" type="checkbox" name="urutan_id_sub_kategori_seni[]" value="<?= (int) $row->id_sub_kategori_seni ?>" <?= ($row->sistem_penampilan ?? '') !== 'battle' ? 'disabled' : '' ?>></td>
                                                    <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                                                    <td><?= esc((string) ($row->jenis_kelamin ?? '-')) ?></td>
                                                    <td><?= esc((string) ($row->jenis_seni ?? '-')) ?></td>
                                                    <td class="fw-semibold"><?= esc((string) ($row->nama_seni ?? '-')) ?></td>
                                                    <td><?= esc((string) ($row->sistem_penampilan ?? '-')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="fw-semibold mb-2">Distribusi Gelanggang & Kapasitas Partai</div>
                                <div class="row g-3">
                                    <?php foreach ($gelanggang as $g) : ?>
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <div class="venue-card">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input venue-battle-check" type="checkbox" id="battle-g-<?= (int) $g->id_gelanggang ?>" name="id_gelanggang[]" value="<?= (int) $g->id_gelanggang ?>">
                                                    <label class="form-check-label fw-semibold" for="battle-g-<?= (int) $g->id_gelanggang ?>"><?= esc((string) ($g->nama_gelanggang ?? 'Gelanggang')) ?></label>
                                                </div>
                                                <label class="form-label small text-muted">Jumlah Partai</label>
                                                <input type="number" min="0" class="form-control" name="jumlah_partai[<?= (int) $g->id_gelanggang ?>]" value="<?= esc((string) old('jumlah_partai.' . (int) $g->id_gelanggang, '0')) ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-md-6">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="langsung_buat_pdf" id="battlePdf" value="1" <?= old('langsung_buat_pdf') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="battlePdf">Langsung buat PDF setelah generate</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">PDF Library</label>
                                    <select class="form-select" name="pdf_library">
                                        <option value="mpdf">mPDF</option>
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-warning small mt-3 mb-0" role="alert">
                                Generate battle akan membuat `jadwal_seni` + `detail_jadwal_seni` untuk `id_battle_seni`, serta menugaskan juri pada penampilan merah dan biru yang terhubung ke battle tersebut.
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="submit" class="btn btn-danger rounded-pill">Buat Jadwal Seni Battle</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.venue-card{border:1px solid rgba(0,0,0,.12);border-radius:14px;padding:14px;background:#fff;height:100%;}
.check-card{display:flex;align-items:center;gap:.5rem;border:1px solid rgba(0,0,0,.12);border-radius:12px;padding:12px;background:#fff;cursor:pointer;height:100%;}
.check-card input{margin:0;}
.check-card:has(input:checked){border-color:rgba(220,53,69,.5);box-shadow:0 0 0 .2rem rgba(220,53,69,.12);}
</style>

<script>
(function(){
    const qsa = (s, root=document) => Array.from(root.querySelectorAll(s));
    qsa('[data-check-all]').forEach(function(btn){
        btn.addEventListener('click', function(){
            const selector = btn.getAttribute('data-check-all');
            qsa(selector).forEach(function(el){ if (!el.disabled) el.checked = true; });
        });
    });

    const ensureChecked = function(selector, message) {
        return qsa(selector).some(function(el){ return !el.disabled && el.checked; }) || (alert(message), false);
    };
    const ensureBattleRound = function() {
        return qsa('input[name="babak_battle_seni[]"]').some(function(el){ return el.checked; }) || (alert('Pilih minimal satu babak battle.'), false);
    };
    const ensureVenue = function(selector, message) {
        return qsa(selector).some(function(el){ return el.checked; }) || (alert(message), false);
    };

    const formPool = document.getElementById('formPool');
    if (formPool) {
        formPool.addEventListener('submit', function(e){
            if (!ensureChecked('.pool-sub', 'Pilih minimal satu sub kategori pool.')) { e.preventDefault(); return; }
            if (!ensureVenue('.venue-pool-check', 'Pilih minimal satu gelanggang pool.')) { e.preventDefault(); return; }
            e.preventDefault();
            Swal.fire({
                title: 'Buat Jadwal Seni Pool Otomatis?',
                text: 'Jadwal seni pool akan dibuat otomatis untuk sub kategori dan gelanggang yang dipilih.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buat',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#6b7280'
            }).then((result) => { if (result.isConfirmed) formPool.submit(); });
        });
    }

    const formBattle = document.getElementById('formBattle');
    if (formBattle) {
        formBattle.addEventListener('submit', function(e){
            if (!ensureChecked('.battle-sub', 'Pilih minimal satu sub kategori battle.')) { e.preventDefault(); return; }
            if (!ensureBattleRound()) { e.preventDefault(); return; }
            if (!ensureVenue('.venue-battle-check', 'Pilih minimal satu gelanggang battle.')) { e.preventDefault(); return; }
            e.preventDefault();
            Swal.fire({
                title: 'Buat Jadwal Seni Battle Otomatis?',
                text: 'Jadwal seni battle akan dibuat otomatis untuk sub kategori, babak, dan gelanggang yang dipilih.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buat',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#6b7280'
            }).then((result) => { if (result.isConfirmed) formBattle.submit(); });
        });
    }
})();
</script>
<?= $this->endSection() ?>

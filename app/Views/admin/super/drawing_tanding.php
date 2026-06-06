<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$kategoriRows = $kategoriRows ?? [];
$summary = $summary ?? [];
?>

<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <p class="eyebrow mb-1">Pembuatan Jadwal</p>
                <h2 class="section-title h4 mb-1">Drawing & Bagan Tanding</h2>
                <p class="muted-copy mb-0">Kelola drawing berdasarkan kategori (struktur CI3), dengan UI yang lebih ringkas dan aman untuk operasi massal.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="badge bg-light text-dark border">Kategori: <?= esc((string) ($summary['kategori'] ?? count($kategoriRows))) ?></div>
                <div class="badge bg-light text-dark border">Pool: <?= esc((string) ($summary['pool'] ?? '-')) ?></div>
                <div class="badge bg-light text-dark border">Pool 1 Peserta: <?= esc((string) ($summary['satuPeserta'] ?? '-')) ?></div>
            </div>
        </div>
    </div>

    <div class="card-body px-0">
        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="admin-card h-100">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Pendistribusian Peserta Tanding</h3>
                        <p class="muted-copy small mb-0">Pilih kategori lalu jalankan metode distribusi. Ini mengikuti struktur CI3, tetapi tetap menjaga konsistensi gaya CI4.</p>
                    </div>
                    <div class="card-body px-0">
                        <form method="post" action="<?= base_url('admin/super/drawing-tanding/distribusikan-peserta') ?>" id="formDistribusiTanding">
                            <?= csrf_field() ?>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" id="selectAllDistribusi" type="checkbox">
                                    <label class="form-check-label fw-semibold" for="selectAllDistribusi">Pilih semua kategori</label>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearDistribusi">Kosongkan</button>
                            </div>

                            <div class="admin-table-wrap mb-3">
                                <div class="table-shell admin-table-scroller" style="max-height: 320px; overflow: auto;">
                                    <table class="table admin-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 44px;">Pilih</th>
                                                <th>Kategori Usia</th>
                                                <th>JK</th>
                                                <th class="text-end">Peserta</th>
                                                <th class="text-end">Pool</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kategoriRows as $row) : ?>
                                                <tr>
                                                    <td>
                                                        <input class="form-check-input checkbox-distribusi" type="checkbox" name="id_kategori_lomba[]" value="<?= esc((string) $row->id_kategori_lomba) ?>">
                                                    </td>
                                                    <td class="fw-semibold"><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                                                    <td><?= esc((string) ($row->jenis_kelamin ?? '-')) ?></td>
                                                    <td class="text-end"><?= esc((string) ($row->jumlah_peserta_tanding ?? 0)) ?></td>
                                                    <td class="text-end"><?= esc((string) ($row->jumlah_pool ?? 0)) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="fw-semibold mb-1">Metode distribusi</div>
                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <label class="radio-card">
                                            <input type="radio" name="mode" value="prestasi" checked>
                                            <span class="radio-card__title">Prestasi</span>
                                            <span class="radio-card__desc">Minimalkan potensi kontingen bertemu sendiri (baseline paling aman).</span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="radio-card">
                                            <input type="radio" name="mode" value="pemasalan">
                                            <span class="radio-card__title">Pemasalan</span>
                                            <span class="radio-card__desc">Distribusi berurutan berdasarkan berat badan ke pool (parity CI3).</span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="radio-card">
                                            <input type="radio" name="mode" value="komposisi_seimbang">
                                            <span class="radio-card__title">Komposisi Seimbang</span>
                                            <span class="radio-card__desc">Distribusi berdasarkan BB/TB, coba hindari kontingen sama dalam 1 pool (parity CI3).</span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="radio-card">
                                            <input type="radio" name="mode" value="komposisi_lengkap">
                                            <span class="radio-card__title">Komposisi Lengkap</span>
                                            <span class="radio-card__desc">Distribusi BB/TB/Tgl Lahir, coba hindari kontingen sama dalam 1 pool (parity CI3).</span>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-danger rounded-pill" id="btnDistribusiTanding" <?= empty($kategoriRows) ? 'disabled' : '' ?>>Distribusikan</button>
                                <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/super/generate-bagan-tanding-dari-jadwal') ?>">Generate Bagan dari Jadwal</a>
                                <a class="btn btn-outline-danger rounded-pill" href="<?= base_url('admin/super/drawing-tanding/laporan-hasil-drawing-bagan') ?>">Laporan Hasil Drawing</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="admin-card h-100">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Acak Bagan</h3>
                        <p class="muted-copy small mb-0">Acak ulang bagan untuk semua pool di kategori terpilih. Toggle "Random" akan memakai seed full random (Persilat style).</p>
                    </div>
                    <div class="card-body px-0">
                        <form method="post" action="<?= base_url('admin/super/drawing-tanding/acak-bagan') ?>" id="formAcakBaganTanding">
                            <?= csrf_field() ?>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" id="selectAllBagan" type="checkbox">
                                    <label class="form-check-label fw-semibold" for="selectAllBagan">Pilih semua kategori</label>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearBagan">Kosongkan</button>
                            </div>

                            <div class="admin-table-wrap mb-3">
                                <div class="table-shell admin-table-scroller" style="max-height: 320px; overflow: auto;">
                                    <table class="table admin-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 44px;">Pilih</th>
                                                <th>Kategori</th>
                                                <th class="text-end">Partai</th>
                                                <th class="text-end">Pool</th>
                                                <th class="text-end" style="width: 120px;">Random</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kategoriRows as $row) : ?>
                                                <tr>
                                                    <td>
                                                        <input class="form-check-input checkbox-bagan" type="checkbox" name="id_kategori_lomba_bagan[]" value="<?= esc((string) $row->id_kategori_lomba) ?>">
                                                    </td>
                                                    <td class="fw-semibold"><?= esc(trim((string) ($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? ''))) ?></td>
                                                    <td class="text-end"><?= esc((string) ($row->jumlah_partai_tanding ?? 0)) ?></td>
                                                    <td class="text-end"><?= esc((string) ($row->jumlah_pool ?? 0)) ?></td>
                                                    <td class="text-end">
                                                        <div class="form-check form-switch d-inline-flex align-items-center justify-content-end">
                                                            <input class="form-check-input" type="checkbox" id="random<?= (int) $row->id_kategori_lomba ?>" name="random_kategori_lomba_<?= (int) $row->id_kategori_lomba ?>" value="1">
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-warning small mb-3" role="alert">
                                <strong>Perhatian:</strong> Acak ulang akan mengganti partai/bagan lama yang belum dijadwalkan. Jika sudah ada pertandingan yang terkunci (skor/pemenang), sebaiknya jangan acak ulang.
                            </div>

                            <button type="submit" class="btn btn-outline-danger rounded-pill" id="btnAcakBagan" <?= empty($kategoriRows) ? 'disabled' : '' ?>>Acak Bagan</button>
                        </form>

                        <hr class="my-4">

                        <div class="admin-card">
                            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                                <h3 class="section-title h6 mb-1">Mode Manual (Opsional)</h3>
                                <p class="muted-copy small mb-0">Fitur ini ada di CI3. Di CI4 akan diaktifkan setelah algoritma distribusi parity tersedia.</p>
                            </div>
                            <div class="card-body px-0">
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ([3,5,7,10,13] as $t) : ?>
                                        <form method="post" action="<?= base_url('admin/super/drawing-tanding/distribusikan-tanpa-lawan/' . $t) ?>" onsubmit="return confirmAdminAction(this, 'Mode Pindah Kelas Manual?', 'Distribusi tanpa lawan akan dijalankan dengan toleransi <?= $t ?> kg.', 'Ya, Jalankan')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Toleransi <?= $t ?>kg</button>
                                        </form>
                                    <?php endforeach; ?>
                                    <form method="post" action="<?= base_url('admin/super/drawing-tanding/pisahkan-kontingen-sendiri') ?>" onsubmit="return confirmAdminAction(this, 'Pisahkan Kontingen Sendiri?', 'Peserta dari kontingen yang sama akan dipisahkan untuk semua kelas.', 'Ya, Jalankan')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Pisah Kontingen Sendiri</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($kategoriRows)) : ?>
            <div class="text-center muted-copy py-4">Belum ada kategori tanding.</div>
        <?php endif; ?>
    </div>
</div>

<style>
.radio-card{display:block;border:1px solid rgba(0,0,0,.12);border-radius:14px;padding:12px 12px;cursor:pointer;background:#fff;}
.radio-card input{margin-right:8px;}
.radio-card__title{display:block;font-weight:700;}
.radio-card__desc{display:block;color:rgba(0,0,0,.6);font-size:.875rem;line-height:1.25rem;}
.radio-card:has(input:checked){border-color:rgba(220,53,69,.5);box-shadow:0 0 0 .2rem rgba(220,53,69,.12);}
</style>

<script>
(function(){
    const qs = (s, root=document) => root.querySelector(s);
    const qsa = (s, root=document) => Array.from(root.querySelectorAll(s));

    const selectAllDistribusi = qs('#selectAllDistribusi');
    const distribusiChecks = () => qsa('.checkbox-distribusi');
    const selectAllBagan = qs('#selectAllBagan');
    const baganChecks = () => qsa('.checkbox-bagan');

    const syncSelectAll = (master, items) => {
        if (!master) return;
        const list = items();
        if (list.length === 0) return;
        master.checked = list.every(i => i.checked);
        master.indeterminate = !master.checked && list.some(i => i.checked);
    };

    if (selectAllDistribusi) {
        selectAllDistribusi.addEventListener('change', function(){
            distribusiChecks().forEach(i => i.checked = this.checked);
        });
        distribusiChecks().forEach(i => i.addEventListener('change', () => syncSelectAll(selectAllDistribusi, distribusiChecks)));
        syncSelectAll(selectAllDistribusi, distribusiChecks);
    }

    if (selectAllBagan) {
        selectAllBagan.addEventListener('change', function(){
            baganChecks().forEach(i => i.checked = this.checked);
        });
        baganChecks().forEach(i => i.addEventListener('change', () => syncSelectAll(selectAllBagan, baganChecks)));
        syncSelectAll(selectAllBagan, baganChecks);
    }

    const clearDistribusi = qs('#btnClearDistribusi');
    if (clearDistribusi) {
        clearDistribusi.addEventListener('click', function(){
            distribusiChecks().forEach(i => i.checked = false);
            syncSelectAll(selectAllDistribusi, distribusiChecks);
        });
    }

    const clearBagan = qs('#btnClearBagan');
    if (clearBagan) {
        clearBagan.addEventListener('click', function(){
            baganChecks().forEach(i => i.checked = false);
            syncSelectAll(selectAllBagan, baganChecks);
        });
    }

    const formDistribusi = qs('#formDistribusiTanding');
    if (formDistribusi) {
        formDistribusi.addEventListener('submit', function(e){
            const picked = distribusiChecks().some(i => i.checked);
            if (!picked) {
                e.preventDefault();
                alert('Pilih minimal satu kategori.');
                return;
            }
            e.preventDefault();
            Swal.fire({
                title: 'Distribusi Peserta?',
                text: 'Distribusi peserta akan dijalankan untuk kategori terpilih.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Jalankan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#6b7280'
            }).then((result) => { if (result.isConfirmed) formDistribusi.submit(); });
        });
    }

    const formBagan = qs('#formAcakBaganTanding');
    if (formBagan) {
        formBagan.addEventListener('submit', function(e){
            const picked = baganChecks().some(i => i.checked);
            if (!picked) {
                e.preventDefault();
                alert('Pilih minimal satu kategori.');
                return;
            }
            e.preventDefault();
            Swal.fire({
                title: 'Acak Ulang Bagan?',
                text: 'Bagan akan diacak ulang untuk semua pool pada kategori terpilih.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Acak',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#6b7280'
            }).then((result) => { if (result.isConfirmed) formBagan.submit(); });
        });
    }
})();
</script>
<?= $this->endSection() ?>


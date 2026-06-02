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
                <h2 class="section-title h4 mb-1">Drawing & Bagan Seni</h2>
                <p class="muted-copy mb-0">Flow bulk berbasis kategori seperti CI3, dengan kontrol nomor undi dan acak bagan battle dalam satu halaman.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="badge bg-light text-dark border">Kategori: <?= esc((string) ($summary['kategori'] ?? count($kategoriRows))) ?></div>
                <div class="badge bg-light text-dark border">Pool: <?= esc((string) ($summary['pool'] ?? '-')) ?></div>
                <div class="badge bg-light text-dark border">Sub Battle: <?= esc((string) ($summary['battlePool'] ?? '-')) ?></div>
            </div>
        </div>
    </div>

    <div class="card-body px-0">
        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <div class="admin-card h-100">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Distribusi Kelompok Peserta Seni</h3>
                        <p class="muted-copy small mb-0">Pilih kategori seni, lalu pilih cara pemakaian nomor undi.</p>
                    </div>
                    <div class="card-body px-0">
                        <form method="post" action="<?= base_url('admin/super/drawing-seni/distribusikan-kelompok') ?>" id="formDistribusiSeni">
                            <?= csrf_field() ?>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" id="selectAllDistribusi" type="checkbox">
                                    <label class="form-check-label fw-semibold" for="selectAllDistribusi">Pilih semua kategori</label>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearDistribusi">Kosongkan</button>
                            </div>

                            <div class="admin-table-wrap mb-3">
                                <div class="table-shell admin-table-scroller" style="max-height: 320px; overflow-y: auto;">
                                    <table class="table admin-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 44px;">Pilih</th>
                                                <th>Kategori Usia</th>
                                                <th>JK</th>
                                                <th class="text-end">Kelompok</th>
                                                <th class="text-end">Pool</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kategoriRows as $row) : ?>
                                                <tr>
                                                    <td><input class="form-check-input checkbox-distribusi" type="checkbox" name="id_kategori_lomba[]" value="<?= esc((string) $row->id_kategori_lomba) ?>"></td>
                                                    <td class="fw-semibold"><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                                                    <td><?= esc((string) ($row->jenis_kelamin ?? '-')) ?></td>
                                                    <td class="text-end"><?= esc((string) ($row->jumlah_kelompok ?? 0)) ?></td>
                                                    <td class="text-end"><?= esc((string) ($row->jumlah_pool ?? 0)) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold mb-1">Nomor undi</div>
                                <div class="row g-2">
                                    <div class="col-12 col-md-4">
                                        <label class="radio-card">
                                            <input type="radio" name="mode_nomor_undi" value="acak_ulang" checked>
                                            <span class="radio-card__title">Acak ulang</span>
                                            <span class="radio-card__desc">Isi ulang nomor undi per pool.</span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="radio-card">
                                            <input type="radio" name="mode_nomor_undi" value="gunakan_nomor_undi">
                                            <span class="radio-card__title">Gunakan existing</span>
                                            <span class="radio-card__desc">Tidak mengubah nomor undi tersimpan.</span>
                                        </label>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="radio-card">
                                            <input type="radio" name="mode_nomor_undi" value="pisah_kontingen">
                                            <span class="radio-card__title">Pisah kontingen</span>
                                            <span class="radio-card__desc">Round-robin per kontingen agar kelompok satu kontingen tersebar antarpool.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning small mb-3" role="alert">
                                <strong>Catatan:</strong> Mode pisah kontingen akan mengubah pembagian pool (round-robin per kontingen) dan mengisi ulang nomor undi. Jika sebagian pool sudah dijadwalkan, proses akan ditolak.
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-danger rounded-pill" <?= empty($kategoriRows) ? 'disabled' : '' ?>>Distribusikan</button>
                                <button type="submit" formaction="<?= base_url('admin/super/drawing-seni/beri-nomor-undi') ?>" class="btn btn-outline-secondary rounded-pill" <?= empty($kategoriRows) ? 'disabled' : '' ?>>Isi Nomor Undi Saja</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="admin-card h-100">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Acak Bagan Battle Seni</h3>
                        <p class="muted-copy small mb-0">Memproses semua pool battle pada kategori terpilih. Toggle random akan memakai mode full random.</p>
                    </div>
                    <div class="card-body px-0">
                        <form method="post" action="<?= base_url('admin/super/drawing-seni/acak-bagan-battle') ?>" id="formAcakBaganSeni">
                            <?= csrf_field() ?>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" id="selectAllBagan" type="checkbox">
                                    <label class="form-check-label fw-semibold" for="selectAllBagan">Pilih semua kategori</label>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearBagan">Kosongkan</button>
                            </div>

                            <div class="admin-table-wrap mb-3">
                                <div class="table-shell admin-table-scroller" style="max-height: 320px; overflow-y: auto;">
                                    <table class="table admin-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 44px;">Pilih</th>
                                                <th>Kategori</th>
                                                <th class="text-end">Battle</th>
                                                <th class="text-end">Sub Battle</th>
                                                <th class="text-end" style="width: 120px;">Random</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kategoriRows as $row) : ?>
                                                <tr>
                                                    <td><input class="form-check-input checkbox-bagan" type="checkbox" name="id_kategori_lomba_bagan[]" value="<?= esc((string) $row->id_kategori_lomba) ?>" <?= ((int) ($row->jumlah_sub_kategori_battle ?? 0) === 0) ? 'disabled' : '' ?>></td>
                                                    <td class="fw-semibold"><?= esc(trim((string) ($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? ''))) ?></td>
                                                    <td class="text-end"><?= esc((string) ($row->jumlah_battle ?? 0)) ?></td>
                                                    <td class="text-end"><?= esc((string) ($row->jumlah_sub_kategori_battle ?? 0)) ?></td>
                                                    <td class="text-end">
                                                        <div class="form-check form-switch d-inline-flex align-items-center justify-content-end">
                                                            <input class="form-check-input" type="checkbox" id="random<?= (int) $row->id_kategori_lomba ?>" name="random_kategori_lomba_<?= (int) $row->id_kategori_lomba ?>" value="1" <?= ((int) ($row->jumlah_sub_kategori_battle ?? 0) === 0) ? 'disabled' : '' ?>>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-warning small mb-3" role="alert">
                                <strong>Perhatian:</strong> Acak ulang akan mengganti bagan battle lama yang belum dijadwalkan. Kategori tanpa sub-kategori battle otomatis dinonaktifkan.
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-outline-danger rounded-pill" <?= empty($kategoriRows) ? 'disabled' : '' ?>>Acak Bagan Battle</button>
                                <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/super/generate-bagan-seni-battle-dari-jadwal') ?>">Generate Bagan dari Jadwal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($kategoriRows)) : ?>
            <div class="text-center muted-copy py-4">Belum ada kategori seni.</div>
        <?php endif; ?>
    </div>
</div>

<style>
.radio-card{display:block;border:1px solid rgba(0,0,0,.12);border-radius:14px;padding:12px;cursor:pointer;background:#fff;height:100%;}
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
    const baganChecks = () => qsa('.checkbox-bagan:not(:disabled)');
    const syncSelectAll = (master, items) => {
        if (!master) return;
        const list = items();
        if (list.length === 0) return;
        master.checked = list.every(i => i.checked);
        master.indeterminate = !master.checked && list.some(i => i.checked);
    };
    if (selectAllDistribusi) {
        selectAllDistribusi.addEventListener('change', function(){ distribusiChecks().forEach(i => i.checked = this.checked); });
        distribusiChecks().forEach(i => i.addEventListener('change', () => syncSelectAll(selectAllDistribusi, distribusiChecks)));
    }
    if (selectAllBagan) {
        selectAllBagan.addEventListener('change', function(){ baganChecks().forEach(i => i.checked = this.checked); });
        baganChecks().forEach(i => i.addEventListener('change', () => syncSelectAll(selectAllBagan, baganChecks)));
    }
    const clearDistribusi = qs('#btnClearDistribusi');
    if (clearDistribusi) clearDistribusi.addEventListener('click', function(){ distribusiChecks().forEach(i => i.checked = false); syncSelectAll(selectAllDistribusi, distribusiChecks); });
    const clearBagan = qs('#btnClearBagan');
    if (clearBagan) clearBagan.addEventListener('click', function(){ baganChecks().forEach(i => i.checked = false); syncSelectAll(selectAllBagan, baganChecks); });

    const formDistribusi = qs('#formDistribusiSeni');
    if (formDistribusi) {
        formDistribusi.addEventListener('submit', function(e){
            if (!distribusiChecks().some(i => i.checked)) { e.preventDefault(); alert('Pilih minimal satu kategori.'); return; }
            e.preventDefault();
            Swal.fire({
                title: 'Distribusi Seni?',
                text: 'Distribusi dan nomor undi akan dijalankan untuk kategori seni terpilih.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Jalankan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#6b7280'
            }).then((result) => { if (result.isConfirmed) formDistribusi.submit(); });
        });
    }
    const formBagan = qs('#formAcakBaganSeni');
    if (formBagan) {
        formBagan.addEventListener('submit', function(e){
            if (!baganChecks().some(i => i.checked)) { e.preventDefault(); alert('Pilih minimal satu kategori battle.'); return; }
            e.preventDefault();
            Swal.fire({
                title: 'Acak Ulang Bagan Battle?',
                text: 'Bagan battle akan diacak ulang untuk kategori seni terpilih.',
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

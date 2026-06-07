<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-1">
        <div>
            <p class="eyebrow mb-1">Tools</p>
            <h3 class="section-title h4 mb-1">Pencetakan Bagan</h3>
            <p class="muted-copy mb-0">Cetak bagan pertandingan tanding, seni battle, dan seni pool. Bagan terbuka di tab baru dan otomatis memunculkan dialog cetak.</p>
        </div>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="admin-card h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:46px;height:46px;background:#fdecec;color:#c60000;"><i class="fas fa-people-arrows"></i></span>
                <div>
                    <h4 class="h6 mb-0">Seluruh Kategori Tanding</h4>
                    <p class="muted-copy small mb-0">Bagan gugur tunggal tanding.</p>
                </div>
            </div>
            <a target="_blank" href="<?= base_url('admin/sekretariat/pencetakan-bagan/cetak-semua/tanding') ?>" class="btn btn-admin-brand rounded-pill mt-auto"><i class="fas fa-print me-2"></i>Cetak Bagan</a>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="admin-card h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:46px;height:46px;background:#fdecec;color:#c60000;"><i class="fas fa-hand-fist"></i></span>
                <div>
                    <h4 class="h6 mb-0">Seluruh Kategori Seni Tanding</h4>
                    <p class="muted-copy small mb-0">Bagan seni tanding (battle).</p>
                </div>
            </div>
            <a target="_blank" href="<?= base_url('admin/sekretariat/pencetakan-bagan/cetak-semua/seni') ?>" class="btn btn-admin-brand rounded-pill mt-auto"><i class="fas fa-print me-2"></i>Cetak Bagan</a>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="admin-card h-100 d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:46px;height:46px;background:#fdecec;color:#c60000;"><i class="fas fa-list-ol"></i></span>
                <div>
                    <h4 class="h6 mb-0">Seluruh Kategori Seni Pool</h4>
                    <p class="muted-copy small mb-0">Hasil penampilan seni pool.</p>
                </div>
            </div>
            <a target="_blank" href="<?= base_url('admin/sekretariat/pencetakan-bagan/cetak-semua/seni_pool') ?>" class="btn btn-admin-brand rounded-pill mt-auto"><i class="fas fa-print me-2"></i>Cetak Bagan</a>
        </div>
    </div>
</section>

<section class="admin-card">
    <div class="mb-3">
        <h3 class="section-title h5 mb-1">Cetak Bagan Berdasarkan Kategori Umur</h3>
        <p class="muted-copy mb-0">Pilih kategori tertentu untuk mencetak baganya saja.</p>
    </div>
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table admin-datatable-export align-middle mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori Usia</th>
                        <th>Jenis</th>
                        <th>Perlombaan</th>
                        <th>Peraturan</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach (($dataKategoriLomba ?? []) as $kategori) : ?>
                        <tr>
                            <td class="text-center"><?= esc((string) $no++) ?></td>
                            <td><?= esc(trim(ucwords(strtolower((string) ($kategori->nama_kategori_usia ?? '-'))) . ' - ' . ucwords(strtolower((string) ($kategori->jenis_kelamin ?? ''))), ' -')) ?></td>
                            <td class="text-capitalize"><?= esc((string) ($kategori->nama_kategori_lomba ?? '-')) ?></td>
                            <td class="text-capitalize"><?= esc((string) ($kategori->jenis_perlombaan ?? '-')) ?></td>
                            <td><?= esc((string) ($kategori->peraturan_pertandingan ?? '-')) ?></td>
                            <td class="text-end">
                                <?php if (($kategori->nama_kategori_lomba ?? '') === 'seni') : ?>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url('admin/sekretariat/pencetakan-bagan/cetak-kategori/' . $kategori->id_kategori_lomba . '/battle') ?>" target="_blank" class="btn btn-outline-danger rounded-pill px-3 <?= (int) ($kategori->jumlah_bagan_seni_battle ?? 0) === 0 ? 'disabled' : '' ?>"><i class="fas fa-print me-1"></i>Tanding</a>
                                        <a href="<?= base_url('admin/sekretariat/pencetakan-bagan/cetak-kategori/' . $kategori->id_kategori_lomba . '/pool') ?>" target="_blank" class="btn btn-outline-danger rounded-pill px-3 ms-1 <?= (int) ($kategori->jumlah_pool_seni ?? 0) === 0 ? 'disabled' : '' ?>"><i class="fas fa-print me-1"></i>Pool</a>
                                    </div>
                                <?php else : ?>
                                    <a href="<?= base_url('admin/sekretariat/pencetakan-bagan/cetak-kategori/' . $kategori->id_kategori_lomba) ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3 <?= (int) ($kategori->jumlah_bagan_tanding ?? 0) === 0 ? 'disabled' : '' ?>"><i class="fas fa-print me-1"></i>Cetak</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($dataKategoriLomba)) : ?><div class="text-center muted-copy py-4">Belum ada kategori lomba.</div><?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <p class="eyebrow mb-1">Belum Dibayar</p>
            <h3 class="section-title h4 mb-0">Item belum masuk transaksi</h3>
            <p class="muted-copy mb-0 mt-2">Pantau seluruh item tanding dan seni yang masih terbuka sebelum dibuatkan transaksi.</p>
        </div>
        <a href="<?= base_url('admin/bendahara/pembayaran') ?>" class="btn btn-outline-danger rounded-pill px-4">Kembali ke Pembayaran</a>
    </div>
</section>

<section class="row g-4">
    <div class="col-12">
        <div class="admin-card mb-4">
            <p class="eyebrow mb-1">Item Tanding</p>
            <h3 class="section-title h4 mb-3">Peserta tanding belum dibayar</h3>
            <div class="admin-table-wrap">
                <div class="table-shell admin-table-scroller">
                    <table class="table admin-table admin-datatable align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kontingen</th>
                                <th>Peserta</th>
                                <th>Kategori</th>
                                <th>Kelas</th>
                                <th>Nominal</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($overview['tanding'] ?? []) as $row) : ?>
                                <?php $nominal = (string) ($row->jenis_kontingen ?? '') === 'luar_negeri' ? (int) ($row->biaya_pendaftaran_ln ?? 0) : (int) ($row->biaya_pendaftaran_dn ?? 0); ?>
                                <tr>
                                    <td class="text-uppercase"><?= esc($row->nama_kontingen ?: '-') ?></td>
                                    <td><?= esc($row->nama_pendaftar ?: '-') ?></td>
                                    <td><?= esc(trim(($row->nama_kategori_usia ?? '-') . ' / ' . ($row->jenis_kelamin ?? '-'))) ?></td>
                                    <td><?= esc($row->label ?: '-') ?></td>
                                    <td>Rp <?= number_format($nominal, 0, ',', '.') ?></td>
                                    <td class="text-end"><a href="<?= base_url('admin/bendahara/kontingen/' . $row->id_kontingen) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Buka Kontingen</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <p class="eyebrow mb-1">Item Seni</p>
            <h3 class="section-title h4 mb-3">Nomor seni belum dibayar</h3>
            <div class="admin-table-wrap">
                <div class="table-shell admin-table-scroller">
                    <table class="table admin-table admin-datatable align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kontingen</th>
                                <th>Nomor</th>
                                <th>Kategori</th>
                                <th>Anggota</th>
                                <th>Nominal</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($overview['seni'] ?? []) as $row) : ?>
                                <?php $nominal = (string) ($row->jenis_kontingen ?? '') === 'luar_negeri' ? (int) ($row->biaya_pendaftaran_ln ?? 0) : (int) ($row->biaya_pendaftaran_dn ?? 0); ?>
                                <tr>
                                    <td class="text-uppercase"><?= esc($row->nama_kontingen ?: '-') ?></td>
                                    <td><?= esc(trim(($row->jenis_seni ?? '-') . ' / ' . ($row->nama_seni ?? '-'))) ?></td>
                                    <td><?= esc(trim(($row->nama_kategori_usia ?? '-') . ' / ' . ($row->jenis_kelamin ?? '-'))) ?></td>
                                    <td><?= esc($row->anggota_kelompok_peserta_seni ?: '-') ?></td>
                                    <td>Rp <?= number_format($nominal, 0, ',', '.') ?></td>
                                    <td class="text-end"><a href="<?= base_url('admin/bendahara/kontingen/' . $row->id_kontingen) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Buka Kontingen</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

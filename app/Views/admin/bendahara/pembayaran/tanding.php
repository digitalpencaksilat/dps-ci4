<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Riwayat Tanding</p>
            <h3 class="section-title h4 mb-0">Pembayaran tanding</h3>
            <p class="muted-copy mb-0 mt-2">Lihat peserta tanding yang sudah terhubung ke transaksi atau masih belum dibayar.</p>
        </div>
        <a href="<?= base_url('admin/bendahara/pembayaran') ?>" class="btn btn-outline-danger rounded-pill px-4">Kembali ke Pembayaran</a>
    </div>

    <?php if (($rows ?? []) === []) : ?>
        <div class="placeholder-stat">
            <h4 class="h5 mb-2">Belum ada data tanding</h4>
            <p class="muted-copy mb-0">Riwayat pembayaran tanding akan tampil setelah peserta tanding terhubung ke transaksi.</p>
        </div>
    <?php else : ?>
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Peserta</th>
                            <th>Kontingen</th>
                            <th>Kategori</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td><?= esc($row->nama_pendaftar ?: '-') ?></td>
                                <td><?= esc($row->nama_kontingen ?: '-') ?></td>
                                <td><?= esc(trim(($row->nama_kategori_usia ?? '-') . ' / ' . ($row->jenis_kelamin ?? '-'))) ?></td>
                                <td><?= esc($row->label ?: '-') ?></td>
                                <td>
                                    <?php if ($row->id_pembayaran === null) : ?>
                                        <span class="status-badge neutral">Belum Dibayar</span>
                                    <?php elseif ($row->status_pembayaran === 'lunas') : ?>
                                        <span class="status-badge success">Lunas</span>
                                    <?php else : ?>
                                        <span class="status-badge warning">Menunggu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($row->id_pembayaran !== null) : ?>
                                        <a href="<?= base_url('admin/bendahara/pembayaran/' . $row->id_pembayaran) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Lihat Transaksi</a>
                                    <?php else : ?>
                                        <a href="<?= base_url('admin/bendahara/kontingen/' . $row->id_kontingen) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Buka Kontingen</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>

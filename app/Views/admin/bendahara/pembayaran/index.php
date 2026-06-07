<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Data Pembayaran</p>
            <h3 class="section-title h4 mb-0">Daftar transaksi pembayaran</h3>
            <p class="muted-copy mb-0 mt-2">Pantau status transaksi, nominal masuk, dan jalur tindak lanjut tiap kontingen.</p>
        </div>
        <a href="<?= base_url('admin/bendahara/dashboard') ?>" class="btn btn-outline-danger rounded-pill px-4">Kembali ke Dashboard</a>
    </div>

    <?php if (($transactions ?? []) === []) : ?>
        <div class="placeholder-stat">
            <h4 class="h5 mb-2">Belum ada transaksi</h4>
            <p class="muted-copy mb-0">Belum ada data pembayaran yang cocok dengan filter halaman ini.</p>
        </div>
    <?php else : ?>
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID Pembayaran</th>
                            <th>Kontingen</th>
                            <th>Pimpinan</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Tanggal</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $trx) : ?>
                            <tr>
                                <td>#<?= esc((string) $trx->id_pembayaran) ?></td>
                                <td class="text-uppercase">
                                    <div class="fw-semibold"><?= esc($trx->nama_kontingen ?: '-') ?></div>
                                    <div class="small muted-copy"><?= esc(ucwords(str_replace('_', ' ', (string) ($trx->jenis_kontingen ?: '-')))) ?></div>
                                </td>
                                <td><?= esc($trx->nama_pimpinan_kontingen ?: '-') ?></td>
                                <td>
                                    <span class="status-badge <?= $trx->status_pembayaran === 'lunas' ? 'success' : 'warning' ?>">
                                        <?= esc(ucfirst((string) $trx->status_pembayaran)) ?>
                                    </span>
                                </td>
                                <td>Rp <?= number_format((int) $trx->total_pembayaran, 0, ',', '.') ?></td>
                                <td><?= esc(format_tanggal_indo($trx->tanggal_pembayaran)) ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('admin/bendahara/pembayaran/' . $trx->id_pembayaran . '?from=' . rawurlencode(uri_string())) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a>
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

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card hero-panel mb-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-4">
            <p class="eyebrow text-white-50">Dashboard Bendahara</p>
            <h2 class="section-title h2 mb-3">Ringkasan pembayaran utama.</h2>
            <p class="mb-0 text-white-50">Fokus cepat ke nominal yang sudah masuk dan nominal yang masih perlu ditagih atau diverifikasi.</p>
        </div>
        <div class="col-lg-4">
            <div class="placeholder-stat h-100" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.16);">
                <div class="small text-white-50 mb-2">Total Pembayaran Lunas</div>
                <div class="section-title h2 mb-2">Rp <?= number_format((int) ($summary['total_lunas'] ?? 0), 0, ',', '.') ?></div>
                <div class="small text-white-50"><?= esc((string) ($summary['jumlah_lunas'] ?? 0)) ?> transaksi sudah terkonfirmasi.</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="placeholder-stat h-100" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.16);">
                <div class="small text-white-50 mb-2">Total Pembayaran Belum Lunas</div>
                <div class="section-title h2 mb-2">Rp <?= number_format((int) (((int) ($summary['detail_pembayaran_atlet']['tanding']['jumlah_uang_belum_diterima'] ?? 0)) + ((int) ($summary['detail_pembayaran_atlet']['tunggal']['jumlah_uang_belum_diterima'] ?? 0)) + ((int) ($summary['detail_pembayaran_atlet']['ganda']['jumlah_uang_belum_diterima'] ?? 0)) + ((int) ($summary['detail_pembayaran_atlet']['beregu']['jumlah_uang_belum_diterima'] ?? 0)) + ((int) ($summary['detail_pembayaran_atlet']['solo_kreatif']['jumlah_uang_belum_diterima'] ?? 0))), 0, ',', '.') ?></div>
                <div class="small text-white-50">Mencakup item menunggu dan item yang belum masuk transaksi.</div>
            </div>
        </div>
    </div>
</section>

<section class="placeholder-grid mb-4">
    <div class="metric-card">
        <div class="d-flex justify-content-between gap-3 mb-4">
            <div>
                <div class="eyebrow">Transaksi</div>
                <div class="metric-value"><?= esc((string) ($summary['jumlah_transaksi'] ?? 0)) ?></div>
            </div>
            <div class="metric-icon"><i class="fas fa-wallet"></i></div>
        </div>
        <p class="mb-1 fw-semibold">Transaksi tercatat</p>
        <p class="muted-copy mb-0">Lunas Rp <?= number_format((int) ($summary['total_lunas'] ?? 0), 0, ',', '.') ?> dari <?= esc((string) ($summary['jumlah_lunas'] ?? 0)) ?> transaksi.</p>
    </div>
    <div class="metric-card">
        <div class="d-flex justify-content-between gap-3 mb-4">
            <div>
                <div class="eyebrow">Menunggu</div>
                <div class="metric-value"><?= esc((string) ($summary['jumlah_menunggu'] ?? 0)) ?></div>
            </div>
            <div class="metric-icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <p class="mb-1 fw-semibold">Perlu verifikasi</p>
        <p class="muted-copy mb-0">Nominal menunggu Rp <?= number_format((int) ($summary['total_menunggu'] ?? 0), 0, ',', '.') ?>.</p>
    </div>
    <div class="metric-card">
        <div class="d-flex justify-content-between gap-3 mb-4">
            <div>
                <div class="eyebrow">Belum Dibayar</div>
                <div class="metric-value"><?= esc((string) (((int) ($summary['tanding_belum'] ?? 0)) + ((int) ($summary['seni_belum'] ?? 0)))) ?></div>
            </div>
            <div class="metric-icon"><i class="fas fa-receipt"></i></div>
        </div>
        <p class="mb-1 fw-semibold">Item masih terbuka</p>
        <p class="muted-copy mb-0">Tanding <?= esc((string) ($summary['tanding_belum'] ?? 0)) ?> item, seni <?= esc((string) ($summary['seni_belum'] ?? 0)) ?> item.</p>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-12">
        <div class="admin-card h-100">
            <p class="eyebrow mb-3">Ringkasan Pembayaran</p>
            <div class="admin-table-wrap">
                <div class="table-shell admin-table-scroller">
                    <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Input</th>
                            <th>Lunas</th>
                            <th>Menunggu/Belum</th>
                            <th>Uang Diterima</th>
                            <th>Uang Belum Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($summary['detail_pembayaran_atlet'] ?? []) as $label => $detail) : ?>
                            <tr>
                                <td><?= esc(ucwords(str_replace('_', ' ', (string) $label))) ?></td>
                                <td><?= esc((string) ($detail['jumlah_atlet_input'] ?? 0)) ?></td>
                                <td><?= esc((string) ($detail['jumlah_atlet_lunas'] ?? 0)) ?></td>
                                <td><?= esc((string) ($detail['jumlah_atlet_belum_lunas'] ?? 0)) ?></td>
                                <td>Rp <?= number_format((int) ($detail['jumlah_uang_diterima'] ?? 0), 0, ',', '.') ?></td>
                                <td>Rp <?= number_format((int) ($detail['jumlah_uang_belum_diterima'] ?? 0), 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="row g-4">
    <div class="col-12">
        <div class="admin-card h-100">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <p class="eyebrow mb-1">Antrian Verifikasi</p>
                    <h2 class="section-title h4 mb-0">Transaksi menunggu konfirmasi</h2>
                </div>
                <a href="<?= base_url('admin/bendahara/pembayaran/menunggu-konfirmasi') ?>" class="btn btn-outline-danger rounded-pill px-4">Lihat Semua</a>
            </div>

            <?php if (($waitingTransactions ?? []) === []) : ?>
                <div class="placeholder-stat">
                    <h4 class="h5 mb-2">Tidak ada antrian aktif</h4>
                    <p class="muted-copy mb-0">Semua transaksi yang masuk saat ini sudah terkonfirmasi atau belum ada transaksi baru.</p>
                </div>
            <?php else : ?>
                <div class="admin-table-wrap">
                    <div class="table-shell admin-table-scroller">
                        <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kontingen</th>
                                <th>Total</th>
                                <th>Tanggal</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($waitingTransactions, 0, 5) as $trx) : ?>
                                <tr>
                                    <td>#<?= esc((string) $trx->id_pembayaran) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($trx->nama_kontingen ?: '-') ?></div>
                                        <div class="small muted-copy"><?= esc($trx->nama_pimpinan_kontingen ?: '-') ?></div>
                                    </td>
                                    <td>Rp <?= number_format((int) $trx->total_pembayaran, 0, ',', '.') ?></td>
                                    <td><?= esc(format_tanggal_indo($trx->tanggal_pembayaran)) ?></td>
                                    <td class="text-end">
                                        <a href="<?= base_url('admin/bendahara/pembayaran/' . $trx->id_pembayaran) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

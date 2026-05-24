<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
        <div>
            <p class="eyebrow">Rincian Transaksi</p>
            <h2 class="section-title h3 mb-1">Pembayaran #<?= esc((string) $detail['pembayaran']->id_pembayaran) ?></h2>
            <p class="muted-copy mb-0">Rincian dibuat lebih padat supaya transaksi cepat dipindai.</p>
        </div>
        <div class="section-toolbar">
            <a href="<?= base_url('admin/bendahara/pembayaran') ?>" class="btn btn-soft btn-sm rounded-pill px-3">Kembali</a>
            <a href="<?= base_url('admin/bendahara/pembayaran/' . $detail['pembayaran']->id_pembayaran . '/nota.pdf') ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3">Nota PDF</a>
        </div>
    </div>
</section>

<section class="row g-4">
    <div class="col-xl-7">
        <div class="admin-card mb-4">
            <p class="eyebrow">Item Tanding</p>
            <h3 class="section-title h4 mb-3">Peserta tanding dalam transaksi</h3>
            <?php if ($detail['tanding'] === []) : ?>
                <div class="placeholder-stat">
                    <p class="muted-copy mb-0">Tidak ada item tanding pada transaksi ini.</p>
                </div>
            <?php else : ?>
                <div class="admin-table-wrap">
                    <div class="table-shell admin-table-scroller">
                        <table class="table admin-table admin-datatable align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Atlet</th>
                                    <th>Kategori Usia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Kelas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail['tanding'] as $row) : ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc($row->nama_pendaftar) ?></td>
                                        <td><?= esc($row->nama_kategori_usia) ?></td>
                                        <td><?= esc($row->jenis_kelamin) ?></td>
                                        <td><?= esc($row->label) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <p class="eyebrow">Item Seni</p>
            <h3 class="section-title h4 mb-3">Kelompok seni dalam transaksi</h3>
            <?php if ($detail['seni'] === []) : ?>
                <div class="placeholder-stat">
                    <p class="muted-copy mb-0">Tidak ada item seni pada transaksi ini.</p>
                </div>
            <?php else : ?>
                <div class="admin-table-wrap">
                    <div class="table-shell admin-table-scroller">
                        <table class="table admin-table admin-datatable align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Anggota</th>
                                    <th>Kategori Usia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Kategori Seni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detail['seni'] as $row) : ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc($row->anggota_kelompok_peserta_seni ?: '-') ?></td>
                                        <td><?= esc($row->nama_kategori_usia) ?></td>
                                        <td><?= esc($row->jenis_kelamin) ?></td>
                                        <td><?= esc($row->jenis_seni) ?> - <?= esc($row->nama_seni) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="admin-card h-100">
            <p class="eyebrow">Ringkasan Transaksi</p>
            <h3 class="section-title h4 mb-3">Informasi pembayaran</h3>
            <div class="compact-meta-grid mb-3">
                <div class="compact-meta-item">
                    <span class="compact-meta-label">Tanggal</span>
                    <strong><?= esc(format_tanggal_indo($detail['pembayaran']->tanggal_pembayaran)) ?></strong>
                </div>
                <div class="compact-meta-item">
                    <span class="compact-meta-label">Status</span>
                    <span class="status-badge <?= $detail['pembayaran']->status_pembayaran === 'lunas' ? 'success' : 'warning' ?>">
                        <?= esc(ucfirst((string) $detail['pembayaran']->status_pembayaran)) ?>
                    </span>
                </div>
                <div class="compact-meta-item">
                    <span class="compact-meta-label">Kontingen</span>
                    <strong><?= esc($detail['pembayaran']->nama_kontingen ?: '-') ?></strong>
                </div>
                <div class="compact-meta-item">
                    <span class="compact-meta-label">Total Pembayaran</span>
                    <div class="h5 fw-bold mb-0">Rp <?= number_format((int) $detail['pembayaran']->total_pembayaran, 0, ',', '.') ?></div>
                </div>
                <div class="compact-meta-item full">
                    <span class="compact-meta-label">Bukti Pembayaran</span>
                    <div class="compact-actions">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#paymentProofModal">Lihat Bukti</button>
                    </div>
                </div>
            </div>

            <div class="border-top pt-3 mt-3">
                <p class="eyebrow">Aksi Admin</p>
                <h3 class="section-title h5 mb-3">Kontrol transaksi</h3>
                <?php if ($detail['pembayaran']->status_pembayaran === 'lunas') : ?>
                    <div class="placeholder-stat mb-3">
                        <strong class="d-block mb-2">Transaksi sudah lunas</strong>
                        <p class="muted-copy mb-0">Pembayaran ini sudah dikonfirmasi. Tidak perlu aksi tambahan.</p>
                    </div>
                <?php else : ?>
                    <form method="post" action="<?= base_url('admin/bendahara/pembayaran/' . $detail['pembayaran']->id_pembayaran . '/konfirmasi') ?>" class="d-grid mb-3">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-sm rounded-pill">Konfirmasi Pembayaran</button>
                    </form>
                <?php endif; ?>

                <form method="post" action="<?= base_url('admin/bendahara/pembayaran/' . $detail['pembayaran']->id_pembayaran . '/tolak') ?>" class="d-grid">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">Tolak Pembayaran</button>
                </form>
                <p class="muted-copy small mt-3 mb-0">Tolak hanya jika bukti bayar tidak valid atau transaksi harus dikembalikan ke status belum dibayar.</p>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="paymentProofModal" tabindex="-1" aria-labelledby="paymentProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <div>
                    <p class="eyebrow mb-1">Bukti Pembayaran</p>
                    <h2 class="modal-title section-title h4" id="paymentProofModalLabel">Transaksi #<?= esc((string) $detail['pembayaran']->id_pembayaran) ?></h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center">
                <img src="<?= base_url('uploads/bukti-pembayaran/' . $detail['pembayaran']->foto) ?>" alt="Bukti pembayaran transaksi #<?= esc((string) $detail['pembayaran']->id_pembayaran) ?>" class="img-fluid rounded-4 border" style="max-height: 70vh; max-width: min(100%, 720px); object-fit: contain;">
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="<?= base_url('uploads/bukti-pembayaran/' . $detail['pembayaran']->foto) ?>" target="_blank" rel="noopener" class="btn btn-soft rounded-pill px-4">Buka File Asli</a>
                <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

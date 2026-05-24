<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
        <div>
            <p class="eyebrow">Nota Pembayaran</p>
            <h2 class="section-title h3 mb-1">Transaksi #<?= esc((string) $detail['pembayaran']->id_pembayaran) ?></h2>
            <p class="muted-copy mb-0">Dokumen ringkas untuk validasi dan arsip bendahara.</p>
        </div>
        <div class="section-toolbar">
            <a href="<?= base_url('admin/bendahara/pembayaran/' . $detail['pembayaran']->id_pembayaran) ?>" class="btn btn-soft btn-sm rounded-pill px-3">Kembali ke Detail</a>
            <a href="<?= base_url('admin/bendahara/pembayaran/' . $detail['pembayaran']->id_pembayaran . '/nota.pdf') ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3">Unduh PDF</a>
        </div>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="admin-card h-100">
            <p class="eyebrow">Informasi Pembayaran</p>
            <div class="compact-meta-grid">
                <div class="compact-meta-item">
                    <span class="compact-meta-label">Kontingen</span>
                    <strong><?= esc($detail['pembayaran']->nama_kontingen ?: '-') ?></strong>
                </div>
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
                    <span class="compact-meta-label">Total</span>
                    <div class="h5 fw-bold mb-0">Rp <?= number_format((int) $detail['pembayaran']->total_pembayaran, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="admin-card h-100">
            <p class="eyebrow">Bukti Pembayaran</p>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#paymentProofModal">Lihat Bukti</button>
            </div>
        </div>
    </div>
</section>

<section class="admin-card mb-4">
    <p class="eyebrow">Rincian Item</p>
    <h3 class="section-title h4 mb-3">Daftar pembayaran</h3>
    <div class="admin-table-wrap mb-4">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($detail['tanding'] ?? []) as $row) : ?>
                        <tr>
                            <td><?= esc($row->nama_pendaftar ?: '-') ?></td>
                            <td><?= esc(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '-') . ' - Kelas ' . ($row->label ?? '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach (($detail['seni'] ?? []) as $row) : ?>
                        <tr>
                            <td><?= esc($row->anggota_kelompok_peserta_seni ?: '-') ?></td>
                            <td><?= esc(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '-') . ' - ' . ucwords((string) ($row->jenis_seni ?? '-')) . ' ' . ucwords((string) ($row->nama_seni ?? '-'))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (($detail['tanding'] ?? []) === [] && ($detail['seni'] ?? []) === []) : ?>
                        <tr>
                            <td colspan="2">Tidak ada item pembayaran.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="muted-copy small">Nota ini dibuat otomatis oleh sistem untuk kebutuhan validasi dan arsip bendahara.</div>
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
                <img src="<?= base_url('uploads/bukti-pembayaran/' . $detail['pembayaran']->foto) ?>" alt="Bukti pembayaran transaksi #<?= esc((string) $detail['pembayaran']->id_pembayaran) ?>" class="admin-proof-image img-fluid rounded-4 border">
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="<?= base_url('uploads/bukti-pembayaran/' . $detail['pembayaran']->foto) ?>" target="_blank" rel="noopener" class="btn btn-soft rounded-pill px-4">Buka File Asli</a>
                <button type="button" class="btn btn-danger rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

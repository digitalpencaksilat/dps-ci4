<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Rekap Pembayaran</p>
            <h3 class="section-title h4 mb-0">Rekap kontingen</h3>
            <p class="muted-copy mb-0 mt-2">Bandingkan jumlah transaksi dan nominal masuk antar kontingen dengan cepat.</p>
        </div>
        <a href="<?= base_url('admin/bendahara/dashboard') ?>" class="btn btn-outline-danger rounded-pill px-4">Kembali ke Dashboard</a>
    </div>

    <?php if (($kontingenRows ?? []) === []) : ?>
        <div class="placeholder-stat">
            <h4 class="h5 mb-2">Belum ada kontingen</h4>
            <p class="muted-copy mb-0">Data kontingen belum tersedia untuk direkap di area bendahara.</p>
        </div>
    <?php else : ?>
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kontingen</th>
                            <th>Transaksi</th>
                            <th>Peserta Tanding</th>
                            <th>Tunggal</th>
                            <th>Ganda</th>
                            <th>Beregu</th>
                            <th>Total Lunas</th>
                            <th>Total Menunggu</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kontingenRows as $row) : ?>
                            <tr>
                                <td class="text-uppercase">
                                    <div class="fw-semibold"><?= esc($row->nama_kontingen ?: '-') ?></div>
                                    <div class="small muted-copy">#<?= esc((string) $row->id_kontingen) ?></div>
                                </td>
                                <td><?= esc((string) ((int) ($row->jumlah_transaksi ?? 0))) ?></td>
                                <td><?= esc((string) ((int) ($row->jumlah_peserta_tanding ?? 0))) ?></td>
                                <td><?= esc((string) ((int) ($row->jumlah_tunggal ?? 0))) ?></td>
                                <td><?= esc((string) ((int) ($row->jumlah_ganda ?? 0))) ?></td>
                                <td><?= esc((string) ((int) ($row->jumlah_beregu ?? 0))) ?></td>
                                <td>Rp <?= number_format((int) ($row->total_lunas ?? 0), 0, ',', '.') ?></td>
                                <td>Rp <?= number_format((int) ($row->total_menunggu ?? 0), 0, ',', '.') ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('admin/bendahara/kontingen/' . $row->id_kontingen) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a>
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

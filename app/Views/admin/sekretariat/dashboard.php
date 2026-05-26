<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Portal Sekretariat</p>
            <h2 class="section-title h3 mb-2"><?= esc($title ?? 'Dashboard Sekretariat') ?></h2>
            <p class="muted-copy mb-0">Kontrol bertahap modul kontingen dan peserta. Cetak ID card/sertifikat disiapkan fase berikutnya.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <a href="<?= base_url('admin/sekretariat/kontingen') ?>" class="btn btn-admin-brand rounded-pill px-4">Kelola Kontingen</a>
            <a href="<?= base_url('admin/sekretariat/peserta-tanding') ?>" class="btn btn-outline-danger rounded-pill px-4">Peserta Tanding</a>
        </div>
    </div>
</section>

<section class="row g-4 mb-4">
    <?php
    $cards = [
        ['label' => 'Kontingen', 'value' => $stats['kontingen'] ?? 0, 'icon' => 'fa-people-group'],
        ['label' => 'Kontingen Belum Input Peserta', 'value' => $stats['kontingenBelumInputPeserta'] ?? 0, 'icon' => 'fa-user-clock'],
        ['label' => 'Peserta Belum Memilih Kategori', 'value' => $stats['pesertaBelumMemilihKategori'] ?? 0, 'icon' => 'fa-list-check'],
        ['label' => 'Pendaftar', 'value' => $stats['pendaftar'] ?? 0, 'icon' => 'fa-users'],
        ['label' => 'Peserta Tanding', 'value' => $stats['pesertaTanding'] ?? 0, 'icon' => 'fa-user-ninja'],
        ['label' => 'Kelompok Seni', 'value' => $stats['kelompokSeni'] ?? 0, 'icon' => 'fa-users-viewfinder'],
    ];
    ?>
    <?php foreach ($cards as $card) : ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="admin-card h-100">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="small muted-copy mb-1"><?= esc($card['label']) ?></div>
                        <div class="h3 section-title mb-0"><?= esc((string) $card['value']) ?></div>
                    </div>
                    <i class="fas <?= esc($card['icon']) ?> text-danger fs-3"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="admin-card">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div>
            <h3 class="section-title h5 mb-1">Kontingen terbaru</h3>
            <p class="muted-copy mb-0">Cuplikan data untuk pengecekan cepat.</p>
        </div>
        <a href="<?= base_url('admin/sekretariat/kontingen') ?>" class="btn btn-outline-danger rounded-pill px-4">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead><tr><th>Kontingen</th><th>Pendaftar</th><th>Tanding</th><th>Seni</th><th>Status Bayar</th></tr></thead>
            <tbody>
                <?php foreach (($kontingenRows ?? []) as $row) : ?>
                    <tr>
                        <td><a href="<?= base_url('admin/sekretariat/kontingen/' . $row->id_kontingen) ?>" class="fw-semibold text-decoration-none text-danger"><?= esc($row->nama_kontingen) ?></a></td>
                        <td><?= esc((string) ((int) ($row->jumlah_pendaftar ?? 0))) ?></td>
                        <td><?= esc((string) ((int) ($row->jumlah_peserta_tanding ?? 0))) ?></td>
                        <td><?= esc((string) ((int) ($row->jumlah_kelompok_peserta_seni ?? 0))) ?></td>
                        <td><?= esc((string) ($row->status_pembayaran ?? 'belum ada')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>

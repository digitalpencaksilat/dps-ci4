<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$paymentBadge = static function (?string $status): string {
    if ($status === 'lunas') {
        return '<span class="badge text-bg-success">Lunas</span>';
    }
    if ($status === 'menunggu') {
        return '<span class="badge text-bg-warning">Menunggu Konfirmasi</span>';
    }

    return '<span class="badge text-bg-danger">Belum Lunas</span>';
};
$formatGender = static fn (?string $gender): string => $gender !== null && $gender !== '' ? ucwords($gender) : '-';
?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Sekretariat</p>
            <h3 class="section-title h4 mb-0">Kelompok peserta seni</h3>
            <p class="muted-copy mb-0 mt-2">Daftar kelompok seni lintas kontingen.</p>
        </div>
        <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createKelompokSeniModal">Tambah Kelompok</button>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
        <table class="table admin-table admin-datatable-export align-middle mb-0">
            <thead><tr><th>Nama</th><th>Kontingen</th><th>Sekolah</th><th>Kategori Usia</th><th>Jenis Kelamin</th><th>Jenis Seni</th><th>Jurus</th><th>Nomor Pool</th><th>Nomor Undi</th><th>Pembayaran</th><th class="text-end no-export">Aksi</th></tr></thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <tr>
                        <td><a href="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni) ?>" class="fw-semibold text-danger text-decoration-none text-capitalize"><?= $row->anggota_kelompok_peserta_seni ?: '-' ?></a></td>
                        <td class="text-capitalize"><?= esc($row->nama_kontingen) ?></td>
                        <td><?= $row->nama_sekolah ?: '-' ?></td>
                        <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                        <td><?= esc($formatGender($row->jenis_kelamin ?? null)) ?></td>
                        <td><?= esc((string) ($row->jenis_seni ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nama_seni ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                        <td class="text-end"><?= ($row->sistem_penampilan ?? '') === 'pool' ? esc((string) ($row->nomor_undi ?? '-')) : '<span class="muted-copy small">Tidak ada undian</span>' ?></td>
                        <td><?= $paymentBadge($row->status_pembayaran ?? null) ?></td>
                        <td class="text-end"><a href="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</section>

<div class="modal fade" id="createKelompokSeniModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('admin/sekretariat/kelompok-seni') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kelompok Seni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?= view('admin/sekretariat/kelompok_seni/_form', [
                    'mode' => 'create',
                    'kontingenRows' => $kontingenRows ?? [],
                    'kompetisiOptions' => $kompetisiOptions ?? [],
                    'pendaftarOptions' => $pendaftarOptions ?? [],
                ]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Kelompok</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

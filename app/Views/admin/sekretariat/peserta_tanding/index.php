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
            <h3 class="section-title h4 mb-0">Peserta tanding</h3>
            <p class="muted-copy mb-0 mt-2">Daftar peserta tanding lintas kontingen.</p>
        </div>
        <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createPesertaTandingModal">Tambah Peserta Tanding</button>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
        <table class="table admin-table admin-datatable align-middle mb-0">
            <thead><tr><th>Nama</th><th>Kontingen</th><th>Sekolah</th><th>Berat Badan</th><th>Tinggi Badan</th><th>Umur</th><th>Kategori</th><th>Jenis Kelamin</th><th>Kelas</th><th>Nomor Pool</th><th>Rentang Berat Badan</th><th>Pembayaran</th><th>Keterangan</th><th>NIK</th><th>No KK</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <tr>
                        <td class="fw-semibold text-capitalize"><?= esc($row->nama_pendaftar) ?></td>
                        <td class="text-capitalize"><?= esc($row->nama_kontingen) ?></td>
                        <td><?= esc((string) ($row->nama_sekolah ?: '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->berat_badan ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->tinggi_badan ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->umur_pendaftar ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                        <td><?= esc($formatGender($row->jenis_kelamin ?? null)) ?></td>
                        <td><?= esc((string) ($row->label ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                        <td class="text-end"><?= esc(trim(((string) ($row->berat_minimal ?? '-')) . ' - ' . ((string) ($row->berat_maksimal ?? '-')))) ?></td>
                        <td><?= $paymentBadge($row->status_pembayaran ?? null) ?></td>
                        <td><?= esc((string) ($row->keterangan ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nomor_induk_kependudukan ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nomor_kartu_keluarga ?? '-')) ?></td>
                        <td class="text-end"><a href="<?= base_url('admin/sekretariat/peserta-tanding/' . $row->id_peserta_tanding) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</section>

<div class="modal fade" id="createPesertaTandingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('admin/sekretariat/peserta-tanding') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Peserta Tanding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?= view('admin/sekretariat/peserta_tanding/_form', ['mode' => 'create', 'pendaftarOptions' => $pendaftarOptions ?? [], 'kompetisiOptions' => $kompetisiOptions ?? []]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

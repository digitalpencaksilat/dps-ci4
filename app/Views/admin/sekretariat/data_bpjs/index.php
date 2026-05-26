<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$formatTanggal = static function (?string $date): string {
    if (empty($date)) {
        return '-';
    }

    try {
        return (new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE))->format(new DateTimeImmutable($date));
    } catch (Throwable) {
        return $date;
    }
};
?>

<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Sekretariat</p>
            <h3 class="section-title h4 mb-0">Data BPJS</h3>
            <p class="muted-copy mb-0 mt-2">Data BPJS seluruh pendaftar lintas kontingen dengan struktur tabel yang mengikuti halaman CI3.</p>
        </div>
        <div class="status-badge neutral">Total Pendaftar: <?= esc((string) count($rows ?? [])) ?></div>
    </div>

    <div class="admin-table-wrap">
        <div class="admin-table-note"><i class="fas fa-arrows-left-right-to-line"></i><span>Geser tabel untuk melihat NIK, nomor KK, dan nomor penanggung jawab secara utuh.</span></div>
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table admin-datatable align-middle mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kontingen</th>
                        <th class="text-center">JK</th>
                        <th class="text-center">Tgl Lahir</th>
                        <th class="text-center">Umur</th>
                        <th class="text-center">NIK</th>
                        <th class="text-center">No. KK</th>
                        <th class="text-center">HP Penanggung Jawab</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $index => $row) : ?>
                        <tr>
                            <td class="text-center fw-semibold"><?= esc((string) ($index + 1)) ?></td>
                            <td class="fw-semibold text-capitalize"><?= esc($row->nama_pendaftar ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->nama_kontingen ?? '-') ?></td>
                            <td class="text-center text-capitalize"><?= esc($row->jenis_kelamin ?? '-') ?></td>
                            <td class="text-center"><?= esc($formatTanggal($row->tanggal_lahir ?? null)) ?></td>
                            <td class="text-center"><?= esc((string) ($row->umur_pendaftar ?? '-')) ?></td>
                            <td class="text-center"><?= esc($row->nomor_induk_kependudukan ?? '-') ?></td>
                            <td class="text-center"><?= esc($row->nomor_kartu_keluarga ?? '-') ?></td>
                            <td class="text-center"><?= esc($row->nomor_telepon_penanggungjawab ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (empty($rows)) : ?>
        <div class="text-center muted-copy py-4">Belum ada data pendaftar untuk ditampilkan pada laporan BPJS.</div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>

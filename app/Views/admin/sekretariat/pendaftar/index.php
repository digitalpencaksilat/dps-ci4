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
$formatGender = static fn (?string $gender): string => $gender !== null && $gender !== '' ? ucwords($gender) : '-';
?>

<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Sekretariat</p>
            <h3 class="section-title h4 mb-0">Data Atlet</h3>
            <p class="muted-copy mb-0 mt-2">Daftar semua atlet lintas kontingen.</p>
        </div>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
        <table class="table admin-table admin-datatable align-middle mb-0">
            <thead><tr><th>Nama</th><th>Kontingen</th><th>Jenis Kelamin</th><th>Tanggal Lahir</th><th>Umur</th><th>Berat Badan</th><th>Tinggi Badan</th><th>Sekolah</th><th>Provinsi</th><th>NIK</th><th>No KK</th><th>Jenis Pendaftaran</th></tr></thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <tr>
                        <td class="fw-semibold text-capitalize"><?= esc($row->nama_pendaftar) ?></td>
                        <td class="text-capitalize"><?= esc((string) ($row->nama_kontingen ?? '-')) ?></td>
                        <td><?= esc($formatGender($row->jenis_kelamin ?? null)) ?></td>
                        <td><?= esc($formatTanggal($row->tanggal_lahir ?? null)) ?></td>
                        <td class="text-end"><?= esc((string) ($row->umur_pendaftar ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->berat_badan ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->tinggi_badan ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nama_sekolah ?: '-')) ?></td>
                        <td><?= esc((string) ($row->provinsi ?: '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->nomor_induk_kependudukan ?: '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->nomor_kartu_keluarga ?: '-')) ?></td>
                        <td><?= esc((string) ($row->jenis_pendaftaran ?: '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
</section>
<?= $this->endSection() ?>

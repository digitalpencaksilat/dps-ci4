<?php
$formatPeserta = static function (?string $anggota, ?string $kontingen, ?string $calonPartai, ?string $calonGelanggang): array {
    if ($anggota !== null && trim($anggota) !== '') {
        return [
            'title' => $anggota,
            'subtitle' => $kontingen !== null && trim($kontingen) !== '' ? $kontingen : '-',
            'fallback' => false,
        ];
    }

    $subtitle = 'Belum ditentukan';
    if ($calonGelanggang !== null && trim($calonGelanggang) !== '') {
        $subtitle = 'Dari Gelanggang ' . $calonGelanggang;
    }

    return [
        'title' => 'Pemenang Partai Ke ' . ($calonPartai !== null && trim($calonPartai) !== '' ? $calonPartai : '-'),
        'subtitle' => $subtitle,
        'fallback' => true,
    ];
};
?>
<div class="admin-table-wrap pesilat-terbaik-table">
    <div class="table-shell admin-table-scroller">
        <table class="table admin-table admin-datatable align-middle mb-0">
            <thead>
                <tr>
                    <th>Jadwal</th>
                    <th>Kategori</th>
                    <th class="text-center">Biru</th>
                    <th class="text-center">Poin Biru</th>
                    <th class="text-center">Babak</th>
                    <th class="text-center">Poin Merah</th>
                    <th class="text-center">Merah</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <?php
                    $pesertaBiru = $formatPeserta(
                        $row->anggota_kelompok_peserta_seni_biru ?? null,
                        $row->nama_kontingen_biru ?? null,
                        isset($row->calon_anggota_kelompok_peserta_seni_biru) ? (string) $row->calon_anggota_kelompok_peserta_seni_biru : null,
                        $row->gelanggang_calon_anggota_kelompok_peserta_seni_biru ?? null,
                    );
                    $pesertaMerah = $formatPeserta(
                        $row->anggota_kelompok_peserta_seni_merah ?? null,
                        $row->nama_kontingen_merah ?? null,
                        isset($row->calon_anggota_kelompok_peserta_seni_merah) ? (string) $row->calon_anggota_kelompok_peserta_seni_merah : null,
                        $row->gelanggang_calon_anggota_kelompok_peserta_seni_merah ?? null,
                    );
                    ?>
                    <tr>
                        <td class="text-center small">
                            <?php if (! empty($row->nomor_partai)) : ?>
                                <span class="fw-semibold d-block text-decoration-underline">Gelanggang <?= esc($row->nama_gelanggang ?? '-') ?></span>
                                <span class="muted-copy d-block">Partai <?= esc((string) $row->nomor_partai) ?></span>
                            <?php else : ?>
                                <span class="fst-italic muted-copy">Belum dijadwalkan</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center small">
                            <span class="fw-semibold d-block"><?= esc(trim(($row->nama_kategori_usia ?? '-') . ' ' . strtoupper((string) ($row->jenis_kelamin ?? '')))) ?></span>
                            <span class="muted-copy"><?= esc(ucwords(trim(($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-') . ' Pool ' . ($row->nomor_pool ?? '-')))) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="d-block px-2 py-1 rounded-2 <?= $pesertaBiru['fallback'] ? 'fst-italic border border-primary-subtle' : 'bg-blue text-white' ?>">
                                <span class="fw-semibold d-block"><?= esc($pesertaBiru['title']) ?></span>
                            </span>
                            <span class="muted-copy d-block mt-1 small"><?= esc($pesertaBiru['subtitle']) ?></span>
                        </td>
                        <td class="text-center"><span class="badge bg-blue"><?= esc((string) ($row->nilai_akhir_biru ?? 0)) ?></span></td>
                        <td class="text-center"><span class="fw-semibold text-decoration-underline"><?= esc($row->babak ?? '-') ?></span></td>
                        <td class="text-center"><span class="badge bg-red"><?= esc((string) ($row->nilai_akhir_merah ?? 0)) ?></span></td>
                        <td class="text-center">
                            <span class="d-block px-2 py-1 rounded-2 <?= $pesertaMerah['fallback'] ? 'fst-italic border border-danger-subtle' : 'bg-red text-white' ?>">
                                <span class="fw-semibold d-block"><?= esc($pesertaMerah['title']) ?></span>
                            </span>
                            <span class="muted-copy d-block mt-1 small"><?= esc($pesertaMerah['subtitle']) ?></span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/battle-seni/' . $row->id_battle_seni) ?>">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<style>
    .bg-blue { background-color: #0d6efd !important; color: #fff; }
    .bg-red { background-color: #dc3545 !important; color: #fff; }
</style>

<?php
$formatAtlet = static function (?string $nama, ?string $kontingen, $berat, $tinggi, ?string $calonPartai, ?string $calonGelanggang): array {
    if ($nama !== null && trim($nama) !== '') {
        $subtitleParts = [];
        $subtitleParts[] = $kontingen !== null && trim($kontingen) !== '' ? $kontingen : '-';

        if ($berat !== null || $tinggi !== null) {
            $subtitleParts[] = trim((string) ($berat ?? '-')) . ' Kg - ' . trim((string) ($tinggi ?? '-')) . ' Cm';
        }

        return [
            'title' => $nama,
            'subtitle' => implode(' | ', $subtitleParts),
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
        <table class="table admin-table admin-datatable-export align-middle mb-0">
            <thead>
                <tr>
                    <th>Arena</th>
                    <th>No Partai</th>
                    <th>Usia</th>
                    <th>Jenis Kelamin</th>
                    <th>Kelas</th>
                    <th>Pool</th>
                    <th class="text-center bg-blue text-white">Tim Biru</th>
                    <th class="text-center bg-blue text-white">Atlet Biru</th>
                    <th class="text-center">Poin Biru</th>
                    <th class="text-center">Babak</th>
                    <th class="text-center">Poin Merah</th>
                    <th class="text-center bg-red text-white">Atlet Merah</th>
                    <th class="text-center bg-red text-white">Tim Merah</th>
                    <th class="text-end no-export">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <?php
                    $atletBiru = $formatAtlet(
                        $row->nama_atlet_biru ?? null,
                        $row->nama_kontingen_biru ?? null,
                        $row->berat_badan_biru ?? null,
                        $row->tinggi_badan_biru ?? null,
                        isset($row->calon_atlet_biru) ? (string) $row->calon_atlet_biru : null,
                        $row->gelanggang_calon_atlet_biru ?? null,
                    );
                    $atletMerah = $formatAtlet(
                        $row->nama_atlet_merah ?? null,
                        $row->nama_kontingen_merah ?? null,
                        $row->berat_badan_merah ?? null,
                        $row->tinggi_badan_merah ?? null,
                        isset($row->calon_atlet_merah) ? (string) $row->calon_atlet_merah : null,
                        $row->gelanggang_calon_atlet_merah ?? null,
                    );
                    ?>
                    <tr>
                        <td class="text-center">
                            <?php if (! empty($row->nomor_partai)) : ?>
                                <?= esc($row->nama_gelanggang ?? '-') ?>
                            <?php else : ?>
                                <span class="fst-italic muted-copy">Belum dijadwalkan</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (! empty($row->nomor_partai)) : ?>
                                <?= esc((string) $row->nomor_partai) ?>
                            <?php else : ?>
                                <span class="fst-italic muted-copy">Belum dijadwalkan</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                        <td class="text-center"><?= esc(ucwords((string) ($row->jenis_kelamin ?? '-'))) ?></td>
                        <td class="text-center"><?= esc($row->label ?? '-') ?></td>
                        <td class="text-center"><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                        <td class="text-center small"><?= esc($row->nama_kontingen_biru ?? '-') ?></td>
                        <td class="text-center">
                            <span class="d-block px-2 py-1 rounded-2 <?= $atletBiru['fallback'] ? 'fst-italic border border-primary-subtle' : 'bg-blue text-white' ?>">
                                <span class="fw-semibold d-block"><?= esc($atletBiru['title']) ?></span>
                            </span>
                            <span class="muted-copy d-block mt-1 small"><?= esc($atletBiru['subtitle']) ?></span>
                        </td>
                        <td class="text-center"><span class="badge bg-blue"><?= esc((string) ($row->skor_biru ?? 0)) ?></span></td>
                        <td class="text-center"><span class="fw-semibold text-decoration-underline"><?= esc($row->babak ?? '-') ?></span></td>
                        <td class="text-center"><span class="badge bg-red"><?= esc((string) ($row->skor_merah ?? 0)) ?></span></td>
                        <td class="text-center">
                            <span class="d-block px-2 py-1 rounded-2 <?= $atletMerah['fallback'] ? 'fst-italic border border-danger-subtle' : 'bg-red text-white' ?>">
                                <span class="fw-semibold d-block"><?= esc($atletMerah['title']) ?></span>
                            </span>
                            <span class="muted-copy d-block mt-1 small"><?= esc($atletMerah['subtitle']) ?></span>
                        </td>
                        <td class="text-center small"><?= esc($row->nama_kontingen_merah ?? '-') ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/pertandingan-tanding/' . $row->id_pertandingan) ?>">Detail</a>
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

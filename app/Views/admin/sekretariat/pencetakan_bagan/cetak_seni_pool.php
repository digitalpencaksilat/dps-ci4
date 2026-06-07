<?php
/**
 * Cetak hasil seni pool (parity CI3 print/bagan/seni_pool/bagan_per_kategori_lomba).
 * Bukan bracket, tapi tabel hasil penampilan per pool.
 *
 * @var array<int,object>            $dataKompetisiSeni
 * @var array<int,array<int,object>> $penampilanPerKompetisi  keyed by id_kompetisi_seni
 */
$groups = [];
foreach (($dataKompetisiSeni ?? []) as $row) {
    $key = (string) ($row->nama_kategori_usia ?? '') . '|' . (string) ($row->jenis_kelamin ?? '');
    if (! isset($groups[$key])) {
        $groups[$key] = ['meta' => $row, 'rows' => []];
    }
    $groups[$key]['rows'][] = $row;
}
$brandLogoUrl = base_url('assets/images/brand/' . ($brandAbbr ?? 'dps') . '/logo.png');

// Format "Usia Dini 2 - Putra".
$fmtKategori = static function ($usia, $jk): string {
    $u = ucwords(strtolower(trim((string) $usia)));
    $j = ucwords(strtolower(trim((string) $jk)));
    return trim($u . ($j !== '' ? ' - ' . $j : ''));
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($eventName ?? 'Hasil Seni Pool') ?> - Hasil Seni Pool</title>
    <?php if (! empty($logoEvent)) : ?><link rel="icon" type="image/png" href="<?= esc((string) $logoEvent) ?>"><?php endif; ?>
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; }
        .bagan { page-break-after: always; }
        .cover { page-break-after: always; }
        .tabel-pool { width:100%; border-collapse:collapse; background:#fff; }
        .tabel-pool thead tr { background:#212529; color:#fff; }
        .tabel-pool thead th { padding:10px 14px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
        .tabel-pool tbody td { padding:10px 14px; font-size:13px; vertical-align:middle; border-bottom:1px solid #e9ecef; }
        .row-gold { border-left:4px solid #FFD700 !important; }
        .row-silver { border-left:4px solid #C0C0C0 !important; }
        .row-bronze { border-left:4px solid #CD7F32 !important; }
        .badge-match { display:inline-block; background:#e9ecef; color:#495057; font-size:11px; font-weight:700; padding:3px 10px; border-radius:4px; min-width:32px; text-align:center; }
        .badge-medal { display:inline-block; font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px; text-transform:uppercase; letter-spacing:.5px; }
        .badge-gold { background:#fff3cd !important; color:#856404 !important; border:1px solid #FFD700 !important; }
        .badge-silver { background:#f0f0f0 !important; color:#555 !important; border:1px solid #aaa !important; }
        .badge-bronze { background:#fdf0e0 !important; color:#8B4513 !important; border:1px solid #CD7F32 !important; }
        .badge-dq { background:#f8d7da !important; color:#721c24 !important; border:1px solid #f5c6cb !important; }
        .score-val { font-weight:700; font-size:14px; font-family:monospace; }
        .time-val { font-size:13px; font-family:monospace; color:#555; }
        .text-empty { color:#adb5bd; }
        .athlete-name { font-weight:600; font-size:13px; }
        .team-name { font-size:12px; color:#6c757d; text-transform:uppercase; }
        .watermark { position: fixed; bottom: 12px; right: 18px; font-size: 9pt; color:#777; display:flex; align-items:center; gap:8px; z-index:9999; }
        .watermark img { height: 20px; width:auto; opacity:.85; }
        @media print { .watermark, .head-title, .head-logo, .tabel-pool thead tr, .badge-medal { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="watermark">
        <img src="<?= esc($brandLogoUrl) ?>" alt="Logo" onerror="this.style.display='none'">
        <span>Powered by <strong><?= esc($brandName ?? 'Digital Pencak Silat') ?></strong> &copy; <?= date('Y') ?></span>
    </div>
    <div class="container-fluid px-0">
        <?php if ($groups === []) : ?>
            <div class="text-center py-5"><h4>Tidak ada data seni pool untuk dicetak.</h4></div>
        <?php endif; ?>

        <?php foreach ($groups as $group) : $meta = $group['meta']; ?>
            <div class="row justify-content-center cover min-vh-100 align-content-center">
                <?php if (! empty($logoEvent)) : ?>
                    <div class="col-3 mb-5 text-center"><img src="<?= esc((string) $logoEvent) ?>" alt="Logo Event" class="img-fluid"></div>
                <?php endif; ?>
                <div class="col-10 mt-5 text-center">
                    <h5 class="h1">Hasil Seni Pool</h5>
                    <h1 class="fw-bolder display-2"><?= esc($fmtKategori($meta->nama_kategori_usia ?? '-', $meta->jenis_kelamin ?? '')) ?></h1>
                    <p class="h4 text-muted"><?= esc(strtoupper((string) ($eventName ?? ''))) ?></p>
                </div>
            </div>

            <?php foreach ($group['rows'] as $kompetisi) : $rows = $penampilanPerKompetisi[(int) $kompetisi->id_kompetisi_seni] ?? [];
                $detailSeni = trim(ucwords(strtolower((string) ($kompetisi->jenis_seni ?? ''))) . ' ' . (string) ($kompetisi->nama_seni ?? ''));
                $headerTitle = $fmtKategori($kompetisi->nama_kategori_usia ?? '', $kompetisi->jenis_kelamin ?? '')
                    . ($detailSeni !== '' ? ' - ' . $detailSeni : '')
                    . ' - Pool ' . (string) ($kompetisi->nomor_pool ?? '');
            ?>
                <div class="row bagan w-100 m-0">
                    <div class="col-12 px-0">
                        <?= view('shared_components/print/medal_export_header', [
                            'title'    => strtoupper($headerTitle),
                            'subtitle' => $eventName ?? '',
                        ]) ?>

                        <div class="px-5 py-2">
                            <div class="card shadow-sm border-0">
                                <div class="card-body p-0">
                                    <?php if ($rows !== []) : ?>
                                        <div class="table-responsive">
                                            <table class="tabel-pool">
                                                <thead>
                                                    <tr>
                                                        <th style="width:70px;">Partai</th>
                                                        <th>Atlet / Tim</th>
                                                        <th style="width:170px;">Kontingen</th>
                                                        <th style="width:120px; text-align:right;">Nilai</th>
                                                        <th style="width:100px; text-align:center;">Waktu</th>
                                                        <th style="width:120px; text-align:center;">Medali</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($rows as $partai) :
                                                        $medali = strtolower((string) ($partai->jenis_medali_pool ?? ''));
                                                        $dq     = (int) ($partai->diskualifikasi ?? 0);
                                                        $nilai  = $partai->nilai_akhir ?? null;
                                                        $waktu  = (int) ($partai->waktu_tampil ?? 0);
                                                        $rowClass = $medali === 'emas' ? 'row-gold' : ($medali === 'perak' ? 'row-silver' : ($medali === 'perunggu' ? 'row-bronze' : ''));
                                                    ?>
                                                        <tr class="<?= $rowClass ?>">
                                                            <td class="text-center"><span class="badge-match"><?= esc((string) ($partai->nomor_partai ?? '-')) ?></span></td>
                                                            <td><div class="athlete-name"><?= esc(ucwords(strtolower((string) ($partai->anggota_kelompok_peserta_seni ?? '-')))) ?></div></td>
                                                            <td><div class="team-name"><?= esc((string) ($partai->nama_kontingen ?? '-')) ?></div></td>
                                                            <td class="text-end">
                                                                <?php if ($nilai !== null && (float) $nilai > 0) : ?>
                                                                    <span class="score-val"><?= esc(number_format((float) $nilai, 3)) ?></span>
                                                                <?php else : ?><span class="text-empty">&mdash;</span><?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($waktu > 0) : ?>
                                                                    <span class="time-val"><?= esc(sprintf('%02d:%02d', intdiv($waktu, 60), $waktu % 60)) ?></span>
                                                                <?php else : ?><span class="text-empty">&mdash;</span><?php endif; ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if ($dq === 1) : ?><span class="badge-medal badge-dq">DQ</span>
                                                                <?php elseif ($medali === 'emas') : ?><span class="badge-medal badge-gold">Emas</span>
                                                                <?php elseif ($medali === 'perak') : ?><span class="badge-medal badge-silver">Perak</span>
                                                                <?php elseif ($medali === 'perunggu') : ?><span class="badge-medal badge-bronze">Perunggu</span>
                                                                <?php else : ?><span class="text-empty">&mdash;</span><?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="text-end text-muted small px-3 py-2 mb-0">Total: <?= count($rows) ?> Peserta</p>
                                    <?php else : ?>
                                        <div class="text-center py-5 text-muted">
                                            <p class="fw-semibold mb-1">Belum Ada Hasil</p>
                                            <p class="small mb-0">Hasil penampilan belum tersedia.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 800);
        });
    </script>
</body>
</html>

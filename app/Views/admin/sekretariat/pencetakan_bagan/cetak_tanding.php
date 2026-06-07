<?php
/**
 * Cetak bagan tanding (parity CI3 print/bagan/tanding/bagan_per_kategori_lomba).
 * Kompetisi dikelompokkan per kategori usia + jenis kelamin; tiap grup punya cover,
 * lalu tiap pool dirender memakai partial bracket interaktif.
 * Header bracket memakai partial print/medal_export_header (parity cetak perolehan medali).
 *
 * @var array<int,object> $dataKompetisiTanding
 */
$groups = [];
foreach (($dataKompetisiTanding ?? []) as $row) {
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
    <title><?= esc($eventName ?? 'Bagan Tanding') ?> - Bagan Pertandingan</title>
    <?php if (! empty($logoEvent)) : ?><link rel="icon" type="image/png" href="<?= esc((string) $logoEvent) ?>"><?php endif; ?>
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/bracket-pertandingan/jquery.bracket.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/bracket-pertandingan/bracket.css') ?>">
    <script src="<?= online_asset('jquery_3_js') ?>"></script>
    <script src="<?= base_url('assets/bracket-pertandingan/jquery.bracket.min.js') ?>"></script>
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; }
        .bagan { page-break-after: always; }
        .cover { page-break-after: always; }
        .watermark { position: fixed; bottom: 12px; right: 18px; font-size: 9pt; color:#777; display:flex; align-items:center; gap:8px; z-index:9999; }
        .watermark img { height: 20px; width:auto; opacity:.85; }
        @media print { .watermark { -webkit-print-color-adjust: exact; print-color-adjust: exact; } .medal-print-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="watermark">
        <img src="<?= esc($brandLogoUrl) ?>" alt="Logo" onerror="this.style.display='none'">
        <span>Powered by <strong><?= esc($brandName ?? 'Digital Pencak Silat') ?></strong> &copy; <?= date('Y') ?></span>
    </div>
    <div class="container-fluid">
        <?php if ($groups === []) : ?>
            <div class="text-center py-5"><h4>Tidak ada bagan tanding untuk dicetak.</h4></div>
        <?php endif; ?>

        <?php foreach ($groups as $group) : $meta = $group['meta']; ?>
            <div class="row justify-content-center cover min-vh-100 align-content-center">
                <?php if (! empty($logoEvent)) : ?>
                    <div class="col-3 mb-5 text-center"><img src="<?= esc((string) $logoEvent) ?>" alt="Logo Event" class="img-fluid"></div>
                <?php endif; ?>
                <div class="col-10 mt-5 text-center">
                    <h5 class="h1">Bagan Pertandingan</h5>
                    <h1 class="fw-bolder display-2"><?= esc($fmtKategori($meta->nama_kategori_usia ?? '-', $meta->jenis_kelamin ?? '')) ?></h1>
                    <p class="h4 text-muted"><?= esc(strtoupper((string) ($eventName ?? ''))) ?></p>
                </div>
            </div>

            <?php foreach ($group['rows'] as $kompetisi) :
                $detail = trim((string) ($kompetisi->label ?? ''));
                if (($kompetisi->jenis_perlombaan ?? '') === 'pemasalan') {
                    $detail = trim($detail . ' Pool ' . (string) ($kompetisi->nomor_pool ?? ''));
                }
                $headerTitle = $fmtKategori($kompetisi->nama_kategori_usia ?? '', $kompetisi->jenis_kelamin ?? '')
                    . ($detail !== '' ? ' - ' . $detail : '');
            ?>
                <div class="row bagan">
                    <div class="col-12">
                        <?= view('shared_components/print/medal_export_header', [
                            'title'    => strtoupper($headerTitle),
                            'subtitle' => $eventName ?? '',
                        ]) ?>
                        <div class="row py-2 px-4">
                            <div class="col-12 p-3 shadow-sm border rounded">
                                <?= view('shared_components/kompetisi_tanding/bagan_pertandingan', ['kompetisi_tanding' => $kompetisi, 'toggle_early_match' => false]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <script>
        window.addEventListener('load', function () {
            // Beri waktu bracket selesai render sebelum dialog cetak muncul.
            setTimeout(function () { window.print(); }, 1200);
        });
    </script>
</body>
</html>

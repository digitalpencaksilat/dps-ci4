<?php
/**
 * Cetak bagan tanding (parity CI3 print/bagan/tanding/bagan_per_kategori_lomba).
 * Kompetisi dikelompokkan per kategori usia + jenis kelamin; tiap grup punya cover,
 * lalu tiap pool dirender memakai partial bracket interaktif.
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
helper('text');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($eventName ?? 'Bagan Tanding') ?> - Bagan Tanding</title>
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
        .bracket-header { border-radius: 10px; overflow: hidden; }
        .bracket-header .head-logo { background:#fff; display:flex; align-items:center; justify-content:center; }
        .bracket-header .head-logo img { max-height:64px; max-width:90%; object-fit:contain; }
        .bracket-header .head-title { background:#111827; color:#fff; }
        .watermark { position: fixed; bottom: 12px; right: 18px; font-size: 9pt; color:#777; display:flex; align-items:center; gap:8px; z-index:9999; }
        .watermark img { height: 20px; width:auto; opacity:.85; }
        @media print { .watermark { -webkit-print-color-adjust: exact; print-color-adjust: exact; } .head-title, .head-logo { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
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
                    <h5 class="h1">Match Bracket</h5>
                    <h1 class="fw-bolder display-2"><?= esc(($meta->nama_kategori_usia ?? '-') . ' ' . ucwords((string) ($meta->jenis_kelamin ?? ''))) ?></h1>
                    <p class="h4 text-muted"><?= esc(strtoupper((string) ($eventName ?? ''))) ?></p>
                </div>
            </div>

            <?php foreach ($group['rows'] as $kompetisi) : ?>
                <div class="row bagan">
                    <div class="col-12">
                        <div class="row mb-3 justify-content-center bracket-header mx-0 shadow-sm">
                            <div class="col-1 head-logo py-2">
                                <?php if (! empty($logoEvent)) : ?><img src="<?= esc((string) $logoEvent) ?>" alt="Event"><?php endif; ?>
                            </div>
                            <div class="col-10 head-title py-3 text-center">
                                <p class="h6 mb-1"><?= esc($eventName ?? '') ?> &mdash; Match Bracket</p>
                                <p class="h3 m-0 fw-bolder">
                                    <?= esc(($kompetisi->nama_kategori_usia ?? '') . ' ' . ucwords((string) ($kompetisi->jenis_kelamin ?? '')) . ' - ' . trim((string) ($kompetisi->label ?? ''))) ?>
                                    <?= (($kompetisi->jenis_perlombaan ?? '') === 'pemasalan') ? ' Pool ' . esc((string) ($kompetisi->nomor_pool ?? '')) : '' ?>
                                </p>
                            </div>
                            <div class="col-1 head-logo py-2">
                                <?php if (! empty($logoHost)) : ?><img src="<?= esc((string) $logoHost) ?>" alt="Host"><?php endif; ?>
                            </div>
                        </div>
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

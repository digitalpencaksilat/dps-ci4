<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak ID Card</title>
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <style>
        @page {
            size: <?= ($paper_size ?? 'A6 portrait') === 'A4 portrait' ? 'A4 portrait' : 'A6 portrait' ?>;
            margin: 0;
        }
        <?php if (($paper_size ?? '') === 'A6 portrait') : ?>
        .kartu-peserta {
            width: 94mm;
            height: 129mm;
            position: relative;
            overflow: hidden;
            page-break-inside: avoid;
        }
        <?php else : ?>
        .kartu-peserta {
            width: 210mm;
            height: 297mm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
        }
        <?php endif; ?>
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
    <?= view('print/id_card/styles/card') ?>
    <?= view('print/id_card/styles/dynamic', [
        'layout' => $layout ?? [],
    ]) ?>
</head>
<body>
    <?= view($main_view ?? '', $data ?? get_defined_vars()) ?>
</body>
</html>

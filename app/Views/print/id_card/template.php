<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak ID Card</title>
    <style>
        @page {
            size: <?= ($paper_size ?? 'A6 portrait') === 'A4 portrait' ? 'A4 portrait' : 'A6 portrait' ?>;
            margin: 0;
        }
        .kartu-peserta {
            width: 94mm;
            height: 129mm;
            position: relative;
            overflow: hidden;
            page-break-inside: avoid;
        }
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }
        /* utility classes (lokal, pengganti Bootstrap supaya html2canvas tidak perlu fetch CDN) */
        .text-center { text-align: center; }
        .text-uppercase { text-transform: uppercase; }
        .img-fluid { max-width: 100%; height: auto; }
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

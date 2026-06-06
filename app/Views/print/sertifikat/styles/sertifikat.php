<style>
    body { margin: 0; }

    .sertifikat {
        background-size: 100% 100%;
        background-repeat: no-repeat;
        background-position: center center;
        position: relative;
        page-break-inside: avoid;
        width: 297mm;
        height: 210mm;
        <?php if (!empty($hide_bg)): ?>background-image: none !important;<?php endif; ?>
    }

    @page { size: A4 landscape; margin: 0; }
    @media print {
        html, body { height: 99.999999%; margin: 0; overflow: hidden; }
        button { display: none !important; }
    }

    <?php
    $l = $layout ?? [];
    $ff  = $l['font_family']  ?? "'Verdana'";
    $fw  = $l['font_weight']  ?? 'bolder';
    $elements = ['nomor', 'nama', 'kategori', 'kontingen', 'sekolah', 'qrcode'];
    foreach ($elements as $el):
        $pos    = $l[$el . '_position']   ?? 'auto';
        $fs     = $l[$el . '_font_size']  ?? '18px';
        $align  = $l[$el . '_text_align'] ?? 'left';
        $disp   = $l[$el . '_display']    ?? 'block';
    ?>
    .<?= $el ?> {
        position: absolute;
        inset: <?= $pos ?>;
        display: <?= $disp ?>;
    }
    .<?= $el ?> h1, .<?= $el ?> h2, .<?= $el ?> h3 {
        font-family: <?= $ff ?>;
        font-size: <?= $fs ?>;
        font-weight: <?= $fw ?>;
        text-align: <?= $align ?>;
        margin: 0;
    }
    <?php if ($el === 'qrcode'): ?>
    .qrcode { width: <?= $fs ?>; }
    .qrcode img { width: 100%; height: auto; }
    <?php endif; ?>
    <?php if ($align === 'center' && str_contains($pos, 'auto')): ?>
    .<?= $el ?> { transform: translateX(-50%); white-space: nowrap; }
    <?php endif; ?>
    <?php endforeach; ?>
</style>

<?php
/**
 * Header halaman/section admin bersama.
 *
 * Menggantikan pola legacy `card-header` + `card-title` (h6 polos) dengan
 * tipografi brand yang konsisten (eyebrow + section-title + subtitle).
 *
 * Letakkan di dalam wrapper `.admin-card` (mengikuti pola Data Atlet).
 *
 * Variabel:
 * @var string|null $eyebrow  Label kecil di atas judul (mis. "Sekretariat").
 * @var string      $title    Judul utama section/halaman.
 * @var string|null $subtitle Deskripsi singkat di bawah judul.
 * @var string|null $icon     Kelas FontAwesome opsional (mis. "fas fa-print").
 * @var string|null $actions  HTML mentah untuk tombol/badge aksi di kanan.
 * @var string|null $titleTag Tag judul (default "h3").
 * @var string|null $titleSize Kelas ukuran Bootstrap untuk judul (default "h4").
 */
$eyebrow   = $eyebrow   ?? null;
$title     = $title     ?? '';
$subtitle  = $subtitle  ?? null;
$icon      = $icon      ?? null;
$actions   = $actions   ?? null;
$titleTag  = $titleTag  ?? 'h3';
$titleSize = $titleSize ?? 'h4';
?>
<div class="admin-page-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <?php if ($eyebrow !== null && $eyebrow !== ''): ?>
            <p class="eyebrow mb-1"><?= esc($eyebrow) ?></p>
        <?php endif; ?>
        <<?= $titleTag ?> class="section-title <?= esc($titleSize) ?> mb-0">
            <?php if ($icon !== null && $icon !== ''): ?><i class="<?= esc($icon) ?> me-2"></i><?php endif; ?><?= esc($title) ?>
        </<?= $titleTag ?>>
        <?php if ($subtitle !== null && $subtitle !== ''): ?>
            <p class="muted-copy mb-0 mt-2"><?= esc($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php if ($actions !== null && $actions !== ''): ?>
        <div class="d-flex flex-wrap gap-2 align-items-center"><?= $actions ?></div>
    <?php endif; ?>
</div>

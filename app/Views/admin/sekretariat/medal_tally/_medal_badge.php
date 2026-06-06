<?php
$medal = (string) ($medal ?? '');
$classes = [
    'emas' => 'medal-badge emas',
    'perak' => 'medal-badge perak',
    'perunggu' => 'medal-badge perunggu',
];
?>
<?php if (isset($classes[$medal])) : ?>
    <span class="<?= esc($classes[$medal]) ?> text-capitalize"><?= esc($medal) ?></span>
<?php else : ?>
    <span class="text-muted">-</span>
<?php endif; ?>

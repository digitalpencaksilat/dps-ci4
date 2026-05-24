<?php
$medal = (string) ($medal ?? '');
$styles = [
    'emas' => 'background-color:#ffb322',
    'perak' => 'background-color:#b0b0b0',
    'perunggu' => 'background-color:#7c4800',
];
?>
<?php if (isset($styles[$medal])) : ?>
    <span class="badge text-white text-capitalize" style="<?= esc($styles[$medal]) ?>"><?= esc($medal) ?></span>
<?php else : ?>
    <span class="text-muted">-</span>
<?php endif; ?>

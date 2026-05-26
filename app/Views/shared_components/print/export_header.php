<div class="export-print-header">
    <h2><?= esc($title ?? 'Data Export') ?></h2>
    <?php if (! empty($subtitle)) : ?>
        <p><?= esc($subtitle) ?></p>
    <?php endif; ?>
    <p>Dicetak pada <?= esc(date('d/m/Y H:i')) ?></p>
</div>

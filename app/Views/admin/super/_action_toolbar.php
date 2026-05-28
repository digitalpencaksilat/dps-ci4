<?php
$eyebrow = $eyebrow ?? null;
$title = $title ?? '';
$description = $description ?? null;
$actions = $actions ?? [];
$meta = $meta ?? null;
$toolbarClass = trim((string) ($toolbarClass ?? 'mb-4'));
?>
<section class="admin-card <?= esc($toolbarClass) ?>">
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-start gap-3 gap-xl-4">
        <div class="pe-xl-3">
            <?php if ($eyebrow) : ?>
                <p class="eyebrow mb-1"><?= esc((string) $eyebrow) ?></p>
            <?php endif; ?>
            <h2 class="section-title h3 mb-2"><?= esc((string) $title) ?></h2>
            <?php if ($description) : ?>
                <p class="muted-copy mb-0"><?= $description ?></p>
            <?php endif; ?>
        </div>

        <div class="ms-xl-auto w-100 w-xl-auto">
            <div class="d-flex flex-column align-items-stretch align-items-xl-end gap-2">
                <?php if ($actions !== []) : ?>
                    <div class="d-flex flex-column flex-sm-row flex-wrap justify-content-stretch justify-content-sm-end gap-2 w-100">
                        <?php foreach ($actions as $action) : ?>
                            <?php
                            $tag = $action['tag'] ?? 'a';
                            $label = $action['label'] ?? '';
                            $class = trim('btn rounded-pill px-4 ' . ($action['class'] ?? 'btn-outline-secondary'));
                            $attrs = $action['attrs'] ?? [];
                            ?>
                            <?php if ($tag === 'button') : ?>
                                <button type="<?= esc((string) ($action['type'] ?? 'button'), 'attr') ?>" class="<?= esc($class, 'attr') ?>"
                                    <?php foreach ($attrs as $name => $value) : ?>
                                        <?= esc((string) $name) ?>="<?= esc((string) $value, 'attr') ?>"
                                    <?php endforeach; ?>><?= esc((string) $label) ?></button>
                            <?php else : ?>
                                <a href="<?= esc((string) ($action['href'] ?? '#'), 'attr') ?>" class="<?= esc($class, 'attr') ?>"
                                    <?php foreach ($attrs as $name => $value) : ?>
                                        <?= esc((string) $name) ?>="<?= esc((string) $value, 'attr') ?>"
                                    <?php endforeach; ?>><?= esc((string) $label) ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($meta) : ?>
                    <div class="d-flex flex-wrap justify-content-start justify-content-xl-end gap-2 w-100">
                        <?= $meta ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

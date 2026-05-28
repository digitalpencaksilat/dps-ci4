<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Event',
    'title' => 'Pengaturan Biaya Kontingen & Max Atlet',
    'description' => 'Migrasi dari modul lama CI3 untuk mengatur biaya pendaftaran kontingen dan batas maksimal atlet per kontingen.',
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/dashboard-pengaturan-event'),
            'label' => 'Kembali ke Dashboard',
            'class' => 'btn-outline-secondary',
        ],
    ],
]) ?>

<?php
$errors = $errors ?? [];
?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/pengaturan-kontingen/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>

        <?php foreach (($fields ?? []) as $field => $meta) : ?>
            <?php
            $value = old($field);
            if ($value === null) {
                $baseValue = (string) (($values ?? [])[$field] ?? $meta['default']);
                $value = ($meta['type'] ?? '') === 'currency'
                    ? number_format((int) $baseValue, 0, ',', '.')
                    : $baseValue;
            }
            ?>
            <?php if (($meta['type'] ?? '') === 'boolean') : ?>
                <div class="col-12">
                    <label class="form-label d-block" for="<?= esc($field) ?>_switch"><?= esc((string) $meta['label']) ?></label>
                    <input type="hidden" name="<?= esc($field) ?>" value="0">
                    <div class="form-check form-switch">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            role="switch"
                            id="<?= esc($field) ?>_switch"
                            name="<?= esc($field) ?>"
                            value="1"
                            <?= (string) $value === '1' ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="<?= esc($field) ?>_switch">Tampilkan dan aktifkan tagihan biaya kontingen</label>
                    </div>
                    <div class="form-text"><?= esc((string) ($meta['help'] ?? '')) ?></div>
                    <?php if (! empty(($errors ?? [])[$field])) : ?>
                        <div class="form-text text-danger"><?= esc((string) $errors[$field]) ?></div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="col-12 col-xl-6">
                    <label class="form-label" for="<?= esc($field) ?>"><?= esc((string) $meta['label']) ?></label>

                    <?php if (($meta['type'] ?? '') === 'currency') : ?>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="text"
                                id="<?= esc($field) ?>"
                                name="<?= esc($field) ?>"
                                class="form-control currency-input"
                                value="<?= esc((string) $value, 'attr') ?>"
                                placeholder="Contoh: 250.000"
                                required
                            >
                        </div>
                    <?php else : ?>
                        <input
                            type="number"
                            min="0"
                            step="1"
                            id="<?= esc($field) ?>"
                            name="<?= esc($field) ?>"
                            class="form-control"
                            value="<?= esc((string) $value, 'attr') ?>"
                            <?= $field === 'max_atlet_per_kontingen' ? 'min="1"' : '' ?>
                            required
                        >
                    <?php endif; ?>

                    <div class="form-text"><?= esc((string) ($meta['help'] ?? '')) ?></div>
                    <?php if (! empty(($errors ?? [])[$field])) : ?>
                        <div class="form-text text-danger"><?= esc((string) $errors[$field]) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Pengaturan</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

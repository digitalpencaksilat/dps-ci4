<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Event',
    'title' => 'Akses Pemilihan Kategori Perlombaan',
    'description' => 'Pengaturan ini menentukan apakah atlet boleh memilih kategori usia dan kelas tanding secara manual, serta aturan kontingen yang sama.',
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/dashboard-pengaturan-event'),
            'label' => 'Kembali ke Dashboard',
            'class' => 'btn-outline-secondary',
        ],
    ],
]) ?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/akses-pemilihan-kategori/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>

        <?php foreach (($fields ?? []) as $field => $label) : ?>
            <?php
            $checked = (bool) (old($field) !== null ? (old($field) !== '0' && old($field) !== '') : (($values ?? [])[$field] ?? false));
            ?>
            <div class="col-12">
                <div class="form-check admin-check">
                    <input class="form-check-input" type="checkbox" value="1" id="<?= esc($field) ?>" name="<?= esc($field) ?>" <?= $checked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= esc($field) ?>"><?= esc($label) ?></label>
                </div>
                <?php if (! empty(($errors ?? [])[$field])) : ?>
                    <div class="form-text text-danger mt-1"><?= esc((string) $errors[$field]) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Pengaturan</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

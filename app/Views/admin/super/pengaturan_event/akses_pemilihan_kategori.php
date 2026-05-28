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

<?php
$settingContext = [
    'perbolehkan_memilih_kategori_usia' => [
        'impact' => 'Kontingen dapat memilih kategori usia secara manual saat mendaftarkan atlet.',
        'warning' => 'Jika aktif, kategori usia tidak sepenuhnya dikunci oleh aturan umur otomatis.',
        'critical' => true,
    ],
    'perbolehkan_memilih_kelas_tanding' => [
        'impact' => 'Kontingen dapat memilih kelas tanding tanpa kunci otomatis berdasarkan berat badan.',
        'warning' => 'Setting ini kritis karena bisa membuat atlet masuk kelas yang tidak sesuai berat aktual.',
        'critical' => true,
    ],
];
?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/akses-pemilihan-kategori/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>

        <?php foreach (($fields ?? []) as $field => $label) : ?>
            <?php
            $checked = (bool) (old($field) !== null ? (old($field) !== '0' && old($field) !== '') : (($values ?? [])[$field] ?? false));
            $context = $settingContext[$field] ?? [
                'impact' => 'Mengubah aturan pemilihan kategori oleh kontingen.',
                'warning' => 'Pastikan perubahan sesuai regulasi event sebelum disimpan.',
                'critical' => true,
            ];
            ?>
            <div class="col-12 col-xl-6">
                <label class="setting-card <?= $checked ? 'is-active' : 'is-inactive' ?> <?= $context['critical'] ? 'is-critical' : '' ?>" for="<?= esc($field) ?>">
                    <input class="setting-card-input" type="checkbox" value="1" id="<?= esc($field) ?>" name="<?= esc($field) ?>" <?= $checked ? 'checked' : '' ?>>
                    <span class="setting-card-main">
                        <span class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <span>
                                <span class="setting-card-title"><?= esc($label) ?></span>
                                <span class="setting-card-impact"><?= esc($context['impact']) ?></span>
                            </span>
                            <span class="setting-status-badge <?= $checked ? 'active' : 'inactive' ?>">
                                <?= $checked ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </span>
                        <span class="setting-card-warning <?= $context['critical'] ? 'text-danger' : 'text-muted' ?>">
                            <i class="fas fa-triangle-exclamation me-1"></i><?= esc($context['warning']) ?>
                        </span>
                    </span>
                </label>
                <?php if (! empty(($errors ?? [])[$field])) : ?>
                    <div class="form-text text-danger mt-1"><?= esc((string) $errors[$field]) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Pengaturan</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

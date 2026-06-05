<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Event',
    'title' => 'Profil Kejuaraan',
    'description' => 'Form ini merupakan migrasi native CodeIgniter 4 untuk pengaturan profil dasar kejuaraan.',
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
    <form action="<?= base_url('admin/super/pengaturan-event/profil-kejuaraan/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
        <?php foreach (($fields ?? []) as $field => $label) : ?>
            <?php
            $isTextarea = $field === 'landing_page_description';
            $value = old($field, $values[$field] ?? '');
            ?>
            <div class="col-12 <?= $isTextarea ? '' : 'col-lg-6' ?>">
                <label for="<?= esc($field) ?>" class="form-label"><?= esc($label) ?></label>
                <?php if ($isTextarea) : ?>
                    <textarea class="form-control" id="<?= esc($field) ?>" name="<?= esc($field) ?>" rows="4"><?= esc((string) $value) ?></textarea>
                <?php else : ?>
                    <input type="text" class="form-control" id="<?= esc($field) ?>" name="<?= esc($field) ?>" value="<?= esc((string) $value) ?>">
                <?php endif; ?>
                <?php if (! empty(($errors ?? [])[$field])) : ?>
                    <div class="form-text text-danger"><?= esc((string) $errors[$field]) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="col-12">
            <hr class="my-2">
            <label class="form-label fw-semibold">Kategori Pertandingan di Landing Page</label>
            <p class="text-muted small mb-3">Centang kategori yang ingin ditampilkan sebagai card di halaman utama. Ukuran card akan menyesuaikan otomatis.</p>
            <div class="d-flex flex-wrap gap-3">
                <?php foreach (($categoryCards ?? []) as $card) : ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="card_<?= esc($card['key']) ?>"
                               id="card_<?= esc($card['key']) ?>"
                               value="1"
                               style="accent-color: #dc3545;"
                               <?= !empty($card['active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="card_<?= esc($card['key']) ?>">
                            <i class="<?= esc($card['icon']) ?> me-1"></i><?= esc($card['label']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Profil Kejuaraan</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Event</p>
            <h2 class="section-title h3 mb-3">Konten Halaman Landing</h2>
            <p class="muted-copy mb-0">Konten ini digunakan untuk menampilkan informasi dasar kegiatan pada halaman landing.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-start">
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-light rounded-pill">Kembali ke Dashboard</a>
        </div>
    </div>
</section>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/konten-landing/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>

        <?php foreach (($fields ?? []) as $field => $label) : ?>
            <?php
            $isTextarea = $field === 'deskripsi';
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
                    <div class="form-text text-danger mt-1"><?= esc((string) $errors[$field]) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary rounded-pill">Simpan Konten</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

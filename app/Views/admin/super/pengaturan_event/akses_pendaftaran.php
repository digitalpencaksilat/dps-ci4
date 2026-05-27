<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Event</p>
            <h2 class="section-title h3 mb-3">Akses Pendaftaran Peserta</h2>
            <p class="muted-copy mb-0">Pengaturan ini mengatur apakah kontingen dapat mendaftar, login, input atlet, memilih kategori, dan melakukan pembayaran.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-start">
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-light rounded-pill">Kembali ke Dashboard</a>
        </div>
    </div>
</section>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/akses-pendaftaran/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>

        <?php foreach (($fields ?? []) as $field => $label) : ?>
            <?php
            $checked = (bool) (old($field) !== null ? (old($field) !== '0' && old($field) !== '') : (($values ?? [])[$field] ?? false));
            ?>
            <div class="col-12 col-lg-6">
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
            <button type="submit" class="btn btn-primary rounded-pill">Simpan Pengaturan</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

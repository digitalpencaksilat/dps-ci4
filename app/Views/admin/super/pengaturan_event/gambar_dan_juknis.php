<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Event</p>
            <h2 class="section-title h3 mb-3">Gambar dan Juknis</h2>
            <p class="muted-copy mb-0">Upload poster, logo, dan technical handbook. File disimpan di folder <code>public/uploads</code> dan URL-nya disimpan ke <code>site_builder_settings</code>.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-start">
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-light rounded-pill">Kembali ke Dashboard</a>
        </div>
    </div>
</section>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/gambar-dan-juknis/update') ?>" method="post" enctype="multipart/form-data" class="row g-4">
        <?= csrf_field() ?>

        <?php foreach (($files ?? []) as $key => $meta) : ?>
            <?php
            $current = (string) (($values ?? [])[$key] ?? '');
            $isImage = str_starts_with(($meta['mimes'][0] ?? ''), 'image/');
            ?>
            <div class="col-12">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div class="flex-grow-1">
                        <label for="<?= esc($key) ?>" class="form-label mb-1"><?= esc((string) ($meta['label'] ?? $key)) ?></label>
                        <div class="muted-copy small mb-2">Max <?= esc((string) ($meta['maxKb'] ?? 0)) ?> KB</div>
                        <input class="form-control" type="file" id="<?= esc($key) ?>" name="<?= esc($key) ?>">
                        <?php if (! empty(($errors ?? [])[$key])) : ?>
                            <div class="form-text text-danger mt-1"><?= esc((string) $errors[$key]) ?></div>
                        <?php endif; ?>
                    </div>

                    <div style="min-width: 220px;" class="align-self-start">
                        <div class="muted-copy small mb-1">File saat ini</div>
                        <?php if ($current !== '') : ?>
                            <?php if ($isImage) : ?>
                                <a href="<?= esc($current) ?>" target="_blank" rel="noopener">
                                    <img src="<?= esc($current) ?>" alt="<?= esc($key) ?>" class="img-fluid rounded" style="max-height: 160px; object-fit: contain; background: rgba(255,255,255,0.05);">
                                </a>
                            <?php else : ?>
                                <a href="<?= esc($current) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">Lihat File</a>
                            <?php endif; ?>
                            <div class="small mt-2 text-break"><code><?= esc($current) ?></code></div>
                        <?php else : ?>
                            <div class="muted-copy">Belum ada</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Event',
    'title' => 'Gambar dan Juknis',
    'description' => 'Upload poster, logo, dan technical handbook. File disimpan di folder <code>public/uploads</code> dan URL-nya disimpan ke <code>site_builder_settings</code>.',
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
$buildAccept = static function (array $mimes): string {
    $extra = [];
    foreach ($mimes as $mime) {
        if ($mime === 'application/pdf') {
            $extra[] = '.pdf';
        }
        if ($mime === 'application/msword') {
            $extra[] = '.doc';
        }
        if ($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $extra[] = '.docx';
        }
        if ($mime === 'image/jpeg') {
            $extra[] = '.jpg';
            $extra[] = '.jpeg';
        }
        if ($mime === 'image/png') {
            $extra[] = '.png';
        }
    }

    return implode(',', array_unique(array_merge($mimes, $extra)));
};

$humanFormats = static function (array $mimes): string {
    $labels = [];
    foreach ($mimes as $mime) {
        if ($mime === 'application/pdf') {
            $labels[] = 'PDF';
        } elseif ($mime === 'application/msword') {
            $labels[] = 'DOC';
        } elseif ($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $labels[] = 'DOCX';
        } elseif ($mime === 'image/jpeg') {
            $labels[] = 'JPG/JPEG';
        } elseif ($mime === 'image/png') {
            $labels[] = 'PNG';
        }
    }

    return implode(', ', array_unique($labels));
};
?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/gambar-dan-juknis/update') ?>" method="post" enctype="multipart/form-data" class="row g-3">
        <?= csrf_field() ?>

        <?php foreach (($files ?? []) as $key => $meta) : ?>
            <?php
            $current = (string) (($values ?? [])[$key] ?? '');
            $isImage = str_starts_with(($meta['mimes'][0] ?? ''), 'image/');
            $fileName = $current !== '' ? basename((string) parse_url($current, PHP_URL_PATH)) : '';
            ?>
            <div class="col-12">
                <article class="asset-setting-card">
                    <div class="asset-setting-grid">
                        <div>
                            <h3 class="h5 mb-1"><?= esc((string) ($meta['label'] ?? $key)) ?></h3>
                            <p class="muted-copy small mb-3">Format: <?= esc($humanFormats((array) ($meta['mimes'] ?? []))) ?> · Maks <?= esc((string) ($meta['maxKb'] ?? 0)) ?> KB</p>

                            <label for="<?= esc($key) ?>" class="form-label fw-semibold">Pilih file baru</label>
                            <input class="form-control" type="file" id="<?= esc($key) ?>" name="<?= esc($key) ?>" accept="<?= esc($buildAccept((array) ($meta['mimes'] ?? []))) ?>">

                            <?php if (! empty(($errors ?? [])[$key])) : ?>
                                <div class="form-text text-danger mt-1"><?= esc((string) $errors[$key]) ?></div>
                            <?php else : ?>
                                <div class="form-text">Biarkan kosong jika tidak ingin mengganti file saat ini.</div>
                            <?php endif; ?>
                        </div>

                        <div class="asset-preview-panel">
                            <div class="asset-preview-header d-flex justify-content-between align-items-center gap-2">
                                <span class="small text-uppercase fw-semibold text-muted">File Saat Ini</span>
                                <span class="setting-status-badge <?= $current !== '' ? 'active' : 'inactive' ?>">
                                    <?= $current !== '' ? 'Tersedia' : 'Kosong' ?>
                                </span>
                            </div>

                            <div class="asset-preview-frame mt-2">
                                <?php if ($current !== '') : ?>
                                    <?php if ($isImage) : ?>
                                        <a href="<?= esc($current) ?>" target="_blank" rel="noopener" class="asset-preview-link" aria-label="Lihat pratinjau penuh <?= esc((string) ($meta['label'] ?? $key), 'attr') ?>">
                                            <img src="<?= esc($current) ?>" alt="<?= esc($key) ?>" class="asset-preview-image">
                                        </a>
                                    <?php else : ?>
                                        <div class="asset-file-preview">
                                            <i class="fas fa-file-lines"></i>
                                            <div class="small text-muted">Dokumen siap dibuka</div>
                                            <a href="<?= esc($current) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm rounded-pill mt-2">Buka File</a>
                                        </div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div class="asset-file-preview empty">
                                        <i class="fas fa-cloud-arrow-up"></i>
                                        <div class="small text-muted">Belum ada file.</div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($current !== '') : ?>
                                <div class="small mt-2 text-break"><code><?= esc($fileName !== '' ? $fileName : $current) ?></code></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>

        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

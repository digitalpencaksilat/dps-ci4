<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Tools / ID Card / Tata Letak',
    'title' => 'Pengaturan Tata Letak ID Card',
    'description' => 'Atur posisi, ukuran, dan visibilitas setiap elemen pada ID Card.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/sekretariat/id-card'), 'label' => 'Kembali', 'class' => 'btn-outline-secondary'],
    ],
]) ?>

<div class="row g-4">
    <div class="col-lg-4">
        <section class="admin-card">
            <div class="p-3">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach (($sections ?? []) as $sec => $label) : ?>
                        <a href="<?= base_url('admin/sekretariat/id-card/pengaturan-tata-letak/' . $sec) ?>"
                           class="btn btn-sm <?= ($currentSection ?? '') === $sec ? 'btn-danger' : 'btn-outline-secondary' ?> rounded-pill">
                            <?= esc($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <form action="<?= base_url('admin/sekretariat/id-card/simpan-tata-letak') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="section" value="<?= esc($currentSection ?? '') ?>">

                    <h6 class="fw-bold mb-3"><?= esc($sectionLabel ?? '') ?></h6>

                    <?php foreach (($fields ?? []) as $key => $value) : ?>
                        <div class="mb-3">
                            <label for="field_<?= esc($key) ?>" class="form-label small fw-semibold"><?= esc($key) ?></label>
                            <?php if (str_ends_with($key, '_display')) : ?>
                                <select class="form-select form-select-sm" id="field_<?= esc($key) ?>" name="<?= esc($key) ?>">
                                    <option value="block" <?= ($value ?? '') === 'block' ? 'selected' : '' ?>>block</option>
                                    <option value="none" <?= ($value ?? '') === 'none' ? 'selected' : '' ?>>none</option>
                                </select>
                            <?php elseif (str_contains($key, 'text_transform')) : ?>
                                <select class="form-select form-select-sm" id="field_<?= esc($key) ?>" name="<?= esc($key) ?>">
                                    <option value="none" <?= ($value ?? '') === 'none' ? 'selected' : '' ?>>none</option>
                                    <option value="uppercase" <?= ($value ?? '') === 'uppercase' ? 'selected' : '' ?>>uppercase</option>
                                    <option value="lowercase" <?= ($value ?? '') === 'lowercase' ? 'selected' : '' ?>>lowercase</option>
                                    <option value="capitalize" <?= ($value ?? '') === 'capitalize' ? 'selected' : '' ?>>capitalize</option>
                                </select>
                            <?php elseif (str_contains($key, 'text_align')) : ?>
                                <select class="form-select form-select-sm" id="field_<?= esc($key) ?>" name="<?= esc($key) ?>">
                                    <option value="left" <?= ($value ?? '') === 'left' ? 'selected' : '' ?>>left</option>
                                    <option value="center" <?= ($value ?? '') === 'center' ? 'selected' : '' ?>>center</option>
                                    <option value="right" <?= ($value ?? '') === 'right' ? 'selected' : '' ?>>right</option>
                                </select>
                            <?php elseif (str_contains($key, 'font_weight')) : ?>
                                <select class="form-select form-select-sm" id="field_<?= esc($key) ?>" name="<?= esc($key) ?>">
                                    <option value="normal" <?= ($value ?? '') === 'normal' ? 'selected' : '' ?>>normal</option>
                                    <option value="bold" <?= ($value ?? '') === 'bold' ? 'selected' : '' ?>>bold</option>
                                    <option value="bolder" <?= ($value ?? '') === 'bolder' ? 'selected' : '' ?>>bolder</option>
                                </select>
                            <?php elseif (str_contains($key, 'white_space')) : ?>
                                <select class="form-select form-select-sm" id="field_<?= esc($key) ?>" name="<?= esc($key) ?>">
                                    <option value="normal" <?= ($value ?? '') === 'normal' ? 'selected' : '' ?>>normal</option>
                                    <option value="nowrap" <?= ($value ?? '') === 'nowrap' ? 'selected' : '' ?>>nowrap</option>
                                </select>
                            <?php else : ?>
                                <input type="text" class="form-control form-control-sm" id="field_<?= esc($key) ?>" name="<?= esc($key) ?>" value="<?= esc((string) ($value ?? '')) ?>">
                            <?php endif; ?>
                            <?php if (! empty(($errors ?? [])[$key])) : ?>
                                <div class="form-text text-danger"><?= esc((string) $errors[$key]) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-danger rounded-pill w-100">Simpan Tata Letak</button>
                </form>
            </div>
        </section>
    </div>

    <div class="col-lg-8">
        <section class="admin-card">
            <div class="p-3 text-center">
                <h6 class="mb-2">Preview</h6>
                <iframe src="<?= base_url('admin/sekretariat/id-card/preview') ?>?t=<?= time() ?>"
                        style="width:100%; height:550px; border:1px solid #dee2e6; border-radius:8px;"
                        id="previewIframe"></iframe>
                <div class="text-muted small mt-2">Preview menggunakan satu peserta tanding teratas. Background dan tata letak mengikuti pengaturan terbaru.</div>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Cache-bust iframe preview setelah simpan tata letak.
    (function () {
        var iframe = document.getElementById('previewIframe');
        var form = document.querySelector('form[action$="simpan-tata-letak"]');
        if (!iframe || !form) return;
        form.addEventListener('submit', function () {
            // Setelah redirect ke halaman ini lagi, ?t=<?= time() ?> sudah cache-bust.
            // Ini hanya sebagai fallback bila redirect tidak terjadi.
            try {
                sessionStorage.setItem('id_card_layout_saved', '1');
            } catch (e) { /* noop */ }
        });

        try {
            if (sessionStorage.getItem('id_card_layout_saved') === '1') {
                sessionStorage.removeItem('id_card_layout_saved');
                // Force iframe reload lagi setelah halaman load (jika cached)
                setTimeout(function () {
                    var src = iframe.src;
                    iframe.src = src.indexOf('?') >= 0
                        ? src + '&_=' + Date.now()
                        : src + '?_=' + Date.now();
                }, 100);
            }
        } catch (e) { /* noop */ }
    })();
</script>
<?= $this->endSection() ?>

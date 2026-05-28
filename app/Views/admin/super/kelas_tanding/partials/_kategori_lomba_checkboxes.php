<div class="col-12">
    <label class="form-label d-block">Kategori Lomba Tanding</label>
    <?php if (empty($kategoriLombaRows)) : ?>
        <div class="alert alert-warning small mb-0">Belum ada Kategori Lomba Tanding. <a href="<?= base_url('admin/super/kategori-lomba') ?>">Buat terlebih dahulu</a>.</div>
    <?php else : ?>
        <div class="row g-2" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
            <?php foreach (($kategoriLombaRows ?? []) as $kategoriLomba) : ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-check-label w-100 py-2 px-3">
                        <input type="checkbox" class="form-check-input me-1" name="id_kategori_lomba[]" value="<?= esc((string) $kategoriLomba->id_kategori_lomba) ?>">
                        <?= esc($kategoriLomba->nama_kategori_usia ?? '-') ?>
                        <span class="muted-copy small text-capitalize">/ <?= esc($kategoriLomba->jenis_kelamin ?? '-') ?> / <?= esc($kategoriLomba->jenis_perlombaan ?? '-') ?></span>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-text text-danger mt-2">Pilih minimal satu kategori lomba tanding.</div>
    <?php endif; ?>
</div>

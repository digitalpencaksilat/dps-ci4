<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Kategori Lomba',
    'title' => 'Edit Kategori Usia',
    'description' => 'Perbarui satu kategori usia. Perubahan dapat memengaruhi kategori lomba yang terkait.',
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/kategori-usia'),
            'label' => 'Kembali',
            'class' => 'btn-outline-secondary',
        ],
    ],
]) ?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/kategori-usia/' . $row->id_kategori_usia . '/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12 col-lg-6">
            <label for="nama_kategori_usia" class="form-label">Nama Kategori Usia</label>
            <input type="text" class="form-control" id="nama_kategori_usia" name="nama_kategori_usia" value="<?= esc((string) old('nama_kategori_usia', $row->nama_kategori_usia ?? '')) ?>" required>
        </div>
        <div class="col-12 col-lg-6">
            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                <?php $selectedGender = (string) old('jenis_kelamin', $row->jenis_kelamin ?? ''); ?>
                <option value="putra" <?= $selectedGender === 'putra' ? 'selected' : '' ?>>Putra</option>
                <option value="putri" <?= $selectedGender === 'putri' ? 'selected' : '' ?>>Putri</option>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label for="min_umur" class="form-label">Min Umur</label>
            <input type="number" min="0" class="form-control" id="min_umur" name="min_umur" value="<?= esc((string) old('min_umur', $row->min_umur ?? '')) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label for="max_umur" class="form-label">Max Umur</label>
            <input type="number" min="0" class="form-control" id="max_umur" name="max_umur" value="<?= esc((string) old('max_umur', $row->max_umur ?? '')) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label for="acuan_tanggal" class="form-label">Acuan Tanggal</label>
            <input type="date" class="form-control" id="acuan_tanggal" name="acuan_tanggal" value="<?= esc((string) old('acuan_tanggal', $row->acuan_tanggal ?? '')) ?>">
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Perubahan</button>
            <a href="<?= base_url('admin/super/kategori-usia') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

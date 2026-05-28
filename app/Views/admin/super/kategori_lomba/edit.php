<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Kategori Lomba',
    'title' => 'Edit Kategori Lomba',
    'description' => 'Perubahan dapat memengaruhi kelas tanding, sub kategori seni, dan flow pendaftaran.',
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/kategori-lomba'),
            'label' => 'Kembali',
            'class' => 'btn-outline-secondary',
        ],
    ],
]) ?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/kategori-lomba/' . $row->id_kategori_lomba . '/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12 col-lg-6">
            <label for="id_kategori_usia" class="form-label">Kategori Usia</label>
            <?php $selectedKategoriUsia = (string) old('id_kategori_usia', $row->id_kategori_usia ?? ''); ?>
            <select class="form-select" id="id_kategori_usia" name="id_kategori_usia" required>
                <?php foreach (($kategoriUsiaRows ?? []) as $kategoriUsia) : ?>
                    <option value="<?= esc((string) $kategoriUsia->id_kategori_usia) ?>" <?= $selectedKategoriUsia === (string) $kategoriUsia->id_kategori_usia ? 'selected' : '' ?>>
                        <?= esc($kategoriUsia->nama_kategori_usia ?? '-') ?> / <?= esc($kategoriUsia->jenis_kelamin ?? '-') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-6">
            <label for="nama_kategori_lomba" class="form-label">Nama Kategori Lomba</label>
            <input type="text" class="form-control" id="nama_kategori_lomba" name="nama_kategori_lomba" value="<?= esc((string) old('nama_kategori_lomba', $row->nama_kategori_lomba ?? '')) ?>" required>
        </div>
        <div class="col-12 col-lg-6">
            <label for="jenis_perlombaan" class="form-label">Jenis Perlombaan</label>
            <input type="text" class="form-control" id="jenis_perlombaan" name="jenis_perlombaan" value="<?= esc((string) old('jenis_perlombaan', $row->jenis_perlombaan ?? '')) ?>" required>
        </div>
        <div class="col-12 col-lg-6">
            <label for="peraturan_pertandingan" class="form-label">Peraturan Pertandingan</label>
            <input type="text" class="form-control" id="peraturan_pertandingan" value="PERSILAT" readonly>
            <div class="form-text">Dikunci untuk menjaga format kategori dan penilaian tetap konsisten.</div>
        </div>
        <div class="col-12 col-md-4">
            <label for="jumlah_juri" class="form-label">Jumlah Juri</label>
            <input type="number" min="0" class="form-control" id="jumlah_juri" name="jumlah_juri" value="<?= esc((string) old('jumlah_juri', $row->jumlah_juri ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
            <label for="semua_dapat_medali" class="form-label">Semua Dapat Medali</label>
            <?php $selectedMedali = (string) old('semua_dapat_medali', $row->semua_dapat_medali ?? '0'); ?>
            <select class="form-select" id="semua_dapat_medali" name="semua_dapat_medali" required>
                <option value="0" <?= $selectedMedali === '0' ? 'selected' : '' ?>>Tidak</option>
                <option value="1" <?= $selectedMedali === '1' ? 'selected' : '' ?>>Ya</option>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label for="kuota_peserta" class="form-label">Kuota Peserta</label>
            <input type="number" min="0" class="form-control" id="kuota_peserta" name="kuota_peserta" value="<?= esc((string) old('kuota_peserta', $row->kuota_peserta ?? '')) ?>">
        </div>
        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Perubahan</button>
            <a href="<?= base_url('admin/super/kategori-lomba') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

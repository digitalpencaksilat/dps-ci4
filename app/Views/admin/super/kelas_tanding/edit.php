<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Edit Kelas Tanding',
    'title' => trim(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '') . ' - ' . ($row->label ?? '-')),
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/super/kelas-tanding'), 'label' => 'Kembali', 'class' => 'btn-outline-secondary'],
        ['tag' => 'a', 'href' => base_url('admin/super/kelas-tanding/' . $row->id_kelas_tanding), 'label' => 'Detail', 'class' => 'btn-outline-secondary'],
    ],
]) ?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/kelas-tanding/' . $row->id_kelas_tanding . '/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12 col-md-4"><label class="form-label" for="label">Label Kelas</label><input class="form-control" id="label" name="label" value="<?= esc((string) old('label', $row->label ?? '')) ?>" required></div>
        <div class="col-12 col-md-4"><label class="form-label" for="berat_minimal">Berat Minimal</label><input type="number" min="0" step="0.01" class="form-control" id="berat_minimal" name="berat_minimal" value="<?= esc((string) old('berat_minimal', $row->berat_minimal ?? '')) ?>" required></div>
        <div class="col-12 col-md-4"><label class="form-label" for="berat_maksimal">Berat Maksimal</label><input type="number" min="0" step="0.01" class="form-control" id="berat_maksimal" name="berat_maksimal" value="<?= esc((string) old('berat_maksimal', $row->berat_maksimal ?? '')) ?>" required></div>
        <div class="col-12 col-md-4"><label class="form-label" for="jumlah_ronde">Jumlah Ronde</label><input type="number" min="1" class="form-control" id="jumlah_ronde" name="jumlah_ronde" value="<?= esc((string) old('jumlah_ronde', $row->jumlah_ronde ?? '3')) ?>" required></div>
        <div class="col-12 col-md-4"><label class="form-label" for="waktu_per_ronde">Waktu Per Ronde</label><input type="number" min="1" class="form-control" id="waktu_per_ronde" name="waktu_per_ronde" value="<?= esc((string) old('waktu_per_ronde', $row->waktu_per_ronde ?? '120')) ?>" required></div>
        <div class="col-12 col-md-4"><label class="form-label" for="waktu_istirahat">Waktu Istirahat</label><input type="number" min="0" class="form-control" id="waktu_istirahat" name="waktu_istirahat" value="<?= esc((string) old('waktu_istirahat', $row->waktu_istirahat ?? '60')) ?>" required></div>
        <div class="col-12 col-md-4"><label class="form-label" for="juara_tiga_bersama">Juara Tiga Bersama</label><select class="form-select" id="juara_tiga_bersama" name="juara_tiga_bersama" required><option value="0" <?= old('juara_tiga_bersama', (string) ($row->juara_tiga_bersama ?? '1')) === '0' ? 'selected' : '' ?>>Tidak</option><option value="1" <?= old('juara_tiga_bersama', (string) ($row->juara_tiga_bersama ?? '1')) === '1' ? 'selected' : '' ?>>Ya</option></select></div>
        <div class="col-12 col-md-4"><label class="form-label" for="format_penilaian">Format Penilaian</label><input type="text" class="form-control" id="format_penilaian" value="PERSILAT" readonly><input type="hidden" name="format_penilaian" value="PERSILAT"></div>
        <div class="col-12 col-md-4"><label class="form-label" for="keterangan">Keterangan</label><input class="form-control" id="keterangan" name="keterangan" value="<?= esc((string) old('keterangan', $row->keterangan ?? '')) ?>"></div>
        <div class="col-12 col-md-6"><label class="form-label" for="biaya_pendaftaran_dn">Biaya Pendaftaran DN</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" class="form-control currency-input" id="biaya_pendaftaran_dn" name="biaya_pendaftaran_dn" value="<?= esc((string) old('biaya_pendaftaran_dn', isset($row->biaya_pendaftaran_dn) ? number_format((float) $row->biaya_pendaftaran_dn, 0, ',', '.') : '')) ?>" placeholder="Contoh: 250.000"></div></div>
        <div class="col-12 col-md-6"><label class="form-label" for="biaya_pendaftaran_ln">Biaya Pendaftaran LN</label><div class="input-group"><span class="input-group-text">Rp</span><input type="text" class="form-control currency-input" id="biaya_pendaftaran_ln" name="biaya_pendaftaran_ln" value="<?= esc((string) old('biaya_pendaftaran_ln', isset($row->biaya_pendaftaran_ln) ? number_format((float) $row->biaya_pendaftaran_ln, 0, ',', '.') : '')) ?>" placeholder="Contoh: 250.000"></div></div>
        <div class="col-12"><button type="submit" class="btn btn-danger rounded-pill">Simpan Perubahan</button></div>
    </form>
</section>
<?= $this->endSection() ?>

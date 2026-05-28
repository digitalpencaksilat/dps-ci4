<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Edit Sub Kategori Seni',
    'title' => trim(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '') . ' - ' . ($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-')),
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/sub-kategori-seni'),
            'label' => 'Kembali',
            'class' => 'btn-outline-secondary',
        ],
    ],
]) ?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/sub-kategori-seni/' . $row->id_sub_kategori_seni . '/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12 col-lg-6">
            <label for="nama_seni" class="form-label">Nama Seni / Nama Jurus</label>
            <input type="text" class="form-control" id="nama_seni" name="nama_seni" value="<?= esc((string) old('nama_seni', $row->nama_seni ?? '')) ?>" required>
        </div>
        <div class="col-12 col-lg-6">
            <label for="jenis_seni" class="form-label">Jenis Seni</label>
            <select class="form-select" id="jenis_seni" name="jenis_seni" required>
                <?php foreach (['tunggal', 'ganda', 'beregu', 'solo kreatif', 'perorangan', 'berpasangan', 'berkelompok'] as $jenisSeni) : ?>
                    <option value="<?= esc($jenisSeni) ?>" <?= old('jenis_seni', $row->jenis_seni ?? '') === $jenisSeni ? 'selected' : '' ?>><?= esc(ucwords($jenisSeni)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label for="jumlah_peserta" class="form-label">Jumlah Peserta</label>
            <input type="number" min="1" class="form-control" id="jumlah_peserta" name="jumlah_peserta" value="<?= esc((string) old('jumlah_peserta', $row->jumlah_peserta ?? '')) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label for="waktu" class="form-label">Waktu (detik)</label>
            <input type="number" min="0" class="form-control" id="waktu" name="waktu" value="<?= esc((string) old('waktu', $row->waktu ?? '')) ?>">
        </div>
        <div class="col-12 col-md-4">
            <label for="sistem_penampilan" class="form-label">Sistem Penampilan</label>
            <select class="form-select" id="sistem_penampilan" name="sistem_penampilan" required>
                <option value="pool" <?= old('sistem_penampilan', $row->sistem_penampilan ?? '') === 'pool' ? 'selected' : '' ?>>Sekali Tampil / Pool</option>
                <option value="battle" <?= old('sistem_penampilan', $row->sistem_penampilan ?? '') === 'battle' ? 'selected' : '' ?>>Battle / Sistem Gugur</option>
            </select>
        </div>
        <div class="col-12 col-md-6">
            <label for="biaya_pendaftaran_dn" class="form-label">Biaya Pendaftaran DN</label>
            <input type="number" min="0" step="0.01" class="form-control" id="biaya_pendaftaran_dn" name="biaya_pendaftaran_dn" value="<?= esc((string) old('biaya_pendaftaran_dn', $row->biaya_pendaftaran_dn ?? '')) ?>">
        </div>
        <div class="col-12 col-md-6">
            <label for="biaya_pendaftaran_ln" class="form-label">Biaya Pendaftaran LN</label>
            <input type="number" min="0" step="0.01" class="form-control" id="biaya_pendaftaran_ln" name="biaya_pendaftaran_ln" value="<?= esc((string) old('biaya_pendaftaran_ln', $row->biaya_pendaftaran_ln ?? '')) ?>">
        </div>
        <div class="col-12">
            <label for="format_penilaian" class="form-label">Format Penilaian</label>
            <input type="text" class="form-control" id="format_penilaian" value="persilat.json" disabled>
            <div class="form-text">Dikunci agar semua sub kategori seni memakai format penilaian PERSILAT.</div>
        </div>
        <div class="col-12">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="2"><?= esc((string) old('keterangan', $row->keterangan ?? '')) ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Perubahan</button>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

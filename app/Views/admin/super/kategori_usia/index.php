<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Master Data',
    'title' => 'Daftar Kategori Usia',
    'description' => 'Create mendukung pilihan putra dan putri sekaligus seperti flow CI3.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        [
            'tag' => 'button',
            'label' => 'Tambah Kategori Usia',
            'class' => 'btn-danger',
            'attrs' => [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modalTambahKategoriUsia',
            ],
        ],
    ],
]) ?>

<section class="admin-card">
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table admin-datatable-export align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori Usia</th>
                        <th>Jenis Kelamin</th>
                        <th>Min Umur</th>
                        <th>Max Umur</th>
                        <th>Acuan Tanggal</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row) : ?>
                        <tr>
                            <td><?= esc((string) ($row->id_kategori_usia ?? '-')) ?></td>
                            <td class="fw-semibold text-capitalize"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_kelamin ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($row->min_umur ?? '-')) ?></td>
                            <td class="text-end"><?= esc((string) ($row->max_umur ?? '-')) ?></td>
                            <td><?= esc((string) ($row->acuan_tanggal ?? '-')) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= base_url('admin/super/kategori-usia/' . $row->id_kategori_usia) ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Detail</a>
                                    <a href="<?= base_url('admin/super/kategori-usia/' . $row->id_kategori_usia . '/edit') ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                                    <form action="<?= base_url('admin/super/kategori-usia/' . $row->id_kategori_usia . '/delete') ?>" method="post" onsubmit="return confirmAdminAction(this, 'Hapus kategori usia?', 'Data kategori yang sudah dipakai lomba atau peserta mungkin tidak dapat dihapus.', 'Hapus')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modalTambahKategoriUsia" tabindex="-1" aria-labelledby="modalTambahKategoriUsiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="<?= base_url('admin/super/kategori-usia') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahKategoriUsiaLabel">Tambah Kategori Usia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <label for="nama_kategori_usia" class="form-label">Nama Kategori Usia</label>
                        <input type="text" class="form-control" id="nama_kategori_usia" name="nama_kategori_usia" value="<?= esc((string) old('nama_kategori_usia')) ?>" required>
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label d-block">Jenis Kelamin</label>
                        <div class="d-flex flex-wrap gap-3">
                            <label class="form-check-label"><input type="checkbox" class="form-check-input me-1" name="jenis_kelamin[]" value="putra"> Putra</label>
                            <label class="form-check-label"><input type="checkbox" class="form-check-input me-1" name="jenis_kelamin[]" value="putri"> Putri</label>
                        </div>
                        <div class="form-text text-danger">Pilih minimal satu jenis kelamin.</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="min_umur" class="form-label">Min Umur</label>
                        <input type="number" min="0" class="form-control" id="min_umur" name="min_umur" value="<?= esc((string) old('min_umur')) ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="max_umur" class="form-label">Max Umur</label>
                        <input type="number" min="0" class="form-control" id="max_umur" name="max_umur" value="<?= esc((string) old('max_umur')) ?>" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="acuan_tanggal" class="form-label">Acuan Tanggal</label>
                        <input type="date" class="form-control" id="acuan_tanggal" name="acuan_tanggal" value="<?= esc((string) old('acuan_tanggal')) ?>">
                        <div class="form-text">Biarkan kosong untuk menggunakan 1 Januari tahun ini.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill">Simpan Kategori Usia</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

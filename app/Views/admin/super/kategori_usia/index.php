<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Kategori Lomba</p>
            <h2 class="section-title h3 mb-3">Kategori Usia</h2>
            <p class="muted-copy mb-0">Kelola kategori usia sebagai pondasi kategori lomba dan sub kategori seni.</p>
        </div>
        <div class="d-flex flex-wrap align-items-start gap-2">
            <span class="status-badge <?= ($activeMode ?? '') === 'perngaturan_kategori_lomba' ? 'success' : 'warning' ?>">
                Mode: <?= esc(($activeMode ?? '') === 'perngaturan_kategori_lomba' ? 'perngaturan_kategori_lomba' : 'belum aktif') ?>
            </span>
            <a href="<?= base_url('admin/super/menu-tipe') ?>" class="btn btn-outline-light rounded-pill">Ganti Mode</a>
        </div>
    </div>
</section>

<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Master Data</p>
            <h3 class="section-title h4 mb-0">Daftar Kategori Usia</h3>
            <p class="muted-copy mb-0 mt-2">Create mendukung pilihan putra dan putri sekaligus seperti flow CI3.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="#formTambahKategoriUsia" class="btn btn-primary rounded-pill">Tambah Kategori Usia</a>
            <a href="<?= base_url('admin/super/kategori-lomba') ?>" class="btn btn-outline-light rounded-pill">Kategori Lomba</a>
            <span class="status-badge neutral">Total: <?= esc((string) count($rows ?? [])) ?></span>
        </div>
    </div>

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
                                    <a href="<?= base_url('admin/super/kategori-usia/' . $row->id_kategori_usia . '/edit') ?>" class="btn btn-sm btn-outline-light rounded-pill">Edit</a>
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

<section class="admin-card mt-4" id="formTambahKategoriUsia">
    <div class="mb-4">
        <p class="eyebrow mb-1">Form</p>
        <h3 class="section-title h4 mb-0">Tambah Kategori Usia</h3>
    </div>
    <form action="<?= base_url('admin/super/kategori-usia') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
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
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary rounded-pill">Simpan Kategori Usia</button>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

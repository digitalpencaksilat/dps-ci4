<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Kategori Lomba</p>
            <h2 class="section-title h3 mb-3">Kategori Lomba</h2>
            <p class="muted-copy mb-0">Kelola kategori lomba dan relasinya ke kategori usia.</p>
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
            <h3 class="section-title h4 mb-0">Daftar Kategori Lomba</h3>
            <p class="muted-copy mb-0 mt-2">Create mendukung beberapa kategori usia sekaligus untuk satu konfigurasi lomba.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="#formTambahKategoriLomba" class="btn btn-primary rounded-pill">Tambah Kategori Lomba</a>
            <a href="<?= base_url('admin/super/kategori-usia') ?>" class="btn btn-outline-light rounded-pill">Kategori Usia</a>
            <a href="<?= base_url('admin/super/sub-kategori-seni') ?>" class="btn btn-outline-light rounded-pill">Sub Kategori Seni</a>
            <span class="status-badge neutral">Total: <?= esc((string) count($rows ?? [])) ?></span>
        </div>
    </div>

    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table admin-datatable-export align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kategori Usia</th>
                        <th>Jenis Kelamin</th>
                        <th>Nama Kategori Lomba</th>
                        <th>Jenis Perlombaan</th>
                        <th>Peraturan</th>
                        <th>Jumlah Juri</th>
                        <th>Semua Dapat Medali</th>
                        <th>Kuota Peserta</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row) : ?>
                        <tr>
                            <td><?= esc((string) ($row->id_kategori_lomba ?? '-')) ?></td>
                            <td class="text-capitalize"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_kelamin ?? '-') ?></td>
                            <td class="fw-semibold text-capitalize"><?= esc($row->nama_kategori_lomba ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_perlombaan ?? '-') ?></td>
                            <td><?= esc($row->peraturan_pertandingan ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($row->jumlah_juri ?? '-')) ?></td>
                            <td><?= esc((string) ($row->semua_dapat_medali ?? '-')) ?></td>
                            <td class="text-end"><?= esc((string) ($row->kuota_peserta ?? '-')) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= base_url('admin/super/kategori-lomba/' . $row->id_kategori_lomba . '/edit') ?>" class="btn btn-sm btn-outline-light rounded-pill">Edit</a>
                                    <form action="<?= base_url('admin/super/kategori-lomba/' . $row->id_kategori_lomba . '/delete') ?>" method="post" onsubmit="return confirmAdminAction(this, 'Hapus kategori lomba?', 'Data kategori yang sudah dipakai sub kategori seni, kelas tanding, atau peserta mungkin tidak dapat dihapus.', 'Hapus')">
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

<section class="admin-card mt-4" id="formTambahKategoriLomba">
    <div class="mb-4">
        <p class="eyebrow mb-1">Form</p>
        <h3 class="section-title h4 mb-0">Tambah Kategori Lomba</h3>
    </div>
    <form action="<?= base_url('admin/super/kategori-lomba') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12">
            <label class="form-label d-block">Kategori Usia</label>
            <div class="row g-2">
                <?php foreach (($kategoriUsiaRows ?? []) as $kategoriUsia) : ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <label class="form-check-label admin-card w-100 py-2 px-3">
                            <input type="checkbox" class="form-check-input me-1" name="id_kategori_usia[]" value="<?= esc((string) $kategoriUsia->id_kategori_usia) ?>">
                            <?= esc($kategoriUsia->nama_kategori_usia ?? '-') ?>
                            <span class="muted-copy small text-capitalize">/ <?= esc($kategoriUsia->jenis_kelamin ?? '-') ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <label for="nama_kategori_lomba" class="form-label">Nama Kategori Lomba</label>
            <input type="text" class="form-control" id="nama_kategori_lomba" name="nama_kategori_lomba" value="<?= esc((string) old('nama_kategori_lomba')) ?>" placeholder="Contoh: tanding / seni" required>
        </div>
        <div class="col-12 col-lg-6">
            <label for="jenis_perlombaan" class="form-label">Jenis Perlombaan</label>
            <input type="text" class="form-control" id="jenis_perlombaan" name="jenis_perlombaan" value="<?= esc((string) old('jenis_perlombaan')) ?>" required>
        </div>
        <div class="col-12 col-lg-6">
            <label for="peraturan_pertandingan" class="form-label">Peraturan Pertandingan</label>
            <input type="text" class="form-control" id="peraturan_pertandingan" name="peraturan_pertandingan" value="<?= esc((string) old('peraturan_pertandingan')) ?>" required>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="jumlah_juri" class="form-label">Jumlah Juri</label>
            <input type="number" min="0" class="form-control" id="jumlah_juri" name="jumlah_juri" value="<?= esc((string) old('jumlah_juri')) ?>">
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="semua_dapat_medali" class="form-label">Semua Dapat Medali</label>
            <select class="form-select" id="semua_dapat_medali" name="semua_dapat_medali" required>
                <option value="0" <?= old('semua_dapat_medali', '0') === '0' ? 'selected' : '' ?>>Tidak</option>
                <option value="1" <?= old('semua_dapat_medali') === '1' ? 'selected' : '' ?>>Ya</option>
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <label for="kuota_peserta" class="form-label">Kuota Peserta</label>
            <input type="number" min="0" class="form-control" id="kuota_peserta" name="kuota_peserta" value="<?= esc((string) old('kuota_peserta')) ?>">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary rounded-pill">Simpan Kategori Lomba</button>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

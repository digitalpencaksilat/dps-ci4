<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Master Data',
    'title' => 'Daftar Kategori Lomba',
    'description' => 'Create mendukung beberapa kategori usia sekaligus untuk satu konfigurasi lomba.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        [
            'tag' => 'button',
            'label' => 'Tambah Kategori Lomba',
            'class' => 'btn-danger',
            'attrs' => [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modalTambahKategoriLomba',
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
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Aksi
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= base_url('admin/super/kategori-lomba/' . $row->id_kategori_lomba . '/edit') ?>">
                                                <i class="fas fa-pen me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="<?= base_url('admin/super/kategori-lomba/' . $row->id_kategori_lomba . '/delete') ?>" method="post" onsubmit="return confirmAdminAction(this, 'Hapus kategori lomba?', 'Data kategori yang sudah dipakai sub kategori seni, kelas tanding, atau peserta mungkin tidak dapat dihapus.', 'Hapus')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt me-2"></i>Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modalTambahKategoriLomba" tabindex="-1" aria-labelledby="modalTambahKategoriLombaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form action="<?= base_url('admin/super/kategori-lomba') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahKategoriLombaLabel">Tambah Kategori Lomba</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
        <div class="col-12">
            <label class="form-label d-block">Kategori Usia</label>
            <?php if (empty($kategoriUsiaRows)) : ?>
                <div class="alert alert-warning small mb-0">Belum ada Kategori Usia. <a href="<?= base_url('admin/super/kategori-usia') ?>">Buat terlebih dahulu</a>.</div>
            <?php else : ?>
                <div class="row g-2" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
                    <?php foreach (($kategoriUsiaRows ?? []) as $kategoriUsia) : ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-check-label w-100 py-2 px-3">
                                <input type="checkbox" class="form-check-input me-1" name="id_kategori_usia[]" value="<?= esc((string) $kategoriUsia->id_kategori_usia) ?>">
                                <?= esc($kategoriUsia->nama_kategori_usia ?? '-') ?>
                                <span class="muted-copy small text-capitalize">/ <?= esc($kategoriUsia->jenis_kelamin ?? '-') ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-text text-danger mt-2">Pilih minimal satu kategori usia.</div>
            <?php endif; ?>
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
            <input type="text" class="form-control" id="peraturan_pertandingan" value="PERSILAT" readonly>
            <input type="hidden" name="peraturan_pertandingan" value="PERSILAT">
            <div class="form-text">Dikunci untuk menjaga format kategori dan penilaian tetap konsisten.</div>
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
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill" <?= empty($kategoriUsiaRows) ? 'disabled' : '' ?>>Simpan Kategori Lomba</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

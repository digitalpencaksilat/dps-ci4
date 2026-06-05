<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Master Data',
    'title' => 'Daftar Sub Kategori Seni',
    'description' => 'Create mendukung beberapa kategori lomba seni sekaligus dan otomatis membuat pool pertama.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/kategori-usia'),
            'label' => 'Kategori Usia',
            'class' => 'btn-outline-secondary',
        ],
        [
            'tag' => 'a',
            'href' => base_url('admin/super/kategori-lomba'),
            'label' => 'Kategori Lomba',
            'class' => 'btn-outline-secondary',
        ],
        [
            'tag' => 'button',
            'label' => 'Ubah Peserta Per Pool',
            'class' => 'btn-outline-danger',
            'attrs' => [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modalUbahJumlahPesertaPerPool',
            ],
        ],
        [
            'tag' => 'button',
            'label' => 'Tambah Sub Kategori Seni',
            'class' => 'btn-danger',
            'attrs' => [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modalTambahSubKategoriSeni',
            ],
        ],
    ],
    'meta' => '<span class="status-badge neutral">Total: ' . esc((string) count($rows ?? [])) . '</span>',
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
                        <th>Kategori Lomba</th>
                        <th>Nama Seni</th>
                        <th>Jenis Seni</th>
                        <th>Jumlah Peserta</th>
                        <th>Waktu</th>
                        <th>Biaya DN</th>
                        <th>Biaya LN</th>
                        <th>Format Penilaian</th>
                        <th>Sistem Penampilan</th>
                        <th>Pool</th>
                        <th>Keterangan</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row) : ?>
                        <tr>
                            <td><?= esc((string) ($row->id_sub_kategori_seni ?? '-')) ?></td>
                            <td class="text-capitalize"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_kelamin ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->nama_kategori_lomba ?? '-') ?></td>
                            <td class="fw-semibold text-capitalize"><?= esc($row->nama_seni ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_seni ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($row->jumlah_peserta ?? '-')) ?></td>
                            <td><?= esc((string) ($row->waktu ?? '-')) ?></td>
                            <td><?= esc((string) ($row->biaya_pendaftaran_dn ?? '-')) ?></td>
                            <td><?= esc((string) ($row->biaya_pendaftaran_ln ?? '-')) ?></td>
                            <td><?= esc($row->format_penilaian ?? '-') ?></td>
                            <td><?= esc($row->sistem_penampilan ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($row->jumlah_pool ?? 0)) ?></td>
                            <td><?= esc($row->keterangan ?? '-') ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= base_url('admin/super/sub-kategori-seni/' . $row->id_sub_kategori_seni) ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Detail</a>
                                    <a href="<?= base_url('admin/super/sub-kategori-seni/' . $row->id_sub_kategori_seni . '/edit') ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                                    <form action="<?= base_url('admin/super/sub-kategori-seni/' . $row->id_sub_kategori_seni . '/delete') ?>" method="post" onsubmit="return confirmAdminAction(this, 'Hapus sub kategori seni?', 'Data pool, kelompok peserta, jadwal, dan penilaian terkait mungkin membuat hapus gagal.', 'Hapus')">
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

<div class="modal fade" id="modalTambahSubKategoriSeni" tabindex="-1" aria-labelledby="modalTambahSubKategoriSeniLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form action="<?= base_url('admin/super/sub-kategori-seni') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header align-items-start">
                <div>
                    <h5 class="modal-title" id="modalTambahSubKategoriSeniLabel">Tambah Sub Kategori Seni</h5>
                    <p class="muted-copy mb-0 small">Setiap kategori lomba seni yang dipilih akan dibuatkan sub kategori dan pool pertama dalam satu transaksi.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
        <div class="col-12">
            <label class="form-label d-block">Kategori Lomba Seni</label>
            <?php if (empty($kategoriLombaRows)) : ?>
                <div class="alert alert-warning small mb-0">Belum ada Kategori Lomba Seni. <a href="<?= base_url('admin/super/kategori-lomba') ?>">Buat terlebih dahulu</a>.</div>
            <?php else : ?>
                <div class="row g-2" style="max-height: 250px; overflow-y: auto; overflow-x: hidden;">
                    <?php foreach (($kategoriLombaRows ?? []) as $kategoriLomba) : ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <label class="form-check-label w-100 py-2 px-3">
                                <input type="checkbox" class="form-check-input me-1" name="id_kategori_lomba[]" value="<?= esc((string) $kategoriLomba->id_kategori_lomba) ?>">
                                <?= esc($kategoriLomba->nama_kategori_usia ?? '-') ?>
                                <span class="muted-copy small text-capitalize">/ <?= esc($kategoriLomba->jenis_kelamin ?? '-') ?> / <?= esc($kategoriLomba->peraturan_pertandingan ?? '-') ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-text text-danger mt-2">Pilih minimal satu kategori lomba seni.</div>
            <?php endif; ?>
        </div>
        <div class="col-12 col-lg-6">
            <label for="nama_seni" class="form-label">Nama Seni / Nama Jurus</label>
            <input type="text" class="form-control" id="nama_seni" name="nama_seni" value="<?= esc((string) old('nama_seni')) ?>" required>
        </div>
        <div class="col-12 col-lg-6">
            <label for="jenis_seni" class="form-label">Jenis Seni</label>
            <select class="form-select" id="jenis_seni" name="jenis_seni" required>
                <?php foreach (['tunggal', 'ganda', 'beregu', 'solo kreatif', 'perorangan', 'berpasangan', 'berkelompok'] as $jenisSeni) : ?>
                    <option value="<?= esc($jenisSeni) ?>" <?= old('jenis_seni', 'tunggal') === $jenisSeni ? 'selected' : '' ?>><?= esc(ucwords($jenisSeni)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4">
            <label for="jumlah_peserta" class="form-label">Jumlah Peserta</label>
            <input type="number" min="1" class="form-control" id="jumlah_peserta" name="jumlah_peserta" value="<?= esc((string) old('jumlah_peserta', '1')) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label for="max_peserta" class="form-label">Max Peserta Per Pool</label>
            <input type="number" min="1" class="form-control" id="max_peserta" name="max_peserta" value="<?= esc((string) old('max_peserta', '4')) ?>" required>
        </div>
        <div class="col-12 col-md-4">
            <label for="waktu" class="form-label">Waktu (detik)</label>
            <input type="number" min="0" class="form-control" id="waktu" name="waktu" value="<?= esc((string) old('waktu')) ?>">
        </div>
        <div class="col-12 col-md-6">
            <label for="biaya_pendaftaran_dn" class="form-label">Biaya Pendaftaran DN</label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control currency-input" id="biaya_pendaftaran_dn" name="biaya_pendaftaran_dn" value="<?= esc((string) old('biaya_pendaftaran_dn')) ?>" placeholder="Contoh: 250.000">
            </div>
        </div>
        <div class="col-12 col-md-6">
            <label for="biaya_pendaftaran_ln" class="form-label">Biaya Pendaftaran LN</label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control currency-input" id="biaya_pendaftaran_ln" name="biaya_pendaftaran_ln" value="<?= esc((string) old('biaya_pendaftaran_ln')) ?>" placeholder="Contoh: 250.000">
            </div>
        </div>
        <div class="col-12 col-md-6">
            <label for="format_penilaian" class="form-label">Format Penilaian</label>
            <input type="text" class="form-control" id="format_penilaian" value="persilat.json" readonly>
            <input type="hidden" name="format_penilaian" value="persilat.json">
            <div class="form-text">Dikunci agar semua sub kategori seni memakai format penilaian PERSILAT.</div>
        </div>
        <div class="col-12 col-md-6">
            <label for="sistem_penampilan" class="form-label">Sistem Penampilan</label>
            <select class="form-select" id="sistem_penampilan" name="sistem_penampilan" required>
                <option value="pool" <?= old('sistem_penampilan', 'pool') === 'pool' ? 'selected' : '' ?>>Sekali Tampil / Pool</option>
                <option value="battle" <?= old('sistem_penampilan') === 'battle' ? 'selected' : '' ?>>Battle / Sistem Gugur</option>
            </select>
        </div>
        <div class="col-12">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan" rows="2"><?= esc((string) old('keterangan')) ?></textarea>
        </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill" <?= empty($kategoriLombaRows) ? 'disabled' : '' ?>>Simpan Sub Kategori Seni</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalUbahJumlahPesertaPerPool" tabindex="-1" aria-labelledby="modalUbahJumlahPesertaPerPoolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="<?= base_url('admin/super/sub-kategori-seni/update-max-peserta-per-pool') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header align-items-start">
                <div>
                    <h5 class="modal-title" id="modalUbahJumlahPesertaPerPoolLabel">Ubah Jumlah Peserta Per Pool</h5>
                    <p class="muted-copy mb-0 small">Mengubah jumlah peserta per pool akan merubah struktur bagan. Harap mengacak ulang bagan dan membuat jadwal baru jika diperlukan.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-semibold d-block">Pilih Kategori Lomba Seni</label>
                        <?php if (empty($kategoriLombaRows)) : ?>
                            <div class="alert alert-warning small mb-0">Belum ada Kategori Lomba Seni.</div>
                        <?php else : ?>
                            <div style="max-height: 250px; overflow-y: auto;">
                                <?php foreach (($kategoriLombaRows ?? []) as $kategoriLomba) : ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="id_kategori_lomba[]" value="<?= esc((string) $kategoriLomba->id_kategori_lomba) ?>" id="ubahPool_kl_<?= esc((string) $kategoriLomba->id_kategori_lomba) ?>">
                                        <label class="form-check-label" for="ubahPool_kl_<?= esc((string) $kategoriLomba->id_kategori_lomba) ?>">
                                            <?= esc($kategoriLomba->nama_kategori_usia ?? '-') ?>
                                            <span class="muted-copy small text-capitalize">/ <?= esc($kategoriLomba->jenis_kelamin ?? '-') ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text text-danger mt-1">Pilih minimal satu kategori.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="mb-3">
                            <label for="ubah_pool_max_peserta" class="form-label fw-semibold">Jumlah Peserta Per Pool</label>
                            <input type="number" min="1" class="form-control" id="ubah_pool_max_peserta" name="max_peserta" required>
                            <div class="form-text">Ganda dihitung 1, beregu dihitung 1.</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="otomatis_distribusi" id="ubah_pool_otomatis_distribusi" value="1">
                                <label class="form-check-label" for="ubah_pool_otomatis_distribusi">Otomatis distribusikan peserta dan acak bagan?</label>
                            </div>
                            <div class="form-text">Setelah mengubah kapasitas per pool, sebaran peserta dan bagan dapat diacak ulang dan disesuaikan.</div>
                        </div>
                        <div class="alert alert-warning small mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                            <strong>Perhatian!</strong> Mengubah jumlah peserta per pool akan merubah struktur bagan. Harap mengacak ulang bagan dan membuat jadwal baru.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill" <?= empty($kategoriLombaRows) ? 'disabled' : '' ?>>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Master Data',
    'title' => 'Daftar Kelas Tanding',
    'description' => 'Create mendukung beberapa kategori lomba tanding sekaligus dan otomatis membuat pool pertama seperti flow CI3.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/super/kategori-usia'), 'label' => 'Kategori Usia', 'class' => 'btn-outline-secondary'],
        ['tag' => 'a', 'href' => base_url('admin/super/kategori-lomba'), 'label' => 'Kategori Lomba', 'class' => 'btn-outline-secondary'],
        ['tag' => 'a', 'href' => base_url('admin/super/sub-kategori-seni'), 'label' => 'Sub Kategori Seni', 'class' => 'btn-outline-secondary'],
        ['tag' => 'button', 'label' => 'Tambah Single', 'class' => 'btn-danger', 'attrs' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#modalTambahKelasTanding']],
        ['tag' => 'button', 'label' => 'Generate Multiple', 'class' => 'btn-outline-danger', 'attrs' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#modalGenerateKelasTanding']],
        ['tag' => 'button', 'label' => 'Ubah Max Pool', 'class' => 'btn-outline-secondary', 'attrs' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#modalUbahMaxPool']],
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
                        <th>Rentang Berat Badan</th>
                        <th>Kelas</th>
                        <th>Jumlah Peserta</th>
                        <th>Jumlah Peserta Lunas</th>
                        <th>Max Peserta</th>
                        <th>Kuota Tersedia</th>
                        <th>Jumlah Pool</th>
                        <th>Jenis Perlombaan</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row) : ?>
                        <?php $kuotaTersedia = (int) ($row->max_peserta ?? 0) - (int) ($row->jumlah_peserta_tanding ?? 0); ?>
                        <tr>
                            <td><?= esc((string) ($row->id_kelas_tanding ?? '-')) ?></td>
                            <td class="text-capitalize"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_kelamin ?? '-') ?></td>
                            <td><?= esc(($row->berat_minimal ?? '-') . ' - ' . ($row->berat_maksimal ?? '-') . ' Kg') ?></td>
                            <td class="fw-semibold text-center"><?= esc($row->label ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($row->jumlah_peserta_tanding ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($row->jumlah_peserta_tanding_lunas ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($row->max_peserta ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) $kuotaTersedia) ?></td>
                            <td class="text-end"><?= esc((string) ($row->jumlah_pool ?? 0)) ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_perlombaan ?? '-') ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?= base_url('admin/super/kelas-tanding/' . $row->id_kelas_tanding) ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Detail</a>
                                    <a href="<?= base_url('admin/super/kelas-tanding/' . $row->id_kelas_tanding . '/edit') ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                                    <form action="<?= base_url('admin/super/kelas-tanding/' . $row->id_kelas_tanding . '/delete') ?>" method="post" onsubmit="return confirmAdminAction(this, 'Hapus kelas tanding?', 'Data pool, peserta, pertandingan, jadwal, dan pembayaran terkait mungkin membuat hapus gagal.', 'Hapus')">
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


<div class="modal fade" id="modalTambahKelasTanding" tabindex="-1" aria-labelledby="modalTambahKelasTandingLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form action="<?= base_url('admin/super/kelas-tanding') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header align-items-start">
                <div>
                    <h5 class="modal-title" id="modalTambahKelasTandingLabel">Tambah Kelas Tanding</h5>
                    <p class="muted-copy mb-0 small">Setiap kategori tanding yang dipilih akan dibuatkan kelas dan pool pertama.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <?= view('admin/super/kelas_tanding/partials/_kategori_lomba_checkboxes', ['kategoriLombaRows' => $kategoriLombaRows ?? []]) ?>
                    <div class="col-12 col-md-4"><label class="form-label" for="label">Label Kelas</label><input class="form-control" id="label" name="label" value="<?= esc((string) old('label')) ?>" required></div>
                    <div class="col-12 col-md-4"><label class="form-label" for="berat_minimal">Berat Minimal</label><input type="number" min="0" step="0.01" class="form-control" id="berat_minimal" name="berat_minimal" value="<?= esc((string) old('berat_minimal')) ?>" required></div>
                    <div class="col-12 col-md-4"><label class="form-label" for="berat_maksimal">Berat Maksimal</label><input type="number" min="0" step="0.01" class="form-control" id="berat_maksimal" name="berat_maksimal" value="<?= esc((string) old('berat_maksimal')) ?>" required></div>
                    <?= view('admin/super/kelas_tanding/partials/_form_fields', ['prefix' => 'single']) ?>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger rounded-pill" <?= empty($kategoriLombaRows) ? 'disabled' : '' ?>>Simpan Kelas Tanding</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalGenerateKelasTanding" tabindex="-1" aria-labelledby="modalGenerateKelasTandingLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form action="<?= base_url('admin/super/kelas-tanding/create-multiple') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header align-items-start">
                <div>
                    <h5 class="modal-title" id="modalGenerateKelasTandingLabel">Generate Multiple Kelas Tanding</h5>
                    <p class="muted-copy mb-0 small">Mengikuti flow CI3: label berurutan, rentang berat naik, opsional kelas Bebas dan Mini.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <?= view('admin/super/kelas_tanding/partials/_kategori_lomba_checkboxes', ['kategoriLombaRows' => $kategoriLombaRows ?? []]) ?>
                    <div class="col-12 col-md-3"><label class="form-label" for="label_awal">Label Awal</label><input maxlength="1" class="form-control text-uppercase" id="label_awal" name="label_awal" value="<?= esc((string) old('label_awal', 'A')) ?>" required></div>
                    <div class="col-12 col-md-3"><label class="form-label" for="berat_awal">Berat Awal</label><input type="number" min="0" step="0.01" class="form-control" id="berat_awal" name="berat_awal" value="<?= esc((string) old('berat_awal')) ?>" required></div>
                    <div class="col-12 col-md-3"><label class="form-label" for="selisih_berat">Selisih Berat</label><input type="number" min="0" step="0.01" class="form-control" id="selisih_berat" name="selisih_berat" value="<?= esc((string) old('selisih_berat')) ?>" required></div>
                    <div class="col-12 col-md-3"><label class="form-label" for="jumlah_kelas">Jumlah Kelas</label><input type="number" min="1" class="form-control" id="jumlah_kelas" name="jumlah_kelas" value="<?= esc((string) old('jumlah_kelas')) ?>" required></div>
                    <div class="col-12 col-md-6"><label class="form-check-label"><input type="checkbox" class="form-check-input me-1" name="kelas_bebas" value="1" <?= old('kelas_bebas') ? 'checked' : '' ?>> Tambahkan kelas Bebas</label></div>
                    <div class="col-12 col-md-6"><label class="form-check-label"><input type="checkbox" class="form-check-input me-1" name="kelas_mini" value="1" <?= old('kelas_mini') ? 'checked' : '' ?>> Tambahkan kelas Mini</label></div>
                    <?= view('admin/super/kelas_tanding/partials/_form_fields', ['prefix' => 'multiple']) ?>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger rounded-pill" <?= empty($kategoriLombaRows) ? 'disabled' : '' ?>>Generate Kelas</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalUbahMaxPool" tabindex="-1" aria-labelledby="modalUbahMaxPoolLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form action="<?= base_url('admin/super/kelas-tanding/update-jumlah-peserta-per-pool') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header align-items-start"><div><h5 class="modal-title" id="modalUbahMaxPoolLabel">Ubah Jumlah Peserta Tanding Per Pool</h5><p class="muted-copy mb-0 small">Mengikuti flow CI3 dari kategori lomba: update semua pool pada kategori tanding terpilih.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
            <div class="modal-body"><div class="row g-3"><?= view('admin/super/kelas_tanding/partials/_kategori_lomba_checkboxes', ['kategoriLombaRows' => $kategoriLombaRows ?? []]) ?><div class="col-12 col-md-6"><label class="form-label" for="max_peserta_mass">Max Peserta Per Pool</label><input type="number" min="1" class="form-control" id="max_peserta_mass" name="max_peserta" value="<?= esc((string) old('max_peserta', '16')) ?>" required></div><div class="col-12 col-md-6 d-flex align-items-end"><label class="form-check-label"><input type="checkbox" class="form-check-input me-1" name="otomatis_distribusi" value="1"> Otomatis distribusikan ulang peserta</label></div></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger rounded-pill" <?= empty($kategoriLombaRows) ? 'disabled' : '' ?>>Simpan Perubahan</button></div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

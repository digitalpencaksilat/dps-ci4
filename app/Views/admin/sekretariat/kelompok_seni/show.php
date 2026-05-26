<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div>
            <p class="eyebrow mb-1">Detail Kelompok Seni</p>
            <h3 class="section-title h4 mb-0"><?= esc($row->nama_kontingen) ?></h3>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#editKelompokSeniModal">Edit Kelompok</button>
            <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#pindahPoolSeniModal">Pindah Pool</button>
            <form method="post" action="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Hapus kelompok seni?', 'Semua anggota kelompok ini akan ikut keluar dari kategori seni.', 'Hapus')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger rounded-pill px-4">Hapus</button>
            </form>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6"><div class="placeholder-stat"><div class="small muted-copy">Kategori</div><div class="fw-semibold"><?= esc(trim(((string) ($row->jenis_seni ?? '')) . ' ' . ((string) ($row->nama_seni ?? '')))) ?></div></div></div>
        <div class="col-md-6"><div class="placeholder-stat"><div class="small muted-copy">Pool</div><div class="fw-semibold"><?= esc((string) ($row->nomor_pool ?? '-')) ?></div></div></div>
        <div class="col-12"><div class="placeholder-stat"><div class="small muted-copy">Anggota</div><div class="fw-semibold"><?= $row->anggota_kelompok_peserta_seni ?: '-' ?></div></div></div>
    </div>
</section>

<section class="admin-card mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div>
            <h4 class="section-title h5 mb-1">Anggota Kelompok</h4>
            <p class="muted-copy mb-0">Tambah atau keluarkan peserta dari kelompok seni.</p>
        </div>
        <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addMemberModal">Tambah Anggota</button>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead><tr><th>Nama</th><th>Jenis Kelamin</th><th>Sekolah</th><th class="text-end no-export">Aksi</th></tr></thead>
            <tbody>
                <?php foreach (($anggotaRows ?? []) as $anggota) : ?>
                    <tr>
                        <td><?= esc($anggota->nama_pendaftar) ?></td>
                        <td><?= esc($anggota->jenis_kelamin) ?></td>
                        <td><?= esc((string) ($anggota->nama_sekolah ?: '-')) ?></td>
                        <td class="text-end">
                            <form method="post" action="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni . '/anggota/' . $anggota->id_peserta_seni . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Hapus anggota?', 'Peserta akan keluar dari kelompok seni.', 'Hapus')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="modal fade" id="editKelompokSeniModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni . '/update') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Edit Kelompok Seni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?= view('admin/sekretariat/kelompok_seni/_form', ['mode' => 'edit', 'row' => $row, 'kompetisiOptions' => $kompetisiOptions ?? []]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="pindahPoolSeniModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni . '/update') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Pindah Pool Seni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="muted-copy small">Hanya menampilkan pool dari kategori <?= esc(trim(($row->jenis_seni ?? '') . ' ' . ($row->nama_seni ?? ''))) ?>.</p>
                <label class="form-label fw-semibold">Pool Tujuan</label>
                <select name="id_kompetisi_seni" class="form-select rounded-4" required>
                    <?php foreach (($poolOptions ?? []) as $item) : ?>
                        <?php $label = 'Pool ' . ($item->nomor_pool ?? '-') . ' - ' . ($item->jumlah_kelompok_peserta_seni ?? 0) . '/' . ($item->max_peserta ?? 0) . ' kelompok'; ?>
                        <option value="<?= esc((string) $item->id_kompetisi_seni) ?>" <?= (int) $row->id_kompetisi_seni === (int) $item->id_kompetisi_seni ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label fw-semibold mt-3">Nomor Undi</label>
                <input type="number" name="nomor_undi" class="form-control rounded-4" value="<?= esc((string) ($row->nomor_undi ?? 0)) ?>">
                <label class="form-label fw-semibold mt-3">Keterangan</label>
                <textarea name="keterangan" class="form-control rounded-4" rows="2"><?= esc($row->keterangan ?? '') ?></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Pindahkan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni . '/anggota') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">Peserta</label>
                <select name="id_pendaftar" class="form-select rounded-4" required>
                    <option value="">Pilih peserta</option>
                    <?php foreach (($pendaftarOptions ?? []) as $item) : ?>
                        <option value="<?= esc((string) $item->id_pendaftar) ?>"><?= esc($item->nama_pendaftar . ' (' . $item->jenis_kelamin . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Tambah</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

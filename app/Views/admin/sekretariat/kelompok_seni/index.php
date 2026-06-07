<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$paymentBadge = static function (?string $status): string {
    if ($status === 'lunas') {
        return '<span class="badge text-bg-success">Lunas</span>';
    }
    if ($status === 'menunggu') {
        return '<span class="badge text-bg-warning">Menunggu Konfirmasi</span>';
    }

    return '<span class="badge text-bg-danger">Belum Lunas</span>';
};
$formatGender = static fn (?string $gender): string => $gender !== null && $gender !== '' ? ucwords($gender) : '-';
?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Sekretariat</p>
            <h3 class="section-title h4 mb-0">Peserta Seni</h3>
            <p class="muted-copy mb-0 mt-2">Daftar kelompok seni lintas kontingen.</p>
        </div>
        <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createKelompokSeniModal">Tambah Kelompok</button>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
        <table class="table admin-table admin-datatable-export align-middle mb-0">
            <thead><tr><th>Nama</th><th>Kontingen</th><th>Sekolah</th><th>Kategori Usia</th><th>Jenis Kelamin</th><th>Jenis Seni</th><th>Jurus</th><th>Nomor Pool</th><th>Nomor Undi</th><th>Pembayaran</th><th class="text-end no-export">Aksi</th></tr></thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <tr>
                        <td><a href="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni) ?>" class="fw-semibold text-danger text-decoration-none text-capitalize"><?= $row->anggota_kelompok_peserta_seni ?: '-' ?></a></td>
                        <td class="text-uppercase"><?= esc($row->nama_kontingen) ?></td>
                        <td><?= $row->nama_sekolah ?: '-' ?></td>
                        <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                        <td><?= esc($formatGender($row->jenis_kelamin ?? null)) ?></td>
                        <td><?= esc((string) ($row->jenis_seni ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nama_seni ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                        <td class="text-end"><?= ($row->sistem_penampilan ?? '') === 'pool' ? esc((string) ($row->nomor_undi ?? '-')) : '<span class="muted-copy small">Tidak ada undian</span>' ?></td>
                        <td><?= $paymentBadge($row->status_pembayaran ?? null) ?></td>
                        <td class="text-end no-export">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                 <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni) ?>">
                                            <i class="fas fa-eye me-2"></i>Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/sekretariat/pool-seni/' . $row->id_kompetisi_seni) ?>">
                                            <i class="fas fa-sitemap me-2"></i>Lihat Bagan
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item js-edit-kelompok-seni" href="#" data-id="<?= $row->id_kelompok_peserta_seni ?>">
                                            <i class="fas fa-user-edit me-2"></i>Ganti Atlet
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item js-pindah-pool-seni" href="#" data-id="<?= $row->id_kelompok_peserta_seni ?>">
                                            <i class="fas fa-arrows-alt me-2"></i>Pindah Pool
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="post" action="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Undur diri kelompok seni?', 'Kelompok <?= esc($row->anggota_kelompok_peserta_seni, 'attr') ?> akan keluar dari kategori seni.', 'Undur Diri')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-user-minus me-2"></i>Undur Diri
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
    </div></div>
</section>

<!-- Modal Edit Kelompok Seni -->
<div class="modal fade" id="editKelompokIndexModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="post" action="" class="modal-content" id="formEditKelompokIndex">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Edit Kelompok Seni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4 js-modal-loading"><span class="spinner-border text-danger"></span></div>
                <div class="js-modal-content d-none">
                    <label class="form-label fw-semibold">Kategori Seni :</label>
                    <select name="id_kompetisi_seni" class="form-select rounded-4" required>
                        <option value="">Memuat...</option>
                    </select>
                    <label class="form-label fw-semibold mt-3">Keterangan</label>
                    <textarea name="keterangan" class="form-control rounded-4" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pindah Pool Seni -->
<div class="modal fade" id="pindahPoolSeniIndexModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="" class="modal-content" id="formPindahPoolSeniIndex">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Pindah Pool Seni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4 js-modal-loading"><span class="spinner-border text-danger"></span></div>
                <div class="js-modal-content d-none">
                    <p class="muted-copy small js-pool-info"></p>
                    <label class="form-label fw-semibold">Pool Tujuan</label>
                    <select name="id_kompetisi_seni" class="form-select rounded-4" required>
                        <option value="">Memuat...</option>
                    </select>
                    <label class="form-label fw-semibold mt-3">Nomor Undi</label>
                    <input type="number" name="nomor_undi" class="form-control rounded-4" value="0">
                    <label class="form-label fw-semibold mt-3">Keterangan</label>
                    <textarea name="keterangan" class="form-control rounded-4" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Pindahkan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="createKelompokSeniModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('admin/sekretariat/kelompok-seni') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kelompok Seni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?= view('admin/sekretariat/kelompok_seni/_form', [
                    'mode' => 'create',
                    'kontingenRows' => $kontingenRows ?? [],
                    'kompetisiOptions' => $kompetisiOptions ?? [],
                    'pendaftarOptions' => $pendaftarOptions ?? [],
                ]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Kelompok</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.querySelectorAll('.js-edit-kelompok-seni').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const modal = document.getElementById('editKelompokIndexModal');
            const form = document.getElementById('formEditKelompokIndex');
            const loading = modal.querySelector('.js-modal-loading');
            const content = modal.querySelector('.js-modal-content');

            form.action = `<?= base_url('admin/sekretariat/kelompok-seni') ?>/${id}/update`;
            loading.classList.remove('d-none');
            content.classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(modal).show();

            try {
                const res = await fetch(`<?= base_url('admin/sekretariat/kelompok-seni') ?>/${id}/ajax-edit-kelompok`, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                const select = content.querySelector('select[name="id_kompetisi_seni"]');
                select.innerHTML = '<option value="">Pilih kategori</option>';
                (data.kompetisiOptions || []).forEach(item => {
                    const label = `${item.nama_kategori_usia || ''} ${item.jenis_kelamin || ''} ${item.jenis_seni || ''} ${item.nama_seni || ''} Pool ${item.nomor_pool || '-'}`;
                    const opt = document.createElement('option');
                    opt.value = item.id_kompetisi_seni;
                    opt.textContent = label.trim();
                    if (String(item.id_kompetisi_seni) === String(data.id_kompetisi_seni)) opt.selected = true;
                    select.appendChild(opt);
                });
                content.querySelector('textarea[name="keterangan"]').value = data.keterangan || '';
            } catch (err) {
                console.error(err);
            }
            loading.classList.add('d-none');
            content.classList.remove('d-none');
        });
    });

    document.querySelectorAll('.js-pindah-pool-seni').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const modal = document.getElementById('pindahPoolSeniIndexModal');
            const form = document.getElementById('formPindahPoolSeniIndex');
            const loading = modal.querySelector('.js-modal-loading');
            const content = modal.querySelector('.js-modal-content');

            form.action = `<?= base_url('admin/sekretariat/kelompok-seni') ?>/${id}/update`;
            loading.classList.remove('d-none');
            content.classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(modal).show();

            try {
                const res = await fetch(`<?= base_url('admin/sekretariat/kelompok-seni') ?>/${id}/ajax-pindah-pool`, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                content.querySelector('.js-pool-info').textContent = `Pool dari kategori ${data.jenis_seni || ''} ${data.nama_seni || ''}.`;
                const select = content.querySelector('select[name="id_kompetisi_seni"]');
                select.innerHTML = '';
                (data.poolOptions || []).forEach(item => {
                    const label = `Pool ${item.nomor_pool || '-'} - ${item.jumlah_kelompok_peserta_seni || 0}/${item.max_peserta || 0} kelompok`;
                    const opt = document.createElement('option');
                    opt.value = item.id_kompetisi_seni;
                    opt.textContent = label;
                    if (String(item.id_kompetisi_seni) === String(data.id_kompetisi_seni)) opt.selected = true;
                    select.appendChild(opt);
                });
                content.querySelector('input[name="nomor_undi"]').value = data.nomor_undi || 0;
                content.querySelector('textarea[name="keterangan"]').value = data.keterangan || '';
            } catch (err) {
                console.error(err);
            }
            loading.classList.add('d-none');
            content.classList.remove('d-none');
        });
    });
</script>
<?= $this->endSection() ?>

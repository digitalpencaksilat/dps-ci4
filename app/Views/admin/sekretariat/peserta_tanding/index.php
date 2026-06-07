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
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$exportTitle = 'DAFTAR PESERTA TANDING';
$exportFilename = 'Daftar Peserta Tanding - ' . $eventName;
$brandName = (string) (get_setting('brand_name') ?? 'Digital Pencak Silat');
$brandAbbr = strtolower((string) (get_setting('brand_abbreviation') ?? 'dps'));
$brandLogoUrl = base_url('assets/images/brand/' . $brandAbbr . '/logo.png');
$printHeaderHtml = view('shared_components/print/medal_export_header', [
    'title' => $exportTitle,
    'subtitle' => $eventName,
]);
?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Sekretariat</p>
            <h3 class="section-title h4 mb-0">Peserta tanding</h3>
            <p class="muted-copy mb-0 mt-2">Daftar peserta tanding lintas kontingen.</p>
        </div>
        <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createPesertaTandingModal">Tambah Peserta Tanding</button>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
        <table class="table admin-table align-middle mb-0" id="tabelPesertaTanding">
            <thead><tr><th class="text-center">No</th><th>Nama</th><th>Kontingen</th><th>Sekolah</th><th>Berat Badan</th><th>Tinggi Badan</th><th>Umur</th><th>Kategori</th><th>Jenis Kelamin</th><th>Kelas</th><th>Nomor Pool</th><th>Rentang Berat Badan</th><th>Pembayaran</th><th>Keterangan</th><th>NIK</th><th>No KK</th><th class="text-end no-export">Aksi</th></tr></thead>
            <tbody>
                <?php foreach (($rows ?? []) as $index => $row) : ?>
                    <tr>
                        <td class="text-center fw-semibold"><?= esc((string) ($index + 1)) ?></td>
                        <td class="fw-semibold text-capitalize"><?= esc($row->nama_pendaftar) ?></td>
                        <td class="text-uppercase"><?= esc($row->nama_kontingen) ?></td>
                        <td><?= esc((string) ($row->nama_sekolah ?: '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->berat_badan ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->tinggi_badan ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->umur_pendaftar ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                        <td><?= esc($formatGender($row->jenis_kelamin ?? null)) ?></td>
                        <td><?= esc((string) ($row->label ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                        <td class="text-end"><?= esc(trim(((string) ($row->berat_minimal ?? '-')) . ' - ' . ((string) ($row->berat_maksimal ?? '-')))) ?></td>
                        <td><?= $paymentBadge($row->status_pembayaran ?? null) ?></td>
                        <td><?= esc((string) ($row->keterangan ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nomor_induk_kependudukan ?? '-')) ?></td>
                        <td><?= esc((string) ($row->nomor_kartu_keluarga ?? '-')) ?></td>
                        <td class="text-end no-export">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                 <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/sekretariat/peserta-tanding/' . $row->id_peserta_tanding) ?>">
                                            <i class="fas fa-eye me-2"></i>Detail
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('admin/sekretariat/pool-tanding/' . $row->id_kompetisi_tanding) ?>">
                                            <i class="fas fa-sitemap me-2"></i>Lihat Bagan
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item js-edit-kelas-tanding" href="#" data-id="<?= $row->id_peserta_tanding ?>">
                                            <i class="fas fa-exchange-alt me-2"></i>Ganti Kelas
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item js-pindah-pool-tanding" href="#" data-id="<?= $row->id_peserta_tanding ?>">
                                            <i class="fas fa-arrows-alt me-2"></i>Pindah Pool
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="post" action="<?= base_url('admin/sekretariat/peserta-tanding/' . $row->id_peserta_tanding . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Undur diri peserta?', 'Peserta <?= esc($row->nama_pendaftar, 'attr') ?> akan keluar dari kategori tanding.', 'Undur Diri')">
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

<!-- Modal Ganti Kelas Tanding -->
<div class="modal fade" id="editKelasIndexModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="post" action="" class="modal-content" id="formEditKelasIndex">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Ganti Kelas Tanding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4 js-modal-loading"><span class="spinner-border text-danger"></span></div>
                <div class="js-modal-content d-none">
                    <label class="form-label fw-semibold">Kategori Baru :</label>
                    <select name="id_kompetisi_tanding" class="form-select rounded-4" required>
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

<!-- Modal Pindah Pool Tanding -->
<div class="modal fade" id="pindahPoolIndexModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="" class="modal-content" id="formPindahPoolIndex">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Pindah Pool Tanding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4 js-modal-loading"><span class="spinner-border text-danger"></span></div>
                <div class="js-modal-content d-none">
                    <p class="muted-copy small js-pool-info"></p>
                    <label class="form-label fw-semibold">Pool Tujuan</label>
                    <select name="id_kompetisi_tanding" class="form-select rounded-4" required>
                        <option value="">Memuat...</option>
                    </select>
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

<div class="modal fade" id="createPesertaTandingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('admin/sekretariat/peserta-tanding') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Peserta Tanding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?= view('admin/sekretariat/peserta_tanding/_form', ['mode' => 'create', 'pendaftarOptions' => $pendaftarOptions ?? [], 'kompetisiOptions' => $kompetisiOptions ?? []]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Simpan</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        window.initAdminExportTable('#tabelPesertaTanding', {
            title: <?= json_encode($exportTitle) ?>,
            filename: <?= json_encode($exportFilename) ?>,
            orientation: 'landscape',
            preset: 'wide-report',
            themedExport: true,
            excelUppercase: false,
            printHeaderHtml: <?= json_encode($printHeaderHtml) ?>,
            watermark: {
                logo: <?= json_encode($brandLogoUrl) ?>,
                text: 'Powered by <strong>' + <?= json_encode($brandName) ?> + '</strong> &copy; ' + new Date().getFullYear()
            },
            excel: {
                columnWidths: { A: 6, B: 22, C: 22, D: 20, E: 12, F: 12, G: 10, H: 18, I: 14, J: 10, K: 10, L: 18, M: 18, N: 20, O: 18, P: 18 },
                numericTextColumns: [14, 15]
            },
            printCustomize: function(win) {
                window.dpsMedalTallyPrintCustomize(win, {
                    watermark: {
                        logo: <?= json_encode($brandLogoUrl) ?>,
                        text: 'Powered by <strong>' + <?= json_encode($brandName) ?> + '</strong> &copy; ' + new Date().getFullYear()
                    }
                });
                $(win.document.head).append('<style>table.medal-data-table tr td:nth-child(5), table.medal-data-table tr td:nth-child(6), table.medal-data-table tr td:nth-child(7), table.medal-data-table tr td:nth-child(11), table.medal-data-table tr td:nth-child(12){text-align:right!important;}</style>');
            },
            dataTable: {
                columnDefs: [
                    { targets: [4, 5, 6, 10, 11], className: 'text-end' },
                    { targets: -1, orderable: false, width: '10%' }
                ]
            }
        });
    });

    document.querySelectorAll('.js-edit-kelas-tanding').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const modal = document.getElementById('editKelasIndexModal');
            const form = document.getElementById('formEditKelasIndex');
            const loading = modal.querySelector('.js-modal-loading');
            const content = modal.querySelector('.js-modal-content');

            form.action = `<?= base_url('admin/sekretariat/peserta-tanding') ?>/${id}/update`;
            loading.classList.remove('d-none');
            content.classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(modal).show();

            try {
                const res = await fetch(`<?= base_url('admin/sekretariat/peserta-tanding') ?>/${id}/ajax-edit-kelas`, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                const select = content.querySelector('select[name="id_kompetisi_tanding"]');
                select.innerHTML = '<option value="">Pilih kategori</option>';
                (data.kompetisiOptions || []).forEach(item => {
                    const label = `${item.nama_kategori_usia || ''} ${item.jenis_kelamin || ''} kelas ${item.label || ''} (${item.berat_minimal || '-'} - ${item.berat_maksimal || '-'} kg)`;
                    const opt = document.createElement('option');
                    opt.value = item.id_kompetisi_tanding;
                    opt.textContent = label;
                    if (String(item.id_kompetisi_tanding) === String(data.id_kompetisi_tanding)) opt.selected = true;
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

    document.querySelectorAll('.js-pindah-pool-tanding').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const modal = document.getElementById('pindahPoolIndexModal');
            const form = document.getElementById('formPindahPoolIndex');
            const loading = modal.querySelector('.js-modal-loading');
            const content = modal.querySelector('.js-modal-content');

            form.action = `<?= base_url('admin/sekretariat/peserta-tanding') ?>/${id}/update`;
            loading.classList.remove('d-none');
            content.classList.add('d-none');
            bootstrap.Modal.getOrCreateInstance(modal).show();

            try {
                const res = await fetch(`<?= base_url('admin/sekretariat/peserta-tanding') ?>/${id}/ajax-pindah-pool`, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                content.querySelector('.js-pool-info').textContent = `Pool dari kelas ${data.label} kategori ${data.nama_kategori_usia}.`;
                const select = content.querySelector('select[name="id_kompetisi_tanding"]');
                select.innerHTML = '';
                (data.poolOptions || []).forEach(item => {
                    const label = `Pool ${item.nomor_pool || '-'} - ${item.jumlah_peserta_tanding || 0}/${item.max_peserta || 0} peserta`;
                    const opt = document.createElement('option');
                    opt.value = item.id_kompetisi_tanding;
                    opt.textContent = label;
                    if (String(item.id_kompetisi_tanding) === String(data.id_kompetisi_tanding)) opt.selected = true;
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
</script>
<?= $this->endSection() ?>

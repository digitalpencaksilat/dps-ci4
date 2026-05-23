<?= $this->extend('layouts/kontingen') ?>

<?= $this->section('content') ?>
<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <p class="eyebrow mb-1">Data Atlet</p>
            <h3 class="panel-title mb-0">Peserta Kontingen</h3>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small">Total: <?= count($peserta) ?></span>
            <?php if ($allowCreate) : ?>
                <button class="btn btn-danger rounded-pill px-4" type="button" data-bs-toggle="modal" data-bs-target="#pesertaModal" data-mode="create">
                    Tambah Peserta
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($peserta === []) : ?>
        <div class="empty-state-box">
            <div class="empty-state-icon"><i class="fas fa-users"></i></div>
            <h4>Belum Ada Peserta</h4>
            <p>Belum ada data atlet yang terdaftar untuk kontingen ini.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table align-middle peserta-table mb-0" id="tabelPesertaKontingen">
                <thead>
                    <tr>
                        <th>Nama Peserta</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Tempat Lahir</th>
                        <th>Sekolah</th>
                        <th>Tinggi Badan</th>
                        <th>Berat Badan</th>
                        <th>NIK</th>
                        <th>Nomor Kartu Keluarga</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peserta as $row) : ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($row->nama_pendaftar) ?></td>
                            <td><?= esc($row->jenis_kelamin) ?></td>
                            <td><?= esc(format_tanggal_indo($row->tanggal_lahir)) ?></td>
                            <td><?= esc($row->tempat_lahir ?: '-') ?></td>
                            <td><?= esc($row->nama_sekolah ?: '-') ?></td>
                            <td><?= esc((string) $row->tinggi_badan) ?></td>
                            <td><?= esc((string) $row->berat_badan) ?></td>
                            <td><?= esc($row->nomor_induk_kependudukan ?: '-') ?></td>
                            <td><?= esc($row->nomor_kartu_keluarga ?: '-') ?></td>
                            <td class="text-center">
                                <?php if ($allowEdit || $allowDelete) : ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-ghost" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-4">
                                            <?php if ($allowEdit) : ?>
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#pesertaModal"
                                                        data-mode="edit"
                                                        data-id="<?= $row->id_pendaftar ?>"
                                                        data-nama="<?= esc($row->nama_pendaftar, 'attr') ?>"
                                                        data-gender="<?= esc($row->jenis_kelamin, 'attr') ?>"
                                                        data-tanggal="<?= esc($row->tanggal_lahir, 'attr') ?>"
                                                        data-tinggi="<?= esc((string) $row->tinggi_badan, 'attr') ?>"
                                                        data-berat="<?= esc((string) $row->berat_badan, 'attr') ?>"
                                                        data-tempat="<?= esc($row->tempat_lahir, 'attr') ?>"
                                                        data-sekolah="<?= esc($row->nama_sekolah, 'attr') ?>"
                                                        data-nik="<?= esc($row->nomor_induk_kependudukan, 'attr') ?>"
                                                        data-kk="<?= esc($row->nomor_kartu_keluarga, 'attr') ?>"
                                                        data-alamat="<?= esc($row->alamat, 'attr') ?>"
                                                        data-arsip='<?= esc(json_encode(array_map(static fn($ars) => [
                                                            'jenis_arsip' => $ars->jenis_arsip,
                                                            'nama_arsip' => $ars->nama_arsip,
                                                        ], $arsipByPendaftar[$row->id_pendaftar] ?? [])), 'attr') ?>'
                                                    >Edit</button>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($allowDelete) : ?>
                                                <li>
                                                    <form method="post" action="<?= base_url('kontingen/peserta/' . $row->id_pendaftar . '/delete') ?>" onsubmit="return confirmDeleteAction(this, 'Peserta <?= esc($row->nama_pendaftar, 'js') ?> akan dihapus.');">
                                                        <?= csrf_field() ?>
                                                        <button class="dropdown-item text-danger" type="submit">Hapus</button>
                                                    </form>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<div class="modal fade" id="pesertaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="post" id="pesertaForm" class="modal-form-grid" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="eyebrow mb-1" id="pesertaModalEyebrow">Tambah</p>
                        <h3 class="panel-title mb-0" id="pesertaModalTitle">Tambah Peserta</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <ul class="nav nav-tabs modal-tabs mb-3" id="pesertaTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="data-peserta-tab" data-bs-toggle="tab" data-bs-target="#data-peserta-pane" type="button" role="tab">Data Peserta</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="arsip-peserta-tab" data-bs-toggle="tab" data-bs-target="#arsip-peserta-pane" type="button" role="tab">Arsip Peserta</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="data-peserta-pane" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nama Peserta</label>
                                    <input type="text" name="nama_pendaftar" class="form-control rounded-4" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="form-select rounded-4" required>
                                        <option value="putra">Putra</option>
                                        <option value="putri">Putri</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir" class="form-control rounded-4" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tinggi Badan</label>
                                    <input type="text" name="tinggi_badan" class="form-control rounded-4 input-numeric-only" inputmode="numeric" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Berat Badan</label>
                                    <input type="text" name="berat_badan" class="form-control rounded-4 input-numeric-only" inputmode="numeric" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" class="form-control rounded-4" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Sekolah</label>
                                    <input type="text" name="nama_sekolah" class="form-control rounded-4">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">NIK</label>
                                    <input type="text" name="nomor_induk_kependudukan" maxlength="16" minlength="16" class="form-control rounded-4 input-digits-16" inputmode="numeric">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nomor Kartu Keluarga</label>
                                    <input type="text" name="nomor_kartu_keluarga" maxlength="16" minlength="16" class="form-control rounded-4 input-digits-16" inputmode="numeric">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Alamat</label>
                                    <textarea name="alamat" rows="3" class="form-control rounded-4"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="arsip-peserta-pane" role="tabpanel">
                            <div class="row g-3">
                                <?php if ($arsipSlots === []) : ?>
                                    <div class="col-12">
                                        <div class="empty-state-box text-start">
                                            Belum ada slot arsip peserta aktif.
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <?php foreach ($arsipSlots as $slotName => $slot) : ?>
                                        <?php $fieldName = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($slot['nama_arsip'] ?? $slotName)) ?? 'arsip'); ?>
                                        <div class="col-md-6">
                                            <div class="arsip-slot-card">
                                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                                    <div>
                                                        <h4 class="h6 fw-bold mb-1"><?= esc($slot['nama_arsip'] ?? $slotName) ?></h4>
                                                        <div class="small text-muted">Tipe: JPG, JPEG, PNG | Max: <?= esc((string) ($slot['max_size'] ?? 0)) ?> KB</div>
                                                    </div>
                                                    <?php if (!empty($slot['required'])) : ?>
                                                        <span class="badge text-bg-danger rounded-pill">Wajib</span>
                                                    <?php else : ?>
                                                        <span class="badge text-bg-secondary rounded-pill">Opsional</span>
                                                    <?php endif; ?>
                                                </div>
                                                <input type="file" name="<?= esc($fieldName) ?>" class="form-control rounded-4" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-max-kb="<?= esc((string) ((int) ($slot['max_size'] ?? 0))) ?>">
                                                <div class="small text-muted mt-2">Gambar akan dioptimasi otomatis agar ukuran lebih ringan dengan kualitas tetap mudah dibaca.</div>
                                                <div class="arsip-preview small text-muted mt-2" data-slot-preview="<?= esc($fieldName) ?>"></div>
                                                <div class="arsip-existing small mt-2" data-slot-existing="<?= esc($fieldName) ?>"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-dark rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4" id="pesertaModalSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initKontingenDataTable('#tabelPesertaKontingen');

        const modalEl = document.getElementById('pesertaModal');
        const form = document.getElementById('pesertaForm');
        if (!modalEl || !form) return;

        const titleEl = document.getElementById('pesertaModalTitle');
        const eyebrowEl = document.getElementById('pesertaModalEyebrow');
        const submitEl = document.getElementById('pesertaModalSubmit');
        const baseAction = <?= json_encode(base_url('kontingen/peserta')) ?>;
        const allowedImageMimes = ['image/jpeg', 'image/png'];
        const allowedImageName = /\.(jpe?g|png)$/i;

        const notifyFileError = (message) => {
            if (window.toastr && typeof window.toastr.error === 'function') {
                window.toastr.error(message);
                return;
            }

            window.alert(message);
        };

        const slotPreview = (fieldName, text) => {
            const el = modalEl.querySelector(`[data-slot-preview="${fieldName}"]`);
            if (el) el.textContent = text || '';
        };

        const slotExisting = (fieldName, html) => {
            const el = modalEl.querySelector(`[data-slot-existing="${fieldName}"]`);
            if (el) el.innerHTML = html || '';
        };

        const setDigitsOnly = () => {
            form.querySelectorAll('.input-digits-16, .input-numeric-only').forEach((input) => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '');
                });
            });

            form.querySelectorAll('input[type="file"]').forEach((input) => {
                input.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file) {
                        slotPreview(input.name, '');
                        return;
                    }

                    const maxKb = Number(input.dataset.maxKb || 0);
                    const validType = allowedImageMimes.includes(String(file.type || '').toLowerCase()) || allowedImageName.test(file.name || '');
                    if (!validType) {
                        input.value = '';
                        slotPreview(input.name, '');
                        notifyFileError('File arsip hanya boleh berupa gambar JPG, JPEG, atau PNG.');
                        return;
                    }

                    if (maxKb > 0 && file.size > (maxKb * 1024)) {
                        input.value = '';
                        slotPreview(input.name, '');
                        notifyFileError(`Ukuran file ${file.name} melebihi batas ${maxKb} KB.`);
                        return;
                    }

                    const sizeKb = Math.max(1, Math.round(file.size / 1024));
                    slotPreview(input.name, `File dipilih: ${file.name} (${sizeKb} KB)`);
                });
            });
        };

        const fillForm = (data = {}) => {
            form.nama_pendaftar.value = data.nama || '';
            form.jenis_kelamin.value = data.gender || 'putra';
            form.tanggal_lahir.value = data.tanggal || '';
            form.tinggi_badan.value = data.tinggi || '';
            form.berat_badan.value = data.berat || '';
            form.tempat_lahir.value = data.tempat || '';
            form.nama_sekolah.value = data.sekolah || '';
            form.nomor_induk_kependudukan.value = data.nik || '';
            form.nomor_kartu_keluarga.value = data.kk || '';
            form.alamat.value = data.alamat || '';
        };

        modalEl.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const mode = trigger?.dataset.mode || 'create';

            if (mode === 'edit') {
                eyebrowEl.textContent = 'Edit';
                titleEl.textContent = 'Edit Peserta';
                submitEl.textContent = 'Simpan Perubahan';
                form.action = `${baseAction}/${trigger.dataset.id}/update`;
                fillForm(trigger.dataset);
                modalEl.querySelectorAll('[data-slot-preview]').forEach((el) => {
                    el.textContent = '';
                });
                modalEl.querySelectorAll('[data-slot-existing]').forEach((el) => {
                    el.innerHTML = '';
                });

                let arsip = [];
                try {
                    arsip = JSON.parse(trigger.dataset.arsip || '[]');
                } catch (e) {
                    arsip = [];
                }

                arsip.forEach((item) => {
                    const field = String(item.jenis_arsip || '').toLowerCase().replace(/[^a-z0-9]+/g, '_');
                    const url = <?= json_encode(base_url('uploads/peserta/arsip')) ?> + '/' + item.nama_arsip;
                    slotExisting(field, `<a href="${url}" target="_blank" class="text-decoration-none">File saat ini: ${item.nama_arsip}</a>`);
                });
            } else {
                eyebrowEl.textContent = 'Tambah';
                titleEl.textContent = 'Tambah Peserta';
                submitEl.textContent = 'Simpan';
                form.action = baseAction;
                form.reset();
                fillForm({});
                modalEl.querySelectorAll('[data-slot-preview]').forEach((el) => {
                    el.textContent = '';
                });
                modalEl.querySelectorAll('[data-slot-existing]').forEach((el) => {
                    el.innerHTML = '';
                });
            }
        });

        setDigitsOnly();
    });
</script>
<?= $this->endSection() ?>

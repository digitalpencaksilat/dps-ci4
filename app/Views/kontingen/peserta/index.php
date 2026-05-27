<?= $this->extend('layouts/kontingen') ?>

<?php
$validation = session()->getFlashdata('errors') ?? session('errors') ?? [];
if ($validation === [] && session()->has('validation')) {
    $validation = session('validation')->getErrors();
}

$fieldError = static fn(string $field): ?string => isset($validation[$field]) && $validation[$field] !== '' ? (string) $validation[$field] : null;
$fieldClass = static fn(string $field): string => $fieldError($field) !== null ? ' is-invalid' : '';
?>

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
            <p><?= $allowCreate ? 'Gunakan tombol Tambah Peserta di kanan atas untuk menambahkan atlet sebelum mendaftarkan kategori tanding atau seni.' : 'Belum ada data atlet yang terdaftar untuk kontingen ini.' ?></p>
            <?php if (! $allowCreate) : ?>
                <p class="small text-muted mb-0">Input atlet sedang ditutup.</p>
            <?php endif; ?>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
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
                                    <label for="peserta_nama_pendaftar" class="form-label fw-semibold">Nama Peserta</label>
                                    <input type="text" id="peserta_nama_pendaftar" name="nama_pendaftar" class="form-control rounded-4<?= $fieldClass('nama_pendaftar') ?>" required>
                                    <?php if ($fieldError('nama_pendaftar') !== null) : ?>
                                        <div class="invalid-feedback d-block"><?= esc($fieldError('nama_pendaftar')) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="peserta_jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" id="peserta_jenis_kelamin" class="form-select rounded-4<?= $fieldClass('jenis_kelamin') ?>" required>
                                        <option value="putra">Putra</option>
                                        <option value="putri">Putri</option>
                                    </select>
                                    <?php if ($fieldError('jenis_kelamin') !== null) : ?>
                                        <div class="invalid-feedback d-block"><?= esc($fieldError('jenis_kelamin')) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="peserta_tanggal_lahir" class="form-label fw-semibold">Tanggal Lahir</label>
                                    <input type="date" id="peserta_tanggal_lahir" name="tanggal_lahir" class="form-control rounded-4<?= $fieldClass('tanggal_lahir') ?>" required>
                                    <div class="invalid-feedback<?= $fieldError('tanggal_lahir') !== null ? ' d-block' : '' ?>" data-error-for="tanggal_lahir"><?= esc($fieldError('tanggal_lahir') ?? 'Tanggal lahir wajib diisi.') ?></div>
                                </div>
                                <div class="col-md-3">
                                    <label for="peserta_tinggi_badan" class="form-label fw-semibold">Tinggi Badan</label>
                                    <input type="text" id="peserta_tinggi_badan" name="tinggi_badan" class="form-control rounded-4 input-numeric-only<?= $fieldClass('tinggi_badan') ?>" inputmode="numeric" required>
                                    <div class="invalid-feedback<?= $fieldError('tinggi_badan') !== null ? ' d-block' : '' ?>" data-error-for="tinggi_badan"><?= esc($fieldError('tinggi_badan') ?? 'Tinggi badan wajib diisi dengan angka.') ?></div>
                                </div>
                                <div class="col-md-3">
                                    <label for="peserta_berat_badan" class="form-label fw-semibold">Berat Badan</label>
                                    <input type="text" id="peserta_berat_badan" name="berat_badan" class="form-control rounded-4 input-numeric-only<?= $fieldClass('berat_badan') ?>" inputmode="numeric" required>
                                    <div class="invalid-feedback<?= $fieldError('berat_badan') !== null ? ' d-block' : '' ?>" data-error-for="berat_badan"><?= esc($fieldError('berat_badan') ?? 'Berat badan wajib diisi dengan angka.') ?></div>
                                </div>
                                <div class="col-md-3">
                                    <label for="peserta_tempat_lahir" class="form-label fw-semibold">Tempat Lahir</label>
                                    <input type="text" id="peserta_tempat_lahir" name="tempat_lahir" class="form-control rounded-4<?= $fieldClass('tempat_lahir') ?>" required>
                                    <?php if ($fieldError('tempat_lahir') !== null) : ?>
                                        <div class="invalid-feedback d-block"><?= esc($fieldError('tempat_lahir')) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <label for="peserta_nama_sekolah" class="form-label fw-semibold">Sekolah</label>
                                    <input type="text" id="peserta_nama_sekolah" name="nama_sekolah" class="form-control rounded-4<?= $fieldClass('nama_sekolah') ?>">
                                    <?php if ($fieldError('nama_sekolah') !== null) : ?>
                                        <div class="invalid-feedback d-block"><?= esc($fieldError('nama_sekolah')) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label for="peserta_nomor_induk_kependudukan" class="form-label fw-semibold">NIK</label>
                                    <input type="text" id="peserta_nomor_induk_kependudukan" name="nomor_induk_kependudukan" maxlength="16" minlength="16" class="form-control rounded-4 input-digits-16<?= $fieldClass('nomor_induk_kependudukan') ?>" inputmode="numeric">
                                    <div class="invalid-feedback<?= $fieldError('nomor_induk_kependudukan') !== null ? ' d-block' : '' ?>" data-error-for="nomor_induk_kependudukan"><?= esc($fieldError('nomor_induk_kependudukan') ?? 'NIK harus terdiri dari 16 digit angka.') ?></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="peserta_nomor_kartu_keluarga" class="form-label fw-semibold">Nomor Kartu Keluarga</label>
                                    <input type="text" id="peserta_nomor_kartu_keluarga" name="nomor_kartu_keluarga" maxlength="16" minlength="16" class="form-control rounded-4 input-digits-16<?= $fieldClass('nomor_kartu_keluarga') ?>" inputmode="numeric">
                                    <div class="invalid-feedback<?= $fieldError('nomor_kartu_keluarga') !== null ? ' d-block' : '' ?>" data-error-for="nomor_kartu_keluarga"><?= esc($fieldError('nomor_kartu_keluarga') ?? 'Nomor kartu keluarga harus terdiri dari 16 digit angka.') ?></div>
                                </div>
                                <div class="col-12">
                                    <label for="peserta_alamat" class="form-label fw-semibold">Alamat</label>
                                    <textarea id="peserta_alamat" name="alamat" rows="3" class="form-control rounded-4<?= $fieldClass('alamat') ?>"></textarea>
                                    <?php if ($fieldError('alamat') !== null) : ?>
                                        <div class="invalid-feedback d-block"><?= esc($fieldError('alamat')) ?></div>
                                    <?php endif; ?>
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
                                                <input type="file" id="peserta_arsip_<?= esc($fieldName, 'attr') ?>" aria-label="Unggah <?= esc($slot['nama_arsip'] ?? $slotName, 'attr') ?>" name="<?= esc($fieldName) ?>" class="form-control rounded-4<?= $fieldClass($fieldName) ?>" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-max-kb="<?= esc((string) ((int) ($slot['max_size'] ?? 0))) ?>" data-required-on-create="<?= !empty($slot['required']) ? '1' : '0' ?>">
                                                <div class="small text-muted mt-2">Gambar akan dioptimasi otomatis agar ukuran lebih ringan dengan kualitas tetap mudah dibaca.</div>
                                                <div class="arsip-preview small text-muted mt-2" data-slot-preview="<?= esc($fieldName) ?>"></div>
                                                <div class="small mt-2" data-slot-requirement="<?= esc($fieldName) ?>"></div>
                                                <div class="invalid-feedback<?= $fieldError($fieldName) !== null ? ' d-block' : '' ?>" data-error-for="<?= esc($fieldName, 'attr') ?>"><?= esc($fieldError($fieldName) ?? 'Arsip ini wajib diunggah sesuai format yang ditentukan.') ?></div>
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
        const reopenMode = <?= json_encode(session()->getFlashdata('openPesertaModal')) ?>;
        const reopenId = <?= json_encode(session()->getFlashdata('openPesertaId')) ?>;
        const oldInput = <?= json_encode([
            'nama' => old('nama_pendaftar'),
            'gender' => old('jenis_kelamin'),
            'tanggal' => old('tanggal_lahir'),
            'tinggi' => old('tinggi_badan'),
            'berat' => old('berat_badan'),
            'tempat' => old('tempat_lahir'),
            'sekolah' => old('nama_sekolah'),
            'nik' => old('nomor_induk_kependudukan'),
            'kk' => old('nomor_kartu_keluarga'),
            'alamat' => old('alamat'),
        ]) ?>;
        const firstInvalidField = form.querySelector('.is-invalid');
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

        const setInlineError = (fieldName, message = '', visible = false) => {
            const el = modalEl.querySelector(`[data-error-for="${fieldName}"]`);
            if (!el) return;

            if (message !== '') {
                el.textContent = message;
            }

            el.classList.toggle('d-block', visible);
        };

        const setFieldInvalid = (fieldName, invalid, message = '') => {
            const input = form.querySelector(`[name="${fieldName}"]`);
            if (!input) return;

            input.classList.toggle('is-invalid', invalid);
            if (!invalid) {
                setInlineError(fieldName, '', false);
                return;
            }

            setInlineError(fieldName, message, true);
        };

        const setRequirementHint = (fieldName, text = '', tone = 'muted') => {
            const el = modalEl.querySelector(`[data-slot-requirement="${fieldName}"]`);
            if (!el) return;

            el.className = `small mt-2 text-${tone}`;
            el.textContent = text;
        };

        const slotExisting = (fieldName, node) => {
            const el = modalEl.querySelector(`[data-slot-existing="${fieldName}"]`);
            if (!el) return;

            el.replaceChildren();
            if (node instanceof Node) {
                el.appendChild(node);
            }
        };

        const setDigitsOnly = () => {
            form.querySelectorAll('.input-digits-16, .input-numeric-only').forEach((input) => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '');

                    if (input.name === 'tinggi_badan' || input.name === 'berat_badan') {
                        setFieldInvalid(input.name, input.value === '', `${input.labels?.[0]?.textContent || 'Field ini'} wajib diisi dengan angka.`);
                    }

                    if (input.name === 'nomor_induk_kependudukan' || input.name === 'nomor_kartu_keluarga') {
                        const hasValue = input.value !== '';
                        const validLength = input.value.length === 16;
                        setFieldInvalid(input.name, hasValue && !validLength, `${input.labels?.[0]?.textContent || 'Field ini'} harus terdiri dari 16 digit angka.`);
                    }
                });
            });

            form.querySelectorAll('input[type="file"]').forEach((input) => {
                input.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file) {
                        slotPreview(input.name, '');
                        if (!input.required) {
                            setFieldInvalid(input.name, false);
                        }
                        return;
                    }

                    const maxKb = Number(input.dataset.maxKb || 0);
                    const validType = allowedImageMimes.includes(String(file.type || '').toLowerCase()) || allowedImageName.test(file.name || '');
                    if (!validType) {
                        input.value = '';
                        slotPreview(input.name, '');
                        setFieldInvalid(input.name, true, 'File arsip hanya boleh berupa gambar JPG, JPEG, atau PNG.');
                        notifyFileError('File arsip hanya boleh berupa gambar JPG, JPEG, atau PNG.');
                        return;
                    }

                    if (maxKb > 0 && file.size > (maxKb * 1024)) {
                        input.value = '';
                        slotPreview(input.name, '');
                        setFieldInvalid(input.name, true, `Ukuran file ${file.name} melebihi batas ${maxKb} KB.`);
                        notifyFileError(`Ukuran file ${file.name} melebihi batas ${maxKb} KB.`);
                        return;
                    }

                    const sizeKb = Math.max(1, Math.round(file.size / 1024));
                    slotPreview(input.name, `File dipilih: ${file.name} (${sizeKb} KB)`);
                    setFieldInvalid(input.name, false);
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

        const clearArsipState = () => {
            modalEl.querySelectorAll('[data-slot-preview]').forEach((el) => {
                el.textContent = '';
            });
            modalEl.querySelectorAll('[data-slot-requirement]').forEach((el) => {
                el.className = 'small mt-2';
                el.textContent = '';
            });
            modalEl.querySelectorAll('[data-slot-existing]').forEach((el) => {
                el.replaceChildren();
            });
        };

        const updateArsipRequirements = (mode, existingFiles = new Set()) => {
            form.querySelectorAll('input[type="file"]').forEach((input) => {
                const requiredOnCreate = input.dataset.requiredOnCreate === '1';
                const hasExistingFile = existingFiles.has(input.name);
                const shouldRequire = requiredOnCreate && (mode === 'create' || !hasExistingFile);

                input.required = shouldRequire;
                if (!requiredOnCreate) {
                    setRequirementHint(input.name, '', 'muted');
                    return;
                }

                if (mode === 'create') {
                    setRequirementHint(input.name, 'Wajib diunggah saat menambah peserta.', 'danger');
                    return;
                }

                setRequirementHint(
                    input.name,
                    hasExistingFile ? 'Arsip sudah tersedia. Unggah ulang jika ingin mengganti file.' : 'Wajib diunggah karena arsip ini belum tersedia.',
                    hasExistingFile ? 'muted' : 'danger'
                );
            });
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
                clearArsipState();

                let arsip = [];
                const existingFiles = new Set();
                try {
                    arsip = JSON.parse(trigger.dataset.arsip || '[]');
                } catch (e) {
                    arsip = [];
                }

                arsip.forEach((item) => {
                    const field = String(item.jenis_arsip || '').toLowerCase().replace(/[^a-z0-9]+/g, '_');
                    const fileName = String(item.nama_arsip || '').trim();
                    if (fileName === '') {
                        return;
                    }

                    const link = document.createElement('a');
                    link.href = <?= json_encode(rtrim(base_url('uploads/peserta/arsip'), '/')) ?> + '/' + encodeURIComponent(fileName);
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.className = 'text-decoration-none';
                    link.textContent = `File saat ini: ${fileName}`;
                    slotExisting(field, link);
                    existingFiles.add(field);
                });

                updateArsipRequirements('edit', existingFiles);
            } else {
                eyebrowEl.textContent = 'Tambah';
                titleEl.textContent = 'Tambah Peserta';
                submitEl.textContent = 'Simpan';
                form.action = baseAction;
                form.reset();
                fillForm({});
                clearArsipState();
                updateArsipRequirements('create');
            }
        });

        if (reopenMode === 'create' || reopenMode === 'edit') {
            eyebrowEl.textContent = reopenMode === 'edit' ? 'Edit' : 'Tambah';
            titleEl.textContent = reopenMode === 'edit' ? 'Edit Peserta' : 'Tambah Peserta';
            submitEl.textContent = reopenMode === 'edit' ? 'Simpan Perubahan' : 'Simpan';
            form.action = reopenMode === 'edit' && reopenId ? `${baseAction}/${reopenId}/update` : baseAction;
            fillForm(oldInput);
            clearArsipState();
            updateArsipRequirements(reopenMode);
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        if (firstInvalidField) {
            firstInvalidField.focus();
        }

        setDigitsOnly();
    });
</script>
<?= $this->endSection() ?>

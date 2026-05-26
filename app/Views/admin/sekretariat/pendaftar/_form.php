<?php $isEdit = ($mode ?? 'create') === 'edit'; ?>
<ul class="nav nav-tabs modal-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#<?= esc($formId ?? 'pendaftar') ?>-data-pane" type="button" role="tab">Data Peserta</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#<?= esc($formId ?? 'pendaftar') ?>-arsip-pane" type="button" role="tab">Arsip Peserta</button>
    </li>
</ul>

<div class="tab-content">
<div class="tab-pane fade show active" id="<?= esc($formId ?? 'pendaftar') ?>-data-pane" role="tabpanel">
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Peserta</label>
        <input type="text" name="nama_pendaftar" class="form-control rounded-4" value="<?= old('nama_pendaftar', $pendaftar->nama_pendaftar ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Jenis Kelamin</label>
        <?php $jk = old('jenis_kelamin', $pendaftar->jenis_kelamin ?? 'putra'); ?>
        <select name="jenis_kelamin" class="form-select rounded-4" required>
            <option value="putra" <?= $jk === 'putra' ? 'selected' : '' ?>>Putra</option>
            <option value="putri" <?= $jk === 'putri' ? 'selected' : '' ?>>Putri</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" class="form-control rounded-4" value="<?= old('tanggal_lahir', $pendaftar->tanggal_lahir ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Tinggi Badan</label>
        <input type="text" name="tinggi_badan" class="form-control rounded-4 input-numeric-only" inputmode="numeric" value="<?= old('tinggi_badan', $pendaftar->tinggi_badan ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Berat Badan</label>
        <input type="text" name="berat_badan" class="form-control rounded-4 input-numeric-only" inputmode="numeric" value="<?= old('berat_badan', $pendaftar->berat_badan ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Tempat Lahir</label>
        <input type="text" name="tempat_lahir" class="form-control rounded-4" value="<?= old('tempat_lahir', $pendaftar->tempat_lahir ?? '') ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-semibold">Sekolah</label>
        <input type="text" name="nama_sekolah" class="form-control rounded-4" value="<?= old('nama_sekolah', $pendaftar->nama_sekolah ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">NIK</label>
        <input type="text" name="nomor_induk_kependudukan" maxlength="16" minlength="16" class="form-control rounded-4 input-digits-16" inputmode="numeric" value="<?= old('nomor_induk_kependudukan', $pendaftar->nomor_induk_kependudukan ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Kartu Keluarga</label>
        <input type="text" name="nomor_kartu_keluarga" maxlength="16" minlength="16" class="form-control rounded-4 input-digits-16" inputmode="numeric" value="<?= old('nomor_kartu_keluarga', $pendaftar->nomor_kartu_keluarga ?? '') ?>">
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Alamat</label>
        <textarea name="alamat" class="form-control rounded-4" rows="3"><?= old('alamat', $pendaftar->alamat ?? '') ?></textarea>
    </div>
</div>
</div>

<div class="tab-pane fade" id="<?= esc($formId ?? 'pendaftar') ?>-arsip-pane" role="tabpanel">
    <div class="row g-3">
        <?php if (($arsipSlots ?? []) === []) : ?>
            <div class="col-12">
                <div class="empty-state-box text-start">Belum ada slot arsip peserta aktif.</div>
            </div>
        <?php else : ?>
            <?php foreach (($arsipSlots ?? []) as $slotName => $slot) : ?>
                <?php $fieldName = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($slot['nama_arsip'] ?? $slotName)) ?? 'arsip'); ?>
                <?php $existingFile = null; ?>
                <?php foreach (($arsipExisting ?? []) as $arsip) : ?>
                    <?php if (($arsip->jenis_arsip ?? '') === ($slot['nama_arsip'] ?? $slotName)) { $existingFile = $arsip; break; } ?>
                <?php endforeach; ?>
                <div class="col-md-6">
                    <div class="arsip-slot-card">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div>
                                <h4 class="h6 fw-bold mb-1"><?= esc($slot['nama_arsip'] ?? $slotName) ?></h4>
                                <div class="small text-muted">Tipe: JPG, JPEG, PNG | Max: <?= esc((string) ($slot['max_size'] ?? 0)) ?> KB</div>
                            </div>
                            <?php if (! empty($slot['required'])) : ?>
                                <span class="badge text-bg-danger rounded-pill">Wajib</span>
                            <?php else : ?>
                                <span class="badge text-bg-secondary rounded-pill">Opsional</span>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="<?= esc($fieldName) ?>" class="form-control rounded-4 js-arsip-file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-slot-name="<?= esc($slot['nama_arsip'] ?? $slotName, 'attr') ?>" data-max-kb="<?= esc((string) ((int) ($slot['max_size'] ?? 0))) ?>">
                        <div class="small text-muted mt-2">Gambar akan dioptimasi otomatis agar ukuran lebih ringan dengan kualitas tetap mudah dibaca.</div>
                        <div class="arsip-preview small text-muted mt-2"></div>
                        <div class="arsip-existing small mt-2" data-arsip-existing="<?= esc($slot['nama_arsip'] ?? $slotName, 'attr') ?>"></div>
                        <?php if ($existingFile !== null) : ?>
                            <div class="small mt-2"><a href="<?= esc(url_arsip_pendaftar_ci4($existingFile->nama_arsip)) ?>" target="_blank" class="text-decoration-none">File saat ini: <?= esc($existingFile->nama_arsip) ?></a></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</div>

<script>
    (() => {
        const form = document.currentScript.closest('form');
        form?.querySelectorAll('.input-digits-16, .input-numeric-only').forEach((input) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '');
            });
        });
        form?.querySelectorAll('.js-arsip-file').forEach((input) => {
            input.addEventListener('change', () => {
                const preview = input.closest('.arsip-slot-card')?.querySelector('.arsip-preview');
                const file = input.files?.[0];
                if (!preview) return;
                preview.textContent = '';
                preview.classList.remove('text-danger', 'text-success');
                if (!file) return;
                const slotName = input.dataset.slotName || 'arsip ini';
                if (!/\.(jpe?g|png)$/i.test(file.name || '') && !['image/jpeg', 'image/png'].includes(String(file.type || '').toLowerCase())) {
                    input.value = '';
                    preview.classList.add('text-danger');
                    preview.textContent = `${slotName}: file hanya boleh JPG, JPEG, atau PNG.`;
                    return;
                }
                const maxKb = Number(input.dataset.maxKb || 0);
                if (maxKb > 0 && file.size > maxKb * 1024) {
                    input.value = '';
                    preview.classList.add('text-danger');
                    preview.textContent = `${slotName}: ukuran file melebihi batas ${maxKb} KB.`;
                    return;
                }
                preview.classList.add('text-success');
                preview.textContent = `${slotName}: file dipilih ${file.name} (${Math.max(1, Math.round(file.size / 1024))} KB)`;
            });
        });
    })();
</script>

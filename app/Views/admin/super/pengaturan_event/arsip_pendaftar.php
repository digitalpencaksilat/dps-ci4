<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Event</p>
            <h2 class="section-title h4 mb-2">Pengaturan Arsip Pendaftar</h2>
            <p class="muted-copy mb-0">Kelola slot arsip yang dibutuhkan untuk pendaftaran peserta. Data disimpan ke <code>site_builder_settings</code> dengan key <code>arsip_pendaftar_slots</code>.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-light rounded-pill">Kembali</a>
            <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahSlot">Tambah Slot</button>
        </div>
    </div>
</section>

<section class="admin-card">
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table align-middle mb-0">
                <thead>
                <tr>
                    <th class="text-center" style="width:72px">No</th>
                    <th>Nama Arsip</th>
                    <th class="text-center">Tipe File</th>
                    <th class="text-center">Max Size (KB)</th>
                    <th class="text-center">Required</th>
                    <th class="text-center">Active</th>
                    <th class="text-center" style="width:120px">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php if (($arsipSlots ?? []) === []) : ?>
                    <tr>
                        <td colspan="7" class="text-center muted-copy py-4">Belum ada slot arsip yang dikonfigurasi.</td>
                    </tr>
                <?php else : ?>
                    <?php $no = 1; foreach (($arsipSlots ?? []) as $slotName => $slot) : ?>
                        <tr>
                            <td class="text-center"><?= esc((string) $no++) ?></td>
                            <td>
                                <div class="fw-semibold"><?= esc((string) ($slot['nama_arsip'] ?? $slotName)) ?></div>
                                <div class="small text-muted"><?= esc((string) $slotName) ?></div>
                            </td>
                            <td class="text-center"><span class="status-badge neutral"><?= esc((string) ($slot['allowed_types'] ?? '')) ?></span></td>
                            <td class="text-center"><?= esc((string) ((int) ($slot['max_size'] ?? 0))) ?></td>
                            <td class="text-center"><span class="status-badge <?= !empty($slot['required']) ? 'danger' : 'neutral' ?>"><?= !empty($slot['required']) ? 'Ya' : 'Tidak' ?></span></td>
                            <td class="text-center"><span class="status-badge <?= !empty($slot['active']) ? 'success' : 'warning' ?>"><?= !empty($slot['active']) ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger rounded-pill"
                                        data-action="edit"
                                        data-slot-name="<?= esc((string) $slotName, 'attr') ?>"
                                        data-nama="<?= esc((string) ($slot['nama_arsip'] ?? ''), 'attr') ?>"
                                        data-types="<?= esc((string) ($slot['allowed_types'] ?? ''), 'attr') ?>"
                                        data-max="<?= esc((string) ((int) ($slot['max_size'] ?? 0)), 'attr') ?>"
                                        data-required="<?= !empty($slot['required']) ? '1' : '0' ?>"
                                        data-active="<?= !empty($slot['active']) ? '1' : '0' ?>"
                                >Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-action="delete" data-slot-name="<?= esc((string) $slotName, 'attr') ?>" data-nama="<?= esc((string) ($slot['nama_arsip'] ?? ''), 'attr') ?>">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modalTambahSlot" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Slot Arsip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahSlot">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Arsip</label>
                        <input type="text" class="form-control rounded-4" name="nama_arsip" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Allowed Types (ext)</label>
                        <input type="text" class="form-control rounded-4" name="allowed_types" value="png|jpg|jpeg" placeholder="png|jpg|jpeg">
                        <div class="form-text">Pisahkan dengan tanda pipe (|)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Size (KB)</label>
                        <input type="number" class="form-control rounded-4" name="max_size" value="5000" min="1">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="required" value="1" id="addRequired">
                        <label class="form-check-label" for="addRequired">Required</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="addActive" checked>
                        <label class="form-check-label" for="addActive">Active</label>
                    </div>
                </form>
                <div class="small muted-copy mt-3">Catatan: perubahan slot mempengaruhi validasi upload arsip pada form peserta.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill" id="btnTambahSlot">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditSlot" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Slot Arsip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="formEditSlot">
                    <?= csrf_field() ?>
                    <input type="hidden" name="slot_name">
                    <div class="mb-3">
                        <label class="form-label">Nama Arsip</label>
                        <input type="text" class="form-control rounded-4" name="nama_arsip" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Allowed Types (ext)</label>
                        <input type="text" class="form-control rounded-4" name="allowed_types">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Size (KB)</label>
                        <input type="number" class="form-control rounded-4" name="max_size" min="1">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="required" value="1" id="editRequired">
                        <label class="form-check-label" for="editRequired">Required</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="editActive">
                        <label class="form-check-label" for="editActive">Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill" id="btnUpdateSlot">Update</button>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const postJson = async (url, form) => {
            const body = new FormData(form);
            const response = await fetch(url, {method: 'POST', body, headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) return {status: false, message: 'Request gagal.'};
            try {
                return await response.json();
            } catch (e) {
                // Common case: redirected to HTML error/login page.
                const text = await response.text();
                return {status: false, message: 'Response bukan JSON. ' + String(text).slice(0, 200)};
            }
        };

        const modalEditEl = document.getElementById('modalEditSlot');
        const canUseBootstrap = typeof window.bootstrap !== 'undefined' && typeof window.bootstrap.Modal === 'function';
        const modalEdit = canUseBootstrap ? new window.bootstrap.Modal(modalEditEl) : null;

        document.getElementById('btnTambahSlot')?.addEventListener('click', async () => {
            const form = document.getElementById('formTambahSlot');
            const res = await postJson(<?= json_encode(base_url('admin/super/pengaturan-event/arsip-pendaftar')) ?>, form);
            if (!res.status) return alert(res.message || 'Gagal menyimpan.');
            location.reload();
        });

        document.getElementById('btnUpdateSlot')?.addEventListener('click', async () => {
            const form = document.getElementById('formEditSlot');
            const res = await postJson(<?= json_encode(base_url('admin/super/pengaturan-event/arsip-pendaftar/update')) ?>, form);
            if (!res.status) return alert(res.message || 'Gagal update.');
            location.reload();
        });

        document.querySelectorAll('[data-action="edit"]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const form = document.getElementById('formEditSlot');
                form.querySelector('[name="slot_name"]').value = btn.dataset.slotName || '';
                form.querySelector('[name="nama_arsip"]').value = btn.dataset.nama || '';
                form.querySelector('[name="allowed_types"]').value = btn.dataset.types || '';
                form.querySelector('[name="max_size"]').value = btn.dataset.max || '';
                form.querySelector('[name="required"]').checked = (btn.dataset.required || '0') === '1';
                form.querySelector('[name="active"]').checked = (btn.dataset.active || '0') === '1';
                if (modalEdit) {
                    modalEdit.show();
                    return;
                }

                // Fallback: Bootstrap JS not loaded. Try opening modal via basic class toggles.
                if (modalEditEl) {
                    modalEditEl.classList.add('show');
                    modalEditEl.style.display = 'block';
                    modalEditEl.removeAttribute('aria-hidden');
                    modalEditEl.setAttribute('aria-modal', 'true');
                    document.body.classList.add('modal-open');
                }
            });
        });

        document.querySelectorAll('[data-action="delete"]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const slot = btn.dataset.slotName || '';
                const nama = btn.dataset.nama || slot;
                if (!confirm(`Hapus slot arsip "${nama}"?`)) return;
                const form = document.createElement('form');
                form.innerHTML = <?= json_encode(csrf_field()) ?>;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'slot_name';
                input.value = slot;
                form.appendChild(input);
                const res = await postJson(<?= json_encode(base_url('admin/super/pengaturan-event/arsip-pendaftar/delete')) ?>, form);
                if (!res.status) return alert(res.message || 'Gagal hapus.');
                location.reload();
            });
        });

        // Fallback close handler when Bootstrap JS is missing.
        document.querySelectorAll('[data-bs-dismiss="modal"]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal');
                if (!modal) return;
                modal.classList.remove('show');
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            });
        });
    })();
</script>

<?= $this->endSection() ?>

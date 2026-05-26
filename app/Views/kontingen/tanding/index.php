<?= $this->extend('layouts/kontingen') ?>

<?= $this->section('content') ?>
<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <p class="eyebrow mb-1">Kategori Tanding</p>
            <h3 class="panel-title mb-0">Manajemen Kategori Tanding</h3>
        </div>
        <?php if ($allowCreate) : ?>
            <button class="btn btn-danger rounded-pill px-4" type="button" data-bs-toggle="modal" data-bs-target="#tandingModal" data-mode="create">
                Tambah Kategori Tanding
            </button>
        <?php endif; ?>
    </div>

    <?php if ($pesertaTanding === []) : ?>
        <div class="empty-state-box">
            <div class="empty-state-icon"><i class="fas fa-fist-raised"></i></div>
            <h4>Belum Ada Kategori Tanding</h4>
            <p>Belum ada atlet yang didaftarkan ke kategori tanding.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table align-middle peserta-table mb-0" id="tabelTandingKontingen">
                <thead>
                    <tr>
                        <th>Nama Atlet</th>
                        <th>Berat</th>
                        <th>Tinggi</th>
                        <th>Kategori Usia</th>
                        <th>Jenis Kelamin</th>
                        <th>Kelas Tanding</th>
                        <th>Pembayaran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesertaTanding as $row) : ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($row->nama_pendaftar) ?></td>
                            <td><?= esc((string) $row->berat_badan) ?> Kg</td>
                            <td><?= esc((string) $row->tinggi_badan) ?> Cm</td>
                            <td><?= esc($row->nama_kategori_usia) ?></td>
                            <td><?= esc($row->jenis_kelamin) ?></td>
                            <td><?= esc($row->label) ?> (<?= esc((string) $row->berat_minimal) ?> - <?= esc((string) $row->berat_maksimal) ?> Kg)</td>
                            <td>
                                <?php if ($row->status_pembayaran === null) : ?>
                                    <span class="badge rounded-pill text-bg-secondary">Belum Masuk Pembayaran</span>
                                <?php elseif ($row->status_pembayaran === 'menunggu') : ?>
                                    <span class="badge rounded-pill text-bg-warning">Menunggu</span>
                                <?php else : ?>
                                    <span class="badge rounded-pill text-bg-success">Lunas</span>
                                <?php endif; ?>
                            </td>
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
                                                        class="dropdown-item btn-edit-tanding"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#tandingModal"
                                                        data-mode="edit"
                                                        data-id="<?= $row->id_peserta_tanding ?>"
                                                        data-pendaftar="<?= $row->id_pendaftar ?>"
                                                        data-selected="<?= $row->id_kompetisi_tanding ?>"
                                                    >Edit</button>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($allowDelete) : ?>
                                                <li>
                                                    <form method="post" action="<?= base_url('kontingen/tanding/' . $row->id_peserta_tanding . '/delete') ?>" onsubmit="return confirmDeleteAction(this, 'Kategori tanding untuk <?= esc($row->nama_pendaftar, 'js') ?> akan dihapus.');">
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

<div class="modal fade" id="tandingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="post" id="tandingForm">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="eyebrow mb-1" id="tandingModalEyebrow">Tambah</p>
                        <h3 class="panel-title mb-0" id="tandingModalTitle">Tambah Kategori Tanding</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilih Atlet</label>
                            <select name="id_pendaftar" id="id_pendaftar_tanding_modal" class="form-select rounded-4" required>
                                <option value="">Pilih atlet</option>
                                <?php foreach ($pendaftarTersedia as $row) : ?>
                                    <option value="<?= $row->id_pendaftar ?>"><?= esc($row->nama_pendaftar) ?> (<?= esc((string) $row->berat_badan) ?> Kg)</option>
                                <?php endforeach; ?>
                                <?php foreach ($pesertaTanding as $row) : ?>
                                    <option value="<?= $row->id_pendaftar ?>"><?= esc($row->nama_pendaftar) ?> (<?= esc((string) $row->berat_badan) ?> Kg)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilih Kategori Tanding</label>
                            <select name="id_kompetisi_tanding" id="id_kompetisi_tanding_modal" class="form-select rounded-4" required>
                                <option value="">Pilih atlet terlebih dahulu</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-dark rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4" id="tandingModalSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initKontingenDataTable('#tabelTandingKontingen');

        const modalEl = document.getElementById('tandingModal');
        const form = document.getElementById('tandingForm');
        const pendaftarSelect = document.getElementById('id_pendaftar_tanding_modal');
        const kompetisiSelect = document.getElementById('id_kompetisi_tanding_modal');
        const titleEl = document.getElementById('tandingModalTitle');
        const eyebrowEl = document.getElementById('tandingModalEyebrow');
        const submitEl = document.getElementById('tandingModalSubmit');
        const baseAction = <?= json_encode(base_url('kontingen/tanding')) ?>;

        const fillOptions = (select, items, selected = null) => {
            select.innerHTML = '';
            const first = document.createElement('option');
            first.value = '';
            first.textContent = 'Pilih kategori tanding';
            select.appendChild(first);

            if (!Array.isArray(items) || items.length === 0) {
                const option = document.createElement('option');
                option.disabled = true;
                option.textContent = 'Kategori tanding tidak ditemukan';
                select.appendChild(option);
                return;
            }

            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id_kompetisi_tanding;
                option.textContent = `${item.nama_kategori_usia} - ${item.jenis_kelamin} kelas ${item.label} (${item.berat_minimal} - ${item.berat_maksimal} Kg)`;
                if (item.disabled) {
                    option.disabled = true;
                    option.textContent += ` - ${item.message}`;
                }
                if (selected && String(selected) === String(item.id_kompetisi_tanding)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        };

        const loadOptions = async (idPendaftar, selected = null) => {
            if (!idPendaftar) {
                fillOptions(kompetisiSelect, []);
                return;
            }
            const response = await fetch(`<?= base_url('kontingen/tanding/options') ?>/` + idPendaftar, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const items = response.ok ? await response.json() : [];
            fillOptions(kompetisiSelect, items, selected);
        };

        pendaftarSelect?.addEventListener('change', () => loadOptions(pendaftarSelect.value));

        modalEl?.addEventListener('show.bs.modal', async (event) => {
            const trigger = event.relatedTarget;
            const mode = trigger?.dataset.mode || 'create';

            if (mode === 'edit') {
                eyebrowEl.textContent = 'Edit';
                titleEl.textContent = 'Edit Kategori Tanding';
                submitEl.textContent = 'Simpan Perubahan';
                form.action = `${baseAction}/${trigger.dataset.id}/update`;
                pendaftarSelect.value = trigger.dataset.pendaftar || '';
                await loadOptions(trigger.dataset.pendaftar || '', trigger.dataset.selected || null);
                pendaftarSelect.setAttribute('disabled', 'disabled');
                let hidden = form.querySelector('input[name="id_pendaftar"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'id_pendaftar';
                    form.appendChild(hidden);
                }
                hidden.value = trigger.dataset.pendaftar || '';
            } else {
                eyebrowEl.textContent = 'Tambah';
                titleEl.textContent = 'Tambah Kategori Tanding';
                submitEl.textContent = 'Simpan';
                form.action = baseAction;
                form.reset();
                fillOptions(kompetisiSelect, []);
                pendaftarSelect.removeAttribute('disabled');
                const hidden = form.querySelector('input[type="hidden"][name="id_pendaftar"]');
                if (hidden) hidden.remove();
            }
        });
    });
</script>
<?= $this->endSection() ?>

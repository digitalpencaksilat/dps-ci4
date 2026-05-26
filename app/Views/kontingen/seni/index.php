<?= $this->extend('layouts/kontingen') ?>

<?= $this->section('content') ?>
<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <p class="eyebrow mb-1">Kategori Seni</p>
            <h3 class="panel-title mb-0">Manajemen Kategori Seni</h3>
        </div>
        <?php if ($allowCreate) : ?>
            <button class="btn btn-danger rounded-pill px-4" type="button" data-bs-toggle="modal" data-bs-target="#seniModal" data-mode="create">
                Tambah Kategori Seni
            </button>
        <?php endif; ?>
    </div>

    <?php if ($kelompokSeni === []) : ?>
        <div class="empty-state-box">
            <div class="empty-state-icon"><i class="fas fa-drum"></i></div>
            <h4>Belum Ada Kategori Seni</h4>
            <p>Belum ada kelompok peserta seni yang didaftarkan untuk kontingen ini.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table align-middle peserta-table mb-0" id="tabelSeniKontingen">
                <thead>
                    <tr>
                        <th>Nama Anggota</th>
                        <th>Berat</th>
                        <th>Tinggi</th>
                        <th>Kategori Usia</th>
                        <th>Jenis Kelamin</th>
                        <th>Kategori Seni</th>
                        <th>Pembayaran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kelompokSeni as $row) : ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($row->anggota_kelompok_peserta_seni ?: '-') ?></td>
                            <td><?= esc($row->berat_anggota_kelompok_peserta_seni ?? '-') ?></td>
                            <td><?= esc($row->tinggi_anggota_kelompok_peserta_seni ?? '-') ?></td>
                            <td><?= esc($row->nama_kategori_usia) ?></td>
                            <td><?= esc($row->jenis_kelamin) ?></td>
                            <td><?= esc($row->jenis_seni) ?> - <?= esc($row->nama_seni) ?></td>
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
                                                        class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#seniModalEdit"
                                                        data-mode="edit"
                                                        data-id="<?= $row->id_kelompok_peserta_seni ?>"
                                                        data-selected="<?= $row->id_kompetisi_seni ?>"
                                                    >Edit</button>
                                                </li>
                                            <?php endif; ?>
                                            <?php if ($allowDelete) : ?>
                                                <li>
                                                    <form method="post" action="<?= base_url('kontingen/seni/' . $row->id_kelompok_peserta_seni . '/delete') ?>" onsubmit="return confirmDeleteAction(this, 'Kategori seni untuk kelompok ini akan dihapus.');">
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

<div class="modal fade" id="seniModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="post" action="<?= base_url('kontingen/seni') ?>" id="formTambahSeniModal">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="eyebrow mb-1">Tambah</p>
                        <h3 class="panel-title mb-0">Tambah Kategori Seni</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pilih Kategori Seni</label>
                            <select name="id_kompetisi_seni" id="id_kompetisi_seni_modal" class="form-select rounded-4" required>
                                <option value="">Pilih kategori seni</option>
                                <?php foreach ($kompetisiSeni as $item) : ?>
                                    <option value="<?= $item->id_kompetisi_seni ?>" data-jenis-seni="<?= esc($item->jenis_seni) ?>" data-jumlah-peserta="<?= esc((string) $item->jumlah_peserta) ?>" <?= !empty($item->disabled) ? 'disabled' : '' ?>>
                                        <?= esc($item->nama_kategori_usia) ?> - <?= esc($item->jenis_kelamin) ?> - <?= esc($item->jenis_seni) ?> <?= esc($item->nama_seni) ?><?= !empty($item->disabled) ? ' (' . esc($item->message) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control rounded-4" placeholder="Opsional, misal senjata atau catatan kelompok">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Pilih Atlet</label>
                            <div id="daftar-atlet-seni-modal" class="peserta-checkbox-grid empty-state-box text-start">
                                Pilih kategori seni terlebih dahulu untuk memuat atlet yang tersedia.
                            </div>
                            <div class="form-text text-muted" id="bantuan-atlet-seni-modal"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-dark rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="seniModalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form method="post" id="formEditSeniModal">
                <?= csrf_field() ?>
                <div class="modal-header border-0 pb-0">
                    <div>
                        <p class="eyebrow mb-1">Edit</p>
                        <h3 class="panel-title mb-0">Edit Kategori Seni</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Kategori Seni Baru</label>
                            <select name="id_kompetisi_seni" id="id_kompetisi_seni_edit" class="form-select rounded-4" required>
                                <?php foreach ($kompetisiSeni as $item) : ?>
                                    <option value="<?= $item->id_kompetisi_seni ?>" <?= !empty($item->disabled) ? 'disabled' : '' ?>>
                                        <?= esc($item->nama_kategori_usia) ?> - <?= esc($item->jenis_kelamin) ?> - <?= esc($item->jenis_seni) ?> <?= esc($item->nama_seni) ?><?= !empty($item->disabled) ? ' (' . esc($item->message) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-dark rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initKontingenDataTable('#tabelSeniKontingen');

        const kategoriSelect = document.getElementById('id_kompetisi_seni_modal');
        const daftarAtlet = document.getElementById('daftar-atlet-seni-modal');
        const bantuan = document.getElementById('bantuan-atlet-seni-modal');
        const editModal = document.getElementById('seniModalEdit');
        const editForm = document.getElementById('formEditSeniModal');
        const editSelect = document.getElementById('id_kompetisi_seni_edit');

        const strictTypes = ['tunggal', 'ganda', 'beregu', 'solo kreatif', 'perorangan', 'berpasangan', 'berkelompok'];

        const renderAtlet = (items, jumlahPeserta, jenisSeni) => {
            daftarAtlet.innerHTML = '';

            if (!Array.isArray(items) || items.length === 0) {
                daftarAtlet.textContent = 'Tidak ada atlet yang memenuhi syarat untuk kategori ini.';
                bantuan.textContent = '';
                return;
            }

            items.forEach((item) => {
                const wrapper = document.createElement('label');
                wrapper.className = 'checkbox-card';
                wrapper.innerHTML = `
                    <input type="checkbox" name="id_pendaftar[]" value="${item.id_pendaftar}">
                    <div>
                        <strong>${item.nama_pendaftar}</strong>
                        <small>${item.nama_sekolah ?? '-'} | ${item.jenis_kelamin}</small>
                    </div>
                `;
                daftarAtlet.appendChild(wrapper);
            });

            bantuan.textContent = strictTypes.includes(String(jenisSeni).toLowerCase())
                ? `Kategori ini membutuhkan tepat ${jumlahPeserta} atlet.`
                : `Kategori ini membutuhkan minimal ${jumlahPeserta} atlet.`;
        };

        const loadAtlet = async () => {
            const id = kategoriSelect?.value;
            if (!id) return;

            const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
            const jumlahPeserta = selectedOption?.dataset.jumlahPeserta || '0';
            const jenisSeni = selectedOption?.dataset.jenisSeni || '';

            const response = await fetch(`<?= base_url('kontingen/seni/options') ?>/` + id, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            const items = response.ok ? await response.json() : [];
            renderAtlet(items, jumlahPeserta, jenisSeni);
        };

        kategoriSelect?.addEventListener('change', loadAtlet);

        editModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            editForm.action = `<?= base_url('kontingen/seni') ?>/${trigger.dataset.id}/update`;
            editSelect.value = trigger.dataset.selected || '';
        });
    });
</script>
<?= $this->endSection() ?>

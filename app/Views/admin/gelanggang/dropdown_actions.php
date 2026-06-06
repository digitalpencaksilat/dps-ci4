<div class="dropdown">
    <button type="button" id="dropdown<?= $gelanggang->id_gelanggang ?>" class="btn btn-sm btn-danger rounded-pill dropdown-toggle px-3"
            data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bars me-1"></i>Aksi
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="min-width: 220px;">
        <li>
            <button type="button" class="dropdown-item"
                    data-bs-toggle="modal" data-bs-target="#modalMergeByDate<?= $gelanggang->id_gelanggang ?>"
                    onclick="loadAvailableDates(<?= $gelanggang->id_gelanggang ?>)">
                <i class="fas fa-calendar-day me-2"></i>Merge PDF (Per Tanggal)
            </button>
        </li>
        <li>
            <form action="<?= base_url('admin/gelanggang/merge/' . $gelanggang->id_gelanggang) ?>" method="post"
                  onsubmit="return confirmAdminAction(this, 'Merge PDF Semua Jadwal?', 'PDF jadwal yang di-merge akan diunduh.', 'Ya, Unduh')">
                <?= csrf_field() ?>
                <button type="submit" class="dropdown-item">
                    <i class="fas fa-file-pdf me-2"></i>Merge PDF (Semua)
                </button>
            </form>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <form action="<?= base_url('admin/gelanggang/delete/' . $gelanggang->id_gelanggang) ?>" method="post"
                  onsubmit="return confirmAdminAction(this, 'Hapus gelanggang?', 'Gelanggang <?= esc($gelanggang->nama_gelanggang, 'attr') ?> beserta seluruh jadwal tanding/seni di dalamnya akan dihapus permanen.', 'Hapus')">
                <?= csrf_field() ?>
                <button type="submit" class="dropdown-item text-danger">
                    <i class="fas fa-trash-alt me-2"></i>Hapus
                </button>
            </form>
        </li>
    </ul>
</div>

<!-- Modal Merge by Date -->
<div class="modal fade" id="modalMergeByDate<?= $gelanggang->id_gelanggang ?>" tabindex="-1"
     aria-labelledby="modalMergeByDateLabel<?= $gelanggang->id_gelanggang ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('admin/gelanggang/merge-by-date/' . $gelanggang->id_gelanggang) ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMergeByDateLabel<?= $gelanggang->id_gelanggang ?>">
                        Merge PDF Per Tanggal - Gelanggang <?= esc($gelanggang->nama_gelanggang) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tanggal_<?= $gelanggang->id_gelanggang ?>" class="form-label">Pilih Tanggal</label>
                        <select name="tanggal" id="tanggal_<?= $gelanggang->id_gelanggang ?>" class="form-select" required>
                            <option value="">-- Loading dates... --</option>
                        </select>
                        <small class="form-text text-muted d-block mt-1" style="white-space: normal; overflow-wrap: break-word;">
                            PDF jadwal hanya akan di-merge untuk tanggal yang dipilih. Nomor partai tetap terurut sesuai jadwal asli.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-brand rounded-pill px-4">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

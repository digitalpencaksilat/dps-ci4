<div class="dropdown">
    <button type="button" id="dropdown<?= esc((string) $jadwal->id_jadwal_tanding) ?>" class="btn btn-sm btn-outline-danger rounded-pill dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        Aksi
    </button>
    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-4">
        <li>
            <a class="dropdown-item" href="<?= base_url('admin/sekretariat/jadwal-tanding/' . $jadwal->id_jadwal_tanding) ?>">
                <i class="fas fa-eye me-2 text-danger"></i>View Matches
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?= base_url('admin/sekretariat/jadwal-tanding/' . $jadwal->id_jadwal_tanding) ?>">
                <i class="fas fa-download me-2 text-danger"></i>Download
            </a>
        </li>
        <?php if (session()->get('level') === 'super_admin'): ?>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalUbahKeteranganTanding<?= esc((string) $jadwal->id_jadwal_tanding) ?>">
                    <i class="fas fa-pen me-2 text-danger"></i>Edit Notes
                </button>
            </li>
            <li>
                <form action="<?= base_url('admin/sekretariat/jadwal-tanding/' . $jadwal->id_jadwal_tanding . '/delete') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin? Schedule will be deleted!')">
                    <?= csrf_field() ?>
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-trash me-2"></i>Delete
                    </button>
                </form>
            </li>
        <?php endif; ?>
    </ul>
</div>

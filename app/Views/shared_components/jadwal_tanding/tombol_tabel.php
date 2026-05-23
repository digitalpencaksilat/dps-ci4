<div class="dropstart">
    <button type="button" id="dropdown<?= $jadwal->id_jadwal_tanding ?>" class="btn btn-default m-0 font-weight-normal shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <ul class="dropdown-menu shadow-lg">
        <li class="dropdown-item">
            <a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('jadwal-tanding/' . $jadwal->id_jadwal_tanding) ?>">View Matches</a>
        </li>
        <li class="dropdown-item">
            <a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('jadwal-tanding/download/' . $jadwal->id_jadwal_tanding) ?>">Download</a>
        </li>
<?php if ($this->session->userdata('level') == 'super_admin'): ?>
            <li class="dropdown-item">
                <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" data-bs-toggle="modal" data-bs-target="#modalUbahKeteranganTanding<?= $jadwal->id_jadwal_tanding ?>">Edit Notes</button>
            </li>
            <li class="dropdown-item">
                <form action="<?= base_url('jadwal-tanding/delete/' . $jadwal->id_jadwal_tanding) ?>" method="post">
                    <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" onclick="confirm_submit('<?= lang('apakah_anda_yakin') ?>', this, 'Schedule will be deleted!', 'Delete', true)">Delete</button>
                </form>
            </li>
        <?php endif; ?>
        <?php if ($this->session->userdata('level') == 'sekretariat' || $this->session->userdata('level') == 'super_admin'): ?>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li class="dropdown-item">
                <a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('jadwal-tanding/konfigurasi-update-pdf/' . $jadwal->id_jadwal_tanding) ?>">Update PDF</a>
            </li>
            <li class="dropdown-item">
                <form action="<?= base_url('jadwal-tanding/create-excel/' . $jadwal->id_jadwal_tanding) ?>" method="post">
                    <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" onclick="confirm_submit('<?= lang('apakah_anda_yakin') ?>', this, 'Excel schedule will be downloaded!', 'Download', true)">Download Excel</button>
                </form>
            </li>
            <li class="dropdown-item">
                <form action="<?= base_url('jadwal-tanding/create-excel-hasil-pertandingan/' . $jadwal->id_jadwal_tanding) ?>" method="post">
                    <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" onclick="confirm_submit('<?= lang('apakah_anda_yakin') ?>', this, 'Match results in Excel format will be downloaded!', 'Download', true)">Download Match Results Excel</button>
                </form>
            </li>
            <li class="dropdown-item">
                <a class="btn btn-default shadow-none m-0 w-100 text-start" target="_blank" href="<?= base_url('jadwal-tanding/cetak-form-penimbangan/' . $jadwal->id_jadwal_tanding) ?>">Print Weighing Form</a>
            </li>
        <?php endif; ?>
    </ul>
</div>
<div class="dropstart">
    <button type="button" id="dropdown<?= $jadwal->id_jadwal_seni ?>" class="btn btn-default m-0 font-weight-normal shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <ul class="dropdown-menu shadow-lg">
        <li class="dropdown-item">
            <a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('jadwal-seni/' . $jadwal->id_jadwal_seni) ?>">View Matches</a>
        </li>
        <li class="dropdown-item">
            <a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('jadwal-seni/download/' . $jadwal->id_jadwal_seni) ?>">Download</a>
        </li>
<?php if( $this->session->userdata('level') == 'super_admin'):?>
            <li class="dropdown-item">
                <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" data-bs-toggle="modal" data-bs-target="#modalUbahKeteranganSeni<?= $jadwal->id_jadwal_seni ?>">Edit Notes</button>
            </li>
            <li class="dropdown-item">
                 <form action="<?= base_url('jadwal-seni/delete/' . $jadwal->id_jadwal_seni) ?>" method="post">
                    <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" onclick="confirm_submit('<?= lang('apakah_anda_yakin')?>', this, 'Schedule will be deleted!', 'Delete', true)">Delete</button>
                </form>
            </li>
        <?php endif;?>
        <?php if($this->session->userdata('level') == 'sekretariat' || $this->session->userdata('level') == 'super_admin'):?>
            <li><hr class="dropdown-divider"></li>
            <li class="dropdown-item">
                 <button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" data-bs-toggle="modal" data-bs-target="#modalUpdatePdfSeni<?= $jadwal->id_jadwal_seni ?>">Update PDF</button>
            </li>
        <?php endif;?>
    </ul>
</div>

<?php if($this->session->userdata('level') == 'sekretariat' || $this->session->userdata('level') == 'super_admin'):?>
<!-- Modal Update PDF -->
<div class="modal fade" id="modalUpdatePdfSeni<?= $jadwal->id_jadwal_seni ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= base_url('jadwal-seni/create-pdf/' . $jadwal->id_jadwal_seni) ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Update PDF Jadwal Seni</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="form-label mb-2">Pilih PDF Library</p>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pdf_library" id="pdfLibDompdfSeni<?= $jadwal->id_jadwal_seni ?>" value="dompdf" checked>
                        <label class="form-check-label" for="pdfLibDompdfSeni<?= $jadwal->id_jadwal_seni ?>">
                            DOMPDF <small class="text-muted">(default, stabil)</small>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="pdf_library" id="pdfLibMpdfSeni<?= $jadwal->id_jadwal_seni ?>" value="mpdf">
                        <label class="form-check-label" for="pdfLibMpdfSeni<?= $jadwal->id_jadwal_seni ?>">
                            mPDF <small class="text-muted">(lebih cepat)</small>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif;?>

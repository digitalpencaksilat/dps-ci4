<?php
/**
 * Modal cetak sertifikat editable (parity dengan legacy modalPrintSertifikat).
 * Form GET ke print URL → window.print() di tab baru.
 */
?>
<div class="modal fade" id="modalPrintSertifikat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="" method="get" target="_blank" class="modal-content" id="formPrintSertifikat">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-certificate text-danger me-2"></i>Cetak Sertifikat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Anda dapat menyesuaikan teks di bawah sebelum mencetak. Kosongkan untuk memakai data default.
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="pmNomor" class="form-label">Nomor Sertifikat</label>
                        <input type="text" class="form-control" name="nomor" id="pmNomor" placeholder="Nomor">
                    </div>
                    <div class="col-md-8">
                        <label for="pmNama" class="form-label">Nama Atlet</label>
                        <input type="text" class="form-control" name="nama" id="pmNama" placeholder="Nama Atlet">
                    </div>
                    <div class="col-12">
                        <label for="pmKategori" class="form-label">Kategori / Juara</label>
                        <input type="text" class="form-control" name="kategori" id="pmKategori" placeholder="Kategori">
                    </div>
                    <div class="col-md-6">
                        <label for="pmKontingen" class="form-label">Kontingen</label>
                        <input type="text" class="form-control" name="kontingen" id="pmKontingen" placeholder="Kontingen">
                    </div>
                    <div class="col-md-6">
                        <label for="pmSekolah" class="form-label">Sekolah</label>
                        <input type="text" class="form-control" name="sekolah" id="pmSekolah" placeholder="Sekolah">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="fa-solid fa-print me-1"></i> Cetak</button>
            </div>
        </form>
    </div>
</div>
<?php /* JS untuk modal ini ada di section('scripts') view pemanggil (cetak_tanding/cetak_seni). */ ?>

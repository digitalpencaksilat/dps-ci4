     1|<button type="button" class="btn btn-outline-primary m-0 mt-0 w-100" data-bs-toggle="modal" data-bs-target="#modalKirimEmail">
     2|	Kirim Email
     3|</button>
     4|<div class="modal fade" id="modalKirimEmail" tabindex="-1" role="dialog" aria-labelledby="modalKirimEmail" aria-hidden="true">
     5|	<div class="modal-dialog" role="document">
     6|		<div class="modal-content">
     7|			<form action="<?= base_url('kontingen/kirim-email/' . $kontingen->id_kontingen) ?>" novalidate="novalidate" id="formKirimEmailKontingen" class="needs-validation" method="post" accept-charset="utf-8">
     8|				<div class="modal-header">
     9|					<h5 class="modal-title">Kirim Email Ke <?= $kontingen->email_kontingen?></h5>
    10|					<button type="button" class="btn btn-link m-0" data-bs-dismiss="modal" aria-label="Close">
    11|						<span aria-hidden="true">×</span>
    12|					</button>
    13|				</div>
    14|				<div class="modal-body">
    15|					<div class="mb-2">
    16|						<label for="subject_email"> Subject</label>
    17|						<input type="text" name="subject_email" value="<?= set_value('subject_email'); ?>" id="subject_email" class="form-control" required="true" />
    18|						<small class="text-danger"><?= form_error('subject_email'); ?></small>
    19|						<div class="invalid-feedback">
    20|							Wajib diisi
    21|						</div>
    22|					</div>
    23|
    24|					<div class="mb-2">
    25|						<label for="pesan_email">Isi Pesan</label>
    26|						<textarea name="isi_pesan" class="form-control" id="pesan_email" cols="30" rows="3"><?= set_value('isi_pesan'); ?></textarea>
    27|						<small class="text-danger"><?= form_error('isi_pesan'); ?></small>
    28|					</div>
    29|				</div>
    30|				<div class="modal-footer">
    31|					<button class="btn btn-outline-default mb-0 me-2" type="button" data-bs-dismiss="modal">Tutup</button>
    32|					<button class="btn btn-primary m-0" type="button" onclick="$('#modalKirimEmail').modal('hide');confirm_submit('<?= Apakah Anda Yakin?>', this, 'Email ini akan segera dikirim', 'Ya kirim', true, 'Sedang mengirim email...')">Kirim </button>
    33|				</div>
    34|			</form>
    35|		</div>
    36|	</div>
    37|</div>
    38|
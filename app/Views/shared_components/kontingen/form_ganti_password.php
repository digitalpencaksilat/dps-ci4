
<form id="formGantiPassword" class="needs-validation" method="post" action="<?= base_url('kontingen/update_password/' . $kontingen->id_kontingen) ?>" enctype="multipart/form-data" novalidate>
    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" name="password" type="password" required="required" value="<?= set_value('password') ?>">
                <div class=" invalid-feedback">
                    Wajib Diisi
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Ketik Ulang Password</label>
                <input class="form-control" name="retype_password" type="password" required="required" value="<?= set_value('retype_password') ?>">
                <div class="invalid-feedback">
                    Wajib Diisi dan harus sama dengan isian password
                </div>
            </div>
          
        </div>
        <div class="col-lg-12">
            <button class="btn btn-primary w-100 m-0" type="submit">Ganti Password</button>
        </div>
    </div>
</form>


<script>
    $(function() {
        $('#formGantiPassword').on('change', '[name="password"], [name="retype_password"]', function(e) {
            if ($('#formGantiPassword [name="password"]').val() !== $('#formGantiPassword [name="retype_password"]').val()) {
                $('#formGantiPassword [name="retype_password"]+.invalid-feedback').fadeIn();
                $('#formGantiPassword [type="submit"]').attr('disabled', true);
            } else {
                $('#formGantiPassword [name="retype_password"]+.invalid-feedback').fadeOut();
                $('#formGantiPassword [type="submit"]').removeAttr('disabled');
            }
        });
    })
</script>
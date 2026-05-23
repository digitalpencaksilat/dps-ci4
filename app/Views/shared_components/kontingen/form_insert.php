<!-- Tambahkan link icon jika belum ada di header Anda (opsional) -->


<style>
    /* Sedikit penyesuaian untuk Select2 agar menyatu dengan gaya modern */
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.5rem;
        padding: 0.375rem 0.75rem;
        border: 1px solid #dee2e6;
    }

    .form-floating>.form-control:focus~label::after,
    .form-floating>.form-control:not(:placeholder-shown)~label::after {
        background-color: transparent !important;
    }
</style>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="card-header bg-primary text-white p-4">
        <h4 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Registrasi Kontingen</h4>
        <p class="mb-0 text-white-50 small">Silakan lengkapi data di bawah ini untuk mendaftar.</p>
    </div>
    <div class="card-body p-4 p-md-5">
        <form id="formRegistrasiKontingen" method="post" action="<?= base_url('kontingen/create') ?>" enctype="multipart/form-data" novalidate>
            <div class="row g-4">

                <!-- KOLOM KIRI: Informasi Akun & Penanggung Jawab -->
                <div class="col-md-6 border-end-md pe-md-4">
                    <h5 class="fw-bold text-secondary mb-4 pb-2 border-bottom">Informasi Akun</h5>

                    <div class="form-floating mb-3">
                        <input class="form-control rounded-3" id="nama_kontingen" placeholder="<?= lang('contoh_nama_kontingen') ?>" name="nama_kontingen" type="text" required="required" value="<?= set_value('nama_kontingen') ?>">
                        <label for="nama_kontingen"><?= lang('nama_kontingen') ?></label>
                        <div class="invalid-feedback"><?= lang('wajib_diisi') ?></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input class="form-control rounded-3" id="email_kontingen" placeholder="<?= lang('contoh_email') ?>" name="email_kontingen" type="email" required="required" value="<?= set_value('email_kontingen') ?>">
                        <label for="email_kontingen"><?= lang('email_kontingen') ?></label>
                        <!-- <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i><?= lang('email_info') ?></div> -->
                        <div class="invalid-feedback"><?= lang('wajib_email_format') ?></div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <div class="form-floating">
                                <input class="form-control rounded-3" id="password" placeholder="Password" name="password" type="password" required="required" value="<?= set_value('password') ?>">
                                <label for="password"><?= lang('password') ?></label>
                                <div class="invalid-feedback"><?= lang('wajib_diisi') ?></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-floating">
                                <input class="form-control rounded-3" id="retype_password" placeholder="Ulangi Password" name="retype_password" type="password" required="required" value="<?= set_value('retype_password') ?>">
                                <label for="retype_password"><?= lang('retype_password') ?></label>
                                <div class="invalid-feedback"><?= lang('wajib_diisi_password_sama') ?></div>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold text-secondary mt-5 mb-4 pb-2 border-bottom">Penanggung Jawab</h5>

                    <div class="form-floating mb-3">
                        <input class="form-control rounded-3" id="nama_pj" placeholder="Nama PJ" name="nama_penanggungjawab" type="text" required="required" value="<?= set_value('nama_penanggungjawab') ?>">
                        <label for="nama_pj"><?= lang('nama_penanggungjawab') ?></label>
                        <div class="invalid-feedback"><?= lang('wajib_diisi') ?></div>
                    </div>

                    <div class="form-floating mb-3">
                        <input class="form-control rounded-3" id="no_telp_pj" placeholder="<?= lang('contoh_nomor_telepon') ?>" name="nomor_telepon_penanggungjawab" type="number" min="0" required="required" value="<?= set_value('nomor_telepon_penanggungjawab') ?>">
                        <label for="no_telp_pj"><?= lang('nomor_telepon_penanggungjawab') ?></label>
                        <div class="invalid-feedback"><?= lang('wajib_diisi_nomor') ?></div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Informasi Wilayah -->
                <div class="col-md-6 ps-md-4">
                    <h5 class="fw-bold text-secondary mb-4 pb-2 border-bottom">Informasi Asal / Wilayah</h5>

                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-2"><?= lang('asal_kontingen') ?></label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="jenis_kontingen" id="jenis_dalam" value="dalam_negeri" required="required" <?= set_radio('jenis_kontingen', 'dalam_negeri') ?>>
                            <label class="btn btn-outline-primary rounded-3 w-100 py-2" for="jenis_dalam">
                                <i class="bi bi-geo-alt me-1"></i> <?= lang('dalam_negeri') ?>
                            </label>

                            <input type="radio" class="btn-check" name="jenis_kontingen" id="jenis_luar" value="luar_negeri" required="required" <?= set_radio('jenis_kontingen', 'luar_negeri') ?>>
                            <label class="btn btn-outline-primary rounded-3 w-100 py-2" for="jenis_luar">
                                <i class="bi bi-globe-americas me-1"></i> <?= lang('luar_negeri') ?>
                            </label>
                        </div>
                    </div>

                    <!-- Gunakan wrapper div sesuai kelas js (.negara, .provinsi, dll) -->
                    <div class="mb-3 negara">
                        <label class="form-label text-muted small fw-semibold"><?= lang('negara') ?></label>
                        <select name="negara" id="inputnegara" class="form-select" required="required">
                            <option value="" disabled selected><?= lang('--pilih_negara--') ?></option>
                        </select>
                        <div class="invalid-feedback"><?= lang('wajib_diisi') ?></div>
                    </div>

                    <div class="mb-3 provinsi">
                        <label class="form-label text-muted small fw-semibold"><?= lang('provinsi') ?></label>
                        <select name="provinsi" id="inputprovinsi" class="form-select">
                            <option value="" disabled selected><?= lang('--pilih_provinsi--') ?></option>
                        </select>
                        <div class="form-text small"><?= lang('apabila_data_provinsi_tidak_ditemukan') ?></div>
                    </div>

                    <div class="mb-3 kabupaten_kota">
                        <label class="form-label text-muted small fw-semibold"><?= lang('kabupaten_kota') ?></label>
                        <select name="kabupaten_kota" id="inputkabupaten_kota" class="form-select">
                            <option value="" disabled selected><?= lang('--pilih_kabupaten_kota--') ?></option>
                        </select>
                        <div class="form-text small"><?= lang('apabila_data_kota_tidak_ditemukan') ?></div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6 kecamatan">
                            <label class="form-label text-muted small fw-semibold" for="inputKecamatan"><?= lang('kecamatan') ?></label>
                            <select name="kecamatan" id="inputKecamatan" class="form-select">
                                <option value="" disabled selected><?= lang('--pilih_kecamatan--') ?></option>
                            </select>
                            <div class="form-text small"><?= lang('apabila_data_kecamatan_tidak_ditemukan') ?></div>
                        </div>

                        <div class="col-sm-6 kelurahan">
                            <label class="form-label text-muted small fw-semibold" for="inputKelurahan"><?= lang('kelurahan') ?></label>
                            <select name="kelurahan" id="inputKelurahan" class="form-select">
                                <option value="" disabled selected><?= lang('--pilih_kelurahan--') ?></option>
                            </select>
                            <div class="form-text small"><?= lang('apabila_data_kelurahan_tidak_ditemukan') ?></div>
                        </div>
                    </div>

                    <div class="form-floating mt-4">
                        <textarea class="form-control rounded-3" name="alamat_lengkap" id="alamat_lengkap" placeholder="Alamat Lengkap" style="height: 100px" required></textarea>
                        <label for="alamat_lengkap"><?= lang('alamat_lengkap') ?></label>
                        <div class="invalid-feedback"><?= lang('wajib_diisi') ?></div>
                        <!-- <div class="form-text text-muted small"><i class="bi bi-building me-1"></i><?= lang('alamat_info') ?></div> -->
                    </div>
                </div>

                <?php if (session('level') == null): ?>
                    <div class="col-12 mt-4">
                        <div class="g-recaptcha" data-sitekey="<?= esc(env('RECAPTCHA_SITE_KEY', '')) ?>"></div>
                        <div class="recaptcha-error text-danger small mt-2" style="display:none;">
                            Verifikasi reCAPTCHA wajib dilakukan.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- TOMBOL SUBMIT -->
                <div class="col-12 text-end mt-5 border-top pt-4">
                    <button class="btn btn-light btn-lg rounded-pill px-4 me-2" type="button" onclick="history.back()">Batal</button>
                    <button class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm" type="submit">
                        <?= lang('daftar_sekarang') ?> <i class="bi bi-arrow-right-circle ms-2"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<?php if (session('level') == null): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
<script>
    (() => {
        'use strict'
        const forms = document.querySelectorAll('#formRegistrasiKontingen')

        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                const needsRecaptcha = form.querySelector('.g-recaptcha') !== null;
                const recaptchaResponse = (needsRecaptcha && window.grecaptcha) ? grecaptcha.getResponse() : '';

                if (needsRecaptcha && recaptchaResponse.length === 0) {
                    event.preventDefault();
                    event.stopPropagation();
                    $('.recaptcha-error').show();
                } else {
                    $('.recaptcha-error').hide();
                }

                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    $(function() {
        get_negara(); // inisiasi awal

        $('#formRegistrasiKontingen .provinsi, #formRegistrasiKontingen .kabupaten_kota, #formRegistrasiKontingen .kecamatan, #formRegistrasiKontingen .kelurahan').hide('fast');
        $('[name="negara"], [name="provinsi"], [name="kabupaten_kota"], [name="kecamatan"]').select2({
            theme: "bootstrap-5",
        });

        $('#formRegistrasiKontingen').on('change', '[name="password"], [name="retype_password"]', function(e) {
            if ($('#formRegistrasiKontingen [name="password"]').val() !== $('#formRegistrasiKontingen [name="retype_password"]').val()) {
                $('#formRegistrasiKontingen [name="retype_password"]+.invalid-feedback').fadeIn();
                $('#formRegistrasiKontingen [type="submit"]').attr('disabled', true);
            } else {
                $('#formRegistrasiKontingen [name="retype_password"]+.invalid-feedback').fadeOut();
                $('#formRegistrasiKontingen [type="submit"]').removeAttr('disabled');
            }
        });

        $('#formRegistrasiKontingen').on('change', '[name="jenis_kontingen"]', function(e) {
            if (e.target.value == "dalam_negeri") {
                if ('<?= $this->config->item('default_nationality', 'pendaftaran/negara') ?>' == "Indonesia") {
                    $('#formRegistrasiKontingen .provinsi, #formRegistrasiKontingen .kabupaten_kota, #formRegistrasiKontingen .kecamatan, #formRegistrasiKontingen .kelurahan').show('slow');
                }
                //GET JSON PROVINSI
                $('#formRegistrasiKontingen .negara').hide('fast');
                get_provinsi();
            } else {
                $('#formRegistrasiKontingen .provinsi, #formRegistrasiKontingen .kabupaten_kota, #formRegistrasiKontingen .kecamatan, #formRegistrasiKontingen .kelurahan').hide('slow');
                $('#formRegistrasiKontingen .negara').show('fast');
                get_negara();
            }
        });

        $('#formRegistrasiKontingen').on('change', '[name="provinsi"]', function(e) {
            //GET JSON KABUPATEN_KOTA
            get_kabupaten_kota();
        });

        $('#formRegistrasiKontingen').on('change', '[name="kabupaten_kota"]', function(e) {
            //GET JSON KECAMATAN
            get_kecamatan();
        });

        $('#formRegistrasiKontingen').on('change', '[name="kecamatan"]', function(e) {
            //GET JSON KELURAHAN
            get_kelurahan();
        });

        function get_provinsi() {
            // Get Provinsi sdh otomatis mengambil daerah dibawahnya
            $.getJSON("<?php echo base_url("utilities/location/load-provinsi") ?>",
                function(data, textStatus, jqXHR) {
                    $('#formRegistrasiKontingen [name="provinsi"]').html('');
                    $.each(data, function(i, v) {
                        $('#formRegistrasiKontingen [name="provinsi"]').append('<option data-id-provinsi="' + v + '" value="' + i + '">' + i + '</option>');
                    });

                    //CALLBACK UPDATE KABUPATEN_KOTA
                    //GET JSON KABUPATEN_KOTA
                    get_kabupaten_kota();
                }
            );
        }

        function get_negara() {
            $.getJSON("<?php echo base_url("utilities/location/load-negara") ?>",
                function(data, textStatus, jqXHR) {
                    $('#formRegistrasiKontingen [name="negara"]').html('');
                    $.each(data, function(i, v) {
                        if (v == 'Indonesia') {
                            $('#formRegistrasiKontingen [name="negara"]').append('<option data-id-negara="' + v + '" value="' + i + '" selected>' + i + '</option>');
                        } else {
                            $('#formRegistrasiKontingen [name="negara"]').append('<option data-id-negara="' + v + '" value="' + i + '">' + i + '</option>');
                        }
                    });
                }
            );
        }

        function get_kabupaten_kota() {
            $.getJSON("<?php echo base_url("utilities/location/load-kabupaten-kota/") ?>" + $('[name="provinsi"]').find(':selected').data('id-provinsi'),
                function(data, textStatus, jqXHR) {
                    $('#formRegistrasiKontingen [name="kabupaten_kota"]').html('');
                    $.each(data, function(i, v) {
                        $('#formRegistrasiKontingen [name="kabupaten_kota"]').append('<option data-id-kabupaten-kota="' + v + '" value="' + i + '">' + i + '</option>');
                    });

                    // Get Kabupaten kota sudah otomatis get kecamatan
                    get_kecamatan();
                }
            );
        }

        function get_kecamatan() {
            $.getJSON("<?php echo base_url("utilities/location/load-kecamatan/") ?>" + $('[name="kabupaten_kota"]').find(':selected').data('id-kabupaten-kota'),
                function(data, textStatus, jqXHR) {
                    $('#formRegistrasiKontingen [name="kecamatan"]').html('');
                    $.each(data, function(i, v) {
                        $('#formRegistrasiKontingen [name="kecamatan"]').append('<option data-id-kecamatan="' + v + '" value="' + i + '">' + i + '</option>');
                    });
                    get_kelurahan();
                }
            );
        }

        function get_kelurahan() {
            $.getJSON("<?php echo base_url("utilities/location/load-kelurahan/") ?>" + $('[name="kecamatan"]').find(':selected').data('id-kecamatan'),
                function(data, textStatus, jqXHR) {
                    $('#formRegistrasiKontingen [name="kelurahan"]').html('');
                    $.each(data, function(i, v) {
                        $('#formRegistrasiKontingen [name="kelurahan"]').append('<option data-id-kelurahan="' + v + '" value="' + i + '">' + i + '</option>');
                    });
                }
            );
        }
    })
</script>
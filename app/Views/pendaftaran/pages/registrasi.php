<?php
$validation = session()->getFlashdata('errors') ?? session('errors') ?? [];
if ($validation === [] && session()->has('validation')) {
    $validation = session('validation')->getErrors();
}

$fieldError = static fn(string $field): ?string => isset($validation[$field]) && $validation[$field] !== '' ? (string) $validation[$field] : null;
$fieldClass = static fn(string $field): string => $fieldError($field) !== null ? ' is-invalid' : '';
?>

<style>
    body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .registration-shell {
        flex: 1 0 auto;
        width: 100%;
        padding-top: 6rem;
        padding-bottom: 6rem;
        --bg-color: #f4f6f9;
        background: var(--bg-color);
    }

    footer {
        flex-shrink: 0;
    }

    .registration-card-header {
        padding: 1.65rem 2rem 1.35rem;
        background: linear-gradient(135deg, rgba(198, 0, 0, 0.92) 0%, rgba(122, 13, 20, 0.98) 55%, rgba(26, 26, 26, 0.98) 100%);
        color: #fff;
    }

    .registration-card-title {
        margin: 0;
        font-family: 'Oswald', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: clamp(1.7rem, 3.5vw, 2.25rem);
        line-height: 1.05;
    }

    .registration-card-lead {
        margin: 0.65rem 0 0;
        max-width: 52rem;
        color: rgba(255,255,255,0.82);
        font-size: 1rem;
        line-height: 1.7;
    }

    .registration-card {
        border: 0;
        border-radius: 28px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 20px 50px rgba(33, 37, 41, 0.09);
    }

    .registration-card .card-body {
        padding: 2rem;
    }

    .registration-stepper {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .registration-step-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 18px;
        border: 1px solid rgba(33, 37, 41, 0.14);
        background: #fff;
        color: #495057;
        font-weight: 700;
        font-size: 0.92rem;
        transition: 0.2s ease;
    }

    .registration-step-btn .step-index {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(33, 37, 41, 0.07);
        color: #495057;
        font-weight: 800;
        flex-shrink: 0;
    }

    .registration-step-btn.is-complete {
        border-color: rgba(198, 0, 0, 0.22);
        color: var(--brand-primary);
        background: rgba(198, 0, 0, 0.04);
    }

    .registration-step-btn.is-complete .step-index {
        background: rgba(198, 0, 0, 0.12);
        color: var(--brand-primary);
    }

    .registration-step-btn.is-active {
        border-color: rgba(198, 0, 0, 0.55);
        box-shadow: 0 0 0 0.2rem rgba(198, 0, 0, 0.10);
        color: var(--brand-primary);
    }

    .registration-step-btn.is-active .step-index {
        background: var(--brand-primary);
        color: #fff;
    }

    .registration-step-btn:hover {
        border-color: rgba(198, 0, 0, 0.35);
        transform: translateY(-1px);
    }

    .registration-step-pane {
        display: none;
    }

    .registration-step-pane.is-active {
        display: block;
    }

    .registration-section {
        padding: 1.35rem;
        border-radius: 24px;
        background: #fff;
        border: 1px solid rgba(33, 37, 41, 0.08);
    }

    .registration-section-heading {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        margin-bottom: 1.1rem;
    }

    .registration-section-icon {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(198, 0, 0, 0.08);
        color: var(--brand-primary);
        flex-shrink: 0;
    }

    .registration-section-heading h3 {
        margin: 0;
        color: var(--brand-dark);
        font-size: 1.2rem;
    }

    .registration-section-heading p {
        margin: 0.3rem 0 0;
        color: #6c757d;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .registration-form .form-label {
        margin-bottom: 0.5rem;
        color: var(--brand-dark);
        font-weight: 600;
    }

    .registration-form .form-control,
    .registration-form .form-select {
        border-radius: 16px;
        border: 1px solid #e0e0e0;
        padding: 0.85rem 1rem;
        color: var(--brand-dark);
        background-color: #fff;
    }

    .registration-form .form-control.form-control-lg,
    .registration-form .form-select.form-select-lg {
        min-height: calc(3.5rem + 2px);
    }

    .registration-form textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .registration-form .form-control:focus,
    .registration-form .form-select:focus {
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 0.2rem rgba(198, 0, 0, 0.12);
    }

    .field-hint {
        margin-top: 0.45rem;
        color: #6c757d;
        font-size: 0.83rem;
    }

    .registration-step-actions {
        margin-top: 1.15rem;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .btn-registration-primary {
        width: 100%;
        padding: 0.9rem 1.3rem;
        border-radius: 999px;
        border: 2px solid var(--brand-primary);
        background: var(--brand-primary);
        color: #fff;
        font-family: 'Oswald', sans-serif;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        transition: all 0.25s ease;
    }

    .btn-registration-primary:hover,
    .btn-registration-primary:focus {
        background: #a00000;
        border-color: #a00000;
        color: #fff;
        box-shadow: 0 10px 24px rgba(198, 0, 0, 0.22);
    }

    .btn-registration-secondary {
        width: 100%;
        padding: 0.9rem 1.3rem;
        border-radius: 999px;
        border: 1px solid rgba(33, 37, 41, 0.14);
        background: #fff;
        color: var(--brand-dark);
        font-weight: 700;
        transition: 0.2s ease;
    }

    .btn-registration-secondary:hover,
    .btn-registration-secondary:focus {
        border-color: var(--brand-primary);
        color: var(--brand-primary);
        background: rgba(198, 0, 0, 0.04);
    }

    .btn-registration-secondary.btn-registration-outline {
        background: transparent;
        border-color: rgba(198, 0, 0, 0.35);
        color: var(--brand-primary);
    }

    .btn-registration-secondary.btn-registration-outline:hover,
    .btn-registration-secondary.btn-registration-outline:focus {
        border-color: rgba(198, 0, 0, 0.7);
    }

    .registration-closed {
        padding: 1.5rem;
        border-radius: 22px;
        background: #f8f9fa;
        color: #495057;
        border: 1px solid rgba(33, 37, 41, 0.08);
    }

    @media (max-width: 991.98px) {
        .registration-stepper {
            grid-template-columns: 1fr;
        }

        .registration-step-btn {
            justify-content: flex-start;
        }

        .registration-step-actions {
            justify-content: stretch;
        }

        .registration-step-actions > * {
            flex: 1 0 220px;
        }
    }

    @media (max-width: 767.98px) {
        .registration-shell {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .registration-card-header {
            padding: 1.35rem 1.25rem 1.15rem;
        }

        .registration-card .card-body {
            padding: 1.25rem;
        }

        .registration-section {
            padding: 1rem;
        }

        .registration-section-heading {
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .registration-section-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
        }
    }
</style>

<section class="registration-shell">
    <div class="container py-lg-4">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="registration-card card">
                    <div class="registration-card-header card-header border-0">
                        <h1 class="registration-card-title card-title">Registrasi Kontingen</h1>
                        <p class="registration-card-lead">Isi data kontingen secara bertahap. Pastikan email aktif dan data wilayah sesuai domisili resmi.</p>
                    </div>
                    <div class="card-body p-4 p-lg-5">
                        <?= view('shared_components/notification') ?>

                        <?php if (! empty($perbolehkan_kontingen_mendaftar)) : ?>
                            <form method="post" action="<?= base_url('registrasi') ?>" class="registration-form" id="formRegistrasiKontingen" novalidate>
                                <?= csrf_field() ?>
                                <input type="hidden" name="jabatan_penanggungjawab" value="<?= esc((string) old('jabatan_penanggungjawab', 'Manager Kontingen'), 'attr') ?>">
                                <input type="hidden" name="nomor_telepon_kontingen" value="<?= esc((string) old('nomor_telepon_kontingen'), 'attr') ?>">

                                <div class="registration-stepper" aria-label="Langkah registrasi">
                                    <button type="button" class="registration-step-btn" data-step-button="1">
                                        <span class="step-index">1</span>
                                        <span>Data Akun</span>
                                    </button>
                                    <button type="button" class="registration-step-btn" data-step-button="2">
                                        <span class="step-index">2</span>
                                        <span>Wilayah</span>
                                    </button>
                                    <button type="button" class="registration-step-btn" data-step-button="3">
                                        <span class="step-index">3</span>
                                        <span>Kontak</span>
                                    </button>
                                </div>

                                <div class="registration-step-pane" data-step-pane="1">
                                    <div class="registration-section">
                                        <div class="registration-section-heading">
                                            <span class="registration-section-icon"><i class="fas fa-id-badge"></i></span>
                                            <div>
                                                <h3>Data Akun Kontingen</h3>
                                                <p>Identitas utama akun yang akan dipakai untuk masuk ke panel kontingen.</p>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="registrasi_nama_kontingen" class="form-label">Nama Kontingen</label>
                                                <input type="text" id="registrasi_nama_kontingen" name="nama_kontingen" class="form-control form-control-lg<?= $fieldClass('nama_kontingen') ?>" value="<?= esc((string) old('nama_kontingen'), 'attr') ?>" required>
                                                <?php if ($fieldError('nama_kontingen') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('nama_kontingen')) ?></div>
                                                <?php endif; ?>
                                                <div class="field-hint">Gunakan nama resmi kontingen/sekolah/perguruan.</div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="registrasi_email_kontingen" class="form-label">Email Kontingen</label>
                                                <input type="email" id="registrasi_email_kontingen" name="email_kontingen" class="form-control form-control-lg<?= $fieldClass('email_kontingen') ?>" value="<?= esc((string) old('email_kontingen'), 'attr') ?>" required>
                                                <?php if ($fieldError('email_kontingen') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('email_kontingen')) ?></div>
                                                <?php endif; ?>
                                                <div class="field-hint">Email ini akan menjadi username login kontingen.</div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="registrasi_password" class="form-label">Password</label>
                                                <input type="password" id="registrasi_password" name="password" class="form-control form-control-lg<?= $fieldClass('password') ?>" required>
                                                <?php if ($fieldError('password') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('password')) ?></div>
                                                <?php endif; ?>
                                                <div class="field-hint">Minimal 6 karakter agar akun lebih aman.</div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="registrasi_retype_password" class="form-label">Ulangi Password</label>
                                                <input type="password" id="registrasi_retype_password" name="retype_password" class="form-control form-control-lg<?= $fieldClass('retype_password') ?>" required>
                                                <?php if ($fieldError('retype_password') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('retype_password')) ?></div>
                                                <?php endif; ?>
                                                <div class="field-hint">Masukkan ulang password yang sama untuk konfirmasi.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="registration-step-actions">
                                        <button type="button" class="btn btn-registration-secondary btn-registration-outline" data-step-next="2">
                                            Lanjut ke Wilayah <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="registration-step-pane" data-step-pane="2">
                                    <div class="registration-section">
                                        <div class="registration-section-heading">
                                            <span class="registration-section-icon"><i class="fas fa-map-location-dot"></i></span>
                                            <div>
                                                <h3>Wilayah dan Domisili Kontingen</h3>
                                                <p>Pilih jenis kontingen terlebih dahulu, lalu lengkapi wilayah sesuai domisili resmi kontingen.</p>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="registrasi_jenis_kontingen" class="form-label">Jenis Kontingen</label>
                                                <select name="jenis_kontingen" id="registrasi_jenis_kontingen" class="form-select form-select-lg<?= $fieldClass('jenis_kontingen') ?>" required>
                                                    <option value="dalam_negeri" <?= old('jenis_kontingen', 'dalam_negeri') === 'dalam_negeri' ? 'selected' : '' ?>>Dalam Negeri</option>
                                                    <option value="luar_negeri" <?= old('jenis_kontingen') === 'luar_negeri' ? 'selected' : '' ?>>Luar Negeri</option>
                                                </select>
                                                <?php if ($fieldError('jenis_kontingen') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('jenis_kontingen')) ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="negara" class="form-label">Negara</label>
                                                <select name="negara" id="negara" class="form-select form-select-lg<?= $fieldClass('negara') ?>"></select>
                                                <?php if ($fieldError('negara') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('negara')) ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-md-6 wilayah-dalam-negeri">
                                                <label for="provinsi" class="form-label">Provinsi</label>
                                                <select name="provinsi" id="provinsi" class="form-select form-select-lg<?= $fieldClass('provinsi') ?>"></select>
                                                <?php if ($fieldError('provinsi') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('provinsi')) ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-md-6 wilayah-dalam-negeri">
                                                <label for="kabupaten_kota" class="form-label">Kabupaten / Kota</label>
                                                <select name="kabupaten_kota" id="kabupaten_kota" class="form-select form-select-lg<?= $fieldClass('kabupaten_kota') ?>"></select>
                                                <?php if ($fieldError('kabupaten_kota') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('kabupaten_kota')) ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-md-6 wilayah-dalam-negeri">
                                                <label for="kecamatan" class="form-label">Kecamatan</label>
                                                <select name="kecamatan" id="kecamatan" class="form-select form-select-lg<?= $fieldClass('kecamatan') ?>"></select>
                                                <?php if ($fieldError('kecamatan') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('kecamatan')) ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-md-6 wilayah-dalam-negeri">
                                                <label for="kelurahan" class="form-label">Kelurahan</label>
                                                <select name="kelurahan" id="kelurahan" class="form-select form-select-lg<?= $fieldClass('kelurahan') ?>"></select>
                                                <?php if ($fieldError('kelurahan') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('kelurahan')) ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-12 d-none" id="wilayahErrorWrap">
                                                <div class="alert alert-danger border-0 rounded-4 mb-0" id="wilayahErrorMessage">
                                                    Data wilayah belum lengkap. Pastikan provinsi, kabupaten/kota, kecamatan, dan kelurahan sudah terisi.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="registration-step-actions">
                                        <button type="button" class="btn btn-registration-secondary" data-step-prev="1">
                                            <i class="fas fa-arrow-left me-2"></i> Kembali
                                        </button>
                                        <button type="button" class="btn btn-registration-secondary btn-registration-outline" data-step-next="3">
                                            Lanjut ke Kontak <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="registration-step-pane" data-step-pane="3">
                                    <div class="registration-section">
                                        <div class="registration-section-heading">
                                            <span class="registration-section-icon"><i class="fas fa-user-shield"></i></span>
                                            <div>
                                                <h3>Penanggung Jawab dan Alamat</h3>
                                                <p>Masukkan kontak utama yang akan dihubungi panitia serta alamat lengkap kontingen.</p>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label for="registrasi_nama_penanggungjawab" class="form-label">Nama Penanggung Jawab</label>
                                                <input type="text" id="registrasi_nama_penanggungjawab" name="nama_penanggungjawab" class="form-control form-control-lg<?= $fieldClass('nama_penanggungjawab') ?>" value="<?= esc((string) old('nama_penanggungjawab'), 'attr') ?>" required>
                                                <?php if ($fieldError('nama_penanggungjawab') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('nama_penanggungjawab')) ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="registrasi_nomor_telepon_penanggungjawab" class="form-label">Nomor Telepon Penanggung Jawab</label>
                                                <input type="text" id="registrasi_nomor_telepon_penanggungjawab" name="nomor_telepon_penanggungjawab" class="form-control form-control-lg<?= $fieldClass('nomor_telepon_penanggungjawab') ?>" value="<?= esc((string) old('nomor_telepon_penanggungjawab'), 'attr') ?>" required>
                                                <?php if ($fieldError('nomor_telepon_penanggungjawab') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('nomor_telepon_penanggungjawab')) ?></div>
                                                <?php endif; ?>
                                                <div class="field-hint">Gunakan nomor aktif yang bisa menerima panggilan atau WhatsApp.</div>
                                            </div>

                                            <div class="col-12">
                                                <label for="registrasi_alamat_lengkap" class="form-label">Alamat Lengkap</label>
                                                <textarea id="registrasi_alamat_lengkap" name="alamat_lengkap" rows="4" class="form-control<?= $fieldClass('alamat_lengkap') ?>" required><?= esc((string) old('alamat_lengkap')) ?></textarea>
                                                <?php if ($fieldError('alamat_lengkap') !== null) : ?>
                                                    <div class="invalid-feedback"><?= esc($fieldError('alamat_lengkap')) ?></div>
                                                <?php endif; ?>
                                                <div class="field-hint">Tulis alamat lengkap untuk kebutuhan administrasi dan verifikasi data.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (! empty($recaptchaEnabled) && ! empty($recaptchaSiteKey)) : ?>
                                        <div class="mt-4">
                                            <div class="g-recaptcha" data-sitekey="<?= esc($recaptchaSiteKey) ?>"></div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row g-3 align-items-end mt-4">
                                        <div class="col-lg-3">
                                            <button type="button" class="btn btn-registration-secondary" data-step-prev="2">
                                                <i class="fas fa-arrow-left me-2"></i> Kembali
                                            </button>
                                        </div>
                                        <div class="col-lg-4">
                                            <a href="<?= base_url('pendaftaran/login') ?>" class="btn btn-registration-secondary">Sudah Punya Akun</a>
                                        </div>
                                        <div class="col-lg-5">
                                            <button type="submit" class="btn btn-registration-primary" id="registrasiSubmit">
                                                Daftarkan Kontingen <i class="fas fa-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        <?php else : ?>
                            <div class="registration-closed">
                                <strong class="d-block mb-2">Pendaftaran kontingen sedang ditutup.</strong>
                                <span>Silakan hubungi panitia atau cek kembali pada jadwal pembukaan pendaftaran berikutnya.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($recaptchaEnabled) && !empty($recaptchaSiteKey)) : ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const jenisKontingen = document.querySelector('[name="jenis_kontingen"]');
        const negara = document.getElementById('negara');
        const provinsi = document.getElementById('provinsi');
        const kabupaten = document.getElementById('kabupaten_kota');
        const kecamatan = document.getElementById('kecamatan');
        const kelurahan = document.getElementById('kelurahan');
        const wilayahDalamNegeri = document.querySelectorAll('.wilayah-dalam-negeri');
        const form = document.getElementById('formRegistrasiKontingen');
        const submitButton = document.getElementById('registrasiSubmit');
        const wilayahErrorWrap = document.getElementById('wilayahErrorWrap');
        const wilayahErrorMessage = document.getElementById('wilayahErrorMessage');
        const wilayahSelects = [provinsi, kabupaten, kecamatan, kelurahan];
        let wilayahLoadFailed = false;

        if (!form || !jenisKontingen || !negara || !provinsi || !kabupaten || !kecamatan || !kelurahan || !submitButton) {
            return;
        }

        const firstInvalidField = form.querySelector('.is-invalid');

        const stepButtons = Array.from(form.querySelectorAll('[data-step-button]'));
        const stepPanes = Array.from(form.querySelectorAll('[data-step-pane]'));
        const nextButtons = Array.from(form.querySelectorAll('[data-step-next]'));
        const prevButtons = Array.from(form.querySelectorAll('[data-step-prev]'));
        const maxStep = stepPanes.length;
        let activeStep = 1;

        const getPane = (step) => form.querySelector(`[data-step-pane="${step}"]`);

        const setActiveStep = (step, focusFirst = true) => {
            activeStep = step;

            stepPanes.forEach((pane) => {
                const paneStep = Number(pane.getAttribute('data-step-pane'));
                pane.classList.toggle('is-active', paneStep === step);
            });

            stepButtons.forEach((button) => {
                const buttonStep = Number(button.getAttribute('data-step-button'));
                button.classList.toggle('is-active', buttonStep === step);
                button.classList.toggle('is-complete', buttonStep < step);
            });

            if (!focusFirst) return;

            const pane = getPane(step);
            const firstField = pane?.querySelector('input, select, textarea');
            if (firstField && typeof firstField.focus === 'function') {
                firstField.focus();
            }
        };

        const isVisibleField = (field) => field.offsetParent !== null && !field.disabled;

        const validateStepFields = (step) => {
            const pane = getPane(step);
            if (!pane) return true;

            const fields = Array.from(pane.querySelectorAll('[required]')).filter(isVisibleField);
            for (const field of fields) {
                if (!field.checkValidity()) {
                    field.reportValidity();
                    return false;
                }
            }

            return true;
        };

        const isDalamNegeri = () => jenisKontingen.value === 'dalam_negeri';

        const hasValidSelection = (select) => Boolean(select?.value && select.options[select.selectedIndex]?.dataset.id);

        const setWilayahMessage = (message = '') => {
            if (!wilayahErrorWrap || !wilayahErrorMessage) return;
            wilayahErrorWrap.classList.toggle('d-none', message === '');
            wilayahErrorMessage.textContent = message;
        };

        const validateWilayah = (showMessage = false) => {
            if (!isDalamNegeri()) {
                submitButton.disabled = false;
                setWilayahMessage('');
                return true;
            }

            if (wilayahLoadFailed) {
                submitButton.disabled = true;
                if (showMessage) {
                    setWilayahMessage('Data wilayah gagal dimuat. Muat ulang halaman atau coba lagi saat koneksi stabil.');
                }
                return false;
            }

            const valid = wilayahSelects.every(hasValidSelection);
            submitButton.disabled = !valid;
            if (showMessage || valid) {
                setWilayahMessage(valid ? '' : 'Lengkapi provinsi, kabupaten/kota, kecamatan, dan kelurahan sebelum mendaftar.');
            }

            return valid;
        };

        const validateStep = (step, showWilayahMessage = true) => {
            if (!validateStepFields(step)) return false;

            if (step === 2) {
                return validateWilayah(showWilayahMessage);
            }

            return true;
        };

        stepButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const targetStep = Number(button.getAttribute('data-step-button'));
                if (!targetStep || targetStep === activeStep) return;

                if (targetStep > activeStep) {
                    for (let step = activeStep; step < targetStep; step += 1) {
                        if (!validateStep(step, true)) {
                            setActiveStep(step);
                            return;
                        }
                    }
                }

                setActiveStep(targetStep);
            });
        });

        nextButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const targetStep = Number(button.getAttribute('data-step-next'));
                if (!targetStep) return;
                if (!validateStep(activeStep, true)) return;
                setActiveStep(targetStep);
            });
        });

        prevButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const targetStep = Number(button.getAttribute('data-step-prev'));
                if (!targetStep) return;
                setActiveStep(targetStep);
            });
        });

        const oldNegara = <?= json_encode(old('negara', 'Indonesia')) ?>;
        const oldProvinsi = <?= json_encode(old('provinsi')) ?>;
        const oldKabupaten = <?= json_encode(old('kabupaten_kota')) ?>;
        const oldKecamatan = <?= json_encode(old('kecamatan')) ?>;
        const oldKelurahan = <?= json_encode(old('kelurahan')) ?>;

        const setOptions = (select, options, selected, placeholder) => {
            select.innerHTML = '';
            select.disabled = false;

            const entries = Object.entries(options);
            if (entries.length === 0) {
                const first = document.createElement('option');
                first.value = '';
                first.textContent = placeholder;
                select.appendChild(first);
                validateWilayah(false);
                return;
            }

            entries.forEach(([label, value], index) => {
                const option = document.createElement('option');
                option.value = label;
                option.dataset.id = value;
                option.textContent = label;
                if ((selected && selected === label) || (!selected && index === 0)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            validateWilayah(false);
        };

        const fetchJson = async (url, targetSelect = null) => {
            if (targetSelect) {
                targetSelect.disabled = true;
                targetSelect.innerHTML = `<option value="">Memuat data...</option>`;
            }
            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) {
                    throw new Error('Gagal memuat data');
                }
                return await response.json();
            } catch (error) {
                if (targetSelect) {
                    targetSelect.innerHTML = `<option value="">Gagal memuat data wilayah</option>`;
                    targetSelect.disabled = false;
                }
                if (targetSelect !== negara) {
                    wilayahLoadFailed = true;
                    validateWilayah(true);
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error('Koneksi terputus. Gagal memuat data wilayah dari server.');
                }
                return {};
            }
        };

        const updateJenisKontingen = () => {
            const dalamNegeri = isDalamNegeri();
            wilayahDalamNegeri.forEach((item) => {
                item.style.display = dalamNegeri ? '' : 'none';
            });
            wilayahSelects.forEach((select) => {
                select.required = dalamNegeri;
            });
            negara.required = !dalamNegeri;
            negara.closest('.col-md-6').style.display = dalamNegeri ? 'none' : '';
            if (dalamNegeri) {
                negara.value = 'Indonesia';
            }
            validateWilayah(false);
        };

        const countries = await fetchJson('<?= base_url('location/countries') ?>', negara);
        setOptions(negara, countries, oldNegara, 'Pilih negara');

        const provinces = await fetchJson('<?= base_url('location/provinces') ?>', provinsi);
        setOptions(provinsi, provinces, oldProvinsi, 'Pilih provinsi');

        const loadRegencies = async (selected = null) => {
            const selectedOption = provinsi.options[provinsi.selectedIndex];
            const id = selectedOption?.dataset.id;
            if (!id) {
                setOptions(kabupaten, {}, null, 'Pilih kabupaten / kota');
                setOptions(kecamatan, {}, null, 'Pilih kecamatan');
                setOptions(kelurahan, {}, null, 'Pilih kelurahan');
                return;
            }
            const items = await fetchJson(`<?= base_url('location/regencies') ?>/` + id, kabupaten);
            setOptions(kabupaten, items, selected, 'Pilih kabupaten / kota');
        };

        const loadDistricts = async (selected = null) => {
            const selectedOption = kabupaten.options[kabupaten.selectedIndex];
            const id = selectedOption?.dataset.id;
            if (!id) {
                setOptions(kecamatan, {}, null, 'Pilih kecamatan');
                setOptions(kelurahan, {}, null, 'Pilih kelurahan');
                return;
            }
            const items = await fetchJson(`<?= base_url('location/districts') ?>/` + id, kecamatan);
            setOptions(kecamatan, items, selected, 'Pilih kecamatan');
        };

        const loadVillages = async (selected = null) => {
            const selectedOption = kecamatan.options[kecamatan.selectedIndex];
            const id = selectedOption?.dataset.id;
            if (!id) {
                setOptions(kelurahan, {}, null, 'Pilih kelurahan');
                return;
            }
            const items = await fetchJson(`<?= base_url('location/villages') ?>/` + id, kelurahan);
            setOptions(kelurahan, items, selected, 'Pilih kelurahan');
        };

        await loadRegencies(oldKabupaten);
        await loadDistricts(oldKecamatan);
        await loadVillages(oldKelurahan);
        updateJenisKontingen();

        jenisKontingen.addEventListener('change', updateJenisKontingen);
        provinsi.addEventListener('change', async () => {
            await loadRegencies();
            await loadDistricts();
            await loadVillages();
            validateWilayah(true);
        });
        kabupaten.addEventListener('change', async () => {
            await loadDistricts();
            await loadVillages();
            validateWilayah(true);
        });
        kecamatan.addEventListener('change', async () => {
            await loadVillages();
            validateWilayah(true);
        });
        kelurahan.addEventListener('change', () => validateWilayah(true));

        if (firstInvalidField) {
            const invalidPane = firstInvalidField.closest('[data-step-pane]');
            if (invalidPane) {
                const invalidStep = Number(invalidPane.getAttribute('data-step-pane'));
                if (invalidStep) {
                    activeStep = invalidStep;
                }
            }
        }

        setActiveStep(activeStep, false);

        if (firstInvalidField) {
            firstInvalidField.focus();
        }

        form.addEventListener('submit', (event) => {
            for (let step = 1; step <= maxStep; step += 1) {
                if (!validateStep(step, true)) {
                    event.preventDefault();
                    setActiveStep(step);
                    return;
                }
            }
        });
    });
</script>

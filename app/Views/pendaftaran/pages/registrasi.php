<?php
$validation = session()->getFlashdata('errors') ?? session('errors') ?? [];
if ($validation === [] && session()->has('validation')) {
    $validation = session('validation')->getErrors();
}

$fieldError = static fn(string $field): ?string => isset($validation[$field]) && $validation[$field] !== '' ? (string) $validation[$field] : null;
$fieldClass = static fn(string $field): string => $fieldError($field) !== null ? ' is-invalid' : '';
?>

<section class="py-5">
    <div class="container py-4">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8 text-center">
                <span class="badge rounded-pill text-bg-danger-subtle text-danger px-3 py-2 mb-3">Registrasi Kontingen</span>
                <h1 class="display-5 fw-bold mb-3">Pendaftaran Kontingen</h1>
                <p class="text-muted lead mb-0">
                    Lengkapi data kontingen Anda dengan benar dan valid untuk melanjutkan proses pendaftaran.
                </p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <?= view('shared_components/notification') ?>

                        <?php if (! empty($perbolehkan_kontingen_mendaftar)) : ?>
                            <form method="post" action="<?= base_url('registrasi') ?>" class="row g-4" id="formRegistrasiKontingen">
                                <?= csrf_field() ?>

                                <div class="col-md-6">
                                    <label for="registrasi_nama_kontingen" class="form-label fw-semibold">Nama Kontingen</label>
                                    <input type="text" id="registrasi_nama_kontingen" name="nama_kontingen" class="form-control form-control-lg rounded-4<?= $fieldClass('nama_kontingen') ?>" value="<?= esc((string) old('nama_kontingen'), 'attr') ?>" required>
                                    <?php if ($fieldError('nama_kontingen') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('nama_kontingen')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="registrasi_email_kontingen" class="form-label fw-semibold">Email Kontingen</label>
                                    <input type="email" id="registrasi_email_kontingen" name="email_kontingen" class="form-control form-control-lg rounded-4<?= $fieldClass('email_kontingen') ?>" value="<?= esc((string) old('email_kontingen'), 'attr') ?>" required>
                                    <?php if ($fieldError('email_kontingen') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('email_kontingen')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="registrasi_password" class="form-label fw-semibold">Password</label>
                                    <input type="password" id="registrasi_password" name="password" class="form-control form-control-lg rounded-4<?= $fieldClass('password') ?>" required>
                                    <?php if ($fieldError('password') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('password')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="registrasi_retype_password" class="form-label fw-semibold">Ulangi Password</label>
                                    <input type="password" id="registrasi_retype_password" name="retype_password" class="form-control form-control-lg rounded-4<?= $fieldClass('retype_password') ?>" required>
                                    <?php if ($fieldError('retype_password') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('retype_password')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="registrasi_jenis_kontingen" class="form-label fw-semibold">Jenis Kontingen</label>
                                    <select name="jenis_kontingen" id="registrasi_jenis_kontingen" class="form-select form-select-lg rounded-4<?= $fieldClass('jenis_kontingen') ?>" required>
                                        <option value="dalam_negeri" <?= old('jenis_kontingen', 'dalam_negeri') === 'dalam_negeri' ? 'selected' : '' ?>>Dalam Negeri</option>
                                        <option value="luar_negeri" <?= old('jenis_kontingen') === 'luar_negeri' ? 'selected' : '' ?>>Luar Negeri</option>
                                    </select>
                                    <?php if ($fieldError('jenis_kontingen') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('jenis_kontingen')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="negara" class="form-label fw-semibold">Negara</label>
                                    <select name="negara" id="negara" class="form-select form-select-lg rounded-4<?= $fieldClass('negara') ?>"></select>
                                    <?php if ($fieldError('negara') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('negara')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 wilayah-dalam-negeri">
                                    <label for="provinsi" class="form-label fw-semibold">Provinsi</label>
                                    <select name="provinsi" id="provinsi" class="form-select form-select-lg rounded-4<?= $fieldClass('provinsi') ?>"></select>
                                    <?php if ($fieldError('provinsi') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('provinsi')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 wilayah-dalam-negeri">
                                    <label for="kabupaten_kota" class="form-label fw-semibold">Kabupaten / Kota</label>
                                    <select name="kabupaten_kota" id="kabupaten_kota" class="form-select form-select-lg rounded-4<?= $fieldClass('kabupaten_kota') ?>"></select>
                                    <?php if ($fieldError('kabupaten_kota') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('kabupaten_kota')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 wilayah-dalam-negeri">
                                    <label for="kecamatan" class="form-label fw-semibold">Kecamatan</label>
                                    <select name="kecamatan" id="kecamatan" class="form-select form-select-lg rounded-4<?= $fieldClass('kecamatan') ?>"></select>
                                    <?php if ($fieldError('kecamatan') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('kecamatan')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 wilayah-dalam-negeri">
                                    <label for="kelurahan" class="form-label fw-semibold">Kelurahan</label>
                                    <select name="kelurahan" id="kelurahan" class="form-select form-select-lg rounded-4<?= $fieldClass('kelurahan') ?>"></select>
                                    <?php if ($fieldError('kelurahan') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('kelurahan')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 d-none" id="wilayahErrorWrap">
                                    <div class="alert alert-danger border-0 rounded-4 mb-0" id="wilayahErrorMessage">
                                        Data wilayah belum lengkap. Pastikan provinsi, kabupaten/kota, kecamatan, dan kelurahan sudah terisi.
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="registrasi_nama_penanggungjawab" class="form-label fw-semibold">Nama Penanggung Jawab</label>
                                    <input type="text" id="registrasi_nama_penanggungjawab" name="nama_penanggungjawab" class="form-control form-control-lg rounded-4<?= $fieldClass('nama_penanggungjawab') ?>" value="<?= esc((string) old('nama_penanggungjawab'), 'attr') ?>" required>
                                    <?php if ($fieldError('nama_penanggungjawab') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('nama_penanggungjawab')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="registrasi_jabatan_penanggungjawab" class="form-label fw-semibold">Jabatan Penanggung Jawab</label>
                                    <input type="text" id="registrasi_jabatan_penanggungjawab" name="jabatan_penanggungjawab" class="form-control form-control-lg rounded-4<?= $fieldClass('jabatan_penanggungjawab') ?>" value="<?= esc((string) old('jabatan_penanggungjawab', 'Manager Kontingen'), 'attr') ?>" required>
                                    <?php if ($fieldError('jabatan_penanggungjawab') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('jabatan_penanggungjawab')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="registrasi_nomor_telepon_penanggungjawab" class="form-label fw-semibold">Nomor Telepon Penanggung Jawab</label>
                                    <input type="text" id="registrasi_nomor_telepon_penanggungjawab" name="nomor_telepon_penanggungjawab" class="form-control form-control-lg rounded-4<?= $fieldClass('nomor_telepon_penanggungjawab') ?>" value="<?= esc((string) old('nomor_telepon_penanggungjawab'), 'attr') ?>" required>
                                    <?php if ($fieldError('nomor_telepon_penanggungjawab') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('nomor_telepon_penanggungjawab')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label for="registrasi_nomor_telepon_kontingen" class="form-label fw-semibold">Nomor Telepon Kontingen</label>
                                    <input type="text" id="registrasi_nomor_telepon_kontingen" name="nomor_telepon_kontingen" class="form-control form-control-lg rounded-4<?= $fieldClass('nomor_telepon_kontingen') ?>" value="<?= esc((string) old('nomor_telepon_kontingen'), 'attr') ?>">
                                    <?php if ($fieldError('nomor_telepon_kontingen') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('nomor_telepon_kontingen')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label for="registrasi_alamat_lengkap" class="form-label fw-semibold">Alamat Lengkap</label>
                                    <textarea id="registrasi_alamat_lengkap" name="alamat_lengkap" rows="4" class="form-control rounded-4<?= $fieldClass('alamat_lengkap') ?>" required><?= esc((string) old('alamat_lengkap')) ?></textarea>
                                    <?php if ($fieldError('alamat_lengkap') !== null) : ?>
                                        <div class="invalid-feedback"><?= esc($fieldError('alamat_lengkap')) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 d-flex flex-wrap gap-3 pt-2">
                                    <?php if (!empty($recaptchaEnabled) && !empty($recaptchaSiteKey)) : ?>
                                        <div class="w-100 mb-2">
                                            <div class="g-recaptcha" data-sitekey="<?= esc($recaptchaSiteKey) ?>"></div>
                                        </div>
                                    <?php endif; ?>

                                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-4" id="registrasiSubmit">Daftarkan Kontingen</button>
                                    <a href="<?= base_url('pendaftaran/login') ?>" class="btn btn-outline-dark btn-lg rounded-pill px-4">Sudah Punya Akun</a>
                                </div>
                            </form>
                        <?php else : ?>
                            <div class="alert alert-secondary border-0 rounded-4 mb-0">
                                Pendaftaran kontingen sedang ditutup.
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
        const firstInvalidField = form.querySelector('.is-invalid');
        let wilayahLoadFailed = false;

        if (!form || !jenisKontingen || !negara || !provinsi || !kabupaten || !kecamatan || !kelurahan || !submitButton) {
            return;
        }

        const oldNegara = <?= json_encode(old('negara', 'Indonesia')) ?>;
        const oldProvinsi = <?= json_encode(old('provinsi')) ?>;
        const oldKabupaten = <?= json_encode(old('kabupaten_kota')) ?>;
        const oldKecamatan = <?= json_encode(old('kecamatan')) ?>;
        const oldKelurahan = <?= json_encode(old('kelurahan')) ?>;

        const setWilayahMessage = (message = '') => {
            if (!wilayahErrorWrap || !wilayahErrorMessage) return;
            wilayahErrorWrap.classList.toggle('d-none', message === '');
            wilayahErrorMessage.textContent = message;
        };

        const isDalamNegeri = () => jenisKontingen.value === 'dalam_negeri';

        const hasValidSelection = (select) => Boolean(select?.value && select.options[select.selectedIndex]?.dataset.id);

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
                if ((selected && selected === label) || (! selected && index === 0)) {
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

        form.addEventListener('submit', (event) => {
            if (!validateWilayah(true)) {
                event.preventDefault();
                if (typeof toastr !== 'undefined') {
                    toastr.error('Lengkapi data wilayah sebelum mendaftar.');
                }
            }
        });

        if (firstInvalidField) {
            firstInvalidField.focus();
        }
    });
</script>

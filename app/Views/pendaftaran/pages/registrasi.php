<section class="py-5">
    <div class="container py-4">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8 text-center">
                <span class="badge rounded-pill text-bg-danger-subtle text-danger px-3 py-2 mb-3">Registrasi Kontingen</span>
                <h1 class="display-5 fw-bold mb-3">Pendaftaran Kontingen</h1>
                <p class="text-muted lead mb-0">
                    Halaman registrasi kontingen sedang disiapkan ulang untuk migrasi penuh ke CodeIgniter 4.
                </p>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <?= view('shared_components/notification') ?>

                        <?php if (! empty($perbolehkan_kontingen_mendaftar)) : ?>
                            <form method="post" action="<?= base_url('registrasi') ?>" class="row g-4">
                                <?= csrf_field() ?>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nama Kontingen</label>
                                    <input type="text" name="nama_kontingen" class="form-control form-control-lg rounded-4" value="<?= old('nama_kontingen') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Kontingen</label>
                                    <input type="email" name="email_kontingen" class="form-control form-control-lg rounded-4" value="<?= old('email_kontingen') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Password</label>
                                    <input type="password" name="password" class="form-control form-control-lg rounded-4" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Ulangi Password</label>
                                    <input type="password" name="retype_password" class="form-control form-control-lg rounded-4" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Jenis Kontingen</label>
                                    <select name="jenis_kontingen" class="form-select form-select-lg rounded-4" required>
                                        <option value="dalam_negeri" <?= old('jenis_kontingen', 'dalam_negeri') === 'dalam_negeri' ? 'selected' : '' ?>>Dalam Negeri</option>
                                        <option value="luar_negeri" <?= old('jenis_kontingen') === 'luar_negeri' ? 'selected' : '' ?>>Luar Negeri</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Negara</label>
                                    <select name="negara" id="negara" class="form-select form-select-lg rounded-4"></select>
                                </div>

                                <div class="col-md-6 wilayah-dalam-negeri">
                                    <label class="form-label fw-semibold">Provinsi</label>
                                    <select name="provinsi" id="provinsi" class="form-select form-select-lg rounded-4"></select>
                                </div>

                                <div class="col-md-6 wilayah-dalam-negeri">
                                    <label class="form-label fw-semibold">Kabupaten / Kota</label>
                                    <select name="kabupaten_kota" id="kabupaten_kota" class="form-select form-select-lg rounded-4"></select>
                                </div>

                                <div class="col-md-6 wilayah-dalam-negeri">
                                    <label class="form-label fw-semibold">Kecamatan</label>
                                    <select name="kecamatan" id="kecamatan" class="form-select form-select-lg rounded-4"></select>
                                </div>

                                <div class="col-md-6 wilayah-dalam-negeri">
                                    <label class="form-label fw-semibold">Kelurahan</label>
                                    <select name="kelurahan" id="kelurahan" class="form-select form-select-lg rounded-4"></select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nama Penanggung Jawab</label>
                                    <input type="text" name="nama_penanggungjawab" class="form-control form-control-lg rounded-4" value="<?= old('nama_penanggungjawab') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Jabatan Penanggung Jawab</label>
                                    <input type="text" name="jabatan_penanggungjawab" class="form-control form-control-lg rounded-4" value="<?= old('jabatan_penanggungjawab', 'Manager Kontingen') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nomor Telepon Penanggung Jawab</label>
                                    <input type="text" name="nomor_telepon_penanggungjawab" class="form-control form-control-lg rounded-4" value="<?= old('nomor_telepon_penanggungjawab') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nomor Telepon Kontingen</label>
                                    <input type="text" name="nomor_telepon_kontingen" class="form-control form-control-lg rounded-4" value="<?= old('nomor_telepon_kontingen') ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Alamat Lengkap</label>
                                    <textarea name="alamat_lengkap" rows="4" class="form-control rounded-4" required><?= old('alamat_lengkap') ?></textarea>
                                </div>

                                <div class="col-12 d-flex flex-wrap gap-3 pt-2">
                                    <?php if (!empty($recaptchaEnabled) && !empty($recaptchaSiteKey)) : ?>
                                        <div class="w-100 mb-2">
                                            <div class="g-recaptcha" data-sitekey="<?= esc($recaptchaSiteKey) ?>"></div>
                                        </div>
                                    <?php endif; ?>

                                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-4">Daftarkan Kontingen</button>
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

        const oldNegara = <?= json_encode(old('negara', 'Indonesia')) ?>;
        const oldProvinsi = <?= json_encode(old('provinsi')) ?>;
        const oldKabupaten = <?= json_encode(old('kabupaten_kota')) ?>;
        const oldKecamatan = <?= json_encode(old('kecamatan')) ?>;
        const oldKelurahan = <?= json_encode(old('kelurahan')) ?>;

        const setOptions = (select, options, selected, placeholder) => {
            select.innerHTML = '';
            const first = document.createElement('option');
            first.value = '';
            first.textContent = placeholder;
            select.appendChild(first);

            Object.entries(options).forEach(([label, value]) => {
                const option = document.createElement('option');
                option.value = label;
                option.dataset.id = value;
                option.textContent = label;
                if (selected && selected === label) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        };

        const fetchJson = async (url) => {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                return {};
            }
            return response.json();
        };

        const updateJenisKontingen = () => {
            const dalamNegeri = jenisKontingen.value === 'dalam_negeri';
            wilayahDalamNegeri.forEach((item) => {
                item.style.display = dalamNegeri ? '' : 'none';
            });
            negara.closest('.col-md-6').style.display = dalamNegeri ? 'none' : '';
            if (dalamNegeri) {
                negara.value = 'Indonesia';
            }
        };

        const countries = await fetchJson('<?= base_url('location/countries') ?>');
        setOptions(negara, countries, oldNegara, 'Pilih negara');

        const provinces = await fetchJson('<?= base_url('location/provinces') ?>');
        setOptions(provinsi, provinces, oldProvinsi, 'Pilih provinsi');

        const loadRegencies = async (selected = null) => {
            const selectedOption = provinsi.options[provinsi.selectedIndex];
            const id = selectedOption?.dataset.id;
            if (!id) {
                setOptions(kabupaten, {}, null, 'Pilih kabupaten / kota');
                return;
            }
            const items = await fetchJson(`<?= base_url('location/regencies') ?>/` + id);
            setOptions(kabupaten, items, selected, 'Pilih kabupaten / kota');
        };

        const loadDistricts = async (selected = null) => {
            const selectedOption = kabupaten.options[kabupaten.selectedIndex];
            const id = selectedOption?.dataset.id;
            if (!id) {
                setOptions(kecamatan, {}, null, 'Pilih kecamatan');
                return;
            }
            const items = await fetchJson(`<?= base_url('location/districts') ?>/` + id);
            setOptions(kecamatan, items, selected, 'Pilih kecamatan');
        };

        const loadVillages = async (selected = null) => {
            const selectedOption = kecamatan.options[kecamatan.selectedIndex];
            const id = selectedOption?.dataset.id;
            if (!id) {
                setOptions(kelurahan, {}, null, 'Pilih kelurahan');
                return;
            }
            const items = await fetchJson(`<?= base_url('location/villages') ?>/` + id);
            setOptions(kelurahan, items, selected, 'Pilih kelurahan');
        };

        await loadRegencies(oldKabupaten);
        await loadDistricts(oldKecamatan);
        await loadVillages(oldKelurahan);
        updateJenisKontingen();

        jenisKontingen.addEventListener('change', updateJenisKontingen);
        provinsi.addEventListener('change', async () => {
            await loadRegencies();
            setOptions(kecamatan, {}, null, 'Pilih kecamatan');
            setOptions(kelurahan, {}, null, 'Pilih kelurahan');
        });
        kabupaten.addEventListener('change', async () => {
            await loadDistricts();
            setOptions(kelurahan, {}, null, 'Pilih kelurahan');
        });
        kecamatan.addEventListener('change', async () => {
            await loadVillages();
        });
    });
</script>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$columns = [
    ['col' => 'A', 'label' => 'Nama Atlet', 'example' => 'BUDI SANTOSO', 'required' => true],
    ['col' => 'B', 'label' => 'Nama Kontingen', 'example' => 'KAB. BANDUNG', 'required' => true],
    ['col' => 'C', 'label' => 'Nama Sekolah', 'example' => 'SMA NEGERI 1', 'required' => false],
    ['col' => 'D', 'label' => 'Tanggal Lahir', 'example' => '15/06/2008', 'required' => false],
    ['col' => 'E', 'label' => 'Umur', 'example' => '17', 'required' => false],
    ['col' => 'F', 'label' => 'Tinggi Badan', 'example' => '170', 'required' => false],
    ['col' => 'G', 'label' => 'Berat Badan', 'example' => '55', 'required' => false],
    ['col' => 'H', 'label' => 'Alamat', 'example' => 'Jl. Merdeka 12', 'required' => false],
    ['col' => 'I', 'label' => 'Jenis Kelamin', 'example' => 'Laki-laki / Perempuan', 'required' => true],
    ['col' => 'J', 'label' => 'Kategori Usia', 'example' => 'Remaja / Dewasa', 'required' => true],
    ['col' => 'K', 'label' => 'Jenis Kategori', 'example' => 'Tanding / Tunggal / Ganda / Beregu', 'required' => true],
    ['col' => 'L', 'label' => 'Kelas / Nama Seni', 'example' => 'Kelas A / Tunggal Putra', 'required' => true],
    ['col' => 'O', 'label' => 'NIK', 'example' => '320101...', 'required' => false],
    ['col' => 'P', 'label' => 'No. Kartu Keluarga', 'example' => '320101...', 'required' => false],
];
?>
<div class="row g-4">
    <div class="col-12">
        <section class="admin-card border-0 shadow-sm" style="background:linear-gradient(135deg, var(--admin-surface-soft) 0%, var(--admin-accent-soft) 100%);">
            <div class="card-body p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between gap-4 align-items-start">
                <div>
                    <p class="eyebrow mb-2">Migrasi Data</p>
                    <h2 class="h3 section-title mb-2">Import Data Excel</h2>
                    <p class="muted-copy mb-0">Unggah file Excel atlet dan sistem akan memvalidasi isi file terlebih dahulu sebelum data disimpan ke database.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge text-bg-light border">.xlsx</span>
                    <span class="badge text-bg-light border">.xls</span>
                    <span class="badge text-bg-light border">.csv</span>
                    <span class="badge text-bg-dark">Maks 4 MB</span>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12 col-xl-5">
        <section class="admin-card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 rounded-4 p-4 text-white mb-4" style="background:linear-gradient(135deg, var(--admin-accent) 0%, var(--admin-accent-dark) 100%);">
                    <div>
                        <p class="eyebrow text-white-50 mb-2">Template Excel</p>
                        <h3 class="h5 mb-2">Gunakan format resmi</h3>
                        <p class="mb-0 small text-white-50">Urutan kolom harus sama dengan template agar validasi bisa berjalan akurat.</p>
                    </div>
                    <a href="<?= base_url('admin/super/import-excel-data/download-template') ?>" class="btn btn-light rounded-pill">Unduh Template</a>
                </div>

                <h3 class="h6 mb-3">Cara Penggunaan</h3>
                <div class="d-grid gap-3 mb-4">
                    <div class="d-flex gap-3"><span class="badge rounded-pill text-bg-dark align-self-start">1</span><div>Unduh template, lalu isi data atlet sesuai struktur kolom yang tersedia.</div></div>
                    <div class="d-flex gap-3"><span class="badge rounded-pill text-bg-dark align-self-start">2</span><div>Pastikan kategori usia, kelas tanding, dan sub kategori seni sudah tersedia di sistem.</div></div>
                    <div class="d-flex gap-3"><span class="badge rounded-pill text-bg-dark align-self-start">3</span><div>Unggah file Excel untuk mendapatkan pratinjau hasil validasi.</div></div>
                    <div class="d-flex gap-3"><span class="badge rounded-pill text-bg-dark align-self-start">4</span><div>Jika data valid, konfirmasi import untuk menyimpan ke database.</div></div>
                </div>

                <h3 class="h6 mb-3">Struktur Kolom Excel</h3>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kolom</th>
                                <th>Field</th>
                                <th>Contoh</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($columns as $column): ?>
                                <tr>
                                    <td><span class="badge text-bg-warning"><?= esc($column['col']) ?></span></td>
                                    <td><?= esc($column['label']) ?></td>
                                    <td class="text-muted small"><?= esc($column['example']) ?></td>
                                    <td class="text-end"><?= $column['required'] ? '<span class="badge text-bg-danger">Wajib</span>' : '<span class="badge text-bg-light border">Opsional</span>' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12 col-xl-7">
        <section class="admin-card h-100">
            <div class="card-body p-4">
                <div class="mb-4">
                    <p class="eyebrow mb-2">Upload</p>
                    <h3 class="h5 mb-2">Unggah File Excel</h3>
                    <p class="muted-copy mb-0">Sistem akan memvalidasi file sebelum menyimpan data ke database.</p>
                </div>
                <form action="<?= base_url('admin/super/import-excel-data/preview') ?>" method="post" enctype="multipart/form-data" id="formImportExcel">
                    <?= csrf_field() ?>
                    <label for="file_excel" class="border border-2 border-secondary-subtle rounded-4 p-5 w-100 text-center d-block mb-3" id="dropzone" style="cursor:pointer;background:var(--admin-bg-soft);">
                        <div class="display-6 mb-3">+</div>
                        <div class="fw-semibold mb-1" id="fileNameLabel">Tarik & lepaskan file di sini</div>
                        <div class="text-muted small" id="fileMetaLabel">atau klik untuk memilih file dari komputer</div>
                        <input type="file" class="d-none" id="file_excel" name="file_excel" accept=".xlsx,.xls,.csv" required>
                    </label>
                    <div class="alert alert-danger d-none" id="fileError"></div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" id="btnReset" disabled>Hapus</button>
                        <button type="submit" class="btn btn-admin-brand rounded-pill" id="btnSubmit" disabled>Pratinjau Data</button>
                    </div>
                </form>
                <hr class="my-4">
                <div class="small text-muted">
                    <strong>Tips:</strong> baris pertama dianggap header, kategori seni ganda/beregu harus berkelipatan jumlah anggota per kelompok, dan data yang sama dengan database akan dilewati saat proses insert.
                </div>
            </div>
        </section>
    </div>
</div>

<script>
(() => {
    const fileInput = document.getElementById('file_excel');
    const fileNameLabel = document.getElementById('fileNameLabel');
    const fileMetaLabel = document.getElementById('fileMetaLabel');
    const errorBox = document.getElementById('fileError');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnReset = document.getElementById('btnReset');
    const maxBytes = 4 * 1024 * 1024;
    const extAllowed = ['xlsx', 'xls', 'csv'];

    function resetState() {
        fileInput.value = '';
        fileNameLabel.textContent = 'Tarik & lepaskan file di sini';
        fileMetaLabel.textContent = 'atau klik untuk memilih file dari komputer';
        errorBox.classList.add('d-none');
        errorBox.textContent = '';
        btnSubmit.disabled = true;
        btnReset.disabled = true;
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    }

    function showError(message) {
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
        btnSubmit.disabled = true;
    }

    function validate(file) {
        if (!file) return false;
        const ext = file.name.split('.').pop().toLowerCase();
        if (!extAllowed.includes(ext)) {
            showError('Gunakan file .xlsx, .xls, atau .csv.');
            return false;
        }
        if (file.size > maxBytes) {
            showError('Ukuran file melebihi 4 MB.');
            return false;
        }
        errorBox.classList.add('d-none');
        return true;
    }

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!validate(file)) return;
        fileNameLabel.textContent = file.name;
        fileMetaLabel.textContent = formatSize(file.size) + ' - siap dipratinjau';
        btnSubmit.disabled = false;
        btnReset.disabled = false;
    });

    btnReset.addEventListener('click', resetState);
    resetState();
})();
</script>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Hapus Peserta Per Kategori Usia</h6>
        <p class="muted-copy small mb-0">Pilih jenis peserta dan kategori usia, lalu sistem akan menampilkan preview data yang terdampak sebelum penghapusan dijalankan.</p>
    </div>
    <div class="card-body px-0">
        <form id="formHapusPeserta" method="post" action="<?= base_url('admin/super/operasi-basis-data/hapus-peserta-berdasarkan-kategori-usia') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label d-block">Jenis Peserta</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_peserta" id="jenisTanding" value="tanding" checked>
                    <label class="form-check-label" for="jenisTanding">Tanding</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="jenis_peserta" id="jenisSeni" value="seni">
                    <label class="form-check-label" for="jenisSeni">Seni</label>
                </div>
            </div>

            <div class="admin-table-wrap mb-4">
                <div class="table-shell admin-table-scroller">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 64px;">Pilih</th>
                                <th>Kategori Usia</th>
                                <th>Jenis Kelamin</th>
                                <th>Rentang Umur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($kategoriUsiaRows ?? []) as $row) : ?>
                                <tr>
                                    <td>
                                        <input class="form-check-input" type="checkbox" name="id_kategori_usia[]" value="<?= esc((string) ($row->id_kategori_usia ?? 0)) ?>">
                                    </td>
                                    <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                                    <td><?= esc((string) ($row->jenis_kelamin ?? '-')) ?></td>
                                    <td><?= esc((string) ($row->min_umur ?? '-')) ?> - <?= esc((string) ($row->max_umur ?? '-')) ?> tahun</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger" onclick="previewHapusPeserta()">Preview Penghapusan</button>
                <button type="submit" class="btn btn-danger" onclick="return submitHapusPeserta(event)">Hapus Peserta</button>
                <a class="btn btn-outline-dark" href="<?= base_url('admin/super/operasi-basis-data') ?>">Kembali</a>
            </div>
        </form>

        <div class="border rounded-3 p-3 mt-4">
            <h6 class="mb-1">Hasil Preview</h6>
            <p class="muted-copy small mb-3">Preview akan muncul di sini setelah tombol preview dijalankan.</p>
            <pre id="previewResult" class="small mb-0" style="white-space: pre-wrap;">(belum ada preview)</pre>
        </div>
    </div>
</div>

<script>
async function getPreviewData() {
    const form = document.getElementById('formHapusPeserta');
    const resultBox = document.getElementById('previewResult');
    const formData = new FormData(form);
    const selected = form.querySelectorAll('input[name="id_kategori_usia[]"]:checked');

    if (selected.length < 1) {
        window.alert('Pilih minimal 1 kategori usia.');
        return null;
    }

    resultBox.textContent = 'loading...';

    const response = await fetch('<?= base_url('admin/super/operasi-basis-data/preview-hapus-peserta-berdasarkan-kategori-usia') ?>', {
        method: 'POST',
        body: new URLSearchParams(formData)
    });

    const data = await response.json();
    resultBox.textContent = JSON.stringify(data, null, 2);
    return data;
}

async function previewHapusPeserta() {
    await getPreviewData();
}

async function submitHapusPeserta(event) {
    event.preventDefault();

    const data = await getPreviewData();
    if (!data || data.status !== true) {
        return false;
    }

    const result = await Swal.fire({
        title: 'Hapus Peserta?',
        html: 'Data peserta sesuai kategori usia terpilih akan dihapus permanen.<br>Ketik <strong>hapuspeserta</strong> untuk melanjutkan.',
        input: 'text',
        inputPlaceholder: 'hapuspeserta',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6b7280',
        preConfirm: (val) => {
            if (val !== 'hapuspeserta') {
                Swal.showValidationMessage('Kata kunci tidak sesuai. Ketik persis: hapuspeserta');
            }
        }
    });

    if (!result.isConfirmed) {
        return false;
    }

    document.getElementById('formHapusPeserta').submit();
    return false;
}
</script>
<?= $this->endSection() ?>

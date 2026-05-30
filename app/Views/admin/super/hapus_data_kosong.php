<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Hapus Data Kosong</h6>
        <p class="muted-copy small mb-0">Membersihkan data kontingen tanpa pendaftar dan pendaftar yang belum punya peserta tanding/seni.</p>
    </div>
    <div class="card-body px-0">
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="muted-copy small">Kontingen kosong</div>
                    <div class="h4 mb-0"><?= esc((string) (($preview['kontingen_kosong'] ?? 0))) ?></div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="border rounded-3 p-3 h-100">
                    <div class="muted-copy small">Pendaftar kosong</div>
                    <div class="h4 mb-0"><?= esc((string) (($preview['pendaftar_kosong'] ?? 0))) ?></div>
                </div>
            </div>
        </div>

        <form method="post" action="<?= base_url('admin/super/operasi-basis-data/proses-hapus-data-kosong') ?>" class="row g-2 align-items-end">
            <?= csrf_field() ?>
            <div class="col-12 col-lg-6">
                <label class="form-label">Mode</label>
                <select class="form-select" name="mode" required>
                    <option value="" selected disabled>-- pilih mode --</option>
                    <option value="kontingen_kosong">Hapus kontingen kosong</option>
                    <option value="pendaftar_kosong">Hapus pendaftar kosong</option>
                    <option value="semua">Hapus semuanya</option>
                </select>
            </div>
            <div class="col-12 col-lg-6 d-flex gap-2">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Jalankan penghapusan? Pastikan sudah backup.')">Proses Hapus</button>
                <a class="btn btn-outline-dark" href="<?= base_url('admin/super/operasi-basis-data') ?>">Kembali</a>
            </div>
        </form>

        <hr class="my-4">

        <div class="border rounded-3 p-3">
            <h6 class="mb-1">Preview via AJAX</h6>
            <p class="muted-copy small mb-3">Untuk parity dengan CI3, endpoint preview tersedia dalam format JSON.</p>
            <a class="btn btn-outline-secondary" href="#" onclick="return fetchPreview()">Ambil Preview</a>
            <pre class="mt-3 small" id="previewBox" style="white-space: pre-wrap;">(belum diambil)</pre>
        </div>
    </div>
</div>

<script>
async function fetchPreview() {
    const box = document.getElementById('previewBox');
    box.textContent = 'loading...';

    const res = await fetch('<?= base_url('admin/super/operasi-basis-data/preview-hapus-data-kosong') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
        },
        body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
    });

    box.textContent = await res.text();
    return false;
}
</script>
<?= $this->endSection() ?>

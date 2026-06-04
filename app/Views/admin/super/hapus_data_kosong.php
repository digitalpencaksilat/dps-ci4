<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$kontingenKosong = (int) ($preview['kontingen_kosong'] ?? 0);
$pendaftarKosong = (int) ($preview['pendaftar_kosong'] ?? 0);
$totalKosong = $kontingenKosong + $pendaftarKosong;
?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <p class="eyebrow mb-1">Operasi Basis Data</p>
                <h6 class="card-title mb-1">Hapus Data Kosong</h6>
                <p class="muted-copy small mb-0">Membersihkan kontingen tanpa pendaftar dan pendaftar yang belum punya peserta tanding/seni.</p>
            </div>
            <a class="btn btn-outline-dark align-self-start" href="<?= base_url('admin/super/operasi-basis-data') ?>">Kembali</a>
        </div>
    </div>

    <div class="card-body px-0">
        <div class="alert alert-warning border-0 rounded-3 mb-4">
            <div class="fw-semibold mb-1">Perhatian sebelum menghapus</div>
            <div class="small">Data akan dihapus permanen. Jalankan backup database terlebih dahulu jika data ini masih perlu diaudit.</div>
        </div>

        <div class="row g-3 mb-4" id="emptyDataPreviewCards">
            <div class="col-12 col-md-4">
                <div class="placeholder-stat h-100 border-danger-subtle">
                    <div class="small muted-copy">Kontingen kosong</div>
                    <div class="h3 mb-1 text-danger" id="previewKontingenKosong"><?= esc((string) $kontingenKosong) ?></div>
                    <div class="small muted-copy">Kontingen tanpa pendaftar sama sekali.</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="placeholder-stat h-100 border-danger-subtle">
                    <div class="small muted-copy">Pendaftar kosong</div>
                    <div class="h3 mb-1 text-danger" id="previewPendaftarKosong"><?= esc((string) $pendaftarKosong) ?></div>
                    <div class="small muted-copy">Pendaftar tanpa peserta tanding maupun seni.</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="placeholder-stat h-100">
                    <div class="small muted-copy">Total target</div>
                    <div class="h3 mb-1" id="previewTotalKosong"><?= esc((string) $totalKosong) ?></div>
                    <div class="small muted-copy">Total data kosong yang bisa dibersihkan.</div>
                </div>
            </div>
        </div>

        <form id="formHapusDataKosong" method="post" action="<?= base_url('admin/super/operasi-basis-data/proses-hapus-data-kosong') ?>" class="row g-3 align-items-end">
            <?= csrf_field() ?>
            <div class="col-12 col-lg-6">
                <label class="form-label">Pilih data yang ingin dihapus</label>
                <select class="form-select" name="mode" id="hapusDataKosongMode" required>
                    <option value="semua" selected>Hapus semua data kosong</option>
                    <option value="kontingen_kosong">Hanya kontingen kosong</option>
                    <option value="pendaftar_kosong">Hanya pendaftar kosong</option>
                </select>
            </div>
            <div class="col-12 col-lg-6 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger" onclick="return refreshHapusDataKosongPreview()">Refresh Preview</button>
                <button type="submit" class="btn btn-danger" onclick="return confirmHapusDataKosong(event)">Preview & Hapus</button>
            </div>
        </form>

        <div class="border rounded-3 p-3 mt-4 bg-light-subtle" id="emptyDataPreviewPanel">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
                <div>
                    <h6 class="mb-1">Ringkasan Preview</h6>
                    <p class="muted-copy small mb-0" id="previewSummaryText">Pilih mode lalu klik “Preview & Hapus”. Sistem akan menghitung ulang target sebelum konfirmasi final.</p>
                </div>
                <span class="badge text-bg-secondary align-self-start" id="previewStatusBadge">Preview awal halaman</span>
            </div>
            <div class="row g-2 small">
                <div class="col-12 col-md-4"><div class="border rounded-3 p-3 bg-white"><div class="muted-copy">Mode aktif</div><div class="fw-semibold" id="previewModeLabel">Hapus semua data kosong</div></div></div>
                <div class="col-12 col-md-4"><div class="border rounded-3 p-3 bg-white"><div class="muted-copy">Target sesuai mode</div><div class="fw-semibold text-danger" id="previewTargetCount"><?= esc((string) $totalKosong) ?> data</div></div></div>
                <div class="col-12 col-md-4"><div class="border rounded-3 p-3 bg-white"><div class="muted-copy">Status</div><div class="fw-semibold" id="previewSafeStatus"><?= $totalKosong > 0 ? 'Siap diproses setelah konfirmasi' : 'Tidak ada data kosong' ?></div></div></div>
            </div>
        </div>
    </div>
</div>

<script>
const hapusDataKosongState = {
    kontingen_kosong: <?= json_encode($kontingenKosong) ?>,
    pendaftar_kosong: <?= json_encode($pendaftarKosong) ?>
};

function currentHapusDataKosongTarget() {
    const mode = document.getElementById('hapusDataKosongMode').value;
    if (mode === 'kontingen_kosong') return hapusDataKosongState.kontingen_kosong || 0;
    if (mode === 'pendaftar_kosong') return hapusDataKosongState.pendaftar_kosong || 0;
    return (hapusDataKosongState.kontingen_kosong || 0) + (hapusDataKosongState.pendaftar_kosong || 0);
}

function modeLabel(mode) {
    if (mode === 'kontingen_kosong') return 'Hanya kontingen kosong';
    if (mode === 'pendaftar_kosong') return 'Hanya pendaftar kosong';
    return 'Hapus semua data kosong';
}

function renderHapusDataKosongPreview(statusText = 'Preview terbaru') {
    const mode = document.getElementById('hapusDataKosongMode').value;
    const target = currentHapusDataKosongTarget();
    document.getElementById('previewKontingenKosong').textContent = hapusDataKosongState.kontingen_kosong || 0;
    document.getElementById('previewPendaftarKosong').textContent = hapusDataKosongState.pendaftar_kosong || 0;
    document.getElementById('previewTotalKosong').textContent = (hapusDataKosongState.kontingen_kosong || 0) + (hapusDataKosongState.pendaftar_kosong || 0);
    document.getElementById('previewModeLabel').textContent = modeLabel(mode);
    document.getElementById('previewTargetCount').textContent = `${target} data`;
    document.getElementById('previewSafeStatus').textContent = target > 0 ? 'Siap diproses setelah konfirmasi' : 'Tidak ada data pada mode ini';
    document.getElementById('previewStatusBadge').textContent = statusText;
    document.getElementById('previewSummaryText').textContent = target > 0
        ? `Mode “${modeLabel(mode)}” akan menghapus ${target} data setelah konfirmasi final.`
        : `Mode “${modeLabel(mode)}” tidak memiliki target penghapusan saat ini.`;
}

async function refreshHapusDataKosongPreview() {
    document.getElementById('previewStatusBadge').textContent = 'Mengambil preview...';
    try {
        const res = await fetch('<?= base_url('admin/super/operasi-basis-data/preview-hapus-data-kosong') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
            body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
        });
        const json = await res.json();
        const data = json.data || {};
        hapusDataKosongState.kontingen_kosong = Number(data.kontingen_kosong || 0);
        hapusDataKosongState.pendaftar_kosong = Number(data.pendaftar_kosong || 0);
        renderHapusDataKosongPreview('Preview baru diambil');
    } catch (error) {
        document.getElementById('previewStatusBadge').textContent = 'Preview gagal';
        Swal.fire('Gagal', 'Preview gagal diambil. Cek koneksi atau refresh halaman.', 'error');
    }
    return false;
}

async function confirmHapusDataKosong(event) {
    event.preventDefault();
    await refreshHapusDataKosongPreview();
    const target = currentHapusDataKosongTarget();
    if (target < 1) {
        Swal.fire('Tidak ada data', 'Tidak ada data kosong pada mode yang dipilih.', 'info');
        return false;
    }

    const result = await Swal.fire({
        title: 'Konfirmasi Hapus Data Kosong',
        html: `Mode: <strong>${modeLabel(document.getElementById('hapusDataKosongMode').value)}</strong><br>Target terdeteksi: <strong>${target} data</strong><br><br>Ketik <strong>hapusdatakosong</strong> untuk melanjutkan.`,
        input: 'text',
        inputPlaceholder: 'hapusdatakosong',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus Sekarang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6b7280',
        preConfirm: (val) => {
            if (val !== 'hapusdatakosong') Swal.showValidationMessage('Kata kunci tidak sesuai. Ketik persis: hapusdatakosong');
        }
    });

    if (result.isConfirmed) document.getElementById('formHapusDataKosong').submit();
    return false;
}

document.getElementById('hapusDataKosongMode').addEventListener('change', () => renderHapusDataKosongPreview('Mode diubah'));
renderHapusDataKosongPreview('Preview awal halaman');
</script>
<?= $this->endSection() ?>

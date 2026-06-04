<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$dryRun = ! empty($dry_run);
$changesFound = (int) ($changes_found ?? 0);
$results = is_array($sync_results ?? null) ? $sync_results : [];
$tableChanges = 0;
$columnChanges = 0;
$errorChanges = 0;
foreach ($results as $row) {
    if (($row['type'] ?? '') === 'table') {
        $tableChanges++;
    } elseif (($row['type'] ?? '') === 'column') {
        $columnChanges++;
    }
    if (($row['status'] ?? '') === 'ERROR') {
        $errorChanges++;
    }
}
?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <p class="eyebrow mb-1">Utilitas Sistem &rsaquo; Basis Data</p>
                <h6 class="card-title mb-1">Sinkronisasi Basis Data</h6>
                <p class="muted-copy small mb-0">Menyamakan struktur database aktif dengan file master <code>public/db/db_structure_dps.sql</code>. Mode awal selalu simulasi, mengikuti flow legacy CI3.</p>
            </div>
            <a href="<?= base_url('admin/super/operasi-basis-data') ?>" class="btn btn-outline-dark align-self-start">Kembali</a>
        </div>
    </div>

    <div class="card-body px-0">
        <div class="alert <?= $dryRun ? 'alert-warning' : ($errorChanges > 0 ? 'alert-danger' : 'alert-success') ?> border-0 rounded-3 mb-4">
            <div class="fw-semibold mb-1">
                <?= $dryRun ? 'MODE SIMULASI — belum ada perubahan database' : ($errorChanges > 0 ? 'MODE LIVE — sebagian perubahan gagal' : 'MODE LIVE — perubahan sudah diterapkan') ?>
            </div>
            <div class="small">
                <?php if ($dryRun) : ?>
                    Sistem hanya membandingkan struktur database aktif dengan file SQL master. Periksa rencana perubahan, lalu eksekusi jika sudah yakin dan backup sudah tersedia.
                <?php elseif ($errorChanges > 0) : ?>
                    Sinkronisasi sudah dijalankan, tetapi ada error pada sebagian item. Cek kolom pesan untuk detail teknis.
                <?php else : ?>
                    Perubahan struktur yang terdeteksi berhasil diproses ke database aktif.
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-3">
                <div class="placeholder-stat h-100">
                    <div class="small muted-copy">Database aktif</div>
                    <div class="h5 mb-0 text-break"><?= esc((string) ($database_name ?? '-')) ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="placeholder-stat h-100">
                    <div class="small muted-copy">Mode</div>
                    <div class="h5 mb-0"><?= $dryRun ? 'Simulasi' : 'Live' ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="placeholder-stat h-100">
                    <div class="small muted-copy">Total perubahan</div>
                    <div class="h5 mb-0"><?= esc((string) $changesFound) ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="placeholder-stat h-100">
                    <div class="small muted-copy">Tabel baru</div>
                    <div class="h5 mb-0"><?= esc((string) $tableChanges) ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="placeholder-stat h-100">
                    <div class="small muted-copy">Kolom baru</div>
                    <div class="h5 mb-0"><?= esc((string) $columnChanges) ?></div>
                </div>
            </div>
        </div>

        <?php if ($changesFound > 0) : ?>
            <div class="border rounded-3 p-3 bg-light-subtle mb-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <h6 class="mb-1"><?= $dryRun ? 'Rencana Perubahan Struktur' : 'Perubahan yang Diproses' ?></h6>
                        <p class="muted-copy small mb-0">
                            <?= $dryRun ? 'Ditemukan perubahan yang bisa diterapkan. Eksekusi akan membuat tabel/kolom yang belum ada tanpa menghapus data lama.' : 'Daftar berikut adalah hasil eksekusi sinkronisasi database.' ?>
                        </p>
                    </div>
                    <?php if ($dryRun) : ?>
                        <button type="button" class="btn btn-danger rounded-pill px-4" onclick="return confirmExecuteDbSync()">
                            Eksekusi Perubahan Sekarang
                        </button>
                    <?php else : ?>
                        <a href="<?= base_url('utilities/db-sync') ?>" class="btn btn-outline-secondary rounded-pill px-4">Jalankan Simulasi Ulang</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="border rounded-3 p-4 bg-light-subtle mb-4 text-center">
                <div class="h3 mb-2">✅</div>
                <h6 class="mb-1">Database Sudah Tersinkronisasi</h6>
                <p class="muted-copy small mb-0">Tidak ada perbedaan struktur antara database aktif dan file SQL master. Tidak ada perubahan yang diperlukan.</p>
            </div>
        <?php endif; ?>

        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 110px;">Tipe</th>
                            <th>Target</th>
                            <th style="width: 140px;">Status</th>
                            <th>Pesan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($results === []) : ?>
                            <tr>
                                <td colspan="4" class="text-center muted-copy py-4">Tidak ada perubahan struktur yang terdeteksi.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($results as $row) : ?>
                                <?php
                                $type = (string) ($row['type'] ?? '-');
                                $status = (string) ($row['status'] ?? '-');
                                $badge = 'bg-secondary';
                                if ($status === 'PLAN') {
                                    $badge = 'bg-warning text-dark';
                                } elseif ($status === 'DONE') {
                                    $badge = 'bg-success';
                                } elseif ($status === 'ERROR') {
                                    $badge = 'bg-danger';
                                }
                                $target = (string) ($row['name'] ?? '-');
                                if (! empty($row['table'])) {
                                    $target = (string) $row['table'] . '.' . $target;
                                }
                                ?>
                                <tr>
                                    <td><span class="badge text-bg-light border"><?= $type === 'table' ? 'Tabel' : ($type === 'column' ? 'Kolom' : esc($type)) ?></span></td>
                                    <td><code><?= esc($target) ?></code></td>
                                    <td><span class="badge <?= $badge ?>"><?= esc($status) ?></span></td>
                                    <td class="small"><?= esc((string) ($row['message'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="<?= base_url('utilities/db-sync') ?>" class="btn btn-outline-secondary">Jalankan Simulasi</a>
            <a href="<?= base_url('admin/super/operasi-basis-data') ?>" class="btn btn-outline-dark">Kembali ke Operasi Basis Data</a>
        </div>
    </div>
</div>

<script>
function confirmExecuteDbSync() {
    Swal.fire({
        title: 'Konfirmasi Eksekusi',
        html: 'Sebanyak <strong><?= esc((string) $changesFound) ?> perubahan</strong> akan diterapkan langsung ke database <strong><?= esc((string) ($database_name ?? '-')) ?></strong>.<br><br>Pastikan backup database sudah dibuat. Operasi ini <strong>tidak dapat dibatalkan</strong>.<br><br>Ketik <strong>SINKRONKAN DATABASE</strong> untuk melanjutkan.',
        input: 'text',
        inputPlaceholder: 'SINKRONKAN DATABASE',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Eksekusi Sekarang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6b7280',
        preConfirm: (val) => {
            if (val !== 'SINKRONKAN DATABASE') {
                Swal.showValidationMessage('Kata kunci tidak sesuai. Ketik persis: SINKRONKAN DATABASE');
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= base_url('utilities/db-sync?run=true') ?>';
        }
    });
    return false;
}
</script>
<?= $this->endSection() ?>

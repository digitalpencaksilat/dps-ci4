<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Sinkronisasi Basis Data</h6>
        <p class="muted-copy small mb-0">Mode default adalah simulasi agar struktur basis data bisa dicek dulu sebelum perubahan dijalankan.</p>
    </div>
    <div class="card-body px-0">
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="muted-copy small">Database aktif</div>
                    <div class="h5 mb-0"><?= esc((string) ($database_name ?? '-')) ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="muted-copy small">Mode</div>
                    <div class="h5 mb-0"><?= ! empty($dry_run) ? 'Dry run / simulasi' : 'Eksekusi langsung' ?></div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="muted-copy small">Perubahan terdeteksi</div>
                    <div class="h5 mb-0"><?= esc((string) ($changes_found ?? 0)) ?></div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="<?= base_url('utilities/db-sync') ?>" class="btn btn-outline-secondary">Jalankan Simulasi</a>
            <a href="<?= base_url('utilities/db-sync?run=true') ?>" class="btn btn-danger" onclick="return confirm('Jalankan sinkronisasi database sungguhan? Pastikan backup sudah dilakukan.')">Jalankan Sinkronisasi</a>
            <a href="<?= base_url('admin/super/operasi-basis-data') ?>" class="btn btn-outline-dark">Kembali ke Operasi Basis Data</a>
        </div>

        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Pesan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sync_results)) : ?>
                            <tr>
                                <td colspan="4" class="text-center muted-copy py-4">Tidak ada perubahan struktur yang terdeteksi.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($sync_results as $row) : ?>
                                <?php
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
                                    <td><?= esc((string) ($row['type'] ?? '-')) ?></td>
                                    <td><?= esc($target) ?></td>
                                    <td><span class="badge <?= $badge ?>"><?= esc($status) ?></span></td>
                                    <td><?= esc((string) ($row['message'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

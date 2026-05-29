<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Operasi Basis Data</h6>
        <p class="muted-copy small mb-0">Panel inspeksi aman untuk data pembuatan jadwal. Operasi destruktif belum dibuka agar migrasi tidak merusak data pertandingan.</p>
    </div>
    <div class="card-body px-0">
        <div class="row g-3 mb-4">
            <?php foreach (($stats ?? []) as $table => $total) : ?>
                <div class="col-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="muted-copy small"><?= esc((string) $table) ?></div>
                        <div class="h4 mb-0"><?= esc((string) $total) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="border rounded-3 p-3 mb-4">
            <h6 class="mb-1">Operasi Risiko Tinggi</h6>
            <p class="muted-copy small mb-3">Gunakan hanya jika kamu benar-benar yakin. Aksi ini akan menghapus seluruh jadwal (tanding & seni) beserta detailnya.</p>

            <form method="post" action="<?= base_url('admin/super/operasi-basis-data/reset-seluruh-jadwal') ?>" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-12 col-lg-6">
                    <input name="confirm" class="form-control" placeholder="Ketik: RESET JADWAL" autocomplete="off" required>
                </div>
                <div class="col-12 col-lg-6 d-flex gap-2">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin reset seluruh jadwal? Ini tidak bisa dibatalkan.')">Reset Seluruh Jadwal</button>
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table align-middle mb-0">
                    <thead><tr><th>Pemeriksaan</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach (($checks ?? []) as $check) : ?>
                            <?php $total = (int) ($check['total'] ?? 0); ?>
                            <tr>
                                <td><?= esc($check['label'] ?? '-') ?></td>
                                <td><?= esc((string) $total) ?></td>
                                <td><span class="badge <?= $total === 0 ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $total === 0 ? 'Aman' : 'Perlu dicek' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

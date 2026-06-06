<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Generate Bagan Seni Battle dari Jadwal</h6>
        <p class="muted-copy small mb-0">Centang kompetisi battle yang akan di-generate ulang bagannya dari data battle existing.</p>
    </div>
    <div class="card-body px-0">
        <form method="post" action="<?= base_url('admin/super/generate-bagan-seni-battle-dari-jadwal') ?>">
            <?= csrf_field() ?>
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table align-middle mb-0">
                    <thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.js-pilih-seni').forEach(cb => cb.checked = this.checked)"></th><th>Kategori</th><th>Pool</th><th>Sistem</th><th>Bagan</th><th>Battle</th><th>Terjadwal</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach (($rows ?? []) as $row) : ?>
                            <?php $complete = (int) ($row->jumlah_battle ?? 0) > 0 && (int) ($row->jumlah_battle ?? 0) === (int) ($row->jumlah_terjadwal ?? 0); ?>
                            <tr>
                                <td><input class="js-pilih-seni" type="checkbox" name="id_kompetisi_seni[]" value="<?= esc((string) $row->id_kompetisi_seni) ?>" <?= (int) ($row->jumlah_battle ?? 0) > 0 ? '' : 'disabled' ?>></td>
                                <td><?= esc(trim(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '') . ' ' . ($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-'))) ?></td>
                                <td><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                                <td><?= esc($row->sistem_penampilan ?? '-') ?></td>
                                <td><?= empty($row->bagan_battle_seni) ? 'Belum tersedia' : 'Tersedia' ?></td>
                                <td><?= esc((string) ($row->jumlah_battle ?? 0)) ?></td>
                                <td><?= esc((string) ($row->jumlah_terjadwal ?? 0)) ?></td>
                                <td><span class="badge <?= $complete ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $complete ? 'Sinkron' : 'Perlu dicek' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mt-3">
                <p class="muted-copy small mb-0">Bagan lama akan ditimpa dari urutan battle yang sudah ada di jadwal/import Excel.</p>
                <button type="submit" class="btn btn-admin-brand rounded-pill" onclick="return confirmAdminAction(this.closest('form'), 'Generate Bagan Seni Battle?', 'Bagan lama akan ditimpa dari urutan battle yang sudah ada di jadwal/import Excel.', 'Ya, Generate')">Generate Bagan Terpilih</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

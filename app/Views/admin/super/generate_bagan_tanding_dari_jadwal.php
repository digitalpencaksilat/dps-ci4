<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Generate Bagan Tanding dari Jadwal</h6>
        <p class="muted-copy small mb-0">Centang kompetisi yang akan di-generate ulang bagannya dari data partai existing (seperti CI3).</p>
    </div>
    <div class="card-body px-0">
        <form method="post" action="<?= base_url('admin/super/generate-bagan-tanding-dari-jadwal') ?>">
            <?= csrf_field() ?>
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table align-middle mb-0">
                    <thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.js-pilih-tanding').forEach(cb => cb.checked = this.checked)"></th><th>Kelas</th><th>Pool</th><th>Bagan</th><th>Partai Terbuat</th><th>Terjadwal</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach (($rows ?? []) as $row) : ?>
                            <?php $complete = (int) ($row->jumlah_pertandingan ?? 0) > 0 && (int) ($row->jumlah_pertandingan ?? 0) === (int) ($row->jumlah_terjadwal ?? 0); ?>
                            <tr>
                                <td><input class="js-pilih-tanding" type="checkbox" name="id_kompetisi_tanding[]" value="<?= esc((string) $row->id_kompetisi_tanding) ?>" <?= (int) ($row->jumlah_pertandingan ?? 0) > 0 ? '' : 'disabled' ?>></td>
                                <td><?= esc(trim(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '') . ' Kelas ' . ($row->label ?? '-'))) ?></td>
                                <td><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                                <td><?= empty($row->bagan_pertandingan) ? 'Belum tersedia' : 'Tersedia' ?></td>
                                <td><?= esc((string) ($row->jumlah_pertandingan ?? 0)) ?></td>
                                <td><?= esc((string) ($row->jumlah_terjadwal ?? 0)) ?></td>
                                <td><span class="badge <?= $complete ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $complete ? 'Sinkron' : 'Perlu dicek' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mt-3">
                <p class="muted-copy small mb-0">Bagan lama akan ditimpa dari urutan pertandingan yang sudah ada di jadwal/import Excel.</p>
                <button type="submit" class="btn btn-primary" onclick="return confirmAdminAction(this.closest('form'), 'Generate Bagan Tanding?', 'Bagan lama akan ditimpa dari urutan pertandingan yang sudah ada di jadwal/import Excel.', 'Ya, Generate')">Generate Bagan Terpilih</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4"><div><p class="eyebrow mb-1">Kategori Tanding</p><h3 class="section-title h4 mb-0"><?= esc($row->label ?? 'Kelas Tanding') ?></h3></div><a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/sekretariat/kelas-tanding') ?>">Kembali</a></div>
    <div class="table-responsive">
        <table class="table table-sm admin-table align-middle mb-0">
            <tbody>
                <tr><th class="text-muted fw-medium">Kategori Usia</th><td><?= esc($row->nama_kategori_usia ?? '-') ?></td><th class="text-muted fw-medium">Jenis Kelamin</th><td><?= esc($row->jenis_kelamin ?? '-') ?></td><th class="text-muted fw-medium">Jenis Perlombaan</th><td><?= esc($row->jenis_perlombaan ?? '-') ?></td></tr>
                <tr><th class="text-muted fw-medium">Rentang Berat</th><td><?= esc(($row->berat_minimal ?? '-') . ' - ' . ($row->berat_maksimal ?? '-') . ' Kg') ?></td><th class="text-muted fw-medium">Peserta</th><td><?= esc((string) ($row->jumlah_peserta_tanding ?? 0)) ?> / <?= esc((string) ($row->max_peserta ?? 0)) ?></td><th class="text-muted fw-medium">Peserta Lunas</th><td><?= esc((string) ($row->jumlah_peserta_tanding_lunas ?? 0)) ?></td></tr>
                <tr><th class="text-muted fw-medium">Jumlah Pool</th><td><?= esc((string) ($row->jumlah_pool ?? 0)) ?></td><th class="text-muted fw-medium">Prediksi Partai</th><td><?= esc((string) ($row->prediksi_jumlah_partai ?? 0)) ?></td><th class="text-muted fw-medium">Belum Dijadwalkan</th><td><?= esc((string) ($row->jumlah_partai_tanding_belum_dijadwalkan ?? 0)) ?></td></tr>
            </tbody>
        </table>
    </div>
</section>
<section class="admin-card mt-4">
    <ul class="nav nav-pills nav-fill mb-4" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#daftarPoolTanding" type="button" role="tab">Daftar Pool</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#daftarPesertaTanding" type="button" role="tab">Daftar Peserta</button></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="daftarPoolTanding" role="tabpanel">
            <div class="admin-table-wrap"><div class="table-shell admin-table-scroller"><table class="table admin-table admin-datatable align-middle mb-0">
                <thead><tr><th>Kategori Usia</th><th>Jenis Kelamin</th><th>Kelas</th><th>Nomor Pool</th><th>Jumlah Peserta</th><th>Jumlah Peserta Lunas</th><th>Max Peserta</th><th class="text-end">Aksi</th></tr></thead>
                <tbody><?php foreach (($poolRows ?? []) as $pool) : ?><tr><td><?= esc($pool->nama_kategori_usia ?? '-') ?></td><td class="text-capitalize"><?= esc($pool->jenis_kelamin ?? '-') ?></td><td class="text-center"><?= esc($pool->label ?? '-') ?></td><td class="text-end"><?= esc((string) ($pool->nomor_pool ?? '-')) ?></td><td class="text-end"><?= esc((string) ($pool->jumlah_peserta_tanding ?? 0)) ?></td><td class="text-end"><?= esc((string) ($pool->jumlah_peserta_tanding_lunas ?? 0)) ?></td><td class="text-end"><?= esc((string) ($pool->max_peserta ?? 0)) ?></td><td class="text-end"><div class="dropdown"><button class="btn btn-sm btn-outline-danger rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?= base_url('admin/sekretariat/pool-tanding/' . $pool->id_kompetisi_tanding) ?>">Lihat Pool</a></li><li><span class="dropdown-item disabled">Cetak Bagan belum tersedia</span></li><li><span class="dropdown-item disabled">Hapus pool belum dimigrasikan</span></li></ul></div></td></tr><?php endforeach; ?></tbody>
            </table></div></div>
        </div>
        <div class="tab-pane fade" id="daftarPesertaTanding" role="tabpanel">
            <div class="admin-table-wrap"><div class="table-shell admin-table-scroller"><table class="table admin-table admin-datatable align-middle mb-0">
                <thead><tr><th>Nama</th><th>Kontingen</th><th>Berat Badan</th><th>Tinggi Badan</th><th>Kategori</th><th>Kelas</th><th>Pool</th><th>Pembayaran</th><th>Keterangan</th><th class="text-end">Aksi</th></tr></thead>
                <tbody><?php foreach (($pesertaRows ?? []) as $peserta) : ?><tr><td class="fw-semibold text-capitalize"><?= esc($peserta->nama_pendaftar ?? '-') ?></td><td><?= esc($peserta->nama_kontingen ?? '-') ?></td><td class="text-end"><?= esc((string) ($peserta->berat_badan ?? '-')) ?> Kg</td><td class="text-end"><?= esc((string) ($peserta->tinggi_badan ?? '-')) ?> Cm</td><td><?= esc(($peserta->nama_kategori_usia ?? '-') . ' ' . ($peserta->jenis_kelamin ?? '')) ?></td><td><?= esc(($peserta->label ?? '-') . ' (' . ($peserta->berat_minimal ?? '-') . ' - ' . ($peserta->berat_maksimal ?? '-') . ' kg)') ?></td><td class="text-end"><?= esc((string) ($peserta->nomor_pool ?? '-')) ?></td><td><?= esc($peserta->status_pembayaran ?? 'belum lunas') ?></td><td><?= esc($peserta->keterangan_peserta_tanding ?? '-') ?></td><td class="text-end"><div class="dropdown"><button class="btn btn-sm btn-outline-danger rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">Aksi</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="<?= base_url('admin/sekretariat/pool-tanding/' . $peserta->id_kompetisi_tanding) ?>">Lihat Bagan</a></li><li><a class="dropdown-item" href="<?= base_url('admin/sekretariat/peserta-tanding/' . $peserta->id_peserta_tanding) ?>">Ganti Atlet / Kategori</a></li><li><a class="dropdown-item" href="<?= base_url('admin/sekretariat/peserta-tanding/' . $peserta->id_peserta_tanding) ?>">Pindah Pool</a></li><li><hr class="dropdown-divider"></li><li><form method="post" action="<?= base_url('admin/sekretariat/peserta-tanding/' . $peserta->id_peserta_tanding . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Undurkan peserta?', 'Peserta tanding ini akan dihapus dari kelas.', 'Undur Diri')"><?= csrf_field() ?><button class="dropdown-item text-danger" type="submit">Undur Diri</button></form></li><li><span class="dropdown-item disabled">Edit nomor bagan belum tersedia</span></li></ul></div></td></tr><?php endforeach; ?></tbody>
            </table></div></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

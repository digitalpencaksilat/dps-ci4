<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Detail Kelas Tanding',
    'title' => trim(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '') . ' - ' . ($row->label ?? '-')),
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/super/kelas-tanding'), 'label' => 'Kembali', 'class' => 'btn-outline-secondary'],
        ['tag' => 'a', 'href' => base_url('admin/super/kelas-tanding/' . $row->id_kelas_tanding . '/edit'), 'label' => 'Edit', 'class' => 'btn-danger'],
    ],
]) ?>

<section class="admin-card">
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
    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <form action="<?= base_url('admin/super/kelas-tanding/' . $row->id_kelas_tanding . '/otomatis-tambah-pool') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="max_peserta" value="<?= esc((string) ((int) ($poolRows[0]->max_peserta ?? 16))) ?>">
            <button type="submit" class="btn btn-outline-danger rounded-pill">Otomatis Tambah Pool</button>
        </form>
    </div>
    <ul class="nav nav-pills nav-fill mb-4" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#daftarPoolTanding" type="button" role="tab">Daftar Pool</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#daftarPesertaTanding" type="button" role="tab">Daftar Peserta</button></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="daftarPoolTanding" role="tabpanel">
            <div class="admin-table-wrap"><div class="table-shell admin-table-scroller"><table class="table admin-table admin-datatable-export align-middle mb-0">
                <thead><tr><th>Kategori Usia</th><th>Jenis Kelamin</th><th>Kelas</th><th>Nomor Pool</th><th>Jumlah Peserta</th><th>Jumlah Peserta Lunas</th><th>Max Peserta</th><th class="text-end no-export">Aksi</th></tr></thead>
                <tbody>
                    <?php foreach (($poolRows ?? []) as $pool) : ?>
                        <tr>
                            <td><?= esc($pool->nama_kategori_usia ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($pool->jenis_kelamin ?? '-') ?></td>
                            <td class="text-center"><?= esc($pool->label ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($pool->nomor_pool ?? '-')) ?></td>
                            <td class="text-end"><?= esc((string) ($pool->jumlah_peserta_tanding ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($pool->jumlah_peserta_tanding_lunas ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($pool->max_peserta ?? 0)) ?></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/pool-tanding/' . $pool->id_kompetisi_tanding) ?>">Lihat Pool</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div></div>
        </div>
        <div class="tab-pane fade" id="daftarPesertaTanding" role="tabpanel">
            <div class="admin-table-wrap"><div class="table-shell admin-table-scroller"><table class="table admin-table admin-datatable-export align-middle mb-0">
                <thead><tr><th>Nama</th><th>Kontingen</th><th>Berat Badan</th><th>Tinggi Badan</th><th>Kategori</th><th>Kelas</th><th>Pool</th><th>Pembayaran</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <?php foreach (($pesertaRows ?? []) as $peserta) : ?>
                        <tr>
                            <td class="fw-semibold text-capitalize"><?= esc($peserta->nama_pendaftar ?? '-') ?></td>
                            <td><?= esc($peserta->nama_kontingen ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($peserta->berat_badan ?? '-')) ?> Kg</td>
                            <td class="text-end"><?= esc((string) ($peserta->tinggi_badan ?? '-')) ?> Cm</td>
                            <td><?= esc(($peserta->nama_kategori_usia ?? '-') . ' ' . ($peserta->jenis_kelamin ?? '')) ?></td>
                            <td><?= esc(($peserta->label ?? '-') . ' (' . ($peserta->berat_minimal ?? '-') . ' - ' . ($peserta->berat_maksimal ?? '-') . ' kg)') ?></td>
                            <td class="text-end"><?= esc((string) ($peserta->nomor_pool ?? '-')) ?></td>
                            <td><?= esc($peserta->status_pembayaran ?? 'belum lunas') ?></td>
                            <td><?= esc($peserta->keterangan_peserta_tanding ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

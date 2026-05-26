<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="admin-card">
    <div class="mb-4">
        <p class="eyebrow mb-1">Kategori Tanding</p>
        <h3 class="section-title h4 mb-0">Daftar kelas tanding</h3>
        <p class="muted-copy mb-0 mt-2">Kolom utama mengikuti tabel CI3, tanpa tombol create/delete master data.</p>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller"><table class="table admin-table admin-datatable-export align-middle mb-0">
        <thead><tr><th>Kategori Usia</th><th>Jenis Kelamin</th><th>Rentang Berat Badan</th><th>Kelas</th><th>Jumlah Peserta</th><th>Jumlah Peserta Lunas</th><th>Max Peserta</th><th>Kuota Tersedia</th><th>Jumlah Pool</th><th>Jenis Perlombaan</th><th class="text-end no-export">Aksi</th></tr></thead>
        <tbody>
            <?php foreach (($rows ?? []) as $row) : ?>
                <?php $kuotaTersedia = (int) ($row->max_peserta ?? 0) - (int) ($row->jumlah_peserta_tanding ?? 0); ?>
                <tr>
                    <td class="text-capitalize"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                    <td class="text-capitalize"><?= esc($row->jenis_kelamin ?? '-') ?></td>
                    <td><?= esc(($row->berat_minimal ?? '-') . ' - ' . ($row->berat_maksimal ?? '-') . ' Kg') ?></td>
                    <td class="fw-semibold text-center"><?= esc($row->label ?? '-') ?></td>
                    <td class="text-end"><?= esc((string) ($row->jumlah_peserta_tanding ?? 0)) ?></td>
                    <td class="text-end"><?= esc((string) ($row->jumlah_peserta_tanding_lunas ?? 0)) ?></td>
                    <td class="text-end"><?= esc((string) ($row->max_peserta ?? 0)) ?></td>
                    <td class="text-end"><?= esc((string) $kuotaTersedia) ?></td>
                    <td class="text-end"><?= esc((string) ($row->jumlah_pool ?? 0)) ?></td>
                    <td class="text-capitalize"><?= esc($row->jenis_perlombaan ?? '-') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/kelas-tanding/' . $row->id_kelas_tanding) ?>">Lihat Pool</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div></div>
</section>
<?= $this->endSection() ?>

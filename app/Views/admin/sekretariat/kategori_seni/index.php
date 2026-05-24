<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="admin-card">
    <div class="mb-4">
        <p class="eyebrow mb-1">Kategori Seni</p>
        <h3 class="section-title h4 mb-0">Daftar kategori seni</h3>
        <p class="muted-copy mb-0 mt-2">Kolom utama mengikuti tabel CI3, tanpa tombol create/delete master data.</p>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller"><table class="table admin-table admin-datatable align-middle mb-0">
        <thead><tr><th>Kategori Usia</th><th>Jenis Kelamin</th><th>Jenis Seni</th><th>Nama Seni</th><th>Sistem Penampilan</th><th>Jumlah Peserta</th><th>Max Peserta</th><th>Jumlah Pool</th><th>Total Kapasitas Peserta</th><th>Konfigurasi Jumlah Peserta</th><th>Format Penilaian</th><th>Biaya DN</th><th>Biaya LN</th><th>Jenis Perlombaan</th><th>Keterangan</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
            <?php foreach (($rows ?? []) as $row) : ?>
                <tr>
                    <td class="text-capitalize"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                    <td class="text-capitalize"><?= esc($row->jenis_kelamin ?? '-') ?></td>
                    <td class="text-capitalize"><?= esc($row->jenis_seni ?? '-') ?></td>
                    <td class="fw-semibold"><?= esc($row->nama_seni ?? '-') ?></td>
                    <td><?= esc($row->sistem_penampilan ?? '-') ?></td>
                    <td class="text-end"><?= esc((string) ($row->jumlah_kelompok_peserta_seni ?? 0)) ?></td>
                    <td class="text-end"><?= esc((string) ($row->total_kapasitas_kelompok_peserta_seni ?? 0)) ?></td>
                    <td class="text-end"><?= esc((string) ($row->jumlah_pool ?? 0)) ?></td>
                    <td class="text-end"><?= esc((string) ($row->total_kapasitas_kelompok_peserta_seni ?? 0)) ?></td>
                    <td class="text-end"><?= esc((string) ($row->jumlah_peserta ?? 0)) ?></td>
                    <td><?= esc($row->format_penilaian ?? '-') ?></td>
                    <td><?= esc((string) ($row->biaya_pendaftaran_dn ?? '-')) ?></td>
                    <td><?= esc((string) ($row->biaya_pendaftaran_ln ?? '-')) ?></td>
                    <td class="text-capitalize"><?= esc($row->jenis_perlombaan ?? '-') ?></td>
                    <td><?= esc($row->keterangan ?? '-') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/kategori-seni/' . $row->id_sub_kategori_seni) ?>">Lihat Pool</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div></div>
</section>
<?= $this->endSection() ?>

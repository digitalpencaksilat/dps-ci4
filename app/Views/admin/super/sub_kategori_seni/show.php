<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Detail Sub Kategori Seni</p>
            <h3 class="section-title h4 mb-0"><?= esc(trim(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '') . ' - ' . ($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-'))) ?></h3>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-light rounded-pill" href="<?= base_url('admin/super/sub-kategori-seni/' . $row->id_sub_kategori_seni . '/edit') ?>">Edit</a>
            <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/super/sub-kategori-seni') ?>">Kembali</a>
        </div>
    </div>
    <div class="row g-3">
        <?php foreach ([
            'Kategori Usia' => $row->nama_kategori_usia ?? '-',
            'Jenis Kelamin' => $row->jenis_kelamin ?? '-',
            'Kategori Lomba' => $row->nama_kategori_lomba ?? '-',
            'Peraturan' => $row->peraturan_pertandingan ?? '-',
            'Jenis Perlombaan' => $row->jenis_perlombaan ?? '-',
            'Nama Seni' => $row->nama_seni ?? '-',
            'Jenis Seni' => $row->jenis_seni ?? '-',
            'Jumlah Peserta' => $row->jumlah_peserta ?? '-',
            'Waktu' => $row->waktu ?? '-',
            'Biaya DN' => $row->biaya_pendaftaran_dn ?? '-',
            'Biaya LN' => $row->biaya_pendaftaran_ln ?? '-',
            'Format Penilaian' => $row->format_penilaian ?? '-',
            'Sistem Penampilan' => $row->sistem_penampilan ?? '-',
            'Keterangan' => $row->keterangan ?? '-',
        ] as $label => $value) : ?>
            <div class="col-12 col-md-4">
                <div class="admin-card h-100">
                    <div class="small muted-copy"><?= esc($label) ?></div>
                    <div class="fw-semibold mt-1 text-capitalize"><?= esc((string) $value) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="admin-card">
    <div class="mb-4">
        <p class="eyebrow mb-1">Pool Seni</p>
        <h3 class="section-title h4 mb-0">Daftar Pool</h3>
    </div>
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table align-middle mb-0">
                <thead><tr><th>ID</th><th>Nomor Pool</th><th>Max Peserta</th><th>Perhitungan Medali</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <?php foreach (($poolRows ?? []) as $pool) : ?>
                        <tr>
                            <td><?= esc((string) ($pool->id_kompetisi_seni ?? '-')) ?></td>
                            <td><?= esc((string) ($pool->nomor_pool ?? '-')) ?></td>
                            <td><?= esc((string) ($pool->max_peserta ?? '-')) ?></td>
                            <td><?= ((int) ($pool->perhitungan_medali ?? 0) === 1) ? 'Ya' : 'Tidak' ?></td>
                            <td><?= esc($pool->keterangan ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

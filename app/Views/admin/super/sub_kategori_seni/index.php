<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Kategori Lomba</p>
            <h2 class="section-title h3 mb-3">Sub Kategori Seni</h2>
            <p class="muted-copy mb-0">Tabel read-only awal untuk cross-check sub kategori seni sebelum CRUD dan otomatis pool dimigrasikan.</p>
        </div>
        <div class="d-flex flex-wrap align-items-start gap-2">
            <span class="status-badge <?= ($activeMode ?? '') === 'perngaturan_kategori_lomba' ? 'success' : 'warning' ?>">
                Mode: <?= esc(($activeMode ?? '') === 'perngaturan_kategori_lomba' ? 'perngaturan_kategori_lomba' : 'belum aktif') ?>
            </span>
            <a href="<?= base_url('admin/super/menu-tipe') ?>" class="btn btn-outline-light rounded-pill">Ganti Mode</a>
        </div>
    </div>
</section>

<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Master Data</p>
            <h3 class="section-title h4 mb-0">Daftar Sub Kategori Seni</h3>
            <p class="muted-copy mb-0 mt-2">Aksi tambah, edit, delete, dan otomatis pool akan diaktifkan pada tahap berikutnya.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('admin/super/kategori-usia') ?>" class="btn btn-outline-light rounded-pill">Kategori Usia</a>
            <a href="<?= base_url('admin/super/kategori-lomba') ?>" class="btn btn-outline-light rounded-pill">Kategori Lomba</a>
            <span class="status-badge neutral">Total: <?= esc((string) count($rows ?? [])) ?></span>
        </div>
    </div>

    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table admin-datatable-export align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kategori Usia</th>
                        <th>Jenis Kelamin</th>
                        <th>Kategori Lomba</th>
                        <th>Nama Seni</th>
                        <th>Jenis Seni</th>
                        <th>Jumlah Peserta</th>
                        <th>Waktu</th>
                        <th>Biaya DN</th>
                        <th>Biaya LN</th>
                        <th>Format Penilaian</th>
                        <th>Sistem Penampilan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row) : ?>
                        <tr>
                            <td><?= esc((string) ($row->id_sub_kategori_seni ?? '-')) ?></td>
                            <td class="text-capitalize"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_kelamin ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->nama_kategori_lomba ?? '-') ?></td>
                            <td class="fw-semibold text-capitalize"><?= esc($row->nama_seni ?? '-') ?></td>
                            <td class="text-capitalize"><?= esc($row->jenis_seni ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($row->jumlah_peserta ?? '-')) ?></td>
                            <td><?= esc((string) ($row->waktu ?? '-')) ?></td>
                            <td><?= esc((string) ($row->biaya_pendaftaran_dn ?? '-')) ?></td>
                            <td><?= esc((string) ($row->biaya_pendaftaran_ln ?? '-')) ?></td>
                            <td><?= esc($row->format_penilaian ?? '-') ?></td>
                            <td><?= esc($row->sistem_penampilan ?? '-') ?></td>
                            <td><?= esc($row->keterangan ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

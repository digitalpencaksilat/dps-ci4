<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Perolehan Medali</p>
            <h2 class="section-title h3 mb-2"><?= esc($reportTitle ?? $title ?? 'Perolehan Medali Seni') ?></h2>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table admin-table admin-datatable-export align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontingen</th>
                    <th>Provinsi</th>
                    <th>Sekolah</th>
                    <th>Usia</th>
                    <th>Kategori</th>
                    <th class="text-center">Medali</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <tr>
                        <td class="fw-semibold text-capitalize"><?= $row['anggota_kelompok_peserta_seni'] ?? '-' ?></td>
                        <td class="text-capitalize"><?= esc($row['nama_kontingen'] ?? '-') ?></td>
                        <td><?= esc($row['provinsi'] ?? '-') ?></td>
                        <td><?= esc($row['nama_sekolah'] ?? '-') ?></td>
                        <td class="text-capitalize"><?= esc(trim(($row['nama_kategori_usia'] ?? '-') . ' ' . ($row['jenis_kelamin'] ?? ''))) ?></td>
                        <td class="text-capitalize">
                            <?php if (($row['jenis_perlombaan'] ?? '') === 'pemasalan') : ?>
                                <?= esc(trim(($row['jenis_seni'] ?? '-') . ' - ' . ($row['nama_seni'] ?? '-') . ' Pool ' . ($row['nomor_pool'] ?? '-'))) ?>
                            <?php else : ?>
                                <?= esc(trim(($row['jenis_seni'] ?? '-') . ' - ' . ($row['nama_seni'] ?? '-'))) ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= view('admin/sekretariat/medal_tally/_medal_badge', ['medal' => $row['jenis_medali'] ?? null]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($rows)) : ?><div class="text-center muted-copy py-4">Belum ada data perolehan medali seni.</div><?php endif; ?>
</section>
<?= $this->endSection() ?>

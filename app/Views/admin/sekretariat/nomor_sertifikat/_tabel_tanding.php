<div class="admin-table-wrap">
    <div class="admin-table-note"><i class="fas fa-arrows-left-right-to-line"></i><span>Struktur tabel mengikuti halaman nomor sertifikat CI3 untuk kategori tanding.</span></div>
    <div class="table-shell admin-table-scroller">
        <table class="table admin-table admin-datatable-export align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontingen</th>
                    <th>Provinsi</th>
                    <th>Nama Sekolah</th>
                    <th class="text-center">Kategori</th>
                    <th class="text-center">Kelas</th>
                    <th class="text-center">Medali</th>
                    <th class="text-center">Nomor Sertifikat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <tr>
                        <td class="fw-semibold text-capitalize"><?= esc($row->nama_pendaftar ?? '-') ?></td>
                        <td class="text-capitalize"><?= esc($row->nama_kontingen ?? '-') ?></td>
                        <td><?= esc($row->provinsi ?? '-') ?></td>
                        <td><?= esc($row->nama_sekolah ?? '-') ?></td>
                        <td class="text-center text-capitalize"><?= esc(trim(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? ''))) ?></td>
                        <td class="text-center text-capitalize">
                            <?php if (($row->jenis_perlombaan ?? '') === 'pemasalan') : ?>
                                <?= esc(trim(($row->label ?? '-') . ' Pool ' . ($row->nomor_pool ?? '-'))) ?>
                            <?php else : ?>
                                <?= esc($row->label ?? '-') ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= view('admin/sekretariat/medal_tally/_medal_badge', ['medal' => $row->jenis_medali ?? null]) ?></td>
                        <td class="text-center"><?= esc($row->nomor_sertifikat ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (empty($rows)) : ?>
    <div class="text-center muted-copy py-4">Belum ada data nomor sertifikat kategori tanding.</div>
<?php endif; ?>

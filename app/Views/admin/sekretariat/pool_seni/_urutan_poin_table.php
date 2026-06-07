<div class="admin-table-wrap pesilat-terbaik-table">
    <div class="table-shell admin-table-scroller">
        <table class="table admin-table admin-datatable-export align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontingen</th>
                    <th>Usia</th>
                    <th>Jenis Kelamin</th>
                    <th>Nomor Lomba</th>
                    <th>Nomor Pool</th>
                    <th>Babak</th>
                    <th>Waktu</th>
                    <th>Nilai</th>
                    <th>Medali</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <tr>
                        <td class="small">
                            <span class="fw-semibold d-block"><?= esc($row->anggota_kelompok_peserta_seni ?? '-') ?></span>
                            <span class="muted-copy d-block"><i><?= $row->status_penampilan === 'sudah_tampil' ? esc((string) ($row->waktu_tampil ?? '-')) : 'Belum Tampil' ?></i></span>
                        </td>
                        <td class="small">
                            <span class="d-block text-uppercase"><?= esc($row->nama_kontingen ?? '-') ?></span>
                            <span class="muted-copy d-block">Gelanggang <?= esc($row->nama_gelanggang ?? '-') ?>, Partai <?= esc((string) ($row->nomor_partai ?? '-')) ?></span>
                        </td>
                        <td class="text-center"><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                        <td class="text-center"><?= esc(strtoupper((string) ($row->jenis_kelamin ?? '-'))) ?></td>
                        <td class="text-center"><?= esc(ucwords((string) ($row->jenis_seni ?? '-'))) ?></td>
                        <td class="text-center"><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                        <td class="text-center"><?= esc($row->babak_pool ?? '-') ?></td>
                        <td class="text-center"><?= esc($row->status_penampilan === 'sudah_tampil' ? (string) ($row->waktu_tampil ?? '-') : '-') ?></td>
                        <td class="text-center"><span class="fw-semibold"><?= esc($row->status_penampilan === 'sudah_tampil' ? (string) ($row->nilai_akhir ?? '-') : '-') ?></span></td>
                        <td class="text-center"><?= view('admin/sekretariat/medal_tally/_medal_badge', ['medal' => $row->jenis_medali ?? null]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

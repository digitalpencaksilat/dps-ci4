<?php $rows = $rows ?? ($data_peserta_tanding_bertemu_kontingen_sendiri_dua_peserta ?? []); ?>
<div class="admin-table-wrap">
    <div class="table-shell admin-table-scroller" style="max-height: 520px;">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontingen</th>
                    <th>Kategori</th>
                    <th>Kelas</th>
                    <th class="text-end">Jml Kontingen Sama</th>
                    <th class="text-end">Jml Peserta Pool</th>
                    <th class="text-end">Pool</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <td class="fw-semibold"><?= esc((string) ($row->nama_pendaftar ?? '-')) ?></td>
                        <td class="text-uppercase"><?= esc((string) ($row->nama_kontingen ?? '-')) ?></td>
                        <td><?= esc(trim((string) ($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? ''))) ?></td>
                        <td><?= esc((string) ($row->label ?? '-')) ?></td>
                        <td class="text-end"><?= esc((string) ($row->jumlah_peserta_tanding_kontingen_sama ?? 0)) ?></td>
                        <td class="text-end"><?= esc((string) ($row->jumlah_peserta_tanding ?? 0)) ?></td>
                        <td class="text-end"><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($rows === []) : ?>
                    <tr><td colspan="7" class="text-center muted-copy py-4">Tidak ada</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $isMobile = service('request')->getUserAgent()->isMobile(); ?>
<table width="100%" class="table admin-table admin-datatable w-100 table-striped table-hover text-sm" id="tabelDetailJadwalseni" caption="Performance">
    <thead>
        <tr>
            <?php if (! $isMobile): ?><th></th><?php endif; ?>
            <th class="exportable" width="4%">Match</th>
            <th class="exportable" width="4%">Number</th>
            <th class="none exportable">Round</th>
            <?php if (! $isMobile): ?><th class="none exportable">Shuffle</th><?php endif; ?>
            <th class="exportable">Name</th>
            <?php if (! $isMobile): ?><th class="exportable">Kontingen</th><?php endif; ?>
            <th class="exportable col-export-category">Category</th>
            <th class="exportable col-export-usia">Usia</th>
            <th class="exportable">Score</th>
            <th class="exportable">Median Kebenaran</th>
            <?php if (! $isMobile): ?><th class="exportable">Standar Deviation</th><?php endif; ?>
            <th class="exportable">Time</th>
            <?php if (! $isMobile): ?><th class="exportable">Status</th><?php endif; ?>
            <th class="exportable">Medal</th>
            <th class="no-export"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($data_detail_jadwal_seni ?? []) as $partai_seni): ?>
            <tr>
                <?php if (! $isMobile): ?><td></td><?php endif; ?>
                <td class="text-center align-middle"><?= esc((string) ($partai_seni->nomor_partai ?? '-')) ?></td>
                <td class="text-center align-middle"><?= esc((string) ($partai_seni->nomor_urut ?? '-')) ?></td>
                <td class="text-center align-middle"><?= esc($partai_seni->babak_pool ?? '-') ?></td>
                <?php if (! $isMobile): ?><td class="text-center align-middle"><?= esc((string) ($partai_seni->nomor_undi ?? '-')) ?></td><?php endif; ?>
                <td class="text-wrap align-middle">
                    <?= esc($partai_seni->anggota_kelompok_peserta_seni ?? '-') ?>
                    <?php if ($isMobile): ?>
                        <small class="text-wrap d-block"><?= esc($partai_seni->nama_kontingen ?? '-') ?></small>
                        <?= view('shared_components/detail_jadwal_seni/badge_status_penampilan', ['partai_seni' => $partai_seni]) ?>
                    <?php endif; ?>
                </td>
                <?php if (! $isMobile): ?><td class="text-wrap align-middle"><?= esc($partai_seni->nama_kontingen ?? '-') ?></td><?php endif; ?>
                <td class="text-capitalize align-middle"><?= esc(trim(($partai_seni->jenis_seni ?? '') . ' ' . ($partai_seni->jenis_kelamin ?? '') . ' - ' . ($partai_seni->nama_seni ?? ''))) ?></td>
                <td class="text-capitalize align-middle"><?= esc(trim(($partai_seni->nama_kategori_usia ?? '-') . ' Pool ' . ($partai_seni->nomor_pool ?? '-'))) ?></td>
                <td class="text-end align-middle"><?= esc(number_format((float) ($partai_seni->nilai_akhir ?? 0), 3)) ?></td>
                <td class="text-end align-middle"><?= esc(number_format((float) ($partai_seni->median_kebenaran ?? 0), 3)) ?></td>
                <?php if (! $isMobile): ?><td class="text-end align-middle"><?= esc(number_format((float) ($partai_seni->standar_deviasi ?? 0), 6)) ?></td><?php endif; ?>
                <td class="text-end align-middle"><?= esc(sprintf('%02d:%02d', floor(((int) ($partai_seni->waktu_tampil ?? 0)) / 60), ((int) ($partai_seni->waktu_tampil ?? 0)) % 60)) ?></td>
                <?php if (! $isMobile): ?>
                    <td class="align-middle text-center">
                        <?= view('shared_components/detail_jadwal_seni/badge_status_penampilan', ['partai_seni' => $partai_seni]) ?>
                        <?= (($partai_seni->diskualifikasi ?? 0) == 1) ? '<br><span class="badge bg-danger">Disqualification</span>' : '' ?>
                    </td>
                <?php endif; ?>
                <td class="text-center align-middle">
                    <?php if (strtolower($partai_seni->jenis_medali_pool ?? '') === 'emas'): ?>
                        <span class="badge text-white" style="background-color:#ffb322">Emas</span>
                    <?php elseif (strtolower($partai_seni->jenis_medali_pool ?? '') === 'perak'): ?>
                        <span class="badge text-white" style="background-color:#b0b0b0">Perak</span>
                    <?php elseif (strtolower($partai_seni->jenis_medali_pool ?? '') === 'perunggu'): ?>
                        <span class="badge text-white" style="background-color:#7c4800">Perunggu</span>
                    <?php else: ?>
                        <?= esc($partai_seni->jenis_medali_pool ?? '-') ?>
                    <?php endif; ?>
                </td>
                <td></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

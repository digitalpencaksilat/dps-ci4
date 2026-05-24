<?php $tableId = $tableId ?? 'schoolMedalTallyTable'; ?>
<div class="table-responsive">
    <table id="<?= esc($tableId) ?>" class="table admin-table admin-datatable align-middle mb-0">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Sekolah</th>
                <th class="text-center">Emas</th>
                <th class="text-center">Perak</th>
                <th class="text-center">Perunggu</th>
                <th class="text-center">Tanding</th>
                <th class="text-center">Seni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($rows ?? []) as $index => $row) : ?>
                <tr>
                    <td class="fw-bold"><?= esc((string) ($index + 1)) ?></td>
                    <td class="fw-semibold"><?= esc(($row['nama_sekolah'] ?? '') !== '' ? $row['nama_sekolah'] : '(Sekolah kosong)') ?></td>
                    <td class="text-center fw-bold text-warning"><?= esc((string) ($row['total_emas'] ?? 0)) ?></td>
                    <td class="text-center fw-bold text-secondary"><?= esc((string) ($row['total_perak'] ?? 0)) ?></td>
                    <td class="text-center fw-bold" style="color:#92400e"><?= esc((string) ($row['total_perunggu'] ?? 0)) ?></td>
                    <td class="text-center small"><?= esc((string) ($row['emas_tanding'] ?? 0)) ?>/<?= esc((string) ($row['perak_tanding'] ?? 0)) ?>/<?= esc((string) ($row['perunggu_tanding'] ?? 0)) ?></td>
                    <td class="text-center small"><?= esc((string) ($row['emas_seni'] ?? 0)) ?>/<?= esc((string) ($row['perak_seni'] ?? 0)) ?>/<?= esc((string) ($row['perunggu_seni'] ?? 0)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (empty($rows)) : ?>
    <div class="text-center muted-copy py-4">Belum ada data medali untuk kategori ini.</div>
<?php endif; ?>

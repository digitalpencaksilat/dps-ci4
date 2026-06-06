<?php if (($data_gelanggang ?? []) === []) : ?>
    <div class="placeholder-stat">
        <h4 class="h5 mb-2">Belum ada gelanggang</h4>
        <p class="muted-copy mb-0">Data gelanggang belum tersedia. Tambahkan arena pertandingan terlebih dahulu.</p>
    </div>
<?php else : ?>
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table align-middle mb-0" id="tabelGelanggang" width="100%">
                <thead>
                    <tr>
                        <th>Nama Gelanggang</th>
                        <th>Nomor Gelanggang</th>
                        <th>Keterangan</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_gelanggang as $gelanggang): ?>
                        <tr>
                            <td class="fw-semibold text-uppercase"><?= esc($gelanggang->nama_gelanggang) ?></td>
                            <td><?= esc($gelanggang->nomor_gelanggang) ?></td>
                            <td><?= esc($gelanggang->keterangan ?? '-') ?></td>
                            <td class="text-end">
                                <?= view('admin/gelanggang/dropdown_actions', ['gelanggang' => $gelanggang]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

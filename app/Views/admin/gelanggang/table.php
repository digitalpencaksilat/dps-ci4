<?php if (isset($this->agent) && $this->agent->is_mobile()) : ?>
    <table width="100%" class="table" id="tabelGelanggang">
        <thead>
            <tr>
                <th class="not-mobile">Nama Gelanggang</th>
                <th class="not-mobile">Nomor Gelanggang</th>
                <th class="not-mobile">Keterangan</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_gelanggang as $gelanggang): ?>
                <tr>
                    <td><?= esc($gelanggang->nama_gelanggang) ?></td>
                    <td><?= esc($gelanggang->nomor_gelanggang) ?></td>
                    <td><?= esc($gelanggang->keterangan ?? '-') ?></td>
                    <td>
                        <?= view('admin/gelanggang/dropdown_actions', ['gelanggang' => $gelanggang]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <table width="100%" class="table" id="tabelGelanggang">
        <thead>
            <tr>
                <th>Nama Gelanggang</th>
                <th>Nomor Gelanggang</th>
                <th>Keterangan</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_gelanggang as $gelanggang): ?>
                <tr>
                    <td><?= esc($gelanggang->nama_gelanggang) ?></td>
                    <td><?= esc($gelanggang->nomor_gelanggang) ?></td>
                    <td><?= esc($gelanggang->keterangan ?? '-') ?></td>
                    <td>
                        <?= view('admin/gelanggang/dropdown_actions', ['gelanggang' => $gelanggang]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

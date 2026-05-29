<table class="table admin-table admin-datatable align-middle mb-0">
    <thead>
        <tr>
            <?php if (session()->get('level') === 'super_admin'): ?>
                <th class="text-center" style="width: 40px;">
                    <input type="checkbox" id="checkAllJadwalTanding" title="Select all on this page">
                </th>
            <?php endif; ?>
            <th>Arena</th>
            <th>Tanggal</th>
            <th>Jumlah Partai</th>
            <th>Partai Awal</th>
            <th>Partai Akhir</th>
            <th>Keterangan</th>
            <th class="text-end">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($data_jadwal_tanding ?? []) as $data) : ?>
            <tr>
                <?php if (session()->get('level') === 'super_admin'): ?>
                    <td class="text-center">
                        <input type="checkbox" class="checkbox-jadwal-tanding" 
                               data-id="<?= esc((string) $data->id_jadwal_tanding) ?>"
                               data-nama="Arena <?= esc($data->nama_gelanggang) ?> - <?= esc($data->keterangan_jadwal ?? '') ?>">
                    </td>
                <?php endif; ?>
                <td>Arena <?= esc($data->nama_gelanggang) ?></td>
                <td><?= esc($data->tanggal_formatted ?? $data->tanggal ?? '-') ?></td>
                <td><?= esc((string) ($data->jumlah_partai ?? 0)) ?></td>
                <td class="text-end"><?= esc((string) ($data->nomor_partai_awal ?? '-')) ?></td>
                <td class="text-end"><?= esc((string) ($data->nomor_partai_akhir ?? '-')) ?></td>
                <td><?= esc($data->keterangan_jadwal ?? $data->keterangan ?? '-') ?></td>
                <td class="text-end">
                    <?= view('shared_components/jadwal_tanding/tombol_tabel', [
                        'jadwal' => $data,
                        'routePrefix' => $routePrefix ?? 'admin/sekretariat/jadwal-tanding',
                    ]) ?>
                </td>
            </tr>
            <?= view('shared_components/jadwal_tanding/modal_ubah_keterangan', [
                'jadwal' => $data,
                'routePrefix' => $routePrefix ?? 'admin/sekretariat/jadwal-tanding',
            ]) ?>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.querySelector('#tabel_jadwal_tanding') || document.querySelector('.admin-datatable');
    if (!table) return;

    function updateCounter() {
        var checked = document.querySelectorAll('.checkbox-jadwal-tanding:checked');
        var count = checked.length;
        var btnUpdate = document.getElementById('btnUpdateSelectedTanding');
        var btnScore = document.getElementById('btnUpdateSelectedTandingScore');
        var countEl = document.getElementById('btnUpdateSelectedTandingCount');
        var countScoreEl = document.getElementById('btnUpdateSelectedTandingScoreCount');
        if (countEl) countEl.textContent = '(' + count + ')';
        if (countScoreEl) countScoreEl.textContent = '(' + count + ')';
        if (btnUpdate) btnUpdate.disabled = count === 0;
        if (btnScore) btnScore.disabled = count === 0;
    }

    document.getElementById('checkAllJadwalTanding')?.addEventListener('change', function() {
        var isChecked = this.checked;
        document.querySelectorAll('.checkbox-jadwal-tanding').forEach(function(cb) {
            cb.checked = isChecked;
        });
        updateCounter();
    });

    document.querySelectorAll('.checkbox-jadwal-tanding').forEach(function(cb) {
        cb.addEventListener('change', updateCounter);
    });

    window.getSelectedJadwalTanding = function() {
        var selected = [];
        document.querySelectorAll('.checkbox-jadwal-tanding:checked').forEach(function(cb) {
            selected.push({ id: cb.getAttribute('data-id'), nama: cb.getAttribute('data-nama') });
        });
        return selected;
    };

    window.resetSelectionJadwalTanding = function() {
        document.querySelectorAll('.checkbox-jadwal-tanding').forEach(function(cb) { cb.checked = false; });
        var checkAll = document.getElementById('checkAllJadwalTanding');
        if (checkAll) checkAll.checked = false;
        updateCounter();
    };
});
</script>

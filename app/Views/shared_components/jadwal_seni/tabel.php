<table class="table admin-table admin-datatable align-middle mb-0" id="tabel_jadwal_seni">
    <thead>
        <tr>
            <?php if (session()->get('level') === 'super_admin'): ?>
                <th class="text-center" style="width: 40px;">
                    <input type="checkbox" id="checkAllJadwalSeni" title="Select all on this page">
                </th>
            <?php endif; ?>
            <th>Arena</th>
            <th>Tanggal</th>
            <th>Jumlah Penampilan</th>
            <th>Penampilan Pertama</th>
            <th>Penampilan Terakhir</th>
            <th>Keterangan</th>
            <th class="text-end">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($data_jadwal_seni ?? []) as $data) : ?>
            <tr>
                <?php if (session()->get('level') === 'super_admin'): ?>
                    <td class="text-center">
                        <input type="checkbox" class="checkbox-jadwal-seni" 
                               data-id="<?= esc((string) $data->id_jadwal_seni) ?>"
                               data-nama="Arena <?= esc($data->nama_gelanggang) ?> - <?= esc($data->keterangan_jadwal ?? '') ?>">
                    </td>
                <?php endif; ?>
                <td>Arena <?= esc($data->nama_gelanggang) ?></td>
                <td><?= esc($data->tanggal_formatted ?? $data->tanggal ?? '-') ?></td>
                <td class="text-end"><?= esc((string) ($data->jumlah_penampilan ?? 0)) ?></td>
                <td class="text-end"><?= esc((string) ($data->nomor_partai_awal ?? '-')) ?></td>
                <td class="text-end"><?= esc((string) ($data->nomor_partai_akhir ?? '-')) ?></td>
                <td><?= esc($data->keterangan_jadwal ?? $data->keterangan ?? '-') ?></td>
                <td class="text-end">
                    <?= view('shared_components/jadwal_seni/tombol_tabel', ['jadwal' => $data]) ?>
                </td>
            </tr>
            <?= view('shared_components/jadwal_seni/modal_ubah_keterangan', ['jadwal' => $data]) ?>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateCounter() {
        var checked = document.querySelectorAll('.checkbox-jadwal-seni:checked');
        var count = checked.length;
        var btnUpdate = document.getElementById('btnUpdateSelectedSeni');
        var btnScore = document.getElementById('btnUpdateSelectedSeniScore');
        var countEl = document.getElementById('btnUpdateSelectedSeniCount');
        var countScoreEl = document.getElementById('btnUpdateSelectedSeniScoreCount');
        if (countEl) countEl.textContent = '(' + count + ')';
        if (countScoreEl) countScoreEl.textContent = '(' + count + ')';
        if (btnUpdate) btnUpdate.disabled = count === 0;
        if (btnScore) btnScore.disabled = count === 0;
    }

    document.getElementById('checkAllJadwalSeni')?.addEventListener('change', function() {
        var isChecked = this.checked;
        document.querySelectorAll('.checkbox-jadwal-seni').forEach(function(cb) { cb.checked = isChecked; });
        updateCounter();
    });

    document.querySelectorAll('.checkbox-jadwal-seni').forEach(function(cb) {
        cb.addEventListener('change', updateCounter);
    });

    window.getSelectedJadwalSeni = function() {
        var selected = [];
        document.querySelectorAll('.checkbox-jadwal-seni:checked').forEach(function(cb) {
            selected.push({ id: cb.getAttribute('data-id'), nama: cb.getAttribute('data-nama') });
        });
        return selected;
    };

    window.resetSelectionJadwalSeni = function() {
        document.querySelectorAll('.checkbox-jadwal-seni').forEach(function(cb) { cb.checked = false; });
        var checkAll = document.getElementById('checkAllJadwalSeni');
        if (checkAll) checkAll.checked = false;
        updateCounter();
    };
});
</script>

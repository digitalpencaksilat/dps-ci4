<?php $isEdit = ($mode ?? 'create') === 'edit'; ?>
<div class="row g-3">
    <?php if (! $isEdit) : ?>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Atlet :</label>
            <select name="id_pendaftar" class="form-select rounded-4 js-tanding-pendaftar" required>
                <option value="">Pilih peserta</option>
                <?php foreach (($pendaftarOptions ?? []) as $item) : ?>
                    <option value="<?= esc((string) $item->id_pendaftar) ?>"><?= esc($item->nama_kontingen . ' - ' . $item->nama_pendaftar . ' (' . $item->berat_badan . ' Kg)') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="col-md-<?= $isEdit ? '8' : '6' ?>">
        <label class="form-label fw-semibold">Kategori :</label>
        <?php $selectedKompetisi = old('id_kompetisi_tanding', $row->id_kompetisi_tanding ?? ''); ?>
        <select name="id_kompetisi_tanding" class="form-select rounded-4 js-tanding-kompetisi" data-selected="<?= esc((string) $selectedKompetisi) ?>" required>
            <option value=""><?= $isEdit ? 'Pilih kategori' : 'Pilih peserta terlebih dahulu' ?></option>
            <?php if ($isEdit) : ?>
                <?php foreach (($kompetisiOptions ?? []) as $item) : ?>
                    <?php $label = trim(($item->nama_kategori_usia ?? '') . ' ' . ($item->jenis_kelamin ?? '') . ' kelas ' . ($item->label ?? '') . ' (' . ($item->berat_minimal ?? '-') . ' kg - ' . ($item->berat_maksimal ?? '-') . ' kg)'); ?>
                    <option value="<?= esc((string) $item->id_kompetisi_tanding) ?>" <?= (string) $selectedKompetisi === (string) $item->id_kompetisi_tanding ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Keterangan</label>
        <textarea name="keterangan" class="form-control rounded-4" rows="2"><?= old('keterangan', $row->keterangan ?? '') ?></textarea>
    </div>
</div>

<script>
    (() => {
        const form = document.currentScript.closest('form');
        const pendaftar = form?.querySelector('.js-tanding-pendaftar');
        const kompetisi = form?.querySelector('.js-tanding-kompetisi');
        const selected = kompetisi?.dataset.selected || '';
        const fill = (items) => {
            if (!kompetisi) return;
            kompetisi.innerHTML = '<option value="">Pilih kategori</option>';
            if (!Array.isArray(items) || items.length === 0) {
                kompetisi.innerHTML += '<option disabled>Kategori tidak ditemukan</option>';
                return;
            }
            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id_kompetisi_tanding;
                option.textContent = `${item.nama_kategori_usia} - ${item.jenis_kelamin} kelas ${item.label} (${item.berat_minimal} kg - ${item.berat_maksimal} kg)`;
                if (item.disabled) {
                    option.disabled = true;
                    option.textContent += ` - ${item.message}`;
                }
                if (selected && String(selected) === String(item.id_kompetisi_tanding)) option.selected = true;
                kompetisi.appendChild(option);
            });
        };
        const load = async (id) => {
            if (!id) return fill([]);
            const response = await fetch(`<?= base_url('admin/sekretariat/kompetisi-tanding/by-pendaftar') ?>/${id}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            fill(response.ok ? await response.json() : []);
        };
        pendaftar?.addEventListener('change', () => load(pendaftar.value));
        if (pendaftar?.value) load(pendaftar.value);
    })();
</script>

<?php $isEdit = ($mode ?? 'create') === 'edit'; ?>
<?php $hideKontingen = (bool) ($hideKontingen ?? false); ?>
<div class="row g-3">
    <?php if (! $isEdit && $hideKontingen) : ?>
        <input type="hidden" name="id_kontingen" value="<?= esc((string) ($idKontingen ?? '')) ?>" class="js-seni-kontingen-fixed">
    <?php endif; ?>
    <?php if (! $isEdit && ! $hideKontingen) : ?>
        <div class="col-md-12">
            <label class="form-label fw-semibold">Kontingen :</label>
            <select name="id_kontingen" class="form-select rounded-4 js-seni-kontingen" required>
                <option value="">Pilih kontingen</option>
                <?php foreach (($kontingenRows ?? []) as $item) : ?>
                    <option value="<?= esc((string) $item->id_kontingen) ?>"><?= esc($item->nama_kontingen) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="col-md-<?= $isEdit ? '8' : '12' ?>">
        <label class="form-label fw-semibold">Kategori Seni :</label>
        <?php $selectedKompetisi = old('id_kompetisi_seni', $row->id_kompetisi_seni ?? ''); ?>
        <select name="id_kompetisi_seni" class="form-select rounded-4 js-seni-kompetisi" required>
            <option value="">Pilih kategori</option>
            <?php foreach (($kompetisiOptions ?? []) as $item) : ?>
                <?php $label = trim(($item->nama_kategori_usia ?? '') . ' ' . ($item->jenis_kelamin ?? '') . ' ' . ($item->jenis_seni ?? '') . ' ' . ($item->nama_seni ?? '') . ' Pool ' . ($item->nomor_pool ?? '-')); ?>
                <option value="<?= esc((string) $item->id_kompetisi_seni) ?>" data-jenis-seni="<?= esc((string) ($item->jenis_seni ?? '')) ?>" data-jumlah-peserta="<?= esc((string) ($item->jumlah_peserta ?? 0)) ?>" <?= ! empty($item->disabled) ? 'disabled' : '' ?> <?= (string) $selectedKompetisi === (string) $item->id_kompetisi_seni ? 'selected' : '' ?>><?= esc($label . (! empty($item->message) ? ' - ' . $item->message : '')) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if (! $isEdit) : ?>
        <div class="col-12">
            <label class="form-label fw-semibold">Atlet :</label>
            <div class="border rounded-4 p-3 js-seni-pendaftar-list" style="max-height: 25vh; overflow-y: auto;">Pilih kategori seni terlebih dahulu untuk memuat atlet.</div>
            <div class="form-text text-danger js-seni-help"></div>
        </div>
    <?php endif; ?>
    <div class="col-12">
        <label class="form-label fw-semibold">Keterangan Senjata (untuk kategori ganda)</label>
        <textarea name="keterangan" class="form-control rounded-4" rows="2"><?= old('keterangan', $row->keterangan ?? '') ?></textarea>
    </div>
</div>

<?php if (! $isEdit) : ?>
<script>
    (() => {
        const form = document.currentScript.closest('form');
        const kontingen = form?.querySelector('.js-seni-kontingen');
        const kompetisi = form?.querySelector('.js-seni-kompetisi');
        const list = form?.querySelector('.js-seni-pendaftar-list');
        const help = form?.querySelector('.js-seni-help');
        const submit = form?.querySelector('[type="submit"]');
        const fixedKontingen = form?.querySelector('.js-seni-kontingen-fixed')?.value || <?= json_encode((string) ($idKontingen ?? '')) ?>;
        const strictTypes = ['tunggal', 'ganda', 'beregu', 'solo kreatif', 'perorangan', 'berpasangan', 'berkelompok'];
        const validCount = () => {
            const selected = list?.querySelectorAll('input[name="id_pendaftar[]"]:checked').length || 0;
            const option = kompetisi?.options[kompetisi.selectedIndex];
            const required = Number(option?.dataset.jumlahPeserta || 0);
            const strict = strictTypes.includes(String(option?.dataset.jenisSeni || '').toLowerCase());
            const valid = required > 0 && (strict ? selected === required : selected >= required);
            if (submit) submit.disabled = !valid;
            if (help) help.textContent = required > 0 ? `${strict ? 'Wajib tepat' : 'Minimal'} ${required} atlet. Terpilih ${selected}.` : '';
        };
        const load = async () => {
            const idKontingen = kontingen?.value || fixedKontingen;
            const idKompetisi = kompetisi?.value || '';
            if (!idKontingen || !idKompetisi || !list) {
                if (list) list.textContent = idKontingen ? 'Pilih kategori seni terlebih dahulu untuk memuat atlet.' : 'Pilih kontingen dan kategori seni terlebih dahulu.';
                validCount();
                return;
            }
            list.textContent = 'Memuat atlet...';
            const response = await fetch(`<?= base_url('admin/sekretariat/pendaftar/by-kompetisi-seni') ?>/${idKompetisi}/${idKontingen}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            const items = response.ok ? await response.json() : [];
            list.innerHTML = '';
            if (!Array.isArray(items) || items.length === 0) {
                list.textContent = 'Tidak ada atlet valid untuk kategori ini.';
                validCount();
                return;
            }
            items.forEach((item) => {
                const label = document.createElement('label');
                label.className = 'd-block mb-2';
                label.innerHTML = `<input type="checkbox" name="id_pendaftar[]" value="${item.id_pendaftar}" class="me-2">${item.nama_pendaftar} (${item.jenis_kelamin})`;
                list.appendChild(label);
            });
            list.querySelectorAll('input').forEach((input) => input.addEventListener('change', validCount));
            validCount();
        };
        kontingen?.addEventListener('change', load);
        kompetisi?.addEventListener('change', load);
        if (fixedKontingen && kompetisi?.value) load();
        if (submit) submit.disabled = true;
    })();
</script>
<?php endif; ?>

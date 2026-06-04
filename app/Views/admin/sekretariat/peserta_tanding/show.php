<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div>
            <p class="eyebrow mb-1">Detail Peserta Tanding</p>
            <h3 class="section-title h4 mb-0"><?= esc($row->nama_pendaftar) ?></h3>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#editPesertaTandingModal">Edit Kategori</button>
            <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#pindahPoolTandingModal">Pindah Pool</button>
            <form method="post" action="<?= base_url('admin/sekretariat/peserta-tanding/' . $row->id_peserta_tanding . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Hapus peserta tanding?', 'Peserta akan keluar dari kategori tanding.', 'Hapus')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger rounded-pill px-4">Hapus</button>
            </form>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6"><div class="placeholder-stat"><div class="small muted-copy">Kontingen</div><div class="fw-semibold"><?= esc($row->nama_kontingen) ?></div></div></div>
        <div class="col-md-6"><div class="placeholder-stat"><div class="small muted-copy">Kategori</div><div class="fw-semibold"><?= esc(trim(((string) ($row->nama_kategori_usia ?? '')) . ' ' . ((string) ($row->label ?? '')))) ?></div></div></div>
        <div class="col-md-4"><div class="placeholder-stat"><div class="small muted-copy">Pool</div><div class="fw-semibold"><?= esc((string) ($row->nomor_pool ?? '-')) ?></div></div></div>
        <div class="col-md-4"><div class="placeholder-stat"><div class="small muted-copy">Bagan</div><div class="fw-semibold"><?= esc((string) ($row->nomor_bagan ?? '-')) ?></div></div></div>
        <div class="col-md-4"><div class="placeholder-stat"><div class="small muted-copy">Status Bayar</div><div class="fw-semibold"><?= esc((string) ($row->status_pembayaran ?? 'belum ada')) ?></div></div></div>
    </div>
</section>

<div class="modal fade" id="editPesertaTandingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('admin/sekretariat/peserta-tanding/' . $row->id_peserta_tanding . '/update') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Edit Peserta Tanding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?= view('admin/sekretariat/peserta_tanding/_form', ['mode' => 'edit', 'row' => $row, 'kompetisiOptions' => $kompetisiOptions ?? []]) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="pindahPoolTandingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('admin/sekretariat/peserta-tanding/' . $row->id_peserta_tanding . '/update') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Pindah Pool Tanding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="muted-copy small">Hanya menampilkan pool dari kelas <?= esc($row->label ?? '-') ?> kategori <?= esc($row->nama_kategori_usia ?? '-') ?>.</p>
                <label class="form-label fw-semibold">Pool Tujuan</label>
                <select name="id_kompetisi_tanding" class="form-select rounded-4" required>
                    <?php foreach (($poolOptions ?? []) as $item) : ?>
                        <?php $label = 'Pool ' . ($item->nomor_pool ?? '-') . ' - ' . ($item->jumlah_peserta_tanding ?? 0) . '/' . ($item->max_peserta ?? 0) . ' peserta'; ?>
                        <option value="<?= esc((string) $item->id_kompetisi_tanding) ?>" <?= (int) $row->id_kompetisi_tanding === (int) $item->id_kompetisi_tanding ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label fw-semibold mt-3">Keterangan</label>
                <textarea name="keterangan" class="form-control rounded-4" rows="2"><?= esc($row->keterangan ?? '') ?></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Pindahkan</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?php if (! empty($openModal)) : ?>
<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById(<?= json_encode((string) $openModal) ?>);
        if (modalElement && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    });
</script>
<?= $this->endSection() ?>
<?php endif; ?>

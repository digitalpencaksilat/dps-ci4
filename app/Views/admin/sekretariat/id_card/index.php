<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Tools',
    'title' => 'Pencetakan ID Card',
    'description' => 'Cetak ID Card peserta tanding dan seni. Upload background, atur tata letak, dan cetak batch per kontingen atau per peserta.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        [
            'tag' => 'button',
            'label' => 'Upload Background',
            'class' => 'btn-outline-secondary',
            'attrs' => [
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#modalUploadBackground',
            ],
        ],
        [
            'tag' => 'a',
            'href' => base_url('admin/sekretariat/id-card/pengaturan-tata-letak/nama_atlet'),
            'label' => 'Tata Letak',
            'class' => 'btn-outline-secondary',
        ],
        [
            'tag' => 'a',
            'href' => base_url('admin/sekretariat/id-card/cetak-per-kontingen'),
            'label' => 'Cetak Per Kontingen',
            'class' => 'btn-danger',
        ],
        [
            'tag' => 'a',
            'href' => base_url('admin/sekretariat/id-card/cetak-per-peserta'),
            'label' => 'Cetak Per Peserta',
            'class' => 'btn-outline-danger',
        ],
    ],
]) ?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="admin-card p-3 text-center">
            <h5 class="mb-2">Peserta Tanding</h5>
            <span class="fs-3 fw-bold text-danger"><?= esc((string) ($totalTanding ?? 0)) ?></span>
            <p class="text-muted small mb-0">kartu</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card p-3 text-center">
            <h5 class="mb-2">Peserta Seni</h5>
            <span class="fs-3 fw-bold text-danger"><?= esc((string) ($totalSeni ?? 0)) ?></span>
            <p class="text-muted small mb-0">kartu</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card p-3 text-center">
            <h5 class="mb-2">Background</h5>
            <?php if ($hasBackground ?? false) : ?>
                <span class="badge bg-success fs-6">Tersedia</span>
            <?php else : ?>
                <span class="badge bg-secondary fs-6">Belum diunggah</span>
            <?php endif; ?>
            <p class="text-muted small mb-0">atlet.png</p>
        </div>
    </div>
</div>

<section class="admin-card">
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kontingen</th>
                        <th class="text-end">Tanding</th>
                        <th class="text-end">Seni</th>
                        <th class="text-end">Total Kartu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($kontingenRows ?? []) as $row) : ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($row->nama_kontingen ?? '-') ?></td>
                            <td class="text-end"><?= esc((string) ($row->jml_tanding ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($row->jml_seni ?? 0)) ?></td>
                            <td class="text-end fw-bold"><?= esc((string) ((int) ($row->jml_tanding ?? 0) + (int) ($row->jml_seni ?? 0))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="modal fade" id="modalUploadBackground" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= base_url('admin/sekretariat/id-card/upload-background') ?>" method="post" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Upload Background ID Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="id_card_file" class="form-label">File PNG (max 3 MB)</label>
                    <input type="file" class="form-control" id="id_card_file" name="id_card" accept="image/png" required>
                    <div class="form-text">Hanya file PNG. Akan disimpan sebagai atlet.png.</div>
                </div>
                <?php if ($hasBackground ?? false) : ?>
                    <div class="alert alert-info small mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i> Background sudah ada. Upload baru akan menimpa yang lama.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill">Upload</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

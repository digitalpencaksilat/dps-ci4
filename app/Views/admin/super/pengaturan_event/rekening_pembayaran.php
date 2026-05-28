<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Event',
    'title' => 'Rekening Pembayaran',
    'description' => 'Kelola rekening pembayaran yang ditampilkan ke kontingen. Data disimpan ke <code>site_builder_settings</code> dengan key <code>rekening_pembayaran_accounts</code>.',
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/dashboard-pengaturan-event'),
            'label' => 'Kembali',
            'class' => 'btn-outline-secondary',
        ],
        [
            'tag' => 'button',
            'label' => 'Tambah Rekening',
            'class' => 'btn-danger',
            'attrs' => [
                'id' => 'btnTambahRekening',
            ],
        ],
    ],
]) ?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/rekening-pembayaran/update') ?>" method="post" id="formRekeningPembayaran">
        <?= csrf_field() ?>

        <div id="rekeningRows" class="vstack gap-3">
            <?php foreach (($accounts ?? []) as $index => $acc) : ?>
                <div class="admin-card rekening-row" data-rekening-row>
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="h5 mb-1">Rekening <span data-row-number><?= esc((string) ($index + 1)) ?></span></h3>
                            <div class="muted-copy small">Jika <b>Active</b> tidak dicentang, rekening tidak ditampilkan ke kontingen.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <div class="form-check admin-check">
                                <input class="form-check-input" type="checkbox" value="1" data-field="active" <?= ! empty($acc['active']) ? 'checked' : '' ?>>
                                <label class="form-check-label">Active</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-remove-row>Hapus</button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Nama Bank</label>
                            <input class="form-control" type="text" data-field="bank_name" value="<?= esc((string) ($acc['bank_name'] ?? ''), 'attr') ?>" maxlength="50">
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Nama Pemilik</label>
                            <input class="form-control" type="text" data-field="bank_account_name" value="<?= esc((string) ($acc['bank_account_name'] ?? ''), 'attr') ?>" maxlength="100">
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Nomor Rekening</label>
                            <input class="form-control" type="text" data-field="bank_account_number" value="<?= esc((string) ($acc['bank_account_number'] ?? ''), 'attr') ?>" maxlength="50">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-start mt-3 <?= ($accounts ?? []) === [] ? '' : 'd-none' ?>" id="emptyRekeningState">
            <p class="muted-copy mb-0">Belum ada rekening pembayaran. Silakan tambahkan rekening.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Rekening</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>

<template id="rekeningRowTemplate">
    <div class="admin-card rekening-row" data-rekening-row>
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div>
                <h3 class="h5 mb-1">Rekening <span data-row-number></span></h3>
                <div class="muted-copy small">Jika <b>Active</b> tidak dicentang, rekening tidak ditampilkan ke kontingen.</div>
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div class="form-check admin-check">
                    <input class="form-check-input" type="checkbox" value="1" data-field="active" checked>
                    <label class="form-check-label">Active</label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-remove-row>Hapus</button>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <label class="form-label">Nama Bank</label>
                <input class="form-control" type="text" data-field="bank_name" maxlength="50">
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label">Nama Pemilik</label>
                <input class="form-control" type="text" data-field="bank_account_name" maxlength="100">
            </div>
            <div class="col-12 col-lg-4">
                <label class="form-label">Nomor Rekening</label>
                <input class="form-control" type="text" data-field="bank_account_number" maxlength="50">
            </div>
        </div>
    </div>
</template>

<script>
    (() => {
        const form = document.getElementById('formRekeningPembayaran');
        const rows = document.getElementById('rekeningRows');
        const template = document.getElementById('rekeningRowTemplate');
        const emptyState = document.getElementById('emptyRekeningState');

        const syncRows = () => {
            const rowList = Array.from(rows.querySelectorAll('[data-rekening-row]'));
            rowList.forEach((row, index) => {
                row.querySelector('[data-row-number]').textContent = String(index + 1);
                row.querySelectorAll('[data-field]').forEach((input) => {
                    input.name = `accounts[${index}][${input.dataset.field}]`;
                });
            });
            emptyState?.classList.toggle('d-none', rowList.length > 0);
        };

        const addRow = () => {
            if (!template || !rows) return;
            rows.appendChild(template.content.cloneNode(true));
            syncRows();
        };

        document.getElementById('btnTambahRekening')?.addEventListener('click', addRow);

        rows?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-row]');
            if (!button) return;
            button.closest('[data-rekening-row]')?.remove();
            syncRows();
        });

        form?.addEventListener('submit', syncRows);
        syncRows();
    })();
</script>
<?= $this->endSection() ?>

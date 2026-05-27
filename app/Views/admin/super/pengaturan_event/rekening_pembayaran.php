<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Event</p>
            <h2 class="section-title h3 mb-3">Rekening Pembayaran</h2>
            <p class="muted-copy mb-0">Atur rekening pembayaran dan QR code. Data disimpan ke <code>site_builder_settings</code>, dan QR disimpan di <code>public/uploads/qrcode-pembayaran</code>.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-self-start">
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-light rounded-pill">Kembali ke Dashboard</a>
        </div>
    </div>
</section>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/rekening-pembayaran/update') ?>" method="post" enctype="multipart/form-data" class="row g-4">
        <?= csrf_field() ?>

        <?php foreach (($accounts ?? []) as $acc) : ?>
            <?php
            $key = (string) ($acc['key'] ?? '');
            $qr = (string) ($acc['qrcode'] ?? '');
            ?>
            <div class="col-12">
                <div class="admin-card" style="background: rgba(255,255,255,0.02);">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="h5 mb-1"><?= esc(strtoupper($key)) ?></h3>
                            <div class="muted-copy small">Jika <b>Active</b> tidak dicentang, rekening tidak ditampilkan ke kontingen.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check admin-check">
                                <input class="form-check-input" type="checkbox" value="1" id="<?= esc($key . '_active') ?>" name="<?= esc($key . '_active') ?>" <?= (bool) ($acc['active'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="<?= esc($key . '_active') ?>">Active</label>
                            </div>
                            <div class="form-check admin-check">
                                <input class="form-check-input" type="checkbox" value="1" id="<?= esc($key . '_display_qrcode') ?>" name="<?= esc($key . '_display_qrcode') ?>" <?= (bool) ($acc['display_qrcode'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="<?= esc($key . '_display_qrcode') ?>">Tampilkan QR</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="<?= esc($key . '_bank_name') ?>">Nama Bank</label>
                            <input class="form-control" type="text" id="<?= esc($key . '_bank_name') ?>" name="<?= esc($key . '_bank_name') ?>" value="<?= esc((string) ($acc['bank_name'] ?? '')) ?>">
                            <?php if (! empty(($errors ?? [])[$key . '_bank_name'])) : ?>
                                <div class="form-text text-danger mt-1"><?= esc((string) $errors[$key . '_bank_name']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="<?= esc($key . '_bank_account_name') ?>">Nama Pemilik</label>
                            <input class="form-control" type="text" id="<?= esc($key . '_bank_account_name') ?>" name="<?= esc($key . '_bank_account_name') ?>" value="<?= esc((string) ($acc['bank_account_name'] ?? '')) ?>">
                            <?php if (! empty(($errors ?? [])[$key . '_bank_account_name'])) : ?>
                                <div class="form-text text-danger mt-1"><?= esc((string) $errors[$key . '_bank_account_name']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label" for="<?= esc($key . '_bank_account_number') ?>">Nomor Rekening</label>
                            <input class="form-control" type="text" id="<?= esc($key . '_bank_account_number') ?>" name="<?= esc($key . '_bank_account_number') ?>" value="<?= esc((string) ($acc['bank_account_number'] ?? '')) ?>">
                            <?php if (! empty(($errors ?? [])[$key . '_bank_account_number'])) : ?>
                                <div class="form-text text-danger mt-1"><?= esc((string) $errors[$key . '_bank_account_number']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label" for="<?= esc($key . '_qrcode') ?>">Upload QR Code (PNG/JPG)</label>
                            <input class="form-control" type="file" id="<?= esc($key . '_qrcode') ?>" name="<?= esc($key . '_qrcode') ?>">
                            <?php if (! empty(($errors ?? [])[$key . '_qrcode'])) : ?>
                                <div class="form-text text-danger mt-1"><?= esc((string) $errors[$key . '_qrcode']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="muted-copy small mb-1">QR saat ini</div>
                            <?php if ($qr !== '') : ?>
                                <a href="<?= esc($qr) ?>" target="_blank" rel="noopener">
                                    <img src="<?= esc($qr) ?>" alt="QR <?= esc($key) ?>" class="img-fluid rounded" style="max-height: 160px; object-fit: contain; background: rgba(255,255,255,0.05);">
                                </a>
                                <div class="small mt-2 text-break"><code><?= esc($qr) ?></code></div>
                            <?php else : ?>
                                <div class="muted-copy">Belum ada</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary rounded-pill">Simpan Rekening</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

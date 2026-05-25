<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<div class="glass-card mx-auto" style="max-width: 650px;">
    <div class="card-header-custom">
        <h5><i class="fas fa-shield-halved"></i> Keamanan & Akun Admin</h5>
    </div>
    <div class="card-body p-4 p-md-5">

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-custom alert-dismissible fade show text-white mb-4" role="alert" style="background-color: #10b981;">
                <i class="fas fa-circle-check me-2"></i>
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-custom alert-dismissible fade show text-white mb-4" role="alert" style="background-color: var(--brand-primary);">
                <i class="fas fa-triangle-exclamation me-2"></i>
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="icon-box mb-3 shadow-sm">
            <i class="fas fa-lock"></i>
        </div>

        <h4 class="font-oswald mb-1">Reset Password BCrypt</h4>
        <p class="small text-muted mb-4">Ganti password akun admin secara langsung menggunakan enkripsi BCrypt.</p>

        <form action="<?= base_url('development/admin-utility/update-password') ?>" method="POST">
            <div class="mb-4">
                <label class="form-label font-oswald text-muted small uppercase">Pilih Akun Admin</label>
                <select name="id_admin" class="form-select form-select-custom" required>
                    <option value="" disabled selected>Pilih salah satu akun...</option>
                    <?php foreach ($admins as $admin) : ?>
                        <option value="<?= esc($admin->id_admin) ?>"><?= esc($admin->nama) ?> (<?= esc($admin->username) ?>) - <?= esc($admin->level) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label font-oswald text-muted small uppercase">Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;"><i class="fas fa-key text-muted"></i></span>
                    <input type="password" name="new_password" class="form-control form-control-custom border-start-0" placeholder="Masukkan password baru" required style="border-radius: 0 10px 10px 0 !important;">
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-custom btn-brand py-3">
                    <i class="fas fa-arrows-rotate me-2"></i> Update Password Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

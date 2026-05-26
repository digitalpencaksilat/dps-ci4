<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<div class="glass-card mx-auto" style="max-width: 600px;">
    <div class="card-header-custom text-center">
        <div class="icon-box mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
            <i class="fas fa-wand-magic-sparkles"></i>
        </div>
        <h2 class="font-oswald mb-1">Inisialisasi Database</h2>
        <p class="small text-muted mb-0">Siapkan database lokal Anda dengan struktur tabel terbaru secara otomatis.</p>
    </div>
    <div class="card-body p-4 p-md-5 pt-md-3">

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-custom d-flex align-items-center mb-4" style="background-color: #10b981; color: #fff;">
                <i class="fas fa-circle-check me-3 fa-lg"></i>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-custom d-flex align-items-center mb-4" style="background-color: var(--brand-primary); color: #fff;">
                <i class="fas fa-triangle-exclamation me-3 fa-lg"></i>
                <div><?= session()->getFlashdata('error') ?></div>
            </div>
        <?php endif; ?>

        <form role="form" id="dbForm" method="POST" action="<?= base_url('development/database-setup/process') ?>">
            <div class="mb-4">
                <label class="form-label font-oswald text-muted small uppercase tracking-wider">Nama Database Baru</label>
                <input type="text" class="form-control form-control-custom" placeholder="Contoh: db_dps_2024" name="database_name" required autocomplete="off">
            </div>

            <div class="d-grid">
                <button type="submit" id="submitBtn" class="btn btn-custom btn-brand py-3">
                    <i class="fas fa-play me-2"></i> Mulai Inisialisasi
                </button>
            </div>
        </form>

        <div class="mt-4 p-3 bg-light rounded-3 border">
            <p class="small text-muted mb-0" style="font-size: 0.75rem;">
                <i class="fas fa-circle-info me-1 text-warning"></i> Proses ini akan membuat database baru di server lokal dan mengunggah seluruh struktur tabel.
            </p>
        </div>
    </div>
</div>

<script>
    $('#dbForm').on('submit', function() {
        const $btn = $('#submitBtn');
        $btn.attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin me-2"></i> Sedang Memproses...');
    });
</script>

<?= $this->endSection() ?>

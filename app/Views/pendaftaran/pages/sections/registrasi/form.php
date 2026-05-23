<section class="py-5 bg-light" id="registrasi">
    <div class="container">

        <?php if (ci3_config_item('perbolehkan_kontingen_mendaftar', 'pendaftaran/akses_pendaftaran')) : ?>

            <div class="row justify-content-center">
                <div class="col-12">
                    <?= view('shared_components/notification') ?>
                </div>
                <div class="col-lg-12 col-xl-12">
                    <?= view('shared_components/kontingen/form_insert') ?>
                </div>
            </div>

        <?php else : ?>

            <!-- Tampilan Modern Pendaftaran Ditutup -->
            <div class="row justify-content-center align-items-center" style="min-height: 50vh;">
                <div class="col-md-8 col-lg-6 text-center">
                    <div class="card border-0 shadow-sm rounded-4 py-5 px-4">
                        <div class="card-body">
                            <!-- Ikon Lingkaran (Pastikan sudah meload Bootstrap Icons) -->
                            <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-4" style="width: 100px; height: 100px;">
                                <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                            </div>

                            <h3 class="fw-bold text-dark mb-3">Pendaftaran Telah Ditutup</h3>
                            <p class="text-muted mb-5 px-md-4">
                                Mohon maaf, saat ini periode pendaftaran kontingen sudah berakhir atau kuota telah terpenuhi. Terima kasih atas antusiasme Anda. Silakan pantau informasi selanjutnya.
                            </p>

                            <!-- Tombol Aksi -->
                            <div class="d-flex justify-content-center gap-3">
                                <a href="<?= base_url() ?>" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                                    <i class="bi bi-house-door me-2"></i> Kembali ke Beranda
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif ?>

    </div>
</section>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kontingen - <?= esc($eventName) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('fontawesome_6_css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/kontingen-theme.css') ?>">
</head>

<body class="login-body">
    <main class="container-fluid min-vh-100">
        <div class="row min-vh-100">
            <div class="col-lg-5 d-flex align-items-center justify-content-center px-4 px-lg-5 py-5 bg-white">
                <div class="auth-card w-100">
                    <div class="text-center mb-4">
                        <?php if (! empty($eventLogo)) : ?>
                            <img src="<?= esc($eventLogo) ?>" alt="<?= esc($eventName) ?>" class="auth-logo mb-3">
                        <?php endif; ?>
                        <p class="eyebrow mb-2">Portal Kontingen</p>
                        <h1 class="auth-title mb-2">Login Kontingen</h1>
                        <p class="text-muted mb-0">Masuk untuk melanjutkan proses pendaftaran, kategori, dan pembayaran.</p>
                    </div>

                    <?= view('shared_components/notification') ?>

                    <?php if ($allowLogin) : ?>
                        <form method="post" action="<?= base_url('kontingen/login') ?>" class="vstack gap-3">
                            <?= csrf_field() ?>
                            <div>
                                <label for="email_kontingen" class="form-label fw-semibold">Email Kontingen</label>
                                <input
                                    type="email"
                                    id="email_kontingen"
                                    name="email_kontingen"
                                    value="<?= old('email_kontingen') ?>"
                                    class="form-control form-control-lg rounded-4"
                                    required
                                >
                            </div>
                            <div>
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control form-control-lg rounded-4"
                                    required
                                >
                            </div>
                            <button type="submit" class="btn btn-brand btn-lg rounded-pill w-100">
                                <i class="fas fa-right-to-bracket me-2"></i>Masuk
                            </button>
                        </form>

                        <div class="text-center mt-4 small text-muted">
                            Belum punya akun?
                            <a href="<?= base_url('registrasi') ?>" class="text-brand fw-semibold text-decoration-none">Daftarkan kontingen</a>
                        </div>

                        <?php if ($allowForgotPassword) : ?>
                            <div class="text-center mt-2 small">
                                <span class="text-muted">Fitur lupa password akan dimigrasikan pada tahap berikutnya.</span>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div class="alert alert-warning border-0 rounded-4 mb-0">
                            Akses login kontingen sedang ditutup. Hubungi admin untuk informasi lebih lanjut.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-7 d-none d-lg-flex align-items-stretch p-4 login-hero-wrap">
                <section class="login-hero-card w-100">
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        <p class="eyebrow text-white-50">Digital Pencak Silat</p>
                        <h2 class="hero-title text-white">Kelola atlet, kategori tanding, kategori seni, dan pembayaran dalam satu panel modern.</h2>
                        <div class="hero-points row g-3 mt-4">
                            <div class="col-md-6">
                                <div class="hero-point-card">
                                    <i class="fas fa-users"></i>
                                    <span>Input peserta lebih terstruktur</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hero-point-card">
                                    <i class="fas fa-medal"></i>
                                    <span>Pemilihan kategori lebih jelas</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hero-point-card">
                                    <i class="fas fa-wallet"></i>
                                    <span>Pembayaran terpusat</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hero-point-card">
                                    <i class="fas fa-shield-heart"></i>
                                    <span>Fondasi CI4 untuk pengembangan lanjut</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script src="<?= online_asset('bootstrap_5_bundle_js') ?>"></script>
</body>

</html>

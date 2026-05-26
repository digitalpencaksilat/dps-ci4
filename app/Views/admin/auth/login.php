<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= esc($eventName) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('fontawesome_6_css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/auth.css') ?>">
</head>

<body class="admin-auth-body">
    <main class="container-fluid min-vh-100">
        <div class="row min-vh-100">
            <div class="col-lg-5 d-flex align-items-center justify-content-center px-4 px-lg-5 py-5 bg-white">
                <div class="admin-auth-card w-100">
                    <div class="text-center mb-4">
                        <?php if (! empty($eventLogo)) : ?>
                            <img src="<?= esc($eventLogo) ?>" alt="<?= esc($eventName) ?>" class="admin-auth-logo mb-3">
                        <?php endif; ?>
                        <p class="eyebrow mb-2">Portal Admin</p>
                        <h1 class="admin-auth-title mb-2">Masuk ke Panel Admin</h1>
                        <p class="text-muted mb-0">Akses cepat ke dashboard bendahara dan area kerja admin.</p>
                    </div>

                    <?= view('shared_components/notification') ?>

                    <form method="post" action="<?= base_url('admin') ?>" class="vstack gap-3">
                        <?= csrf_field() ?>
                        <div>
                            <label for="username" class="form-label fw-semibold">Username Admin</label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= old('username') ?>"
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
                        <button type="submit" class="btn btn-admin-brand btn-lg rounded-pill w-100">
                            <i class="fas fa-right-to-bracket me-2"></i>Masuk Admin
                        </button>
                    </form>

                    <div class="text-center mt-4 small text-muted">
                        Login kontingen tetap tersedia di
                        <a href="<?= base_url('pendaftaran/login') ?>" class="text-admin-brand fw-semibold text-decoration-none">portal kontingen</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 d-none d-lg-flex align-items-stretch p-4 admin-auth-hero-wrap">
                <section class="admin-auth-hero-card w-100">
                    <div class="admin-auth-hero-content">
                        <p class="eyebrow text-white-50">Digital Pencak Silat</p>
                         <h2 class="admin-auth-hero-title text-white">Panel admin fokus ke validasi pembayaran, rekap kontingen, dan alur kerja yang lebih cepat.</h2>
                        <div class="hero-points row g-3 mt-4">
                            <div class="col-md-6">
                                <div class="admin-auth-point-card">
                                    <i class="fas fa-wallet"></i>
                                    <span>Area bendahara lebih fokus</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-auth-point-card">
                                    <i class="fas fa-user-shield"></i>
                                    <span>Role admin lebih terkontrol</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-auth-point-card">
                                    <i class="fas fa-diagram-project"></i>
                                     <span>Rekap transaksi lebih rapi</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-auth-point-card">
                                    <i class="fas fa-shield-heart"></i>
                                     <span>Akses admin lewat `/admin` lebih singkat</span>
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

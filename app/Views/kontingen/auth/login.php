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

<body class="login-body kontingen-auth-centered">
    <main class="kontingen-auth-wrap">
        <div class="kontingen-auth-card card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <?php if (! empty($eventLogo)) : ?>
                        <img src="<?= esc($eventLogo) ?>" alt="<?= esc($eventName) ?>" class="auth-logo mb-3">
                    <?php endif; ?>
                    <h1 class="kontingen-auth-title">KONTINGEN PANEL</h1>
                    <p class="kontingen-auth-subtitle"><?= esc($eventName ?? 'Digital Pencak Silat') ?></p>
                </div>

                <?= view('shared_components/notification') ?>

                <?php if ($allowLogin) : ?>
                    <form method="post" action="<?= base_url('kontingen/login') ?>" id="loginForm">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="far fa-envelope"></i></span>
                                <input
                                    type="email"
                                    id="email_kontingen"
                                    name="email_kontingen"
                                    value="<?= old('email_kontingen') ?>"
                                    class="form-control"
                                    placeholder="Email Kontingen"
                                    required
                                    autofocus
                                    style="font-size: 0.95rem;"
                                >
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Password"
                                    required
                                    style="font-size: 0.95rem;"
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn btn-brand-login w-100">
                            Masuk Kontingen <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center mt-4 small text-muted">
                        Belum punya akun?
                        <a href="<?= base_url('registrasi') ?>" class="text-brand fw-semibold text-decoration-none">Daftarkan kontingen</a>
                    </div>

                    <?php if ($allowForgotPassword) : ?>
                        <div class="text-center mt-2 small">
                            <span class="text-muted">Lupa password? Hubungi admin untuk bantuan reset akses.</span>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="alert alert-warning border-0 rounded-4 mb-0">
                        Akses login kontingen sedang ditutup. Hubungi admin untuk informasi lebih lanjut.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <footer class="text-center mt-4 text-muted small">
            <span class="fw-bold text-brand">DIGITAL PENCAK SILAT</span> &copy; <?= date('Y') ?>
        </footer>
    </main>

    <script src="<?= online_asset('bootstrap_5_bundle_js') ?>"></script>
</body>

</html>

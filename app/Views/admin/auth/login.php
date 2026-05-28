<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - <?= esc($eventName) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('fontawesome_6_css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/auth.css') ?>">
</head>

<body class="admin-auth-body admin-auth-centered">
    <main class="admin-login-wrap">
        <div class="login-card">
            <div class="card-header-custom">
                <?php if (! empty($eventLogo)) : ?>
                    <img src="<?= esc($eventLogo) ?>" alt="<?= esc($eventName) ?>" class="logo-img">
                <?php endif; ?>
                <h1 class="app-title">ADMIN PANEL</h1>
                <p class="app-subtitle"><?= esc($eventName ?? 'Digital Pencak Silat') ?></p>
            </div>

            <div class="card-body-custom">
                <?= view('shared_components/notification') ?>

                <form method="post" action="<?= base_url('admin') ?>" id="loginForm">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="far fa-user"></i></span>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= old('username') ?>"
                                class="form-control"
                                placeholder="Username"
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

                    <button type="submit" class="btn btn-brand-login">
                        Masuk Admin <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted">Akses terbatas Administrator</small>
                </div>
            </div>
        </div>

        <footer class="text-center mt-4 text-muted small">
            <span class="fw-bold text-admin-brand">DIGITAL PENCAK SILAT</span> &copy; <?= date('Y') ?>
        </footer>
    </main>

    <script src="<?= online_asset('bootstrap_5_bundle_js') ?>"></script>
</body>

</html>

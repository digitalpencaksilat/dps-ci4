<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard Super Admin') ?> - <?= esc($eventName ?? 'Digital Pencak Silat') ?></title>
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('fontawesome_6_css') ?>">
</head>
<body class="bg-dark text-light">
    <main class="container py-5">
        <div class="card bg-black border-secondary text-light shadow-lg">
            <div class="card-body p-4 p-lg-5">
                <p class="text-uppercase text-secondary small mb-2">Portal Admin</p>
                <h1 class="h2 mb-3"><?= esc($title ?? 'Dashboard Super Admin') ?></h1>
                <p class="text-secondary mb-4">Landing minimal super admin sudah aktif agar alur login admin role ini tidak buntu. Area super admin penuh tetap menunggu migrasi modul lanjutan.</p>
                <div class="d-flex flex-wrap gap-3">
                    <span class="badge text-bg-danger rounded-pill px-3 py-2">Role aktif: super_admin</span>
                    <span class="badge text-bg-secondary rounded-pill px-3 py-2">User: <?= esc($adminName ?? 'Super Admin') ?></span>
                </div>
                <hr class="border-secondary my-4">
                <a href="<?= base_url('admin/logout') ?>" class="btn btn-outline-light rounded-pill">Logout</a>
            </div>
        </div>
    </main>
</body>
</html>

<?php
$code = (string) ($code ?? '500');
$title = $title ?? 'Terjadi Gangguan';
$message = $message ?? 'Terjadi kendala pada sistem. Silakan coba beberapa saat lagi.';
$actionUrl = $actionUrl ?? base_url('/');
$actionLabel = $actionLabel ?? 'Kembali ke Beranda';
$showHome = $showHome ?? true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= esc($code) ?> - <?= esc($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('fontawesome_6_css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/kontingen-theme.css') ?>">
    <style>
        .dps-error-shell {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(255, 215, 0, 0.18), transparent 28%),
                linear-gradient(160deg, #6b0000 0%, #c60000 45%, #1a1a1a 100%);
        }
        .dps-error-card {
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            color: #fff;
            max-width: 680px;
        }
        .dps-error-code { font-size: clamp(3rem, 12vw, 6rem); line-height: 1; }
        .dps-error-logo { width: 96px; height: 96px; object-fit: contain; }
    </style>
</head>
<body>
    <main class="dps-error-shell d-flex align-items-center justify-content-center px-3 py-5">
        <section class="dps-error-card text-center p-4 p-md-5 shadow-lg">
            <img src="<?= base_url('assets/images/brand/dps/logo.png') ?>" alt="DPS" class="dps-error-logo mb-3">
            <p class="eyebrow text-warning mb-2">Digital Pencak Silat</p>
            <h1 class="dps-error-code fw-bold mb-2"><?= esc($code) ?></h1>
            <h2 class="hero-title h3 mb-3"><?= esc($title) ?></h2>
            <p class="mb-4 text-white-50"><?= esc($message) ?></p>
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                <a href="<?= esc($actionUrl) ?>" class="btn btn-warning rounded-pill px-4 fw-semibold"><?= esc($actionLabel) ?></a>
                <?php if ($showHome) : ?>
                    <a href="<?= base_url('/') ?>" class="btn btn-outline-light rounded-pill px-4">Beranda</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>

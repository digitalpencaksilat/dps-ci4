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
    <style>
        :root {
            --brand-primary: #c60000;
            --brand-dark: #212529;
            --bg-color: #f4f6f9;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: var(--brand-dark);
            background: var(--bg-color);
        }

        .dps-error-shell {
            min-height: 100vh;
            background: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .dps-error-card {
            width: min(100%, 720px);
            border-radius: 28px;
            border: 1px solid rgba(33, 37, 41, 0.08);
            background: #fff;
            box-shadow: 0 20px 48px rgba(33, 37, 41, 0.1);
            padding: 2rem 1.75rem;
        }

        .dps-error-logo-wrap {
            width: 88px;
            height: 88px;
            border-radius: 22px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(198, 0, 0, 0.08);
        }

        .dps-error-logo {
            width: 62px;
            height: 62px;
            object-fit: contain;
        }

        .dps-error-eyebrow {
            margin-bottom: 0.35rem;
            color: var(--brand-primary);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .dps-error-code {
            margin-bottom: 0.3rem;
            font-family: 'Oswald', sans-serif;
            font-size: clamp(3rem, 11vw, 5.8rem);
            line-height: 1;
            letter-spacing: 0.03em;
            color: var(--brand-primary);
        }

        .dps-error-title {
            margin-bottom: 0.85rem;
            color: var(--brand-dark);
            font-family: 'Oswald', sans-serif;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .dps-error-message {
            margin: 0 auto 1.5rem;
            max-width: 580px;
            color: #6c757d;
            line-height: 1.75;
        }

        .btn-error-primary {
            border-radius: 999px;
            border: 2px solid var(--brand-primary);
            background: var(--brand-primary);
            color: #fff;
            font-family: 'Oswald', sans-serif;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.62rem 1.4rem;
        }

        .btn-error-primary:hover,
        .btn-error-primary:focus {
            background: #a00000;
            border-color: #a00000;
            color: #fff;
        }

        .btn-error-secondary {
            border-radius: 999px;
            border: 1px solid rgba(33, 37, 41, 0.16);
            color: var(--brand-dark);
            background: #fff;
            padding: 0.62rem 1.4rem;
        }

        .btn-error-secondary:hover,
        .btn-error-secondary:focus {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
            background: rgba(198, 0, 0, 0.04);
        }

        @media (max-width: 575.98px) {
            .dps-error-card {
                border-radius: 22px;
                padding: 1.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <main class="dps-error-shell">
        <section class="dps-error-card text-center">
            <div class="dps-error-logo-wrap">
                <img src="<?= base_url('assets/images/brand/dps/logo.png') ?>" alt="DPS" class="dps-error-logo">
            </div>
            <p class="dps-error-eyebrow">Digital Pencak Silat</p>
            <h1 class="dps-error-code fw-bold"><?= esc($code) ?></h1>
            <h2 class="dps-error-title h3"><?= esc($title) ?></h2>
            <p class="dps-error-message"><?= esc($message) ?></p>
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                <a href="<?= esc($actionUrl) ?>" class="btn btn-error-primary fw-semibold"><?= esc($actionLabel) ?></a>
                <?php if ($showHome) : ?>
                    <a href="<?= base_url('/') ?>" class="btn btn-error-secondary">Beranda</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>

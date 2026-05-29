<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Landing page pendaftaran <?= esc(get_instance()->get_setting('event_name') ?? 'Digital Pencak Silat') ?>">
    <meta name="theme-color" content="#c60000">
    <link rel="icon" type="image/png" href="<?= get_instance()->get_setting('event_logo', 'pendaftaran/gambar_dan_juknis') ?>">

    <title><?= esc(get_instance()->get_setting('event_name') ?? 'Digital Pencak Silat') ?></title>

    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('fontawesome_6_css') ?>">

    <style>
        :root {
            --brand-primary: #c60000;
            --brand-secondary: #ffd700;
            --brand-dark: #1a1a1a;
            --brand-soft: #fff6f6;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #222;
            background: #fffaf9;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .navbar-brand,
        .display-4,
        .display-5 {
            font-family: 'Oswald', sans-serif;
            text-transform: uppercase;
        }

        #mainNav {
            background: rgba(26, 26, 26, 0.82);
            backdrop-filter: blur(8px);
            transition: padding 0.2s ease;
            padding: 0.65rem 0;
        }

        #mainNav .nav-link {
            color: rgba(255,255,255,0.82);
            font-weight: 600;
            margin-left: 0.5rem;
        }

        #mainNav .nav-link:hover {
            color: #fff;
        }

        .landing-hero {
            position: relative;
            min-height: 100vh;
            padding: 7.5rem 0 4rem;
            background: url('<?= base_url('assets/images/landing/landing-hero-bg.jpg') ?>') center center / cover no-repeat;
            overflow: hidden;
        }

        .hero-backdrop {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top right, rgba(255, 215, 0, 0.18), transparent 25%),
                linear-gradient(180deg, rgba(0,0,0,0.78) 0%, rgba(0,0,0,0.72) 55%, rgba(18,18,18,0.82) 100%);
        }

        .hero-content-wrap {
            z-index: 2;
        }

        .landing-subtitle {
            display: inline-flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: #ffd166;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .landing-title {
            font-size: clamp(2.6rem, 6vw, 4.6rem);
            line-height: 1.05;
            text-shadow: 0 6px 24px rgba(0,0,0,0.28);
        }

        .landing-lead {
            font-size: 1.08rem;
            line-height: 1.8;
            max-width: 92%;
            padding-left: 1.25rem;
            border-left: 4px solid var(--brand-primary);
            color: rgba(255,255,255,0.92);
        }

        .hero-poster-shell {
            max-width: 390px;
            border-radius: 24px;
            overflow: hidden;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            backdrop-filter: blur(6px);
        }

        .hero-poster-image {
            width: 100%;
            max-height: 620px;
            object-fit: cover;
            display: block;
        }

        .poster-fallback-landing {
            min-height: 540px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            color: #fff;
            background: linear-gradient(160deg, rgba(198,0,0,0.68), rgba(40,0,0,0.86));
        }

        .poster-fallback-landing i {
            font-size: 3rem;
            color: var(--brand-secondary);
        }

        .countdown-caption {
            color: #ffd166;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.85rem;
        }

        .countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 100px));
            gap: 1rem;
        }

        .countdown-box {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 18px;
            padding: 1rem 0.5rem;
            text-align: center;
            box-shadow: 0 10px 24px rgba(0,0,0,0.16);
        }

        .countdown-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
        }

        .countdown-label {
            display: block;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.78);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .countdown-live-alert {
            grid-column: 1 / -1;
            font-size: clamp(0.95rem, 2.3vw, 1.1rem);
            line-height: 1.45;
            padding: 0.95rem 1rem;
            white-space: normal;
            word-break: break-word;
        }

        .hero-cta-group .btn {
            min-height: 52px;
        }

        .hero-section {
            padding-top: 7rem;
            padding-bottom: 4rem;
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(255, 215, 0, 0.18), transparent 24%),
                linear-gradient(135deg, #fff5f5 0%, #ffffff 55%, #fff8e8 100%);
        }

        .landing-highlight-card,
        .feature-card,
        .landing-stat-card {
            border-radius: 24px;
        }

        .landing-highlight-card {
            background: #ffffff;
            padding: 2rem;
            border: 1px solid rgba(198, 0, 0, 0.08);
        }

        .icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--brand-soft);
            color: var(--brand-primary);
            font-size: 1.6rem;
        }

        .landing-stat-card {
            background: rgba(255, 255, 255, 0.85);
            padding: 1rem 1.1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        }

        .landing-stat-card small {
            display: block;
            color: #6c757d;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.06em;
        }

        .landing-stat-card strong {
            font-size: 1rem;
        }

        .feature-card {
            background: #fff;
            padding: 1.5rem;
            border: 1px solid rgba(198, 0, 0, 0.08);
            box-shadow: 0 10px 28px rgba(26, 26, 26, 0.05);
        }

        .feature-card h3 {
            font-size: 1.2rem;
            margin-top: 1rem;
            margin-bottom: 0.75rem;
        }

        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .feature-box {
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(198, 0, 0, 0.08);
        }

        .feature-badge,
        .info-icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--brand-primary);
            color: #fff;
            flex-shrink: 0;
        }

        .info-card {
            background: #fff;
            border-radius: 24px;
            padding: 1.5rem;
            border: 1px solid rgba(198,0,0,0.08);
            box-shadow: 0 12px 28px rgba(26,26,26,0.05);
            text-align: center;
        }

        .info-card .info-icon-wrapper {
            margin: 0 auto 1rem;
        }

        .section-title h2 {
            font-size: clamp(2rem, 4vw, 2.9rem);
            margin-bottom: 0.75rem;
            position: relative;
            display: inline-block;
            padding-bottom: 0.95rem;
        }

        .section-title h2::before,
        .section-title h2::after {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 999px;
        }

        .section-title h2::before {
            bottom: 0;
            width: 88px;
            height: 5px;
            background: linear-gradient(90deg, #7a0d14, #c60000);
        }

        .section-title h2::after {
            bottom: -8px;
            width: 42px;
            height: 4px;
            background: linear-gradient(90deg, #ffd166, #ffe09a);
        }

        .bg-pattern-soft {
            background:
                radial-gradient(circle at top left, rgba(198,0,0,0.05), transparent 26%),
                linear-gradient(180deg, #fffaf9 0%, #ffffff 100%);
        }

        .bg-dot-white-section {
            position: relative;
            overflow: hidden;
        }

        .bg-dot-white-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.85) 1px, transparent 1px);
            background-size: 22px 22px;
            opacity: 0.38;
            pointer-events: none;
        }

        .bg-dot-white-section > .container {
            position: relative;
            z-index: 1;
        }

        .bg-dot-dark-section {
            position: relative;
            overflow: hidden;
            background: #fff;
        }

        .bg-dot-dark-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(20,20,20,0.18) 1px, transparent 1px);
            background-size: 22px 22px;
            opacity: 0.55;
            pointer-events: none;
        }

        .bg-dot-dark-section > .container {
            position: relative;
            z-index: 1;
        }

        .category-card-modern {
            position: relative;
            overflow: hidden;
            min-height: 370px;
            border-radius: 24px;
            box-shadow: 0 16px 36px rgba(26,26,26,0.10);
        }

        .category-img-modern {
            width: 100%;
            height: 100%;
            min-height: 370px;
            object-fit: cover;
            transition: transform 0.45s ease;
        }

        .category-card-modern:hover .category-img-modern {
            transform: scale(1.05);
        }

        .category-overlay-modern {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 1.5rem;
            color: #fff;
            background: linear-gradient(180deg, rgba(0,0,0,0.12), rgba(0,0,0,0.74));
        }

        .category-overlay-modern i {
            font-size: 1.4rem;
            margin-bottom: 0.75rem;
            color: #ffd166;
        }

        .category-overlay-modern h4 {
            margin-bottom: 0.35rem;
        }

        .timeline-modern {
            display: grid;
            gap: 1.5rem;
            max-width: 880px;
            margin: 0 auto;
        }

        .timeline-item-modern {
            background: #fff;
            border: 1px solid rgba(198,0,0,0.10);
            border-radius: 0 0 26px 26px;
            padding: 1.5rem;
            box-shadow: 0 14px 30px rgba(26,26,26,0.06);
            position: relative;
        }

        .timeline-item-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            border-radius: 0;
            background: linear-gradient(90deg, #7a0d14, #c60000 65%, #ffd166);
        }

        .timeline-date-modern {
            display: inline-block;
            margin-bottom: 0.65rem;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--brand-primary);
        }

        .landing-closing-cta {
            background: linear-gradient(135deg, #4b0d14 0%, #8a1019 55%, #c60000 100%);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .landing-checks li {
            padding: 0.4rem 0;
        }

        footer {
            background: rgba(26, 26, 26, 0.94);
            color: rgba(255,255,255,0.84);
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 1.5rem 0;
            position: relative;
            z-index: 2;
            text-align: center;
        }

        footer .text-secondary {
            color: rgba(255,255,255,0.78) !important;
        }

        @media (max-width: 991.98px) {
            .landing-hero {
                padding-top: 6.75rem;
                padding-bottom: 3rem;
                min-height: auto;
            }

            .landing-lead {
                max-width: 100%;
            }

            .countdown-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .hero-poster-shell {
                max-width: 340px;
            }
        }

        @media (max-width: 767.98px) {
            .landing-title {
                font-size: 2.35rem;
            }

            .landing-lead {
                font-size: 1rem;
                padding-left: 1rem;
            }

            .hero-cta-group .btn {
                width: 100%;
            }

            .category-card-modern,
            .category-img-modern {
                min-height: 320px;
            }
        }
    </style>
</head>

<body>
    <?= view('pendaftaran/components/topnav') ?>
    <?= view($main_view) ?>
    <?= view('pendaftaran/components/footer') ?>

    <script src="<?= online_asset('bootstrap_5_bundle_js') ?>"></script>
    <script>
        const yearElement = document.getElementById('year');
        if (yearElement) {
            yearElement.textContent = new Date().getFullYear();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const eventConfig = <?= json_encode($event['countdown'] ?: '') ?>;
            const countdownElement = document.getElementById('landingCountdown');
            const daysElement = document.getElementById('days');
            const hoursElement = document.getElementById('hours');
            const minutesElement = document.getElementById('minutes');
            const secondsElement = document.getElementById('seconds');
            const reveals = document.querySelectorAll('.reveal');

            const revealOnScroll = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, {
                threshold: 0.12,
            });

            reveals.forEach((element) => revealOnScroll.observe(element));

            if (countdownElement && daysElement && hoursElement && minutesElement && secondsElement && eventConfig) {
                const eventDate = new Date(eventConfig).getTime();

                const timer = setInterval(() => {
                    const now = new Date().getTime();
                    const distance = eventDate - now;

                    if (distance < 0) {
                        clearInterval(timer);
                        countdownElement.innerHTML = '<div class="alert alert-warning border-0 rounded-4 w-100 text-center mb-0 countdown-live-alert">Event sedang berlangsung!</div>';
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    daysElement.textContent = String(days).padStart(2, '0');
                    hoursElement.textContent = String(hours).padStart(2, '0');
                    minutesElement.textContent = String(minutes).padStart(2, '0');
                    secondsElement.textContent = String(seconds).padStart(2, '0');
                }, 1000);
            }
        });

        window.addEventListener('scroll', function() {
            const mainNav = document.getElementById('mainNav');
            if (!mainNav) {
                return;
            }

            mainNav.style.padding = window.scrollY > 50 ? '0.45rem 0' : '0.65rem 0';
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin Panel') ?> - <?= esc($eventName ?? 'Digital Pencak Silat') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('datatables_bs5_css') ?>">
    <link rel="stylesheet" href="<?= online_asset('datatables_responsive_css') ?>">
    <link rel="stylesheet" href="<?= online_asset('toastr_css') ?>">
    <link rel="stylesheet" href="<?= online_asset('fontawesome_6_css') ?>">
    <style>
        :root {
            --admin-bg: #fff6f7;
            --admin-bg-soft: #f6f7fb;
            --admin-surface: #ffffff;
            --admin-surface-soft: #fff0f1;
            --admin-surface-strong: #6f1018;
            --admin-border: rgba(198, 0, 0, 0.08);
            --admin-text: #20161a;
            --admin-muted: #7d6670;
            --admin-accent: #c60000;
            --admin-accent-dark: #8f0b14;
            --admin-accent-soft: rgba(198, 0, 0, 0.08);
            --admin-success: #0f9f6e;
            --admin-warning: #f59e0b;
            --admin-danger: #dc2626;
            --admin-shadow: 0 18px 46px rgba(74, 17, 23, 0.08);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(255, 215, 0, 0.12), transparent 24%),
                linear-gradient(180deg, var(--admin-bg) 0%, var(--admin-bg-soft) 100%);
            color: var(--admin-text);
            overflow-x: hidden;
        }

        .admin-shell {
            display: flex;
            min-height: 100vh;
            max-width: 100vw;
            overflow-x: hidden;
        }

        .admin-sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.52);
            backdrop-filter: blur(2px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 1040;
        }

        body.admin-sidebar-open {
            overflow: hidden;
        }

        body.admin-sidebar-open .admin-sidebar-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        .admin-sidebar {
            width: 310px;
            padding: 28px 20px;
            background:
                radial-gradient(circle at top right, rgba(255, 215, 0, 0.16), transparent 26%),
                linear-gradient(180deg, #7b0f17 0%, #510b11 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.34) transparent;
            z-index: 1030;
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 8px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.28);
            border-radius: 999px;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .admin-sidebar-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .admin-sidebar-close {
            display: none;
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .admin-brand img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.94);
            padding: 8px;
        }

        .admin-brand-title,
        .admin-page-title,
        .section-title {
            font-family: 'Oswald', sans-serif;
            letter-spacing: 0.04em;
        }

        .admin-brand-subtitle,
        .admin-section-label {
            color: rgba(255, 255, 255, 0.68);
        }

        .admin-topbar-meta,
        .muted-copy {
            color: var(--admin-muted);
        }

        .admin-section-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            padding: 0 14px;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 16px;
            color: rgba(255, 255, 255, 0.92);
            text-decoration: none;
            transition: 0.2s ease;
            border: 1px solid transparent;
        }

        .admin-nav-link:hover,
        .admin-nav-link.active {
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .admin-nav-link .label-block {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-nav-link .chevron {
            font-size: 0.72rem;
            transition: transform 0.2s ease;
        }

        .admin-nav-link[aria-expanded='true'] .chevron {
            transform: rotate(90deg);
        }

        .admin-submenu {
            margin-top: 8px;
            margin-left: 16px;
            padding-left: 14px;
            border-left: 1px solid rgba(255, 255, 255, 0.12);
        }

        .admin-submenu-inner {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .admin-submenu-link {
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.74);
        }

        .admin-submenu-link:hover,
        .admin-submenu-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.08);
        }

        .admin-main {
            flex: 1;
            min-width: 0;
            margin-left: 310px;
            padding: 28px;
            overflow-x: hidden;
        }

        .admin-topbar,
        .admin-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--admin-border);
            border-radius: 24px;
            box-shadow: var(--admin-shadow);
        }

        .admin-topbar {
            padding: 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .admin-card {
            padding: 24px;
        }

        .admin-sidebar .admin-card {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            box-shadow: none;
        }

        .hero-panel {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(255, 215, 0, 0.18), transparent 30%),
                linear-gradient(135deg, #8c1018 0%, #c60000 58%, #4f0910 100%);
            color: #fff;
        }

        .hero-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(0, 0, 0, 0.08));
            pointer-events: none;
        }

        .hero-panel > * {
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-size: 0.75rem;
            color: var(--admin-accent);
            margin-bottom: 6px;
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            min-height: 170px;
            padding: 22px;
            border-radius: 24px;
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-shadow);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            inset: auto 0 0 0;
            height: 4px;
            background: linear-gradient(90deg, var(--admin-accent-dark), var(--admin-accent), #f59e0b);
        }

        .metric-value {
            font-family: 'Oswald', sans-serif;
            font-size: 2.2rem;
            line-height: 1;
        }

        .metric-icon {
            width: 56px;
            height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: var(--admin-accent-soft);
            color: var(--admin-accent);
            font-size: 1.25rem;
        }

        .table-shell {
            border: 1px solid var(--admin-border);
            border-radius: 20px;
            overflow: hidden;
            background: var(--admin-surface);
        }

        .admin-table-wrap {
            margin-top: 8px;
            padding: 8px 0 0;
            max-width: 100%;
            overflow: hidden;
        }

        .admin-table-scroller {
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 6px;
        }

        .admin-table-scroller .table,
        .admin-table-scroller .dataTables_wrapper {
            min-width: 100%;
        }

        .admin-table-scroller .dataTables_wrapper,
        .admin-table-wrap .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .admin-table-scroller table.dataTable,
        .admin-table-wrap table.admin-table {
            min-width: 100%;
            width: 100% !important;
        }

        .table.admin-table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--admin-text);
            --bs-table-striped-bg: rgba(198, 0, 0, 0.025);
            --bs-table-striped-color: var(--admin-text);
            --bs-table-hover-bg: rgba(198, 0, 0, 0.045);
            --bs-table-hover-color: var(--admin-text);
            margin-bottom: 0;
        }

        .table.admin-table thead th {
            background: rgba(111, 16, 24, 0.05);
            color: #5c1d24;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border-bottom-width: 1px;
            white-space: nowrap;
        }

        .table.admin-table > :not(caption) > * > * {
            padding: 0.95rem 1rem;
            border-color: rgba(198, 0, 0, 0.08);
            vertical-align: middle;
        }

        .admin-table-wrap .admin-table-toolbar,
        .admin-table-wrap .d-flex.flex-column.flex-md-row.justify-content-between.align-items-md-center.gap-2.mt-3 {
            padding-inline: 16px;
        }

        .admin-table-wrap .table-responsive {
            padding-inline: 16px;
        }

        .admin-table-toolbar .form-select,
        .admin-table-toolbar .form-control {
            border-radius: 999px;
            border-color: rgba(198, 0, 0, 0.12);
            box-shadow: none;
        }

        .dataTables_filter input {
            min-width: 220px;
        }

        .admin-table-wrap .dataTables_paginate .pagination {
            gap: 8px;
            flex-wrap: wrap;
        }

        .admin-table-wrap .dataTables_paginate .page-link {
            border-radius: 999px;
            border-color: rgba(198, 0, 0, 0.18);
            color: var(--admin-accent);
            background: rgba(198, 0, 0, 0.04);
            box-shadow: none;
        }

        .admin-table-wrap .dataTables_paginate .page-link:hover,
        .admin-table-wrap .dataTables_paginate .page-link:focus {
            color: #fff;
            background: var(--admin-accent);
            border-color: var(--admin-accent);
            box-shadow: 0 0 0 0.2rem rgba(198, 0, 0, 0.12);
        }

        .admin-table-wrap .dataTables_paginate .page-item.active .page-link {
            color: #fff;
            background: linear-gradient(135deg, #c60000, #8f1111);
            border-color: #a4161a;
        }

        .admin-table-wrap .dataTables_paginate .page-item.disabled .page-link {
            color: rgba(92, 29, 36, 0.45);
            background: rgba(198, 0, 0, 0.02);
            border-color: rgba(198, 0, 0, 0.08);
        }

        .compact-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .compact-meta-item {
            padding: 12px 14px;
            border-radius: 16px;
            background: var(--admin-surface-soft);
            border: 1px solid var(--admin-border);
        }

        .compact-meta-item.full {
            grid-column: 1 / -1;
        }

        .compact-meta-label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--admin-muted);
        }

        .compact-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .compact-actions .btn {
            min-width: 0;
        }

        .checkout-item-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 18px;
            background: var(--admin-surface-soft);
            border: 1px solid var(--admin-border);
        }

        .checkout-item-card small {
            display: block;
            color: var(--admin-muted);
            margin-top: 2px;
        }

        .checkout-price {
            font-weight: 700;
            color: var(--admin-accent-dark);
            white-space: nowrap;
        }

        .rekening-card {
            padding: 14px 16px;
            border-radius: 18px;
            background: var(--admin-surface-soft);
            border: 1px solid var(--admin-border);
        }

        .tab-chip-nav {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 8px;
            border-radius: 18px;
            background: rgba(198, 0, 0, 0.05);
        }

        .tab-chip-nav .nav-link {
            border: 0;
            border-radius: 999px;
            padding: 0.7rem 1rem;
            color: var(--admin-muted);
            font-weight: 600;
            background: transparent;
        }

        .tab-chip-nav .nav-link.active {
            background: var(--admin-accent);
            color: #fff;
            box-shadow: 0 10px 24px rgba(198, 0, 0, 0.18);
        }

        .compact-kontingen-header {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 18px;
        }

        .compact-kontingen-stat {
            padding: 14px 16px;
            border-radius: 18px;
            background: var(--admin-surface-soft);
            border: 1px solid var(--admin-border);
        }

        .assist-summary-box {
            padding: 18px;
            border-radius: 20px;
            background: linear-gradient(180deg, #fff5f5 0%, #fff 100%);
            border: 1px solid rgba(198, 0, 0, 0.1);
        }

        .assist-summary-total {
            font-family: 'Oswald', sans-serif;
            font-size: 2rem;
            line-height: 1;
        }

        .hidden-upload-panel {
            display: none;
        }

        .hidden-upload-panel.show {
            display: block;
        }

        .section-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .btn-soft {
            background: rgba(198, 0, 0, 0.06);
            border: 1px solid rgba(198, 0, 0, 0.1);
            color: var(--admin-accent-dark);
        }

        .btn-soft:hover {
            background: rgba(198, 0, 0, 0.1);
            color: var(--admin-accent-dark);
        }

        .info-grid {
            display: grid;
            gap: 14px;
        }

        .info-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: var(--admin-surface-soft);
            border: 1px solid var(--admin-border);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .status-badge.success {
            color: #0b6b4a;
            background: rgba(15, 159, 110, 0.14);
        }

        .status-badge.warning {
            color: #9a6700;
            background: rgba(245, 158, 11, 0.16);
        }

        .status-badge.neutral {
            color: #6b7280;
            background: rgba(107, 114, 128, 0.14);
        }

        .section-stack {
            display: grid;
            gap: 24px;
        }

        .placeholder-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .placeholder-stat {
            padding: 18px;
            border-radius: 20px;
            background: var(--admin-surface-soft);
            border: 1px dashed rgba(198, 0, 0, 0.16);
        }

        .admin-footer {
            margin-top: 24px;
            padding: 0 4px;
            color: var(--admin-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }

        .admin-mobile-toggle {
            display: none;
            width: 46px;
            height: 46px;
            border: 0;
            border-radius: 16px;
            background: rgba(198, 0, 0, 0.08);
            color: var(--admin-accent-dark);
        }

        @media (max-width: 991.98px) {
            .admin-shell {
                flex-direction: column;
            }

            .admin-sidebar {
                width: min(88vw, 340px);
                height: calc(100vh - 24px);
                position: fixed;
                top: 12px;
                left: 12px;
                z-index: 1050;
                border-radius: 28px;
                transform: translateX(calc(-100% - 24px));
                transition: transform 0.24s ease;
                box-shadow: 0 24px 80px rgba(15, 23, 42, 0.34);
            }

            body.admin-sidebar-open .admin-sidebar {
                transform: translateX(0);
            }

            .admin-sidebar-close,
            .admin-mobile-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .admin-main {
                margin-left: 0;
                padding: 18px;
            }

            .admin-card,
            .admin-topbar {
                padding: 18px;
                border-radius: 20px;
            }

            .placeholder-grid {
                grid-template-columns: 1fr;
            }

            .compact-meta-grid {
                grid-template-columns: 1fr;
            }

            .compact-kontingen-header {
                grid-template-columns: 1fr;
            }

            .admin-topbar,
            .admin-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-topbar {
                position: sticky;
                top: 12px;
                z-index: 1020;
            }

            .admin-topbar-actions {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
            }

            .admin-table-wrap .admin-table-toolbar,
            .admin-table-wrap .table-responsive,
            .admin-table-wrap .d-flex.flex-column.flex-md-row.justify-content-between.align-items-md-center.gap-2.mt-3 {
                padding-inline: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="admin-sidebar-overlay" data-admin-sidebar-close></div>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-head">
                <a href="<?= base_url('admin/bendahara/dashboard') ?>" class="admin-brand text-decoration-none text-reset flex-grow-1">
                    <?php if (! empty($eventLogo)) : ?>
                        <img src="<?= esc($eventLogo) ?>" alt="<?= esc($eventName ?? 'Event') ?>">
                    <?php endif; ?>
                    <div>
                        <div class="admin-brand-title h4 mb-1">Bendahara Panel</div>
                        <div class="admin-brand-subtitle small"><?= esc($eventName ?? 'Digital Pencak Silat') ?></div>
                    </div>
                </a>
                <button type="button" class="admin-sidebar-close" data-admin-sidebar-close aria-label="Tutup menu">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div>
                <div class="admin-section-label mb-2">Navigasi</div>
                <nav class="d-flex flex-column gap-2">
                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/dashboard') ?>">
                        <span class="label-block"><i class="fas fa-chart-line"></i><span>Dashboard</span></span>
                    </a>

                    <div>
                        <a class="admin-nav-link <?= ($activeMenu ?? '') === 'pembayaran' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminPembayaranSubmenu" role="button" aria-expanded="<?= ($activeMenu ?? '') === 'pembayaran' ? 'true' : 'false' ?>" aria-controls="adminPembayaranSubmenu">
                            <span class="label-block"><i class="fas fa-wallet"></i><span>Pembayaran</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= ($activeMenu ?? '') === 'pembayaran' ? 'show' : '' ?>" id="adminPembayaranSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($paymentSubmenu ?? '') === 'all' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/pembayaran') ?>">Semua Transaksi</a>
                                <a class="admin-submenu-link <?= ($paymentSubmenu ?? '') === 'waiting' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/pembayaran/menunggu-konfirmasi') ?>">Menunggu Konfirmasi</a>
                                <a class="admin-submenu-link <?= ($paymentSubmenu ?? '') === 'paid' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/pembayaran/lunas') ?>">Lunas</a>
                                <a class="admin-submenu-link <?= ($paymentSubmenu ?? '') === 'unpaid' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/pembayaran/belum-dibayar') ?>">Belum Dibayar</a>
                                <a class="admin-submenu-link <?= ($paymentSubmenu ?? '') === 'tanding' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/pembayaran/tanding') ?>">Riwayat Tanding</a>
                                <a class="admin-submenu-link <?= ($paymentSubmenu ?? '') === 'seni' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/pembayaran/seni') ?>">Riwayat Seni</a>
                            </div>
                        </div>
                    </div>

                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'kontingen' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/kontingen') ?>">
                        <span class="label-block"><i class="fas fa-people-group"></i><span>Kontingen</span></span>
                    </a>
                </nav>
            </div>

            <div class="mt-auto admin-card">
                <div class="small admin-section-label px-0 mb-2">Sesi Admin</div>
                <div class="fw-semibold mb-1"><?= esc($adminName ?? 'Admin Bendahara') ?></div>
                <div class="muted-copy small">Role aktif: <?= esc((string) (session()->get('level') ?? 'unknown')) ?></div>
                <a href="<?= base_url('admin/logout') ?>" class="btn btn-outline-light btn-sm rounded-pill mt-3">Logout</a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-actions">
                    <div>
                        <div class="eyebrow">Area Admin Bendahara</div>
                        <h1 class="admin-page-title h2 mb-0"><?= esc($title ?? 'Admin Panel') ?></h1>
                    </div>
                    <button type="button" class="admin-mobile-toggle" data-admin-sidebar-open aria-label="Buka menu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="admin-topbar-meta text-md-end">
                    <div class="fw-semibold"><?= esc($adminName ?? 'Admin Bendahara') ?></div>
                    <div class="small">Versi aplikasi <?= esc(app_version()) ?></div>
                </div>
            </header>

            <?= $this->renderSection('content') ?>

            <footer class="admin-footer">
                <span>Digital Pencak Silat Bendahara Panel</span>
                <span class="footer-version"><?= esc(app_version()) ?></span>
            </footer>
        </main>
    </div>

    <script src="<?= online_asset('bootstrap_5_bundle_js') ?>"></script>
    <script src="<?= online_asset('jquery_3_js') ?>"></script>
    <script src="<?= online_asset('datatables_jquery_js') ?>"></script>
    <script src="<?= online_asset('datatables_bs5_js') ?>"></script>
    <script src="<?= online_asset('datatables_responsive_js') ?>"></script>
    <script src="<?= online_asset('datatables_responsive_bs5_js') ?>"></script>
    <script src="<?= online_asset('sweetalert2_js') ?>"></script>
    <script src="<?= online_asset('toastr_js') ?>"></script>
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            newestOnTop: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
        };

        <?php if (session()->getFlashdata('status') === true) : ?>
            toastr.success(<?= json_encode(is_array(session()->getFlashdata('message')) ? implode(' ', session()->getFlashdata('message')) : (string) session()->getFlashdata('message')) ?>);
        <?php elseif (session()->getFlashdata('status') === false) : ?>
            <?php if (is_array(session()->getFlashdata('message'))) : ?>
                <?php foreach (session()->getFlashdata('message') as $message) : ?>
                    toastr.error(<?= json_encode((string) $message) ?>);
                <?php endforeach; ?>
            <?php else : ?>
                toastr.error(<?= json_encode((string) session()->getFlashdata('message')) ?>);
            <?php endif; ?>
        <?php endif; ?>

        window.initAdminDataTable = function(selector, options = {}) {
            if (!window.jQuery || !$(selector).length) {
                return null;
            }

            return $(selector).DataTable(Object.assign({
                responsive: false,
                scrollX: false,
                autoWidth: false,
                pageLength: 10,
                language: {
                    search: '',
                    searchPlaceholder: 'Cari data...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Belum ada data',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    }
                },
                dom: "<'admin-table-toolbar d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3'<'d-flex align-items-center gap-2'l><'admin-search'f>>" +
                    "<'table-responsive'tr>" +
                    "<'d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3'<'small text-muted'i><'p-0'p>>"
            }, options));
        };

        window.confirmAdminAction = function(form, title, text, confirmText = 'Lanjutkan') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal',
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });

            return false;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const openButton = document.querySelector('[data-admin-sidebar-open]');
            const closeButtons = document.querySelectorAll('[data-admin-sidebar-close]');

            const openSidebar = () => body.classList.add('admin-sidebar-open');
            const closeSidebar = () => body.classList.remove('admin-sidebar-open');

            openButton?.addEventListener('click', openSidebar);
            closeButtons.forEach((button) => button.addEventListener('click', closeSidebar));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });

            document.querySelectorAll('.admin-datatable').forEach((table) => {
                if (!table.id) {
                    table.id = 'admin-table-' + Math.random().toString(36).slice(2, 10);
                }

                window.initAdminDataTable('#' + table.id);
            });
        });
    </script>
</body>

</html>

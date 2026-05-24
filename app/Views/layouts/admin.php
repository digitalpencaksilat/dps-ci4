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
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/admin.css') ?>">
</head>

<?php
$adminRole = (string) (session()->get('level') ?? 'bendahara');
$adminPanels = [
    'bendahara' => [
        'label' => 'Bendahara Panel',
        'area' => 'Area Admin Bendahara',
        'home' => 'admin/bendahara/dashboard',
        'footer' => 'Digital Pencak Silat Bendahara Panel',
    ],
    'sekretariat' => [
        'label' => 'Sekretariat Panel',
        'area' => 'Area Admin Sekretariat',
        'home' => 'admin/sekretariat/dashboard',
        'footer' => 'Digital Pencak Silat Sekretariat Panel',
    ],
    'super_admin' => [
        'label' => 'Super Admin Panel',
        'area' => 'Area Super Admin',
        'home' => 'admin/super/dashboard',
        'footer' => 'Digital Pencak Silat Super Admin Panel',
    ],
];
$adminPanel = $adminPanels[$adminRole] ?? $adminPanels['bendahara'];
?>

<body class="admin-body">
    <div class="admin-sidebar-overlay" data-admin-sidebar-close></div>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-head">
                <a href="<?= base_url($adminPanel['home']) ?>" class="admin-brand text-decoration-none text-reset flex-grow-1">
                    <?php if (! empty($eventLogo)) : ?>
                        <img src="<?= esc($eventLogo) ?>" alt="<?= esc($eventName ?? 'Event') ?>">
                    <?php endif; ?>
                    <div>
                        <div class="admin-brand-title h4 mb-1"><?= esc($adminPanel['label']) ?></div>
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
                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= base_url($adminPanel['home']) ?>">
                        <span class="label-block"><i class="fas fa-chart-line"></i><span>Dashboard</span></span>
                    </a>

                    <?php if ($adminRole === 'bendahara') : ?>
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
                    <?php endif; ?>
                </nav>
            </div>

            <div class="mt-auto admin-card">
                <div class="small admin-section-label px-0 mb-2">Sesi Admin</div>
                <div class="fw-semibold mb-1"><?= esc($adminName ?? $adminPanel['label']) ?></div>
                <div class="muted-copy small">Role aktif: <?= esc((string) (session()->get('level') ?? 'unknown')) ?></div>
                <a href="<?= base_url('admin/logout') ?>" class="btn btn-outline-light btn-sm rounded-pill mt-3">Logout</a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-actions">
                    <div>
                        <div class="eyebrow"><?= esc($adminPanel['area']) ?></div>
                        <h1 class="admin-page-title h2 mb-0"><?= esc($title ?? 'Admin Panel') ?></h1>
                    </div>
                    <button type="button" class="admin-mobile-toggle" data-admin-sidebar-open aria-label="Buka menu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="admin-topbar-meta text-md-end">
                    <div class="fw-semibold"><?= esc($adminName ?? $adminPanel['label']) ?></div>
                    <div class="small">Versi aplikasi <?= esc(app_version()) ?></div>
                </div>
            </header>

            <?= $this->renderSection('content') ?>

            <footer class="admin-footer">
                <span><?= esc($adminPanel['footer']) ?></span>
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

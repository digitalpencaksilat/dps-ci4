<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard Kontingen') ?> - <?= esc($eventName ?? 'Digital Pencak Silat') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('datatables_bs5_css') ?>">
    <link rel="stylesheet" href="<?= online_asset('datatables_responsive_css') ?>">
    <link rel="stylesheet" href="<?= online_asset('toastr_css') ?>">
    <link rel="stylesheet" href="<?= online_asset('fontawesome_6_css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/kontingen-theme.css') ?>">
</head>

<body class="kontingen-body">
    <div class="kontingen-shell">
        <aside class="kontingen-sidebar">
            <a href="<?= base_url('kontingen/dashboard') ?>" class="brand-block text-decoration-none">
                <?php if (! empty($eventLogo)) : ?>
                    <img src="<?= esc($eventLogo) ?>" alt="<?= esc($eventName ?? 'Event') ?>">
                <?php endif; ?>
                <div>
                    <div class="brand-title">Kontingen Panel</div>
                    <div class="brand-subtitle"><?= esc($eventName ?? 'Digital Pencak Silat') ?></div>
                </div>
            </a>

            <div class="sidebar-section-label">Menu Utama</div>
            <nav class="nav flex-column gap-2">
                <a class="sidebar-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('kontingen/dashboard') ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a class="sidebar-link <?= ($activeMenu ?? '') === 'peserta' ? 'active' : '' ?>" href="<?= base_url('kontingen/peserta') ?>">
                    <i class="fas fa-users"></i>
                    <span>Peserta</span>
                </a>
                <a class="sidebar-link <?= ($activeMenu ?? '') === 'tanding' ? 'active' : '' ?>" href="<?= base_url('kontingen/tanding') ?>">
                    <i class="fas fa-fist-raised"></i>
                    <span>Tanding</span>
                </a>
                <a class="sidebar-link <?= ($activeMenu ?? '') === 'seni' ? 'active' : '' ?>" href="<?= base_url('kontingen/seni') ?>">
                    <i class="fas fa-drum"></i>
                    <span>Seni</span>
                </a>
                <a class="sidebar-link <?= ($activeMenu ?? '') === 'pembayaran' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#pembayaranSubmenu" role="button" aria-expanded="<?= ($activeMenu ?? '') === 'pembayaran' ? 'true' : 'false' ?>" aria-controls="pembayaranSubmenu">
                    <i class="fas fa-wallet"></i>
                    <span>Pembayaran</span>
                    <small><i class="fas fa-chevron-down"></i></small>
                </a>
                <div class="collapse <?= ($activeMenu ?? '') === 'pembayaran' ? 'show' : '' ?>" id="pembayaranSubmenu">
                    <div class="submenu-links">
                        <a class="sidebar-link secondary-link <?= (($paymentSubmenu ?? '') === 'tagihan') ? 'active' : '' ?>" href="<?= base_url('kontingen/pembayaran') ?>">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Tagihan</span>
                        </a>
                        <a class="sidebar-link secondary-link <?= (($paymentSubmenu ?? '') === 'waiting') ? 'active' : '' ?>" href="<?= base_url('kontingen/pembayaran/menunggu-konfirmasi') ?>">
                            <i class="fas fa-hourglass-half"></i>
                            <span>Menunggu Konfirmasi</span>
                        </a>
                        <a class="sidebar-link secondary-link <?= (($paymentSubmenu ?? '') === 'paid') ? 'active' : '' ?>" href="<?= base_url('kontingen/pembayaran/lunas') ?>">
                            <i class="fas fa-circle-check"></i>
                            <span>Pembayaran Lunas</span>
                        </a>
                    </div>
                </div>
            </nav>

            <div class="sidebar-footer mt-auto">
                <a href="<?= base_url('kontingen/logout') ?>" class="btn btn-outline-danger w-100 rounded-pill">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </aside>

        <main class="kontingen-main">
            <header class="topbar-card">
                <div>
                    <p class="eyebrow mb-1">Area Kontingen</p>
                    <h1 class="page-title mb-0"><?= esc($title ?? 'Dashboard Kontingen') ?></h1>
                </div>
                <div class="topbar-meta text-end">
                    <div class="kontingen-name"><?= esc(session('nama_kontingen') ?? '-') ?></div>
                </div>
            </header>

            <?= $this->renderSection('content') ?>

            <footer class="kontingen-content-footer">
                <span>Digital Pencak Silat Kontingen Panel</span>
                <span class="footer-version"><?= esc(app_version()) ?></span>
            </footer>
        </main>
    </div>

    <script src="<?= online_asset('jquery_3_js') ?>"></script>
    <script src="<?= online_asset('bootstrap_5_bundle_js') ?>"></script>
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

        window.initKontingenDataTable = function(selector, options = {}) {
            if (!window.jQuery || !$(selector).length) {
                return null;
            }

            return $(selector).DataTable(Object.assign({
                responsive: true,
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
                dom: "<'kontingen-table-toolbar d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3'<'d-flex align-items-center gap-2'l><'kontingen-search'f>>" +
                    "<'table-responsive'tr>" +
                    "<'d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3'<'small text-muted'i><'p-0'p>>"
            }, options));
        };

        window.confirmDeleteAction = function(form, message = 'Data ini akan dihapus.') {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
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
    </script>
</body>

</html>

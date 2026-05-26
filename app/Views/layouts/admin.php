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
    <link rel="stylesheet" href="<?= base_url('assets/bracket-pertandingan/jquery.bracket.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/bracket-pertandingan/bracket.css') ?>">
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

                    <?php if ($adminRole === 'sekretariat') : ?>
                    <div>
                        <a class="admin-nav-link <?= ($activeMenu ?? '') === 'kontingen' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminKontingenSubmenu" role="button" aria-expanded="<?= ($activeMenu ?? '') === 'kontingen' ? 'true' : 'false' ?>" aria-controls="adminKontingenSubmenu">
                            <span class="label-block"><i class="fas fa-people-group"></i><span>Kontingen</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= ($activeMenu ?? '') === 'kontingen' ? 'show' : '' ?>" id="adminKontingenSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($kontingenSubmenu ?? '') === 'sub_kontingen' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kontingen') ?>">Sub Kontingen</a>
                                <a class="admin-submenu-link <?= ($kontingenSubmenu ?? '') === 'rekap_atlet' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kontingen/rekap-atlet') ?>">Rekap Atlet</a>
                            </div>
                        </div>
                    </div>
                    <?php $isAtletMenu = in_array(($activeMenu ?? ''), ['data_atlet', 'data_bpjs', 'peserta_tanding', 'kelompok_seni'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isAtletMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminAtletSubmenu" role="button" aria-expanded="<?= $isAtletMenu ? 'true' : 'false' ?>" aria-controls="adminAtletSubmenu">
                            <span class="label-block"><i class="fas fa-users"></i><span>Atlet</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isAtletMenu ? 'show' : '' ?>" id="adminAtletSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'data_atlet' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/data-atlet') ?>">Data Atlet</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'data_bpjs' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/data-bpjs') ?>">Data BPJS</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'peserta_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/peserta-tanding') ?>">Peserta Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'kelompok_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kelompok-seni') ?>">Peserta Seni</a>
                            </div>
                        </div>
                    </div>
                    <?php $isStatistikMenu = in_array(($activeMenu ?? ''), ['statistik_pendaftaran', 'statistik_tanding', 'statistik_seni'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isStatistikMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminStatistikSubmenu" role="button" aria-expanded="<?= $isStatistikMenu ? 'true' : 'false' ?>" aria-controls="adminStatistikSubmenu">
                            <span class="label-block"><i class="fas fa-chart-pie"></i><span>Statistik</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isStatistikMenu ? 'show' : '' ?>" id="adminStatistikSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'statistik_pendaftaran' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/statistik') ?>">Progress Pendaftaran</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'statistik_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/statistik/tanding') ?>">Statistik Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'statistik_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/statistik/seni') ?>">Statistik Seni</a>
                            </div>
                        </div>
                    </div>
                    <?php $isTandingMenu = in_array(($activeMenu ?? ''), ['kelas_tanding', 'pool_tanding', 'pertandingan_tanding', 'kuota_prestasi_tanding'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isTandingMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminTandingSubmenu" role="button" aria-expanded="<?= $isTandingMenu ? 'true' : 'false' ?>" aria-controls="adminTandingSubmenu">
                            <span class="label-block"><i class="fas fa-hand-fist"></i><span>Kategori Tanding</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isTandingMenu ? 'show' : '' ?>" id="adminTandingSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'kelas_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kelas-tanding') ?>">Daftar Kelas Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pool_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/pool-tanding') ?>">Daftar Pool</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pertandingan_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/pertandingan-tanding') ?>">Daftar Pertandingan</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'kuota_prestasi_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kuota-prestasi-tanding') ?>">Kuota Kelas Prestasi</a>
                            </div>
                        </div>
                    </div>
                    <?php $isPesilatTerbaikMenu = in_array(($activeMenu ?? ''), ['pesilat_terbaik_pertandingan_tanding', 'pesilat_terbaik_battle_seni', 'pesilat_terbaik_pool_seni'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isPesilatTerbaikMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminPesilatTerbaikSubmenu" role="button" aria-expanded="<?= $isPesilatTerbaikMenu ? 'true' : 'false' ?>" aria-controls="adminPesilatTerbaikSubmenu">
                            <span class="label-block"><i class="fas fa-ranking-star"></i><span>Pesilat Terbaik</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isPesilatTerbaikMenu ? 'show' : '' ?>" id="adminPesilatTerbaikSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pesilat_terbaik_pertandingan_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/pesilat-terbaik/pertandingan-tanding') ?>">Urutan Poin Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pesilat_terbaik_battle_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/pesilat-terbaik/battle-seni') ?>">Urutan Poin Battle</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pesilat_terbaik_pool_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/pesilat-terbaik/pool-seni') ?>">Urutan Poin Pool</a>
                            </div>
                        </div>
                    </div>
                    <?php $isSeniMenu = in_array(($activeMenu ?? ''), ['kategori_seni_admin', 'pool_seni', 'sistem_pool_seni', 'battle_seni', 'kuota_prestasi_seni'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isSeniMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminSeniSubmenu" role="button" aria-expanded="<?= $isSeniMenu ? 'true' : 'false' ?>" aria-controls="adminSeniSubmenu">
                            <span class="label-block"><i class="fas fa-medal"></i><span>Kategori Seni</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isSeniMenu ? 'show' : '' ?>" id="adminSeniSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'kategori_seni_admin' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kategori-seni') ?>">Daftar Kategori Seni</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pool_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/pool-seni') ?>">Daftar Pool Seni</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'sistem_pool_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/sistem-pool-seni') ?>">Sistem Penampilan Pool</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'battle_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/battle-seni') ?>">Daftar Battle Seni</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'kuota_prestasi_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kuota-prestasi-seni') ?>">Kuota Kelas Prestasi Seni</a>
                            </div>
                        </div>
                    </div>
                    <?php $isJadwalMenu = in_array(($activeMenu ?? ''), ['jadwal_tanding', 'jadwal_seni'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isJadwalMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminJadwalSubmenu" role="button" aria-expanded="<?= $isJadwalMenu ? 'true' : 'false' ?>" aria-controls="adminJadwalSubmenu">
                            <span class="label-block"><i class="fas fa-calendar-alt"></i><span>Jadwal Pertandingan</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isJadwalMenu ? 'show' : '' ?>" id="adminJadwalSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'jadwal_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/jadwal-tanding') ?>">Jadwal Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'jadwal_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/jadwal-seni') ?>">Jadwal Seni</a>
                            </div>
                        </div>
                    </div>
                    <?php $isMedalMenu = ($activeMenu ?? '') === 'medal_tally'; ?>
                    <?php $medalSlug = uri_string() ?? ''; ?>
                    <div>
                        <a class="admin-nav-link <?= $isMedalMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminMedalSubmenu" role="button" aria-expanded="<?= $isMedalMenu ? 'true' : 'false' ?>" aria-controls="adminMedalSubmenu">
                            <span class="label-block"><i class="fas fa-trophy"></i><span>Perolehan Medali</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isMedalMenu ? 'show' : '' ?>" id="adminMedalSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= str_ends_with($medalSlug, 'akumulasi') ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/perolehan-medali/akumulasi') ?>">Akumulasi</a>
                                <a class="admin-submenu-link <?= str_ends_with($medalSlug, 'kategori-usia') ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/perolehan-medali/kategori-usia') ?>">Per Kategori Usia</a>
                                <a class="admin-submenu-link <?= str_ends_with($medalSlug, 'sekolah') ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/perolehan-medali/sekolah') ?>">Berdasarkan Sekolah</a>
                                <a class="admin-submenu-link <?= str_ends_with($medalSlug, 'akumulasi-eksklusif') ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/perolehan-medali/akumulasi-eksklusif') ?>">Akumulasi Eksklusif</a>
                                <a class="admin-submenu-link <?= str_ends_with($medalSlug, 'kategori-usia-eksklusif') ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/perolehan-medali/kategori-usia-eksklusif') ?>">Kategori Eksklusif</a>
                                <a class="admin-submenu-link <?= str_ends_with($medalSlug, 'tanding') ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/perolehan-medali/tanding') ?>">Raw Tanding</a>
                                <a class="admin-submenu-link <?= str_ends_with($medalSlug, 'seni') ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/perolehan-medali/seni') ?>">Raw Seni</a>
                            </div>
                        </div>
                    </div>
                    <?php $isToolsMenu = in_array(($activeMenu ?? ''), ['pengadaan_medali', 'nomor_sertifikat'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isToolsMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminToolsSubmenu" role="button" aria-expanded="<?= $isToolsMenu ? 'true' : 'false' ?>" aria-controls="adminToolsSubmenu">
                            <span class="label-block"><i class="fas fa-screwdriver-wrench"></i><span>Tools</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isToolsMenu ? 'show' : '' ?>" id="adminToolsSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pengadaan_medali' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/pengadaan-medali') ?>">Pengadaan Medali</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'nomor_sertifikat' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/nomor-sertifikat') ?>">Nomor Sertifikat</a>
                            </div>
                        </div>
                    </div>
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
    <script src="<?= base_url('assets/bracket-pertandingan/jquery.bracket.min.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
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

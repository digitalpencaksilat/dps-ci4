<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin Panel') ?> - <?= esc($eventName ?? 'Digital Pencak Silat') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= online_asset('datatables_bs5_css') ?>">
    <link rel="stylesheet" href="<?= online_asset('datatables_responsive_css') ?>">
    <link rel="stylesheet" href="<?= online_asset('datatables_buttons_css') ?>">
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
                    <?php if ($adminRole === 'super_admin') : ?>
                    <?php $superMode = (string) (session()->get('tipe_super_admin') ?? ''); ?>
                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'super_home' ? 'active' : '' ?>" href="<?= base_url('admin/super/menu-utama') ?>">
                        <span class="label-block"><i class="fas fa-house"></i><span>Menu Utama</span></span>
                    </a>

                    <?php if ($superMode === 'pengaturan_event') : ?>
                    <a class="admin-nav-link <?= uri_string() === 'admin/super/dashboard-pengaturan-event' ? 'active' : '' ?>" href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>">
                        <span class="label-block"><i class="fas fa-sliders"></i><span>Dashboard Pengaturan Event</span></span>
                    </a>

                    <a class="admin-nav-link <?= str_contains(uri_string(), 'admin/super/pengaturan-event/profil-kejuaraan') ? 'active' : '' ?>" href="<?= base_url('admin/super/pengaturan-event/profil-kejuaraan') ?>">
                        <span class="label-block"><i class="fas fa-trophy"></i><span>Profil Kejuaraan</span></span>
                    </a>
                    <a class="admin-nav-link <?= str_contains(uri_string(), 'admin/super/pengaturan-event/akses-pendaftaran') ? 'active' : '' ?>" href="<?= base_url('admin/super/pengaturan-event/akses-pendaftaran') ?>">
                        <span class="label-block"><i class="fas fa-shield-halved"></i><span>Akses Pendaftaran</span></span>
                    </a>
                    <a class="admin-nav-link <?= str_contains(uri_string(), 'admin/super/pengaturan-event/akses-pemilihan-kategori') ? 'active' : '' ?>" href="<?= base_url('admin/super/pengaturan-event/akses-pemilihan-kategori') ?>">
                        <span class="label-block"><i class="fas fa-list-check"></i><span>Akses Pemilihan Kategori</span></span>
                    </a>
                    <a class="admin-nav-link <?= str_contains(uri_string(), 'admin/super/pengaturan-event/gambar-dan-juknis') ? 'active' : '' ?>" href="<?= base_url('admin/super/pengaturan-event/gambar-dan-juknis') ?>">
                        <span class="label-block"><i class="fas fa-image"></i><span>Gambar dan Juknis</span></span>
                    </a>
                    <a class="admin-nav-link <?= str_contains(uri_string(), 'admin/super/pengaturan-event/rekening-pembayaran') ? 'active' : '' ?>" href="<?= base_url('admin/super/pengaturan-event/rekening-pembayaran') ?>">
                        <span class="label-block"><i class="fas fa-building-columns"></i><span>Rekening Pembayaran</span></span>
                    </a>
                    <a class="admin-nav-link <?= str_contains(uri_string(), 'admin/super/pengaturan-event/pengaturan-kontingen') ? 'active' : '' ?>" href="<?= base_url('admin/super/pengaturan-event/pengaturan-kontingen') ?>">
                        <span class="label-block"><i class="fas fa-people-group"></i><span>Pengaturan Kontingen</span></span>
                    </a>

                    <a class="admin-nav-link <?= str_contains(uri_string(), 'admin/super/pengaturan-event/arsip-pendaftar') ? 'active' : '' ?>" href="<?= base_url('admin/super/pengaturan-event/arsip-pendaftar') ?>">
                        <span class="label-block"><i class="fas fa-folder-open"></i><span>Arsip Pendaftar</span></span>
                    </a>
                    <?php endif; ?>

                    <?php if ($superMode === 'pembuatan_jadwal') : ?>
                    <?php
                        $isDrawingMenu = ($activeMenu ?? '') === 'pembuatan_jadwal_drawing_tanding'
                            || ($activeMenu ?? '') === 'pembuatan_jadwal_drawing_seni';
                        $isGenerateBaganMenu = ($activeMenu ?? '') === 'pembuatan_jadwal_generate_bagan_tanding'
                            || ($activeMenu ?? '') === 'pembuatan_jadwal_generate_bagan_seni_battle';
                        $isPenjadwalanOtomatisMenu = ($activeMenu ?? '') === 'pembuatan_jadwal_penjadwalan_otomatis_tanding'
                            || ($activeMenu ?? '') === 'pembuatan_jadwal_penjadwalan_otomatis_seni';
                        $isJadwalPertandinganMenu = ($activeMenu ?? '') === 'pembuatan_jadwal_jadwal_tanding'
                            || ($activeMenu ?? '') === 'pembuatan_jadwal_jadwal_seni'
                            || ($activeMenu ?? '') === 'pembuatan_jadwal_tukar_atlet';
                    ?>

                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_dashboard' ? 'active' : '' ?>" href="<?= base_url('admin/super/dashboard-pembuatan-jadwal') ?>">
                        <span class="label-block"><i class="fas fa-chart-bar"></i><span>Dashboard Pembuatan Jadwal</span></span>
                    </a>

                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_operasi_basis_data' ? 'active' : '' ?>" href="<?= base_url('admin/super/operasi-basis-data') ?>">
                        <span class="label-block"><i class="fas fa-database"></i><span>Operasi Basis Data</span></span>
                    </a>

                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'import_excel_data' ? 'active' : '' ?>" href="<?= base_url('admin/super/import-excel-data') ?>">
                        <span class="label-block"><i class="fas fa-file-import"></i><span>Import Excel</span></span>
                    </a>

                    <a class="admin-nav-link <?= str_contains(uri_string(), 'admin/gelanggang') ? 'active' : '' ?>" href="<?= base_url('admin/gelanggang') ?>">
                        <span class="label-block"><i class="fas fa-warehouse"></i><span>Gelanggang</span></span>
                    </a>

                    <div>
                        <a class="admin-nav-link <?= $isDrawingMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#superDrawingPertandinganSubmenu" role="button" aria-expanded="<?= $isDrawingMenu ? 'true' : 'false' ?>" aria-controls="superDrawingPertandinganSubmenu">
                            <span class="label-block"><i class="fas fa-random"></i><span>Drawing Pertandingan</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isDrawingMenu ? 'show' : '' ?>" id="superDrawingPertandinganSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_drawing_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/super/drawing-tanding') ?>">Drawing Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_drawing_seni' ? 'active' : '' ?>" href="<?= base_url('admin/super/drawing-seni') ?>">Drawing Seni</a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <a class="admin-nav-link <?= $isGenerateBaganMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#superGenerateBaganSubmenu" role="button" aria-expanded="<?= $isGenerateBaganMenu ? 'true' : 'false' ?>" aria-controls="superGenerateBaganSubmenu">
                            <span class="label-block"><i class="fas fa-magic"></i><span>Generate Bagan</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isGenerateBaganMenu ? 'show' : '' ?>" id="superGenerateBaganSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_generate_bagan_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/super/generate-bagan-tanding-dari-jadwal') ?>">Generate Bagan Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_generate_bagan_seni_battle' ? 'active' : '' ?>" href="<?= base_url('admin/super/generate-bagan-seni-battle-dari-jadwal') ?>">Generate Bagan Seni Battle</a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <a class="admin-nav-link <?= $isPenjadwalanOtomatisMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#superPenjadwalanOtomatisSubmenu" role="button" aria-expanded="<?= $isPenjadwalanOtomatisMenu ? 'true' : 'false' ?>" aria-controls="superPenjadwalanOtomatisSubmenu">
                            <span class="label-block"><i class="fas fa-wand-magic-sparkles"></i><span>Penjadwalan Otomatis</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isPenjadwalanOtomatisMenu ? 'show' : '' ?>" id="superPenjadwalanOtomatisSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_penjadwalan_otomatis_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/super/jadwal-tanding/penjadwalan-otomatis') ?>">Penjadwalan Otomatis Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_penjadwalan_otomatis_seni' ? 'active' : '' ?>" href="<?= base_url('admin/super/jadwal-seni/penjadwalan-otomatis') ?>">Penjadwalan Otomatis Seni</a>
                            </div>
                        </div>
                    </div>

                    <div>
                        <a class="admin-nav-link <?= $isJadwalPertandinganMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#superJadwalPertandinganSubmenu" role="button" aria-expanded="<?= $isJadwalPertandinganMenu ? 'true' : 'false' ?>" aria-controls="superJadwalPertandinganSubmenu">
                            <span class="label-block"><i class="fas fa-calendar-alt"></i><span>Jadwal Pertandingan</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isJadwalPertandinganMenu ? 'show' : '' ?>" id="superJadwalPertandinganSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_jadwal_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/super/jadwal-tanding') ?>">Jadwal Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_tukar_atlet' ? 'active' : '' ?>" href="<?= base_url('admin/super/jadwal-tanding/tukar-atlet') ?>">Tukar Atlet</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pembuatan_jadwal_jadwal_seni' ? 'active' : '' ?>" href="<?= base_url('admin/super/jadwal-seni') ?>">Jadwal Seni</a>
                            </div>
                        </div>
                    </div>

                    <?php endif; ?>

                    <?php if ($superMode === 'perngaturan_kategori_lomba') : ?>
                     <a class="admin-nav-link <?= ($activeMenu ?? '') === 'pengaturan_kategori_lomba' && str_contains(uri_string(), 'kategori-usia') ? 'active' : '' ?>" href="<?= base_url('admin/super/kategori-usia') ?>">
                         <span class="label-block"><i class="fas fa-users-between-lines"></i><span>Kategori Usia</span></span>
                     </a>
                     <a class="admin-nav-link <?= ($activeMenu ?? '') === 'pengaturan_kategori_lomba' && str_contains(uri_string(), 'kategori-lomba') ? 'active' : '' ?>" href="<?= base_url('admin/super/kategori-lomba') ?>">
                         <span class="label-block"><i class="fas fa-medal"></i><span>Kategori Lomba</span></span>
                     </a>
                     <a class="admin-nav-link <?= ($activeMenu ?? '') === 'pengaturan_kategori_lomba' && str_contains(uri_string(), 'kelas-tanding') ? 'active' : '' ?>" href="<?= base_url('admin/super/kelas-tanding') ?>">
                         <span class="label-block"><i class="fas fa-hand-fist"></i><span>Kelas Tanding</span></span>
                     </a>
                     <a class="admin-nav-link <?= ($activeMenu ?? '') === 'pengaturan_kategori_lomba' && str_contains(uri_string(), 'sub-kategori-seni') ? 'active' : '' ?>" href="<?= base_url('admin/super/sub-kategori-seni') ?>">
                         <span class="label-block"><i class="fas fa-masks-theater"></i><span>Sub Kategori Seni</span></span>
                     </a>
                     <?php endif; ?>
                    <?php else : ?>
                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= base_url($adminPanel['home']) ?>">
                        <span class="label-block"><i class="fas fa-chart-line"></i><span>Dashboard</span></span>
                    </a>
                    <?php endif; ?>

                    <?php if ($adminRole === 'bendahara') : ?>
                    <div>
                        <a class="admin-nav-link <?= ($activeMenu ?? '') === 'pembayaran' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminPembayaranSubmenu" role="button" aria-expanded="<?= ($activeMenu ?? '') === 'pembayaran' ? 'true' : 'false' ?>" aria-controls="adminPembayaranSubmenu">
                            <span class="label-block"><i class="fas fa-wallet"></i><span>Transaksi Pembayaran</span></span>
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
                                <a class="admin-submenu-link <?= ($paymentSubmenu ?? '') === 'biaya_kontingen' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/pembayaran/biaya-kontingen') ?>">Biaya Kontingen</a>
                            </div>
                        </div>
                    </div>

                    <a class="admin-nav-link <?= ($activeMenu ?? '') === 'kontingen' ? 'active' : '' ?>" href="<?= base_url('admin/bendahara/kontingen') ?>">
                        <span class="label-block"><i class="fas fa-people-group"></i><span>Data Kontingen</span></span>
                    </a>
                    <?php endif; ?>

                    <?php if ($adminRole === 'sekretariat') : ?>
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
                    <div>
                        <a class="admin-nav-link <?= ($activeMenu ?? '') === 'kontingen' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminKontingenSubmenu" role="button" aria-expanded="<?= ($activeMenu ?? '') === 'kontingen' ? 'true' : 'false' ?>" aria-controls="adminKontingenSubmenu">
                            <span class="label-block"><i class="fas fa-people-group"></i><span>Data Kontingen</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= ($activeMenu ?? '') === 'kontingen' ? 'show' : '' ?>" id="adminKontingenSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($kontingenSubmenu ?? '') === 'sub_kontingen' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kontingen') ?>">Sub Kontingen</a>
                                <a class="admin-submenu-link <?= ($kontingenSubmenu ?? '') === 'rekap_atlet' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kontingen/rekap-atlet') ?>">Rekap Atlet</a>
                            </div>
                        </div>
                    </div>
                    <?php $isAtletMenu = in_array(($activeMenu ?? ''), ['data_atlet', 'data_bpjs', 'peserta_tanding', 'kelompok_seni', 'cek_data_arsip'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isAtletMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminAtletSubmenu" role="button" aria-expanded="<?= $isAtletMenu ? 'true' : 'false' ?>" aria-controls="adminAtletSubmenu">
                            <span class="label-block"><i class="fas fa-users"></i><span>Data Atlet</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isAtletMenu ? 'show' : '' ?>" id="adminAtletSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'data_atlet' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/data-atlet') ?>">Data Atlet</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'cek_data_arsip' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/cek-data-arsip') ?>">Data Arsip</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'data_bpjs' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/data-bpjs') ?>">Data BPJS</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'peserta_tanding' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/peserta-tanding') ?>">Peserta Tanding</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'kelompok_seni' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/kelompok-seni') ?>">Peserta Seni</a>
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
                    <?php $isToolsMenu = in_array(($activeMenu ?? ''), ['pengadaan_medali', 'nomor_sertifikat', 'pencetakan_id_card'], true); ?>
                    <div>
                        <a class="admin-nav-link <?= $isToolsMenu ? 'active' : '' ?>" data-bs-toggle="collapse" href="#adminToolsSubmenu" role="button" aria-expanded="<?= $isToolsMenu ? 'true' : 'false' ?>" aria-controls="adminToolsSubmenu">
                            <span class="label-block"><i class="fas fa-screwdriver-wrench"></i><span>Tools</span></span>
                            <i class="fas fa-chevron-right chevron"></i>
                        </a>
                        <div class="admin-submenu collapse <?= $isToolsMenu ? 'show' : '' ?>" id="adminToolsSubmenu">
                            <div class="admin-submenu-inner">
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pencetakan_id_card' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/id-card') ?>">Cetak ID Card</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'pengadaan_medali' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/pengadaan-medali') ?>">Pengadaan Medali</a>
                                <a class="admin-submenu-link <?= ($activeMenu ?? '') === 'nomor_sertifikat' ? 'active' : '' ?>" href="<?= base_url('admin/sekretariat/nomor-sertifikat') ?>">Nomor Sertifikat</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </nav>
            </div>

            <div class="mt-auto">
                <a href="<?= base_url('admin/logout') ?>" class="btn btn-admin-logout w-100 rounded-pill" title="Logout">
                    <i class="fas fa-sign-out-alt me-2"></i><span class="logout-label">Logout</span>
                </a>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-actions">
                    <div>
                        <div class="eyebrow"><?= esc($adminPanel['area']) ?></div>
                        <h1 class="admin-page-title h2 mb-0"><?= esc($title ?? 'Admin Panel') ?></h1>
                    </div>
                    <button type="button" class="admin-collapse-toggle" data-admin-sidebar-collapse aria-label="Minimize sidebar" title="Minimize sidebar">
                        <i class="fas fa-angles-left"></i>
                    </button>
                    <button type="button" class="admin-mobile-toggle" data-admin-sidebar-open aria-label="Buka menu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </header>

            <?= $this->renderSection('content') ?>

            <footer class="admin-footer">
                <span>&copy; <?= date('Y') ?> Digital Pencak Silat. All Rights Reserved.</span>
                <span class="footer-version">Version <?= esc(app_version()) ?></span>
            </footer>
        </main>
    </div>

    <script src="<?= online_asset('bootstrap_5_bundle_js') ?>"></script>
    <script src="<?= online_asset('jquery_3_js') ?>"></script>
    <script src="<?= online_asset('datatables_jquery_js') ?>"></script>
    <script src="<?= online_asset('datatables_bs5_js') ?>"></script>
    <script src="<?= online_asset('datatables_responsive_js') ?>"></script>
    <script src="<?= online_asset('datatables_responsive_bs5_js') ?>"></script>
    <script src="<?= online_asset('jszip_js') ?>"></script>
    <script src="<?= online_asset('datatables_buttons_js') ?>"></script>
    <script src="<?= online_asset('datatables_buttons_html5_js') ?>"></script>
    <script src="<?= online_asset('datatables_buttons_print_js') ?>"></script>
    <script src="<?= online_asset('datatables_buttons_colvis_js') ?>"></script>
    <script src="<?= online_asset('sweetalert2_js') ?>"></script>
    <script src="<?= online_asset('toastr_js') ?>"></script>
    <script src="<?= base_url('assets/bracket-pertandingan/jquery.bracket.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin-export-datatable.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin-currency-formatter.js') ?>"></script>
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

        // initAdminExportTable — loaded from public/assets/js/admin-export-datatable.js

        window.confirmAdminAction = function(target, title, text, confirmText = 'Lanjutkan') {
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
                if (!result.isConfirmed || !target) {
                    return;
                }

                if (typeof target.submit === 'function') {
                    target.submit();
                    return;
                }

                const href = target.getAttribute ? target.getAttribute('href') : null;
                if (href) {
                    window.location.href = href;
                }
            });

            return false;
        };

        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const openButton = document.querySelector('[data-admin-sidebar-open]');
            const closeButtons = document.querySelectorAll('[data-admin-sidebar-close]');
            const collapseButton = document.querySelector('[data-admin-sidebar-collapse]');

            const COLLAPSE_KEY = 'dps_admin_sidebar_collapsed_v1';
            const applyCollapsed = (collapsed) => {
                body.classList.toggle('admin-sidebar-collapsed', collapsed);
                if (!collapseButton) {
                    return;
                }

                collapseButton.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
                collapseButton.title = collapsed ? 'Expand sidebar' : 'Minimize sidebar';
                const icon = collapseButton.querySelector('i');
                if (icon) {
                    icon.className = collapsed ? 'fas fa-angles-right' : 'fas fa-angles-left';
                }
            };

            const getStoredCollapsed = () => {
                try {
                    return window.localStorage.getItem(COLLAPSE_KEY) === '1';
                } catch (e) {
                    return false;
                }
            };

            const setStoredCollapsed = (collapsed) => {
                try {
                    window.localStorage.setItem(COLLAPSE_KEY, collapsed ? '1' : '0');
                } catch (e) {
                    // ignore
                }
            };

            const openSidebar = () => body.classList.add('admin-sidebar-open');
            const closeSidebar = () => body.classList.remove('admin-sidebar-open');

            openButton?.addEventListener('click', openSidebar);
            closeButtons.forEach((button) => button.addEventListener('click', closeSidebar));

            applyCollapsed(getStoredCollapsed());
            collapseButton?.addEventListener('click', () => {
                const next = !body.classList.contains('admin-sidebar-collapsed');
                applyCollapsed(next);
                setStoredCollapsed(next);
            });

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

            document.querySelectorAll('.admin-datatable-export').forEach((table) => {
                if (!table.id) {
                    table.id = 'admin-table-' + Math.random().toString(36).slice(2, 10);
                }

                var config = {};
                var configAttr = table.getAttribute('data-export-config');
                if (configAttr) {
                    try { config = JSON.parse(configAttr); } catch(e) { console.warn('Invalid data-export-config JSON:', e); }
                }
                window.initAdminExportTable('#' + table.id, config);
            });

            const normalizeAdminTableActionButtons = () => {
                document.querySelectorAll('.admin-table td.no-export .dropdown > [data-bs-toggle="dropdown"], .admin-table td:last-child .dropdown > [data-bs-toggle="dropdown"]').forEach((toggle) => {
                    toggle.classList.add('admin-action-toggle');

                    const label = toggle.textContent.trim();
                    if (!label) {
                        const text = document.createElement('span');
                        text.className = 'admin-action-toggle-label';
                        text.textContent = 'Aksi';
                        toggle.appendChild(text);
                    }
                });
            };

            normalizeAdminTableActionButtons();
            document.addEventListener('draw.dt', normalizeAdminTableActionButtons);

            const tableDropdownState = new WeakMap();
            const positionTableDropdown = (toggle, menu) => {
                const rect = toggle.getBoundingClientRect();
                const menuWidth = Math.max(menu.offsetWidth || 192, 192);
                const menuHeight = menu.offsetHeight || 0;
                const viewportPadding = 12;
                let left = rect.right - menuWidth;
                let top = rect.bottom + 6;

                left = Math.max(viewportPadding, Math.min(left, window.innerWidth - menuWidth - viewportPadding));

                if (top + menuHeight > window.innerHeight - viewportPadding) {
                    top = Math.max(viewportPadding, rect.top - menuHeight - 6);
                }

                menu.style.position = 'fixed';
                menu.style.left = left + 'px';
                menu.style.top = top + 'px';
                menu.style.right = 'auto';
                menu.style.bottom = 'auto';
                menu.style.transform = 'none';
                menu.style.zIndex = '2000';
            };

            document.addEventListener('show.bs.dropdown', (event) => {
                const toggle = event.target?.matches?.('[data-bs-toggle="dropdown"]')
                    ? event.target
                    : event.target?.querySelector?.('[data-bs-toggle="dropdown"]');
                const dropdown = toggle?.closest?.('.dropdown, .dropup, .dropend, .dropstart');

                if (!toggle || !dropdown?.closest('.admin-table')) {
                    return;
                }

                const menu = dropdown.querySelector('.dropdown-menu');
                if (!menu) {
                    return;
                }

                tableDropdownState.set(toggle, {
                    menu,
                    parent: menu.parentNode,
                    nextSibling: menu.nextSibling,
                    toggle,
                });

                document.body.appendChild(menu);
                menu.classList.add('admin-floating-dropdown-menu');
                requestAnimationFrame(() => positionTableDropdown(toggle, menu));
            });

            document.addEventListener('shown.bs.dropdown', (event) => {
                const toggle = event.target?.matches?.('[data-bs-toggle="dropdown"]')
                    ? event.target
                    : event.target?.querySelector?.('[data-bs-toggle="dropdown"]');
                const state = toggle ? tableDropdownState.get(toggle) : null;
                if (state) {
                    positionTableDropdown(state.toggle, state.menu);
                }
            });

            document.addEventListener('hide.bs.dropdown', (event) => {
                const toggle = event.target?.matches?.('[data-bs-toggle="dropdown"]')
                    ? event.target
                    : event.target?.querySelector?.('[data-bs-toggle="dropdown"]');
                const state = toggle ? tableDropdownState.get(toggle) : null;
                if (!state) {
                    return;
                }

                state.menu.classList.remove('admin-floating-dropdown-menu');
                state.menu.removeAttribute('style');

                if (state.nextSibling) {
                    state.parent.insertBefore(state.menu, state.nextSibling);
                } else {
                    state.parent.appendChild(state.menu);
                }

                tableDropdownState.delete(toggle);
            });

            window.addEventListener('resize', () => {
                document.querySelectorAll('.admin-table .dropdown.show [data-bs-toggle="dropdown"]').forEach((toggle) => {
                    const state = tableDropdownState.get(toggle);
                    if (state) {
                        positionTableDropdown(state.toggle, state.menu);
                    }
                });
            });

            document.addEventListener('scroll', () => {
                document.querySelectorAll('.admin-table .dropdown.show [data-bs-toggle="dropdown"]').forEach((toggle) => {
                    window.bootstrap?.Dropdown.getInstance(toggle)?.hide();
                });
            }, true);

            document.querySelectorAll('.setting-card-input').forEach((input) => {
                input.addEventListener('change', () => {
                    const card = input.closest('.setting-card');
                    const badge = card?.querySelector('.setting-status-badge');
                    card?.classList.toggle('is-active', input.checked);
                    card?.classList.toggle('is-inactive', !input.checked);
                    if (badge) {
                        badge.textContent = input.checked ? 'Aktif' : 'Nonaktif';
                        badge.classList.toggle('active', input.checked);
                        badge.classList.toggle('inactive', !input.checked);
                    }
                });
            });
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>

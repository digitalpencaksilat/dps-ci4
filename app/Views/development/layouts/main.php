<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc($description ?? 'Pusat Pengembangan DPS') ?>">
    <meta name="author" content="DPS">
    <meta name="theme-color" content="#c60000">
    <title><?= esc($title ?? 'Development Center') ?></title>

    <link rel="icon" type="image/png" href="<?= base_url('assets/images/brand/dps/logo.png') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
    <link href="<?= online_asset('fontawesome_6_css') ?>" rel="stylesheet">
    <link href="<?= online_asset('toastr_css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/development/css/development.css') ?>" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= base_url('development') ?>">
                <img src="<?= base_url('assets/images/brand/dps/logo.png') ?>" class="me-2" style="height: 40px;" alt="Logo">
                <span class="text-uppercase fw-bold"><?= esc($headerTitle ?? 'Development Center') ?></span>
            </a>
            <?php if (service('request')->getUri()->getPath() !== 'development') : ?>
            <a href="<?= base_url('development') ?>" class="back-btn">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <footer>
        <div class="container text-center">
            <p class="footer-text">&copy; <?= date('Y') ?> DIGITAL PENCAK SILAT &middot; v<?= esc(app_version()) ?></p>
        </div>
    </footer>

    <script src="<?= online_asset('jquery_3_js') ?>"></script>
    <script src="<?= online_asset('bootstrap_5_bundle_js') ?>"></script>
    <script src="<?= online_asset('toastr_js') ?>"></script>
    <script src="<?= online_asset('sweetalert2_js') ?>"></script>
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            newestOnTop: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
        };

        <?php if (session()->getFlashdata('success')) : ?>
            toastr.success(<?= json_encode((string) session()->getFlashdata('success')) ?>);
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            toastr.error(<?= json_encode((string) session()->getFlashdata('error')) ?>);
        <?php endif; ?>
    </script>
</body>

</html>

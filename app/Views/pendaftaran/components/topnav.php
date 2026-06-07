<nav class="navbar navbar-expand-lg navbar-dark fixed-top py-1" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= base_url('/') ?>">
            <img src="<?= get_instance()->get_setting('event_logo', 'pendaftaran/gambar_dan_juknis') ?>"
                alt="Logo" width="45" class="me-2 h-auto" decoding="async">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/') ?>">Home</a>
                </li>

                <li class="nav-item"><a class="nav-link" href="<?= base_url('registrasi') ?>">Registrasi</a></li>

                <?php foreach (($nav_items ?? []) as $nav_item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($nav_item->status == 'disabled') ? 'disabled' : '' ?>" href="<?= $nav_item->link ?>">
                            <?= $nav_item->text ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <li class="nav-item">
                    <a class="btn btn-outline-light px-4 ms-lg-3 my-2 my-lg-0 rounded-pill" href="<?= base_url('pendaftaran/login') ?>">
	                        <i class="fa fa-sign-in-alt me-1"></i> Masuk
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

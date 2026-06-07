<section class="landing-hero text-white">
    <div class="hero-backdrop"></div>
    <div class="container position-relative hero-content-wrap">
        <div class="row align-items-center g-5 flex-lg-row-reverse">
            <div class="col-lg-5 text-center reveal">
                <div class="hero-poster-shell mx-auto shadow-lg">
                    <?php if ($poster = get_setting('poster', 'pendaftaran/gambar_dan_juknis')) : ?>
                        <img src="<?= esc($poster) ?>" alt="Poster Event" class="img-fluid hero-poster-image" decoding="async" fetchpriority="high">
                    <?php else : ?>
                        <div class="poster-fallback-landing">
                            <i class="fa-solid fa-trophy"></i>
                            <span>Poster Event</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-7 reveal">
                <span class="landing-subtitle"><i class="fa-solid fa-medal me-2"></i>KEJUARAAN PENCAK SILAT</span>
                <h1 class="landing-title mb-3"><?= esc($event['event_name'] ?: 'Digital Pencak Silat') ?></h1>

                <p class="landing-lead mb-4">
                    <?= esc($event['landing_page_description'] ?: 'Bangkitkan semangat juangmu! Jadilah saksi lahirnya legenda baru di arena ini. Tunjukkan ketangguhan mental dan fisik terbaikmu di hadapan dunia. Ini bukan sekadar pertandingan, ini adalah pembuktian jati diri seorang pendekar sejati!') ?>
                </p>

                <div class="countdown-caption">Pertandingan Dimulai Dalam:</div>
                <div class="countdown-grid mb-4" id="landingCountdown">
                    <div class="countdown-box">
                        <span class="countdown-number" id="days">00</span>
                        <span class="countdown-label">Hari</span>
                    </div>
                    <div class="countdown-box">
                        <span class="countdown-number" id="hours">00</span>
                        <span class="countdown-label">Jam</span>
                    </div>
                    <div class="countdown-box">
                        <span class="countdown-number" id="minutes">00</span>
                        <span class="countdown-label">Menit</span>
                    </div>
                    <div class="countdown-box">
                        <span class="countdown-number" id="seconds">00</span>
                        <span class="countdown-label">Detik</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 hero-cta-group">
                    <a href="<?= base_url('registrasi') ?>" class="btn btn-danger btn-lg rounded-pill px-4">
                        <i class="fa-solid fa-file-signature me-2"></i>Daftar Sekarang
                    </a>
                    <a href="<?= base_url('pendaftaran/download-juknis') ?>" class="btn btn-light btn-lg rounded-pill px-4 text-danger fw-semibold">
                        <i class="fa-solid fa-download me-2"></i>Unduh Proposal
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-pattern-soft bg-dot-white-section">
    <div class="container py-5">
        <div class="section-title text-center mb-5 reveal">
            <h2>Informasi Event</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4 reveal">
                <div class="info-card h-100">
                    <div class="info-icon-wrapper"><i class="fa-solid fa-user-shield info-icon"></i></div>
                    <h4>Penyelenggara</h4>
                    <p class="text-muted small mb-2">Kompetisi ini diselenggarakan secara profesional oleh:</p>
                    <p class="fw-bold mb-0 text-dark fs-5 text-uppercase"><?= esc($event['event_host'] ?: '-') ?></p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="info-card h-100">
                    <div class="info-icon-wrapper"><i class="fa-regular fa-calendar-check info-icon"></i></div>
                    <h4>Waktu & Tempat</h4>
                    <p class="text-muted small mb-2">Pelaksanaan pertandingan pada tanggal:</p>
                    <p class="fw-bold text-danger mb-1"><?= esc($event['date_start'] ?: '-') ?> - <?= esc($event['date_end'] ?: '-') ?></p>
                    <p class="fw-bold text-dark mb-0"><i class="fa-solid fa-location-dot me-1"></i><?= esc($event['event_location'] ?: '-') ?></p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="info-card h-100">
                    <div class="info-icon-wrapper"><i class="fa-solid fa-clipboard-list info-icon"></i></div>
                    <h4>Technical Meeting</h4>
                    <p class="text-muted small mb-2">Sinkronisasi regulasi & pengundian bagan:</p>
                    <p class="fw-bold mb-1 text-dark fs-5"><?= esc($event['technical_meeting_date'] ?: '-') ?></p>
                    <p class="text-secondary small"><i class="fa-solid fa-map-pin me-1"></i><?= esc($event['technical_meeting_location'] ?: '-') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="section-title text-center mb-5 reveal">
            <h2>Mengapa Harus Ikut?</h2>
            <p class="text-muted">Keunggulan event kami yang memberikan pengalaman terbaik</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 reveal"><div class="feature-box d-flex align-items-start p-3 shadow-sm"><div class="feature-badge me-3"><i class="fa-solid fa-laptop-code"></i></div><div><h5 class="fw-bold mb-1">Digital Scoring System</h5><p class="text-muted small mb-0">Sistem penilaian digital real-time yang transparan, akurat, dan dapat dipantau langsung.</p></div></div></div>
            <div class="col-lg-4 col-md-6 reveal"><div class="feature-box d-flex align-items-start p-3 shadow-sm"><div class="feature-badge me-3"><i class="fa-solid fa-gavel"></i></div><div><h5 class="fw-bold mb-1">Wasit Juri Berlisensi</h5><p class="text-muted small mb-0">Pertandingan dipimpin langsung oleh wasit dan juri berlisensi resmi untuk menjamin fair play.</p></div></div></div>
            <div class="col-lg-4 col-md-6 reveal"><div class="feature-box d-flex align-items-start p-3 shadow-sm"><div class="feature-badge me-3"><i class="fa-solid fa-user-tie"></i></div><div><h5 class="fw-bold mb-1">Professional EO</h5><p class="text-muted small mb-0">Event ditangani oleh tim organizer berpengalaman dengan alur dan manajemen waktu yang rapi.</p></div></div></div>
            <div class="col-lg-4 col-md-6 reveal"><div class="feature-box d-flex align-items-start p-3 shadow-sm"><div class="feature-badge me-3"><i class="fa-solid fa-kit-medical"></i></div><div><h5 class="fw-bold mb-1">Tim Medis Professional</h5><p class="text-muted small mb-0">Kesiapsiagaan tim medis dan peralatan standar untuk penanganan cedera atlet secara cepat dan tepat.</p></div></div></div>
            <div class="col-lg-4 col-md-6 reveal"><div class="feature-box d-flex align-items-start p-3 shadow-sm"><div class="feature-badge me-3"><i class="fa-solid fa-print"></i></div><div><h5 class="fw-bold mb-1">Kemudahan Administrasi</h5><p class="text-muted small mb-0">Sistem terpadu via website untuk pendaftaran, login kontingen, hingga pengelolaan kategori dan pembayaran.</p></div></div></div>
            <div class="col-lg-4 col-md-6 reveal"><div class="feature-box d-flex align-items-start p-3 shadow-sm"><div class="feature-badge me-3"><i class="fa-solid fa-medal"></i></div><div><h5 class="fw-bold mb-1">Medali Eksklusif</h5><p class="text-muted small mb-0">Penghargaan terbaik berupa medali custom berkualitas tinggi dengan desain elegan untuk para juara.</p></div></div></div>
        </div>
    </div>
</section>

<?php
$activeCards = array_filter($event['category_cards'] ?? [], fn($c) => !empty($c['active']));
$cardCount = count($activeCards);
?>
<?php if ($cardCount > 0) : ?>
<section class="py-5 bg-white border-top">
    <div class="container py-5">
        <div class="section-title text-center mb-5 reveal">
            <h2>Kategori Pertandingan</h2>
            <p class="text-muted">Kelas yang diperlombakan untuk <?= esc($event['fight_category'] ?: '-') ?></p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $colClass = match ($cardCount) {
                1 => 'col-md-8 col-lg-6',
                2 => 'col-md-6 col-lg-5',
                3 => 'col-md-6 col-lg-4',
                default => 'col-md-6 col-lg-3',
            };
            ?>
            <?php foreach ($activeCards as $card) : ?>
                <div class="<?= $colClass ?> reveal">
                    <div class="category-card-modern">
                        <?= webp_picture($card['image'], [
                            'alt'      => $card['label'],
                            'class'    => 'category-img-modern',
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                        ]) ?>
                        <div class="category-overlay-modern">
                            <i class="<?= esc($card['icon']) ?>"></i>
                            <h4><?= esc($card['label']) ?></h4>
                            <p><?= esc($card['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-5 bg-dot-dark-section">
    <div class="container py-5">
        <div class="section-title text-center mb-5 reveal">
            <h2>Timeline Kegiatan</h2>
        </div>
        <div class="timeline-modern">
            <div class="timeline-item-modern reveal">
                <span class="timeline-date-modern"><?= esc($event['registration_start'] ?: '-') ?> - <?= esc($event['registration_end'] ?: '-') ?></span>
                <h4>Pendaftaran Online</h4>
                <p class="text-muted small mb-2">Via Website Resmi</p>
                <p class="mb-0">Pendaftaran dibuka untuk seluruh kontingen. Lengkapi data peserta dan administrasi melalui sistem.</p>
            </div>
            <div class="timeline-item-modern reveal">
                <span class="timeline-date-modern"><?= esc($event['technical_meeting_date'] ?: '-') ?></span>
                <h4>Technical Meeting</h4>
                <p class="text-muted small mb-2"><?= esc($event['technical_meeting_location'] ?: '-') ?></p>
                <p class="mb-0">Pembahasan aturan pertandingan, drawing undian, dan pembagian jadwal tanding.</p>
            </div>
            <div class="timeline-item-modern reveal">
                <span class="timeline-date-modern"><?= esc($event['date_start'] ?: '-') ?> - <?= esc($event['date_end'] ?: '-') ?></span>
                <h4>Pelaksanaan Pertandingan</h4>
                <p class="text-muted small mb-2"><?= esc($event['event_location'] ?: '-') ?></p>
                <p class="mb-0">Pelaksanaan pertandingan dari babak penyisihan hingga final sesuai jadwal event.</p>
            </div>
        </div>
    </div>
</section>

<section class="landing-closing-cta text-white text-center reveal">
    <div class="container py-5">
        <h2 class="mb-3">Tunjukkan Taringmu!</h2>
        <p class="lead mb-4">Jangan biarkan kesempatan berlalu. Arena memanggil namamu untuk tampil sebagai pendekar terbaik.</p>
        <div class="d-flex justify-content-center flex-wrap gap-3">
            <a href="<?= base_url('registrasi') ?>" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-danger">Daftar Sekarang</a>
            <?php if (! empty($event['contact_person'])) : ?>
                <a href="https://wa.me/<?= preg_replace('/\D+/', '', $event['contact_person']) ?>" target="_blank" class="btn btn-outline-light rounded-pill px-4 py-2 fw-bold">Hubungi Panitia</a>
            <?php endif; ?>
        </div>
    </div>
</section>

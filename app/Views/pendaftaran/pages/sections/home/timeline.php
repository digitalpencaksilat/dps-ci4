<!-- Hero Section -->
<section id="beranda" class="hero-section" style='background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.7)), url("<?= base_url("assets/images/brand/" . strtolower(ci3_config_item("brand_abbreviation", "pendaftaran/profil_kejuaraan")) . "/ilustrasi/ilustrasiv2.jpg") ?>"); background-size: cover; background-position: center; background-attachment: fixed;'>
    <div class="container">
        <div class="row align-items-center flex-lg-row-reverse">
            <!-- Poster Column -->
            <div class="col-lg-5 col-md-12 text-center mb-5 mb-lg-0 reveal">
                <svg class="hero-poster img-fluid" width="380" height="540" viewBox="0 0 350 500" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" style="background-color: #eee; border-radius: 15px; overflow: hidden;">
                    <defs>
                        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#C60000;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#800000;stop-opacity:1" />
                        </linearGradient>
                    </defs>

                    <rect width="100%" height="100%" fill="#1a1a1a" />

                    <image
                        href="<?= get_instance()->get_setting('poster', 'pendaftaran/gambar_dan_juknis') ?>"
                        x="0" y="0" width="100%" height="100%"
                        preserveAspectRatio="xMidYMid slice" />

                    <rect width="100%" height="100%" fill="rgba(0,0,0,0.2)" />

                    <circle cx="50" cy="50" r="100" fill="rgba(255,255,255,0.05)" />
                </svg>
            </div>

            <!-- Content Column -->
            <div class="col-lg-7 col-md-12 reveal">
                <h4 class="event-subtitle"><i class="fa-solid fa-medal me-2"></i>KEJUARAAN PENCAK SILAT</h4>
                <h1 class="event-title"><?= get_instance()->get_setting('event_name') ?></h1>

                <!-- Modified Motivational Text -->
                <p class="lead mb-4 fw-light" style="opacity: 0.95; max-width: 95%; border-left: 4px solid var(--primary-red); padding-left: 20px;">
                    Bangkitkan semangat juangmu! Jadilah saksi lahirnya legenda baru di arena ini. Tunjukkan ketangguhan mental dan fisik terbaikmu di hadapan dunia. Ini bukan sekadar pertandingan, ini adalah pembuktian jati diri seorang pendekar sejati!
                </p>

                <!-- Countdown -->
                <div class="mb-4">
                    <small class="text-uppercase text-warning fw-bold mb-2 d-block">Pertandingan Dimulai Dalam:</small>
                    <div class="countdown-container" id="countdown">
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
                </div>

                <!-- Symmetrical Buttons -->
                <div class="hero-btn-group">
                    <a href="<?= base_url('registrasi') ?>" class="hero-btn hero-btn-primary">
                        <i class="fa-solid fa-file-signature me-2"></i> Daftar Sekarang
                    </a>
                    <a href="<?= base_url("pendaftaran/download-juknis") ?>" class="hero-btn hero-btn-outline">
                        <i class="fa-solid fa-download me-2"></i> Unduh Proposal
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Info/About Section -->
<section id="tentang" class="py-5 bg-pattern">
    <div class="container py-5">
        <div class="section-title reveal">
            <h2>INFORMASI EVENT</h2>
        </div>
        <div class="row g-4 justify-content-center">

            <div class="col-md-4 reveal">
                <div class="info-card h-100">
                    <div class="info-icon-wrapper">
                        <i class="fa-solid fa-user-shield info-icon"></i>
                    </div>
                    <h4>PENYELENGGARA</h4>
                    <p class="text-muted small mb-2">Kompetisi ini diselenggarakan secara profesional oleh:</p>
                    <p class="fw-bold mb-0 text-dark fs-5 text-uppercase">
                        <?= ci3_config_item('event_host', 'pendaftaran/profil_kejuaraan') ?>
                    </p>
                </div>
            </div>

            <div class="col-md-4 reveal">
                <div class="info-card h-100">
                    <div class="info-icon-wrapper">
                        <i class="fa-regular fa-calendar-check info-icon"></i>
                    </div>
                    <h4>WAKTU & TEMPAT</h4>
                    <p class="text-muted small mb-2">Pelaksanaan pertandingan pada tanggal:</p>
                    <p class="fw-bold text-danger mb-1">
                        <?= ci3_config_item('date_start', 'pendaftaran/profil_kejuaraan') . ' - ' . ci3_config_item('date_end', 'pendaftaran/profil_kejuaraan') ?>
                    </p>
                    <p class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-location-dot me-1"></i> <?= ci3_config_item('event_location', 'pendaftaran/profil_kejuaraan') ?>
                    </p>
                </div>
            </div>

            <div class="col-md-4 reveal">
                <div class="info-card h-100">
                    <div class="info-icon-wrapper">
                        <i class="fa-solid fa-clipboard-list info-icon"></i>
                    </div>
                    <h4>TECHNICAL MEETING</h4>
                    <p class="text-muted small mb-2">Sinkronisasi regulasi & pengundian bagan:</p>
                    <p class="fw-bold mb-1 text-dark fs-5">
                        <?= ci3_config_item('technical_meeting_date', 'pendaftaran/profil_kejuaraan') ?>
                    </p>
                    <p class="text-secondary small">
                        <i class="fa-solid fa-map-pin me-1"></i> <?= ci3_config_item('technical_meeting_location', 'pendaftaran/profil_kejuaraan') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- NEW SECTION: Kelebihan Event -->
<section id="kelebihan" class="py-5 bg-white">
    <div class="container py-5">
        <div class="section-title reveal">
            <h2>MENGAPA HARUS IKUT?</h2>
            <p class="text-muted">Keunggulan event kami yang memberikan pengalaman terbaik</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Feature 1: Digital Scoring -->
            <div class="col-lg-4 col-md-6 reveal">
                <div class="d-flex align-items-start p-3 shadow-sm feature-box">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-laptop-code fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Digital Scoring System</h5>
                        <p class="text-muted small mb-0">Sistem penilaian digital real-time yang transparan, akurat, dan dapat dipantau langsung.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 2: Wasit Juri -->
            <div class="col-lg-4 col-md-6 reveal">
                <div class="d-flex align-items-start p-3 shadow-sm feature-box">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-gavel fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Wasit Juri Berlisensi</h5>
                        <p class="text-muted small mb-0">Pertandingan dipimpin langsung oleh wasit dan juri yang memiliki lisensi resmi untuk menjamin fair play.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 3: EO Professional -->
            <div class="col-lg-4 col-md-6 reveal">
                <div class="d-flex align-items-start p-3 shadow-sm feature-box">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-user-tie fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Professional EO</h5>
                        <p class="text-muted small mb-0">Event ditangani oleh tim organizer berpengalaman dengan manajemen waktu dan alur yang rapi.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 4: Tim Medis -->
            <div class="col-lg-4 col-md-6 reveal">
                <div class="d-flex align-items-start p-3 shadow-sm feature-box">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-kit-medical fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Tim Medis Professional</h5>
                        <p class="text-muted small mb-0">Kesiapsiagaan tim medis dan peralatan standar untuk penanganan cedera atlet secara cepat dan tepat.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 5: Administrasi -->
            <div class="col-lg-4 col-md-6 reveal">
                <div class="d-flex align-items-start p-3 shadow-sm feature-box">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-print fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Kemudahan Administrasi</h5>
                        <p class="text-muted small mb-0">Sistem terpadu via website: Pendaftaran, Cetak ID Card, Bagan, Jadwal, hingga Unduh E-Sertifikat.</p>
                    </div>
                </div>
            </div>

            <!-- Feature 6: Medali Eksklusif -->
            <div class="col-lg-4 col-md-6 reveal">
                <div class="d-flex align-items-start p-3 shadow-sm feature-box">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa-solid fa-medal fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Medali Eksklusif</h5>
                        <p class="text-muted small mb-0">Penghargaan terbaik berupa medali custom berkualitas tinggi dan desain elegan untuk para juara.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- Kategori Pertandingan Section (With Images) -->
<section id="kategori" class="py-5 bg-white">
    <div class="container py-5">
        <div class="section-title reveal">
            <h2>KATEGORI PERTANDINGAN</h2>
            <p class="text-muted">Kelas yang diperlombakan untuk <?= ci3_config_item('fight_category', 'pendaftaran/profil_kejuaraan') ?></p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3 reveal">
                <div class="category-card">
                    <!-- Tanding Image: Action kick -->
                    <img src="<?= base_url('assets/images/brand/' . strtolower(ci3_config_item('brand_abbreviation', 'pendaftaran/profil_kejuaraan')) . '/ilustrasi/tanding.jpg') ?>" class="category-img" alt="Kategori Tanding">
                    <div class="category-overlay">
                        <i class="fa-solid fa-hand-fist category-icon"></i>
                        <h4 class="font-oswald">TANDING</h4>
                        <p class="small mb-0">Full body contact sesuai aturan IPSI terbaru.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal">
                <div class="category-card">
                    <!-- Tunggal Image: Solo focus/stance -->
                    <img src="<?= base_url('assets/images/brand/' . strtolower(ci3_config_item('brand_abbreviation', 'pendaftaran/profil_kejuaraan')) . '/ilustrasi/tunggal.jpg') ?>" class="category-img" alt="Kategori Tunggal">
                    <div class="category-overlay">
                        <i class="fa-solid fa-user category-icon"></i>
                        <h4 class="font-oswald">TUNGGAL</h4>
                        <p class="small mb-0">Peragaan jurus baku tangan kosong & senjata.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal">
                <div class="category-card">
                    <!-- Ganda Image: Intense duo -->
                    <img src="<?= base_url('assets/images/brand/' . strtolower(ci3_config_item('brand_abbreviation', 'pendaftaran/profil_kejuaraan')) . '/ilustrasi/ganda.jpg') ?>" class="category-img" alt="Kategori Ganda">
                    <div class="category-overlay">
                        <i class="fa-solid fa-users category-icon"></i>
                        <h4 class="font-oswald">GANDA</h4>
                        <p class="small mb-0">Koreografi tempur dua pesilat.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal">
                <div class="category-card">
                    <!-- Regu Image: Group training/formation -->
                    <img src="<?= base_url('assets/images/brand/' . strtolower(ci3_config_item('brand_abbreviation', 'pendaftaran/profil_kejuaraan')) . '/ilustrasi/beregu.jpg') ?>" class="category-img" alt="Kategori Regu">
                    <div class="category-overlay">
                        <i class="fa-solid fa-users-viewfinder category-icon"></i>
                        <h4 class="font-oswald">REGU</h4>
                        <p class="small mb-0">Kekompakan gerak 3 pesilat.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section (Removed Verification) -->
<section id="timeline" class="py-5 bg-pattern">
    <div class="container py-5">
        <div class="section-title reveal">
            <h2>TIMELINE KEGIATAN</h2>
        </div>

        <div class="timeline">
            <!-- Timeline Item 1 -->
            <div class="timeline-container left reveal">
                <div class="timeline-content">
                    <span class="timeline-date"><?= ci3_config_item('registration_start', 'pendaftaran/profil_kejuaraan') . ' - ' . ci3_config_item('registration_end', 'pendaftaran/profil_kejuaraan') ?> </span>
                    <h4 class="fw-bold">Pendaftaran Online</h4>
                    <p class="text-muted small">Via Website Resmi</p>
                    <p class="mb-2">Pendaftaran dibuka untuk seluruh kontingen. Upload berkas persyaratan dan bukti transfer.</p>
                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> OPEN</span>
                </div>
            </div>

            <!-- Timeline Item 2 (Former Item 3, Moved to Right) -->
            <div class="timeline-container right reveal">
                <div class="timeline-content">
                    <span class="timeline-date"><?= ci3_config_item('technical_meeting_date', 'pendaftaran/profil_kejuaraan') ?></span>
                    <h4 class="fw-bold">Technical Meeting</h4>
                    <p class="text-muted small"><?= ci3_config_item('technical_meeting_location', 'pendaftaran/profil_kejuaraan') ?></p>
                    <p class="mb-0">Pembahasan aturan pertandingan, drawing undian, dan pembagian jadwal tanding.</p>
                </div>
            </div>

            <!-- Timeline Item 3 (Former Item 4, Moved to Left) -->
            <div class="timeline-container left reveal">
                <div class="timeline-content">
                    <span class="timeline-date"><?= ci3_config_item('date_start', 'pendaftaran/profil_kejuaraan') . ' - ' . ci3_config_item('date_end', 'pendaftaran/profil_kejuaraan') ?> </span>
                    <h4 class="fw-bold">Pelaksanaan Pertandingan</h4>
                    <p class="text-muted small"><?= ci3_config_item('event_location', 'pendaftaran/profil_kejuaraan') ?></p>
                    <p class="mb-0">Babak penyisihan dimulai pukul 08:00 WIB setiap harinya hingga babak Final.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call To Action -->
<section class="py-5 text-white text-center" style="background-color: var(--primary-red);">
    <div class="container reveal">
        <h2 class="font-oswald mb-3">TUNJUKKAN TARINGMU!</h2>
        <p class="lead mb-4">Jangan biarkan kesempatan berlalu. Arena memanggil namamu!</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="#" class="btn btn-light rounded-pill px-4 py-2 fw-bold" style="color: var(--primary-red);">HUBUNGI PANITIA</a>
        </div>
    </div>
</section>
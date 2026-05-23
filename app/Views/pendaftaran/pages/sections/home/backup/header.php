     1|     1|<!-- Wrapper dengan background -->
     2|     2|<div class="hero-wrapper"
     3|     3|	style="background: url('<?= base_url('assets/images/brand/' . strtolower($this->config->item('brand_abbreviation')) . '/ilustrasi/header-home.jpg') ?>') no-repeat center center;
     4|     4|            background-size: cover;
     5|     5|            background-attachment: fixed;
     6|     6|            position: relative;
     7|     7|            padding: 30px 0;">
     8|     8|
     9|     9|	<!-- overlay -->
    10|    10|	<div class="overlay" style="position:absolute; top:0; left:0; width:100%; height:100%;
    11|    11|         background: rgba(33, 32, 32, 0.81); z-index:1;"></div>
    12|    12|
    13|    13|	<div class="container mt-6 position-relative" style="z-index:2;">
    14|    14|		<?php if (get_setting_live_server('kejuaraan_sedang_berlangsung')) : ?>
    15|    15|			<?= view('pendaftaran/components/kejuaraan_berlangsung' ) ?>
    16|    16|		<?php else : ?>
    17|    17|			<div class="row py-5 mt-5">
    18|    18|				<div class="col-12 col-lg-5 d-flex justify-content-center align-items-center">
    19|    19|					<img src="<?= get_instance()->get_setting('poster', 'pendaftaran/gambar_dan_juknis') ?>"
    20|    20|						alt="<?= get_instance()->get_setting('event_name') ?>"
    21|    21|						class="img-fluid w-100 h-auto"
    22|    22|						style="max-height: 600px; object-fit: contain;">
    23|    23|				</div>
    24|    24|				<div class="col-12 col-lg-7 ml-auto mr-auto text-center py-7">
    25|    25|					<h1 class="display-3 fw-bold text-white"><?= get_instance()->get_setting('event_name') ?></h1>
    26|    26|					<h4 class="fw-normal text-light mb-5"><?= get_instance()->get_setting('landing_page_description') ?></h4>
    27|    27|					<div class="buttons">
    28|    28|						<a class="btn btn-primary btn-lg mb-3" href="<?= base_url('registrasi') ?>"><i class="fa fa-user-plus me-2"></i> <?= Daftar Sekarang ?></a>
    29|    29|						<a href="<?= base_url("pendaftaran/download-juknis") ?>" class="btn btn-secondary btn-lg mb-3"><i class="fa fa-download me-2"></i> <?= Download Juknis ?></a>
    30|    30|						<!-- Countdown Timer -->
    31|    31|						<!-- Countdown Section -->
    32|    32|						<div id="countdown" class="d-flex justify-content-center gap-3 mb-4 text-white fw-bold"></div>
    33|    33|
    34|    34|						<style>
    35|    35|							#countdown .time-box {
    36|    36|								background: rgba(0, 0, 0, 0.6);
    37|    37|								/* transparan elegan */
    38|    38|								padding: 20px;
    39|    39|								border-radius: 12px;
    40|    40|								min-width: 90px;
    41|    41|								text-align: center;
    42|    42|								box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    43|    43|								transition: transform 0.2s ease;
    44|    44|							}
    45|    45|
    46|    46|							#countdown .time-box:hover {
    47|    47|								transform: scale(1.05);
    48|    48|							}
    49|    49|
    50|    50|							#countdown .number {
    51|    51|								font-size: 2rem;
    52|    52|								color: #ff4d6d;
    53|    53|								/* merah muda / bisa sesuai branding */
    54|    54|								font-weight: 700;
    55|    55|							}
    56|    56|
    57|    57|							#countdown .label {
    58|    58|								font-size: 0.9rem;
    59|    59|								color: #fff;
    60|    60|								text-transform: uppercase;
    61|    61|								letter-spacing: 1px;
    62|    62|							}
    63|    63|
    64|    64|							@media (max-width: 576px) {
    65|    65|								#countdown {
    66|    66|									display: grid !important;
    67|    67|									grid-template-columns: repeat(2, 1fr);
    68|    68|									gap: 10px;
    69|    69|								}
    70|    70|
    71|    71|								#countdown .time-box {
    72|    72|									min-width: auto;
    73|    73|									width: 100%;
    74|    74|								}
    75|    75|							}
    76|    76|						</style>
    77|    77|
    78|    78|						<script>
    79|    79|							const eventDate = new Date("Oct 26, 2025 00:00:00").getTime();
    80|    80|							const countdownEl = document.getElementById("countdown");
    81|    81|
    82|    82|							const x = setInterval(function() {
    83|    83|								const now = new Date().getTime();
    84|    84|								const distance = eventDate - now;
    85|    85|
    86|    86|								if (distance < 0) {
    87|    87|									clearInterval(x);
    88|    88|									countdownEl.innerHTML = "<div class='text-danger fs-3 fw-bold'>🚨 Pendaftaran Ditutup!</div>";
    89|    89|									return;
    90|    90|								}
    91|    91|
    92|    92|								const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    93|    93|								const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    94|    94|								const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    95|    95|								const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    96|    96|
    97|    97|								countdownEl.innerHTML = `
    98|    98|      <div class="time-box">
    99|    99|        <div class="number">${days}</div>
   100|   100|        <div class="label">Hari</div>
   101|   101|      </div>
   102|   102|      <div class="time-box">
   103|   103|        <div class="number">${hours}</div>
   104|   104|        <div class="label">Jam</div>
   105|   105|      </div>
   106|   106|      <div class="time-box">
   107|   107|        <div class="number">${minutes}</div>
   108|   108|        <div class="label">Menit</div>
   109|   109|      </div>
   110|   110|      <div class="time-box">
   111|   111|        <div class="number">${seconds}</div>
   112|   112|        <div class="label">Detik</div>
   113|   113|      </div>
   114|   114|    `;
   115|   115|							}, 1000);
   116|   116|						</script>
   117|   117|					</div>
   118|   118|				</div>
   119|   119|			</div>
   120|   120|		<?php endif; ?>
   121|   121|	</div>
   122|   122|</div>
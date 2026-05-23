<?php
$this->CI = &get_instance();
?>
<div class="py-7 bg-black bg-gradient">
	<div class="container">
		<div class="row">
			<div class="col-md-12 text-center mb-5 text-light">
				<h2 class="h3 text-light fw-bolder">Alur Kegiatan</h2>
				<p>Jangan lewatkan <?= $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') ?></p>
			</div>
			<div class="row justify-content-between">
				<div class="col-12 col-md-5">
					<div class="row">
						<div class="col-12 mb-3">
							<i class="fas fa-pencil-alt fa-3x text-primary mb-4"></i>
							<h4 class="h5 text-light fw-bolder">Pendaftaran</h4>
							<p class="text-light small"> Pendaftaran <?= $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') ?> dibuka dari tanggal
								<?= $this->CI->config->item('registration_start', 'pendaftaran/profil_kejuaraan') ?> sampai <?= $this->CI->config->item('registration_end', 'pendaftaran/profil_kejuaraan') ?> </p>
						</div>
						<div class="col-12 mb-3">

							<i class="fas fa-users fa-3x text-primary mb-4"></i>
							<h4 class="h5 text-light fw-bolder">Technical Meeting</h4>
							<p class="text-light small">Technical Meeting <?= $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') ?> dilaksanakan tanggal
								<?= $this->CI->config->item('technical_meeting_date', 'pendaftaran/profil_kejuaraan') ?> di <?= $this->CI->config->item('technical_meeting_location', 'pendaftaran/profil_kejuaraan') ?>.</p>
						</div>
						<div class="col-12 mb-3">
							<i class="fas fa-running fa-3x text-primary mb-4"></i>
							<h4 class="h5 text-light fw-bolder">Pertandingan</h4>
							<p class="text-light small">Pertandingan berlangsung dari tanggal
								<?= $this->CI->config->item('date_start', 'pendaftaran/profil_kejuaraan') . ' sampai ' . $this->CI->config->item('date_end', 'pendaftaran/profil_kejuaraan') ?>.
								<br>Pertandingan dilaksanakan di <?= $this->CI->config->item('event_location', 'pendaftaran/profil_kejuaraan') ?>
							</p>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<img src="<?= base_url('assets/images/brand/'.strtolower($this->config->item('brand_abbreviation')).'/ilustrasi/alur-foreground.jpg') ?>" alt="<?= 'event ' . $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') ?>" class="img-fluid shadow-lg">
				</div>
			</div>
		</div>
	</div>
</div>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>
		Bagan Seni - <?php echo $kompetisi_seni->nama_kategori_usia . ' ' . $kompetisi_seni->jenis_kelamin . ' ' . $kompetisi_seni->jenis_seni . ' - ' . $kompetisi_seni->nama_seni ?>
	</title>
	<meta name="theme-color" content="#890108">
	<link rel="icon" type="image/png" href="<?= get_instance()->get_setting('event_logo', 'pendaftaran/gambar_dan_juknis') ?>">
	
	<!-- CSS Resources -->
	<link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">
	<link href="<?php echo base_url() ?>assets/bracket-pertandingan/jquery.bracket.min.css" rel="stylesheet">
	<link href="<?php echo base_url() ?>assets/bracket-pertandingan/bracket.css" rel="stylesheet">
	
	<!-- Custom Style for Public Page -->
	<style>
		body {
			background-color: #f8f9fa;
		}
		.btn-home {
			position: fixed;
			bottom: 20px;
			right: 20px;
			z-index: 9999;
			border-radius: 30px;
			padding: 10px 20px;
			box-shadow: 0 4px 6px rgba(0,0,0,0.1);
		}
		@media print {
			.btn-home {
				display: none;
			}
		}
	</style>

	<!-- jQuery -->
	<script src="<?= online_asset('jquery_3_js') ?>"></script>
	<!-- Jquery Bracket -->
	<script src="<?php echo base_url() ?>assets/bracket-pertandingan/jquery.bracket.min.js"></script>

</head>

<body>
	<a href="<?= base_url() ?>" class="btn btn-primary btn-home shadow-lg">
		<i class="fa fa-home"></i> Kembali ke Beranda
	</a>

	<div class="container-fluid">
		<?= view('print/bagan/seni/components/header' ) ?>
		
		<div class="card shadow-sm border-0 mb-5">
			<div class="card-body p-0">
				<?= view('shared_components/kompetisi_seni/bagan_battle_seni' ) ?>
			</div>
		</div>
	</div>

	<footer class="text-center py-4 text-muted">
		<small>&copy; <?= date('Y') ?> <?= get_instance()->get_setting('event_name'); ?>. All Rights Reserved.</small>
	</footer>
</body>

</html>

     1|<!DOCTYPE html>
     2|<html>
     3|
     4|<head>
     5|	<meta charset="utf-8">
     6|	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
     7|	<title>
     8|		Bagan Seni - <?php echo $kompetisi_seni->nama_kategori_usia . ' ' . $kompetisi_seni->jenis_kelamin . ' ' . $kompetisi_seni->jenis_seni . ' - ' . $kompetisi_seni->nama_seni ?>
     9|	</title>
    10|	<meta name="theme-color" content="#890108">
    11|	<link rel="icon" type="image/png" href="<?= get_instance()->get_setting('event_logo', 'pendaftaran/gambar_dan_juknis') ?>">
    12|	
    13|	<!-- CSS Resources -->
    14|	<link href="<?php echo base_url() ?>assets/print/css/bootstrap.min.css" rel="stylesheet">
    15|	<link href="<?php echo base_url() ?>assets/bracket-pertandingan/jquery.bracket.min.css" rel="stylesheet">
    16|	<link href="<?php echo base_url() ?>assets/bracket-pertandingan/bracket.css" rel="stylesheet">
    17|	
    18|	<!-- Custom Style for Public Page -->
    19|	<style>
    20|		body {
    21|			background-color: #f8f9fa;
    22|		}
    23|		.btn-home {
    24|			position: fixed;
    25|			bottom: 20px;
    26|			right: 20px;
    27|			z-index: 9999;
    28|			border-radius: 30px;
    29|			padding: 10px 20px;
    30|			box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    31|		}
    32|		@media print {
    33|			.btn-home {
    34|				display: none;
    35|			}
    36|		}
    37|	</style>
    38|
    39|	<!-- jQuery -->
    40|	<script src="<?php echo base_url() ?>assets/jquery/jquery.min.js"></script>
    41|	<!-- Jquery Bracket -->
    42|	<script src="<?php echo base_url() ?>assets/bracket-pertandingan/jquery.bracket.min.js"></script>
    43|
    44|</head>
    45|
    46|<body>
    47|	<a href="<?= base_url() ?>" class="btn btn-primary btn-home shadow-lg">
    48|		<i class="fa fa-home"></i> Kembali ke Beranda
    49|	</a>
    50|
    51|	<div class="container-fluid">
    52|		<?= view('print/bagan/seni/components/header' ) ?>
    53|		
    54|		<div class="card shadow-sm border-0 mb-5">
    55|			<div class="card-body p-0">
    56|				<?= view('shared_components/kompetisi_seni/bagan_battle_seni' ) ?>
    57|			</div>
    58|		</div>
    59|	</div>
    60|
    61|	<footer class="text-center py-4 text-muted">
    62|		<small>&copy; <?= date('Y') ?> <?= get_instance()->get_setting('event_name'); ?>. All Rights Reserved.</small>
    63|	</footer>
    64|</body>
    65|
    66|</html>
    67|
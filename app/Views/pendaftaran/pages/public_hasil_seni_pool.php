     1|<!DOCTYPE html>
     2|<html lang="en">
     3|
     4|<head>
     5|	<meta charset="utf-8">
     6|	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
     7|	<title>
     8|		Pool Results - <?php echo strtoupper($kompetisi_seni->nama_kategori_usia . ' ' . $kompetisi_seni->jenis_kelamin . ' ' . $kompetisi_seni->nama_seni . ' POOL ' . $kompetisi_seni->nomor_pool) ?>
     9|	</title>
    10|	<meta name="theme-color" content="#890108">
    11|	<link rel="icon" type="image/png" href="<?= get_instance()->get_setting('event_logo', 'pendaftaran/gambar_dan_juknis') ?>">
    12|
    13|	<!-- CSS Resources -->
    14|	<link href="<?php echo base_url() ?>assets/print/css/bootstrap.min.css" rel="stylesheet">
    15|
    16|	<!-- Custom Style -->
    17|	<style>
    18|		body {
    19|			background-color: #f8f9fa;
    20|		}
    21|
    22|		/* ===== TABLE ===== */
    23|		.tabel-pool {
    24|			width: 100%;
    25|			border-collapse: collapse;
    26|			background: #fff;
    27|		}
    28|		.tabel-pool thead tr {
    29|			background-color: #212529;
    30|			color: #fff;
    31|		}
    32|		.tabel-pool thead th {
    33|			padding: 12px 16px;
    34|			font-size: 12px;
    35|			font-weight: 700;
    36|			letter-spacing: 0.5px;
    37|			text-transform: uppercase;
    38|			border: none;
    39|		}
    40|		.tabel-pool tbody tr {
    41|			border-bottom: 1px solid #e9ecef;
    42|			transition: background 0.1s;
    43|		}
    44|		.tabel-pool tbody tr:hover {
    45|			background-color: #f1f3f5;
    46|		}
    47|		.tabel-pool tbody td {
    48|			padding: 12px 16px;
    49|			font-size: 14px;
    50|			vertical-align: middle;
    51|			color: #212529;
    52|		}
    53|
    54|		/* Medal row highlights */
    55|		.row-gold   { border-left: 4px solid #FFD700 !important; }
    56|		.row-silver { border-left: 4px solid #C0C0C0 !important; }
    57|		.row-bronze { border-left: 4px solid #CD7F32 !important; }
    58|
    59|		/* Match badge */
    60|		.badge-match {
    61|			display: inline-block;
    62|			background: #e9ecef;
    63|			color: #495057;
    64|			font-size: 11px;
    65|			font-weight: 700;
    66|			padding: 3px 10px;
    67|			border-radius: 4px;
    68|			text-align: center;
    69|			min-width: 32px;
    70|		}
    71|
    72|		/* Medal badge */
    73|		.badge-medal {
    74|			display: inline-block;
    75|			font-size: 11px;
    76|			font-weight: 700;
    77|			padding: 4px 12px;
    78|			border-radius: 20px;
    79|			letter-spacing: 0.5px;
    80|			text-transform: uppercase;
    81|		}
    82|		.badge-gold   { background: #fff3cd; color: #856404; border: 1px solid #FFD700; }
    83|		.badge-silver { background: #f0f0f0; color: #555;    border: 1px solid #aaa; }
    84|		.badge-bronze { background: #fdf0e0; color: #8B4513; border: 1px solid #CD7F32; }
    85|		.badge-dq     { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    86|
    87|		/* Score */
    88|		.score-val { font-weight: 700; font-size: 15px; font-family: monospace; }
    89|		.time-val  { font-size: 13px; font-family: monospace; color: #555; }
    90|		.text-empty { color: #adb5bd; }
    91|
    92|		/* Athlete */
    93|		.athlete-name { font-weight: 600; font-size: 14px; }
    94|		.team-name    { font-size: 12px; color: #6c757d; }
    95|
    96|		/* Summary info */
    97|		.info-text { font-size: 12px; color: #6c757d; text-align: right; margin-top: 8px; }
    98|
    99|		/* Back button */
   100|		.btn-home {
   101|			position: fixed;
   102|			bottom: 20px;
   103|			right: 20px;
   104|			z-index: 9999;
   105|			border-radius: 30px;
   106|			padding: 10px 20px;
   107|			box-shadow: 0 4px 6px rgba(0,0,0,0.15);
   108|			font-weight: 600;
   109|		}
   110|
   111|		@media print {
   112|			.btn-home { display: none; }
   113|		}
   114|	</style>
   115|</head>
   116|
   117|<body>
   118|
   119|<!-- Back button -->
   120|<a href="<?= base_url() ?>" class="btn btn-danger btn-home">
   121|	&#8592; Kembali ke Beranda
   122|</a>
   123|
   124|<div class="container-fluid px-0">
   125|
   126|	<!-- ===== HEADER (sama persis seperti tanding) ===== -->
   127|	<div class="row mb-4 justify-content-center shadow mx-0">
   128|		<div class="col-1 bg-white d-flex justify-content-center align-items-center py-2">
   129|			<img src="<?= base_url('uploads/assets/' . ci3_config_item('\1', '\2')['file_name']) ?>" class="img-fluid" style="max-height: 70px; object-fit: contain;">
   130|		</div>
   131|		<div class="col-10 py-3 bg-dark text-center">
   132|			<p class="h5 mb-1 text-white">
   133|				<?= get_instance()->get_setting('event_name') ?> &mdash; ARTISTIC PERFORMANCE RESULTS
   134|			</p>
   135|			<p class="h2 m-0 my-1 fw-bolder text-white">
   136|				<?= strtoupper($kompetisi_seni->nama_kategori_usia . ' ' . $kompetisi_seni->jenis_kelamin . ' ' . $kompetisi_seni->jenis_seni . ' ' . $kompetisi_seni->nama_seni) ?>
   137|				&mdash; POOL <?= strtoupper($kompetisi_seni->nomor_pool) ?>
   138|			</p>
   139|		</div>
   140|		<div class="col-1 bg-white d-flex justify-content-center align-items-center py-2">
   141|			<img src="<?= base_url('uploads/assets/' . ci3_config_item('\1', '\2')['file_name']) ?>" class="img-fluid" style="max-height: 70px; object-fit: contain;">
   142|		</div>
   143|	</div>
   144|
   145|	<!-- ===== CONTENT ===== -->
   146|	<div class="container pb-5">
   147|		<div class="card shadow-sm border-0">
   148|			<div class="card-body p-0">
   149|				<?php if (!empty($data_penampilan_seni)): ?>
   150|					<div class="table-responsive">
   151|						<table class="tabel-pool">
   152|							<thead>
   153|								<tr>
   154|									<th style="width: 70px;">Match</th>
   155|									<th>Athlete / Team</th>
   156|									<th style="width: 160px;">Contingent</th>
   157|									<th style="width: 120px; text-align:right;">Score</th>
   158|									<th style="width: 100px; text-align:center;">Time</th>
   159|									<th style="width: 120px; text-align:center;">Medal</th>
   160|								</tr>
   161|							</thead>
   162|							<tbody>
   163|								<?php foreach ($data_penampilan_seni as $partai):
   164|									$medali = strtolower($partai->jenis_medali_pool ?? '');
   165|									$dq     = $partai->diskualifikasi ?? 0;
   166|									$nilai  = $partai->nilai_akhir ?? null;
   167|									$waktu  = $partai->waktu_tampil ?? 0;
   168|
   169|									$row_class = '';
   170|									if ($medali == 'emas')    $row_class = 'row-gold';
   171|									elseif ($medali == 'perak')    $row_class = 'row-silver';
   172|									elseif ($medali == 'perunggu') $row_class = 'row-bronze';
   173|								?>
   174|								<tr class="<?= $row_class ?>">
   175|									<td class="text-center">
   176|										<span class="badge-match"><?= $partai->nomor_partai ?></span>
   177|									</td>
   178|									<td>
   179|										<div class="athlete-name"><?= ucwords(strtolower($partai->anggota_kelompok_peserta_seni)) ?></div>
   180|									</td>
   181|									<td>
   182|										<div class="team-name"><?= strtoupper($partai->nama_kontingen) ?></div>
   183|									</td>
   184|									<td class="text-end">
   185|										<?php if ($nilai !== null && $nilai > 0): ?>
   186|											<span class="score-val"><?= number_format($nilai, 3) ?></span>
   187|										<?php else: ?>
   188|											<span class="text-empty">&mdash;</span>
   189|										<?php endif; ?>
   190|									</td>
   191|									<td class="text-center">
   192|										<?php if ($waktu > 0): ?>
   193|											<span class="time-val"><?= sprintf('%02d:%02d', floor($waktu / 60), $waktu % 60) ?></span>
   194|										<?php else: ?>
   195|											<span class="text-empty">&mdash;</span>
   196|										<?php endif; ?>
   197|									</td>
   198|									<td class="text-center">
   199|										<?php if ($dq == 1): ?>
   200|											<span class="badge-medal badge-dq">DQ</span>
   201|										<?php elseif ($medali == 'emas'): ?>
   202|											<span class="badge-medal badge-gold">Emas</span>
   203|										<?php elseif ($medali == 'perak'): ?>
   204|											<span class="badge-medal badge-silver">Perak</span>
   205|										<?php elseif ($medali == 'perunggu'): ?>
   206|											<span class="badge-medal badge-bronze">Perunggu</span>
   207|										<?php else: ?>
   208|											<span class="text-empty">&mdash;</span>
   209|										<?php endif; ?>
   210|									</td>
   211|								</tr>
   212|								<?php endforeach; ?>
   213|							</tbody>
   214|						</table>
   215|					</div>
   216|					<p class="info-text px-3 py-2">
   217|						Total: <?= count($data_penampilan_seni) ?> Participants
   218|					</p>
   219|				<?php else: ?>
   220|					<div class="text-center py-5 text-muted">
   221|						<p style="font-size: 40px; opacity: 0.3;">🏅</p>
   222|						<p class="fw-semibold">No Results Available Yet</p>
   223|						<p class="small">Competition results have not been entered.</p>
   224|					</div>
   225|				<?php endif; ?>
   226|			</div>
   227|		</div>
   228|	</div>
   229|</div>
   230|
   231|<footer class="text-center py-4 text-muted">
   232|	<small>&copy; <?= date('Y') ?> <?= get_instance()->get_setting('event_name') ?>. All Rights Reserved.</small>
   233|</footer>
   234|
   235|</body>
   236|
   237|</html>
   238|
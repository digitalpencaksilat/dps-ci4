<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>
		Pool Results - <?php echo strtoupper($kompetisi_seni->nama_kategori_usia . ' ' . $kompetisi_seni->jenis_kelamin . ' ' . $kompetisi_seni->nama_seni . ' POOL ' . $kompetisi_seni->nomor_pool) ?>
	</title>
	<meta name="theme-color" content="#890108">
	<link rel="icon" type="image/png" href="<?= get_instance()->get_setting('event_logo', 'pendaftaran/gambar_dan_juknis') ?>">

	<!-- CSS Resources -->
	<link href="<?= online_asset('bootstrap_5_css') ?>" rel="stylesheet">

	<!-- Custom Style -->
	<style>
		body {
			background-color: #f8f9fa;
		}

		/* ===== TABLE ===== */
		.tabel-pool {
			width: 100%;
			border-collapse: collapse;
			background: #fff;
		}
		.tabel-pool thead tr {
			background-color: #212529;
			color: #fff;
		}
		.tabel-pool thead th {
			padding: 12px 16px;
			font-size: 12px;
			font-weight: 700;
			letter-spacing: 0.5px;
			text-transform: uppercase;
			border: none;
		}
		.tabel-pool tbody tr {
			border-bottom: 1px solid #e9ecef;
			transition: background 0.1s;
		}
		.tabel-pool tbody tr:hover {
			background-color: #f1f3f5;
		}
		.tabel-pool tbody td {
			padding: 12px 16px;
			font-size: 14px;
			vertical-align: middle;
			color: #212529;
		}

		/* Medal row highlights */
		.row-gold   { border-left: 4px solid #FFD700 !important; }
		.row-silver { border-left: 4px solid #C0C0C0 !important; }
		.row-bronze { border-left: 4px solid #CD7F32 !important; }

		/* Match badge */
		.badge-match {
			display: inline-block;
			background: #e9ecef;
			color: #495057;
			font-size: 11px;
			font-weight: 700;
			padding: 3px 10px;
			border-radius: 4px;
			text-align: center;
			min-width: 32px;
		}

		/* Medal badge */
		.badge-medal {
			display: inline-block;
			font-size: 11px;
			font-weight: 700;
			padding: 4px 12px;
			border-radius: 20px;
			letter-spacing: 0.5px;
			text-transform: uppercase;
		}
		.badge-gold   { background: #fff3cd; color: #856404; border: 1px solid #FFD700; }
		.badge-silver { background: #f0f0f0; color: #555;    border: 1px solid #aaa; }
		.badge-bronze { background: #fdf0e0; color: #8B4513; border: 1px solid #CD7F32; }
		.badge-dq     { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

		/* Score */
		.score-val { font-weight: 700; font-size: 15px; font-family: monospace; }
		.time-val  { font-size: 13px; font-family: monospace; color: #555; }
		.text-empty { color: #adb5bd; }

		/* Athlete */
		.athlete-name { font-weight: 600; font-size: 14px; }
		.team-name    { font-size: 12px; color: #6c757d; }

		/* Summary info */
		.info-text { font-size: 12px; color: #6c757d; text-align: right; margin-top: 8px; }

		/* Back button */
		.btn-home {
			position: fixed;
			bottom: 20px;
			right: 20px;
			z-index: 9999;
			border-radius: 30px;
			padding: 10px 20px;
			box-shadow: 0 4px 6px rgba(0,0,0,0.15);
			font-weight: 600;
		}

		@media print {
			.btn-home { display: none; }
		}
	</style>
</head>

<body>

<!-- Back button -->
<a href="<?= base_url() ?>" class="btn btn-danger btn-home">
	&#8592; Kembali ke Beranda
</a>

<div class="container-fluid px-0">

	<!-- ===== HEADER (sama persis seperti tanding) ===== -->
	<div class="row mb-4 justify-content-center shadow mx-0">
		<div class="col-1 bg-white d-flex justify-content-center align-items-center py-2">
			<img src="<?= base_url('uploads/assets/' . ci3_config_item('\1', '\2')['file_name']) ?>" class="img-fluid" style="max-height: 70px; object-fit: contain;">
		</div>
		<div class="col-10 py-3 bg-dark text-center">
			<p class="h5 mb-1 text-white">
				<?= get_instance()->get_setting('event_name') ?> &mdash; ARTISTIC PERFORMANCE RESULTS
			</p>
			<p class="h2 m-0 my-1 fw-bolder text-white">
				<?= strtoupper($kompetisi_seni->nama_kategori_usia . ' ' . $kompetisi_seni->jenis_kelamin . ' ' . $kompetisi_seni->jenis_seni . ' ' . $kompetisi_seni->nama_seni) ?>
				&mdash; POOL <?= strtoupper($kompetisi_seni->nomor_pool) ?>
			</p>
		</div>
		<div class="col-1 bg-white d-flex justify-content-center align-items-center py-2">
			<img src="<?= base_url('uploads/assets/' . ci3_config_item('\1', '\2')['file_name']) ?>" class="img-fluid" style="max-height: 70px; object-fit: contain;">
		</div>
	</div>

	<!-- ===== CONTENT ===== -->
	<div class="container pb-5">
		<div class="card shadow-sm border-0">
			<div class="card-body p-0">
				<?php if (!empty($data_penampilan_seni)): ?>
					<div class="table-responsive">
						<table class="tabel-pool">
							<thead>
								<tr>
									<th style="width: 70px;">Match</th>
									<th>Athlete / Team</th>
									<th style="width: 160px;">Contingent</th>
									<th style="width: 120px; text-align:right;">Score</th>
									<th style="width: 100px; text-align:center;">Time</th>
									<th style="width: 120px; text-align:center;">Medal</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($data_penampilan_seni as $partai):
									$medali = strtolower($partai->jenis_medali_pool ?? '');
									$dq     = $partai->diskualifikasi ?? 0;
									$nilai  = $partai->nilai_akhir ?? null;
									$waktu  = $partai->waktu_tampil ?? 0;

									$row_class = '';
									if ($medali == 'emas')    $row_class = 'row-gold';
									elseif ($medali == 'perak')    $row_class = 'row-silver';
									elseif ($medali == 'perunggu') $row_class = 'row-bronze';
								?>
								<tr class="<?= $row_class ?>">
									<td class="text-center">
										<span class="badge-match"><?= $partai->nomor_partai ?></span>
									</td>
									<td>
										<div class="athlete-name"><?= ucwords(strtolower($partai->anggota_kelompok_peserta_seni)) ?></div>
									</td>
									<td>
										<div class="team-name"><?= strtoupper($partai->nama_kontingen) ?></div>
									</td>
									<td class="text-end">
										<?php if ($nilai !== null && $nilai > 0): ?>
											<span class="score-val"><?= number_format($nilai, 3) ?></span>
										<?php else: ?>
											<span class="text-empty">&mdash;</span>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php if ($waktu > 0): ?>
											<span class="time-val"><?= sprintf('%02d:%02d', floor($waktu / 60), $waktu % 60) ?></span>
										<?php else: ?>
											<span class="text-empty">&mdash;</span>
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php if ($dq == 1): ?>
											<span class="badge-medal badge-dq">DQ</span>
										<?php elseif ($medali == 'emas'): ?>
											<span class="badge-medal badge-gold">Emas</span>
										<?php elseif ($medali == 'perak'): ?>
											<span class="badge-medal badge-silver">Perak</span>
										<?php elseif ($medali == 'perunggu'): ?>
											<span class="badge-medal badge-bronze">Perunggu</span>
										<?php else: ?>
											<span class="text-empty">&mdash;</span>
										<?php endif; ?>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<p class="info-text px-3 py-2">
						Total: <?= count($data_penampilan_seni) ?> Participants
					</p>
				<?php else: ?>
					<div class="text-center py-5 text-muted">
						<p style="font-size: 40px; opacity: 0.3;">🏅</p>
						<p class="fw-semibold">No Results Available Yet</p>
						<p class="small">Competition results have not been entered.</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<footer class="text-center py-4 text-muted">
	<small>&copy; <?= date('Y') ?> <?= get_instance()->get_setting('event_name') ?>. All Rights Reserved.</small>
</footer>

</body>

</html>

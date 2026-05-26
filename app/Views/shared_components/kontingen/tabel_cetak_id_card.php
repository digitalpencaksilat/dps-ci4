<?php if ($this->agent->is_mobile()) : ?>
	<table width="100%" class="table table-hover" id="tabel_cek_data">
		<thead>
			<tr>
				<th>Nama Kontingen</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data_kontingen as $data) : ?>
				<?php $jumlah = isset($jumlah_id_card[$data->id_kontingen]) ? $jumlah_id_card[$data->id_kontingen] : 0; ?>
				<tr>
					<td>
						<p class="mb-2 fw-bold"><?= $data->nama_kontingen ?></p>
						<p class="mb-2 text-muted small">
							<i class="fas fa-id-card me-1"></i> <?= $jumlah ?> ID Card
							<?php if ($jumlah == 0): ?>
								<span class="badge bg-warning text-dark ms-1">Tidak ada peserta</span>
							<?php endif; ?>
						</p>
						<div class="mb-3">
							<a target="_blank" href="<?= base_url('kontingen/id-card/') . $data->id_kontingen ?>" class="btn btn-primary btn-sm m-0">
								Cetak ID Card
							</a>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php else : ?>
	<table width="100%" class="table table-hover" id="tabel_cek_data">
		<thead>
			<tr>
				<th class="text-center">No</th>
				<th>Nama Kontingen</th>
				<th class="text-center">Jumlah ID Card</th>
				<th class="no-export">Aksi</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1; ?>
			<?php foreach ($data_kontingen as $data) : ?>
				<?php $jumlah = isset($jumlah_id_card[$data->id_kontingen]) ? $jumlah_id_card[$data->id_kontingen] : 0; ?>
				<tr>
					<td class="text-center align-middle"><?= $i++ ?></td>
					<td class="text-wrap align-middle text-capitalize"><?= $data->nama_kontingen ?></td>
					<td class="text-center align-middle">
						<?php if ($jumlah > 0): ?>
							<span class="badge bg-primary"><?= $jumlah ?> ID Card</span>
						<?php else: ?>
							<span class="badge bg-warning text-dark">0 ID Card</span>
						<?php endif; ?>
					</td>
					<td class="no-export">
						<a target="_blank" href="<?= base_url('kontingen/id-card/') . $data->id_kontingen ?>" class="btn btn-primary m-0">
							Cetak ID Card
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>

<?php
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$exportTitle = 'DATA ID CARD PER KONTINGEN';
$exportFilename = $exportTitle . ' - ' . $eventName;
$printHeaderHtml = view('shared_components/print/export_header', [
    'title' => $exportTitle,
    'subtitle' => $eventName,
]);
?>
<script>
	$(document).ready(function() {
		window.initAdminExportTable('#tabel_cek_data', {
			title: <?= json_encode($exportFilename) ?>,
			filename: <?= json_encode($exportFilename) ?>,
			orientation: 'landscape',
			preset: 'wide-report',
			printHeader: {
				title: <?= json_encode($exportTitle) ?>,
				subtitle: <?= json_encode($eventName) ?>
			},
			printHeaderHtml: <?= json_encode($printHeaderHtml) ?>,
			excel: {
				columnWidths: { A: 8, B: 45, C: 20 },
				customize: function(xlsx) {
					var sheet = xlsx.xl.worksheets['sheet1.xml'];
					$('row c', sheet).each(function() {
						$(this).find('v, t').each(function() {
							var text = $(this).text();
							if (isNaN(text)) {
								$(this).text(text.toUpperCase());
							}
						});
					});
				}
			},
			printCustomize: function(win) {
				var $body = $(win.document.body);
				$(win.document.head).append('<style>table{text-transform:uppercase!important;} table tr td:nth-child(1), table tr td:nth-child(3){text-align:center!important;} .print-badge{border:1px solid #000!important;padding:2px 6px!important;border-radius:4px!important;font-weight:bold!important;display:inline-block!important;min-width:80px!important;text-align:center!important;} .print-badge-primary{background-color:#cfe2ff!important;border-color:#0d6efd!important;color:#0d6efd!important;} .print-badge-warning{background-color:#fff2cc!important;border-color:#ffc107!important;color:#856404!important;}</style>');

				$body.find('table tbody tr').each(function() {
					var $badgeCell = $(this).find('td:nth-child(3)');
					var txt = $badgeCell.text().toUpperCase().trim();

					if (txt.includes('ID CARD')) {
						var match = txt.match(/(\d+)/);
						$badgeCell.html('<span class="print-badge ' + (match && parseInt(match[1]) > 0 ? 'print-badge-primary' : 'print-badge-warning') + '">' + txt + '</span>');
					}
				});
			},
			dataTable: {
				columnDefs: [{ orderable: false, width: '10%', target: -1 }]
			}
		});
	});
</script>

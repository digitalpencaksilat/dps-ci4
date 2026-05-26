<?php if ($this->agent->is_mobile()) : ?>
	<table width="100%" class="table text-sm" id="tabelKompetisiTanding">
		<thead>
			<tr>
				<th><?= lang('no') ?></th>
				<th><?= lang('kategori_tanding') ?></th>
				<th><?= lang('nomor_pool') ?></th>
				<th class="not-mobile"><?= lang('jumlah_peserta') ?></th>
				<th class="none"><?= lang('max_peserta') ?></th>
				<th class="no-export"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data_kompetisi_tanding as $kompetisi_tanding) : ?>
				<tr>
					<td></td>
					<td class="align-middle">
						<a class="mb-0 text-wrap text-capitalize">
							<?= $kompetisi_tanding->nama_kategori_usia . '<br>' . $kompetisi_tanding->jenis_kelamin.' - '.$kompetisi_tanding->label ?>
						</a>
					</td>
					<td class="align-middle text-end"><?= $kompetisi_tanding->nomor_pool ?></td>
					<td class="align-middle text-end"><?= $kompetisi_tanding->jumlah_peserta_tanding ?></td>
					<td class="align-middle text-capitalize"><?= $kompetisi_tanding->max_peserta ?></td>
					<td class="align-middle text-end no-export">
						<?= view('shared_components/kompetisi_tanding/tombol_tabel', ['data' => $kompetisi_tanding]) ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php else : ?>
	<table width="100%" class="table" id="tabelKompetisiTanding">
		<thead>
			<tr>
				<th class="no-export"></th>
				<th><?= lang('kategori_usia') ?></th>
				<th class="not-mobile"><?= lang('jenis_kelamin') ?></th>
				<th class="not-mobile"><?= lang('kelas') ?></th>
				<th class="not-mobile"><?= lang('nomor_pool') ?></th>
				<th class="not-mobile text-wrap"><?= lang('jumlah_peserta') ?></th>
				<th class="not-mobile text-wrap"><?= lang('jumlah_peserta_lunas') ?></th>
				<th class="not-mobile text-wrap"><?= lang('max_peserta') ?></th>
				<th class="no-export"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data_kompetisi_tanding as $kompetisi_tanding) : ?>
				<tr>
					<td></td>
					<td class="align-middle">
						<a class="mb-0 text-wrap text-capitalize"><?= $kompetisi_tanding->nama_kategori_usia ?></a>
					</td>
					<td class="align-middle text-capitalize"><?= $kompetisi_tanding->jenis_kelamin ?></td>
					<td class="align-middle text-center"><?= $kompetisi_tanding->label ?></td>
					<td class="align-middle text-end"><?= $kompetisi_tanding->nomor_pool ?></td>
					<td class="align-middle text-end"><?= $kompetisi_tanding->jumlah_peserta_tanding ?></td>
					<td class="align-middle text-end"><?= $kompetisi_tanding->jumlah_peserta_tanding_lunas ?></td>
					<td class="align-middle text-capitalize text-end"><?= $kompetisi_tanding->max_peserta ?></td>
					<td class="align-middle text-end no-export">
						<?= view('shared_components/kompetisi_tanding/tombol_tabel', ['data' => $kompetisi_tanding]) ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>


<?php
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$exportTitle = 'DAFTAR POOL TANDING';
$exportFilename = 'Daftar Pool Tanding - ' . $eventName;
$printHeaderHtml = view('shared_components/print/export_header', [
	'title' => $exportTitle,
	'subtitle' => $eventName,
]);
?>
<script>
	$(document).ready(function() {
		window.initAdminExportTable('#tabelKompetisiTanding', {
			title: <?= json_encode($exportTitle) ?>,
			filename: <?= json_encode($exportFilename) ?>,
			orientation: 'landscape',
			preset: 'wide-report',
			printHeader: {
				title: <?= json_encode($exportTitle) ?>,
				subtitle: <?= json_encode($eventName) ?>
			},
			printHeaderHtml: <?= json_encode($printHeaderHtml) ?>,
			excel: {
				columnWidths: { A: 18, B: 18, C: 12, D: 12, E: 16, F: 18, G: 14 }
			},
			printCustomize: function(win) {
				$(win.document.head).append('<style>table tr td:nth-child(4), table tr td:nth-child(5), table tr td:nth-child(6), table tr td:nth-child(7){text-align:right!important;} table tr td:nth-child(3){text-align:center!important;}</style>');
			},
			dataTable: {
				columnDefs: [
					{ width: '10%', targets: -1 },
					{ className: 'dtr-control text-center py-3', orderable: false, targets: 0, width: '10%' },
					{ width: '20%', targets: 1 },
					{ orderable: false, width: '10%', targets: -1 },
					{ width: '10%', targets: -2 },
					{ width: '10%', targets: -3 },
					{ width: '10%', targets: -4 }
				],
				paging: true,
				searching: true,
				ordering: true,
				info: true,
				responsive: true
			}
		});
	});
</script>

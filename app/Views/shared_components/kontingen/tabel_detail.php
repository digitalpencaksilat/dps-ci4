<?php if ($this->agent->is_mobile()) : ?>
	<table width="100%" class="table text-sm" id="tabelKontingen">
		<thead>
			<tr>
				<th><?= lang('no') ?></th>
				<th><?= lang('nama') ?></th>
				<th class="not-mobile"><?= lang('email') ?></th>
				<th class="not-mobile"><?= lang('nama_penanggungjawab') ?></th>
				<th class="not-mobile"><?= lang('nomor_telepon_penanggungjawab') ?></th>
				<th class="not-mobile"><?= lang('tanggal_daftar') ?></th>
				<th></th>
			</tr>

		</thead>
		<tbody>
			<?php $i = 1; ?>
			<?php foreach ($data_kontingen as $kontingen) : ?>
				<tr>
					<td></td>
					<td class="align-middle">
						<a class="mb-0 text-wrap text-capitalize"><?= $kontingen->nama_kontingen ?></a>
					</td>
					<td><?= $kontingen->email_kontingen ?></td>
					<td><?= $kontingen->nama_penanggungjawab ?></td>
					<td class="align-middle text-end">
						<a href="<?= wa_me(convert_to_indonesian_phone_number($kontingen->nomor_telepon_penanggungjawab)) ?>" target="_blank">
							<?= $kontingen->nomor_telepon_penanggungjawab ?>
						</a>
					</td>
					<td class="align-middle text-end"><?= format_indo_bulan($kontingen->tanggal_daftar) ?></td>
					<td class="align-middle text-end">
						<?= view('shared_components/kontingen/tombol_tabel', ['kontingen' => $kontingen]) ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<script>
		$(document).ready(function() {

			if ($('#tabelKontingen').length != 0) {
				$('#tabelKontingen').DataTable({
					"language": {
						"paginate": {
							"next": ">",
							"previous": "<"
						}
					},
					'autoWidth': false,
					"columnDefs": [{
							className: 'dtr-control text-center py-3',
							orderable: false,
							targets: 0
						},
						{
							orderable: false,
							width: '10%',
							target: -1
						}
					],
					'paging': true,
					'searching': true,
					'ordering': true,
					'info': true,
					'responsive': true,
				})
			}

		});
	</script>
<?php else : ?>
	<table width="100%" class="table" id="tabelKontingen">
		<thead>
			<tr>
				<th><?= lang('nama') ?></th>
				<th><?= lang('email') ?></th>
				<th><?= lang('nama_penanggungjawab') ?></th>
				<th><?= lang('nomor_telepon_penanggungjawab') ?></th>
				<th><?= lang('tanggal_daftar') ?></th>
				<th><?= lang('provinsi') ?></th>
				<th><?= lang('kabupaten_kota') ?></th>
				<th><?= lang('kecamatan') ?></th>
				<th><?= lang('kelurahan') ?></th>
				<th><?= lang('alamat_lengkap') ?></th>
				<th class="no-export"></th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1; ?>
			<?php foreach ($data_kontingen as $kontingen) : ?>
				<tr>
					<td class="align-middle">
						<a class="mb-0 text-wrap text-capitalize"><?= $kontingen->nama_kontingen ?></a>
					</td>
					<td class="text-wrap"><?= $kontingen->email_kontingen ?></td>
					<td><?= $kontingen->nama_penanggungjawab ?></td>
					<td class="align-middle text-end">
						<a href="<?= wa_me(convert_to_indonesian_phone_number($kontingen->nomor_telepon_penanggungjawab)) ?>" target="_blank" class="text-wrap">
							<?= $kontingen->nomor_telepon_penanggungjawab ?>
						</a>
					</td>
					<td class="align-middle text-end"><?= format_indo_bulan($kontingen->tanggal_daftar) ?></td>
					<td class="align-middle"><?= $kontingen->provinsi ?></td>
					<td class="align-middle"><?= $kontingen->kabupaten_kota ?></td>
					<td class="align-middle"><?= $kontingen->kecamatan ?></td>
					<td class="align-middle"><?= $kontingen->kelurahan ?></td>
					<td class="align-middle text-wrap"><?= $kontingen->alamat_lengkap ?></td>
					<td class="align-middle text-end no-export">
						<?= view('shared_components/kontingen/tombol_tabel', ['kontingen' => $kontingen]) ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$exportTitle = 'DATA KONTINGEN';
$exportFilename = $exportTitle . ' - ' . $eventName;
$printHeaderHtml = view('shared_components/print/export_header', [
	'title' => $exportTitle,
	'subtitle' => $eventName,
]);
?>
<script>
	$(document).ready(function() {
		window.initAdminExportTable('#tabelKontingen', {
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
				columnWidths: { A: 45, B: 30, C: 25, D: 25, E: 25, F: 25, G: 25, H: 25, I: 25, J: 30 },
				customize: function(xlsx) {
					var sheet = xlsx.xl.worksheets['sheet1.xml'];
					var styles = xlsx.xl['styles.xml'];

					var addStyle = function(xml, styleStr) {
						var el = xml.getElementsByTagName('cellXfs')[0];
						var newStyle = new DOMParser().parseFromString(styleStr, 'text/xml').childNodes[0];
						el.appendChild(newStyle);
						return el.childNodes.length - 1;
					};

					var fonts = styles.getElementsByTagName('fonts')[0];
					$(fonts).append('<font><sz val="14"/><name val="Calibri"/><b/><color rgb="000000"/></font>');
					var fontHdrIdx = fonts.childNodes.length - 1;
					$(fonts).append('<font><sz val="12"/><name val="Calibri"/><color rgb="000000"/></font>');
					var fontBdyIdx = fonts.childNodes.length - 1;

					var fills = styles.getElementsByTagName('fills')[0];
					$(fills).append('<fill><patternFill patternType="solid"><fgColor rgb="D3D3D3"/><bgColor indexed="64"/></patternFill></fill>');
					var fillGreyIdx = fills.childNodes.length - 1;

					var styleTitleIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontHdrIdx + '" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');
					var styleHeaderIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontHdrIdx + '" fillId="' + fillGreyIdx + '" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');
					var styleBodyIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontBdyIdx + '" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>');
					var styleBodyCenterIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontBdyIdx + '" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');

					$('row:eq(0) c', sheet).attr('s', styleTitleIdx);
					$('row:eq(1) c', sheet).attr('s', styleHeaderIdx);
					$('row:gt(1) c', sheet).attr('s', styleBodyIdx);

					// Center-align columns C through I (skip A=No, B=Nama, J=Alamat)
					$('row:gt(1) c[r^="C"]', sheet).attr('s', styleBodyCenterIdx);
					$('row:gt(1) c[r^="D"]', sheet).attr('s', styleBodyCenterIdx);
					$('row:gt(1) c[r^="E"]', sheet).attr('s', styleBodyCenterIdx);
					$('row:gt(1) c[r^="F"]', sheet).attr('s', styleBodyCenterIdx);
					$('row:gt(1) c[r^="G"]', sheet).attr('s', styleBodyCenterIdx);
					$('row:gt(1) c[r^="H"]', sheet).attr('s', styleBodyCenterIdx);
					$('row:gt(1) c[r^="I"]', sheet).attr('s', styleBodyCenterIdx);

					// Uppercase all text
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
				var $table = $body.find('table');

				// Inject row numbers
				if ($table.find('thead th:first').text().toUpperCase() !== 'NO.') {
					$table.find('thead tr').prepend('<th style="width:25px!important;">NO.</th>');
					$table.find('tbody tr').each(function(index) {
						$(this).prepend('<td class="text-center">' + (index + 1) + '</td>');
					});
				}

				var style = '<style>' +
					'@page{size:landscape;margin:0.5cm;}' +
					'body{font-family:Helvetica,Arial,sans-serif;font-size:14px;}' +
					'table{text-transform:uppercase!important;border-collapse:collapse!important;width:100%!important;margin-top:15px;}' +
					'table thead th{background-color:#f2f2f2!important;border:0.3pt solid #444!important;font-weight:bold;padding:6px 3px!important;text-align:center!important;-webkit-print-color-adjust:exact;}' +
					'table tbody td{border:0.3pt solid #777!important;padding:4px 3px!important;vertical-align:middle!important;white-space:normal!important;word-wrap:break-word!important;word-break:break-word!important;}' +
					'table tbody tr:nth-child(even){background-color:#f9f9f9!important;-webkit-print-color-adjust:exact;}' +
					'table tbody tr:nth-child(odd){background-color:#ffffff!important;}' +
					'table th:nth-child(4),table td:nth-child(4),table th:nth-child(5),table td:nth-child(5),table th:nth-child(6),table td:nth-child(6){width:1%!important;white-space:nowrap!important;text-align:center!important;}' +
					'table th:nth-child(3),table td:nth-child(3){width:1%!important;white-space:nowrap!important;}' +
					'table td:nth-child(2),table th:nth-child(2){max-width:200px!important;min-width:150px!important;}' +
					'.text-center{text-align:center!important;}.text-end{text-align:right!important;}' +
					'</style>';
				$(win.document.head).append(style);

				$table.find('th, td').css({'font-size':'14px','text-transform':'uppercase'});
			},
			dataTable: {
				columnDefs: [
					{ orderable: false, width: '3%', target: -1 },
					{ visible: false, target: -2 },
					{ visible: false, target: -3 },
					{ visible: false, target: -4 },
					{ visible: false, target: -5 },
					{ visible: false, target: -6 }
				],
				paging: true,
				searching: true,
				ordering: true,
				info: true,
				responsive: false,
				scrollX: true
			}
		});
	});
</script>
<?php endif; ?>
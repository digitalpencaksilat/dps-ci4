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

<!-- DataTables Buttons Extension (hanya untuk halaman ini) -->
<link href="<?= base_url('assets/') ?>DataTables/css/buttons.dataTables.min.css" rel="stylesheet" />
<script src="<?= base_url('assets/') ?>DataTables/js/dataTables.buttons.min.js"></script>
<script src="<?= base_url('assets/') ?>DataTables/js/buttons.colVis.min.js"></script>
<script src="<?= base_url('assets/') ?>DataTables/js/buttons.html5.min.js"></script>
<script src="<?= base_url('assets/') ?>DataTables/js/buttons.print.min.js"></script>
<script src="<?= base_url('assets/') ?>DataTables/js/jszip.min.js"></script>
<script src="<?= base_url('assets/') ?>DataTables/js/pdfmake.min.js"></script>
<script src="<?= base_url('assets/') ?>DataTables/js/vfs_fonts.js"></script>

<script>
	$(document).ready(function() {

		if ($('#tabel_cek_data').length != 0) {
			$('#tabel_cek_data').DataTable({
				dom: 'Bfrtip',
				buttons: [
					{
						extend: 'colvis',
						className: 'btn btn-outline-secondary btn-sm',
						text: '<i class="fas fa-columns me-1"></i> Pilih Kolom'
					},
					{
						title: 'DATA ID CARD PER KONTINGEN - <?= get_instance()->get_setting('event_name') ?>',
						extend: 'print',
						className: 'btn btn-info btn-sm',
						text: '<i class="fas fa-print me-1"></i> Cetak',
						orientation: 'landscape',
						exportOptions: {
							columns: ':visible:not(.no-export)'
						},
						customize: function(win) {
							// 1. Header Custom
							$(win.document.body).prepend(
								`<?php
									$this->load->view(
										'print/components/header',
										['header_title' => 'DATA ID CARD PER KONTINGEN', 'header_subtitle' => get_instance()->get_setting('event_name')],
										FALSE
									);
								?>`
							);

							// 2. CSS yang Diperkuat dengan Uppercase
							var style = `
								<style>
									@page { size: landscape; margin: 0.5cm; }
									body { font-family: 'Helvetica', Arial, sans-serif; font-size: 8.5px; }
									
									table { 
										border-collapse: collapse !important; 
										width: 100% !important; 
										table-layout: auto !important; 
										margin-top: 15px;
										text-transform: uppercase !important; 
									}

									table thead th { 
										background-color: #f2f2f2 !important;
										border: 0.3pt solid #444 !important;
										font-weight: bold;
										padding: 6px 3px !important;
										text-align: center !important;
										-webkit-print-color-adjust: exact;
									}

									table tbody td { 
										border: 0.3pt solid #777 !important; 
										padding: 4px 3px !important;
										vertical-align: middle !important;
										white-space: normal !important;      
										word-wrap: break-word !important;    
										word-break: break-all !important;    
										overflow-wrap: break-word !important;
									}
									
									/* Zebra Striping */
									table tbody tr:nth-child(even) {
										background-color: #f9f9f9 !important; 
										-webkit-print-color-adjust: exact;
									}
									table tbody tr:nth-child(odd) {
										background-color: #ffffff !important;
										-webkit-print-color-adjust: exact;
									}
									
									/* Center alignment untuk kolom No dan Jumlah */
									table tr td:nth-child(1),
									table tr td:nth-child(3) { 
										text-align: center !important; 
									}
									
									.text-center { text-align: center !important; }
									
									/* Styling badge saat print */
									.print-badge {
										border: 1px solid #000 !important;
										padding: 2px 6px !important;
										border-radius: 4px !important;
										font-weight: bold !important;
										display: inline-block !important;
										min-width: 80px !important;
										text-align: center !important;
									}
									.print-badge-primary { 
										background-color: #cfe2ff !important; 
										border-color: #0d6efd !important; 
										color: #0d6efd !important; 
									}
									.print-badge-warning { 
										background-color: #fff2cc !important; 
										border-color: #ffc107 !important; 
										color: #856404 !important; 
									}
								</style>
							`;
							$(win.document.head).append(style);

							// 3. Bersihkan h1
							$(win.document.body).find('h1').remove();
							var $table = $(win.document.body).find('table');

							// 4. Final Touch: Paksa font-size dan pastikan uppercase
							$table.find('th, td').css({
								'font-size': '10px',
								'text-transform': 'uppercase'
							});

							// 5. Convert badge to print-friendly badge
							$(win.document.body).find('table tbody tr').each(function() {
								var $badgeCell = $(this).find('td:nth-child(3)');
								var txt = $badgeCell.text().toUpperCase().trim();
								
								if (txt.includes('ID CARD')) {
									// Check if contains number > 0
									var match = txt.match(/(\d+)/);
									if (match && parseInt(match[1]) > 0) {
										$badgeCell.html('<span class="print-badge print-badge-primary">' + txt + '</span>');
									} else {
										$badgeCell.html('<span class="print-badge print-badge-warning">' + txt + '</span>');
									}
								}
							});
						}
					},
					{
						title: 'DATA ID CARD PER KONTINGEN - <?= get_instance()->get_setting('event_name') ?>',
						extend: 'excelHtml5',
						className: 'btn btn-success btn-sm',
						text: '<i class="fas fa-file-excel me-1"></i> Excel',
						exportOptions: {
							columns: ':visible:not(.no-export)'
						},
						customize: function(xlsx) {
							var sheet = xlsx.xl.worksheets['sheet1.xml'];
							var styles = xlsx.xl['styles.xml'];

							// 1. Fungsi Helper Style
							var addStyle = function(xml, styleStr) {
								var el = xml.getElementsByTagName('cellXfs')[0];
								var newStyle = new DOMParser().parseFromString(styleStr, 'text/xml').childNodes[0];
								el.appendChild(newStyle);
								return el.childNodes.length - 1;
							};

							// 2. Setup Font & Fill
							var fonts = styles.getElementsByTagName('fonts')[0];
							$(fonts).append('<font><sz val="14"/><name val="Calibri"/><b/><color rgb="000000"/></font>');
							var fontHdrIdx = fonts.childNodes.length - 1;

							$(fonts).append('<font><sz val="12"/><name val="Calibri"/><color rgb="000000"/></font>');
							var fontBdyIdx = fonts.childNodes.length - 1;

							var fills = styles.getElementsByTagName('fills')[0];
							$(fills).append('<fill><patternFill patternType="solid"><fgColor rgb="D3D3D3"/><bgColor indexed="64"/></patternFill></fill>');
							var fillGreyIdx = fills.childNodes.length - 1;

							// 3. Registrasi Style
							var styleTitleIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontHdrIdx + '" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');
							var styleHeaderIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontHdrIdx + '" fillId="' + fillGreyIdx + '" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');
							var styleBodyIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontBdyIdx + '" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>');
							var styleBodyCenterIdx = addStyle(styles, '<xf numFmtId="0" fontId="' + fontBdyIdx + '" fillId="0" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>');

							// 4. Terapkan Style ke Baris & Kolom Spesifik
							$('row:eq(0) c', sheet).attr('s', styleTitleIdx); // Judul Atas
							$('row:eq(1) c', sheet).attr('s', styleHeaderIdx); // Header Tabel
							$('row:gt(1) c', sheet).attr('s', styleBodyIdx); // Default Body (Kiri)

							// PERATAAN TENGAH UNTUK KONTEN:
							// Kolom A (No) dan C (Jumlah ID Card)
							$('row:gt(1) c[r^="A"]', sheet).attr('s', styleBodyCenterIdx);
							$('row:gt(1) c[r^="C"]', sheet).attr('s', styleBodyCenterIdx);

							// 5. ATUR LEBAR KOLOM (Custom Width)
							var colElement = sheet.getElementsByTagName('cols')[0];
							if (!colElement) {
								colElement = sheet.createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'cols');
								sheet.insertBefore(colElement, sheet.getElementsByTagName('sheetData')[0]);
							}
							$(colElement).empty();

							// Penyesuaian Lebar:
							$(colElement).append('<col min="1" max="1" width="8" customWidth="1"/>'); // No (A)
							$(colElement).append('<col min="2" max="2" width="45" customWidth="1"/>'); // Nama Kontingen (B)
							$(colElement).append('<col min="3" max="3" width="20" customWidth="1"/>'); // Jumlah ID Card (C)

							// 6. PAKSA UPPERCASE
							$('row c', sheet).each(function() {
								var cell = $(this);
								cell.find('v, t').each(function() {
									var text = $(this).text();
									if (isNaN(text)) {
										$(this).text(text.toUpperCase());
									}
								});
							});
						}
					},
					{
						title: 'DATA ID CARD PER KONTINGEN - <?= get_instance()->get_setting('event_name') ?>',
						extend: 'pdfHtml5',
						className: 'btn btn-danger btn-sm',
						text: '<i class="fas fa-file-pdf me-1"></i> PDF',
						orientation: 'landscape',
						pageSize: 'A4',
						exportOptions: {
							columns: ':visible:not(.no-export)'
						}
					},
					{
						title: 'DATA ID CARD PER KONTINGEN - <?= get_instance()->get_setting('event_name') ?>',
						extend: 'csvHtml5',
						className: 'btn btn-secondary btn-sm',
						text: '<i class="fas fa-file-csv me-1"></i> CSV',
						exportOptions: {
							columns: ':visible:not(.no-export)'
						}
					}
				],
				"language": {
					"paginate": {
						"next": ">",
						"previous": "<"
					}
				},
				'autoWidth': false,
				"columnDefs": [{
					orderable: false,
					width: '10%',
					target: -1
				}],
				'paging': true,
				'searching': true,
				'ordering': true,
				'info': true
			})
		}
	});
</script>
<?php if ($this->agent->is_mobile()) : ?>
	<table class="table" id="tabelBaganPeserta">
		<thead>
			<tr>
				<th>Nama</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data_peserta_tanding as $peserta_tanding) : ?>
				<tr>
					<td>
						<span class="d-block fw-bolder"><?= $peserta_tanding->nama_pendaftar ?></span>
						<small><?= $peserta_tanding->nama_kontingen ?></small>
					</td>
					<td>
						<?php $this->load->view('shared_components/kompetisi_tanding/tombol_tabel_daftar_bagan', ['kompetisi_tanding' => $peserta_tanding])?>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>

<?php else : ?>
	<table class="table" id="tabelBaganPeserta">
		<thead>
			<tr>
				<th>Nama</th>
				<th>Kontingen</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data_peserta_tanding as $peserta_tanding) : ?>
				<tr>
					<td class="align-middle">
						<?= $peserta_tanding->nama_pendaftar ?>
					</td>
					<td class="align-middle">
						<?= $peserta_tanding->nama_kontingen ?>
					</td>
					<td class="align-middle">
						<?php $this->load->view('shared_components/kompetisi_tanding/tombol_tabel_daftar_bagan', ['kompetisi_tanding' => $peserta_tanding])?>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
<?php endif; ?>

<script>
	$(document).ready(function() {

		if ($('#tabelBaganPeserta').length != 0) {
			$('#tabelBaganPeserta').DataTable({
				'autoWidth': false,
				"language": {
					"paginate": {
						"next": ">",
						"previous": "<"
					}
				},
				"columnDefs": [
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

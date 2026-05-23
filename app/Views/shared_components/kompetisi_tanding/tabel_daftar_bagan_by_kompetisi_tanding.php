<?php if ($this->agent->is_mobile()) : ?>
	<table class="table" id="tabelBaganKelas">
		<thead>
			<tr>
				<th>Kelas</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data_kompetisi_tanding as $kompetisi_tanding) : ?>
				<tr>
					<td class="text-wrap">
						<?php echo $kompetisi_tanding->nama_kategori_usia . ' ' . $kompetisi_tanding->jenis_kelamin . ' <br> Kelas ' . $kompetisi_tanding->label . ' <br >Pool ' . $kompetisi_tanding->nomor_pool ?>	
					</td>
					<td>
						<?php $this->load->view('shared_components/kompetisi_tanding/tombol_tabel_daftar_bagan', ['kompetisi_tanding' => $kompetisi_tanding])?>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>

<?php else : ?>
	<table class="table" id="tabelBaganKelas">
		<thead>
			<tr>
				<th>Kategori Usia</th>
				<th>Kelas</th>
				<th>Nomor Pool</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data_kompetisi_tanding as $kompetisi_tanding) : ?>
				<tr>
					<td class="align-middle">
						<?= $kompetisi_tanding->nama_kategori_usia . ' ' . ucwords($kompetisi_tanding->jenis_kelamin) ?>	
					</td>
					<td><?= $kompetisi_tanding->label?></td>
					<td class="text-end align-middle"><?= $kompetisi_tanding->nomor_pool?></td>
					<td class="align-middle">
						<?php $this->load->view('shared_components/kompetisi_tanding/tombol_tabel_daftar_bagan', ['kompetisi_tanding' => $kompetisi_tanding])?>
					</td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
<?php endif; ?>

<script>
	$(document).ready(function() {

		if ($('#tabelBaganKelas').length != 0) {
			$('#tabelBaganKelas').DataTable({
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

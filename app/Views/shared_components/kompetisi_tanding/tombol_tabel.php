<div class="dropstart">
	<button type="button" id="dropdown<?= $data->id_kompetisi_tanding ?>" class="btn btn-default m-0 font-weight-normal shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
		<i class="fas fa-ellipsis-v"></i>
	</button>
	<ul class="dropdown-menu shadow-lg">
		<li class="dropdown-item">
			<a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('kompetisi-tanding/' . $data->id_kompetisi_tanding) ?>">Lihat Pool </a>
		</li>
		<li class="dropdown-item">
			<a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('kompetisi-tanding/bagan/' . $data->id_kompetisi_tanding) ?>/print">Cetak Bagan </a>
		</li>
		<?php if ($this->session->userdata('level') == 'sekretariat' || $this->session->userdata('level') == 'super_admin') : ?>
			<li class="dropdown-item">
				<form action="<?= base_url('kompetisi_tanding/delete/' . $data->id_kompetisi_tanding) ?>" method="post">
					<button type="button" class="btn btn-default shadow-none m-0 w-100 text-start" onclick="confirm_submit('<?= lang('apakah_anda_yakin')?>', this, 'Kelas <?= $data->label . ' ' . $data->nama_kategori_usia . ' - ' . $data->jenis_kelamin ?>  akan dihapus !')">Hapus</button>
				</form>
			</li>
		<?php endif ?>
	</ul>
</div>

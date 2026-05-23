<div class="dropstart">
	<button type="button" id="dropdown<?= $kompetisi_tanding->id_kompetisi_tanding ?>" class="btn btn-default m-0 font-weight-normal shadow-none" data-bs-toggle="dropdown" aria-expanded="false">
		<i class="fas fa-ellipsis-v"></i>
	</button>
	<ul class="dropdown-menu shadow-lg">
		<li class="dropdown-item">
			<a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('kompetisi-tanding/bagan/' . $kompetisi_tanding->id_kompetisi_tanding) ?>">Lihat Bagan</a>
		</li>
		<li class="dropdown-item">
			<a class="btn btn-default shadow-none m-0 w-100 text-start" href="<?= base_url('kompetisi-tanding/bagan/' . $kompetisi_tanding->id_kompetisi_tanding . '/print') ?>">Download</a>
		</li>
	</ul>
</div>

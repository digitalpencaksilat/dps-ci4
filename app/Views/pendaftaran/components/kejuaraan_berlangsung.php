
<div class="row h-100 align-items-center py-9">
	<div class="col-md-12 text-center">
		<div class="row">
			<div class="col-12">
				<h1 class="small text-light"><?= get_instance()->get_setting('event_name') ?></h1>
				<p class="display-5 my-3 fw-bolder text-light">Kejuaraan Sedang Berlangsung !</p>
			</div>
		</div>
		<?php if (get_setting_live_server('menggunakan_live_server')) : ?>
			<div class="row">
				<div class="col-12 mt-2 text-center">
					<a class="btn btn-primary mr-1" href="<?= base_url('pendaftaran/live-jadwal') ?>">Lihat Progress Partai</a>
					<a href="<?= base_url("pendaftaran/live-medali") ?>" class="btn btn-info mr-1">Lihat Perolehan Medali</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php if (get_setting_live_server('streaming')) : ?>
		<div class="col-md-12 py-3 text-center">
			<iframe width="400" height="300" src="<?= get_setting_live_server('youtube_embed_link')?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
	<?php endif; ?>
</div>

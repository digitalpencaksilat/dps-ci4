<?php
	$this->CI =& get_instance();
?>
<div class="container">
	<?php if (get_setting_live_server('kejuaraan_sedang_berlangsung')) : ?>
		<?php $this->CI->load->view('pendaftaran/components/kejuaraan_berlangsung') ?>
	<?php else : ?>
		<div class="row py-7">
			<div class="col-12 col-lg-5 d-flex justify-content-center align-items-center">
				<img src="<?= base_url('uploads/assets/'.$this->CI->config->item('poster', 'pendaftaran/gambar_dan_juknis')['file_name']) ?>" alt="<?= $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') ?>" class="img-fluid">
			</div>
			<div class="col-12 col-lg-7 ml-auto mr-auto text-center py-7">
				<h1 class="display-3 fw-bolder"><?= $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') ?></h1>
				<h4 class="fw-light mb-5">
					<?= $this->CI->config->item('landing_page_description', 'pendaftaran/profil_kejuaraan') ?>
				</h4>
				<div class="buttons">
					<a class="btn btn-primary btn-lg mb-3" href="<?= base_url('registrasi') ?>"><?= lang('daftar_sekarang') ?></a>
					<a href="<?= base_url("pendaftaran/download-juknis") ?>" class="btn btn-secondary btn-lg mb-3"><?= lang('download_juknis') ?></a>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

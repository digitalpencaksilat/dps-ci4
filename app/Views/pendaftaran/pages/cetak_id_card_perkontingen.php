     1|<div class="w-100" style="background-image: url('<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/cek-data-background.jpg') ?>'); 
     2|background-size: cover; background-position: center center; background-repeat: no-repeat">
     3|	<div class="container py-7">
     4|		<div class="row mt-5 mt-md-0">
     5|			<div class="col-lg-7 col-md-7 mr-auto text-left mb-4 d-flex flex-column justify-content-center">
     6|				<h6 class="small text-light"><?= get_instance()->get_setting('event_name') ?></h6>
     7|				<h1 class="text-light mb-3">
     8|					Mari kurangi penggunaan kertas dengan menggunakan,<br>
     9|					<span class="text-primary">ID Card Digital </span>
    10|				</h1>
    11|				<div>
    12|					<a href="#cetak_id_card" class="btn btn-primary">
    13|						Periksa Sekarang
    14|					</a>
    15|					<a href="<?= base_url('cetak-id-card-perpeserta')?>" class="btn btn-secondary">
    16|						Cetak Per Atlet
    17|					</a>
    18|				</div>
    19|			</div>
    20|			<div class="d-none d-md-block col-lg-5 col-md-12 d-flex flex-column justify-content-end"">
    21|				<img class=" img-fluid" src="<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/jadwal.png') ?>" alt="registrasi-event">
    22|			</div>
    23|		</div>
    24|	</div>
    25|</div>
    26|<section id="cetak_id_card" class="py-5">
    27|	<div class="container">
    28|		<div class="card shadow mt-n7">
    29|			<div class="card-body p-4">
    30|				<div class="row">
    31|					<div class="col-12">
    32|						<?php $this->load->view('shared_components/kontingen/tabel_cetak_id_card', ['data_kontingen' => $data_kontingen]) ?>
    33|					</div>
    34|				</div>
    35|			</div>
    36|		</div>
    37|	</div>
    38|</section>
    39|
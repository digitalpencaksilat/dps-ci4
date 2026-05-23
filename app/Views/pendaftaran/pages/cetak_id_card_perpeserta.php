     1|<div class="w-100" style="background-image: url('<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/cek-data-background.jpg') ?>'); 
     2|background-size: cover; background-position: center center; background-repeat: no-repeat">
     3|	<div class="container py-7">
     4|		<div class="row mt-5 mt-md-0">
     5|			<div class="col-lg-7 col-md-7 mr-auto text-left mb-4 d-flex flex-column justify-content-center">
     6|				<h6 class="small text-light"><?= get_instance()->get_setting('event_name') ?></h6>
     7|				<h1 class="text-light mb-3">
     8|					Mari kurangi penggunaan kertas dengan menggunakan,<br>
     9|					<span class="text-primary">ID CARD Digital </span>
    10|				</h1>
    11|				<div>
    12|					<a href="#cetak_id_card" class="btn btn-primary">
    13|						Periksa Sekarang
    14|					</a>
    15|
    16|					<a href="<?= base_url('cetak-id-card-perkontingen')?>" class="btn btn-secondary">
    17|						Cetak Per Kontingen
    18|					</a>
    19|				</div>
    20|			</div>
    21|			<div class="d-none d-md-block col-lg-5 col-md-12 d-flex flex-column justify-content-end"">
    22|				<img class=" img-fluid" src="<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/jadwal.png') ?>" alt="registrasi-event">
    23|			</div>
    24|		</div>
    25|	</div>
    26|</div>
    27|<section id="cetak_id_card" class="py-5">
    28|	<div class="container">
    29|		<div class="card shadow mt-n7">
    30|			<div class="card-body p-4">
    31|				<div class="row">
    32|					<div class="col-md-12 px-0">
    33|						<ul class="nav nav-pills nav-pills-primary justify-content-center">
    34|							<li class="nav-item">
    35|								<button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tanding">Tanding</button>
    36|							</li>
    37|							<li class="nav-item">
    38|								<button class="nav-link" data-bs-toggle="pill" data-bs-target="#seni">Seni</button>
    39|							</li>
    40|						</ul>
    41|					</div>
    42|					<div class="col-md-12 px-0">
    43|						<div class="tab-content mt-5">
    44|							<div class="tab-pane active table-responsive" id="tanding">
    45|								<?php $this->load->view('shared_components/peserta_tanding/tabel_cetak_id_card', ['data_peserta_tanding' => $data_peserta_tanding]) ?>
    46|							</div>
    47|							<div class="tab-pane fade table-responsive" id="seni">
    48|								<?php $this->load->view('shared_components/peserta_seni/tabel_cetak_id_card', ['data_peserta_seni' => $data_peserta_seni]) ?>
    49|							</div>
    50|						</div>
    51|					</div>
    52|				</div>
    53|			</div>
    54|		</div>
    55|	</div>
    56|</section>
    57|
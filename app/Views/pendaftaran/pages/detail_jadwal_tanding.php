     1|<div class="w-100" style="background-image: url('<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/header-background.jpg') ?>'); 
     2|	background-size: cover; background-position: center center; background-repeat: no-repeat">
     3|	<div class="container py-6">
     4|		<div class="row justify-content-center mt-5 mt-md-8">
     5|			<div class="col-lg-4 col-md-8 ml-auto mr-auto">
     6|				<div class="row">
     7|					<div class="col-12">
     8|						<h1 class="text-light fw-bolder">Jadwal Tanding Gelanggang <?= $jadwal_tanding->nama_gelanggang ?></h1>
     9|					</div>
    10|				</div>
    11|				<div class="row my-4">
    12|					<div class="col-12 text-left">
    13|						<a class="btn btn-primary mb-2" href="#jadwal">
    14|							Cek Jadwal Gelanggang
    15|						</a>
    16|						<a class="btn btn-info mb-2" href="<?= base_url('pendaftaran/cek-data') ?>">
    17|							Cek Jadwal Per Kontingen
    18|						</a>
    19|					</div>
    20|				</div>
    21|			</div>
    22|			<div class="d-none d-md-block col-lg-6 ml-auto text-center">
    23|				<img class="w-75" src="<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/jadwal.png') ?>" class="phone">
    24|			</div>
    25|		</div>
    26|	</div>
    27|</div>
    28|
    29|<section class="py-5" id="jadwal">
    30|	<div class="container">
    31|		<div class="row justify-content-center">
    32|			<div class="col-11 shadow-lg mt-n7 px-0">
    33|				<?php $this->load->view('shared_pages/detail_jadwal_tanding/all') ?>
    34|			</div>
    35|		</div>
    36|	</div>
    37|</section>
    38|
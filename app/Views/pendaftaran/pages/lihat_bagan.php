     1|<div class="w-100" style="background-image: url('<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/header-background.jpg') ?>'); 
     2|background-size: cover; background-position: center center; background-repeat: no-repeat">
     3|	<div class="container py-6">
     4|		<div class="row justify-content-center mt-5 mt-md-8">
     5|			<div class="col-lg-6 col-md-8 ml-auto mr-auto">
     6|				<div class="row">
     7|					<div class="col-12">
     8|						<h1 class="text-light fw-bolder">
     9|							<?= $kompetisi_tanding->nama_kategori_usia.' '.ucwords($kompetisi_tanding->jenis_kelamin)?> <br>
    10|							Kelas <?= $kompetisi_tanding->label ?> <?= ($kompetisi_tanding->jenis_perlombaan == 'pemasalan')? 'Pool '.$kompetisi_tanding->nomor_pool : ''?>
    11|						</h1>
    12|					</div>
    13|				</div>
    14|			</div>
    15|			<div class="d-none d-md-block col-lg-6 ml-auto text-center">
    16|				<img class="w-75" src="<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/bagan.png') ?>" class="phone">
    17|			</div>
    18|		</div>
    19|	</div>
    20|</div>
    21|
    22|<section class="py-5" id="bagan">
    23|	<div class="container">
    24|		<div class="card shadow mt-n7">
    25|			<div class="card-body p-2 p-md-4">
    26|				<div class="row">
    27|					<div class="col-md-12">
    28|						<?php $this->load->view('shared_components/kompetisi_tanding/bagan_pertandingan'); ?>
    29|					</div>
    30|				</div>
    31|			</div>
    32|		</div>
    33|	</div>
    34|</section>
    35|
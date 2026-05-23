     1|<div class="w-100" style="background-image: url('<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/header-background.jpg') ?>'); 
     2|background-size: cover; background-position: center center; background-repeat: no-repeat">
     3|	<div class="container py-6">
     4|		<div class="row justify-content-center mt-5 mt-md-8">
     5|			<div class="col-lg-6 col-md-8 ml-auto mr-auto">
     6|				<div class="row">
     7|					<div class="col-12">
     8|						<h1 class="display-4 text-light fw-bolder">Ketahui Siapa Lawanmu Lebih Awal !</h1>
     9|						<p class="text-left text-light fw-normal">
    10|							Silahkan melihat data bagan pertandingan
    11|						</p>
    12|					</div>
    13|				</div>
    14|				<div class="row my-4">
    15|					<div class="col-12 text-left">
    16|						<a class="btn btn-primary mb-2" href="#bagan">
    17|							Cek Bagan
    18|						</a>
    19|						<a class="btn btn-info mb-2" href="<?= base_url('pendaftaran/jadwal') ?>">
    20|							Lihat Jadwal
    21|						</a>
    22|					</div>
    23|				</div>
    24|			</div>
    25|			<div class="d-none d-md-block col-lg-6 ml-auto text-center">
    26|				<img class="w-75" src="<?= base_url('assets/images/brand/'.strtolower(ci3_config_item('\1', 'pendaftaran/profil_kejuaraan')).'/ilustrasi/bagan.png') ?>" class="phone">
    27|			</div>
    28|		</div>
    29|	</div>
    30|</div>
    31|
    32|<section class="py-5" id="bagan">
    33|	<div class="container">
    34|		<div class="card shadow mt-n7">
    35|			<div class="card-body p-2 p-md-4">
    36|				<div class="row">
    37|					<div class="col-md-12">
    38|						<ul class="nav nav-pills nav-pills-primary justify-content-center" role="tablist">
    39|							<li class="nav-item" role="presentation">
    40|								<button class="nav-link active" data-bs-toggle="pill" data-bs-target="#kelas">Berdasarkan Kelas</button>
    41|							</li>
    42|							<li class="nav-item" role="presentation">
    43|								<button class="nav-link" data-bs-toggle="pill" data-bs-target="#peserta">Berdasarkan Peserta</button>
    44|							</li>
    45|						</ul>
    46|						<div class="tab-content mt-5">
							<div class="tab-pane container active" id="kelas">
								<?= view('shared_components/kompetisi_tanding/tabel_daftar_bagan_by_kompetisi_tanding') ?>
							</div>
							<div class="tab-pane container fade" id="peserta">
								<?= view('shared_components/kompetisi_tanding/tabel_daftar_bagan_by_peserta') ?>
							</div>

    54|					</div>
    55|				</div>
    56|			</div>
    57|		</div>
    58|	</div>
    59|</section>
    60|
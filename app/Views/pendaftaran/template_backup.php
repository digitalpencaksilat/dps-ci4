     1|<!DOCTYPE html>
     2|<html lang="en">
     3|
     4|<head>
     5|	<meta charset="utf-8">
     6|	<meta http-equiv="X-UA-Compatible" content="IE=edge">
     7|	<meta name="viewport" content="width=device-width, initial-scale=1">
     8|	<meta name="description" content="Halaman Kontingen  <?php echo get_instance()->get_setting('event_name') ?>">
     9|	<meta name="author" content="">
    10|	<meta name="theme-color" content="#222222">
    11|	<link rel="icon" type="image/png" href="<?= get_instance()->get_setting('event_logo', 'pendaftaran/gambar_dan_juknis') ?>">
    12|
    13|	<title>
    14|		<?= get_instance()->get_setting('event_name') ?>
    15|	</title>
    16|	<?= view('pendaftaran/components/dependency' ) ?>
    17|	<link href="<?php echo base_url() ?>assets/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    18|	<link href="<?= phpb_theme_asset('/public/css/theme/red.css') ?>" rel="stylesheet" />
    19|	<link href="<?= phpb_theme_asset('/public/css/theme/landing_page.css') ?>" rel="stylesheet" />
    20|</head>
    21|
    22|<body>
    23|	<?= view('pendaftaran/components/topnav' ) ?>
    24|
    25|	<?php $this->load->view($main_view); ?>
    26|
    27|	<?php if (get_all_sponsors() !== null) : ?>
    28|		<div class="container py-5">
    29|			<div class="row justify-content-center">
    30|				<div class="col-md-12">
    31|					<h4 class="title text-center"><?= lang('Terima kasih atas dukungannya !') ?></h4>
    32|				</div>
    33|			</div>
    34|			<div class="row justify-content-center mt-4">
    35|				<?php
    36|				foreach (get_all_sponsors() as $key => $value) {
    37|					echo '
    38|                          <div class="col-md-3 col-lg-2 col-6 p-3">
    39|                            <img src="' . base_url() . 'uploads/assets/' . get_sponsor($key) . '" alt=" asd " class="img-fluid">
    40|                          </div>
    41|                        ';
    42|				}
    43|				?>
    44|			</div>
    45|		</div>
    46|	<?php endif; ?>
    47|	<?= view('shared_components/contact/whatsapp' ) ?>
    48|
    49|	<?= view('pendaftaran/components/footer' ) ?>
    50|</body>
    51|
    52|</html>
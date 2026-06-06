<?php
$bgUrl = $background_url ?? '';
$hideB = !empty($hide_bg);
?>
<div class="sertifikat"<?= (!$hideB && $bgUrl) ? ' style="background-image:url(' . esc($bgUrl) . ')"' : '' ?>>
    <div class="nomor"><h1>0001/CONTOH/SERTIFIKAT</h1></div>
    <div class="nama"><h1>NAMA ATLET CONTOH</h1></div>
    <div class="kategori"><h2>JUARA I TANDING PUTRA DEWASA KELAS A</h2></div>
    <div class="kontingen"><h3>KONTINGEN CONTOH</h3></div>
    <div class="sekolah"><h3>SMA NEGERI 1 CONTOH</h3></div>
    <div class="qrcode"><i class="fas fa-qrcode" style="font-size:3rem;"></i></div>
</div>

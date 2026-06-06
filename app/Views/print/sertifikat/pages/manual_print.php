<?php
/** Cetak sertifikat via window.print() dengan QR Code */
$bgUrl = $background_url ?? '';
$hideB = !empty($hide_bg);
?>
<div class="sertifikat"<?= (!$hideB && $bgUrl) ? ' style="background-image:url(' . esc($bgUrl) . ')"' : '' ?>>
    <div class="nomor"><h1 id="nomor"><?= esc($nomor ?? '') ?></h1></div>
    <div class="nama"><h1 id="nama"><?= esc($nama ?? '') ?></h1></div>
    <div class="kategori"><h2 id="kategori"><?= esc($kategori ?? '') ?></h2></div>
    <div class="kontingen"><h3 id="kontingen"><?= esc($kontingen ?? '') ?></h3></div>
    <div class="sekolah"><h3 id="sekolah"><?= esc($sekolah ?? '') ?></h3></div>
    <div class="qrcode"><div id="qrcode-container"></div></div>
</div>

<script src="<?= base_url('assets/qrcode/js/qrcode.min.js') ?>"></script>
<script>
(function(){
    var url = <?= json_encode($qrcode_url ?? '') ?>;
    if (url) {
        new QRCode(document.getElementById('qrcode-container'), {
            text: url, width: 500, height: 500,
            colorDark: '#000000', colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    }
    setTimeout(function(){ window.print(); }, 500);
    window.addEventListener('afterprint', function(){ window.close(); });
})();
</script>

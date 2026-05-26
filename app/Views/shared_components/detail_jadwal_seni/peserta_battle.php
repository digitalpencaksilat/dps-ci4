<?php
$name = $partai_seni->{'anggota_kelompok_peserta_seni_' . $corner} ?? null;
$kontingen = $partai_seni->{'nama_kontingen_' . $corner} ?? '-';
$calon = $partai_seni->{'calon_anggota_kelompok_peserta_seni_' . $corner} ?? '-';
$gelanggang = $partai_seni->{'gelanggang_calon_anggota_kelompok_peserta_seni_' . $corner} ?? '-';
?>
<?php if (empty($name)): ?>
    <span class="mb-0 d-block text-capitalize px-2 text-center text-decoration-underline fst-italic text-wrap">
        <u class="d-block fw-bold"><?= (($partai_seni->babak_battle ?? '') === 'Perebutan Juara Tiga') ? 'Kalah dari Partai Ke' : 'Pemenang Partai Ke' ?> <?= esc((string) $calon) ?></u>
        Dari Gelanggang <?= esc((string) $gelanggang) ?>
    </span>
<?php else: ?>
    <span class="fw-bolder mb-0 d-block text-capitalize text-decoration-underline text-wrap"><?= esc($name) ?></span>
    <span class="text-capitalize text-wrap d-block"><?= esc($kontingen) ?></span>
<?php endif; ?>

<?php
/**
 * @var object|null $peserta
 * @var array       $partai
 * @var string      $background_url
 */
$bg = (string) ($background_url ?? '');
$photoUrl = service('idCardPhoto')->photoUrl($peserta->foto ?? null);
?>
<div class="kartu-peserta">
    <?php if ($bg !== '') : ?>
        <img class="kartu-bg" src="<?= esc($bg) ?>" alt="" aria-hidden="true">
    <?php endif; ?>
    <div class="atlet-img">
        <?php if ($photoUrl !== '') : ?>
            <img class="img-fluid" src="<?= esc($photoUrl) ?>" alt="Foto" onerror="this.style.display='none'">
        <?php endif; ?>
    </div>

    <h3 class="nama-atlet"><?= esc($peserta->nama_pendaftar ?? '-') ?></h3>
    <p class="kontingen-atlet"><?= esc($peserta->nama_kontingen ?? '-') ?></p>

    <div class="barcode" id="bar_tanding_<?= esc((string) ($peserta->id_peserta_tanding ?? 0)) ?>"></div>

    <h3 class="kategori-lomba">
        <?= esc(ucwords(($peserta->nama_kategori_usia ?? '-') . ' - ' . ($peserta->jenis_kelamin ?? '-') . ' Kelas ' . ($peserta->label ?? '-'))) ?>
    </h3>

    <table class="tabel-pertandingan">
        <thead>
            <tr>
                <th>Arena</th>
                <th>Babak</th>
                <th>Nomor Partai</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($partai ?? []) as $p) : ?>
                <tr>
                    <td><?= esc($p['gelanggang'] ?? '-') ?></td>
                    <td><?= esc($p['babak'] ?? '-') ?></td>
                    <td class="<?= esc($p['sudut'] ?? '') ?>"><?= esc($p['nomor_partai'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($partai)) : ?>
                <tr><td colspan="3" class="text-center">Belum ada jadwal</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

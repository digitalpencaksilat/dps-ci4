<?php
/**
 * @var object|null $peserta
 * @var array       $partai          // dipakai bila sistem_penampilan = battle
 * @var array       $data_penampilan // dipakai bila sistem_penampilan = pool
 * @var array       $data_battle     // raw data battle (legacy compat, jarang dipakai langsung)
 * @var string      $background_url
 */
$bg = (string) ($background_url ?? '');
$sistemPenampilan = (string) ($peserta->sistem_penampilan ?? 'pool');
?>
<div class="kartu-peserta">
    <?php if ($bg !== '') : ?>
        <img class="kartu-bg" src="<?= esc($bg) ?>" alt="" aria-hidden="true">
    <?php endif; ?>
    <div class="atlet-img">
        <?php
        $fotoPath = ! empty($peserta->foto) ? (FCPATH . 'uploads/peserta/foto/' . $peserta->foto) : '';
        if ($fotoPath !== '' && is_file($fotoPath)) : ?>
            <img class="img-fluid" src="<?= base_url('uploads/peserta/foto/') . esc($peserta->foto) ?>" alt="Foto" onerror="this.style.display='none'">
        <?php endif; ?>
    </div>

    <h3 class="nama-atlet"><?= esc($peserta->nama_pendaftar ?? '-') ?></h3>
    <p class="kontingen-atlet"><?= esc($peserta->nama_kontingen ?? '-') ?></p>

    <div class="barcode" id="bar_seni_<?= esc((string) ($peserta->id_peserta_seni ?? 0)) ?>"></div>

    <h3 class="kategori-lomba">
        <?= esc(ucwords(($peserta->nama_kategori_usia ?? '-') . ' ' . ($peserta->jenis_kelamin ?? '-') . ' - ' . ($peserta->jenis_seni ?? '') . ' ' . ($peserta->nama_seni ?? ''))) ?>
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
            <?php
            $hasRow = false;
            if ($sistemPenampilan === 'pool') :
                foreach (($data_penampilan ?? []) as $penampilan) :
                    if (($penampilan->id_kelompok_peserta_seni ?? null) == ($peserta->id_kelompok_peserta_seni ?? null)) :
                        $hasRow = true;
                        ?>
                        <tr>
                            <td><?= esc($penampilan->nama_gelanggang ?? '-') ?></td>
                            <td class="text-uppercase"><?= esc($penampilan->babak_pool ?? '-') ?></td>
                            <td><?= esc((string) ($penampilan->nomor_partai ?? '-')) ?></td>
                        </tr>
                        <?php
                    endif;
                endforeach;
            else :
                foreach (($partai ?? []) as $p) :
                    $hasRow = true;
                    ?>
                    <tr>
                        <td><?= esc($p['gelanggang'] ?? '-') ?></td>
                        <td><?= esc($p['babak'] ?? '-') ?></td>
                        <td class="<?= esc($p['sudut'] ?? '') ?>"><?= esc($p['nomor_partai'] ?? '-') ?></td>
                    </tr>
                    <?php
                endforeach;
            endif;

            if (! $hasRow) : ?>
                <tr><td colspan="3" class="text-center">Belum ada jadwal</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

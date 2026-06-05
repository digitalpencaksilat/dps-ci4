<div class="kartu-peserta" style="background-image: url('<?= esc($background_url ?? base_url('uploads/kartu-peserta/atlet.png')) ?>')">
    <div class="atlet-img">
        <?php if (! empty($peserta->foto)) : ?>
            <img class="img-fluid" src="<?= base_url('uploads/peserta/foto/') . esc($peserta->foto) ?>" alt="Foto">
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
            <?php if (($peserta->sistem_penampilan ?? 'pool') === 'pool') : ?>
                <?php foreach (($data_penampilan ?? []) as $penampilan) : ?>
                    <?php if (($penampilan->id_kelompok_peserta_seni ?? null) == ($peserta->id_kelompok_peserta_seni ?? null)) : ?>
                        <tr>
                            <td><?= esc($penampilan->nama_gelanggang ?? '-') ?></td>
                            <td class="text-uppercase"><?= esc($penampilan->babak_pool ?? '-') ?></td>
                            <td><?= esc((string) ($penampilan->nomor_partai ?? '-')) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <?php foreach (($partai ?? []) as $p) : ?>
                    <tr>
                        <td><?= esc($p['gelanggang'] ?? '-') ?></td>
                        <td><?= esc($p['babak'] ?? '-') ?></td>
                        <td class="<?= esc($p['sudut'] ?? '') ?>"><?= esc($p['nomor_partai'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (empty($partai) && empty($data_penampilan)) : ?>
                <tr><td colspan="3" class="text-center">Belum ada jadwal</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

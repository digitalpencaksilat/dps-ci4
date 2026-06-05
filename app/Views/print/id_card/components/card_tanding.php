<div class="kartu-peserta" style="background-image: url('<?= esc($background_url ?? base_url('uploads/kartu-peserta/atlet.png')) ?>')">
    <div class="atlet-img">
        <?php if (! empty($peserta->foto)) : ?>
            <img class="img-fluid" src="<?= base_url('uploads/peserta/foto/') . esc($peserta->foto) ?>" alt="Foto">
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

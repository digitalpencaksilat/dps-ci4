<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$baseUrl = base_url('admin/super/drawing-prestasi/seni-pool');

$labelKategori = static function ($row): string {
    $label = trim(($row->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($row->jenis_kelamin ?? '')));
    $label .= ', ' . ucwords((string) ($row->jenis_seni ?? '')) . ' ' . ($row->nama_seni ?? '');
    $pool = ($row->jenis_perlombaan ?? '') === 'pemasalan' ? ' Pool ' . ($row->nomor_pool ?? '-') : '';
    $jumlah = ' - ' . (int) ($row->jumlah_kelompok_peserta_seni ?? 0) . ' Peserta';

    return $label . $pool . $jumlah;
};
?>

<section class="admin-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-1">
        <div>
            <p class="eyebrow mb-1">Drawing Prestasi</p>
            <h3 class="section-title h4 mb-0">Drawing Seni Pool</h3>
            <p class="muted-copy mb-0 small">Pilih satu kategori seni prestasi bersistem pool, lalu undi nomor penampilan via roulette.</p>
        </div>
    </div>

    <form action="<?= $baseUrl ?>" method="get" class="row g-2 align-items-end mt-2">
        <div class="col-12 col-lg-9">
            <label class="form-label small fw-semibold">Kategori Seni Pool Prestasi</label>
            <select class="form-select" name="id" required>
                <option value="" disabled <?= $selected === null ? 'selected' : '' ?>>-- Pilih kategori --</option>
                <?php foreach ($rows as $row) : ?>
                    <option value="<?= (int) $row->id_kompetisi_seni ?>" <?= ($selected !== null && (int) $selected->id_kompetisi_seni === (int) $row->id_kompetisi_seni) ? 'selected' : '' ?>>
                        <?= esc($labelKategori($row)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-3">
            <button type="submit" class="btn btn-admin-brand rounded-pill w-100">
                <i class="fas fa-magnifying-glass me-1"></i> Tampilkan
            </button>
        </div>
    </form>

    <?php if ($rows === []) : ?>
        <p class="muted-copy mb-0 mt-3">Belum ada kategori seni pool prestasi yang tersedia.</p>
    <?php endif; ?>

    <?php if ($selected !== null) : ?>
        <div class="row g-2 mt-3">
            <div class="col-6">
                <?php if ($prev !== null) : ?>
                    <a class="btn btn-outline-danger rounded-pill w-100 text-truncate" href="<?= $baseUrl . '?id=' . (int) $prev->id_kompetisi_seni ?>">
                        <i class="fas fa-chevron-left me-1"></i> <?= esc(trim(($prev->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($prev->jenis_seni ?? '')) . ' ' . ($prev->nama_seni ?? ''))) ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-6 text-end">
                <?php if ($next !== null) : ?>
                    <a class="btn btn-outline-danger rounded-pill w-100 text-truncate" href="<?= $baseUrl . '?id=' . (int) $next->id_kompetisi_seni ?>">
                        <?= esc(trim(($next->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($next->jenis_seni ?? '')) . ' ' . ($next->nama_seni ?? ''))) ?> <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($selected !== null) : ?>
    <?php
    $idKompetisi = (int) $selected->id_kompetisi_seni;
    $jumlahKelompok = (int) ($selected->jumlah_kelompok_peserta_seni ?? 0);
    ?>

    <?php if (($selected->sistem_penampilan ?? '') !== 'pool') : ?>
        <section class="admin-card">
            <h4 class="h5 mb-2">Ooopss</h4>
            <p class="muted-copy mb-0">Kategori ini tidak dikonfigurasi dengan sistem penampilan <strong>pool</strong>, sehingga pengundian tidak dapat dilakukan.</p>
        </section>
    <?php elseif ($jumlahKelompok <= 1) : ?>
        <section class="admin-card">
            <h4 class="h5 mb-2">Ooopss</h4>
            <p class="muted-copy">Pool ini hanya berisi <?= count($kelompok) ?> peserta, minimal 2 peserta diperlukan untuk pengundian.</p>
            <ul class="list-group">
                <?php foreach ($kelompok as $item) : ?>
                    <li class="list-group-item">
                        <?= esc($item->anggota_kelompok_peserta_seni ?? '-') ?><br><strong class="text-uppercase"><?= esc($item->nama_kontingen ?? '-') ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php else : ?>
        <section class="admin-card">
            <div class="mb-3">
                <h4 class="h5 mb-1"><?= esc($labelKategori($selected)) ?></h4>
                <p class="muted-copy mb-0 small">Putar roulette untuk menentukan nomor undi tiap peserta, lalu tetapkan undian.</p>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="d-flex flex-column align-items-center">
                        <canvas id="canvasRoulette" width="434" height="434">
                            <p>Browser Anda tidak mendukung canvas.</p>
                        </canvas>
                        <button type="button" class="btn btn-danger rounded-pill mt-3 px-5" onclick="startSpin()">SPIN</button>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-3" id="tabelHasilSpin">
                            <thead>
                                <tr><th>Nama Atlet</th><th>Kontingen</th><th class="text-end">Nomor Undi</th></tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <form action="<?= base_url('admin/super/drawing-prestasi/seni-pool/' . $idKompetisi . '/beri-nomor-undi') ?>" method="post" id="formUndianSeniPool">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-admin-brand rounded-pill w-100" id="btnTetapkanUndian" disabled
                            onclick="confirmAdminAction(this.form, 'Tetapkan Undian?', 'Nomor undi peserta akan disimpan sesuai hasil roulette.', 'Ya, Tetapkan')">
                            Tetapkan Undian
                        </button>
                    </form>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="h6 mb-3">Daftar Peserta</h5>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr><th>No Undi</th><th>Anggota</th><th>Kontingen</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kelompok as $item) : ?>
                            <tr>
                                <td><?= esc((string) ($item->nomor_undi ?? '-')) ?></td>
                                <td><?= esc($item->anggota_kelompok_peserta_seni ?? '-') ?></td>
                                <td class="text-uppercase"><?= esc($item->nama_kontingen ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <script src="<?= base_url('assets/js/plugins/winwheel.min.js') ?>"></script>
        <script>
            (function () {
                var dataKelompok = <?= json_encode(array_map(static fn ($k) => [
                    'id_kelompok_peserta_seni' => (int) $k->id_kelompok_peserta_seni,
                    'anggota_kelompok_peserta_seni' => (string) ($k->anggota_kelompok_peserta_seni ?? '-'),
                    'nama_kontingen' => (string) ($k->nama_kontingen ?? '-'),
                ], $kelompok), JSON_UNESCAPED_UNICODE) ?>;

                var jumlahPeserta = dataKelompok.length;
                var nomorUndi = 1;
                var wheelSpinning = false;
                var urutanTerpilih = [];
                var palette = ['#222222', '#006fbe', '#f12a2a', '#DB073D', '#DBA507', '#00305a', '#0D6986', '#f2b705', '#546e7a'];

                function randomWarna() {
                    return palette[Math.floor(Math.random() * palette.length)];
                }

                var segments = dataKelompok.map(function (v) {
                    return {
                        textFillStyle: '#fff',
                        fillStyle: randomWarna(),
                        text: v.anggota_kelompok_peserta_seni,
                        anggota_kelompok_peserta_seni: v.anggota_kelompok_peserta_seni,
                        nama_kontingen: v.nama_kontingen,
                        id_kelompok_peserta_seni: v.id_kelompok_peserta_seni
                    };
                });

                var wheel = new Winwheel({
                    canvasId: 'canvasRoulette',
                    numSegments: segments.length,
                    outerRadius: 212,
                    textFontSize: 13,
                    segments: segments,
                    animation: { type: 'spinToStop', duration: 4, spins: 10, callbackFinished: hasilSpin }
                });

                window.startSpin = function () {
                    if (!wheelSpinning) {
                        wheel.startAnimation();
                        wheelSpinning = true;
                    }
                };

                function resetWheel() {
                    wheel.stopAnimation(false);
                    wheel.rotationAngle = 0;
                    wheel.draw();
                    wheelSpinning = false;
                }

                function hasilSpin(indicatedSegment) {
                    if (urutanTerpilih.indexOf(indicatedSegment.id_kelompok_peserta_seni) === -1) {
                        Swal.fire({
                            title: indicatedSegment.text,
                            text: 'Atlet mendapatkan nomor undi = ' + nomorUndi,
                            confirmButtonText: 'OK'
                        }).then(function () {
                            ambilAtlet(indicatedSegment);
                        });
                    } else {
                        Swal.fire('Silakan diulang kembali...');
                        resetWheel();
                    }
                }

                function ambilAtlet(seg) {
                    document.querySelector('#tabelHasilSpin tbody').insertAdjacentHTML('beforeend',
                        '<tr><td>' + seg.anggota_kelompok_peserta_seni + '</td><td class="text-uppercase">' + seg.nama_kontingen + '</td><td class="text-end">' + nomorUndi + '</td></tr>');

                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'id_kelompok_peserta_seni[]';
                    input.value = seg.id_kelompok_peserta_seni;
                    document.getElementById('formUndianSeniPool').appendChild(input);

                    urutanTerpilih.push(seg.id_kelompok_peserta_seni);
                    nomorUndi++;

                    for (var i = 0; i < wheel.segments.length; i++) {
                        var s = wheel.segments[i];
                        if (s !== null && s !== undefined && s.id_kelompok_peserta_seni === seg.id_kelompok_peserta_seni) {
                            wheel.deleteSegment(i);
                        }
                    }

                    resetWheel();

                    if (urutanTerpilih.length === jumlahPeserta) {
                        document.getElementById('btnTetapkanUndian').disabled = false;
                    }
                }
            })();
        </script>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>

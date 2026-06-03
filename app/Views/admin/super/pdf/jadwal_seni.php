<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 0; padding: 0; color: #222222; }
        .table-header { width: 100%; border-collapse: collapse; border: 2px solid #222222; margin-bottom: 5px; font-family: "Arial Black", Gadget, sans-serif; }
        .table-header td { padding: 5px; text-align: center; vertical-align: middle; }
        .table-header .logo-cell { width: 20%; padding: 8px; }
        .table-header .text-cell { width: 60%; padding: 8px 15px; }
        .jadwal-title { font-size: 12px; font-weight: bold; text-transform: uppercase; margin: 8px 0 0 0; text-align: center; }
        .nama-kejuaraan { font-size: 18px; font-weight: bold; text-align: center; margin: 5px 0; text-transform: uppercase; font-family: "Arial Black", Gadget, sans-serif; }
        .panitia-kejuaraan { margin: 0 0 8px 0; font-size: 11px; text-align: center; font-family: "Arial Black", Gadget, sans-serif; }
        .table-metadata { width: 100%; border-collapse: collapse; border: 2px solid #222222; margin-bottom: 8px; font-family: "Arial Black", Gadget, sans-serif; }
        .table-metadata td { background-color: #ffffff; color: #222222; border: 1px solid #222222; padding: 5px 10px; text-align: center; font-weight: bold; font-size: 10pt; }
        .judul-sub-jadwal { font-size: 11pt; font-weight: bold; text-transform: uppercase; text-align: center; margin: 12px 0 5px 0; font-family: "Arial Black", Gadget, sans-serif; }
        .jadwal-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        .jadwal-table th { padding: 8px 6px; font-size: 9pt; border: 1px solid #222222; font-weight: bold; text-align: center; vertical-align: middle; }
        .jadwal-table td { padding: 8px 6px; font-size: 9pt; border: 1px solid #dddddd; text-align: center; vertical-align: middle; word-wrap: break-word; }
        .bg-dark { background-color: #222222; color: #ffffff; }
        .bg-blue { background-color: #006fbe; color: #ffffff; }
        .bg-red { background-color: #f12a2a; color: #ffffff; }
        .nama-atlet { text-decoration: underline; font-weight: bold; text-transform: capitalize; margin: 0; }
        .kontingen, .nama-kontingen { font-size: 8pt; font-style: italic; margin: 2px 0 0 0; }
    </style>
</head>
<body>
<?php
$logoHost = get_setting('event_host_big_logo', 'pendaftaran/gambar_dan_juknis')
    ?? get_setting('event_host_logo', 'pendaftaran/gambar_dan_juknis')
    ?? get_setting('event_logo', 'pendaftaran/gambar_dan_juknis');
$logoEvent = get_setting('event_big_logo', 'pendaftaran/gambar_dan_juknis')
    ?? get_setting('event_logo', 'pendaftaran/gambar_dan_juknis');
$eventName = (string) (get_setting('event_name') ?? 'Digital Pencak Silat');
$eventHost = (string) (get_setting('event_host') ?? '');
$brandName = (string) (get_setting('brand_name') ?? 'Digital Pencak Silat');
$brandAbbr = strtolower((string) (get_setting('brand_abbreviation') ?? 'dps'));
$brandLogo = FCPATH . 'assets/images/brand/' . $brandAbbr . '/logo.png';
$bulanId = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
$hariId = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
$tgl = ! empty($jadwal->tanggal) ? date_create((string) $jadwal->tanggal) : null;
$tanggalId = '-';
if ($tgl !== false && $tgl !== null) {
    $hari = $hariId[date_format($tgl, 'l')] ?? date_format($tgl, 'l');
    $tanggalId = $hari . ', ' . date_format($tgl, 'j') . ' ' . ($bulanId[(int) date_format($tgl, 'n')] ?? date_format($tgl, 'F')) . ' ' . date_format($tgl, 'Y');
}
$tampilkanSkor = ! empty($withScore);
$rows = $details ?? [];
$battleRows = array_values(array_filter($rows, static fn($row) => ! empty($row->id_battle_seni)));
$poolRows = array_values(array_filter($rows, static fn($row) => empty($row->id_battle_seni)));
$poolGroups = [];
foreach ($poolRows as $row) {
    $key = implode('|', [
        (string) ($row->jenis_seni ?? ''),
        (string) ($row->jenis_kelamin ?? ''),
        (string) ($row->nama_seni ?? ''),
        (string) ($row->nama_kategori_usia ?? ''),
        (string) ($row->nomor_pool ?? ''),
    ]);
    if (! isset($poolGroups[$key])) {
        $poolGroups[$key] = ['meta' => $row, 'rows' => []];
    }
    $poolGroups[$key]['rows'][] = $row;
}
?>
<table class="table-header">
    <tr>
        <td class="logo-cell">
            <?php if (! empty($logoHost)) : ?>
                <img src="<?= esc((string) $logoHost) ?>" style="max-height:75px; max-width:75px; width:auto;" alt="Logo Host">
            <?php endif; ?>
        </td>
        <td class="text-cell">
            <h2 class="jadwal-title">JADWAL PENAMPILAN SENI</h2>
            <p class="nama-kejuaraan"><?= esc(strtoupper($eventName)) ?></p>
            <p class="panitia-kejuaraan"><?= esc($eventHost) ?></p>
        </td>
        <td class="logo-cell">
            <?php if (! empty($logoEvent)) : ?>
                <img src="<?= esc((string) $logoEvent) ?>" style="max-height:75px; max-width:75px; width:auto;" alt="Logo Event">
            <?php endif; ?>
        </td>
    </tr>
</table>

<table class="table-metadata">
    <tr>
        <td style="width:30%;"><?= esc($tanggalId) ?></td>
        <td style="width:30%;"><?= esc(substr((string) ($jadwal->jam_mulai ?? '-'), 0, 5) . ' - ' . substr((string) ($jadwal->jam_selesai ?? '-'), 0, 5)) ?></td>
        <td style="width:20%;">ARENA <?= esc(strtoupper((string) ($jadwal->nama_gelanggang ?? '-'))) ?></td>
        <td style="width:20%;"><?= esc((string) ($jadwal->keterangan_jadwal ?? $jadwal->keterangan ?? '')) ?></td>
    </tr>
</table>

<?php foreach ($poolGroups as $group) : ?>
    <?php $meta = $group['meta']; ?>
    <p class="judul-sub-jadwal">
        <?php
        $jenisSeni = (string) ($meta->jenis_seni ?? '');
        echo esc($jenisSeni);
        if (strcasecmp($jenisSeni, 'berkelompok') !== 0) {
            echo ' ' . esc((string) ($meta->jenis_kelamin ?? '')) . ' ';
        }
        echo esc((string) ($meta->nama_seni ?? '') . ' ' . (string) ($meta->nama_kategori_usia ?? '') . ' - Pool ' . (string) ($meta->nomor_pool ?? '-'));
        ?>
    </p>
    <table class="jadwal-table">
        <thead>
            <tr>
                <th class="bg-dark" style="width:12%;">MATCH</th>
                <th class="bg-dark" style="width:28%;">NAME</th>
                <th class="bg-dark" style="width:20%;">TEAM</th>
                <th class="bg-dark" style="width:12%;">TIME</th>
                <th class="bg-dark" style="width:12%;">SCORE</th>
                <th class="bg-dark" style="width:16%;">MEDAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($group['rows'] as $row) : ?>
                <?php
                $medal = (string) ($row->jenis_medali_pool ?? '');
                $colorMap = ['emas' => 'gold', 'perak' => 'silver', 'perunggu' => '#cd7f32'];
                $medalColor = $colorMap[strtolower($medal)] ?? '';
                $time = '';
                if ($tampilkanSkor && isset($row->waktu_tampil) && $row->waktu_tampil !== null && $row->waktu_tampil !== '') {
                    $seconds = (int) $row->waktu_tampil;
                    $time = sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
                }
                ?>
                <tr>
                    <td><?= esc((string) ($row->nomor_partai ?? '-')) ?></td>
                    <td style="text-align:left; padding-left:6px;"><span class="nama-atlet"><?= esc(ucwords(strtolower((string) ($row->anggota_kelompok_peserta_seni ?? '-')))) ?></span></td>
                    <td><?= esc(strtoupper((string) ($row->nama_kontingen ?? '-'))) ?></td>
                    <td><?= esc($time) ?></td>
                    <td><?= $tampilkanSkor && isset($row->nilai_akhir) && $row->nilai_akhir !== null ? esc(number_format((float) $row->nilai_akhir, 3, '.', '')) : '' ?></td>
                    <td style="background:<?= esc($tampilkanSkor ? $medalColor : '') ?>; font-weight:bold; font-size:9pt;"><?= $tampilkanSkor ? esc(strtoupper($medal)) : '' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

<?php if (! empty($battleRows)) : ?>
<table class="jadwal-table">
    <thead>
        <tr>
            <th class="bg-dark" style="width:10%;">MATCH</th>
            <th class="bg-dark" style="width:9%;">ROUND</th>
            <th class="bg-blue" style="width:23%;">BLUE</th>
            <th class="bg-dark" style="width:17%;">CLASS</th>
            <th class="bg-red" style="width:23%;">RED</th>
            <th class="bg-dark" colspan="2" style="width:18%;">SCORE</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($battleRows as $row) : ?>
            <?php
            $babak = (string) ($row->babak_battle ?? '');
            $roundLabel = $babak === '' ? 'belum ditentukan' : ($babak === 'Perebutan Juara Tiga' ? 'Third Place' : $babak);
            $blueStyle = '';
            $redStyle = '';
            if ($tampilkanSkor && ! empty($row->id_penampilan_seni_pemenang)) {
                if ((string) $row->id_penampilan_seni_pemenang === (string) ($row->id_penampilan_seni_biru ?? '')) {
                    $blueStyle = 'background-color:#006fbe; color:#ffffff;';
                } elseif ((string) $row->id_penampilan_seni_pemenang === (string) ($row->id_penampilan_seni_merah ?? '')) {
                    $redStyle = 'background-color:#f12a2a; color:#ffffff;';
                }
            }
            ?>
            <tr>
                <td><?= esc((string) ($row->nomor_partai ?? '-')) ?></td>
                <td><?= $roundLabel === 'belum ditentukan' ? '<i>belum ditentukan</i>' : esc($roundLabel) ?></td>
                <td>
                    <?php if (empty($row->anggota_kelompok_peserta_seni_biru) && ! empty($row->calon_anggota_kelompok_peserta_seni_biru)) : ?>
                        <i><?= $babak === 'Perebutan Juara Tiga' ? 'Loser from' : 'Winner from' ?> <b>Match <?= esc((string) $row->calon_anggota_kelompok_peserta_seni_biru) ?></b></i><br>
                        <small>From Arena <?= esc((string) ($row->gelanggang_calon_anggota_kelompok_peserta_seni_biru ?? '-')) ?></small>
                    <?php elseif (empty($row->anggota_kelompok_peserta_seni_biru) && (string) ($row->jenis_kemenangan_battle ?? '') === 'BYE') : ?>
                        <i>BYE</i>
                    <?php elseif (! empty($row->anggota_kelompok_peserta_seni_biru)) : ?>
                        <p class="nama-atlet"><?= esc(ucwords(strtolower((string) $row->anggota_kelompok_peserta_seni_biru))) ?></p>
                        <p class="kontingen">(<?= esc(strtoupper((string) ($row->nama_kontingen_biru ?? '-'))) ?>)</p>
                    <?php else : ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?= esc((string) ($row->nama_kategori_usia_battle ?? '-')) ?> <?= esc((string) ($row->jenis_kelamin_battle ?? '')) ?><br>
                    <strong><?= esc((string) ($row->jenis_seni_battle ?? '-')) ?> - <?= esc((string) ($row->nama_seni_battle ?? '-')) ?></strong>
                    <?php if (! empty($row->nomor_pool_battle)) : ?>
                        <br>Pool <?= esc((string) $row->nomor_pool_battle) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (empty($row->anggota_kelompok_peserta_seni_merah) && ! empty($row->calon_anggota_kelompok_peserta_seni_merah)) : ?>
                        <i><?= $babak === 'Perebutan Juara Tiga' ? 'Loser from' : 'Winner from' ?> <b>Match <?= esc((string) $row->calon_anggota_kelompok_peserta_seni_merah) ?></b></i><br>
                        <small>From Arena <?= esc((string) ($row->gelanggang_calon_anggota_kelompok_peserta_seni_merah ?? '-')) ?></small>
                    <?php elseif (empty($row->anggota_kelompok_peserta_seni_merah) && (string) ($row->jenis_kemenangan_battle ?? '') === 'BYE') : ?>
                        <i>BYE</i>
                    <?php elseif (! empty($row->anggota_kelompok_peserta_seni_merah)) : ?>
                        <p class="nama-atlet"><?= esc(ucwords(strtolower((string) $row->anggota_kelompok_peserta_seni_merah))) ?></p>
                        <p class="kontingen">(<?= esc(strtoupper((string) ($row->nama_kontingen_merah ?? '-'))) ?>)</p>
                    <?php else : ?>
                        -
                    <?php endif; ?>
                </td>
                <td style="font-weight:bold; <?= esc($blueStyle) ?>"><?= $tampilkanSkor && isset($row->nilai_akhir_biru) && $row->nilai_akhir_biru !== null ? esc(number_format((float) $row->nilai_akhir_biru, 3, '.', '')) : '' ?></td>
                <td style="font-weight:bold; <?= esc($redStyle) ?>"><?= $tampilkanSkor && isset($row->nilai_akhir_merah) && $row->nilai_akhir_merah !== null ? esc(number_format((float) $row->nilai_akhir_merah, 3, '.', '')) : '' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php if (empty($poolGroups) && empty($battleRows)) : ?>
    <p style="text-align:center; font-style:italic; margin-top:20px;">Tidak ada partai yang dialokasikan.</p>
<?php endif; ?>

<div style="position: absolute; bottom: 3mm; right: 3mm;">
    <table style="border-collapse: collapse; border: none;">
        <tr>
            <td style="border: none; padding: 0 4px; vertical-align: middle;">
                <?php if (is_file($brandLogo)) : ?>
                    <img src="<?= esc($brandLogo) ?>" style="height: 22px; width: auto; vertical-align: middle;" alt="Logo">
                <?php endif; ?>
            </td>
            <td style="border: none; padding: 0 4px; vertical-align: middle;">
                <span style="font-size: 9.5pt; color: #555555; font-family: Arial, sans-serif; white-space: nowrap;">
                    Powered by <strong><?= esc($brandName) ?></strong> &copy; <?= esc(date('Y')) ?>
                </span>
            </td>
        </tr>
    </table>
</div>
</body>
</html>

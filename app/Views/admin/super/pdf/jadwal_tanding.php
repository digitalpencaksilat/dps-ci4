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
        .jadwal-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .jadwal-table th { padding: 8px 6px; font-size: 9pt; border: 1px solid #222222; font-weight: bold; text-align: center; vertical-align: middle; }
        .jadwal-table td { padding: 8px 6px; font-size: 9pt; border: 1px solid #dddddd; text-align: center; vertical-align: middle; }
        .bg-dark { background-color: #222222; color: #ffffff; }
        .bg-blue { background-color: #006fbe; color: #ffffff; }
        .bg-red { background-color: #f12a2a; color: #ffffff; }
        .nama-atlet { text-decoration: underline; font-weight: bold; text-transform: capitalize; margin: 0; }
        .kontingen { font-size: 8pt; font-style: italic; margin: 2px 0 0 0; }
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
$babakList = [];
foreach (($details ?? []) as $row) {
    $babak = trim((string) ($row->babak ?? ''));
    if ($babak !== '' && ! in_array($babak, $babakList, true)) {
        $babakList[] = $babak;
    }
}
$tampilkanSkor = ! empty($withScore);
?>
<table class="table-header">
    <tr>
        <td class="logo-cell">
            <?php if (! empty($logoHost)) : ?>
                <img src="<?= esc((string) $logoHost) ?>" style="max-height:75px; max-width:75px; width:auto;" alt="Logo Host">
            <?php endif; ?>
        </td>
        <td class="text-cell">
            <h2 class="jadwal-title">JADWAL TANDING</h2>
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
        <td style="width:30%;"><?= esc(implode(', ', $babakList)) ?></td>
        <td style="width:20%;"><?= esc(substr((string) ($jadwal->jam_mulai ?? '-'), 0, 5) . ' - ' . substr((string) ($jadwal->jam_selesai ?? '-'), 0, 5)) ?></td>
        <td style="width:20%;">ARENA <?= esc(strtoupper((string) ($jadwal->nama_gelanggang ?? '-'))) ?></td>
    </tr>
</table>

<?php if (! empty($details)) : ?>
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
        <?php foreach (($details ?? []) as $row) : ?>
            <?php
            $babak = (string) ($row->babak ?? '');
            $roundLabel = $babak === '' ? 'belum ditentukan' : ($babak === 'Perebutan Juara Tiga' ? 'Third Place' : $babak);
            $scoreBlue = ($row->skor_biru === null || $row->skor_biru === '') ? '' : (string) $row->skor_biru;
            $scoreRed = ($row->skor_merah === null || $row->skor_merah === '') ? '' : (string) $row->skor_merah;
            $scoreBlueClass = '';
            $scoreRedClass = '';
            if ($tampilkanSkor && ($row->id_atlet_biru ?? null) !== null && ($row->id_atlet_merah ?? null) !== null) {
                if ((string) ($row->id_pemenang ?? '') !== '' && (string) $row->id_pemenang === (string) ($row->id_atlet_biru ?? '')) {
                    $scoreBlueClass = 'bg-blue';
                } elseif ((string) ($row->id_pemenang ?? '') !== '' && (string) $row->id_pemenang === (string) ($row->id_atlet_merah ?? '')) {
                    $scoreRedClass = 'bg-red';
                }
            }
            ?>
            <tr>
                <td><?= esc((string) ($row->nomor_partai ?? '-')) ?></td>
                <td>
                    <?php if ($roundLabel === 'belum ditentukan') : ?>
                        <i>belum ditentukan</i>
                    <?php else : ?>
                        <?= esc($roundLabel) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (empty($row->nama_atlet_biru) && ! empty($row->calon_atlet_biru)) : ?>
                        <i><?= $babak === 'Perebutan Juara Tiga' ? 'Loser from' : 'Winner from' ?> <b>match <?= esc((string) $row->calon_atlet_biru) ?></b></i><br>
                        <small>From arena <?= esc((string) ($row->gelanggang_calon_atlet_biru ?? '-')) ?></small>
                    <?php elseif (empty($row->nama_atlet_biru) && (string) ($row->jenis_kemenangan ?? '') === 'BYE') : ?>
                        <i>BYE</i>
                    <?php elseif (! empty($row->nama_atlet_biru)) : ?>
                        <p class="nama-atlet"><?= esc(ucwords(strtolower((string) $row->nama_atlet_biru))) ?></p>
                        <p class="kontingen">(<?= esc(strtoupper((string) ($row->nama_kontingen_biru ?? '-'))) ?>)</p>
                    <?php else : ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?= esc((string) ($row->nama_kategori_usia ?? '-')) ?><br>
                    <?= esc(ucwords((string) ($row->jenis_kelamin ?? '-'))) ?> - <?= esc((string) ($row->label ?? '-')) ?>
                    <?php if ((string) ($row->jenis_perlombaan ?? '') === 'pemasalan' && ! empty($row->nomor_pool)) : ?>
                        <br>Pool <?= esc((string) $row->nomor_pool) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (empty($row->nama_atlet_merah) && ! empty($row->calon_atlet_merah)) : ?>
                        <i><?= $babak === 'Perebutan Juara Tiga' ? 'Loser from' : 'Winner from' ?> <b>match <?= esc((string) $row->calon_atlet_merah) ?></b></i><br>
                        <small>From arena <?= esc((string) ($row->gelanggang_calon_atlet_merah ?? '-')) ?></small>
                    <?php elseif (empty($row->nama_atlet_merah) && (string) ($row->jenis_kemenangan ?? '') === 'BYE') : ?>
                        <i>BYE</i>
                    <?php elseif (! empty($row->nama_atlet_merah)) : ?>
                        <p class="nama-atlet"><?= esc(ucwords(strtolower((string) $row->nama_atlet_merah))) ?></p>
                        <p class="kontingen">(<?= esc(strtoupper((string) ($row->nama_kontingen_merah ?? '-'))) ?>)</p>
                    <?php else : ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="<?= esc($scoreBlueClass) ?>" style="font-weight:bold; width:8.5%;"><?= $tampilkanSkor ? esc($scoreBlue) : '' ?></td>
                <td class="<?= esc($scoreRedClass) ?>" style="font-weight:bold; width:8.5%;"><?= $tampilkanSkor ? esc($scoreRed) : '' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else : ?>
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

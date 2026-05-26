<?php
$pembayaran = $detail['pembayaran'];
$seniGroups = [
    'tunggal' => 0,
    'ganda' => 0,
    'beregu' => 0,
    'solo kreatif' => 0,
];
foreach (($detail['seni'] ?? []) as $row) {
    $jenis = strtolower(trim((string) ($row->jenis_seni ?? '')));
    if (array_key_exists($jenis, $seniGroups)) {
        $seniGroups[$jenis]++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Nota Pembayaran') ?></title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #111827; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 16px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { color: #4b5563; }
        .meta-table, .item-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .meta-table td { padding: 4px 0; vertical-align: top; }
        .item-table th, .item-table td { border: 1px solid #d1d5db; padding: 8px; }
        .item-table th { background: #f3f4f6; text-align: left; }
        .section-title { font-size: 13px; font-weight: bold; margin: 14px 0 8px; }
        .total-box { margin-top: 16px; text-align: right; }
        .total-label { color: #4b5563; }
        .total-value { font-size: 18px; font-weight: bold; }
        .summary-box { border: 1px solid #d1d5db; padding: 12px; margin-top: 16px; }
        .summary-row { margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title"><?= esc($eventName ?? 'Digital Pencak Silat') ?></div>
        <div class="subtitle">Nota pembayaran bendahara</div>
    </div>

    <table class="meta-table">
        <tr>
            <td width="25%">ID Pembayaran</td>
            <td width="2%">:</td>
            <td>#<?= esc((string) $pembayaran->id_pembayaran) ?></td>
        </tr>
        <tr>
            <td>Tanggal Pembayaran</td>
            <td>:</td>
            <td><?= esc(format_tanggal_indo($pembayaran->tanggal_pembayaran)) ?></td>
        </tr>
        <tr>
            <td>Status</td>
            <td>:</td>
            <td><?= esc(ucfirst((string) $pembayaran->status_pembayaran)) ?></td>
        </tr>
        <tr>
            <td>Kontingen</td>
            <td>:</td>
            <td><?= esc($pembayaran->nama_kontingen ?: '-') ?></td>
        </tr>
        <tr>
            <td>Nomor Invoice</td>
            <td>:</td>
            <td>#<?= esc((string) (1000 + (int) $pembayaran->id_pembayaran)) ?></td>
        </tr>
    </table>

    <div class="section-title">Item Tanding</div>
    <table class="item-table">
        <thead>
            <tr>
                <th>Nama Atlet</th>
                <th>Kategori Usia</th>
                <th>Jenis Kelamin</th>
                <th>Kelas</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($detail['tanding'] === []) : ?>
                <tr>
                    <td colspan="4">Tidak ada item tanding.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($detail['tanding'] as $row) : ?>
                    <tr>
                        <td><?= esc($row->nama_pendaftar) ?></td>
                        <td><?= esc($row->nama_kategori_usia) ?></td>
                        <td><?= esc($row->jenis_kelamin) ?></td>
                        <td><?= esc($row->label) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">Item Seni</div>
    <table class="item-table">
        <thead>
            <tr>
                <th>Nama Anggota</th>
                <th>Kategori Usia</th>
                <th>Jenis Kelamin</th>
                <th>Kategori Seni</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($detail['seni'] === []) : ?>
                <tr>
                    <td colspan="4">Tidak ada item seni.</td>
                </tr>
            <?php else : ?>
                <?php foreach ($detail['seni'] as $row) : ?>
                    <tr>
                        <td><?= esc($row->anggota_kelompok_peserta_seni ?: '-') ?></td>
                        <td><?= esc($row->nama_kategori_usia) ?></td>
                        <td><?= esc($row->jenis_kelamin) ?></td>
                        <td><?= esc($row->jenis_seni) ?> - <?= esc($row->nama_seni) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-label">Total Pembayaran</div>
        <div class="total-value">Rp <?= number_format((int) $pembayaran->total_pembayaran, 0, ',', '.') ?></div>
    </div>

    <div class="summary-box">
        <div class="section-title" style="margin-top:0;">Ringkasan Item</div>
        <div class="summary-row">Jumlah Tanding: <?= count($detail['tanding'] ?? []) ?></div>
        <div class="summary-row">Jumlah Tunggal: <?= $seniGroups['tunggal'] ?></div>
        <div class="summary-row">Jumlah Ganda: <?= $seniGroups['ganda'] ?></div>
        <div class="summary-row">Jumlah Beregu: <?= $seniGroups['beregu'] ?></div>
        <div class="summary-row">Jumlah Solo Kreatif: <?= $seniGroups['solo kreatif'] ?></div>
    </div>
</body>
</html>

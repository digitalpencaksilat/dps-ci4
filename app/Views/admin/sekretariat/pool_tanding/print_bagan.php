<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bagan Tanding</title>
    <style>body{font-family:Arial,sans-serif;margin:24px}.print-header{margin-bottom:16px}.bagan-json{white-space:pre-wrap;border:1px solid #ddd;padding:12px;border-radius:8px}</style>
</head>
<body>
    <div class="print-header"><h2>Bagan Tanding</h2><p><?= esc(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '') . ' Kelas ' . ($row->label ?? '-') . ' Pool ' . ($row->nomor_pool ?? '-')) ?></p></div>
    <?php if (! empty($row->bagan_pertandingan)) : ?>
        <pre class="bagan-json"><?= esc(json_encode(json_decode((string) $row->bagan_pertandingan), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: (string) $row->bagan_pertandingan) ?></pre>
    <?php else : ?>
        <p>Bagan belum tersedia.</p>
    <?php endif; ?>
    <script>window.print();</script>
</body>
</html>

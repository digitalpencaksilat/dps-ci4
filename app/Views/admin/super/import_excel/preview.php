<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$preview = $preview ?? [];
$isValid = ! empty($preview['is_valid']);
$errors = $preview['errors'] ?? [];
$rows = $preview['data_dari_excel'] ?? [];
$token = (string) ($preview['token'] ?? '');
$filename = (string) ($preview['original_filename'] ?? 'unknown.xlsx');
$createdAt = isset($preview['created_at']) ? date('d M Y H:i', strtotime($preview['created_at'])) : '-';
$stats = $preview['stats'] ?? [];

$displayLimit = 200;
$displayRows = array_slice($rows, 0, $displayLimit);
$hasMore = count($rows) > $displayLimit;

$errorsByCategory = [];
foreach ($errors as $e) {
    $errorsByCategory[$e['category']][] = $e;
}
?>

<div class="row g-4">
    <div class="col-12">
        <section class="admin-card border-0 shadow-sm">
            <div class="card-body p-4 d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-start">
                <div>
                    <p class="eyebrow mb-2">Pratinjau</p>
                    <h2 class="h4 section-title mb-2"><?= $isValid ? 'Data valid dan siap diimport' : 'Ditemukan kesalahan pada data' ?></h2>
                    <p class="muted-copy mb-0"><?= $isValid ? 'Silakan konfirmasi import untuk menyimpan data ke database.' : 'Perbaiki file Excel berdasarkan daftar kesalahan, lalu unggah ulang.' ?></p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <form action="<?= base_url('admin/super/import-excel-data/cancel') ?>" method="post" class="m-0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="token" value="<?= esc($token) ?>">
                        <button type="submit" class="btn btn-outline-secondary rounded-pill">Batal & Kembali</button>
                    </form>
                    <?php if ($isValid): ?>
                        <form action="<?= base_url('admin/super/import-excel-data/commit') ?>" method="post" class="m-0" onsubmit="return confirmAdminAction(this, 'Konfirmasi Import?', 'Data dari Excel akan disimpan ke database. Pastikan sudah preview dengan benar.', 'Ya, Import')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="token" value="<?= esc($token) ?>">
                            <button type="submit" class="btn btn-dark rounded-pill">Konfirmasi Import</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= base_url('admin/super/import-excel-data') ?>" class="btn btn-dark rounded-pill">Unggah Ulang</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12">
        <section class="admin-card">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div class="small text-muted">
                        <div><strong><?= esc($filename) ?></strong></div>
                        <div><?= esc($createdAt) ?></div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge text-bg-light border">Baris: <?= (int) ($preview['total_rows_in_excel'] ?? 0) ?></span>
                        <?php if ($isValid): ?>
                            <span class="badge text-bg-success">Tanding: <?= (int) ($stats['tanding'] ?? 0) ?></span>
                            <span class="badge text-bg-success">Tunggal: <?= (int) ($stats['tunggal'] ?? 0) ?></span>
                            <span class="badge text-bg-success">Ganda: <?= (int) ($stats['ganda'] ?? 0) ?></span>
                            <span class="badge text-bg-success">Beregu: <?= (int) ($stats['beregu'] ?? 0) ?></span>
                        <?php else: ?>
                            <span class="badge text-bg-danger">Error: <?= (int) ($preview['error_count'] ?? count($errors)) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php if (! $isValid && $errorsByCategory !== []): ?>
        <div class="col-12 col-xl-5">
            <section class="admin-card">
                <div class="card-body p-4">
                    <h3 class="h6 mb-3">Daftar Kesalahan</h3>
                    <?php foreach ($errorsByCategory as $cat => $items): ?>
                        <div class="mb-3">
                            <div class="fw-semibold mb-2"><?= esc($cat) ?> <span class="badge text-bg-danger"><?= count($items) ?></span></div>
                            <?php foreach ($items as $err): ?>
                                <div class="border rounded-3 p-2 mb-2 small" style="background:#fff5f5;">
                                    <?php if (! empty($err['line'])): ?>
                                        <span class="badge text-bg-light border">Baris <?= (int) $err['line'] ?></span>
                                    <?php endif; ?>
                                    <?= esc($err['message']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    <?php endif; ?>

    <div class="col-12 <?= (! $isValid && $errorsByCategory !== []) ? 'col-xl-7' : '' ?>">
        <section class="admin-card">
            <div class="card-body p-0">
                <div class="p-4 border-bottom">
                    <h3 class="h6 mb-1">Pratinjau Data</h3>
                    <div class="small text-muted"><?= $hasMore ? 'Menampilkan ' . $displayLimit . ' dari ' . count($rows) . ' baris.' : (count($rows) . ' baris.') ?></div>
                </div>
                <div class="table-responsive" style="max-height:60vh;overflow:auto;">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="sticky-top" style="background:#f7f7f7;">
                            <tr>
                                <th>#</th>
                                <?php foreach ([0, 1, 8, 9, 10, 11, 6, 5, 3, 14] as $idx): ?>
                                    <th><?= esc(($columnLabels[$idx]['label'] ?? ('Col ' . $idx))) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($displayRows as $i => $row): ?>
                                <tr>
                                    <td class="text-muted"><?= $i + 2 ?></td>
                                    <?php foreach ([0, 1, 8, 9, 10, 11, 6, 5, 3, 14] as $idx): ?>
                                        <td><?= esc((string) ($row[$idx] ?? '')) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<?= $this->endSection() ?>

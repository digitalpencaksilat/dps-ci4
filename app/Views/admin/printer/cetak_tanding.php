<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?= view('admin/super/_action_toolbar', [
    'eyebrow'     => 'Printer',
    'title'       => 'Cetak Sertifikat Peserta Tanding',
    'description' => 'Cetak sertifikat sebagai peserta (partisipasi) atau sebagai juara untuk peraih medali. Teks dapat disesuaikan sebelum mencetak.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/printer/dashboard'), 'label' => 'Kembali', 'class' => 'btn-outline-secondary'],
    ],
]) ?>

<section class="admin-card">
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table align-middle mb-0 w-100" id="tableCetakTanding">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kontingen</th>
                        <th class="d-none d-md-table-cell">Kategori</th>
                        <th class="d-none d-lg-table-cell">Kelas</th>
                        <th>Medali</th>
                        <th>Nomor</th>
                        <th>Status</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $p) : ?>
                        <?php
                        $kelas = $p->label ? 'Kelas ' . $p->label : '-';
                        $showPool = ($p->jenis_perlombaan ?? '') === 'pemasalan' && ($p->nomor_pool ?? null) !== null && $p->nomor_pool !== '';
                        $kategoriPeserta = strtoupper(trim('PESERTA ' . ($p->nama_kategori_usia ?? '') . ' ' . ucwords((string) ($p->jenis_kelamin ?? '')) . ($p->label ? ' Kelas ' . $p->label : '')));
                        $isJuara = ! empty($p->jenis_medali);
                        $medaliLabel = ['emas' => 'I', 'perak' => 'II', 'perunggu' => 'III'][$p->jenis_medali ?? ''] ?? '';
                        $kategoriJuara = strtoupper(trim('JUARA ' . $medaliLabel . ' TANDING ' . ($p->jenis_kelamin ?? '') . ' ' . ($p->nama_kategori_usia ?? '') . ($p->label ? ' Kelas ' . $p->label : '')));
                        $link = base_url('admin/printer/cetak/tanding/' . $p->id_peserta_tanding);
                        ?>
                        <tr>
                            <td class="fw-semibold text-capitalize"><?= esc($p->nama_pendaftar) ?></td>
                            <td class="text-capitalize"><?= esc($p->nama_kontingen) ?></td>
                            <td class="d-none d-md-table-cell text-capitalize small"><?= esc(($p->nama_kategori_usia ?? '') . ' ' . ($p->jenis_kelamin ?? '')) ?></td>
                            <td class="d-none d-lg-table-cell">
                                <?= esc($kelas) ?>
                                <?php if ($showPool) : ?>
                                    <span class="badge bg-corner-blue ms-1">Pool <?= esc((string) $p->nomor_pool) ?></span>
                                <?php endif; ?>
                            </td>
                            <td data-order="<?= esc($p->jenis_medali ?? '', 'attr') ?>">
                                <?php if ($isJuara) : ?>
                                    <?php
                                    $medaliBadge = [
                                        'emas'     => ['cls' => 'medal-badge emas', 'icon' => '🥇', 'text' => 'Emas'],
                                        'perak'    => ['cls' => 'medal-badge perak', 'icon' => '🥈', 'text' => 'Perak'],
                                        'perunggu' => ['cls' => 'medal-badge perunggu', 'icon' => '🥉', 'text' => 'Perunggu'],
                                    ][$p->jenis_medali];
                                    ?>
                                    <span class="<?= esc($medaliBadge['cls'], 'attr') ?>">
                                        <?= $medaliBadge['icon'] ?> <?= esc($medaliBadge['text']) ?>
                                    </span>
                                <?php else : ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td data-order="<?= esc((string) ($p->nomor_sertifikat ?? ''), 'attr') ?>">
                                <span class="nomor-cell">
                                    <?= $p->nomor_sertifikat ? '<code class="text-success">' . esc($p->nomor_sertifikat) . '</code>' : '<span class="text-muted">—</span>' ?>
                                </span>
                                <?php if (! $p->nomor_sertifikat) : ?>
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-1 btn-generate-nomor"
                                        data-jenis="tanding" data-id="<?= esc((string) $p->id_peserta_tanding, 'attr') ?>"
                                        title="Generate nomor sertifikat" aria-label="Generate nomor sertifikat">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (($p->status_sertifikat ?? '') === 'sudah_dicetak') : ?>
                                    <span class="status-badge success">Dicetak</span>
                                <?php else : ?>
                                    <span class="status-badge neutral">Belum</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-print me-1"></i> Cetak
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                onclick="printSertifikat(this)"
                                                data-link="<?= esc($link, 'attr') ?>"
                                                data-nama="<?= esc(strtoupper($p->nama_pendaftar), 'attr') ?>"
                                                data-kontingen="<?= esc(strtoupper($p->nama_kontingen), 'attr') ?>"
                                                data-sekolah="<?= esc(strtoupper((string) ($p->nama_sekolah ?? '')), 'attr') ?>"
                                                data-nomor="<?= esc((string) ($p->nomor_sertifikat ?? ''), 'attr') ?>"
                                                data-kategori="<?= esc($kategoriPeserta, 'attr') ?>">
                                                Sebagai Peserta
                                            </button>
                                        </li>
                                        <?php if ($isJuara) : ?>
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                onclick="printSertifikat(this)"
                                                data-link="<?= esc($link, 'attr') ?>"
                                                data-nama="<?= esc(strtoupper($p->nama_pendaftar), 'attr') ?>"
                                                data-kontingen="<?= esc(strtoupper($p->nama_kontingen), 'attr') ?>"
                                                data-sekolah="<?= esc(strtoupper((string) ($p->nama_sekolah ?? '')), 'attr') ?>"
                                                data-nomor="<?= esc((string) ($p->nomor_sertifikat ?? ''), 'attr') ?>"
                                                data-kategori="<?= esc($kategoriJuara, 'attr') ?>">
                                                Sebagai Juara <?= esc($medaliLabel) ?>
                                            </button>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= view('admin/printer/_modal_cetak') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.PRINTER_GENERATE_URL = '<?= base_url('admin/printer/generate-nomor-sertifikat') ?>';
    window.CSRF_NAME  = '<?= csrf_token() ?>';
    window.CSRF_HASH  = '<?= csrf_hash() ?>';
</script>
<script src="<?= base_url('assets/js/admin/printer-cetak.js') ?>"></script>
<script>
    initPrinterCetakTable('#tableCetakTanding');
</script>
<?= $this->endSection() ?>

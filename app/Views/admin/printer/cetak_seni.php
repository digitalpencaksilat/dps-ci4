<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?= view('admin/super/_action_toolbar', [
    'eyebrow'     => 'Printer',
    'title'       => 'Cetak Sertifikat Peserta Seni',
    'description' => 'Cetak sertifikat sebagai peserta (partisipasi) atau sebagai juara untuk peraih medali. Teks dapat disesuaikan sebelum mencetak.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/printer/dashboard'), 'label' => 'Kembali', 'class' => 'btn-outline-secondary'],
    ],
]) ?>

<section class="admin-card">
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table align-middle mb-0 w-100" id="tableCetakSeni">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kontingen</th>
                        <th class="d-none d-md-table-cell">Kategori</th>
                        <th class="d-none d-lg-table-cell">Seni</th>
                        <th>Nomor</th>
                        <th>Status</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $p) : ?>
                        <?php
                        $seniLabel = trim((string) ($p->jenis_seni ?? '') . ' - ' . (string) ($p->nama_seni ?? ''), ' -');
                        $kategoriPeserta = strtoupper(trim('PESERTA ' . ($p->nama_kategori_usia ?? '') . ' ' . ucwords((string) ($p->jenis_kelamin ?? '')) . ' SENI ' . ($p->jenis_seni ?? '') . ' ' . ($p->nama_seni ?? '')));
                        $isJuara = ! empty($p->jenis_medali);
                        $medaliLabel = ['emas' => 'I', 'perak' => 'II', 'perunggu' => 'III'][$p->jenis_medali ?? ''] ?? '';
                        $kategoriJuara = strtoupper(trim('JUARA ' . $medaliLabel . ' ' . ($p->jenis_seni ?? '') . ' ' . ($p->nama_seni ?? '') . ' ' . ($p->jenis_kelamin ?? '') . ' ' . ($p->nama_kategori_usia ?? '')));
                        $link = base_url('admin/printer/cetak/seni/' . $p->id_peserta_seni);
                        ?>
                        <tr>
                            <td class="fw-semibold text-capitalize"><?= esc($p->nama_pendaftar) ?></td>
                            <td class="text-capitalize"><?= esc($p->nama_kontingen) ?></td>
                            <td class="d-none d-md-table-cell text-capitalize small"><?= esc(($p->nama_kategori_usia ?? '') . ' ' . ($p->jenis_kelamin ?? '')) ?></td>
                            <td class="d-none d-lg-table-cell text-capitalize small"><?= esc($seniLabel) ?></td>
                            <td><?= $p->nomor_sertifikat ? '<code>' . esc($p->nomor_sertifikat) . '</code>' : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <?php if (($p->status_sertifikat ?? '') === 'sudah_dicetak') : ?>
                                    <span class="badge bg-success">Dicetak</span>
                                <?php else : ?>
                                    <span class="badge bg-secondary">Belum</span>
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

<script>
$(function () {
    $('#tableCetakSeni').DataTable({
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }],
        language: { search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: 'Menampilkan _START_–_END_ dari _TOTAL_', paginate: { next: '›', previous: '‹' } }
    });
});
</script>
<?= $this->endSection() ?>

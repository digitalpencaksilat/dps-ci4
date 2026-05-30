<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Mode Pembuatan Jadwal</p>
            <h2 class="section-title h3 mb-2">Dashboard Pembuatan Jadwal</h2>
            <p class="muted-copy mb-0">Ringkasan awal untuk validasi jadwal tanding, seni pool, dan seni battle sebelum parity fitur lengkap.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <a href="<?= base_url('admin/super/jadwal-tanding') ?>" class="btn btn-danger rounded-pill">Jadwal Tanding</a>
            <a href="<?= base_url('admin/super/jadwal-tanding/penjadwalan-otomatis') ?>" class="btn btn-danger rounded-pill">Otomatis Tanding</a>
            <a href="<?= base_url('admin/super/jadwal-seni') ?>" class="btn btn-outline-secondary rounded-pill">Jadwal Seni</a>
            <a href="<?= base_url('admin/super/jadwal-seni/penjadwalan-otomatis') ?>" class="btn btn-outline-secondary rounded-pill">Otomatis Seni</a>
        </div>
    </div>
</section>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-6">
        <section class="admin-card h-100 border border-danger-subtle">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <p class="eyebrow mb-1">Shortcut Utama</p>
                    <h3 class="h4 section-title mb-1">Penjadwalan Otomatis Tanding</h3>
                    <p class="muted-copy mb-0">Masuk cepat ke flow generate tanding dengan pola parity CI3 yang sudah dimigrasikan di CI4.</p>
                </div>
                <span class="status-badge danger"><i class="fas fa-wand-magic-sparkles"></i></span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('admin/super/jadwal-tanding/penjadwalan-otomatis') ?>" class="btn btn-danger rounded-pill">Buka Otomatis Tanding</a>
                <a href="<?= base_url('admin/super/jadwal-tanding/overview') ?>" class="btn btn-outline-danger rounded-pill">Lihat Overview Tanding</a>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="admin-card h-100 border border-secondary-subtle">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <p class="eyebrow mb-1">Shortcut Utama</p>
                    <h3 class="h4 section-title mb-1">Penjadwalan Otomatis Seni</h3>
                    <p class="muted-copy mb-0">Akses cepat ke generate pool dan battle seni beserta validasi parity yang sedang dimigrasikan ke CI4.</p>
                </div>
                <span class="status-badge success"><i class="fas fa-masks-theater"></i></span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('admin/super/jadwal-seni/penjadwalan-otomatis') ?>" class="btn btn-secondary rounded-pill">Buka Otomatis Seni</a>
                <a href="<?= base_url('admin/super/jadwal-seni/overview') ?>" class="btn btn-outline-secondary rounded-pill">Lihat Overview Seni</a>
            </div>
        </section>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php
    $cards = [
        ['label' => 'Pertandingan Belum Dijadwalkan', 'value' => $count_pertandingan_belum_dijadwalkan ?? 0, 'icon' => 'fa-hand-fist', 'tone' => 'danger'],
        ['label' => 'BYE Terjadwal', 'value' => $count_pertandingan_bye_terjadwal ?? 0, 'icon' => 'fa-triangle-exclamation', 'tone' => 'warning'],
        ['label' => 'Seni Pool Belum Dijadwalkan', 'value' => $count_seni_pool_belum_dijadwalkan ?? 0, 'icon' => 'fa-masks-theater', 'tone' => 'danger'],
        ['label' => 'Seni Battle Belum Dijadwalkan', 'value' => $count_seni_battle_belum_dijadwalkan ?? 0, 'icon' => 'fa-people-arrows', 'tone' => 'danger'],
        ['label' => 'Mismatch Sistem Penampilan', 'value' => $count_mismatch_sistem_penampilan ?? 0, 'icon' => 'fa-code-compare', 'tone' => 'secondary'],
    ];
    ?>
    <?php foreach ($cards as $card) : ?>
        <div class="col-12 col-md-6 col-xl">
            <section class="admin-card h-100">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <p class="eyebrow mb-2"><?= esc($card['label']) ?></p>
                        <h3 class="display-6 fw-bold mb-0"><?= esc((string) $card['value']) ?></h3>
                    </div>
                    <span class="status-badge <?= $card['tone'] === 'danger' && (int) $card['value'] > 0 ? 'danger' : 'success' ?>">
                        <i class="fas <?= esc($card['icon']) ?>"></i>
                    </span>
                </div>
            </section>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <section class="admin-card">
            <p class="eyebrow mb-1">Audit Detail</p>
            <h3 class="h4 section-title mb-3">Daftar Item Bermasalah (preview)</h3>
            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th colspan="4">Pertandingan Belum Dijadwalkan</th>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <th>Kategori</th>
                                    <th>Kelas</th>
                                    <th>Babak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice(($data_pertandingan_belum_dijadwalkan ?? []), 0, 10) as $row) : ?>
                                    <tr>
                                        <td><?= esc((string) ($row->id_pertandingan ?? '-')) ?></td>
                                        <td><?= esc((string) (($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? ''))) ?></td>
                                        <td><?= esc((string) ($row->nama_kelas ?? '-')) ?></td>
                                        <td><?= esc((string) ($row->babak ?? '-')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($data_pertandingan_belum_dijadwalkan)) : ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th colspan="4">Seni Battle Belum Dijadwalkan</th>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <th>Kategori</th>
                                    <th>Seni</th>
                                    <th>Babak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice(($data_battle_seni_belum_dijadwalkan ?? []), 0, 10) as $row) : ?>
                                    <tr>
                                        <td><?= esc((string) ($row->id_battle_seni ?? '-')) ?></td>
                                        <td><?= esc((string) (($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? ''))) ?></td>
                                        <td><?= esc((string) ($row->nama_seni ?? '-')) ?></td>
                                        <td><?= esc((string) ($row->babak ?? '-')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($data_battle_seni_belum_dijadwalkan)) : ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th colspan="6">Mismatch Sistem Penampilan (jadwal seni)</th>
                                </tr>
                                <tr>
                                    <th>Partai</th>
                                    <th>Gelanggang</th>
                                    <th>Pool?</th>
                                    <th>Battle?</th>
                                    <th>Sistem Pool</th>
                                    <th>Sistem Battle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice(($data_penampilan_seni_tidak_sesuai_sistem_penampilan ?? []), 0, 10) as $row) : ?>
                                    <tr>
                                        <td><?= esc((string) ($row->nomor_partai ?? '-')) ?></td>
                                        <td><?= esc((string) ($row->nama_gelanggang ?? '-')) ?></td>
                                        <td><?= ! empty($row->id_penampilan_seni) ? '<span class="status-badge success">Ya</span>' : '<span class="status-badge secondary">Tidak</span>' ?></td>
                                        <td><?= ! empty($row->id_battle_seni) ? '<span class="status-badge danger">Ya</span>' : '<span class="status-badge secondary">Tidak</span>' ?></td>
                                        <td><?= esc((string) ($row->sistem_penampilan_pool ?? '-')) ?></td>
                                        <td><?= esc((string) ($row->sistem_penampilan_battle ?? '-')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($data_penampilan_seni_tidak_sesuai_sistem_penampilan)) : ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Tidak ada mismatch.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <p class="muted-copy mt-3 mb-0">Preview dibatasi 10 baris per tabel. Nanti kita bisa tambah pagination/filter seperti CI3.</p>
        </section>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-5">
        <section class="admin-card h-100">
            <p class="eyebrow mb-1">Arena</p>
            <h3 class="h4 section-title mb-3">Gelanggang Terdaftar</h3>
            <?php if (! empty($data_gelanggang)) : ?>
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Gelanggang</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_gelanggang as $index => $gelanggang) : ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($gelanggang->nama_gelanggang ?? '-') ?></td>
                                    <td><?= esc($gelanggang->keterangan ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="alert alert-warning mb-0">Belum ada data gelanggang.</div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-12 col-xl-7">
        <section class="admin-card h-100">
            <p class="eyebrow mb-1">Ringkasan Jadwal</p>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h3 class="h4 section-title mb-1">Jadwal Tanding dan Seni</h3>
                    <p class="muted-copy mb-0">Akses cepat ke daftar jadwal dan form generate otomatis untuk tanding maupun seni.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= base_url('admin/super/jadwal-tanding/penjadwalan-otomatis') ?>" class="btn btn-outline-danger rounded-pill btn-sm">Buka Otomatis Tanding</a>
                    <a href="<?= base_url('admin/super/jadwal-seni/penjadwalan-otomatis') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">Buka Otomatis Seni</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Gelanggang</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($data_jadwal_tanding ?? []) as $row) : ?>
                            <tr>
                                <td><span class="status-badge danger">Tanding</span></td>
                                <td><?= esc($row->nama_gelanggang ?? '-') ?></td>
                                <td><?= esc($row->tanggal ?? '-') ?></td>
                                <td><?= esc(($row->jam_mulai ?? '-') . ' - ' . ($row->jam_selesai ?? '-')) ?></td>
                                <td><?= esc((string) ($row->jumlah_partai ?? 0)) ?> partai</td>
                            </tr>
                        <?php endforeach; ?>
                        <?php foreach (($data_jadwal_seni ?? []) as $row) : ?>
                            <tr>
                                <td><span class="status-badge success">Seni</span></td>
                                <td><?= esc($row->nama_gelanggang ?? '-') ?></td>
                                <td><?= esc($row->tanggal ?? '-') ?></td>
                                <td><?= esc(($row->jam_mulai ?? '-') . ' - ' . ($row->jam_selesai ?? '-')) ?></td>
                                <td><?= esc((string) ($row->jumlah_penampilan ?? 0)) ?> penampilan</td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data_jadwal_tanding) && empty($data_jadwal_seni)) : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada jadwal tanding atau seni.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="operasi-basis-data-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="mb-1">Operasi Basis Data</h2>
            <p class="muted-copy mb-0">Migrasi CI4 dengan parity fungsi utama dari halaman CI3, lalu dikembangkan dengan tampilan yang lebih rapi dan aman.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('admin/super/operasi-basis-data/hapus-data-kosong') ?>" class="btn btn-outline-danger">Hapus Data Kosong</a>
            <a href="<?= base_url('admin/super/operasi-basis-data/hapus-peserta-per-kategori-usia') ?>" class="btn btn-outline-danger">Hapus Peserta per Kategori Usia</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php foreach (($stats ?? []) as $table => $total) : ?>
            <div class="col-6 col-lg-3">
                <div class="admin-card h-100">
                    <div class="card-body">
                        <div class="muted-copy small text-uppercase"><?= esc((string) $table) ?></div>
                        <div class="display-6 mb-0"><?= esc((string) $total) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <h5 class="mb-0 text-dark">Pencadangan Basis Data</h5>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-primary-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2">Backup Database</h6>
                        <p class="muted-copy mb-0">Mencadangkan seluruh struktur dan data utama basis data pertandingan sebelum operasi besar dijalankan.</p>
                    </div>
                    <form action="<?= base_url('admin/super/operasi-basis-data/backup-database') ?>" method="post" class="mt-auto">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-primary w-100" onclick="return confirm('Database akan segera diunduh. Lanjutkan?')">Cadangkan Basis Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <h5 class="mb-0 text-dark">Operasi Penghapusan Atlet</h5>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-danger-subtle bg-danger-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-danger">Hapus Pool Seni Kosong</h6>
                        <p class="mb-0">Menghapus seluruh pool seni yang tidak memiliki kelompok peserta.</p>
                    </div>
                    <form action="<?= base_url('admin/super/operasi-basis-data/hapus-pool-seni-kosong') ?>" method="post" class="mt-auto">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Pool seni kosong akan dihapus. Lanjutkan?')">Hapus Pool Kosong</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-danger-subtle bg-danger-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-danger">Hapus Atlet Belum Lunas</h6>
                        <p class="mb-0">Menghapus peserta tanding dan kelompok peserta seni yang terhubung ke pembayaran berstatus <code>belum_lunas</code>, mengikuti intent modul CI3.</p>
                    </div>
                    <form action="<?= base_url('admin/super/operasi-basis-data/hapus-atlet-belum-lunas') ?>" method="post" class="mt-auto">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Atlet belum lunas akan segera dihapus. Lanjutkan?')">Hapus Atlet Belum Lunas</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-danger-subtle bg-danger-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-danger">Hapus Data dari Excel</h6>
                        <p class="mb-0">Menghapus kontingen yang diimpor dari excel beserta data turunannya melalui constraint basis data.</p>
                    </div>
                    <form action="<?= base_url('admin/super/operasi-basis-data/hapus-data-dari-excel') ?>" method="post" class="mt-auto">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Seluruh data dari excel akan dihapus. Lanjutkan?')">Hapus Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <h5 class="mb-0 text-dark">Operasi Lainnya</h5>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-success-subtle bg-success-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-success-emphasis">Perbaiki Kelas Tanpa Pool</h6>
                        <p class="mb-0">Membuat pool baru pada kelas atau sub kategori yang belum memiliki pool dan menambahkan pool baru bila kapasitas tanding sudah penuh.</p>
                    </div>
                    <form action="<?= base_url('admin/super/operasi-basis-data/buat-pool-baru') ?>" method="post" class="mt-auto">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Buat pool baru untuk kategori yang membutuhkan?')">Buat Pool</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-info-subtle" style="background: linear-gradient(145deg, #d9f3ff 0%, #bce8ff 100%);">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-info-emphasis">Buat Kategori untuk Partai Tambahan</h6>
                        <p class="mb-0">Menambahkan pool berketerangan Partai Tambahan untuk keperluan pertandingan tambahan pada kelas tanding.</p>
                    </div>
                    <form action="<?= base_url('admin/super/operasi-basis-data/buat-kategori-partai-tambahan') ?>" method="post" class="mt-auto">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-info w-100" onclick="return confirm('Buat kategori/pool untuk partai tambahan?')">Buat Kategori Tambahan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-danger-subtle bg-danger-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-danger">Sinkronisasi Basis Data</h6>
                        <p class="mb-0">Membandingkan struktur database aktif terhadap file referensi `public/db/db_structure_dps.sql` dalam mode simulasi terlebih dahulu.</p>
                    </div>
                    <a href="<?= base_url('utilities/db-sync') ?>" class="btn btn-danger w-100 mt-auto">Sinkronkan Sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <h5 class="mb-0 text-dark">Penghapusan Masal</h5>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-danger-subtle bg-danger-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-danger">Reset Database</h6>
                        <p class="mb-0">Mengosongkan tabel data operasional (kecuali master tertentu) dan mereset AUTO_INCREMENT. Sangat berisiko.</p>
                    </div>
                    <form action="<?= base_url('admin/super/operasi-basis-data/reset-database') ?>" method="post" class="mt-auto" onsubmit="return confirmResetDatabase(this)">
                        <?= csrf_field() ?>
                        <input type="hidden" name="confirm" value="RESET DATABASE">
                        <button type="submit" class="btn btn-danger w-100">Reset Database</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-danger-subtle bg-danger-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-danger">Reset Jadwal</h6>
                        <p class="mb-0">Menghapus seluruh penjadwalan tanding dan seni beserta detail jadwalnya.</p>
                    </div>
                    <form action="<?= base_url('admin/super/operasi-basis-data/reset-seluruh-jadwal') ?>" method="post" class="mt-auto" onsubmit="return confirmResetJadwal(this)">
                        <?= csrf_field() ?>
                        <input type="hidden" name="confirm" value="RESET JADWAL">
                        <button type="submit" class="btn btn-danger w-100">Reset Jadwal</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-danger-subtle bg-danger-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-danger">Hapus Data Kosong</h6>
                        <p class="mb-2">Masuk ke halaman khusus untuk membersihkan kontingen tanpa pendaftar dan pendaftar tanpa peserta.</p>
                        <div class="small text-muted">Preview saat ini: kontingen kosong <strong><?= esc((string) (($emptyDataPreview['kontingen_kosong'] ?? 0))) ?></strong>, pendaftar kosong <strong><?= esc((string) (($emptyDataPreview['pendaftar_kosong'] ?? 0))) ?></strong>.</div>
                    </div>
                    <a href="<?= base_url('admin/super/operasi-basis-data/hapus-data-kosong') ?>" class="btn btn-danger w-100 mt-auto">Buka Halaman Hapus Data Kosong</a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="admin-card h-100 border-danger-subtle bg-danger-subtle">
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <h6 class="card-title mb-2 text-danger">Hapus Peserta Per Kategori Usia</h6>
                        <p class="mb-0">Masuk ke halaman khusus untuk memilih peserta tanding/seni dan kategori usia putra/putri yang akan dihapus.</p>
                    </div>
                    <a href="<?= base_url('admin/super/operasi-basis-data/hapus-peserta-per-kategori-usia') ?>" class="btn btn-danger w-100 mt-auto">Buka Halaman Hapus Peserta</a>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body px-0 pb-0">
            <div class="px-3 px-md-4 pt-3 pt-md-4">
                <h6 class="mb-1">Pemeriksaan Referensi Jadwal</h6>
                <p class="muted-copy small mb-0">Ringkasan cepat untuk membantu verifikasi sebelum operasi destruktif dijalankan.</p>
            </div>
            <div class="admin-table-wrap mt-3">
                <div class="table-shell admin-table-scroller">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Pemeriksaan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($checks ?? []) as $check) : ?>
                                <?php $total = (int) ($check['total'] ?? 0); ?>
                                <tr>
                                    <td><?= esc((string) ($check['label'] ?? '-')) ?></td>
                                    <td><?= esc((string) $total) ?></td>
                                    <td><span class="badge <?= $total === 0 ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $total === 0 ? 'Aman' : 'Perlu dicek' ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmResetJadwal(form) {
    const keyword = window.prompt("Seluruh penjadwalan akan dihapus. Ketik 'RESET JADWAL' untuk melanjutkan:");
    if (keyword !== 'RESET JADWAL') {
        window.alert('Kata kunci tidak sesuai.');
        return false;
    }
    return window.confirm('Yakin reset seluruh jadwal? Aksi ini tidak bisa dibatalkan.');
}

function confirmResetDatabase(form) {
    const keyword = window.prompt("Seluruh data operasional akan dihapus. Ketik 'RESET DATABASE' untuk melanjutkan:");
    if (keyword !== 'RESET DATABASE') {
        window.alert('Kata kunci tidak sesuai.');
        return false;
    }
    return window.confirm('Yakin reset database? Aksi ini sangat berisiko dan tidak bisa dibatalkan.');
}
</script>
<?= $this->endSection() ?>

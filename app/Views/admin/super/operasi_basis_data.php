<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="operasi-basis-data-page">
    <div class="row g-4 operasi-db-mode-grid align-items-stretch">
        <div class="col-12">
            <section class="operasi-db-section">
                <p class="eyebrow mb-2">Basis Data</p>
                <h3 class="h4 section-title mb-0">Pencadangan</h3>
            </section>
        </div>
        <div class="col-12 col-md-6 col-xl-4" id="operasi-db-backup">
            <div class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-backup">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-database"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Backup</p>
                        <h3 class="h3 section-title mb-3">Backup Database</h3>
                        <p class="muted-copy mb-0">Mencadangkan seluruh struktur dan data utama basis data pertandingan sebelum operasi besar dijalankan.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <form action="<?= base_url('admin/super/operasi-basis-data/backup-database') ?>" method="post" class="w-100">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-danger w-100 rounded-pill" onclick="return confirm('Database akan segera diunduh. Lanjutkan?')">Jalankan Backup</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div class="col-12">
            <section class="operasi-db-section mt-2">
                <p class="eyebrow mb-2">Atlet</p>
                <h3 class="h4 section-title mb-0">Operasi Penghapusan</h3>
            </section>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-danger">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-trash"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Penghapusan</p>
                        <h3 class="h3 section-title mb-3">Hapus Pool Seni Kosong</h3>
                        <p class="muted-copy mb-0">Menghapus seluruh pool seni yang tidak memiliki kelompok peserta.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <form action="<?= base_url('admin/super/operasi-basis-data/hapus-pool-seni-kosong') ?>" method="post" class="w-100">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger w-100 rounded-pill" onclick="return confirm('Pool seni kosong akan dihapus. Lanjutkan?')">Hapus Pool Kosong</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-danger">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-user-slash"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Penghapusan</p>
                        <h3 class="h3 section-title mb-3">Hapus Atlet Belum Lunas</h3>
                        <p class="muted-copy mb-0">Menghapus peserta tanding dan kelompok peserta seni yang terhubung ke pembayaran berstatus <code>belum_lunas</code>, mengikuti intent modul CI3.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <form action="<?= base_url('admin/super/operasi-basis-data/hapus-atlet-belum-lunas') ?>" method="post" class="w-100">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger w-100 rounded-pill" onclick="return confirm('Atlet belum lunas akan segera dihapus. Lanjutkan?')">Hapus Atlet Belum Lunas</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-danger">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Penghapusan</p>
                        <h3 class="h3 section-title mb-3">Hapus Data dari Excel</h3>
                        <p class="muted-copy mb-0">Menghapus kontingen yang diimpor dari excel beserta data turunannya melalui constraint basis data.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <form action="<?= base_url('admin/super/operasi-basis-data/hapus-data-dari-excel') ?>" method="post" class="w-100">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger w-100 rounded-pill" onclick="return confirm('Seluruh data dari excel akan dihapus. Lanjutkan?')">Hapus Data</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div class="col-12">
            <section class="operasi-db-section mt-2">
                <p class="eyebrow mb-2">Pemeliharaan</p>
                <h3 class="h4 section-title mb-0">Operasi Lainnya</h3>
            </section>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-success">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-sitemap"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Perbaikan</p>
                        <h3 class="h3 section-title mb-3">Perbaiki Kelas Tanpa Pool</h3>
                        <p class="muted-copy mb-0">Membuat pool baru pada kelas atau sub kategori yang belum memiliki pool dan menambahkan pool baru bila kapasitas tanding sudah penuh.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <form action="<?= base_url('admin/super/operasi-basis-data/buat-pool-baru') ?>" method="post" class="w-100">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success w-100 rounded-pill" onclick="return confirm('Buat pool baru untuk kategori yang membutuhkan?')">Buat Pool</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-info">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Tambahan</p>
                        <h3 class="h3 section-title mb-3">Buat Kategori untuk Partai Tambahan</h3>
                        <p class="muted-copy mb-0">Menambahkan pool berketerangan Partai Tambahan untuk keperluan pertandingan tambahan pada kelas tanding.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <form action="<?= base_url('admin/super/operasi-basis-data/buat-kategori-partai-tambahan') ?>" method="post" class="w-100">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-info w-100 rounded-pill" onclick="return confirm('Buat kategori/pool untuk partai tambahan?')">Buat Kategori Tambahan</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <a href="<?= base_url('utilities/db-sync') ?>" class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-danger">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-rotate"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Sinkronisasi</p>
                        <h3 class="h3 section-title mb-3">Sinkronisasi Basis Data</h3>
                        <p class="muted-copy mb-0">Membandingkan struktur database aktif terhadap file referensi <code>public/db/db_structure_dps.sql</code> dalam mode simulasi terlebih dahulu.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <span>Sinkronkan Sekarang</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </section>
            </a>
        </div>

        <div class="col-12">
            <section class="operasi-db-section mt-2">
                <p class="eyebrow mb-2">Berisiko</p>
                <h3 class="h4 section-title mb-0">Penghapusan Masal</h3>
            </section>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-danger">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-bomb"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Reset</p>
                        <h3 class="h3 section-title mb-3">Reset Database</h3>
                        <p class="muted-copy mb-0">Mengosongkan tabel data operasional (kecuali master tertentu) dan mereset AUTO_INCREMENT. Sangat berisiko.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <form action="<?= base_url('admin/super/operasi-basis-data/reset-database') ?>" method="post" class="w-100" onsubmit="return confirmResetDatabase(this)">
                            <?= csrf_field() ?>
                            <input type="hidden" name="confirm" value="RESET DATABASE">
                            <button type="submit" class="btn btn-danger w-100 rounded-pill">Reset Database</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-danger">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-calendar-xmark"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Reset</p>
                        <h3 class="h3 section-title mb-3">Reset Jadwal</h3>
                        <p class="muted-copy mb-0">Menghapus seluruh penjadwalan tanding dan seni beserta detail jadwalnya.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <form action="<?= base_url('admin/super/operasi-basis-data/reset-seluruh-jadwal') ?>" method="post" class="w-100" onsubmit="return confirmResetJadwal(this)">
                            <?= csrf_field() ?>
                            <input type="hidden" name="confirm" value="RESET JADWAL">
                            <button type="submit" class="btn btn-danger w-100 rounded-pill">Reset Jadwal</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <a href="<?= base_url('admin/super/operasi-basis-data/hapus-data-kosong') ?>" class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-danger">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-broom"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Penghapusan</p>
                        <h3 class="h3 section-title mb-3">Hapus Data Kosong</h3>
                        <p class="muted-copy mb-0">Masuk ke halaman khusus untuk membersihkan kontingen tanpa pendaftar dan pendaftar tanpa peserta. Preview: kontingen kosong <strong><?= esc((string) (($emptyDataPreview['kontingen_kosong'] ?? 0))) ?></strong>, pendaftar kosong <strong><?= esc((string) (($emptyDataPreview['pendaftar_kosong'] ?? 0))) ?></strong>.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <span>Buka Halaman</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </section>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <a href="<?= base_url('admin/super/operasi-basis-data/hapus-peserta-per-kategori-usia') ?>" class="operasi-db-mode-card text-decoration-none text-reset d-flex flex-column h-100">
                <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner operasi-db-card-inner operasi-db-card-danger">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                        <div class="super-mode-icon operasi-db-card-icon">
                            <i class="fas fa-user-minus"></i>
                        </div>
                    </div>
                    <div class="super-mode-copy">
                        <p class="eyebrow mb-2">Penghapusan</p>
                        <h3 class="h3 section-title mb-3">Hapus Peserta Per Kategori Usia</h3>
                        <p class="muted-copy mb-0">Masuk ke halaman khusus untuk memilih peserta tanding/seni dan kategori usia putra/putri yang akan dihapus.</p>
                    </div>
                    <div class="super-mode-link mt-4">
                        <span>Buka Halaman</span>
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </section>
            </a>
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

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$kontingen = $detail['kontingen'];
$formatTanggal = static function (?string $date): string {
    if (empty($date)) {
        return '-';
    }

    try {
        return (new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE))->format(new DateTimeImmutable($date));
    } catch (Throwable) {
        return $date;
    }
};
$paymentBadge = static function (?string $status): string {
    if ($status === 'lunas') {
        return '<span class="badge text-bg-success">Lunas</span>';
    }
    if ($status === 'menunggu') {
        return '<span class="badge text-bg-warning">Menunggu Konfirmasi</span>';
    }

    return '<span class="badge text-bg-danger">Belum Lunas</span>';
};
$formatGender = static fn (?string $gender): string => $gender !== null && $gender !== '' ? ucwords($gender) : '-';
$jumlahTanding = (int) ($kontingen->jumlah_peserta_tanding ?? 0);
$jumlahTunggal = (int) ($kontingen->jumlah_kelompok_peserta_seni_tunggal ?? 0);
$jumlahGanda = (int) ($kontingen->jumlah_kelompok_peserta_seni_ganda ?? 0);
$jumlahBeregu = (int) ($kontingen->jumlah_kelompok_peserta_seni_beregu ?? 0);
$jumlahSoloKreatif = (int) ($kontingen->jumlah_kelompok_peserta_seni_solo_kreatif ?? 0);
$totalPeserta = $jumlahTanding + $jumlahTunggal + $jumlahGanda + $jumlahBeregu + $jumlahSoloKreatif;
$totalIdCard = $jumlahTanding + $jumlahTunggal + ($jumlahGanda * 2) + ($jumlahBeregu * 3) + $jumlahSoloKreatif;
?>

<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Detail Kontingen</p>
            <h3 class="section-title h4 mb-1"><?= esc($kontingen->nama_kontingen) ?></h3>
            <p class="muted-copy mb-0">Penanggung jawab: <?= esc((string) ($kontingen->nama_penanggungjawab ?? '-')) ?> | Email: <?= esc((string) ($kontingen->email_kontingen ?? '-')) ?></p>
            <p class="muted-copy mb-0 mt-1">Telepon: <?= esc((string) ($kontingen->nomor_telepon_penanggungjawab ?? '-')) ?> | Provinsi: <?= esc((string) ($kontingen->provinsi ?? '-')) ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <span class="status-badge neutral">Pendaftar: <?= esc((string) ((int) ($kontingen->jumlah_pendaftar ?? 0))) ?></span>
            <span class="status-badge neutral">Tanding: <?= esc((string) $jumlahTanding) ?></span>
            <span class="status-badge neutral">Seni: <?= esc((string) ((int) ($kontingen->jumlah_kelompok_peserta_seni ?? 0))) ?></span>
            <span class="status-badge neutral">Total Peserta: <?= esc((string) $totalPeserta) ?></span>
            <span class="status-badge neutral">ID Card: <?= esc((string) $totalIdCard) ?> lembar</span>
            <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#editKontingenModal">Edit Data</button>
        </div>
    </div>
</section>

<section class="row g-3 mb-4">
    <div class="col-6 col-lg-2"><div class="placeholder-stat h-100"><div class="small muted-copy">Tanding</div><div class="h4 mb-0"><?= esc((string) $jumlahTanding) ?></div></div></div>
    <div class="col-6 col-lg-2"><div class="placeholder-stat h-100"><div class="small muted-copy">Tunggal</div><div class="h4 mb-0"><?= esc((string) $jumlahTunggal) ?></div></div></div>
    <div class="col-6 col-lg-2"><div class="placeholder-stat h-100"><div class="small muted-copy">Ganda</div><div class="h4 mb-0"><?= esc((string) $jumlahGanda) ?></div></div></div>
    <div class="col-6 col-lg-2"><div class="placeholder-stat h-100"><div class="small muted-copy">Beregu</div><div class="h4 mb-0"><?= esc((string) $jumlahBeregu) ?></div></div></div>
    <div class="col-6 col-lg-2"><div class="placeholder-stat h-100"><div class="small muted-copy">Solo Kreatif</div><div class="h4 mb-0"><?= esc((string) $jumlahSoloKreatif) ?></div></div></div>
    <div class="col-6 col-lg-2"><div class="placeholder-stat h-100"><div class="small muted-copy">Official</div><div class="h4 mb-0"><?= esc((string) ((int) ($kontingen->jumlah_official ?? 0))) ?></div></div></div>
</section>

<section class="admin-card mb-4">
    <ul class="nav nav-pills gap-2 mb-4" id="kontingenTabs" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active rounded-pill" data-bs-toggle="pill" data-bs-target="#tabPendaftar" type="button" role="tab">Data Atlet</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link rounded-pill" data-bs-toggle="pill" data-bs-target="#tabTanding" type="button" role="tab">Peserta Tanding</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link rounded-pill" data-bs-toggle="pill" data-bs-target="#tabSeni" type="button" role="tab">Peserta Seni</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link rounded-pill" data-bs-toggle="pill" data-bs-target="#tabOfficial" type="button" role="tab">Official</button></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tabPendaftar" role="tabpanel">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h4 class="section-title h5 mb-1">Data Atlet</h4>
                    <p class="muted-copy mb-0">Biodata atlet kontingen.</p>
                </div>
                <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createPendaftarModal">Tambah Peserta</button>
            </div>
            <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable-export align-middle mb-0" data-export-config='{"excel":{"numericTextColumns":[8,9]}}'>
                    <thead>
                        <tr>
                            <th>Nama</th><th>JK</th><th>Tanggal Lahir</th><th>Umur</th><th>BB</th><th>TB</th><th>Sekolah</th><th>Provinsi</th><th>NIK</th><th>No KK</th><th class="text-end no-export">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($detail['pendaftar'] ?? []) as $row) : ?>
                            <tr>
                                <td class="fw-semibold text-capitalize"><?= esc($row->nama_pendaftar) ?></td>
                                <td><?= esc(ucwords((string) $row->jenis_kelamin)) ?></td>
                                <td><?= esc($formatTanggal($row->tanggal_lahir ?? null)) ?></td>
                                <td><?= esc((string) ($row->umur_pendaftar ?? '-')) ?></td>
                                <td><?= esc((string) ($row->berat_badan ?? '-')) ?></td>
                                <td><?= esc((string) ($row->tinggi_badan ?? '-')) ?></td>
                                <td><?= esc((string) ($row->nama_sekolah ?: '-')) ?></td>
                                <td><?= esc((string) ($kontingen->provinsi ?: '-')) ?></td>
                                <td><?= esc((string) ($row->nomor_induk_kependudukan ?: '-')) ?></td>
                                <td><?= esc((string) ($row->nomor_kartu_keluarga ?: '-')) ?></td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#editPendaftarModal<?= esc((string) $row->id_pendaftar) ?>">Edit</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="tab-pane fade" id="tabTanding" role="tabpanel">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h4 class="section-title h5 mb-1">Peserta Tanding</h4>
                    <p class="muted-copy mb-0">Tampilan disesuaikan dengan tabel peserta tanding CI3.</p>
                </div>
                <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createPesertaTandingModal">Tambah Peserta Tanding</button>
            </div>
            <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable-export align-middle mb-0">
                    <thead><tr><th>Nama</th><th>JK</th><th>BB</th><th>TB</th><th>Kategori</th><th>Kelas</th><th>Pembayaran</th><th>Keterangan</th><th class="text-end no-export">Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach (($detail['pesertaTanding'] ?? []) as $row) : ?>
                            <tr>
                                <td class="fw-semibold text-capitalize"><?= esc($row->nama_pendaftar) ?></td>
                                <td><?= esc($formatGender($row->jenis_kelamin ?? null)) ?></td>
                                <td><?= esc((string) ($row->berat_badan ?? '-')) ?></td>
                                <td><?= esc((string) ($row->tinggi_badan ?? '-')) ?></td>
                                <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                                <td><?= esc((string) ($row->label ?? '-')) ?></td>
                                <td><?= $paymentBadge($row->status_pembayaran ?? null) ?></td>
                                <td><?= esc((string) ($row->keterangan ?? '-')) ?></td>
                                <td class="text-end"><a href="<?= base_url('admin/sekretariat/peserta-tanding/' . $row->id_peserta_tanding) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="tab-pane fade" id="tabSeni" role="tabpanel">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h4 class="section-title h5 mb-1">Peserta Seni</h4>
                    <p class="muted-copy mb-0">Mengikuti tabel kelompok peserta seni CI3.</p>
                </div>
                <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createKelompokSeniModal">Tambah Kelompok Seni</button>
            </div>
            <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable-export align-middle mb-0">
                    <thead><tr><th>Nama/Anggota</th><th>JK</th><th>Kategori</th><th>Jurus</th><th>Pool</th><th>No Undi</th><th>Pembayaran</th><th class="text-end no-export">Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach (($detail['kelompokSeni'] ?? []) as $row) : ?>
                            <tr>
                                <td><a href="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni) ?>" class="fw-semibold text-danger text-decoration-none text-capitalize"><?= $row->anggota_kelompok_peserta_seni ?: '-' ?></a></td>
                                <td><?= esc($formatGender($row->jenis_kelamin ?? null)) ?></td>
                                <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                                <td><?= esc(trim(((string) ($row->jenis_seni ?? '')) . ' - ' . ((string) ($row->nama_seni ?? '')))) ?></td>
                                <td><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                                <td><?= ($row->sistem_penampilan ?? '') === 'pool' ? esc((string) ($row->nomor_undi ?? '-')) : '<span class="muted-copy small">Tidak ada undian</span>' ?></td>
                                <td><?= $paymentBadge($row->status_pembayaran ?? null) ?></td>
                                <td class="text-end"><a href="<?= base_url('admin/sekretariat/kelompok-seni/' . $row->id_kelompok_peserta_seni) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div></div>
        </div>

        <div class="tab-pane fade" id="tabOfficial" role="tabpanel">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h4 class="section-title h5 mb-1">Official</h4>
                    <p class="muted-copy mb-0">Daftar official kontingen mengikuti tab detail CI3.</p>
                </div>
            </div>
            <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable-export align-middle mb-0">
                    <thead><tr><th>Nama</th><th>Kontingen</th><th>Nomor Telepon</th></tr></thead>
                    <tbody>
                        <?php foreach (($detail['official'] ?? []) as $row) : ?>
                            <tr>
                                <td class="fw-semibold text-capitalize"><?= esc((string) ($row->nama_official ?? '-')) ?></td>
                                <td><?= esc((string) ($row->nama_kontingen ?? $kontingen->nama_kontingen ?? '-')) ?></td>
                                <td><?= esc((string) ($row->nomor_telepon ?? '-')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-12 col-xl-8"><div class="admin-card h-100"><h4 class="section-title h5 mb-3">Kontrol Kontingen</h4><div class="d-flex flex-wrap gap-2"><button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">Reset Password</button><form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $kontingen->id_kontingen . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Hapus kontingen?', 'Data kontingen akan dihapus dari sistem.', 'Hapus')"><?= csrf_field() ?><button type="submit" class="btn btn-outline-secondary rounded-pill px-4">Hapus Kontingen</button></form></div></div></div>
    <div class="col-12 col-xl-4"><div class="admin-card h-100"><div class="small muted-copy mb-1">Status Data</div><div class="fw-semibold"><?= esc((string) ($kontingen->status_data ?? '-')) ?></div></div></div>
</section>

<div class="modal fade" id="editKontingenModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $kontingen->id_kontingen . '/update') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Kontingen</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><?= view('admin/sekretariat/kontingen/_form', ['mode' => 'edit', 'kontingen' => $kontingen]) ?></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Perubahan</button></div></form></div></div>
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $kontingen->id_kontingen . '/reset-password') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Reset Password</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><label class="form-label fw-semibold">Password Baru</label><input type="password" name="password" class="form-control rounded-4" minlength="6" required></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-admin-brand rounded-pill">Reset Password</button></div></form></div></div>

<div class="modal fade" id="createPendaftarModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $kontingen->id_kontingen . '/pendaftar') ?>" class="modal-content" enctype="multipart/form-data"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Peserta</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><?= view('admin/sekretariat/pendaftar/_form', ['mode' => 'create', 'formId' => 'create-pendaftar', 'arsipSlots' => $arsipSlots ?? [], 'arsipExisting' => []]) ?></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Peserta</button></div></form></div></div>
<div class="modal fade" id="createPesertaTandingModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $kontingen->id_kontingen . '/peserta-tanding') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Peserta Tanding</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><?= view('admin/sekretariat/peserta_tanding/_form', ['mode' => 'create', 'pendaftarOptions' => $detail['pendaftarTandingOptions'] ?? [], 'kompetisiOptions' => $detail['kompetisiTandingOptions'] ?? []]) ?></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-admin-brand rounded-pill">Simpan</button></div></form></div></div>
<div class="modal fade" id="createKelompokSeniModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $kontingen->id_kontingen . '/kelompok-seni') ?>" class="modal-content"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Kelompok Seni</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><?= view('admin/sekretariat/kelompok_seni/_form', ['mode' => 'create', 'hideKontingen' => true, 'idKontingen' => (int) $kontingen->id_kontingen, 'kompetisiOptions' => $detail['kompetisiSeniOptions'] ?? [], 'pendaftarOptions' => $detail['pendaftarSeniOptions'] ?? []]) ?></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Kelompok</button></div></form></div></div>

<?php foreach (($detail['pendaftar'] ?? []) as $pendaftar) : ?>
    <div class="modal fade" id="editPendaftarModal<?= esc((string) $pendaftar->id_pendaftar) ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content"><form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $kontingen->id_kontingen . '/pendaftar/' . $pendaftar->id_pendaftar . '/update') ?>" enctype="multipart/form-data"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Edit Peserta</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><?= view('admin/sekretariat/pendaftar/_form', ['mode' => 'edit', 'formId' => 'edit-pendaftar-' . $pendaftar->id_pendaftar, 'pendaftar' => $pendaftar, 'arsipSlots' => $arsipSlots ?? [], 'arsipExisting' => $arsipByPendaftar[$pendaftar->id_pendaftar] ?? []]) ?></div><div class="modal-footer justify-content-between"><button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Perubahan</button></div></form><form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $kontingen->id_kontingen . '/pendaftar/' . $pendaftar->id_pendaftar . '/delete') ?>" class="px-3 pb-3" onsubmit="return confirmAdminAction(this, 'Hapus peserta?', 'Data peserta dan kategori terkait akan ikut dihapus.', 'Hapus')"><?= csrf_field() ?><button type="submit" class="btn btn-outline-danger rounded-pill">Hapus Peserta</button></form></div></div></div>
<?php endforeach; ?>
<?= $this->endSection() ?>

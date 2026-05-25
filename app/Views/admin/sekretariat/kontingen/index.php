<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Sekretariat</p>
            <h3 class="section-title h4 mb-0">Data kontingen</h3>
            <p class="muted-copy mb-0 mt-2">Pantau identitas kontingen dan rekap peserta dari satu layar.</p>
        </div>
        <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createKontingenModal">
            <i class="fas fa-plus me-2"></i>Tambah Kontingen
        </button>
    </div>

    <?php if (($kontingenRows ?? []) === []) : ?>
        <div class="placeholder-stat">
            <h4 class="h5 mb-2">Belum ada kontingen</h4>
            <p class="muted-copy mb-0">Data kontingen belum tersedia.</p>
        </div>
    <?php else : ?>
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Provinsi</th>
                            <th class="text-end">Tanding</th>
                            <th class="text-end">Tunggal</th>
                            <th class="text-end">Ganda</th>
                            <th class="text-end">Beregu</th>
                            <th class="text-end">Solo Kreatif</th>
                            <th class="text-end">Total Peserta</th>
                            <th class="text-end">ID Card</th>
                            <th>Jenis Pendaftaran</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kontingenRows as $row) : ?>
                            <?php
                            $jumlahTanding = (int) ($row->jumlah_peserta_tanding ?? 0);
                            $jumlahTunggal = (int) ($row->jumlah_kelompok_peserta_seni_tunggal ?? 0);
                            $jumlahGanda = (int) ($row->jumlah_kelompok_peserta_seni_ganda ?? 0);
                            $jumlahBeregu = (int) ($row->jumlah_kelompok_peserta_seni_beregu ?? 0);
                            $jumlahSoloKreatif = (int) ($row->jumlah_kelompok_peserta_seni_solo_kreatif ?? 0);
                            $totalPeserta = $jumlahTanding + $jumlahTunggal + $jumlahGanda + $jumlahBeregu + $jumlahSoloKreatif;
                            $totalIdCard = $jumlahTanding + $jumlahTunggal + ($jumlahGanda * 2) + ($jumlahBeregu * 3) + $jumlahSoloKreatif;
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('admin/sekretariat/kontingen/' . $row->id_kontingen) ?>" class="fw-semibold text-decoration-none text-capitalize"><?= esc($row->nama_kontingen ?: '-') ?></a>
                                </td>
                                <td><?= esc((string) ($row->provinsi ?? '-')) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahTanding) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahTunggal) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahGanda) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahBeregu) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahSoloKreatif) ?></td>
                                <td class="text-end"><?= esc((string) $totalPeserta) ?></td>
                                <td class="text-end"><?= esc((string) $totalIdCard) ?> lembar</td>
                                <td><?= esc((string) ($row->jenis_pendaftaran ?? '-')) ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('admin/sekretariat/kontingen/' . $row->id_kontingen) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>

<div class="modal fade" id="createKontingenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form method="post" action="<?= base_url('admin/sekretariat/kontingen') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kontingen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <?= view('admin/sekretariat/kontingen/_form', ['mode' => 'create']) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-admin-brand rounded-pill">Simpan Kontingen</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

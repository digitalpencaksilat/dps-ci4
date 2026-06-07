<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Sekretariat</p>
            <h3 class="section-title h4 mb-0">Rekap Atlet Kontingen</h3>
            <p class="muted-copy mb-0 mt-2">Ringkasan jumlah atlet tanding, kelompok seni, dan official per kontingen.</p>
        </div>
        <a href="<?= base_url('admin/sekretariat/kontingen') ?>" class="btn btn-soft rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Kontingen
        </a>
    </div>

    <?php if (($kontingenRows ?? []) === []) : ?>
        <div class="placeholder-stat">
            <h4 class="h5 mb-2">Belum ada kontingen</h4>
            <p class="muted-copy mb-0">Data rekap atlet belum tersedia.</p>
        </div>
    <?php else : ?>
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable-export align-middle mb-0">
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
                            <th class="text-end">Official</th>
                            <th class="text-end no-export">Aksi</th>
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
                                <td class="text-uppercase">
                                    <a href="<?= base_url('admin/sekretariat/kontingen/' . $row->id_kontingen) ?>" class="fw-semibold text-decoration-none text-uppercase text-danger"><?= esc($row->nama_kontingen ?: '-') ?></a>
                                </td>
                                <td><?= esc((string) ($row->provinsi ?? '-')) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahTanding) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahTunggal) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahGanda) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahBeregu) ?></td>
                                <td class="text-end"><?= esc((string) $jumlahSoloKreatif) ?></td>
                                <td class="text-end"><?= esc((string) $totalPeserta) ?></td>
                                <td class="text-end"><?= esc((string) $totalIdCard) ?> lembar</td>
                                <td class="text-end"><?= esc((string) ((int) ($row->jumlah_official ?? 0))) ?></td>
                                <td class="text-end text-uppercase">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('admin/sekretariat/kontingen/' . $row->id_kontingen) ?>">
                                                    <i class="fas fa-eye me-2"></i>Detail
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="post" action="<?= base_url('admin/sekretariat/kontingen/' . $row->id_kontingen . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Hapus kontingen?', 'Kontingen <?= esc($row->nama_kontingen, 'attr') ?> akan dihapus dari sistem.', 'Hapus')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash-alt me-2"></i>Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>

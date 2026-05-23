<?= $this->extend('layouts/kontingen') ?>

<?= $this->section('content') ?>
<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <p class="eyebrow mb-1">Transaksi</p>
            <h3 class="panel-title mb-0">Rincian Pembayaran #<?= $detail['pembayaran']->id_pembayaran ?></h3>
        </div>
        <a href="<?= base_url('kontingen/pembayaran') ?>" class="btn btn-outline-dark rounded-pill btn-sm">Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="vstack gap-4">
                <div>
                    <h4 class="h6 fw-bold mb-3">Item Tanding</h4>
                    <?php if ($detail['tanding'] === []) : ?>
                        <p class="text-muted mb-0">Tidak ada item tanding pada transaksi ini.</p>
                    <?php else : ?>
                        <div class="table-responsive">
                            <table class="table align-middle peserta-table mb-0" id="tabelDetailPembayaranTanding">
                                <thead>
                                    <tr>
                                        <th>Nama Atlet</th>
                                        <th>Kategori Usia</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Kelas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detail['tanding'] as $row) : ?>
                                        <tr>
                                            <td class="fw-semibold"><?= esc($row->nama_pendaftar) ?></td>
                                            <td><?= esc($row->nama_kategori_usia) ?></td>
                                            <td><?= esc($row->jenis_kelamin) ?></td>
                                            <td><?= esc($row->label) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <h4 class="h6 fw-bold mb-3">Item Seni</h4>
                    <?php if ($detail['seni'] === []) : ?>
                        <p class="text-muted mb-0">Tidak ada item seni pada transaksi ini.</p>
                    <?php else : ?>
                        <div class="table-responsive">
                            <table class="table align-middle peserta-table mb-0" id="tabelDetailPembayaranSeni">
                                <thead>
                                    <tr>
                                        <th>Nama Anggota</th>
                                        <th>Kategori Usia</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Kategori Seni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detail['seni'] as $row) : ?>
                                        <tr>
                                            <td class="fw-semibold"><?= esc($row->anggota_kelompok_peserta_seni ?: '-') ?></td>
                                            <td><?= esc($row->nama_kategori_usia) ?></td>
                                            <td><?= esc($row->jenis_kelamin) ?></td>
                                            <td><?= esc($row->jenis_seni) ?> - <?= esc($row->nama_seni) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel-card h-100">
                <div class="vstack gap-3">
                    <div>
                        <small class="text-muted d-block">Tanggal Pembayaran</small>
                        <strong><?= esc(format_tanggal_indo($detail['pembayaran']->tanggal_pembayaran)) ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Status</small>
                        <span class="badge rounded-pill <?= $detail['pembayaran']->status_pembayaran === 'lunas' ? 'text-bg-success' : 'text-bg-warning' ?>">
                            <?= esc($detail['pembayaran']->status_pembayaran) ?>
                        </span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Pembayaran</small>
                        <div class="h4 fw-bold mb-0">Rp <?= number_format((int) $detail['pembayaran']->total_pembayaran, 0, ',', '.') ?></div>
                    </div>
                    <div>
                        <small class="text-muted d-block">ID Kontingen</small>
                        <strong>#<?= esc((string) $detail['pembayaran']->id_kontingen) ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block mb-2">Bukti Pembayaran</small>
                        <a href="<?= base_url('uploads/bukti-pembayaran/' . $detail['pembayaran']->foto) ?>" target="_blank" class="btn btn-outline-danger rounded-pill">Lihat Bukti</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initKontingenDataTable('#tabelDetailPembayaranTanding', { paging: false, searching: false, info: false });
        initKontingenDataTable('#tabelDetailPembayaranSeni', { paging: false, searching: false, info: false });
    });
</script>
<?= $this->endSection() ?>

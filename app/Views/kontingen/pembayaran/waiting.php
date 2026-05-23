<?= $this->extend('layouts/kontingen') ?>

<?= $this->section('content') ?>
<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <p class="eyebrow mb-1">Pembayaran</p>
            <h3 class="panel-title mb-0">Transaksi Menunggu Konfirmasi</h3>
        </div>
    </div>

    <?php if ($transactions === []) : ?>
        <div class="empty-state-box">
            <div class="empty-state-icon"><i class="fas fa-hourglass-half"></i></div>
            <h4>Belum Ada Transaksi Menunggu</h4>
            <p>Tidak ada transaksi pembayaran yang sedang menunggu konfirmasi admin.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table align-middle peserta-table mb-0" id="tabelPembayaranWaiting">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Tanggal</th>
                        <th>Total Pembayaran</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $trx) : ?>
                        <tr>
                            <td>#<?= $trx->id_pembayaran ?></td>
                            <td><?= esc(format_tanggal_indo($trx->tanggal_pembayaran)) ?></td>
                            <td>Rp <?= number_format((int) $trx->total_pembayaran, 0, ',', '.') ?></td>
                            <td><span class="badge rounded-pill text-bg-warning">Menunggu</span></td>
                            <td class="text-center">
                                <a href="<?= base_url('kontingen/pembayaran/' . $trx->id_pembayaran) ?>" class="btn btn-sm btn-icon btn-ghost">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initKontingenDataTable('#tabelPembayaranWaiting');
    });
</script>
<?= $this->endSection() ?>

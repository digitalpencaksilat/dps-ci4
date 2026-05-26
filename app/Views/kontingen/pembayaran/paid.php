<?= $this->extend('layouts/kontingen') ?>

<?= $this->section('content') ?>
<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <p class="eyebrow mb-1">Pembayaran</p>
            <h3 class="panel-title mb-0">Transaksi Lunas</h3>
        </div>
    </div>

    <?php if ($transactions === []) : ?>
        <div class="empty-state-box">
            <div class="empty-state-icon"><i class="fas fa-circle-check"></i></div>
            <h4>Belum Ada Transaksi Lunas</h4>
            <p>Belum ada transaksi pembayaran yang sudah dikonfirmasi lunas.</p>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table align-middle peserta-table mb-0" id="tabelPembayaranPaid">
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
                            <td><span class="badge rounded-pill text-bg-success">Lunas</span></td>
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
        initKontingenDataTable('#tabelPembayaranPaid');
    });
</script>
<?= $this->endSection() ?>

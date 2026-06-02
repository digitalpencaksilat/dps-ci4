<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Biaya Kontingen</p>
            <h3 class="section-title h4 mb-0">Tagihan biaya kontingen</h3>
            <p class="muted-copy mb-0 mt-2">Tagihan ini terpisah dari tagihan peserta, tetapi tetap memakai relasi lama kontingen.id_pembayaran.</p>
        </div>
        <a href="<?= base_url('admin/bendahara/pembayaran') ?>" class="btn btn-outline-danger rounded-pill px-4">Semua Transaksi</a>
    </div>

    <?php if (($rows ?? []) === []) : ?>
        <div class="placeholder-stat">
            <h4 class="h5 mb-2">Belum ada tagihan biaya kontingen</h4>
            <p class="muted-copy mb-0">Pastikan toggle biaya kontingen aktif dan nominal biaya kontingen sudah diatur.</p>
        </div>
    <?php else : ?>
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table admin-datatable align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kontingen</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Nominal</th>
                            <th>Bukti</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($row->nama_kontingen ?: '-') ?></div>
                                    <div class="small muted-copy"><?= esc($row->nama_pimpinan_kontingen ?: '-') ?></div>
                                </td>
                                <td><?= esc(ucwords(str_replace('_', ' ', (string) $row->jenis_kontingen))) ?></td>
                                <td>
                                    <span class="status-badge <?= $row->status_tagihan === 'lunas' ? 'success' : ($row->status_tagihan === 'menunggu' ? 'warning' : 'neutral') ?>">
                                        <?= esc(ucwords(str_replace('_', ' ', (string) $row->status_tagihan))) ?>
                                    </span>
                                </td>
                                <td>Rp <?= number_format((int) $row->nominal_tagihan, 0, ',', '.') ?></td>
                                <td>
                                    <?php if (!empty($row->foto)) : ?>
                                        <a href="<?= base_url('uploads/bukti-pembayaran/' . $row->foto) ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">Lihat</a>
                                    <?php else : ?>
                                        <span class="muted-copy">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <?php if (!empty($row->id_pembayaran)) : ?>
                                            <a href="<?= base_url('admin/bendahara/pembayaran/' . $row->id_pembayaran) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Detail</a>
                                        <?php endif; ?>

                                        <?php if ($row->status_tagihan === 'menunggu' && !empty($row->id_pembayaran)) : ?>
                                            <form method="post" action="<?= base_url('admin/bendahara/pembayaran/biaya-kontingen/' . $row->id_pembayaran . '/konfirmasi') ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill">Konfirmasi</button>
                                            </form>
                                            <form method="post" action="<?= base_url('admin/bendahara/pembayaran/biaya-kontingen/' . $row->id_pembayaran . '/tolak') ?>" onsubmit="return confirmAdminAction(this, 'Tolak pembayaran ini?', 'Pembayaran biaya kontingen akan ditolak dan dikembalikan ke status tagihan aktif.', 'Ya, Tolak')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Tolak</button>
                                            </form>
                                        <?php endif; ?>
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

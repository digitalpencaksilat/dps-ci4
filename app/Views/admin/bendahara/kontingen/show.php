<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
            <p class="eyebrow mb-1">Detail Kontingen</p>
            <h3 class="section-title h4 mb-1"><?= esc($detail['kontingen']->nama_kontingen ?: '-') ?></h3>
            <p class="muted-copy mb-0">Kelola histori transaksi dan item yang masih menunggu pembayaran dari satu halaman kerja.</p>
        </div>
        <a href="<?= base_url('admin/bendahara/kontingen') ?>" class="btn btn-outline-danger rounded-pill px-4">Kembali ke Rekap</a>
    </div>
</section>

<section class="row g-4">
    <div class="col-xl-8">
        <div class="admin-card h-100">
            <p class="eyebrow mb-1">Ringkasan Kontingen</p>
            <h3 class="section-title h4 mb-2"><?= esc($detail['kontingen']->nama_kontingen ?: '-') ?></h3>
            <p class="muted-copy mb-0">Jenis <?= esc(ucwords(str_replace('_', ' ', (string) ($detail['kontingen']->jenis_kontingen ?? '-')))) ?> · <?= esc($detail['kontingen']->nama_pimpinan_kontingen ?: '-') ?> · <?= esc(implode(', ', array_filter([(string) ($detail['kontingen']->kabupaten ?? ''), (string) ($detail['kontingen']->provinsi ?? ''), (string) ($detail['kontingen']->negara ?? '')])) ?: '-') ?></p>

            <div class="compact-kontingen-header">
                <div class="compact-kontingen-stat">
                    <small class="muted-copy d-block">Transaksi Tercatat</small>
                    <strong class="h5 mb-0 d-block"><?= count($detail['transactions'] ?? []) ?></strong>
                </div>
                <div class="compact-kontingen-stat">
                    <small class="muted-copy d-block">Item Menunggu Pembayaran</small>
                    <strong class="h5 mb-0 d-block"><?= esc((string) ($detail['summary']['total_pending_items'] ?? 0)) ?> item</strong>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 mb-3">
                <ul class="nav tab-chip-nav" id="kontingenPaymentTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="transaksi-tab" data-bs-toggle="tab" data-bs-target="#transaksi-pane" type="button" role="tab">Transaksi Kontingen</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="waiting-tab" data-bs-toggle="tab" data-bs-target="#waiting-pane" type="button" role="tab">Menunggu Pembayaran</button>
                    </li>
                </ul>
                <a href="<?= wa_me(convert_to_indonesian_phone_number((string) ($detail['kontingen']->nomor_telepon_penanggungjawab ?? ''))) ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill">
                    <i class="fab fa-whatsapp me-2"></i>Hubungi Penanggung Jawab
                </a>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="transaksi-pane" role="tabpanel" aria-labelledby="transaksi-tab">
                    <?php if (($detail['transactions'] ?? []) === []) : ?>
                        <div class="placeholder-stat">
                            <h4 class="h5 mb-2">Belum ada transaksi</h4>
                            <p class="muted-copy mb-0">Kontingen ini belum pernah mengirim pembayaran.</p>
                        </div>
                    <?php else : ?>
                        <div class="admin-table-wrap">
                            <div class="table-shell admin-table-scroller">
                                <table class="table admin-table admin-datatable align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Tanggal</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detail['transactions'] as $trx) : ?>
                                            <tr>
                                                <td>#<?= esc((string) $trx->id_pembayaran) ?></td>
                                                <td>
                                                    <span class="status-badge <?= $trx->status_pembayaran === 'lunas' ? 'success' : 'warning' ?>">
                                                        <?= esc(ucfirst((string) $trx->status_pembayaran)) ?>
                                                    </span>
                                                </td>
                                                <td>Rp <?= number_format((int) $trx->total_pembayaran, 0, ',', '.') ?></td>
                                                <td><?= esc(format_tanggal_indo($trx->tanggal_pembayaran)) ?></td>
                                                <td class="text-end">
                                                    <a href="<?= base_url('admin/bendahara/pembayaran/' . $trx->id_pembayaran) ?>" class="btn btn-sm btn-outline-danger rounded-pill">Buka Transaksi</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="waiting-pane" role="tabpanel" aria-labelledby="waiting-tab">
                    <?php
                    $waitingRows = [];
                    foreach (($detail['pendingItems']['tanding'] ?? []) as $row) {
                        $waitingRows[] = [
                            'source' => 'tanding',
                            'id' => (int) $row->id_peserta_tanding,
                            'label' => (string) ($row->nama_pendaftar ?? '-'),
                            'kategori' => trim((string) (($row->nama_kategori_usia ?? '-') . ' / ' . ($row->jenis_kelamin ?? '-'))),
                            'rincian' => (string) ($row->label ?? '-'),
                            'nominal' => (string) ($row->jenis_kontingen ?? '') === 'luar_negeri' ? (int) ($row->biaya_pendaftaran_ln ?? 0) : (int) ($row->biaya_pendaftaran_dn ?? 0),
                        ];
                    }
                    foreach (($detail['pendingItems']['seni'] ?? []) as $row) {
                        $waitingRows[] = [
                            'source' => 'seni',
                            'id' => (int) $row->id_kelompok_peserta_seni,
                            'label' => (string) ($row->anggota_kelompok_peserta_seni ?? '-'),
                            'kategori' => trim((string) (($row->nama_kategori_usia ?? '-') . ' / ' . ($row->jenis_kelamin ?? '-'))),
                            'rincian' => trim((string) (($row->jenis_seni ?? '-') . ' / ' . ($row->nama_seni ?? '-'))),
                            'nominal' => (string) ($row->jenis_kontingen ?? '') === 'luar_negeri' ? (int) ($row->biaya_pendaftaran_ln ?? 0) : (int) ($row->biaya_pendaftaran_dn ?? 0),
                        ];
                    }
                    ?>

                    <?php if ($waitingRows === []) : ?>
                        <div class="placeholder-stat">
                            <h4 class="h5 mb-2">Tidak ada item terbuka</h4>
                            <p class="muted-copy mb-0">Semua item tanding dan seni sudah terhubung ke transaksi.</p>
                        </div>
                    <?php else : ?>
                        <div class="admin-table-wrap">
                            <div class="table-shell admin-table-scroller">
                                <table class="table admin-table admin-datatable align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Pilih</th>
                                            <th>Tipe</th>
                                            <th>Peserta / Anggota</th>
                                            <th>Kategori</th>
                                            <th>Rincian</th>
                                            <th>Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($waitingRows as $row) : ?>
                                            <tr>
                                                <td>
                                                    <input
                                                        form="adminCreatePaymentForm"
                                                        type="checkbox"
                                                        name="<?= $row['source'] === 'tanding' ? 'id_peserta_tanding[]' : 'id_kelompok_peserta_seni[]' ?>"
                                                        value="<?= esc((string) $row['id']) ?>"
                                                        data-admin-nominal="<?= esc((string) $row['nominal']) ?>"
                                                    >
                                                </td>
                                                <td><span class="status-badge neutral"><?= esc(ucfirst($row['source'])) ?></span></td>
                                                <td><?= esc($row['label']) ?></td>
                                                <td><?= esc($row['kategori']) ?></td>
                                                <td><?= esc($row['rincian']) ?></td>
                                                <td>Rp <?= number_format((int) $row['nominal'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-card h-100">
            <p class="eyebrow mb-1">Admin Assist</p>
            <h3 class="section-title h4 mb-3">Ringkasan pembayaran terpilih</h3>
            <p class="muted-copy mb-3">Pilih item dari tab <strong>Menunggu Pembayaran</strong>. Setelah siap, klik bayar untuk membuka form bukti pembayaran.</p>

            <form id="adminCreatePaymentForm" method="post" action="<?= base_url('admin/bendahara/kontingen/' . $detail['kontingen']->id_kontingen . '/buat-transaksi') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="assist-summary-box mb-3">
                    <div class="small muted-copy mb-1">Item terbuka</div>
                    <div class="fw-semibold mb-2"><?= esc((string) ($detail['summary']['total_pending_items'] ?? 0)) ?> item menunggu pembayaran</div>
                    <div class="muted-copy small mb-2">Potensi seluruh tagihan</div>
                    <div class="assist-summary-total mb-3">Rp <?= number_format((int) ($detail['summary']['total_pending_amount'] ?? 0), 0, ',', '.') ?></div>
                    <div class="small muted-copy mb-1">Total yang dipilih</div>
                    <div class="h4 fw-bold mb-0" id="adminSelectedAmount">Rp 0</div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" id="showUploadPaymentPanel" class="btn btn-danger rounded-pill px-4">Bayar Item Terpilih</button>
                    <a href="<?= base_url('admin/bendahara/pembayaran/belum-dibayar') ?>" class="btn btn-outline-danger rounded-pill px-4">Lihat Semua Unpaid</a>
                </div>

                <div id="adminUploadPaymentPanel" class="hidden-upload-panel mt-4">
                    <div class="border-top pt-4">
                        <label class="form-label fw-semibold">Upload Bukti Pembayaran</label>
                        <input type="file" name="foto" class="form-control rounded-4" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-max-kb="10240">
                        <div class="small muted-copy mt-2">Format JPG, JPEG, PNG. Maksimal 10 MB.</div>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 mt-3 w-100">Buat Transaksi</button>
                    </div>
                </div>
            </form>

            <div class="border-top mt-4 pt-4">
                <p class="eyebrow mb-1">Rekening Tujuan</p>
                <h3 class="section-title h5 mb-3">Info transfer</h3>
            <?php if (($detail['accounts'] ?? []) === []) : ?>
                <div class="placeholder-stat">
                    <p class="muted-copy mb-0">Belum ada rekening pembayaran aktif.</p>
                </div>
            <?php else : ?>
                <div class="vstack gap-3">
                    <?php foreach ($detail['accounts'] as $account) : ?>
                        <div class="rekening-card">
                            <strong><?= esc($account['bank_name']) ?></strong>
                            <div class="small muted-copy"><?= esc($account['bank_account_name']) ?></div>
                            <div class="fw-semibold"><?= esc($account['bank_account_number']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('adminCreatePaymentForm');
        const totalEl = document.getElementById('adminSelectedAmount');
        const fileInput = form?.querySelector('input[type="file"][name="foto"]');
        const showPanelButton = document.getElementById('showUploadPaymentPanel');
        const uploadPanel = document.getElementById('adminUploadPaymentPanel');
        const selectedItems = new Map();

        if (!form || !totalEl) {
            return;
        }

        const selectedPaymentItems = () => Array.from(selectedItems.values());

        const syncHiddenFields = () => {
            form.querySelectorAll('input[data-admin-generated="selected-payment-item"]').forEach((input) => input.remove());

            selectedItems.forEach((item) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = item.name;
                hidden.value = item.value;
                hidden.dataset.adminGenerated = 'selected-payment-item';
                form.appendChild(hidden);
            });
        };

        const updateSelectedTotal = () => {
            let total = 0;
            selectedPaymentItems().forEach((item) => {
                total += Number(item.nominal || 0);
            });
            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        };

        document.querySelectorAll('input[data-admin-nominal]').forEach((input) => {
            input.addEventListener('change', () => {
                const key = input.name + ':' + input.value;
                if (input.checked) {
                    selectedItems.set(key, {
                        name: input.name,
                        value: input.value,
                        nominal: Number(input.dataset.adminNominal || 0),
                    });
                } else {
                    selectedItems.delete(key);
                }

                syncHiddenFields();
                updateSelectedTotal();
            });
        });

        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) {
                return;
            }

            const validType = ['image/jpeg', 'image/png'].includes(String(file.type || '').toLowerCase()) || /\.(jpe?g|png)$/i.test(file.name || '');
            if (!validType) {
                fileInput.value = '';
                window.toastr?.error('Bukti pembayaran hanya boleh JPG, JPEG, atau PNG.');
                return;
            }

            const maxKb = Number(fileInput.dataset.maxKb || 0);
            if (maxKb > 0 && file.size > (maxKb * 1024)) {
                fileInput.value = '';
                window.toastr?.error(`Ukuran file ${file.name} melebihi batas ${maxKb} KB.`);
            }
        });

        showPanelButton?.addEventListener('click', () => {
            const selected = selectedPaymentItems().length;
            if (selected === 0) {
                window.toastr?.error('Pilih minimal satu item dari tab menunggu pembayaran.');
                return;
            }

            uploadPanel?.classList.add('show');
            if (fileInput) {
                fileInput.required = true;
                fileInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        form.addEventListener('submit', (event) => {
            const selected = selectedPaymentItems().length;
            if (selected === 0) {
                event.preventDefault();
                window.toastr?.error('Pilih minimal satu item untuk dibuatkan transaksi.');
                return;
            }

            if (!uploadPanel?.classList.contains('show')) {
                event.preventDefault();
                window.toastr?.error('Klik tombol bayar terlebih dahulu untuk membuka form bukti pembayaran.');
                return;
            }

            syncHiddenFields();
            document.querySelectorAll('input[data-admin-nominal]').forEach((input) => {
                input.disabled = true;
            });
        });
    });
</script>
<?= $this->endSection() ?>

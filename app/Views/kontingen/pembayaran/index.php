<?= $this->extend('layouts/kontingen') ?>

<?= $this->section('content') ?>
<section class="panel-card mb-4">
    <div class="panel-header">
        <div>
            <p class="eyebrow mb-1">Tagihan</p>
            <h3 class="panel-title mb-0">Tagihan Pembayaran Kontingen</h3>
        </div>
    </div>
    <p class="text-muted mb-0">Pilih item tanding atau seni yang belum dibayar, lalu unggah bukti transfer untuk membuat transaksi pembayaran.</p>
</section>

<?php
$biayaKontingen = $biayaKontingen ?? null;
$biayaEnabled = is_array($biayaKontingen) && !empty($biayaKontingen['enabled']);
$biayaCanPay = is_array($biayaKontingen) && !empty($biayaKontingen['can_pay']);
$biayaNominal = is_array($biayaKontingen) ? (int) ($biayaKontingen['nominal'] ?? 0) : 0;
$biayaStatus = is_array($biayaKontingen) ? (string) ($biayaKontingen['status'] ?? '') : '';
?>

<?php if ($biayaEnabled) : ?>
    <section class="panel-card mb-4">
        <div class="panel-header">
            <div>
                <p class="eyebrow mb-1">Biaya Kontingen</p>
                <h3 class="panel-title mb-0">Tagihan Biaya Kontingen</h3>
            </div>
            <?php if ($biayaStatus !== '') : ?>
                <span class="status-badge <?= $biayaStatus === 'lunas' ? 'success' : ($biayaStatus === 'menunggu' ? 'warning' : 'neutral') ?>">
                    <?= esc(ucwords(str_replace('_', ' ', $biayaStatus))) ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($biayaNominal <= 0) : ?>
            <div class="empty-state-box">
                <p class="mb-0">Biaya kontingen bernilai 0 (gratis).</p>
            </div>
        <?php elseif (! $allowPayment) : ?>
            <div class="alert alert-warning border-0 rounded-4 mb-0">Akses pembayaran sedang ditutup.</div>
        <?php elseif ($biayaCanPay) : ?>
            <form method="post" action="<?= base_url('kontingen/pembayaran/biaya-kontingen') ?>" enctype="multipart/form-data" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-12 col-xl-7">
                    <label class="form-label fw-semibold">Upload Bukti Pembayaran Biaya Kontingen</label>
                    <input type="file" name="foto" class="form-control rounded-4" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-max-kb="10240" required>
                    <div class="small text-muted mt-2">Total tagihan: <strong>Rp <?= number_format($biayaNominal, 0, ',', '.') ?></strong></div>
                </div>
                <div class="col-12 col-xl-5 d-flex align-items-end">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-4 w-100">Upload Bukti Biaya Kontingen</button>
                </div>
            </form>
        <?php elseif (is_array($biayaKontingen) && !empty($biayaKontingen['payment'])) : ?>
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <div>
                    <div class="small text-muted">Nominal</div>
                    <div class="h5 fw-bold mb-0">Rp <?= number_format($biayaNominal, 0, ',', '.') ?></div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if (!empty($biayaKontingen['payment']->id_pembayaran)) : ?>
                        <a href="<?= base_url('kontingen/pembayaran/' . $biayaKontingen['payment']->id_pembayaran) ?>" class="btn btn-outline-danger rounded-pill">Lihat Transaksi</a>
                    <?php endif; ?>
                    <?php if (!empty($biayaKontingen['payment']->foto)) : ?>
                        <a href="<?= base_url('uploads/bukti-pembayaran/' . $biayaKontingen['payment']->foto) ?>" target="_blank" class="btn btn-outline-secondary rounded-pill">Lihat Bukti</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="row g-4">
    <div class="col-xl-8">
        <div class="panel-card h-100">
            <div class="panel-header">
                <div>
                    <p class="eyebrow mb-1">Checkout</p>
                    <h3 class="panel-title mb-0">Item Belum Dibayar</h3>
                </div>
            </div>

            <?php if (($tanding === []) && ($seni === [])) : ?>
                <?php
                    $waitingCount = count($waitingTransactions ?? []);
                    $paidCount = count($paidTransactions ?? []);
                    if ($waitingCount > 0) {
                        $emptyTitle = 'Tagihan Sedang Diproses';
                        $emptyMessage = 'Semua item pembayaran sudah masuk transaksi dan sedang menunggu konfirmasi bendahara.';
                    } elseif ($paidCount > 0) {
                        $emptyTitle = 'Semua Pembayaran Lunas';
                        $emptyMessage = 'Tidak ada tagihan aktif yang perlu dibayar saat ini.';
                    } else {
                        $emptyTitle = 'Belum Ada Tagihan';
                        $emptyMessage = 'Daftarkan peserta tanding atau seni terlebih dahulu untuk membuat tagihan pembayaran.';
                    }
                ?>
                <div class="empty-state-box">
                    <div class="empty-state-icon"><i class="fas fa-wallet"></i></div>
                    <h4><?= esc($emptyTitle) ?></h4>
                    <p><?= esc($emptyMessage) ?></p>
                    <?php if ($waitingCount > 0) : ?>
                        <a href="<?= base_url('kontingen/pembayaran/menunggu-konfirmasi') ?>" class="btn btn-danger rounded-pill px-4 mt-2">Lihat Menunggu Konfirmasi</a>
                    <?php elseif ($paidCount > 0) : ?>
                        <a href="<?= base_url('kontingen/pembayaran/lunas') ?>" class="btn btn-danger rounded-pill px-4 mt-2">Lihat Pembayaran Lunas</a>
                    <?php else : ?>
                        <a href="<?= base_url('kontingen/peserta') ?>" class="btn btn-danger rounded-pill px-4 mt-2">Tambah Peserta</a>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <form method="post" action="<?= base_url('kontingen/pembayaran') ?>" enctype="multipart/form-data" id="formPembayaranKontingen">
                    <?= csrf_field() ?>

                    <div class="vstack gap-4">
                        <?php if ($tanding !== []) : ?>
                            <div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <h4 class="h6 fw-bold mb-0">Kategori Tanding</h4>
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" id="selectAllTanding">
                                            <label class="form-check-label small fw-semibold" for="selectAllTanding">Pilih semua tanding</label>
                                        </div>
                                        <span class="small text-muted" id="selectedTandingCount">0/<?= count($tanding) ?> item dipilih</span>
                                    </div>
                                </div>
                                <div class="vstack gap-2">
                                    <?php foreach ($tanding as $item) : ?>
                                        <?php $nominal = $item->jenis_kontingen === 'dalam_negeri' ? (int) $item->biaya_pendaftaran_dn : (int) $item->biaya_pendaftaran_ln; ?>
                                        <label class="checkout-item-card">
                                            <input type="checkbox" name="id_peserta_tanding[]" value="<?= $item->id_peserta_tanding ?>" data-nominal="<?= $nominal ?>" data-category="tanding">
                                            <div>
                                                <strong><?= esc($item->nama_pendaftar) ?></strong>
                                                <small><?= esc($item->nama_kategori_usia) ?> - <?= esc($item->jenis_kelamin) ?> - Kelas <?= esc($item->label) ?></small>
                                            </div>
                                            <span class="checkout-price">Rp <?= number_format($nominal, 0, ',', '.') ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($seni !== []) : ?>
                            <div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <h4 class="h6 fw-bold mb-0">Kategori Seni</h4>
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" id="selectAllSeni">
                                            <label class="form-check-label small fw-semibold" for="selectAllSeni">Pilih semua seni</label>
                                        </div>
                                        <span class="small text-muted" id="selectedSeniCount">0/<?= count($seni) ?> item dipilih</span>
                                    </div>
                                </div>
                                <div class="vstack gap-2">
                                    <?php foreach ($seni as $item) : ?>
                                        <?php $nominal = $item->jenis_kontingen === 'dalam_negeri' ? (int) $item->biaya_pendaftaran_dn : (int) $item->biaya_pendaftaran_ln; ?>
                                        <label class="checkout-item-card">
                                            <input type="checkbox" name="id_kelompok_peserta_seni[]" value="<?= $item->id_kelompok_peserta_seni ?>" data-nominal="<?= $nominal ?>" data-category="seni">
                                            <div>
                                                <strong><?= esc($item->anggota_kelompok_peserta_seni ?: '-') ?></strong>
                                                <small><?= esc($item->nama_kategori_usia) ?> - <?= esc($item->jenis_kelamin) ?> - <?= esc($item->jenis_seni) ?> <?= esc($item->nama_seni) ?></small>
                                            </div>
                                            <span class="checkout-price">Rp <?= number_format($nominal, 0, ',', '.') ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($allowPayment) : ?>
                            <div>
                                <label class="form-label fw-semibold">Upload Bukti Pembayaran</label>
                                <input type="file" name="foto" class="form-control rounded-4" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-max-kb="10240" required>
                                <div class="small text-muted mt-2">Hanya JPG, JPEG, atau PNG. Maksimal 10 MB. Gambar akan dioptimasi otomatis agar ukuran lebih ringan.</div>
                                <div class="small fw-semibold text-success mt-2" id="paymentProofPreview"></div>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-top pt-3">
                                <div>
                                    <div class="small text-muted" id="paymentSelectionSummary">Belum ada item yang dipilih.</div>
                                    <div class="text-muted small">Total tagihan terpilih</div>
                                    <div class="h4 fw-bold mb-0" id="totalPembayaranKontingen">Rp 0</div>
                                    <div class="small text-muted mt-1" id="paymentSelectionHint">Pilih minimal satu item pembayaran dan unggah bukti transfer yang valid untuk mengaktifkan tombol submit.</div>
                                </div>
                                <button type="submit" class="btn btn-danger btn-lg rounded-pill px-4">Upload Bukti & Buat Transaksi</button>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-warning border-0 rounded-4 mb-0">Akses pembayaran sedang ditutup.</div>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="panel-card h-100">
            <div class="panel-header">
                <div>
                    <p class="eyebrow mb-1">Rekening Tujuan</p>
                    <h3 class="panel-title mb-0">Informasi Transfer</h3>
                </div>
            </div>

            <?php if ($accounts === []) : ?>
                <div class="empty-state-box">
                    <p class="mb-0">Belum ada rekening pembayaran aktif.</p>
                </div>
            <?php else : ?>
                <div class="vstack gap-3">
                    <?php foreach ($accounts as $account) : ?>
                        <div class="rekening-card">
                            <strong><?= esc($account['bank_name']) ?></strong>
                            <div class="small text-muted"><?= esc($account['bank_account_name']) ?></div>
                            <div class="fw-semibold"><?= esc($account['bank_account_number']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('formPembayaranKontingen');
        const totalEl = document.getElementById('totalPembayaranKontingen');
        const selectionHint = document.getElementById('paymentSelectionHint');
        const selectionSummary = document.getElementById('paymentSelectionSummary');
        const proofPreview = document.getElementById('paymentProofPreview');
        const uploadInput = form?.querySelector('input[type="file"][name="foto"]');
        const selectAllTanding = document.getElementById('selectAllTanding');
        const selectAllSeni = document.getElementById('selectAllSeni');
        const selectedTandingCount = document.getElementById('selectedTandingCount');
        const selectedSeniCount = document.getElementById('selectedSeniCount');
        const itemCheckboxes = Array.from(form.querySelectorAll('input[type="checkbox"][data-category]'));
        const tandingCheckboxes = itemCheckboxes.filter((input) => input.dataset.category === 'tanding');
        const seniCheckboxes = itemCheckboxes.filter((input) => input.dataset.category === 'seni');

        if (!form || !totalEl) {
            return;
        }

        const notifyFileError = (message) => {
            if (window.toastr && typeof window.toastr.error === 'function') {
                window.toastr.error(message);
                return;
            }

            window.alert(message);
        };

        const submitBtn = form.querySelector('button[type="submit"]');
        let proofIsValid = false;

        const formatFileSize = (bytes) => {
            if (!Number.isFinite(bytes) || bytes <= 0) {
                return '0 MB';
            }

            return (bytes / (1024 * 1024)).toLocaleString('id-ID', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1,
            }) + ' MB';
        };

        const clearProofPreview = () => {
            if (proofPreview) {
                proofPreview.textContent = '';
            }
        };

        const updateSelectAllState = (master, items) => {
            if (!master || items.length === 0) {
                return;
            }

            const checkedCount = items.filter((input) => input.checked).length;
            master.checked = checkedCount > 0 && checkedCount === items.length;
            master.indeterminate = checkedCount > 0 && checkedCount < items.length;
        };

        const updateCategoryCount = (target, items) => {
            if (!target) {
                return;
            }

            target.textContent = `${items.filter((input) => input.checked).length}/${items.length} item dipilih`;
        };

        const updateTotal = () => {
            let total = 0;
            itemCheckboxes.filter((input) => input.checked).forEach((input) => {
                total += Number(input.dataset.nominal || 0);
            });

            const tandingSelected = tandingCheckboxes.filter((input) => input.checked).length;
            const seniSelected = seniCheckboxes.filter((input) => input.checked).length;
            const selectedCount = tandingSelected + seniSelected;

            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
            updateCategoryCount(selectedTandingCount, tandingCheckboxes);
            updateCategoryCount(selectedSeniCount, seniCheckboxes);
            updateSelectAllState(selectAllTanding, tandingCheckboxes);
            updateSelectAllState(selectAllSeni, seniCheckboxes);

            if (selectionSummary) {
                selectionSummary.textContent = selectedCount === 0
                    ? 'Belum ada item yang dipilih.'
                    : `${selectedCount} item dipilih: ${tandingSelected} tanding, ${seniSelected} seni.`;
            }

            if (submitBtn) {
                submitBtn.disabled = (selectedCount === 0 || !proofIsValid);
            }

            if (selectionHint) {
                if (selectedCount === 0 && !proofIsValid) {
                    selectionHint.textContent = 'Pilih minimal satu item pembayaran dan unggah bukti transfer yang valid untuk mengaktifkan tombol submit.';
                } else if (selectedCount === 0) {
                    selectionHint.textContent = 'Pilih minimal satu item pembayaran untuk melanjutkan checkout.';
                } else if (!proofIsValid) {
                    selectionHint.textContent = 'Unggah bukti transfer JPG, JPEG, atau PNG yang valid untuk melanjutkan checkout.';
                } else {
                    selectionHint.textContent = 'Siap membuat transaksi. Pastikan bukti transfer sesuai dengan total tagihan terpilih.';
                }

                selectionHint.classList.toggle('text-danger', selectedCount === 0 || !proofIsValid);
                selectionHint.classList.toggle('text-muted', selectedCount > 0 && proofIsValid);
            }
        };

        itemCheckboxes.forEach((input) => {
            input.addEventListener('change', updateTotal);
        });

        selectAllTanding?.addEventListener('change', () => {
            tandingCheckboxes.forEach((input) => {
                input.checked = selectAllTanding.checked;
            });
            updateTotal();
        });

        selectAllSeni?.addEventListener('change', () => {
            seniCheckboxes.forEach((input) => {
                input.checked = selectAllSeni.checked;
            });
            updateTotal();
        });

        // Jalankan pada pemuatan pertama untuk inisialisasi status tombol submit
        updateTotal();

        uploadInput?.addEventListener('change', () => {
            const file = uploadInput.files?.[0];
            if (!file) {
                proofIsValid = false;
                clearProofPreview();
                updateTotal();
                return;
            }

            const validType = ['image/jpeg', 'image/png'].includes(String(file.type || '').toLowerCase()) || /\.(jpe?g|png)$/i.test(file.name || '');
            if (!validType) {
                uploadInput.value = '';
                proofIsValid = false;
                clearProofPreview();
                notifyFileError('Bukti pembayaran hanya boleh berupa gambar JPG, JPEG, atau PNG.');
                updateTotal();
                return;
            }

            const maxKb = Number(uploadInput.dataset.maxKb || 0);
            if (maxKb > 0 && file.size > (maxKb * 1024)) {
                uploadInput.value = '';
                proofIsValid = false;
                clearProofPreview();
                notifyFileError(`Ukuran file ${file.name} melebihi batas 10 MB.`);
                updateTotal();
                return;
            }

            proofIsValid = true;
            if (proofPreview) {
                proofPreview.textContent = `File dipilih: ${file.name} (${formatFileSize(file.size)})`;
            }
            updateTotal();
        });
    });
</script>
<?= $this->endSection() ?>

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
                <div class="empty-state-box">
                    <div class="empty-state-icon"><i class="fas fa-wallet"></i></div>
                    <h4>Tidak Ada Tagihan Aktif</h4>
                    <p>Semua item tanding dan seni untuk kontingen ini belum tersedia atau sudah masuk transaksi pembayaran.</p>
                </div>
            <?php else : ?>
                <form method="post" action="<?= base_url('kontingen/pembayaran') ?>" enctype="multipart/form-data" id="formPembayaranKontingen">
                    <?= csrf_field() ?>

                    <div class="vstack gap-4">
                        <?php if ($tanding !== []) : ?>
                            <div>
                                <h4 class="h6 fw-bold mb-3">Kategori Tanding</h4>
                                <div class="vstack gap-2">
                                    <?php foreach ($tanding as $item) : ?>
                                        <?php $nominal = $item->jenis_kontingen === 'dalam_negeri' ? (int) $item->biaya_pendaftaran_dn : (int) $item->biaya_pendaftaran_ln; ?>
                                        <label class="checkout-item-card">
                                            <input type="checkbox" name="id_peserta_tanding[]" value="<?= $item->id_peserta_tanding ?>" data-nominal="<?= $nominal ?>">
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
                                <h4 class="h6 fw-bold mb-3">Kategori Seni</h4>
                                <div class="vstack gap-2">
                                    <?php foreach ($seni as $item) : ?>
                                        <?php $nominal = $item->jenis_kontingen === 'dalam_negeri' ? (int) $item->biaya_pendaftaran_dn : (int) $item->biaya_pendaftaran_ln; ?>
                                        <label class="checkout-item-card">
                                            <input type="checkbox" name="id_kelompok_peserta_seni[]" value="<?= $item->id_kelompok_peserta_seni ?>" data-nominal="<?= $nominal ?>">
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
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-top pt-3">
                                <div>
                                    <div class="text-muted small">Total tagihan terpilih</div>
                                    <div class="h4 fw-bold mb-0" id="totalPembayaranKontingen">Rp 0</div>
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
        const uploadInput = form?.querySelector('input[type="file"][name="foto"]');

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

        const updateTotal = () => {
            let total = 0;
            form.querySelectorAll('input[type="checkbox"]:checked').forEach((input) => {
                total += Number(input.dataset.nominal || 0);
            });
            totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        };

        form.querySelectorAll('input[type="checkbox"]').forEach((input) => {
            input.addEventListener('change', updateTotal);
        });

        uploadInput?.addEventListener('change', () => {
            const file = uploadInput.files?.[0];
            if (!file) {
                return;
            }

            const validType = ['image/jpeg', 'image/png'].includes(String(file.type || '').toLowerCase()) || /\.(jpe?g|png)$/i.test(file.name || '');
            if (!validType) {
                uploadInput.value = '';
                notifyFileError('Bukti pembayaran hanya boleh berupa gambar JPG, JPEG, atau PNG.');
                return;
            }

            const maxKb = Number(uploadInput.dataset.maxKb || 0);
            if (maxKb > 0 && file.size > (maxKb * 1024)) {
                uploadInput.value = '';
                notifyFileError(`Ukuran file ${file.name} melebihi batas ${maxKb} KB.`);
            }
        });
    });
</script>
<?= $this->endSection() ?>

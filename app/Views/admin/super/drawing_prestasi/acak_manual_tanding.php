<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$idKompetisi = (int) $selected->id_kompetisi_tanding;
$judul = trim(($selected->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($selected->jenis_kelamin ?? '')) . ', ' . ($selected->label ?? '-') . ' Class');
?>

<section class="admin-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <p class="eyebrow mb-1">Drawing Prestasi - Manual Shuffle</p>
            <h3 class="section-title h4 mb-1"><?= esc($judul) ?></h3>
            <p class="muted-copy mb-0 small">Tentukan slot bagan untuk tiap atlet, lalu simpan untuk membuat bagan (standar Persilat).</p>
        </div>
        <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/super/drawing-prestasi/tanding?id=' . $idKompetisi) ?>">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</section>

<?php if ($jumlahPeserta < 2) : ?>
    <section class="admin-card">
        <h4 class="h5 mb-2">Ooopss</h4>
        <p class="muted-copy mb-0">Kategori ini hanya memiliki <?= (int) $jumlahPeserta ?> peserta, minimal 2 peserta diperlukan untuk membuat bagan.</p>
    </section>
<?php else : ?>
    <section class="admin-card">
        <form method="post" action="<?= base_url('admin/super/drawing-prestasi/tanding/' . $idKompetisi . '/acak-manual') ?>" id="formBaganManual"
            onsubmit="return validasiSlotManual(event)">
            <?= csrf_field() ?>
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-3">
                    <thead>
                        <tr>
                            <th style="width:140px">Slot Bagan</th>
                            <th>Nama Atlet</th>
                            <th>Kontingen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peserta as $item) : ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="id_peserta_tanding[]" value="<?= (int) $item->id_peserta_tanding ?>">
                                    <select class="form-select form-select-sm slot-peserta" name="urutan_slot[]" required>
                                        <option value="" disabled selected>-</option>
                                        <?php for ($slot = 0; $slot < $jumlahPeserta; $slot++) : ?>
                                            <option value="<?= $slot ?>"><?= $slot + 1 ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                                <td><?= esc($item->nama_pendaftar ?? '-') ?></td>
                                <td class="text-uppercase"><?= esc($item->nama_kontingen ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-danger rounded-pill px-4">
                <i class="fas fa-shuffle me-1"></i> Simpan Bagan Manual
            </button>
        </form>
    </section>

    <script>
        function validasiSlotManual(e) {
            var selects = document.querySelectorAll('#formBaganManual .slot-peserta');
            var values = [];
            for (var i = 0; i < selects.length; i++) {
                if (selects[i].value === '') {
                    e.preventDefault();
                    Swal.fire('Slot belum lengkap', 'Setiap atlet harus diberi slot.', 'warning');
                    return false;
                }
                if (values.indexOf(selects[i].value) !== -1) {
                    e.preventDefault();
                    Swal.fire('Slot duplikat', 'Terdapat slot yang sama, silakan periksa kembali.', 'error');
                    return false;
                }
                values.push(selects[i].value);
            }
            e.preventDefault();
            confirmAdminAction(document.getElementById('formBaganManual'), 'Simpan Bagan Manual?', 'Bagan lama yang belum dijadwalkan akan diganti dengan hasil shuffle manual.', 'Ya, Simpan');
            return false;
        }

        // Cegah slot kembar secara interaktif.
        document.querySelectorAll('#formBaganManual .slot-peserta').forEach(function (sel) {
            sel.addEventListener('change', function () {
                var taken = [];
                document.querySelectorAll('#formBaganManual .slot-peserta').forEach(function (s) {
                    if (s.value !== '') { taken.push(s.value); }
                });
                document.querySelectorAll('#formBaganManual .slot-peserta option').forEach(function (opt) {
                    if (opt.value === '') { return; }
                    var inOwn = opt.parentElement.value === opt.value;
                    opt.disabled = (taken.indexOf(opt.value) !== -1) && !inOwn;
                });
            });
        });
    </script>
<?php endif; ?>
<?= $this->endSection() ?>

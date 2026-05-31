<?php $routePrefix = (string) ($routePrefix ?? 'admin/sekretariat/jadwal-seni'); ?>
<div class="modal fade" id="modalTukarKelompokPesertaSeniPool" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form action="<?= base_url($routePrefix . '/tukar-kelompok-peserta-seni-pool') ?>" method="post" id="formTukarKelompokPesertaSeniPool">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Swap Athletes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-secondary small mb-3">
                        Pilih dua penampilan (Artistic Pool) yang ingin ditukar. Sistem akan menolak jika jenis seni berbeda atau data sudah terkunci (sudah tampil / sudah ada penilaian ready).
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Penampilan 1</label>
                            <select class="form-select" name="id_penampilan_seni_1" required>
                                <option value="">- pilih penampilan -</option>
                                <?php foreach (($poolSwapCandidates ?? []) as $row): ?>
                                    <?php $label = trim(
                                        'Partai ' . ($row->nomor_partai ?? '-') .
                                        ' | ' . ($row->nama_gelanggang ?? '-') .
                                        ' | ' . ($row->nama_kontingen ?? '-') .
                                        ' | ' . ($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-') .
                                        ' | ' . ($row->anggota_kelompok ?? '-')
                                    ); ?>
                                    <option value="<?= esc((string) ($row->id_penampilan_seni ?? '')) ?>">
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label">Penampilan 2</label>
                            <select class="form-select" name="id_penampilan_seni_2" required>
                                <option value="">- pilih penampilan -</option>
                                <?php foreach (($poolSwapCandidates ?? []) as $row): ?>
                                    <?php $label = trim(
                                        'Partai ' . ($row->nomor_partai ?? '-') .
                                        ' | ' . ($row->nama_gelanggang ?? '-') .
                                        ' | ' . ($row->nama_kontingen ?? '-') .
                                        ' | ' . ($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-') .
                                        ' | ' . ($row->anggota_kelompok ?? '-')
                                    ); ?>
                                    <option value="<?= esc((string) ($row->id_penampilan_seni ?? '')) ?>">
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Swap</button>
                </div>
            </div>
        </form>
    </div>
</div>

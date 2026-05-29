<?php $routePrefix = (string) ($routePrefix ?? 'admin/sekretariat/jadwal-tanding'); ?>
<div class="modal fade" id="modalTukarAtlet" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <form action="<?= base_url($routePrefix . '/tukar-atlet') ?>" method="post" id="formTukarAtlet">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Swap Athletes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Athlete 1</label>
                        <select class="form-select" name="id_atlet_1">
                            <?php foreach (($data_peserta_tanding ?? []) as $peserta_tanding): ?>
                                <option value="<?= esc((string) $peserta_tanding->id_peserta_tanding) ?>">
                                    <?= esc($peserta_tanding->nama_pendaftar . ' - ' . ($peserta_tanding->nama_kontingen ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Athlete 2</label>
                        <select class="form-select" name="id_atlet_2">
                            <?php foreach (($data_peserta_tanding ?? []) as $peserta_tanding): ?>
                                <option value="<?= esc((string) $peserta_tanding->id_peserta_tanding) ?>">
                                    <?= esc($peserta_tanding->nama_pendaftar . ' - ' . ($peserta_tanding->nama_kontingen ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

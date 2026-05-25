<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card">
            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                <a href="<?= base_url('admin/sekretariat/jadwal-tanding') ?>" class="text-decoration-none muted-copy small mb-2 d-block">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Jadwal Tanding
                </a>
                <h6 class="card-title">Schedule of Matches at Arena <?= esc($jadwal->nama_gelanggang ?? '-') ?> - <?= esc($jadwal->keterangan_jadwal ?? $jadwal->keterangan ?? '') ?></h6>
            </div>
            <div class="card-body px-0">
                <?php if (session()->get('level') === 'super_admin'): ?>
                    <div class="mb-3 d-flex flex-wrap gap-2">
                        <div class="dropdown">
                            <button class="btn btn-secondary bg-dark text-white dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Edit Schedule
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?= base_url('admin/sekretariat/jadwal-tanding/' . $jadwal->id_jadwal_tanding) ?>">Set Match Sequence</a>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalSortirNomorPartai">Sort Match Numbers</button>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalAturPolaJadwal">Set Schedule Pattern</button>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalTukarAtlet">Swap Athletes</button>
                            </div>
                        </div>
                    </div>
                    <?= view('shared_components/jadwal_tanding/modal_tukar_atlet', ['data_peserta_tanding' => $peserta ?? []]) ?>
                    <?= view('shared_components/jadwal_tanding/modal_atur_pola_jadwal', ['jadwal_tanding' => $jadwal]) ?>
                    <?= view('shared_components/jadwal_tanding/modal_sortir_ulang_nomor_partai', ['jadwal_tanding' => $jadwal]) ?>
                <?php endif; ?>

                <div class="admin-table-wrap">
                    <div class="table-shell admin-table-scroller">
                        <table class="table admin-table admin-datatable align-middle mb-0" id="tabelDetailJadwalTanding">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th class="text-center">Match</th>
                                    <th class="text-center">Class</th>
                                    <th class="bg-info text-white text-center">Blue</th>
                                    <th class="text-center">Round</th>
                                    <th class="bg-danger text-white text-center">Red</th>
                                    <th class="text-center">Winner</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($details ?? []) as $partai): ?>
                                    <tr>
                                        <td></td>
                                        <td class="align-middle text-capitalize text-center">
                                            <span class="fw-bold"><?= esc((string) ($partai->nomor_partai ?? '-')) ?></span>
                                        </td>
                                        <td class="align-middle text-capitalize text-center small">
                                            <?= esc(($partai->nama_kategori_usia ?? '') . ' ' . ($partai->jenis_kelamin ?? '')) ?><br>
                                            <?= esc($partai->label ?? '') ?>
                                            <?= ($partai->jenis_perlombaan ?? '') === 'pemasalan' ? ' Pool ' . esc((string) ($partai->nomor_pool ?? '')) : '' ?>
                                        </td>
                                        <td class="align-middle text-capitalize text-center">
                                            <?php if (empty($partai->nama_atlet_biru)): ?>
                                                <span class="d-block text-capitalize px-2 text-center text-decoration-underline fst-italic">TBD</span>
                                            <?php else: ?>
                                                <span class="fw-bold d-block text-capitalize"><?= esc($partai->nama_atlet_biru) ?></span>
                                                <span class="text-capitalize d-block small"><?= esc($partai->nama_kontingen_biru ?? '-') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-capitalize text-center">
                                            <span class="fw-bold d-block"><?= esc($partai->babak ?? '-') ?></span>
                                            <span class="badge <?= (int) ($partai->skor_biru ?? 0) > (int) ($partai->skor_merah ?? 0) ? 'bg-blue' : 'bg-dark' ?>"><?= esc((string) ($partai->skor_biru ?? 0)) ?></span>
                                            <span class="text-muted">-</span>
                                            <span class="badge <?= (int) ($partai->skor_merah ?? 0) > (int) ($partai->skor_biru ?? 0) ? 'bg-red' : 'bg-dark' ?>"><?= esc((string) ($partai->skor_merah ?? 0)) ?></span>
                                        </td>
                                        <td class="align-middle text-capitalize text-center">
                                            <?php if (empty($partai->nama_atlet_merah)): ?>
                                                <span class="d-block text-capitalize px-2 text-center text-decoration-underline fst-italic">TBD</span>
                                            <?php else: ?>
                                                <span class="fw-bold d-block text-capitalize"><?= esc($partai->nama_atlet_merah) ?></span>
                                                <span class="text-capitalize d-block small"><?= esc($partai->nama_kontingen_merah ?? '-') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <?php if (!empty($partai->id_atlet_biru) && !empty($partai->id_atlet_merah)): ?>
                                                <?php if ($partai->id_pemenang == $partai->id_atlet_biru): ?>
                                                    <span class="badge bg-blue text-white">Biru</span>
                                                <?php elseif ($partai->id_pemenang == $partai->id_atlet_merah): ?>
                                                    <span class="badge bg-red text-white">Merah</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-capitalize"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($details)): ?>
                        <div class="text-center muted-copy py-4">Belum ada partai dijadwalkan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-blue { background-color: #0d6efd !important; color: #fff; }
.bg-red { background-color: #dc3545 !important; color: #fff; }
</style>
<?= $this->endSection() ?>

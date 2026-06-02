<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card">
            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                <?php $routePrefix = (string) ($routePrefix ?? 'admin/sekretariat/jadwal-tanding'); ?>
                <a href="<?= base_url($routePrefix) ?>" class="text-decoration-none muted-copy small mb-2 d-block">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Jadwal Tanding
                </a>
                <h6 class="card-title">Schedule of Matches at Arena <?= esc($jadwal->nama_gelanggang ?? '-') ?> - <?= esc($jadwal->keterangan_jadwal ?? $jadwal->keterangan ?? '') ?></h6>
            </div>
            <div class="card-body px-0">
                <?php if (!empty($bracketBentrokError ?? false)): ?>
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-1"></i> Struktur Bracket Bentrok Terdeteksi</h5>
                        <div class="small mb-2">
                            <?= $bracketBentrokMessage ?? '' ?>
                        </div>
                        <?php if (session()->get('level') === 'super_admin'): ?>
                            <hr>
                            <form action="<?= base_url('admin/super/jadwal-tanding/' . (int) $jadwal->id_jadwal_tanding . '/perbaiki-bracket-bentrok') ?>"
                                  method="post"
                                  class="mb-0"
                                  onsubmit="return confirmAdminAction(this, 'Perbaiki Bracket Bentrok?', 'Sistem akan menjalankan perbaikan otomatis untuk bracket bentrok pada jadwal ini.', 'Ya, Perbaiki');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-tools me-1"></i> Perbaiki Bracket Otomatis
                                </button>
                            </form>
                        <?php else: ?>
                            <hr>
                            <small class="text-muted">Hubungi Super Admin untuk memperbaiki struktur bracket ini.</small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->get('level') === 'super_admin'): ?>
                    <div class="mb-3 d-flex flex-wrap gap-2">
                        <div class="dropdown">
                            <button class="btn btn-secondary bg-dark text-white dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Edit Schedule
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?= base_url($routePrefix . '/' . $jadwal->id_jadwal_tanding) ?>">Set Match Sequence</a>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalSortirNomorPartai">Sort Match Numbers</button>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalAturPolaJadwal">Set Schedule Pattern</button>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalTukarAtlet">Swap Athletes</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                            <i class="fas fa-file-excel me-1"></i> Import Excel
                        </button>
                    </div>
                    <?= view('shared_components/jadwal_tanding/modal_tukar_atlet', ['data_peserta_tanding' => $peserta ?? [], 'routePrefix' => $routePrefix]) ?>
                    <?= view('shared_components/jadwal_tanding/modal_atur_pola_jadwal', ['jadwal_tanding' => $jadwal, 'routePrefix' => $routePrefix]) ?>
                    <?= view('shared_components/jadwal_tanding/modal_sortir_ulang_nomor_partai', ['jadwal_tanding' => $jadwal, 'routePrefix' => $routePrefix]) ?>
                    <?= view('shared_components/jadwal_tanding/modal_import_excel', ['jadwal' => $jadwal, 'routePrefix' => $routePrefix]) ?>
                <?php endif; ?>

                <div class="admin-table-wrap">
                    <div class="table-shell admin-table-scroller">
                        <table class="table admin-table admin-datatable-export align-middle mb-0" id="tabelDetailJadwalTanding">
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
                                                <?php if (!empty($partai->calon_atlet_biru)): ?>
                                                    <span class="d-block text-capitalize px-2 text-center fst-italic small">
                                                        <u class="d-block">Pemenang Partai Ke <strong><?= esc((string) $partai->calon_atlet_biru) ?></strong></u>
                                                        Dari Gelanggang <?= esc($partai->gelanggang_calon_atlet_biru ?? '-') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="d-block text-capitalize px-2 text-center text-decoration-underline fst-italic">TBD</span>
                                                <?php endif; ?>
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
                                                <?php if (!empty($partai->calon_atlet_merah)): ?>
                                                    <span class="d-block text-capitalize px-2 text-center fst-italic small">
                                                        <u class="d-block">Pemenang Partai Ke <strong><?= esc((string) $partai->calon_atlet_merah) ?></strong></u>
                                                        Dari Gelanggang <?= esc($partai->gelanggang_calon_atlet_merah ?? '-') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="d-block text-capitalize px-2 text-center text-decoration-underline fst-italic">TBD</span>
                                                <?php endif; ?>
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

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card mb-3">
            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                <?php $routePrefix = (string) ($routePrefix ?? 'admin/sekretariat/jadwal-seni'); ?>
                <a href="<?= base_url($routePrefix) ?>" class="text-decoration-none muted-copy small mb-2 d-block">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Jadwal Seni
                </a>
                <h6 class="card-title">Jadwal Seni Arena <?= esc($jadwal->nama_gelanggang ?? '-') ?></h6>
            </div>
        </div>

        <div class="nav-wrapper">
            <ul class="nav nav-pills nav-pills-primary nav-fill p-1 mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#battle_seni" role="tab">Artistic Battle</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#pool_seni" role="tab">Artistic Pool</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="battle_seni">
                    <div class="admin-card">
                        <div class="card-body px-0">
                            <?php if (session()->get('level') === 'super_admin'): ?>
                                <div class="mb-3 d-flex flex-wrap gap-2 px-3 px-md-0">
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalImportExcelBattle">
                                        <i class="fas fa-file-excel me-1"></i> Import Excel Battle
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-pen me-1"></i> Edit Schedule
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="<?= base_url(($routePrefix === 'admin/sekretariat/jadwal-seni' ? 'admin/super/jadwal-seni' : $routePrefix) . '/pengaturan-urutan-partai-seni/' . (int) ($jadwal->id_jadwal_seni ?? 0)) ?>">
                                                <i class="fas fa-grip-vertical me-1"></i> Set Match Sequence
                                            </a>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalSortirNomorPartaiSeniBattle">
                                                <i class="fas fa-sort-numeric-down me-1"></i> Sort Match Numbers
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="admin-table-wrap">
                                <div class="table-shell admin-table-scroller">
                                    <?= view('shared_components/detail_jadwal_seni/tabel_battle', ['data_detail_jadwal_seni' => $battleDetails ?? []]) ?>
                                </div>
                            </div>
                            <?php $battle = $battleDetails ?? $details ?? []; if (empty($battle)): ?>
                                <div class="text-center muted-copy py-4">Belum ada data battle dijadwalkan.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="pool_seni">
                    <div class="admin-card">
                        <div class="card-body px-0">
                            <?php if (session()->get('level') === 'super_admin'): ?>
                                <div class="mb-3 d-flex flex-wrap gap-2 px-3 px-md-0">
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalImportExcelPool">
                                        <i class="fas fa-file-excel me-1"></i> Import Excel Pool
                                    </button>
                                </div>
                            <?php endif; ?>
                            <?php if (session()->get('level') === 'super_admin' && ! empty($poolDetails ?? [])): ?>
                                <div class="mb-3 d-flex flex-wrap gap-2 px-3 px-md-0">
                                    <div class="dropdown">
                                        <button class="btn btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-pen me-1"></i> Edit Schedule
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="<?= base_url(($routePrefix === 'admin/sekretariat/jadwal-seni' ? 'admin/super/jadwal-seni' : $routePrefix) . '/pengaturan-urutan-partai-seni/' . (int) ($jadwal->id_jadwal_seni ?? 0)) ?>">
                                                <i class="fas fa-grip-vertical me-1"></i> Set Match Sequence
                                            </a>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalSortirNomorPartaiSeniPool">
                                                <i class="fas fa-sort-numeric-down me-1"></i> Sort Match Numbers
                                            </button>
                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalTukarKelompokPesertaSeniPool">
                                                <i class="fas fa-exchange-alt me-1"></i> Swap Athletes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?= view('shared_components/detail_jadwal_seni/modal_tukar_kelompok_peserta_seni_pool', [
                                    'poolSwapCandidates' => $poolSwapCandidates ?? [],
                                    'routePrefix' => $routePrefix ?? 'admin/sekretariat/jadwal-seni',
                                ]) ?>
                            <?php endif; ?>
                            <div class="admin-table-wrap">
                                <div class="table-shell admin-table-scroller">
                                    <?= view('shared_components/detail_jadwal_seni/tabel_pool', ['data_detail_jadwal_seni' => $poolDetails ?? []]) ?>
                                </div>
                            </div>
                            <?php $pool = $poolDetails ?? $details ?? []; if (empty($pool)): ?>
                                <div class="text-center muted-copy py-4">Belum ada data pool dijadwalkan.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (session()->get('level') === 'super_admin'): ?>
    <?php $seniRoutePrefix = ($routePrefix === 'admin/sekretariat/jadwal-seni' ? 'admin/super/jadwal-seni' : $routePrefix); ?>
    <?= view('shared_components/detail_jadwal_seni/modal_import_excel_pool', [
        'jadwal' => $jadwal,
        'routePrefix' => $seniRoutePrefix,
    ]) ?>
    <?= view('shared_components/detail_jadwal_seni/modal_import_excel_battle', [
        'jadwal' => $jadwal,
        'routePrefix' => $seniRoutePrefix,
    ]) ?>
    <?= view('shared_components/detail_jadwal_seni/modal_sortir_nomor_partai', [
        'jadwal' => $jadwal,
        'routePrefix' => $seniRoutePrefix,
        'modalSuffix' => 'Pool',
    ]) ?>
    <?= view('shared_components/detail_jadwal_seni/modal_sortir_nomor_partai', [
        'jadwal' => $jadwal,
        'routePrefix' => $seniRoutePrefix,
        'modalSuffix' => 'Battle',
    ]) ?>
<?php endif; ?>
<?= $this->endSection() ?>

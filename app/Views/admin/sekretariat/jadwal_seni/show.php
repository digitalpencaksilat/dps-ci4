<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card mb-3">
            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                <a href="<?= base_url('admin/sekretariat/jadwal-seni') ?>" class="text-decoration-none muted-copy small mb-2 d-block">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Jadwal Seni
                </a>
                <h6 class="card-title">Artistic Arena Schedule <?= esc($jadwal->nama_gelanggang ?? '-') ?></h6>
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

<style>
.bg-blue { background-color: #0d6efd !important; color: #fff; }
.bg-red { background-color: #dc3545 !important; color: #fff; }
.bg-info-gradient { background: linear-gradient(180deg, #0dcaf0, #0d6efd) !important; }
.bg-danger-gradient { background: linear-gradient(180deg, #dc3545, #a71d2a) !important; }
</style>
<?= $this->endSection() ?>

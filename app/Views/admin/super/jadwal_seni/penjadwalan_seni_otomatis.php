<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$gelanggang = $gelanggang ?? [];
$subKategori = $subKategori ?? [];
$babakOptions = $babakOptions ?? [];
$showValidation = session('status') === false;
$poolSubOld = array_map('strval', old('pool_urutan_id_sub_kategori_seni', old('urutan_id_sub_kategori_seni', [])) ?: []);
$battleSubOld = array_map('strval', old('battle_urutan_id_sub_kategori_seni', old('urutan_id_sub_kategori_seni', [])) ?: []);
$battleBabakOld = array_map('strval', old('babak_battle_seni', []));
$jenisBattle = (string) old('jenis_penjadwalan', 'prestasi');
$langsungPdf = old('langsung_buat_pdf');
$poolCount = 0;
$battleCount = 0;
$battlePartaiEstimate = 0;
foreach ($subKategori as $row) {
    if (($row->sistem_penampilan ?? '') === 'pool') {
        $poolCount += (int) ($row->jumlah_pool_seni ?? 0);
    }
    if (($row->sistem_penampilan ?? '') === 'battle') {
        $battleCount++;
        $battlePartaiEstimate += (int) ($row->jumlah_battle_belum_jadwal ?? 0);
    }
}
$battlePartaiEstimate = max(1, $battlePartaiEstimate);
?>

<div class="admin-card ci3-schedule-card seni-schedule-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div class="hero-strip">
            <div>
                <p class="eyebrow mb-1">Pembuatan Jadwal</p>
                <h2 class="section-title h4 mb-1">Penjadwalan Otomatis Seni</h2>
                <p class="muted-copy mb-0">Tampilan dan interaksi diselaraskan dengan penjadwalan otomatis tanding, termasuk urutan drag and drop.</p>
            </div>
            <div class="hero-stats">
                <div class="hero-stat-item">
                    <span class="hero-stat-label">Seni Pool</span>
                    <strong><?= esc((string) $poolCount) ?></strong>
                </div>
                <div class="hero-stat-item accent">
                    <span class="hero-stat-label">Seni Battle</span>
                    <strong><?= esc((string) $battleCount) ?></strong>
                </div>
                <div class="hero-stat-item">
                    <span class="hero-stat-label">Gelanggang</span>
                    <strong><?= esc((string) count($gelanggang)) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body px-0">
        <ul class="nav nav-pills mb-3 gap-2" id="seniAutoTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="btn btn-outline-danger active" id="pool-tab" data-bs-toggle="pill" data-bs-target="#pool-pane" type="button" role="tab">Sistem Pool</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="btn btn-outline-danger" id="battle-tab" data-bs-toggle="pill" data-bs-target="#battle-pane" type="button" role="tab">Sistem Battle</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="pool-pane" role="tabpanel" aria-labelledby="pool-tab">
                <form novalidate method="post" action="<?= base_url('admin/super/jadwal-seni/buat-jadwal-seni-pool-otomatis') ?>" id="formPool" class="seni-auto-form" data-unit-label="Pool">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card sticky-top ci3-panel-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="poolTanggal">Tanggal</label>
                                                <input type="date" id="poolTanggal" class="form-control <?= $showValidation && ! old('tanggal') ? 'is-invalid' : '' ?>" name="tanggal" value="<?= esc((string) old('tanggal', date('Y-m-d'))) ?>" required>
                                                <div class="invalid-feedback">Silahkan memilih tanggal</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="poolJamMulai">Jam Mulai :</label>
                                                <input type="time" id="poolJamMulai" class="form-control <?= $showValidation && ! old('jam_mulai') ? 'is-invalid' : '' ?>" name="jam_mulai" value="<?= esc((string) old('jam_mulai', '08:00')) ?>" required>
                                                <div class="invalid-feedback">Silahkan memilih jam mulai</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="poolJamSelesai">Jam Selesai :</label>
                                                <input type="time" id="poolJamSelesai" class="form-control <?= $showValidation && ! old('jam_selesai') ? 'is-invalid' : '' ?>" name="jam_selesai" value="<?= esc((string) old('jam_selesai', '22:00')) ?>" required>
                                                <div class="invalid-feedback">Silahkan memilih jam selesai</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="poolKeterangan">Keterangan :</label>
                                                <textarea id="poolKeterangan" name="keterangan" class="form-control" rows="3"><?= esc((string) old('keterangan')) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input pdf-toggle" type="checkbox" name="langsung_buat_pdf" id="poolPdf" value="1" <?= $langsungPdf === null || $langsungPdf ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="poolPdf">Langsung Buat Jadwal PDF</label>
                                                </div>
                                            </div>
                                            <div class="mb-3 pdf-engine-group">
                                                <input type="hidden" name="pdf_library" value="mpdf">
                                                <small class="text-muted">PDF Engine: mPDF</small>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="section-chip-row mb-3">
                                                <span class="section-chip">Distribusi Gelanggang</span>
                                                <small class="text-muted">Aktifkan gelanggang lalu atur jumlah pool per arena.</small>
                                            </div>
                                            <?php foreach ($gelanggang as $arena) : ?>
                                                <?php $idG = (string) $arena->id_gelanggang; $oldPool = (int) old('jumlah_pool.' . $idG, 0); ?>
                                                <div class="arena-row row align-items-center mb-2">
                                                    <div class="col-12 col-lg-4">
                                                        <div class="form-check arena-check">
                                                            <input id="poolGelanggang<?= esc($idG) ?>" type="checkbox" class="checkbox-gelanggang form-check-input" value="<?= esc($idG) ?>" name="id_gelanggang[]" data-caption="#poolCaption<?= esc($idG) ?>" data-range-slider="#poolSlider<?= esc($idG) ?>" <?= $oldPool > 0 ? 'checked' : '' ?>>
                                                            <label class="form-label cursor-pointer mb-0" for="poolGelanggang<?= esc($idG) ?>"><span class="arena-name">Gelanggang <?= esc((string) ($arena->nama_gelanggang ?? $idG)) ?></span></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6 d-flex justify-content-center flex-column">
                                                        <input id="poolSlider<?= esc($idG) ?>" type="range" name="jumlah_pool[<?= esc($idG) ?>]" value="<?= esc((string) $oldPool) ?>" min="0" max="<?= esc((string) max(1, $poolCount)) ?>" class="unit-slider form-range" data-caption="#poolCaption<?= esc($idG) ?>" data-checkbox="#poolGelanggang<?= esc($idG) ?>">
                                                    </div>
                                                    <div class="col-12 col-lg-2 text-lg-end"><small class="arena-caption" id="poolCaption<?= esc($idG) ?>"><?= esc((string) $oldPool) ?> Pool</small></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-danger my-3 w-100">Buat Jadwal Seni Pool</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 opsi_penjadwalan">
                            <div class="alert alert-danger text-white ci3-info-alert" role="alert">
                                <strong class="d-block">Info</strong>
                                Geser sub kategori untuk menentukan prioritas penjadwalan. Jumlah pool slider dihitung dari sub kategori yang dipilih.
                            </div>
                            <div class="card mb-3 ci3-panel-card">
                                <div class="card-header pb-0"><h6 class="card-title">Daftar Sub Kategori Seni Pool</h6></div>
                                <div class="card-body pt-2">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input check-all-sub" type="checkbox" id="poolPilihSemua" data-target="#pool-subkategori .sub-checkbox:not(:disabled)">
                                        <label class="form-check-label" for="poolPilihSemua">Pilih Semua Sub Kategori Pool</label>
                                    </div>
                                    <p class="text-sm">Urutan drag and drop dikirim sebagai urutan `urutan_id_sub_kategori_seni[]`.</p>
                                    <ul class="list-group sortable-sub-list" id="pool-subkategori">
                                        <?php foreach ($subKategori as $row) : ?>
                                            <?php $idSub = (string) $row->id_sub_kategori_seni; $jumlahPoolSeni = (int) ($row->jumlah_pool_seni ?? 0); $isPool = ($row->sistem_penampilan ?? '') === 'pool' && $jumlahPoolSeni > 0; ?>
                                            <li class="list-group-item py-0 sub-item <?= $isPool ? '' : 'is-disabled' ?>" data-sub-id="<?= esc($idSub) ?>">
                                                <div class="row align-items-center kategori-row-shell">
                                                    <div class="col-auto pe-0"><button type="button" class="btn btn-link drag-handle" aria-label="Geser urutan sub kategori"><i class="fa fa-grip-vertical" aria-hidden="true"></i></button></div>
                                                    <div class="col px-2 py-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input sub-checkbox" id="poolSub<?= esc($idSub) ?>" type="checkbox" name="urutan_id_sub_kategori_seni[]" value="<?= esc($idSub) ?>" data-jumlah-pool="<?= esc((string) $jumlahPoolSeni) ?>" <?= $isPool ? '' : 'disabled' ?> <?= $isPool && in_array($idSub, $poolSubOld, true) ? 'checked' : '' ?>>
                                                            <label class="form-label cursor-pointer mb-0" for="poolSub<?= esc($idSub) ?>">&nbsp; <?= esc((string) (($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '-') . ' - ' . ($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-'))) ?> <span class="kelas-babak-pill"><?= esc((string) ($row->sistem_penampilan ?? '-')) ?></span> <small class="text-muted">(<?= esc((string) $jumlahPoolSeni) ?> pool)</small></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade" id="battle-pane" role="tabpanel" aria-labelledby="battle-tab">
                <form novalidate method="post" action="<?= base_url('admin/super/jadwal-seni/buat-jadwal-seni-battle-otomatis') ?>" id="formBattle" class="seni-auto-form" data-unit-label="Partai">
                    <?= csrf_field() ?>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card sticky-top ci3-panel-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3"><label class="form-label" for="battleTanggal">Tanggal</label><input type="date" id="battleTanggal" class="form-control" name="tanggal" value="<?= esc((string) old('tanggal', date('Y-m-d'))) ?>" required></div>
                                            <div class="mb-3"><label class="form-label" for="battleJamMulai">Jam Mulai :</label><input type="time" id="battleJamMulai" class="form-control" name="jam_mulai" value="<?= esc((string) old('jam_mulai', '08:00')) ?>" required></div>
                                            <div class="mb-3"><label class="form-label" for="battleJamSelesai">Jam Selesai :</label><input type="time" id="battleJamSelesai" class="form-control" name="jam_selesai" value="<?= esc((string) old('jam_selesai', '22:00')) ?>" required></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3"><label class="form-label" for="battleKeterangan">Keterangan :</label><textarea id="battleKeterangan" name="keterangan" class="form-control" rows="3"><?= esc((string) old('keterangan')) ?></textarea></div>
                                            <div class="mb-3"><label class="form-label" for="battleJenis">Jenis Penjadwalan</label><select class="form-select" name="jenis_penjadwalan" id="battleJenis" required><option value="prestasi" <?= $jenisBattle === 'prestasi' ? 'selected' : '' ?>>Prestasi</option><option value="pemasalan_seling_1" <?= $jenisBattle === 'pemasalan_seling_1' ? 'selected' : '' ?>>Pemasalan Seling 1</option><option value="pemasalan_seling_2" <?= $jenisBattle === 'pemasalan_seling_2' ? 'selected' : '' ?>>Pemasalan Seling 2</option><option value="pemasalan_seling_3" <?= $jenisBattle === 'pemasalan_seling_3' ? 'selected' : '' ?>>Pemasalan Seling 3</option></select></div>
                                            <div class="form-check form-switch mb-3"><input class="form-check-input pdf-toggle" type="checkbox" name="langsung_buat_pdf" id="battlePdf" value="1" <?= $langsungPdf === null || $langsungPdf ? 'checked' : '' ?>><label class="form-check-label" for="battlePdf">Langsung Buat Jadwal PDF</label></div>
                                            <div class="pdf-engine-group"><input type="hidden" name="pdf_library" value="mpdf"><small class="text-muted">PDF Engine: mPDF</small></div>
                                        </div>
                                        <div class="col-12">
                                            <div class="section-chip-row mb-3"><span class="section-chip">Distribusi Gelanggang</span><small class="text-muted">Aktifkan gelanggang lalu atur partai per arena.</small></div>
                                            <?php foreach ($gelanggang as $arena) : ?>
                                                <?php $idG = (string) $arena->id_gelanggang; $oldPartai = (int) old('jumlah_partai.' . $idG, 0); ?>
                                                <div class="arena-row row align-items-center mb-2">
                                                    <div class="col-12 col-lg-4"><div class="form-check arena-check"><input id="battleGelanggang<?= esc($idG) ?>" type="checkbox" class="checkbox-gelanggang form-check-input" value="<?= esc($idG) ?>" name="id_gelanggang[]" data-caption="#battleCaption<?= esc($idG) ?>" data-range-slider="#battleSlider<?= esc($idG) ?>" <?= $oldPartai > 0 ? 'checked' : '' ?>><label class="form-label cursor-pointer mb-0" for="battleGelanggang<?= esc($idG) ?>"><span class="arena-name">Gelanggang <?= esc((string) ($arena->nama_gelanggang ?? $idG)) ?></span></label></div></div>
                                                    <div class="col-12 col-lg-6 d-flex justify-content-center flex-column"><input id="battleSlider<?= esc($idG) ?>" type="range" name="jumlah_partai[<?= esc($idG) ?>]" value="<?= esc((string) $oldPartai) ?>" min="0" max="<?= esc((string) $battlePartaiEstimate) ?>" class="unit-slider form-range" data-caption="#battleCaption<?= esc($idG) ?>" data-checkbox="#battleGelanggang<?= esc($idG) ?>"></div>
                                                    <div class="col-12 col-lg-2 text-lg-end"><small class="arena-caption" id="battleCaption<?= esc($idG) ?>"><?= esc((string) $oldPartai) ?> Partai</small></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-danger my-3 w-100">Buat Jadwal Seni Battle</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 opsi_penjadwalan">
                            <div class="alert alert-danger text-white ci3-info-alert" role="alert"><strong class="d-block">Info</strong>Urutan sub kategori bisa digeser. Total partai slider mengikuti sub kategori dan babak yang dipilih.</div>
                            <div class="card mb-3 ci3-panel-card">
                                <div class="card-header pb-0"><h6 class="card-title">Babak Battle</h6></div>
                                <div class="card-body pt-2" id="battle-babak-container">
                                    <?php foreach ($babakOptions as $babak) : ?>
                                        <?php $babakId = str_replace([' ', '/'], ['_', '_per_'], strtolower((string) $babak)); ?>
                                        <div class="form-check form-check-inline"><input class="form-check-input babak-checkbox" type="checkbox" name="babak_battle_seni[]" id="battleBabak<?= esc($babakId) ?>" value="<?= esc($babak) ?>" <?= in_array((string) $babak, $battleBabakOld, true) ? 'checked' : '' ?>><label class="form-check-label" for="battleBabak<?= esc($babakId) ?>"><?= esc((string) $babak) ?></label></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="card mb-3 ci3-panel-card">
                                <div class="card-header pb-0"><h6 class="card-title">Daftar Sub Kategori Seni Battle</h6></div>
                                <div class="card-body pt-2">
                                    <div class="form-check mb-2"><input class="form-check-input check-all-sub" type="checkbox" id="battlePilihSemua" data-target="#battle-subkategori .sub-checkbox:not(:disabled)"><label class="form-check-label" for="battlePilihSemua">Pilih Semua Sub Kategori Battle</label></div>
                                    <p class="text-sm">Sub kategori non-battle ditampilkan redup agar urutan data tetap mudah dicocokkan.</p>
                                    <ul class="list-group sortable-sub-list" id="battle-subkategori">
                                        <?php foreach ($subKategori as $row) : ?>
                                            <?php $idSub = (string) $row->id_sub_kategori_seni; $isBattle = ($row->sistem_penampilan ?? '') === 'battle'; ?>
                                            <li class="list-group-item py-0 sub-item <?= $isBattle ? '' : 'is-disabled' ?>" data-sub-id="<?= esc($idSub) ?>">
                                                <div class="row align-items-center kategori-row-shell">
                                                    <div class="col-auto pe-0"><button type="button" class="btn btn-link drag-handle" aria-label="Geser urutan sub kategori"><i class="fa fa-grip-vertical" aria-hidden="true"></i></button></div>
                                                    <div class="col px-2 py-3"><div class="form-check"><input class="form-check-input sub-checkbox" id="battleSub<?= esc($idSub) ?>" type="checkbox" name="urutan_id_sub_kategori_seni[]" value="<?= esc($idSub) ?>" data-jumlah-battle="<?= esc((string) ($row->jumlah_battle_belum_jadwal ?? 0)) ?>" <?= $isBattle ? '' : 'disabled' ?> <?= in_array($idSub, $battleSubOld, true) ? 'checked' : '' ?>><label class="form-check-label" for="battleSub<?= esc($idSub) ?>">&nbsp; <?= esc((string) (($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '-') . ' - ' . ($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-'))) ?> <span class="kelas-babak-pill"><?= esc((string) ($row->sistem_penampilan ?? '-')) ?></span></label></div></div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<style>
:root{--schedule-ink:#0f172a;--schedule-muted:rgba(15,23,42,.62);--schedule-line:rgba(15,23,42,.10);--schedule-soft:rgba(220,53,69,.10);--schedule-accent:#dc3545;--schedule-sky:#ef4444;}
.ci3-schedule-card .ci3-panel-card{border:1px solid var(--schedule-line);border-radius:14px}.ci3-schedule-card .form-label{color:var(--schedule-ink);font-weight:600}.ci3-schedule-card .text-sm{color:var(--schedule-muted)}.ci3-schedule-card .form-check-input:checked{background-color:var(--schedule-accent);border-color:var(--schedule-accent)}.ci3-schedule-card .form-range::-webkit-slider-thumb{background:var(--schedule-accent)}.ci3-schedule-card .form-range::-moz-range-thumb{background:var(--schedule-accent);border-color:var(--schedule-accent)}
.hero-strip{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;padding:14px 0 6px;border-bottom:1px solid var(--schedule-line)}.hero-stats{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}.hero-stat-item{border:1px solid var(--schedule-line);border-radius:999px;padding:8px 12px;background:rgba(255,255,255,.66);backdrop-filter:blur(6px);line-height:1.1}.hero-stat-item strong{display:block;font-size:16px;color:var(--schedule-ink)}.hero-stat-label{display:block;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--schedule-muted)}.hero-stat-item.accent{border-color:rgba(220,53,69,.35);background:rgba(220,53,69,.08)}
.ci3-info-alert{background:linear-gradient(135deg,var(--schedule-sky),var(--schedule-accent));border:0;border-radius:14px}.section-chip-row{display:flex;align-items:baseline;justify-content:space-between;gap:10px}.section-chip{display:inline-flex;align-items:center;border:1px solid var(--schedule-line);border-radius:999px;padding:6px 10px;font-weight:700;font-size:12px;letter-spacing:.02em;background:rgba(255,255,255,.75)}
.arena-row{padding:10px;border:1px solid var(--schedule-line);border-radius:12px;background:rgba(255,255,255,.8)}.arena-check{padding-top:2px}.arena-name{font-weight:700}.arena-caption{color:var(--schedule-muted)}.kategori-row-shell{flex-wrap:nowrap}.drag-handle{color:var(--schedule-accent);text-decoration:none;padding:0 8px;min-height:48px;display:inline-flex;align-items:center;cursor:grab}.drag-handle:hover,.drag-handle:focus{color:var(--schedule-accent)}.drag-handle:active{cursor:grabbing}.kelas-babak-pill{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;border:1px solid rgba(15,23,42,.10);background:rgba(15,23,42,.03);color:var(--schedule-muted);font-size:12px;font-weight:600}.sortable-sub-list{min-height:280px}.sortable-sub-list>li{cursor:move;border-radius:12px}.sortable-sub-list .list-group-item{border-color:var(--schedule-line)}.sub-item.is-disabled{opacity:.46}.sub-item.ui-sortable-helper{box-shadow:0 18px 42px rgba(127,29,29,.18);border-color:rgba(220,53,69,.28)}.ui-sortable-placeholder{visibility:visible!important;background:var(--schedule-soft);border:1px dashed rgba(220,53,69,.55);min-height:54px;border-radius:12px}.ui-sortable-helper{box-shadow:0 18px 42px rgba(15,23,42,.14)}
@media(max-width:992px){.hero-strip{align-items:flex-start;flex-direction:column}.hero-stats{justify-content:flex-start}}
</style>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function(){
    if ($.fn.sortable) {
        $('.sortable-sub-list').sortable({items:'> li.sub-item',handle:'.drag-handle',placeholder:'ui-sortable-placeholder',tolerance:'pointer',distance:6,cancel:'input, label, a'});
    }

    function setSlider(slider,value,label){
        const caption = $($(slider).data('caption')).first();
        slider.value = value;
        caption.text(value + ' ' + label);
    }

    function totalChecked(form){
        const label = form.data('unit-label') || 'Partai';
        if (label === 'Pool') {
            let totalPool = 0;
            form.find('.sub-checkbox:checked').each(function(){
                totalPool += parseInt($(this).data('jumlah-pool'), 10) || 0;
            });
            return totalPool;
        }

        let totalBattle = 0;
        form.find('.sub-checkbox:checked').each(function(){
            totalBattle += parseInt($(this).data('jumlah-battle'), 10) || 0;
        });
        return totalBattle;
    }

    function updateSliders(form){
        const label = form.data('unit-label') || 'Partai';
        const total = totalChecked(form);
        const checked = form.find('.checkbox-gelanggang:checked');
        const totalGelanggang = checked.length;
        const perArena = totalGelanggang > 0 ? Math.round(total / totalGelanggang) : 0;
        checked.each(function(index){
            const slider = $($(this).data('range-slider'))[0];
            if (!slider) return;
            setSlider(slider, index < totalGelanggang - 1 ? perArena : total - (perArena * index), label);
        });
        form.find('.checkbox-gelanggang:not(:checked)').each(function(){
            const slider = $($(this).data('range-slider'))[0];
            if (slider) setSlider(slider, 0, label);
        });
    }

    $('.seni-auto-form').each(function(){
        updateSliders($(this));
    });

    $(document).on('change', '.sub-checkbox, .babak-checkbox, .checkbox-gelanggang', function(){
        updateSliders($(this).closest('form'));
    });

    $(document).on('input', '.unit-slider', function(e){
        const form = $(this).closest('form');
        const label = form.data('unit-label') || 'Partai';
        const total = totalChecked(form);
        const checkbox = $($(this).data('checkbox')).first();
        if (!checkbox.is(':checked')) { setSlider(this, 0, label); return; }
        if ((parseInt(this.value,10) || 0) > total) { setSlider(this, total, label); }
        else { setSlider(this, this.value, label); }
    });

    $('.check-all-sub').on('change', function(){
        const form = $(this).closest('form');
        $($(this).data('target')).prop('checked', $(this).is(':checked'));
        updateSliders(form);
    });

    $('.pdf-toggle').on('change', function(){
        $(this).closest('form').find('.pdf-engine-group').toggle($(this).is(':checked'));
    }).trigger('change');

    function ensure(form, selector, message){
        if (form.find(selector).filter(':checked').length > 0) return true;
        alert(message);
        return false;
    }

    $('.seni-auto-form').on('submit', function(e){
        const form = $(this);
        const isBattle = form.attr('id') === 'formBattle';
        if (!ensure(form, '.sub-checkbox:not(:disabled)', isBattle ? 'Pilih minimal satu sub kategori battle.' : 'Pilih minimal satu sub kategori pool.')) { e.preventDefault(); return; }
        if (isBattle && !ensure(form, '.babak-checkbox', 'Pilih minimal satu babak battle.')) { e.preventDefault(); return; }
        if (!ensure(form, '.checkbox-gelanggang', isBattle ? 'Pilih minimal satu gelanggang battle.' : 'Pilih minimal satu gelanggang pool.')) { e.preventDefault(); return; }
        e.preventDefault();
        Swal.fire({title:isBattle?'Buat Jadwal Seni Battle Otomatis?':'Buat Jadwal Seni Pool Otomatis?',text:'Jadwal seni akan dibuat otomatis sesuai urutan dan distribusi gelanggang yang dipilih.',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, Buat',cancelButtonText:'Batal',confirmButtonColor:'#b91c1c',cancelButtonColor:'#6b7280'}).then(function(result){ if(result.isConfirmed) e.target.submit(); });
    });
});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>

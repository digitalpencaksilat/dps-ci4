<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$gelanggang = $gelanggang ?? [];
$kelas = $kelas ?? [];
$babakOptions = $babakOptions ?? [];
$prediksiJumlahPartai = (int) ($prediksiJumlahPartai ?? max(1, count($kelas)));
$jumlahPesertaTanding = (int) ($jumlahPesertaTanding ?? 0);
$jumlahKompetisiTanding = (int) ($jumlahKompetisiTanding ?? 0);
$dataKategoriTandingGabung = $dataKategoriTandingGabung ?? [];
$kategoriLomba = $kategoriLomba ?? [];
$kelasByKategori = $kelasByKategori ?? [];
$oldBabak = old('babak_pertandingan') ?: array_map(static fn ($item) => (string) ($item->babak ?? $item), $babakOptions);
$oldJenis = (string) old('jenis_penjadwalan', 'pemasalan_seling_2');
$oldLangsungPdf = old('langsung_buat_pdf');
$oldJumlahSelang = (int) old('jumlah_selang_seling', preg_match('/pemasalan_seling_(\d+)/', $oldJenis, $mOldSelang) ? (int) $mOldSelang[1] : 2);
$oldCheckedKelas = array_map('strval', old('urutan_id_kelas_tanding') ?: []);
$oldCheckedKategori = array_map('strval', old('urutan_id_kategori_lomba') ?: []);
$oldIdGelanggang = old('id_gelanggang');
$oldJumlahPartai = old('jumlah_partai');
$gelanggangSelectedMap = [];
$jumlahPartaiMap = [];
if (is_array($oldIdGelanggang)) {
    foreach ($oldIdGelanggang as $key => $value) {
        $id = (string) (is_numeric($key) ? $key : $value);
        if ($id === '' || $id === '0') {
            continue;
        }
        $gelanggangSelectedMap[$id] = true;
        $jumlahPartaiMap[$id] = (int) ($oldJumlahPartai[$key] ?? 0);
    }
}
$showValidation = session('status') === false;
?>

<div class="admin-card ci3-schedule-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div class="hero-strip">
            <div>
                <p class="eyebrow mb-1">Pembuatan Jadwal</p>
                <h2 class="section-title h4 mb-1">Penjadwalan Otomatis Tanding</h2>
                <p class="muted-copy mb-0">Flow dan interaksi diselaraskan dengan halaman CI3, tetap berada di area Admin Super.</p>
            </div>
            <div class="hero-stats">
                <div class="hero-stat-item">
                    <span class="hero-stat-label">Peserta</span>
                    <strong><?= esc((string) $jumlahPesertaTanding) ?></strong>
                </div>
                <div class="hero-stat-item accent">
                    <span class="hero-stat-label">Partai Aktif</span>
                    <strong><?= esc((string) $prediksiJumlahPartai) ?></strong>
                </div>
                <div class="hero-stat-item">
                    <span class="hero-stat-label">Pool</span>
                    <strong><?= esc((string) $jumlahKompetisiTanding) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body px-0">
        <form novalidate id="penjadwalanForm" action="<?= base_url('admin/super/jadwal-tanding/buat-jadwal-tanding-otomatis') ?>" method="post">
            <?= csrf_field() ?>
            <div id="dragOrderFields"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card sticky-top ci3-panel-card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="col-12 p-0 mb-3">
                                                <div class="form-group">
                                                    <label class="form-label" for="tanggal">Tanggal</label>
                                                    <input type="date" id="tanggal" name="tanggal" class="form-control <?= $showValidation && ! old('tanggal') ? 'is-invalid' : '' ?>" value="<?= esc((string) old('tanggal', date('Y-m-d'))) ?>" required>
                                                    <div class="invalid-feedback">Silahkan memilih tanggal</div>
                                                </div>
                                            </div>
                                            <div class="col-12 p-0 mb-3">
                                                <div class="form-group">
                                                    <label class="form-label" for="jam_mulai">Jam Mulai :</label>
                                                    <input type="text" id="jam_mulai" class="form-control <?= $showValidation && ! old('jam_mulai') ? 'is-invalid' : '' ?>" value="<?= esc((string) old('jam_mulai', '08:00')) ?>" name="jam_mulai" required>
                                                    <div class="invalid-feedback">Silahkan memilih jam mulai</div>
                                                </div>
                                            </div>
                                            <div class="col-12 p-0 mb-3">
                                                <div class="form-group">
                                                    <label class="form-label" for="jam_selesai">Jam Selesai :</label>
                                                    <input type="text" id="jam_selesai" class="form-control <?= $showValidation && ! old('jam_selesai') ? 'is-invalid' : '' ?>" value="<?= esc((string) old('jam_selesai', '22:00')) ?>" name="jam_selesai" required>
                                                    <div class="invalid-feedback">Silahkan memilih jam selesai</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="keterangan">Keterangan :</label>
                                                <textarea id="keterangan" name="keterangan" class="form-control" rows="3"><?= esc((string) old('keterangan')) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="langsung_buat_pdf" id="langsungBuatPdf" value="1" <?= $oldLangsungPdf === null || $oldLangsungPdf ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="langsungBuatPdf">Langsung Buat Jadwal PDF</label>
                                                </div>
                                            </div>
                                            <div class="mb-3" id="pdfEngineGroup">
                                                <input type="hidden" name="pdf_library" value="mpdf">
                                                <small class="text-muted">PDF Engine: mPDF</small>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group gelanggang-group">
                                                <div class="section-chip-row mb-3">
                                                    <span class="section-chip">Distribusi Gelanggang</span>
                                                    <small class="text-muted">Aktifkan gelanggang lalu atur partai per arena.</small>
                                                </div>
                                                <?php foreach ($gelanggang as $arena) : ?>
                                                    <?php
                                                    $idG = (string) $arena->id_gelanggang;
                                                    $checked = isset($gelanggangSelectedMap[$idG]);
                                                    $jumlahPartai = $jumlahPartaiMap[$idG] ?? 0;
                                                    ?>
                                                    <div class="arena-row row align-items-center mb-2">
                                                        <div class="col-12 col-lg-4">
                                                            <div class="form-check arena-check">
                                                                <input id="checkboxGelanggang<?= esc($idG) ?>" type="checkbox" class="checkbox-gelanggang form-check-input" value="<?= esc($idG) ?>" name="id_gelanggang[<?= esc($idG) ?>]" data-caption="#rangeCaption<?= esc($idG) ?>" data-range-slider="#sliderGelanggang<?= esc($idG) ?>" <?= $checked ? 'checked' : '' ?>>
                                                                <label class="form-label cursor-pointer mb-0" for="checkboxGelanggang<?= esc($idG) ?>">
                                                                    <span class="arena-name">Gelanggang <?= esc((string) ($arena->nama_gelanggang ?? $idG)) ?></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-lg-6 d-flex justify-content-center flex-column">
                                                            <input id="sliderGelanggang<?= esc($idG) ?>" type="range" name="jumlah_partai[<?= esc($idG) ?>]" value="<?= esc((string) $jumlahPartai) ?>" min="0" max="<?= esc((string) max(1, $prediksiJumlahPartai)) ?>" class="partai-slider form-range" data-caption="#rangeCaption<?= esc($idG) ?>" data-checkbox="#checkboxGelanggang<?= esc($idG) ?>">
                                                        </div>
                                                        <div class="col-12 col-lg-2 text-lg-end">
                                                            <small class="arena-caption" id="rangeCaption<?= esc($idG) ?>"><?= esc((string) $jumlahPartai) ?> Partai</small>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-danger my-3 w-100">Buat Jadwal Tanding</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 opsi_penjadwalan">
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-danger text-white ci3-info-alert" role="alert">
                                        <strong class="d-block">Info</strong>
                                        <a data-bs-toggle="modal" href="#modalInformasiPartaiTanding" class="text-white text-decoration-underline">Klik disini</a>
                                        untuk informasi jumlah peserta dan jumlah partai pertandingan.
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3 ci3-panel-card">
                                <div class="card-body">
                                    <div class="col-md-12 mb-3">
                                        <label for="inputjenis_jadwal" class="form-label">Jenis Penjadwalan</label>
                                        <select name="jenis_penjadwalan" id="inputjenis_jadwal" class="form-select">
                                            <option value="prestasi" <?= $oldJenis === 'prestasi' ? 'selected' : '' ?>>Prestasi</option>
                                            <option value="pemasalan_seling_1" <?= $oldJenis === 'pemasalan_seling_1' ? 'selected' : '' ?>>pemasalan seling 1</option>
                                            <option value="pemasalan_seling_2" <?= $oldJenis === 'pemasalan_seling_2' ? 'selected' : '' ?>>pemasalan seling 2</option>
                                            <option value="pemasalan_seling_3" <?= $oldJenis === 'pemasalan_seling_3' ? 'selected' : '' ?>>pemasalan seling 3</option>
                                        </select>
                                        <input type="hidden" name="jumlah_selang_seling" id="jumlahSelangSeling" value="<?= esc((string) max(1, $oldJumlahSelang)) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3 ci3-panel-card">
                                <div class="card-header pb-0">
                                    <h6 class="card-title">Daftar Kelas dan Babak Yang Akan Dijadwalkan</h6>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="col-12 mb-3">
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="form-label">Babak Pertandingan</p>
                                            </div>
                                        </div>
                                        <div class="row" id="container_checkbox_babak_pertandingan">
                                            <div class="col-12">
                                                <?php foreach ($babakOptions as $babakItem) : ?>
                                                    <?php
                                                    $babakLabel = (string) ($babakItem->babak ?? $babakItem);
                                                    $babakData = str_replace(' ', '-', str_replace('/', '-per-', strtolower($babakLabel)));
                                                    $babakId = str_replace(' ', '_', str_replace('/', '_per_', strtolower($babakLabel)));
                                                    ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input checkbox-babak-pertandingan" data-babak-pertandingan="<?= esc($babakData) ?>" value="<?= esc($babakLabel) ?>" name="babak_pertandingan[]" type="checkbox" id="babak_<?= esc($babakId) ?>" <?= (empty($oldBabak) || in_array($babakLabel, $oldBabak, true)) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="babak_<?= esc($babakId) ?>"><?= esc($babakLabel) ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkbox_pilih_semua_pemasalan">
                                            <label class="form-check-label" for="checkbox_pilih_semua_pemasalan">Pilih Semua Kelas Pemasalan</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="checkbox_pilih_semua_prestasi">
                                            <label class="form-check-label" for="checkbox_pilih_semua_prestasi">Pilih Semua Kelas Prestasi</label>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <p class="text-sm">Jumlah partai dibawah ini hanya mewakili pertandingan yang BELUM DIJADWALKAN saja</p>
                                            </div>
                                        </div>

                                        <div class="accordion" id="accordionKategoriLomba">
                                            <ul class="list-group" id="urutan_id_kelas_tanding">
                                                <?php foreach ($kategoriLomba as $kategori) : ?>
                                                    <?php
                                                    $idKategori = (string) $kategori->id_kategori_lomba;
                                                    $kelasKategori = $kelasByKategori[$idKategori] ?? [];
                                                    $jenisPerlombaan = (string) ($kategori->jenis_perlombaan ?? 'prestasi');
                                                    $jumlahPartaiKategori = 0;
                                                    foreach ($kelasKategori as $kelasItem) {
                                                        $jumlahPartaiKategori += (int) ($kelasItem->jumlah_partai_tanding_belum_dijadwalkan ?? 0);
                                                    }
                                                    $kategoriChecked = in_array($idKategori, $oldCheckedKategori, true);
                                                    ?>
                                                    <li class="list-group-item py-0 category-item" data-kategori-id="<?= esc($idKategori) ?>">
                                                        <div class="accordion-item border-0">
                                                            <div class="accordion-header" id="kategori_lomba_heading_collapse_<?= esc($idKategori) ?>">
                                                                <div class="row align-items-center kategori-row-shell">
                                                                    <div class="col-auto pe-0">
                                                                        <button type="button" class="btn btn-link drag-handle" aria-label="Geser urutan kategori" title="Geser untuk ubah urutan kategori">
                                                                            <i class="fa fa-grip-vertical" aria-hidden="true"></i>
                                                                        </button>
                                                                    </div>
                                                                    <div class="col px-2 pb-2 py-3">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input checkbox_kategori_<?= esc($jenisPerlombaan) ?>" id="id_kategori_lomba<?= esc($idKategori) ?>" type="checkbox" name="urutan_id_kategori_lomba[]" value="<?= esc($idKategori) ?>" <?= $kategoriChecked ? 'checked' : '' ?>>
                                                                            <label class="form-check-label" for="id_kategori_lomba<?= esc($idKategori) ?>">&nbsp; <?= esc((string) (($kategori->nama_kategori_usia ?? 'Kategori') . ' ' . ucwords((string) ($kategori->jenis_kelamin ?? '')) . ' (' . $jumlahPartaiKategori . ' Partai)')) ?></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-auto ps-0">
                                                                        <button class="accordion-button font-weight-bold collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#kategori_lomba_collapse<?= esc($idKategori) ?>" aria-expanded="false" aria-controls="kategori_lomba_collapse<?= esc($idKategori) ?>">
                                                                            <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                                                                            <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div id="kategori_lomba_collapse<?= esc($idKategori) ?>" class="accordion-collapse collapse" aria-labelledby="kategori_lomba_heading_collapse_<?= esc($idKategori) ?>" data-bs-parent="#accordionKategoriLomba">
                                                                <ul class="list-group border-0 mt-2 ps-3">
                                                                    <?php foreach ($kelasKategori as $kelasItem) : ?>
                                                                        <?php
                                                                        $idKelas = (string) $kelasItem->id_kelas_tanding;
                                                                        $jumlahPerBabak = $kelasItem->jumlah_pertandingan_per_babak ?? false;
                                                                        $dataAttrs = '';
                                                                        if (is_array($jumlahPerBabak)) {
                                                                            foreach ($jumlahPerBabak as $babakNama => $jumlahPertandingan) {
                                                                                $dataKey = str_replace(' ', '-', str_replace('/', '-per-', strtolower((string) $babakNama)));
                                                                                $dataAttrs .= ' data-jumlah-partai-' . $dataKey . '="' . esc((string) $jumlahPertandingan) . '"';
                                                                            }
                                                                        }
                                                                        ?>

                                                                        <li class="list-group-item py-0 border-0 kelas-item" data-kelas-id="<?= esc($idKelas) ?>" data-kategori-id="<?= esc($idKategori) ?>">
                                                                            <div class="form-check py-1">
                                                                                <input class="form-check-input kelas-checkbox"<?= $dataAttrs ?> data-jumlah-seluruh-partai="<?= esc((string) ($kelasItem->jumlah_partai_tanding_belum_dijadwalkan ?? 0)) ?>" id="id_kelas_tanding<?= esc($idKelas) ?>" type="checkbox" name="urutan_id_kelas_tanding[]" value="<?= esc($idKelas) ?>" <?= in_array($idKelas, $oldCheckedKelas, true) ? 'checked' : '' ?>>
                                                                                <label class="form-check-label" for="id_kelas_tanding<?= esc($idKelas) ?>">
                                                                                    &nbsp; <?= esc((string) (($kelasItem->label ?? ('Kelas ' . $idKelas)) . ' (Total ' . ((int) ($kelasItem->jumlah_partai_tanding_belum_dijadwalkan ?? 0)) . ' Partai)')) ?>
                                                                                    <?php if (is_array($jumlahPerBabak) && $jumlahPerBabak !== []) : ?>
                                                                                        <span class="kelas-babak-summary">
                                                                                            <?php foreach ($jumlahPerBabak as $babakNama => $jumlahPertandingan) : ?>
                                                                                                <span class="kelas-babak-pill"><?= esc((string) $babakNama) ?>: <?= esc((string) $jumlahPertandingan) ?></span>
                                                                                            <?php endforeach; ?>
                                                                                        </span>
                                                                                    <?php endif; ?>
                                                                                </label>
                                                                            </div>
                                                                        </li>
                                                                    <?php endforeach; ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalInformasiPartaiTanding" tabindex="-1" aria-labelledby="modalInformasiPartaiTandingTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInformasiPartaiTandingTitle">Informasi Partai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <p>Total Peserta</p>
                                        <h6 class="display-4 fw-bolder"><?= esc((string) $jumlahPesertaTanding) ?></h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card mb-3 bg-primary text-white">
                                    <div class="card-body">
                                        <p>Jumlah Partai</p>
                                        <h6 class="display-4 fw-bolder text-white"><?= esc((string) $prediksiJumlahPartai) ?></h6>
                                        <small>Partai BYE tidak dihitung</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <p>Total Pool</p>
                                        <h6 class="display-4 fw-bolder"><?= esc((string) $jumlahKompetisiTanding) ?></h6>
                                        <small>Pool dengan 0 peserta tidak dihitung</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <table class="table w-100" id="tabelDataKategoriTanding">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Jumlah <br>Peserta</th>
                                    <th>Jumlah <br>Pool</th>
                                    <th>Jumlah <br>Partai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataKategoriTandingGabung as $namaKategori => $dataKategori) : ?>
                                    <tr>
                                        <td class="text-wrap"><?= esc((string) $namaKategori) ?></td>
                                        <td class="text-end"><?= esc((string) ($dataKategori['jumlah_peserta_tanding'] ?? 0)) ?></td>
                                        <td class="text-end"><?= esc((string) ($dataKategori['jumlah_pool_tanding'] ?? 0)) ?></td>
                                        <td class="text-end"><?= esc((string) ($dataKategori['jumlah_partai_tanding'] ?? 0)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<style>
:root {
    --schedule-ink: #0f172a;
    --schedule-muted: rgba(15, 23, 42, 0.62);
    --schedule-line: rgba(15, 23, 42, 0.10);
    /* Match project brand: red accents (instead of CI3 blue). */
    --schedule-soft: rgba(220, 53, 69, 0.10);
    --schedule-accent: #dc3545;
    --schedule-sky: #ef4444;
}

.ci3-schedule-card .ci3-panel-card { border: 1px solid var(--schedule-line); border-radius: 14px; }
.ci3-schedule-card .form-label { color: var(--schedule-ink); font-weight: 600; }
.ci3-schedule-card .text-sm { color: var(--schedule-muted); }
.ci3-schedule-card .form-check-input:checked { background-color: var(--schedule-accent); border-color: var(--schedule-accent); }
.ci3-schedule-card .form-switch .form-check-input:checked { background-color: var(--schedule-accent); border-color: var(--schedule-accent); }
.ci3-schedule-card .form-range::-webkit-slider-thumb { background: var(--schedule-accent); }
.ci3-schedule-card .form-range::-moz-range-thumb { background: var(--schedule-accent); border-color: var(--schedule-accent); }
.ci3-schedule-card .btn-primary,
.ci3-schedule-card .bg-primary { background-color: var(--schedule-accent) !important; border-color: var(--schedule-accent) !important; }

.hero-strip {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 0 6px;
    border-bottom: 1px solid var(--schedule-line);
}
.hero-stats { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
.hero-stat-item {
    border: 1px solid var(--schedule-line);
    border-radius: 999px;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.66);
    backdrop-filter: blur(6px);
    line-height: 1.1;
}
.hero-stat-item strong { display: block; font-size: 16px; color: var(--schedule-ink); }
.hero-stat-label { display: block; font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--schedule-muted); }
.hero-stat-item.accent { border-color: rgba(220, 53, 69, 0.35); background: rgba(220, 53, 69, 0.08); }

.ci3-info-alert { background: linear-gradient(135deg, var(--schedule-sky), var(--schedule-accent)); border: 0; border-radius: 14px; }

.section-chip-row { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; }
.section-chip {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--schedule-line);
    border-radius: 999px;
    padding: 6px 10px;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.02em;
    background: rgba(255, 255, 255, 0.75);
}

.gelanggang-group { padding-top: 6px; }
.arena-row {
    padding: 10px 10px;
    border: 1px solid var(--schedule-line);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.8);
}
.arena-check { padding-top: 2px; }
.arena-name { font-weight: 700; }
.arena-caption { color: var(--schedule-muted); }
.kategori-row-shell { flex-wrap: nowrap; }
.category-item .accordion-header { cursor: default; }
.drag-handle {
    color: var(--schedule-accent);
    text-decoration: none;
    padding: 0 8px;
    min-height: 48px;
    display: inline-flex;
    align-items: center;
    cursor: grab;
}
.drag-handle:hover,
.drag-handle:focus { color: var(--schedule-accent); }
.drag-handle:active { cursor: grabbing; }
.category-item .accordion-item { border-radius: 12px; transition: box-shadow 0.2s ease, border-color 0.2s ease; }
.category-item.ui-sortable-helper .accordion-item { box-shadow: 0 18px 42px rgba(127, 29, 29, 0.18); border-color: rgba(220, 53, 69, 0.28); }

.kelas-babak-summary { display: inline-flex; flex-wrap: wrap; gap: 6px; margin-left: 10px; }
.kelas-babak-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.10);
    background: rgba(15, 23, 42, 0.03);
    color: var(--schedule-muted);
    font-size: 12px;
    font-weight: 600;
}

#urutan_id_kelas_tanding { min-height: 280px; }
#urutan_id_kelas_tanding > li { cursor: move; border-radius: 12px; }
#urutan_id_kelas_tanding .list-group-item { border-color: var(--schedule-line); }
#urutan_id_kelas_tanding .accordion-button::after { display: none; }
#urutan_id_kelas_tanding .accordion-button { background: transparent; box-shadow: none; }

.ui-sortable-placeholder {
    visibility: visible !important;
    background: var(--schedule-soft);
    border: 1px dashed rgba(220, 53, 69, 0.55);
    min-height: 54px;
    border-radius: 12px;
}
.ui-sortable-helper { box-shadow: 0 18px 42px rgba(15, 23, 42, 0.14); }

@media (max-width: 992px) {
    .hero-strip { align-items: flex-start; flex-direction: column; }
    .hero-stats { justify-content: flex-start; }
}
</style>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function () {
    let totalPartai = 0;

    if ($.fn.DataTable) {
        $('#tabelDataKategoriTanding').DataTable({
            paging: false,
            lengthChange: false,
            searching: false,
            ordering: true,
            info: true,
            responsive: true,
            autoWidth: true,
        });
    }

    if ($.fn.sortable) {
        // Allow re-ordering categories (top-level LI). This controls scheduling priority order.
        $('#urutan_id_kelas_tanding').sortable({
            items: '> li.category-item',
            handle: '.drag-handle',
            placeholder: 'ui-sortable-placeholder',
            tolerance: 'pointer',
            distance: 6,
            cancel: 'input, label, .accordion-button, .accordion-collapse, a',
            update: syncDragOrderFields,
        });
    }

    function syncPdfOptions() {
        $('#pdfEngineGroup').toggle($('#langsungBuatPdf').is(':checked'));
    }

    function syncJenisPenjadwalan() {
        const value = $('#inputjenis_jadwal').val() || 'prestasi';
        const match = value.match(/pemasalan_seling_(\d+)/);
        $('#jumlahSelangSeling').val(match ? match[1] : 1);
    }

    function setSlider(slider, value) {
        const captionSelector = $(slider).data('caption');
        const caption = $(captionSelector).first();
        slider.value = value;
        caption.text(value + ' Partai');
    }

    function hitungJumlahPartaiChecked() {
        let jumlahPartaiChecked = 0;
        $('#urutan_id_kelas_tanding').find('input[name="urutan_id_kelas_tanding[]"]').each(function () {
            const checkboxKelas = $(this);
            if (!checkboxKelas.is(':checked')) {
                return;
            }

            let partaiKelas = 0;
            $('.checkbox-babak-pertandingan').each(function () {
                const checkboxBabak = $(this);
                if (!checkboxBabak.is(':checked')) {
                    return;
                }

                const labelData = checkboxBabak.data('babak-pertandingan');
                const value = checkboxKelas.data('jumlah-partai-' + labelData);
                if (value !== undefined) {
                    partaiKelas += parseInt(value, 10) || 0;
                }
            });

            if (partaiKelas === 0) {
                partaiKelas = parseInt(checkboxKelas.data('jumlah-seluruh-partai'), 10) || 0;
            }

            jumlahPartaiChecked += partaiKelas;
        });

        totalPartai = jumlahPartaiChecked;
        return jumlahPartaiChecked;
    }

    function updateSlider(total) {
        const checked = $('.checkbox-gelanggang:checked');
        const totalGelanggang = checked.length;
        const jumlahPerGelanggang = totalGelanggang > 0 ? Math.round(total / totalGelanggang) : 0;

        checked.each(function (index) {
            const sliderSelector = $(this).data('range-slider');
            const slider = $(sliderSelector)[0];
            if (!slider) {
                return;
            }
            if (index < totalGelanggang - 1) {
                setSlider(slider, jumlahPerGelanggang);
            } else {
                setSlider(slider, total - (jumlahPerGelanggang * index));
            }
        });

        $('.checkbox-gelanggang:not(:checked)').each(function () {
            const sliderSelector = $(this).data('range-slider');
            const slider = $(sliderSelector)[0];
            if (slider) {
                setSlider(slider, 0);
            }
        });
    }

    function syncDragOrderFields() {
        const container = $('#dragOrderFields');
        container.empty();

        $('#urutan_id_kelas_tanding > li.category-item').each(function () {
            const kategoriId = $(this).data('kategori-id');
            if (kategoriId !== undefined) {
                container.append($('<input>', {
                    type: 'hidden',
                    name: 'urutan_kategori_drag[]',
                    value: kategoriId,
                }));
            }

            $(this).find('input[name="urutan_id_kelas_tanding[]"]:checked').each(function () {
                container.append($('<input>', {
                    type: 'hidden',
                    name: 'ordered_kelas_from_drag[]',
                    value: $(this).val(),
                }));
            });
        });
    }

    $(document).on('input', '.partai-slider', function (e) {
        const checkbox = $($(e.target).data('checkbox')).first();
        if (!checkbox.is(':checked')) {
            setSlider(e.target, 0);
            return;
        }

        setSlider(e.target, e.target.value);
        const sliderLain = $('input.partai-slider').not(e.target);

        if (parseInt(e.target.value, 10) > totalPartai) {
            e.target.value = totalPartai;
            sliderLain.each(function () { setSlider(this, 0); });
            setSlider(e.target, totalPartai);
            return;
        }

        let totalPartaiSliderLain = 0;
        sliderLain.each(function () {
            totalPartaiSliderLain += parseInt(this.value, 10) || 0;
        });

        const sisa = totalPartai - (parseInt(e.target.value, 10) || 0);
        sliderLain.each(function (index) {
            if ((parseInt(this.value, 10) || 0) <= 0) {
                return;
            }
            if (index < sliderLain.length - 1 && totalPartaiSliderLain > 0) {
                const presentase = (parseInt(this.value, 10) || 0) / totalPartaiSliderLain;
                setSlider(this, Math.round(presentase * sisa));
            } else {
                let nilaiSliderTerakhir = 0;
                $('input.partai-slider').not(this).each(function () {
                    nilaiSliderTerakhir += parseInt(this.value, 10) || 0;
                });
                setSlider(this, totalPartai - nilaiSliderTerakhir);
            }
        });
    });

    $('#penjadwalanForm').on('change', 'input.checkbox-gelanggang', function () {
        updateSlider(hitungJumlahPartaiChecked());
    });

    $('#urutan_id_kelas_tanding').on('change', 'input[name="urutan_id_kategori_lomba[]"]', function () {
        const idKategori = $(this).val();
        const isChecked = $(this).is(':checked');
        const checkboxKelasTanding = $('#kategori_lomba_collapse' + idKategori).find('[name="urutan_id_kelas_tanding[]"]');
        checkboxKelasTanding.each((_, el) => {
            $(el).prop('checked', isChecked).trigger('change');
        });
    });

    $('#urutan_id_kelas_tanding').on('change', 'input[name="urutan_id_kelas_tanding[]"]', function () {
        syncDragOrderFields();
        updateSlider(hitungJumlahPartaiChecked());
    });

    $('#container_checkbox_babak_pertandingan').on('change', 'input[name="babak_pertandingan[]"]', function () {
        updateSlider(hitungJumlahPartaiChecked());
    });

    $('#penjadwalanForm').on('change', '#checkbox_pilih_semua_pemasalan', function () {
        $('.checkbox_kategori_pemasalan').each((_, el) => {
            $(el).prop('checked', $(this).is(':checked')).trigger('change');
        });
    });

    $('#penjadwalanForm').on('change', '#checkbox_pilih_semua_prestasi', function () {
        $('.checkbox_kategori_prestasi').each((_, el) => {
            $(el).prop('checked', $(this).is(':checked')).trigger('change');
        });
    });

    $('#inputjenis_jadwal').on('change', syncJenisPenjadwalan);
    $('#langsungBuatPdf').on('change', syncPdfOptions);
    $('#penjadwalanForm').on('submit', syncDragOrderFields);

    syncJenisPenjadwalan();
    syncPdfOptions();
    syncDragOrderFields();
    updateSlider(hitungJumlahPartaiChecked());
});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="text-center">
        <p class="eyebrow mb-1">Sekretariat</p>
        <h3 class="section-title h4 mb-0">Nomor Sertifikat Pemenang</h3>
    </div>
</section>

<section class="admin-card">
    <ul class="nav nav-tabs justify-content-center mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nomor-sertifikat-tanding" type="button" role="tab">Kategori Tanding</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nomor-sertifikat-seni" type="button" role="tab">Kategori Seni</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="nomor-sertifikat-tanding" role="tabpanel">
            <div class="py-2">
                <?= view('admin/sekretariat/nomor_sertifikat/_tabel_tanding', ['rows' => $dataPerolehanMedaliTanding ?? []]) ?>
            </div>
        </div>
        <div class="tab-pane fade" id="nomor-sertifikat-seni" role="tabpanel">
            <div class="py-2">
                <?= view('admin/sekretariat/nomor_sertifikat/_tabel_seni', ['rows' => $dataPerolehanMedaliSeni ?? [], 'pesertaSeniRows' => $dataPesertaSeni ?? []]) ?>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

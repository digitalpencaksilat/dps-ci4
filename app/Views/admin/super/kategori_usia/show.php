<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Kategori Lomba</p>
            <h2 class="section-title h3 mb-3">Detail Kategori Usia</h2>
            <p class="muted-copy mb-0">Ringkasan data kategori usia untuk verifikasi sebelum edit atau hapus.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('admin/super/kategori-usia/' . $row->id_kategori_usia . '/edit') ?>" class="btn btn-outline-light rounded-pill align-self-start">Edit</a>
            <a href="<?= base_url('admin/super/kategori-usia') ?>" class="btn btn-outline-secondary rounded-pill align-self-start">Kembali</a>
        </div>
    </div>
</section>

<section class="admin-card">
    <div class="row g-3">
        <?php foreach ([
            'ID' => $row->id_kategori_usia ?? '-',
            'Nama Kategori Usia' => $row->nama_kategori_usia ?? '-',
            'Jenis Kelamin' => $row->jenis_kelamin ?? '-',
            'Min Umur' => $row->min_umur ?? '-',
            'Max Umur' => $row->max_umur ?? '-',
            'Acuan Tanggal' => $row->acuan_tanggal ?? '-',
        ] as $label => $value) : ?>
            <div class="col-12 col-md-4">
                <div class="admin-card h-100">
                    <div class="small muted-copy"><?= esc($label) ?></div>
                    <div class="fw-semibold mt-1 text-capitalize"><?= esc((string) $value) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?= $this->endSection() ?>

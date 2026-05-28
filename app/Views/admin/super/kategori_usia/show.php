<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Kategori Lomba',
    'title' => 'Detail Kategori Usia',
    'description' => 'Ringkasan data kategori usia untuk verifikasi sebelum edit atau hapus.',
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/kategori-usia'),
            'label' => 'Kembali',
            'class' => 'btn-outline-secondary',
        ],
        [
            'tag' => 'a',
            'href' => base_url('admin/super/kategori-usia/' . $row->id_kategori_usia . '/edit'),
            'label' => 'Edit',
            'class' => 'btn-danger',
        ],
    ],
]) ?>

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

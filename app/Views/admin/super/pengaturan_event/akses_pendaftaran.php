<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Pengaturan Event',
    'title' => 'Akses Pendaftaran Peserta',
    'description' => 'Pengaturan ini mengatur apakah kontingen dapat mendaftar, login, input atlet, memilih kategori, dan melakukan pembayaran.',
    'actions' => [
        [
            'tag' => 'a',
            'href' => base_url('admin/super/dashboard-pengaturan-event'),
            'label' => 'Kembali ke Dashboard',
            'class' => 'btn-outline-secondary',
        ],
    ],
]) ?>

<?php
$settingContext = [
    'perbolehkan_kontingen_mendaftar' => [
        'impact' => 'Menentukan apakah kontingen baru dapat membuat akun/registrasi ke event.',
        'warning' => 'Jika dimatikan saat pendaftaran berjalan, kontingen baru tidak bisa masuk ke alur registrasi.',
        'critical' => true,
    ],
    'perbolehkan_kontingen_login' => [
        'impact' => 'Mengatur akses login semua kontingen ke dashboard mereka.',
        'warning' => 'Mematikan ini akan mengunci kontingen dari dashboard, termasuk melihat pembayaran dan data peserta.',
        'critical' => true,
    ],
    'perbolehkan_kontingen_input_atlet' => [
        'impact' => 'Mengizinkan kontingen menambah data atlet dan arsip peserta.',
        'warning' => 'Matikan hanya setelah masa input atlet selesai agar data tidak berubah mendadak.',
        'critical' => true,
    ],
    'perbolehkan_kontingen_memilih_kategori' => [
        'impact' => 'Mengizinkan kontingen mendaftarkan atlet ke kategori tanding/seni.',
        'warning' => 'Perubahan ini berdampak langsung pada jumlah peserta kompetisi.',
        'critical' => true,
    ],
    'perbolehkan_kontingen_melunasi_pembayaran' => [
        'impact' => 'Mengizinkan kontingen membuat atau menyelesaikan transaksi pembayaran.',
        'warning' => 'Jika dimatikan, kontingen tidak bisa melunasi biaya meskipun data sudah lengkap.',
        'critical' => true,
    ],
    'perbolehkan_undur_diri_atlet' => [
        'impact' => 'Mengizinkan kontingen menarik atlet dari kategori yang sudah dipilih.',
        'warning' => 'Periksa jadwal/bagan sebelum membuka opsi ini saat kompetisi sudah disusun.',
        'critical' => false,
    ],
    'perbolehkan_ganti_atlet_dan_kategori' => [
        'impact' => 'Mengizinkan perubahan atlet dan kategori setelah pendaftaran awal.',
        'warning' => 'Perubahan bisa memengaruhi pool, jadwal, dan kebutuhan verifikasi ulang.',
        'critical' => true,
    ],
    'perbolehkan_edit_biodata' => [
        'impact' => 'Mengizinkan kontingen memperbarui biodata atlet yang sudah tersimpan.',
        'warning' => 'Matikan setelah data final untuk mengurangi risiko perbedaan dokumen dan data event.',
        'critical' => false,
    ],
    'perbolehkan_kontingen_input_official' => [
        'impact' => 'Mengizinkan kontingen mengelola data official/pendamping.',
        'warning' => 'Pastikan kuota dan persyaratan official sudah jelas sebelum dibuka.',
        'critical' => false,
    ],
];
?>

<section class="admin-card">
    <form action="<?= base_url('admin/super/pengaturan-event/akses-pendaftaran/update') ?>" method="post" class="row g-3">
        <?= csrf_field() ?>

        <?php foreach (($fields ?? []) as $field => $label) : ?>
            <?php
            $checked = (bool) (old($field) !== null ? (old($field) !== '0' && old($field) !== '') : (($values ?? [])[$field] ?? false));
            $context = $settingContext[$field] ?? [
                'impact' => 'Mengubah akses operasional yang digunakan kontingen pada event ini.',
                'warning' => 'Pastikan perubahan sudah disepakati panitia sebelum disimpan.',
                'critical' => false,
            ];
            ?>
            <div class="col-12 col-xl-6">
                <label class="setting-card <?= $checked ? 'is-active' : 'is-inactive' ?> <?= $context['critical'] ? 'is-critical' : '' ?>" for="<?= esc($field) ?>">
                    <input class="setting-card-input" type="checkbox" value="1" id="<?= esc($field) ?>" name="<?= esc($field) ?>" <?= $checked ? 'checked' : '' ?>>
                    <span class="setting-card-main">
                        <span class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <span>
                                <span class="setting-card-title"><?= esc($label) ?></span>
                                <span class="setting-card-impact"><?= esc($context['impact']) ?></span>
                            </span>
                            <span class="setting-status-badge <?= $checked ? 'active' : 'inactive' ?>">
                                <?= $checked ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </span>
                        <span class="setting-card-warning <?= $context['critical'] ? 'text-danger' : 'text-muted' ?>">
                            <i class="fas fa-triangle-exclamation me-1"></i><?= esc($context['warning']) ?>
                        </span>
                    </span>
                </label>
                <?php if (! empty(($errors ?? [])[$field])) : ?>
                    <div class="form-text text-danger mt-1"><?= esc((string) $errors[$field]) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="col-12 d-flex flex-wrap gap-2 mt-2">
            <button type="submit" class="btn btn-danger rounded-pill">Simpan Pengaturan</button>
            <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-secondary rounded-pill">Batal</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

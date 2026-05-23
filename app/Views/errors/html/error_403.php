<?= view('shared_sections/dps_error_panel', [
    'code' => '403',
    'title' => 'Akses Ditolak',
    'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
    'actionUrl' => base_url('/'),
    'actionLabel' => 'Kembali',
    'showHome' => true,
]) ?>

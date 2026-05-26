<?= view('shared_sections/dps_error_panel', [
    'code' => '404',
    'title' => 'Halaman Tidak Ditemukan',
    'message' => ENVIRONMENT !== 'production' ? (string) $message : lang('Errors.sorryCannotFind'),
    'actionUrl' => base_url('/'),
    'actionLabel' => 'Kembali',
    'showHome' => true,
]) ?>

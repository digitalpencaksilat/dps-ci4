<?= view('shared_sections/dps_error_panel', [
    'code' => '400',
    'title' => 'Permintaan Tidak Valid',
    'message' => ENVIRONMENT !== 'production' ? (string) $message : lang('Errors.sorryBadRequest'),
    'actionUrl' => base_url('/'),
    'actionLabel' => 'Kembali',
    'showHome' => true,
]) ?>

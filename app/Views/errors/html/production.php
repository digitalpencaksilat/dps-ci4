<?= view('shared_sections/dps_error_panel', [
    'code' => '500',
    'title' => 'Terjadi Gangguan',
    'message' => lang('Errors.weHitASnag'),
    'actionUrl' => base_url('/'),
    'actionLabel' => 'Kembali',
    'showHome' => true,
]) ?>

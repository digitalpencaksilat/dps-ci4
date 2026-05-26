<?php
$status = (string) ($partai_seni->status_penampilan ?? '-');
$class = match ($status) {
    'belum_tampil' => 'bg-warning text-dark',
    'sudah_tampil' => 'bg-success',
    '-' => 'bg-secondary',
    default => 'bg-info',
};

$label = $status === 'sudah_tampil' ? 'Ubah Tampil' : $status;
?>
<span class="badge <?= esc($class) ?>"><?= esc($label) ?></span>

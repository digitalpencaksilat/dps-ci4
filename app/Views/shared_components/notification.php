<?php
$message = session()->getFlashdata('message');
$status = session()->getFlashdata('status');

if ($message === null || $message === '') {
    return;
}

$alertClass = $status === false ? 'alert-danger' : 'alert-success';
?>

<?php if (is_array($message)) : ?>
    <div class="alert <?= esc($alertClass) ?> border-0 rounded-4" role="alert">
        <ul class="mb-0 ps-3">
            <?php foreach ($message as $item) : ?>
                <li><?= esc((string) $item) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else : ?>
    <div class="alert <?= esc($alertClass) ?> border-0 rounded-4" role="alert">
        <?= esc((string) $message) ?>
    </div>
<?php endif; ?>

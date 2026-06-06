<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat</title>
    <?= view('print/sertifikat/styles/sertifikat', ['layout' => $layout ?? [], 'hide_bg' => $hide_bg ?? false]) ?>
</head>
<body>
    <?= view($main_view ?? '', get_defined_vars()) ?>
</body>
</html>

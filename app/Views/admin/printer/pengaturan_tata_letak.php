<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/admin/certificate_editor.css') ?>">

<?= view('admin/super/_action_toolbar', [
    'eyebrow'     => 'Printer',
    'title'       => 'Tata Letak Sertifikat',
    'description' => 'Atur posisi setiap elemen pada sertifikat. Seret elemen di kanvas lalu simpan.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/printer/preview'), 'label' => 'Preview', 'class' => 'btn-outline-secondary', 'attrs' => ['target' => '_blank']],
        ['tag' => 'button', 'label' => 'Simpan Perubahan', 'class' => 'btn-danger', 'type' => 'submit', 'attrs' => ['form' => 'form-certificate']],
    ],
]) ?>

<form action="<?= base_url('admin/printer/simpan-tata-letak') ?>" method="POST" id="form-certificate">
    <?= csrf_field() ?>
    <section class="admin-card">
        <div class="certificate-editor-container">
            <div class="editor-canvas-wrapper" id="editor-wrapper">
                <div id="certificate-canvas" class="certificate-canvas"
                     style="<?= $backgroundUrl ? 'background-image:url(' . esc($backgroundUrl) . ')' : '' ?>">
                    <?php foreach (['nomor', 'nama', 'kategori', 'kontingen', 'sekolah', 'qrcode'] as $el) : ?>
                    <div id="editor-<?= $el ?>" class="editable-element" data-id="<?= $el ?>">
                        <span class="element-label"><?= ucfirst($el) ?></span>
                        <div class="content" style="width:100%;">
                            <?= $el === 'qrcode' ? '<i class="fas fa-qrcode text-dark"></i>' : ucfirst($el) . ' Sertifikat' ?>
                        </div>
                        <span class="hidden-badge text-uppercase">Tersembunyi</span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="zoom-controls">
                    <button type="button" class="zoom-btn" id="zoom-out"><i class="fas fa-minus"></i></button>
                    <span id="zoom-level">60%</span>
                    <button type="button" class="zoom-btn" id="zoom-in"><i class="fas fa-plus"></i></button>
                </div>
            </div>

            <div class="editor-sidebar" id="sidebar-container">
                <div id="property-sidebar" class="sidebar-content">
                    <div class="text-center text-muted mt-5">
                        <i class="fas fa-mouse-pointer d-block mb-3" style="font-size:2rem;"></i>
                        Pilih elemen di kanvas untuk mengatur properti
                    </div>
                </div>
                <div class="d-none">
                    <?php foreach ($layout as $key => $value) : ?>
                        <input type="text" name="<?= esc($key) ?>" value="<?= esc($value) ?>" data-layout-key="<?= esc($key) ?>">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</form>

<script src="<?= base_url('assets/js/admin/certificate_editor.js') ?>"></script>
<?= $this->endSection() ?>

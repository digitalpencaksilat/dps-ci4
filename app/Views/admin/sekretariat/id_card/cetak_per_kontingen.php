<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Tools / ID Card',
    'title' => 'Cetak Per Kontingen',
    'description' => 'Pilih kontingen yang ingin dicetak ID Card-nya. Semua peserta tanding dan seni dalam kontingen terpilih akan dimasukkan ke batch.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/sekretariat/id-card'), 'label' => 'Kembali', 'class' => 'btn-outline-secondary'],
    ],
]) ?>

<section class="admin-card">
    <form action="<?= base_url('admin/sekretariat/id-card/proses-cetak-batch') ?>" method="post" target="id-card-print-iframe" id="formBatchKontingen">
        <?= csrf_field() ?>
        <div class="p-3">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Pilih Kontingen</label>
                    <div class="mb-2">
                        <label class="form-check-label me-3">
                            <input type="checkbox" class="form-check-input select-all-checkbox" data-target=".kontingen-checkbox"> Pilih Semua
                        </label>
                    </div>
                    <div class="row g-2" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach (($kontingenRows ?? []) as $row) : ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label class="form-check-label w-100 py-2 px-3 border rounded">
                                    <input type="checkbox" class="form-check-input me-2 kontingen-checkbox" name="id_kontingen[]" value="<?= esc((string) $row->id_kontingen) ?>" data-tanding="<?= (int) ($row->jml_tanding ?? 0) ?>" data-seni="<?= (int) ($row->jml_seni ?? 0) ?>" data-name="<?= esc($row->nama_kontingen) ?>">
                                    <?= esc($row->nama_kontingen) ?>
                                    <span class="muted-copy small ms-2">
                                        T:<?= (int) ($row->jml_tanding ?? 0) ?> / S:<?= (int) ($row->jml_seni ?? 0) ?>
                                    </span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" id="btnCetakKontingen" class="btn btn-danger rounded-pill" disabled>
                        <i class="fa-solid fa-print me-1"></i> Cetak ID Card
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<iframe name="id-card-print-iframe" id="idCardPrintIframe" style="display:none; width:100%; height:100vh; border:none;"></iframe>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('formBatchKontingen');
    var btn = document.getElementById('btnCetakKontingen');
    var checkboxes = document.querySelectorAll('.kontingen-checkbox');
    var iframe = document.getElementById('idCardPrintIframe');

    // Select all checkbox
    document.querySelector('.select-all-checkbox').addEventListener('change', function() {
        var target = document.querySelectorAll(this.dataset.target);
        target.forEach(function(cb) { cb.checked = this.checked; }.bind(this));
        updateButton();
    });

    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateButton);
    });

    function updateButton() {
        var checked = document.querySelectorAll('.kontingen-checkbox:checked');
        btn.disabled = checked.length === 0;
        btn.textContent = checked.length === 0 ? 'Cetak ID Card' : 'Cetak ID Card (' + checked.length + ' kontingen)';
    }

    form.addEventListener('submit', function(e) {
        var checked = document.querySelectorAll('.kontingen-checkbox:checked');
        if (checked.length === 0) {
            e.preventDefault();
            return;
        }

        var totalKartu = 0;
        checked.forEach(function(cb) {
            totalKartu += parseInt(cb.dataset.tanding) + parseInt(cb.dataset.seni);
        });

        // The form submits to iframe - the batch.js handles progress
        Swal.fire({
            title: 'Memproses...',
            html: 'Menyiapkan <b>' + totalKartu + '</b> kartu...',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        // Listen for messages from the iframe
        var handler = function(event) {
            if (event.data.type === 'id-card-progress') {
                Swal.update({
                    html: 'Memproses kartu <b>' + event.data.processed + '</b> dari <b>' + event.data.total + '</b>...'
                });
            } else if (event.data.type === 'id-card-complete') {
                window.removeEventListener('message', handler);
                Swal.fire({
                    icon: 'success',
                    title: 'Selesai!',
                    html: '<b>' + event.data.processed + '</b> kartu berhasil diproses.' +
                        (event.data.failed.length > 0 ? '<br><span class="text-danger">Gagal: ' + event.data.failed.join(', ') + '</span>' : ''),
                    confirmButtonColor: '#dc3545'
                });
            }
        };
        window.addEventListener('message', handler);
    });
});
</script>
<?= $this->endSection() ?>

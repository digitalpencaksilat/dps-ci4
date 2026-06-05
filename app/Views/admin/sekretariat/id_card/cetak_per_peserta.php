<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= view('admin/super/_action_toolbar', [
    'eyebrow' => 'Tools / ID Card',
    'title' => 'Cetak Per Peserta',
    'description' => 'Pilih peserta individual untuk dicetak ID Card-nya.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        ['tag' => 'a', 'href' => base_url('admin/sekretariat/id-card'), 'label' => 'Kembali', 'class' => 'btn-outline-secondary'],
    ],
]) ?>

<section class="admin-card">
    <div class="p-3">
        <div class="mb-3">
            <label for="filterKontingen" class="form-label fw-semibold">Filter Kontingen</label>
            <select class="form-select" id="filterKontingen">
                <option value="">-- Semua Kontingen --</option>
                <?php foreach (($kontingenRows ?? []) as $row) : ?>
                    <option value="<?= esc((string) $row->id_kontingen) ?>"><?= esc($row->nama_kontingen) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <ul class="nav nav-tabs mb-3" id="pesertaTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabTanding" type="button">Tanding</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSeni" type="button">Seni</button>
            </li>
        </ul>

        <form action="<?= base_url('admin/sekretariat/id-card/proses-cetak-batch') ?>" method="post" target="id-card-print-iframe" id="formBatchPeserta">
            <?= csrf_field() ?>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tabTanding">
                    <div class="mb-2">
                        <label class="form-check-label me-3">
                            <input type="checkbox" class="form-check-input select-all-checkbox" data-target="#tabTanding .peserta-checkbox"> Pilih Semua Tanding
                        </label>
                    </div>
                    <div id="listTanding" class="row g-2" style="max-height: 400px; overflow-y: auto;">
                        <div class="col-12 text-muted py-3">Pilih kontingen untuk memuat daftar peserta tanding.</div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tabSeni">
                    <div class="mb-2">
                        <label class="form-check-label me-3">
                            <input type="checkbox" class="form-check-input select-all-checkbox" data-target="#tabSeni .peserta-checkbox"> Pilih Semua Seni
                        </label>
                    </div>
                    <div id="listSeni" class="row g-2" style="max-height: 400px; overflow-y: auto;">
                        <div class="col-12 text-muted py-3">Pilih kontingen untuk memuat daftar peserta seni.</div>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" id="btnCetakPeserta" class="btn btn-danger rounded-pill" disabled>
                    <i class="fa-solid fa-print me-1"></i> Cetak ID Card Terpilih
                </button>
            </div>
        </form>
    </div>
</section>

<iframe name="id-card-print-iframe" id="idCardPrintIframe" style="display:none; width:100%; height:100vh; border:none;"></iframe>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var filter = document.getElementById('filterKontingen');
    var listTanding = document.getElementById('listTanding');
    var listSeni = document.getElementById('listSeni');
    var btn = document.getElementById('btnCetakPeserta');
    var form = document.getElementById('formBatchPeserta');
    var iframe = document.getElementById('idCardPrintIframe');

    filter.addEventListener('change', function() {
        var id = this.value;
        loadPeserta(id || 0);
    });

    function loadPeserta(idKontingen) {
        if (!idKontingen) {
            listTanding.innerHTML = '<div class="col-12 text-muted py-3">Pilih kontingen untuk memuat daftar peserta tanding.</div>';
            listSeni.innerHTML = '<div class="col-12 text-muted py-3">Pilih kontingen untuk memuat daftar peserta seni.</div>';
            updateButton();
            return;
        }

        listTanding.innerHTML = '<div class="col-12 text-muted py-3">Memuat...</div>';
        listSeni.innerHTML = '<div class="col-12 text-muted py-3">Memuat...</div>';

        fetch('<?= base_url('admin/sekretariat/id-card/api/peserta-tanding/') ?>' + idKontingen)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.length === 0) {
                    listTanding.innerHTML = '<div class="col-12 text-muted py-3">Tidak ada peserta tanding.</div>';
                    return;
                }
                listTanding.innerHTML = data.map(function(p) {
                    return '<div class="col-12 col-md-6 col-lg-4">' +
                        '<label class="form-check-label w-100 py-2 px-3 border rounded">' +
                        '<input type="checkbox" class="form-check-input me-2 peserta-checkbox" name="id_peserta_tanding[]" value="' + p.id_peserta_tanding + '">' +
                        p.nama_pendaftar +
                        '<span class="muted-copy small ms-2">' + p.nama_kategori_usia + ' / ' + p.jenis_kelamin + ' / ' + p.label + '</span>' +
                        '</label></div>';
                }).join('');
                bindCheckboxes();
            });

        fetch('<?= base_url('admin/sekretariat/id-card/api/peserta-seni/') ?>' + idKontingen)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.length === 0) {
                    listSeni.innerHTML = '<div class="col-12 text-muted py-3">Tidak ada peserta seni.</div>';
                    return;
                }
                listSeni.innerHTML = data.map(function(p) {
                    return '<div class="col-12 col-md-6 col-lg-4">' +
                        '<label class="form-check-label w-100 py-2 px-3 border rounded">' +
                        '<input type="checkbox" class="form-check-input me-2 peserta-checkbox" name="id_peserta_seni[]" value="' + p.id_peserta_seni + '">' +
                        p.nama_pendaftar +
                        '<span class="muted-copy small ms-2">' + p.nama_kategori_usia + ' / ' + p.jenis_kelamin + ' / ' + p.jenis_seni + ' ' + p.nama_seni + '</span>' +
                        '</label></div>';
                }).join('');
                bindCheckboxes();
            });
    }

    function bindCheckboxes() {
        document.querySelectorAll('.peserta-checkbox').forEach(function(cb) {
            cb.addEventListener('change', updateButton);
        });
        updateButton();
    }

    function updateButton() {
        var checked = document.querySelectorAll('.peserta-checkbox:checked');
        btn.disabled = checked.length === 0;
        btn.textContent = checked.length === 0 ? 'Cetak ID Card Terpilih' : 'Cetak ID Card (' + checked.length + ' peserta)';
    }

    // Select all per tab
    document.querySelectorAll('.select-all-checkbox').forEach(function(el) {
        el.addEventListener('change', function() {
            var target = document.querySelectorAll(this.dataset.target);
            target.forEach(function(cb) { cb.checked = this.checked; }.bind(this));
            updateButton();
        });
    });

    form.addEventListener('submit', function(e) {
        var checked = document.querySelectorAll('.peserta-checkbox:checked');
        if (checked.length === 0) {
            e.preventDefault();
            return;
        }

        Swal.fire({
            title: 'Memproses...',
            html: 'Menyiapkan <b>' + checked.length + '</b> kartu...',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        var handler = function(event) {
            if (event.data.type === 'id-card-progress') {
                Swal.update({ html: 'Memproses kartu <b>' + event.data.processed + '</b> dari <b>' + event.data.total + '</b>...' });
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

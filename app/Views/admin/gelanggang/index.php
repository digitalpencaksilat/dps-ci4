<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Manajemen Arena</p>
            <h2 class="section-title h3 mb-2"><?= esc($title ?? 'Daftar Gelanggang') ?></h2>
            <p class="muted-copy mb-0">Kelola arena pertandingan dan merge jadwal PDF.</p>
        </div>
    </div>
</section>

<section class="admin-card">
    <?php if (session()->get('level') === 'super_admin'): ?>
        <!-- Add Arena Button -->
        <button type="button" class="btn btn-admin-brand rounded-pill px-4 mb-3" data-bs-toggle="modal" data-bs-target="#modalInsertGelanggang">
            <i class="fas fa-plus me-2"></i>Tambah Gelanggang
        </button>

        <!-- Download All Schedules Button -->
        <form action="<?= base_url('admin/gelanggang/merge-all') ?>" method="post" class="d-inline-block"
              onsubmit="return confirmAdminAction(this, 'Download Semua Jadwal?', 'Semua jadwal PDF dari seluruh gelanggang akan di-merge dan diunduh.', 'Ya, Unduh')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger rounded-pill px-4 mb-3 ms-2">
                <i class="fas fa-file-pdf me-2"></i>Download Semua Jadwal
            </button>
        </form>

        <?= view('admin/gelanggang/modal_insert') ?>
    <?php endif; ?>

    <!-- Arena Table -->
    <?= view('admin/gelanggang/table', ['data_gelanggang' => $data_gelanggang]) ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.initAdminDataTable === 'function') {
            window.initAdminDataTable('#tabelGelanggang', { ordering: true });
        }
    });

    function loadAvailableDates(id_gelanggang) {
        const selectEl = document.getElementById('tanggal_' + id_gelanggang);
        selectEl.innerHTML = '<option value="">-- Loading... --</option>';

        const ajaxUrl = '<?= base_url('admin/gelanggang/get-dates') ?>/' + id_gelanggang;

        fetch(ajaxUrl)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(dates => {
                if (dates.error) {
                    selectEl.innerHTML = '<option value="">-- ' + dates.error + ' --</option>';
                    return;
                }

                if (!Array.isArray(dates) || dates.length === 0) {
                    selectEl.innerHTML = '<option value="">-- No dates available --</option>';
                    return;
                }

                let options = '<option value="">-- Pilih Tanggal --</option>';
                dates.forEach(tanggal => {
                    const dateObj = new Date(tanggal + 'T00:00:00');
                    const formatted = dateObj.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    options += `<option value="${tanggal}">${formatted}</option>`;
                });
                selectEl.innerHTML = options;
            })
            .catch(error => {
                console.error('Error loading dates:', error);
                selectEl.innerHTML = '<option value="">-- Failed to load dates --</option>';
            });
    }
</script>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <p class="eyebrow mb-1">Operasi Basis Data</p>
                <h6 class="card-title mb-1">Hapus Peserta Per Kategori Usia</h6>
                <p class="muted-copy small mb-0">Pilih jenis peserta dan kategori usia, lalu cek dampak penghapusan dalam panel preview sebelum proses dijalankan.</p>
            </div>
            <a class="btn btn-outline-dark align-self-start" href="<?= base_url('admin/super/operasi-basis-data') ?>">Kembali</a>
        </div>
    </div>

    <div class="card-body px-0">
        <div class="alert alert-warning border-0 rounded-3 mb-4">
            <div class="fw-semibold mb-1">Perhatian sebelum menghapus</div>
            <div class="small">Data peserta, jadwal, partai/battle, penilaian, dan medali terkait akan ikut dibersihkan. Gunakan preview untuk memastikan kategori yang dipilih sudah benar.</div>
        </div>

        <form id="formHapusPeserta" method="post" action="<?= base_url('admin/super/operasi-basis-data/hapus-peserta-berdasarkan-kategori-usia') ?>">
            <?= csrf_field() ?>

            <div class="row g-3 align-items-end mb-3">
                <div class="col-12 col-lg-6">
                    <label class="form-label d-block">Jenis Peserta</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="jenis_peserta" id="jenisTanding" value="tanding" checked>
                            <label class="form-check-label" for="jenisTanding">Tanding</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="jenis_peserta" id="jenisSeni" value="seni">
                            <label class="form-check-label" for="jenisSeni">Seni</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                        <div class="small muted-copy">Status pilihan</div>
                        <div class="fw-semibold" id="selectedKategoriText">Belum ada kategori usia dipilih.</div>
                    </div>
                </div>
            </div>

            <div class="admin-table-wrap mb-4">
                <div class="table-shell admin-table-scroller">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 64px;">Pilih</th>
                                <th>Kategori Usia</th>
                                <th>Jenis Kelamin</th>
                                <th>Rentang Umur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (($kategoriUsiaRows ?? []) as $row) : ?>
                                <tr>
                                    <td>
                                        <input class="form-check-input kategori-usia-checkbox" type="checkbox" name="id_kategori_usia[]" value="<?= esc((string) ($row->id_kategori_usia ?? 0)) ?>">
                                    </td>
                                    <td><?= esc((string) ($row->nama_kategori_usia ?? '-')) ?></td>
                                    <td><?= esc((string) ($row->jenis_kelamin ?? '-')) ?></td>
                                    <td><?= esc((string) ($row->min_umur ?? '-')) ?> - <?= esc((string) ($row->max_umur ?? '-')) ?> tahun</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger" onclick="previewHapusPeserta()">Preview Penghapusan</button>
                <button type="submit" class="btn btn-danger" onclick="return submitHapusPeserta(event)">Preview & Hapus Peserta</button>
            </div>
        </form>

        <div class="border rounded-3 p-3 mt-4 bg-light-subtle" id="hapusPesertaPreviewPanel">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
                <div>
                    <h6 class="mb-1">Hasil Preview</h6>
                    <p class="muted-copy small mb-0" id="previewHelpText">Pilih jenis peserta dan minimal satu kategori usia, lalu klik preview untuk melihat data yang akan ikut terhapus.</p>
                </div>
                <span class="badge text-bg-secondary align-self-start" id="previewPesertaBadge">Belum ada preview</span>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4 col-xl-2">
                    <div class="placeholder-stat h-100"><div class="small muted-copy">Pendaftar</div><div class="h4 mb-0" id="previewPendaftarCount">0</div></div>
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <div class="placeholder-stat h-100"><div class="small muted-copy">Peserta</div><div class="h4 mb-0" id="previewPesertaCount">0</div></div>
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <div class="placeholder-stat h-100"><div class="small muted-copy">Jadwal Detail</div><div class="h4 mb-0" id="previewJadwalCount">0</div></div>
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <div class="placeholder-stat h-100"><div class="small muted-copy">Penilaian</div><div class="h4 mb-0" id="previewPenilaianCount">0</div></div>
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <div class="placeholder-stat h-100"><div class="small muted-copy">Medali</div><div class="h4 mb-0" id="previewMedaliCount">0</div></div>
                </div>
                <div class="col-12 col-md-4 col-xl-2">
                    <div class="placeholder-stat h-100"><div class="small muted-copy">Kategori Dipilih</div><div class="h4 mb-0" id="previewKategoriCount">0</div></div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <div class="border rounded-3 p-3 bg-white h-100">
                        <div class="small muted-copy mb-2">Ringkasan dampak</div>
                        <div class="fw-semibold mb-2" id="previewJenisLabel">Belum ada data preview.</div>
                        <ul class="small mb-0 ps-3" id="previewImpactList">
                            <li>Preview akan menampilkan jumlah data terkait yang ikut dibersihkan.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="border rounded-3 p-3 bg-white h-100">
                        <div class="small muted-copy mb-2">Objek pertandingan terkait</div>
                        <div class="fw-semibold mb-2" id="previewCompetitionLabel">Belum ada data preview.</div>
                        <ul class="small mb-0 ps-3" id="previewCompetitionList">
                            <li>Untuk tanding akan ditampilkan jumlah partai.</li>
                            <li>Untuk seni akan ditampilkan jumlah kelompok, penampilan, dan battle.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getSelectedKategoriCount() {
    return document.querySelectorAll('#formHapusPeserta input[name="id_kategori_usia[]"]:checked').length;
}

function updateSelectedKategoriText() {
    const selected = getSelectedKategoriCount();
    document.getElementById('selectedKategoriText').textContent = selected > 0
        ? `${selected} kategori usia dipilih.`
        : 'Belum ada kategori usia dipilih.';
}

function renderPesertaPreview(data) {
    const preview = data || {};
    const jenis = (preview.jenis_peserta || 'tanding').toLowerCase();
    const jenisLabel = jenis === 'seni' ? 'Peserta Seni' : 'Peserta Tanding';

    document.getElementById('previewPendaftarCount').textContent = Number(preview.pendaftar || 0);
    document.getElementById('previewPesertaCount').textContent = Number(preview.peserta || 0);
    document.getElementById('previewJadwalCount').textContent = Number(preview.jadwal_detail || 0);
    document.getElementById('previewPenilaianCount').textContent = Number(preview.penilaian || 0);
    document.getElementById('previewMedaliCount').textContent = Number(preview.medali || 0);
    document.getElementById('previewKategoriCount').textContent = Number(preview.jumlah_kategori_usia || 0);
    document.getElementById('previewJenisLabel').textContent = `Preview ${jenisLabel}`;
    document.getElementById('previewCompetitionLabel').textContent = jenis === 'seni' ? 'Objek seni yang akan ikut dibersihkan' : 'Objek tanding yang akan ikut dibersihkan';
    document.getElementById('previewPesertaBadge').textContent = 'Preview berhasil dibuat';
    document.getElementById('previewHelpText').textContent = `Sistem mendeteksi ${Number(preview.peserta || 0)} peserta dari ${Number(preview.pendaftar || 0)} pendaftar pada ${Number(preview.jumlah_kategori_usia || 0)} kategori usia terpilih.`;

    const impactItems = [
        `Pendaftar terdampak: ${Number(preview.pendaftar || 0)}`,
        `Peserta terdampak: ${Number(preview.peserta || 0)}`,
        `Detail jadwal terkait: ${Number(preview.jadwal_detail || 0)}`,
        `Penilaian terkait: ${Number(preview.penilaian || 0)}`,
        `Medali terkait: ${Number(preview.medali || 0)}`
    ];

    document.getElementById('previewImpactList').innerHTML = impactItems.map((item) => `<li>${item}</li>`).join('');

    const competitionItems = jenis === 'seni'
        ? [
            `Kelompok seni: ${Number(preview.kelompok || 0)}`,
            `Penampilan seni: ${Number(preview.penampilan || 0)}`,
            `Battle seni: ${Number(preview.battle || 0)}`
        ]
        : [`Partai/pertandingan: ${Number(preview.partai || 0)}`];

    document.getElementById('previewCompetitionList').innerHTML = competitionItems.map((item) => `<li>${item}</li>`).join('');
}

async function getPreviewData() {
    const form = document.getElementById('formHapusPeserta');
    const selected = form.querySelectorAll('input[name="id_kategori_usia[]"]:checked');

    if (selected.length < 1) {
        Swal.fire('Kategori belum dipilih', 'Pilih minimal 1 kategori usia untuk menampilkan preview.', 'warning');
        return null;
    }

    document.getElementById('previewPesertaBadge').textContent = 'Mengambil preview...';
    const formData = new FormData(form);

    const response = await fetch('<?= base_url('admin/super/operasi-basis-data/preview-hapus-peserta-berdasarkan-kategori-usia') ?>', {
        method: 'POST',
        body: new URLSearchParams(formData)
    });

    const data = await response.json();
    if (!data || data.status !== true) {
        document.getElementById('previewPesertaBadge').textContent = 'Preview gagal';
        Swal.fire('Gagal', (data && data.message) ? data.message : 'Preview gagal dibuat.', 'error');
        return null;
    }

    renderPesertaPreview(data.data || {});
    return data;
}

async function previewHapusPeserta() {
    await getPreviewData();
}

async function submitHapusPeserta(event) {
    event.preventDefault();

    const data = await getPreviewData();
    if (!data || data.status !== true) {
        return false;
    }

    const preview = data.data || {};
    const jenis = (preview.jenis_peserta || 'tanding').toLowerCase();
    const objekUtama = jenis === 'seni'
        ? `Kelompok: <strong>${Number(preview.kelompok || 0)}</strong><br>Penampilan: <strong>${Number(preview.penampilan || 0)}</strong><br>Battle: <strong>${Number(preview.battle || 0)}</strong>`
        : `Partai/pertandingan: <strong>${Number(preview.partai || 0)}</strong>`;

    const result = await Swal.fire({
        title: 'Hapus Peserta?',
        html: `Pendaftar: <strong>${Number(preview.pendaftar || 0)}</strong><br>Peserta: <strong>${Number(preview.peserta || 0)}</strong><br>Jadwal detail: <strong>${Number(preview.jadwal_detail || 0)}</strong><br>Penilaian: <strong>${Number(preview.penilaian || 0)}</strong><br>Medali: <strong>${Number(preview.medali || 0)}</strong><br>${objekUtama}<br><br>Ketik <strong>hapuspeserta</strong> untuk melanjutkan.`,
        input: 'text',
        inputPlaceholder: 'hapuspeserta',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6b7280',
        preConfirm: (val) => {
            if (val !== 'hapuspeserta') {
                Swal.showValidationMessage('Kata kunci tidak sesuai. Ketik persis: hapuspeserta');
            }
        }
    });

    if (!result.isConfirmed) {
        return false;
    }

    document.getElementById('formHapusPeserta').submit();
    return false;
}

document.querySelectorAll('.kategori-usia-checkbox').forEach((checkbox) => {
    checkbox.addEventListener('change', updateSelectedKategoriText);
});
document.querySelectorAll('input[name="jenis_peserta"]').forEach((radio) => {
    radio.addEventListener('change', updateSelectedKategoriText);
});
updateSelectedKategoriText();
</script>
<?= $this->endSection() ?>

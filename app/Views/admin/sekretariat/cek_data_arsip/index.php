<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$exportTitle = 'CEK DATA ARSIP PESERTA';
$exportFilename = 'Cek Data Arsip - ' . $eventName;
$brandName = (string) (get_setting('brand_name') ?? 'Digital Pencak Silat');
$brandAbbr = strtolower((string) (get_setting('brand_abbreviation') ?? 'dps'));
$brandLogoUrl = base_url('assets/images/brand/' . $brandAbbr . '/logo.png');
$printHeaderHtml = view('shared_components/print/medal_export_header', [
    'title' => $exportTitle,
    'subtitle' => $eventName,
]);
?>
<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Sekretariat</p>
            <h3 class="section-title h4 mb-0">Cek Data Arsip Peserta</h3>
            <p class="muted-copy mb-0 mt-2">Verifikasi kelengkapan dokumen arsip yang telah diupload oleh peserta.</p>
        </div>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
                    <table class="table admin-table align-middle mb-0" id="tabelCekDataArsip">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama</th>
                                <th>Kontingen</th>
                                <th class="text-center">Foto</th>
                                <?php foreach ($activeArsip as $key => $arsipConfig): ?>
                                    <th class="text-center"><?= esc($arsipConfig['nama_arsip']) ?></th>
                                <?php endforeach; ?>
                                <th class="text-center no-export">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendaftarRows as $arsipIndex => $pendaftar): ?>
                                <?php $arsipList = $arsipGrouped[$pendaftar->id_pendaftar] ?? []; ?>
                                <tr>
                                    <td class="text-center fw-semibold"><?= esc((string) ($arsipIndex + 1)) ?></td>
                                    <td>
                                        <span class="fw-semibold text-capitalize"><?= esc($pendaftar->nama_pendaftar) ?></span>
                                    </td>
                                    <td class="text-uppercase"><?= esc($pendaftar->nama_kontingen) ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($pendaftar->foto)): ?>
                                            <img src="<?= base_url('uploads/peserta/foto/' . $pendaftar->foto) ?>"
                                                 class="img-thumbnail rounded"
                                                 style="width: 48px; height: 48px; object-fit: cover; cursor: pointer;"
                                                 onclick="showImageModal('<?= base_url('uploads/peserta/foto/' . esc($pendaftar->foto, 'js')) ?>', 'Foto Peserta', '<?= esc($pendaftar->nama_pendaftar, 'js') ?>')"
                                                 alt="Foto <?= esc($pendaftar->nama_pendaftar) ?>">
                                        <?php else: ?>
                                            <span class="badge bg-secondary">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php foreach ($activeArsip as $key => $arsipConfig): ?>
                                        <td class="text-center">
                                            <?php
                                            $found = false;
                                            foreach ($arsipList as $ars):
                                                if ($ars->jenis_arsip === $arsipConfig['nama_arsip']):
                                                    $found = true;
                                            ?>
                                                    <img src="<?= base_url('uploads/peserta/arsip/' . $ars->nama_arsip) ?>"
                                                         class="img-thumbnail rounded"
                                                         style="width: 48px; height: 48px; object-fit: cover; cursor: pointer;"
                                                         onclick="showImageModal('<?= base_url('uploads/peserta/arsip/' . esc($ars->nama_arsip, 'js')) ?>', '<?= esc($arsipConfig['nama_arsip'], 'js') ?>', '<?= esc($pendaftar->nama_pendaftar, 'js') ?>')"
                                                         alt="<?= esc($arsipConfig['nama_arsip']) ?>">
                                            <?php
                                                endif;
                                            endforeach;
                                            if (!$found):
                                            ?>
                                                <span class="badge bg-warning text-dark">Belum Upload</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center no-export">
                                        <button class="btn btn-sm btn-danger rounded-pill px-3" onclick="lihatDetailArsip(<?= $pendaftar->id_pendaftar ?>)">
                                            <i class="fas fa-eye me-1"></i>Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
    </div></div>
</section>

<div class="modal fade" id="modalZoomImage" tabindex="-1" aria-labelledby="modalZoomImageLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalZoomImageLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="zoomedImage" src="" class="img-fluid rounded" style="max-height: 70vh;" alt="Preview">
            </div>
            <div class="modal-footer">
                <a id="downloadImageBtn" href="" download class="btn btn-admin-brand btn-sm rounded-pill px-3">
                    <i class="fas fa-download me-1"></i>Download
                </a>
                <a id="openNewTabBtn" href="" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="fas fa-external-link-alt me-1"></i>Buka di Tab Baru
                </a>
                <button type="button" class="btn btn-soft btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailArsip" tabindex="-1" aria-labelledby="modalDetailArsipLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailArsipLabel">Detail Arsip Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDetailArsipBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-admin-brand" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfName = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';

$(document).ready(function() {
    window.initAdminExportTable('#tabelCekDataArsip', {
        title: <?= json_encode($exportTitle) ?>,
        filename: <?= json_encode($exportFilename) ?>,
        orientation: 'landscape',
        preset: 'wide-report',
        themedExport: true,
        excelUppercase: false,
        printHeaderHtml: <?= json_encode($printHeaderHtml) ?>,
        watermark: {
            logo: <?= json_encode($brandLogoUrl) ?>,
            text: 'Powered by <strong>' + <?= json_encode($brandName) ?> + '</strong> &copy; ' + new Date().getFullYear()
        },
        // Convert image/badge cells to readable text for Excel & print
        exportFormatBody: function(data, row, column, node) {
            if (typeof data !== 'string') return data;
            if (data.indexOf('<img') !== -1) return 'Ada';
            if (data.indexOf('Belum Upload') !== -1) return 'Belum Upload';
            var txt = data.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
            return txt === '—' ? '-' : txt;
        }
    });
});

function updateCsrfToken(response) {
    const newToken = response.headers.get('X-CSRF-TOKEN');
    if (newToken) csrfHash = newToken;
}

function showImageModal(imageUrl, jenisArsip, namaPeserta) {
    document.getElementById('modalZoomImageLabel').textContent = jenisArsip + ' — ' + namaPeserta;
    document.getElementById('zoomedImage').src = imageUrl;
    document.getElementById('downloadImageBtn').href = imageUrl;
    document.getElementById('openNewTabBtn').href = imageUrl;
    new bootstrap.Modal(document.getElementById('modalZoomImage')).show();
}

function lihatDetailArsip(idPendaftar) {
    const body = document.getElementById('modalDetailArsipBody');
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-admin-brand" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    new bootstrap.Modal(document.getElementById('modalDetailArsip')).show();

    const bodyParams = new URLSearchParams();
    bodyParams.append('id_pendaftar', idPendaftar);
    bodyParams.append(csrfName, csrfHash);

    fetch('<?= base_url("admin/sekretariat/cek-data-arsip/detail") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: bodyParams.toString()
    })
    .then(response => {
        updateCsrfToken(response); // refresh token dari response (regenerate=true)
        return response.text();
    })
    .then(html => {
        body.innerHTML = html;
    })
    .catch(() => {
        body.innerHTML = '<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>';
    });
}
</script>
<?= $this->endSection() ?>

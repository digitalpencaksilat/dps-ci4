<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="glass-card h-100">
            <div class="card-header-custom">
                <h5><i class="fas fa-paper-plane"></i> Integrasi Portal</h5>
            </div>
            <div class="card-body p-4">
                <form id="pushForm" class="row g-3">
                    <div class="col-12">
                        <label class="form-label font-oswald text-muted small uppercase tracking-wider">Target API URL</label>
                        <input type="url" name="url" class="form-control form-control-custom" placeholder="https://portal.example.com/api/results" value="https://portal.digitalsilat.com/api/push_results" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label font-oswald text-muted small uppercase tracking-wider">Event ID</label>
                        <input type="text" name="event_id" class="form-control form-control-custom" placeholder="Contoh: EVT-001" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label font-oswald text-muted small uppercase tracking-wider">API Key</label>
                        <input type="text" name="api_key" class="form-control form-control-custom" placeholder="Masukkan API Key" required>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-custom btn-brand w-100 py-3" id="btnPush">
                            <i class="fas fa-rocket me-2"></i> Mulai Sinkronisasi
                        </button>
                    </div>
                </form>

                <div class="mt-4 p-3 bg-light rounded-3 border">
                    <h6 class="font-oswald text-dark mb-2" style="font-size: 0.8rem;">Panduan Singkat</h6>
                    <ul class="small text-muted ps-3 mb-0" style="font-size: 0.75rem;">
                        <li>Pastikan URL API Portal sudah benar dan dapat dijangkau.</li>
                        <li><b>Event ID</b> harus sesuai dengan ID yang terdaftar di Portal.</li>
                        <li>Gunakan <b>API Key</b> yang valid untuk autentikasi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="glass-card h-100">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-terminal"></i> Synchronization Log</h5>
                <button class="btn btn-link btn-sm text-muted p-0 font-oswald" onclick="$('#logContainer').empty()">
                    <i class="fas fa-eraser"></i> Clear
                </button>
            </div>
            <div class="card-body p-4">
                <div class="terminal-container" id="logContainer">
                    <div class="opacity-30 small text-center mt-5">Menunggu aktivitas sinkronisasi...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#pushForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#btnPush');
        const $log = $('#logContainer');
        const formData = $(this).serialize();

        if ($log.find('.opacity-30').length) $log.empty();

        $btn.attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin me-2"></i> Memproses...');

        $log.append('<div class="mb-1 text-info">[INFO] ' + new Date().toLocaleTimeString() + ' - Menginisialisasi payload...</div>');
        $log.append('<div class="mb-1 text-warning">[WAIT] Menghubungkan ke API...</div>');
        $log.animate({ scrollTop: $log.prop("scrollHeight") }, 500);

        $.ajax({
            url: "<?= base_url('development/data-pusher/push') ?>",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                $log.append('<div class="mb-1 text-success">[DONE] ' + response.message + '</div>');
                if (response.api_response) {
                    $log.append('<div class="mb-1 text-muted small" style="padding-left: 20px;">' + JSON.stringify(response.api_response) + '</div>');
                }
                $btn.removeAttr('disabled').html('<i class="fas fa-rocket me-2"></i> Mulai Sinkronisasi');
                $log.animate({ scrollTop: $log.prop("scrollHeight") }, 500);
            },
            error: function(xhr) {
                let errorMsg = "Terjadi kesalahan pada server.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $log.append('<div class="mb-1 text-danger">[ERROR] HTTP ' + xhr.status + ': ' + errorMsg + '</div>');
                $btn.removeAttr('disabled').html('<i class="fas fa-rocket me-2"></i> Mulai Sinkronisasi');
                $log.animate({ scrollTop: $log.prop("scrollHeight") }, 500);
            }
        });
    });
</script>

<?= $this->endSection() ?>

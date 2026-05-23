<section class="py-5" style="font-family: 'Poppins', sans-serif; text-transform: uppercase;">
    <div class="container text-center">
        <h2 class="mb-4"><?= get_instance()->get_setting('event_name') ?></h2>
        <div class="row">
            <div class="col-md-4 mb-2">
                <div class="value-card">
                    <!-- Menambahkan ikon dengan warna merah -->
                    <i class="fas fa-map-marker fa-3x value-card-icon"></i>
                    <h4><?= lang('lokasi'); ?></h4>
                    <p><?= get_instance()->get_setting('event_name') . ' ' . lang('dilaksanakan_di') . ' ' . $this->CI->config->item('event_location', 'pendaftaran/profil_kejuaraan') ?>.</p>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="value-card">
                    <!-- Menambahkan ikon dengan warna merah -->
                    <i class="fas fa-users fa-3x value-card-icon"></i>
                    <h4><?= lang('dipersembahkan_oleh'); ?></h4>
                    <p><?= lang('event_dipersembahkan_oleh') . ' ' . $this->CI->config->item('event_host', 'pendaftaran/profil_kejuaraan') ?>.</p>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="value-card">
                    <!-- Menambahkan ikon dengan warna merah -->
                    <i class="fas fa-calendar fa-3x value-card-icon"></i>
                    <h4><?= lang('tanggal'); ?></h4>
                    <p><?= lang('dilaksanakan_mulai_tanggal') . ' ' . $this->CI->config->item('date_start', 'pendaftaran/profil_kejuaraan') . ' ' . lang('sampai_dengan') . ' ' . $this->CI->config->item('date_end', 'pendaftaran/profil_kejuaraan') ?>.</p>
                </div>
            </div>
        </div>
    </div>
</section>
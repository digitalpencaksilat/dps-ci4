<?php
	$this->CI =& get_instance();
?>
<div class="container py-5">
    <div class="card shadow-lg text-center">
        <div class="card-body p-5">
            <div class="row justify-content-center">
                <div class="col-md-12 mb-5">
                    <h2 class="h3 fw-bolder"><?= lang('informasi_kegiatan'); ?></h2>
                    <p><?= $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-primary mb-3">
                        <i class="fas fa-map-marker fa-3x"></i>
                    </div>
                    <h4 class="h5"><?= lang('lokasi'); ?></h4>
                    <p class="px-0 px-md-3">
                        <?= $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') . ' ' . lang('dilaksanakan_di') . ' ' . $this->CI->config->item('event_location', 'pendaftaran/profil_kejuaraan') ?>.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-info mb-3">
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                    <h4 class="h5"><?= lang('dipersembahkan_oleh'); ?></h4>
                    <p class="px-0 px-md-3"><?= lang('event_dipersembahkan_oleh') . ' ' . $this->CI->config->item('event_host', 'pendaftaran/profil_kejuaraan') ?>.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-success mb-3">
                        <i class="fas fa-calendar fa-3x"></i>
                    </div>
                    <h4 class="h5"><?= lang('tanggal'); ?></h4>
                    <p class="px-0 px-md-3">
                        <?= $this->CI->config->item('event_name', 'pendaftaran/profil_kejuaraan') . ' ' . lang('dilaksanakan_mulai_tanggal') . ' ' . $this->CI->config->item('date_start', 'pendaftaran/profil_kejuaraan') . ' ' . lang('sampai_dengan') . ' ' . $this->CI->config->item('date_end', 'pendaftaran/profil_kejuaraan') ?>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

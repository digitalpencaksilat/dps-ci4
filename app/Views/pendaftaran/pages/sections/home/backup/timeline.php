<section class="mb-5" style="background-image: url('<?= base_url('assets/images/brand/' . strtolower($this->config->item('brand_abbreviation')) . '/ilustrasi/alur-background.jpg') ?>'); background-size: cover; background-position: center; font-family: 'Poppins' , sans-serif; text-transform: uppercase;">
    <div class="container text-center text-light">
        <h2 class="mb-4 text-white"><?= 'TIMELINE ' . get_instance()->get_setting('event_name') ?></h2>
        <div class="row">
            <div class="col-md-4 mb-2">
                <div class="card-timeline text-white" style="background-color: #B8001F;">
                    <div class="card-body text-center">
                        <i class="fas fa-pencil-alt fa-3x mb-3"></i>
                        <h3><?= lang('pendaftaran'); ?></h3>
                        <p><?= lang('pendaftaran') . ' ' . get_instance()->get_setting('event_name') . ' ' . lang('dibuka_dari') . ' ' . $this->CI->config->item('registration_start', 'pendaftaran/profil_kejuaraan') . ' ' . lang('sampai') . ' ' . $this->CI->config->item('registration_end', 'pendaftaran/profil_kejuaraan') ?>.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="card-timeline text-white" style="background-color: #B8001F;">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h3><?= lang('technical_meeting'); ?></h3>
                        <p><?= lang('technical_meeting') . ' ' . get_instance()->get_setting('event_name') . ' ' . lang('dilaksanakan_tanggal') . ' ' . $this->CI->config->item('technical_meeting_date', 'pendaftaran/profil_kejuaraan') . ' ' . lang('di') . ' ' . $this->CI->config->item('technical_meeting_location', 'pendaftaran/profil_kejuaraan') ?>.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2">
                <div class="card-timeline text-white" style="background-color: #B8001F;">
                    <div class="card-body text-center">
                        <i class="fas fa-running fa-3x mb-3"></i>
                        <h3><?= lang('pertandingan'); ?></h3>
                        <p><?= lang('pertandingan_berlangsung') . ' ' . $this->CI->config->item('date_start', 'pendaftaran/profil_kejuaraan') . ' ' . lang('sampai') . ' ' . $this->CI->config->item('date_end', 'pendaftaran/profil_kejuaraan') ?>.<br><?= lang('pertandingan_dilaksanakan_di') . ' ' . $this->CI->config->item('event_location', 'pendaftaran/profil_kejuaraan') ?>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
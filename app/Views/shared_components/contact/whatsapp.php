<?php if (get_setting('integrasi_whatsapp') == true) : ?>
    <div class="fixed-bottom me-3 mb-4 ms-auto" style="width:75px; height:auto;">
        <a href="<?= wa_me(convert_to_indonesian_phone_number($this->config->item('contact_person', 'pendaftaran/profil_kejuaraan'))) ?>" target="_blank">
            <img src="<?= base_url('assets/images/brand/'.strtolower($this->config->item('brand_abbreviation')).'/ilustrasi/whatsapp.png') ?>" class="img-fluid img-raised" alt="Contact-person">
        </a>
    </div>
<?php endif ?>

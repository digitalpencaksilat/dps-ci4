

<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * File konfigurasi ini berhubungan dengan akses pengisian NIK No KK.
 */


$config['allow_citizen_id_input'] = true;
$config['citizen_id_alias_name'] = 'NIK';
$config['maximum_citizen_id_digit'] = 16;

$config['allow_family_id_input'] = true;
$config['family_id_alias_name'] = 'Nomor KK';
$config['maximum_family_id_digit'] = 16;

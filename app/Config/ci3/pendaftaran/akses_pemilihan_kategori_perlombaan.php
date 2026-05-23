

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * File konfigurasi ini berhubungan dengan akses pemilihan kategori perlombaan oleh peserta.
*/


/**
 *  Peserta dipernolehkan memilih kategori usia, 
 * apabila bernilai false, maka kategori usia yang muncul adalah 
 * kategori usia yang valid berdasarkan usia pendaftar
 */
$config['perbolehkan_memilih_kategori_usia'] = true; 


/**
 *  Peserta dipernolehkan memilih kelas tanding tanpa megunci pilihan 
 * kelas berdasarkan berat badan, 
 */
$config['perbolehkan_memilih_kelas_tanding'] = true; 


/**
 * Perbolehkan peserta untuk daftar pada kategori kelas tanding
 * yang sebelumnya sudah diikuti oleh rekan se-kontingennya
 */
$config['perbolehkan_atlet_dari_kontingen_yang_sama'] = true; 

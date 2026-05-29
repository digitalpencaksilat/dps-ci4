<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Services\SistemGugurTunggalService;

class KompetisiTandingModel extends Model
{
    protected $table = 'kompetisi_tanding';
    protected $primaryKey = 'id_kompetisi_tanding';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kelas_tanding', 'max_peserta', 'perhitungan_medali', 'nomor_pool', 'keterangan', 'bagan_pertandingan'];

    /**
     * CI3 parity wrapper.
     *
     * CI3: Kompetisi_tanding_model::acak_bagan_tanding($id_kompetisi_tanding, $random_seed = FALSE)
     * - $random_seed=FALSE => formula
     * - $random_seed=TRUE  => full_random_persilat (standar persilat)
     */
    public function acak_bagan_tanding(int $id_kompetisi_tanding, bool $random_seed = false): bool
    {
        $mode = $random_seed ? 'full_random_persilat' : 'formula';
        (new SistemGugurTunggalService())->acakBaganTanding($id_kompetisi_tanding, $mode);

        return true;
    }

    /**
     * CI3 parity wrapper.
     *
     * CI3: Kompetisi_tanding_model::generate_bagan_dari_jadwal_excel($id_kompetisi_tanding)
     */
    public function generate_bagan_dari_jadwal_excel(int $id_kompetisi_tanding): bool
    {
        (new SistemGugurTunggalService())->generateBaganTandingDariJadwal($id_kompetisi_tanding);

        return true;
    }
}

<?php

namespace App\Services;

use App\Services\Admin\Super\SettingWriterService;
use Config\IdCard as IdCardConfig;
use RuntimeException;

class IdCardService
{
    private IdCardConfig $defaultConfig;

    public function __construct()
    {
        $this->defaultConfig = new IdCardConfig();
    }

    // ============================================================
    //  Layout Configuration
    // ============================================================

    /**
     * Get layout config for all elements.
     * DB first, merge with file defaults for any missing keys.
     *
     * @return array<string, array<string, string>>
     */
    public function getLayoutConfig(): array
    {
        $dbConfig = $this->loadFromDb();
        $defaults = $this->defaultConfig->allDefaults();

        if ($dbConfig === null) {
            return $defaults;
        }

        $merged = [];
        foreach ($defaults as $section => $fields) {
            $merged[$section] = array_merge($fields, $dbConfig[$section] ?? []);
        }

        return $merged;
    }

    /**
     * Get layout config for a specific section.
     *
     * @return array<string, string>
     */
    public function getLayoutSection(string $section): array
    {
        $all = $this->getLayoutConfig();

        return $all[$section] ?? [];
    }

    /**
     * Save layout config to database.
     *
     * @param array<string, array<string, string>> $config
     */
    public function saveLayoutConfig(array $config): void
    {
        (new SettingWriterService())->setString(
            'id_card_layout',
            json_encode($config, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * @return array<string, array<string, string>>|null
     */
    private function loadFromDb(): ?array
    {
        $raw = get_setting('id_card_layout');
        if ($raw === null || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    // ============================================================
    //  Card Data Queries
    // ============================================================

    /**
     * Get tanding participant data for ID card.
     */
    public function getCardDataTanding(int $idPesertaTanding): ?object
    {
        return db_connect()
            ->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding',
                'p.nama_pendaftar',
                'p.foto',
                'k.nama_kontingen',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kt.label',
                'kl.nama_kategori_lomba',
                'pt.id_kompetisi_tanding',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('pt.id_peserta_tanding', $idPesertaTanding)
            ->get()
            ->getRow();
    }

    /**
     * Get pertandingan (match) data for a tanding participant.
     *
     * Mengembalikan baris pertandingan + nomor_partai (dari detail_jadwal_tanding)
     * + nama_gelanggang (dari jadwal_tanding → gelanggang). Helper
     * get_partai_pertandingan() butuh kolom-kolom ini sebagai alias.
     *
     * @return list<object>
     */
    public function getPertandinganData(int $idKompetisiTanding): array
    {
        return db_connect()
            ->table('pertandingan')
            ->select('pertandingan.*, djt.nomor_partai, g.nama_gelanggang', false)
            ->join('detail_jadwal_tanding djt', 'djt.id_pertandingan = pertandingan.id_pertandingan', 'left')
            ->join('jadwal_tanding jt', 'jt.id_jadwal_tanding = djt.id_jadwal_tanding', 'left')
            ->join('gelanggang g', 'g.id_gelanggang = jt.id_gelanggang', 'left')
            ->where('pertandingan.id_kompetisi_tanding', $idKompetisiTanding)
            ->orderBy('pertandingan.nomor_pertandingan', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get seni participant data for ID card.
     */
    public function getCardDataSeni(int $idPesertaSeni): ?object
    {
        return db_connect()
            ->table('peserta_seni ps')
            ->select([
                'ps.id_peserta_seni',
                'ps.id_kelompok_peserta_seni',
                'p.nama_pendaftar',
                'p.foto',
                'k.nama_kontingen',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'sks.nama_seni',
                'sks.jenis_seni',
                'sks.sistem_penampilan',
                'kps.id_kompetisi_seni',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('ps.id_peserta_seni', $idPesertaSeni)
            ->get()
            ->getRow();
    }

    /**
     * Get penampilan (schedule) data for pool-mode seni.
     *
     * Tabel `detail_jadwal_seni` tidak punya kolom `id_kompetisi_seni`, jadi
     * filter id_kompetisi_seni dilakukan via chain:
     *  detail_jadwal_seni → penampilan_seni → kelompok_peserta_seni.id_kompetisi_seni
     *
     * `babak_pool` di-derive dari `penampilan_seni.babak` (alias legacy).
     *
     * @return list<object>
     */
    public function getPenampilanSeniData(int $idKompetisiSeni): array
    {
        return db_connect()
            ->table('detail_jadwal_seni djs')
            ->select(
                'djs.id_detail_jadwal_seni, djs.nomor_partai, '
                . 'g.nama_gelanggang, '
                . 'ps.babak AS babak_pool, '
                . 'ps.id_kelompok_peserta_seni, '
                . 'kps.id_kompetisi_seni',
                false
            )
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang', 'left')
            ->where('kps.id_kompetisi_seni', $idKompetisiSeni)
            ->orderBy('djs.nomor_partai', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get battle seni data for battle-mode seni.
     *
     * Mengembalikan baris `battle_seni` dengan alias yang dipakai helper
     * `get_partai_battle_seni()`:
     *  - id_kompetisi_seni_battle
     *  - babak_battle
     *  - id_kelompok_peserta_seni_merah / _biru (di-derive via penampilan_seni)
     *  - nomor_partai (dari detail_jadwal_seni)
     *  - nama_gelanggang (dari jadwal_seni → gelanggang)
     *
     * @return list<object>
     */
    public function getBattleSeniData(int $idKompetisiSeni): array
    {
        $select = 'bs.id_battle_seni, '
            . 'bs.id_kompetisi_seni AS id_kompetisi_seni_battle, '
            . 'bs.nomor_battle, '
            . 'bs.nomor_battle_selanjutnya, '
            . 'bs.babak AS babak_battle, '
            . 'bs.id_penampilan_seni_merah, '
            . 'bs.id_penampilan_seni_biru, '
            . '(SELECT id_kelompok_peserta_seni FROM penampilan_seni WHERE id_penampilan_seni = bs.id_penampilan_seni_merah) AS id_kelompok_peserta_seni_merah, '
            . '(SELECT id_kelompok_peserta_seni FROM penampilan_seni WHERE id_penampilan_seni = bs.id_penampilan_seni_biru) AS id_kelompok_peserta_seni_biru, '
            . 'djs.nomor_partai, '
            . 'g.nama_gelanggang';

        return db_connect()
            ->table('battle_seni bs')
            ->select($select, false)
            ->join('detail_jadwal_seni djs', 'djs.id_battle_seni = bs.id_battle_seni', 'left')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni', 'left')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang', 'left')
            ->where('bs.id_kompetisi_seni', $idKompetisiSeni)
            ->orderBy('bs.nomor_battle', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get list peserta tanding with full data for UI (DataTables).
     * Includes kontingen filter, kategori label, dan foto status.
     *
     * @return list<object>
     */
    public function getListPesertaTanding(?int $idKontingenFilter = null): array
    {
        $query = db_connect()
            ->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding',
                'p.nama_pendaftar',
                'p.foto',
                'p.id_kontingen',
                'k.nama_kontingen',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kt.label',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia');

        if ($idKontingenFilter !== null) {
            $query->where('p.id_kontingen', $idKontingenFilter);
        }

        $results = $query->orderBy('p.nama_pendaftar', 'ASC')->get()->getResult();

        // Hitung has_foto per baris (cek kolom non-empty)
        foreach ($results as $row) {
            $row->has_foto = ! empty($row->foto);
            $row->kategori_label = ($row->nama_kategori_usia ?? '') . ' ' . (ucfirst($row->jenis_kelamin ?? '')) . (isset($row->label) ? ' Kelas ' . $row->label : '');
        }

        return $results;
    }

    /**
     * Get list peserta seni with full data for UI (DataTables).
     * Includes kontingen filter, kategori label, dan foto status.
     *
     * @return list<object>
     */
    public function getListPesertaSeni(?int $idKontingenFilter = null): array
    {
        $query = db_connect()
            ->table('peserta_seni ps')
            ->select([
                'ps.id_peserta_seni',
                'p.nama_pendaftar',
                'p.foto',
                'p.id_kontingen',
                'k.nama_kontingen',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'sks.nama_seni',
                'sks.jenis_seni',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia');

        if ($idKontingenFilter !== null) {
            $query->where('p.id_kontingen', $idKontingenFilter);
        }

        $results = $query->orderBy('p.nama_pendaftar', 'ASC')->get()->getResult();

        // Hitung has_foto per baris + format kategori label
        foreach ($results as $row) {
            $row->has_foto = ! empty($row->foto);
            $row->kategori_label = ($row->nama_kategori_usia ?? '') . ' ' . (ucfirst($row->jenis_kelamin ?? '')) . ' ' . (ucfirst($row->jenis_seni ?? '') . ' ' . ($row->nama_seni ?? ''));
        }

        return $results;
    }

    /**
     * Get all tanding peserta IDs for a kontingen.
     *
     * @return list<int>
     */
    public function getPesertaTandingIdsByKontingen(int $idKontingen): array
    {
        $rows = db_connect()
            ->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->where('p.id_kontingen', $idKontingen)
            ->orderBy('pt.id_peserta_tanding', 'ASC')
            ->get()
            ->getResult();

        return array_map(static fn ($r): int => (int) $r->id_peserta_tanding, $rows);
    }

    /**
     * Get all seni peserta IDs for a kontingen.
     *
     * @return list<int>
     */
    public function getPesertaSeniIdsByKontingen(int $idKontingen): array
    {
        $rows = db_connect()
            ->table('peserta_seni ps')
            ->select('ps.id_peserta_seni')
            ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
            ->where('p.id_kontingen', $idKontingen)
            ->orderBy('ps.id_peserta_seni', 'ASC')
            ->get()
            ->getResult();

        return array_map(static fn ($r): int => (int) $r->id_peserta_seni, $rows);
    }

    /**
     * Get all tanding peserta IDs (for batch all).
     *
     * @return list<int>
     */
    public function getAllPesertaTandingIds(): array
    {
        $rows = db_connect()
            ->table('peserta_tanding')
            ->select('id_peserta_tanding')
            ->orderBy('id_peserta_tanding', 'ASC')
            ->get()
            ->getResult();

        return array_map(static fn ($r): int => (int) $r->id_peserta_tanding, $rows);
    }

    /**
     * Get all seni peserta IDs (for batch all).
     *
     * @return list<int>
     */
    public function getAllPesertaSeniIds(): array
    {
        $rows = db_connect()
            ->table('peserta_seni')
            ->select('id_peserta_seni')
            ->orderBy('id_peserta_seni', 'ASC')
            ->get()
            ->getResult();

        return array_map(static fn ($r): int => (int) $r->id_peserta_seni, $rows);
    }

    // ============================================================
    //  Barcode
    // ============================================================

    /**
     * Generate EAN-8 barcode value for a tanding participant.
     * Prefix "001" + (id_peserta_tanding + 1000), pad to 7 digits.
     */
    public static function barcodeValueTanding(int $idPesertaTanding): string
    {
        $num = $idPesertaTanding + 1000;
        if ($num > 9999) {
            $num %= 10000;
        }

        return '001' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate EAN-8 barcode value for a seni participant.
     * Prefix "002" + (id_peserta_seni + 1000), pad to 7 digits.
     */
    public static function barcodeValueSeni(int $idPesertaSeni): string
    {
        $num = $idPesertaSeni + 1000;
        if ($num > 9999) {
            $num %= 10000;
        }

        return '002' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    // ============================================================
    //  Background Image
    // ============================================================

    /**
     * Upload background image for ID Card.
     * Only allows PNG, max 3MB, overwrites existing file.
     */
    public function uploadBackground(\CodeIgniter\HTTP\Files\UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new RuntimeException('File background tidak valid.');
        }

        $ext = strtolower((string) $file->getExtension());
        if ($ext !== 'png') {
            throw new RuntimeException('Background ID Card hanya boleh file PNG.');
        }

        if ($file->getSizeByUnit('kb') > 3072) {
            throw new RuntimeException('Ukuran background maksimal 3 MB.');
        }

        $targetDir = FCPATH . 'uploads/kartu-peserta';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $file->move($targetDir, 'atlet.png', true);
    }

    /**
     * Check if background image exists.
     */
    public function hasBackground(): bool
    {
        return is_file(FCPATH . 'uploads/kartu-peserta/atlet.png');
    }

    /**
     * Get background image URL. Returns empty string when file does not exist
     * so the card view can skip emitting an invalid background-image URL.
     */
    public function backgroundUrl(): string
    {
        if (! $this->hasBackground()) {
            return '';
        }

        // Cache-bust by file mtime so admin yang baru upload tidak terkena cache lama.
        $mtime = @filemtime(FCPATH . 'uploads/kartu-peserta/atlet.png') ?: time();

        return base_url('uploads/kartu-peserta/atlet.png') . '?v=' . $mtime;
    }
}

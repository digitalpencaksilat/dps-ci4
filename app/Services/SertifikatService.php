<?php

namespace App\Services;

use App\Services\Admin\Super\SettingWriterService;
use Config\Sertifikat as SertifikatConfig;
use RuntimeException;

class SertifikatService
{
    private SertifikatConfig $config;

    public function __construct()
    {
        $this->config = new SertifikatConfig();
    }

    // ============================================================
    //  Layout Config (DB-first, fallback to Config/Sertifikat.php)
    // ============================================================

    public function getLayoutConfig(): array
    {
        $raw = get_setting('sertifikat_layout');
        if ($raw !== null && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_merge($this->config->defaults, $decoded);
            }
        }
        return $this->config->defaults;
    }

    public function saveLayoutConfig(array $config): void
    {
        (new SettingWriterService())->setString(
            'sertifikat_layout',
            json_encode($config, JSON_UNESCAPED_UNICODE)
        );
    }

    // ============================================================
    //  Background Image
    // ============================================================

    public function uploadBackground(\CodeIgniter\HTTP\Files\UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new RuntimeException('File tidak valid.');
        }
        if (strtolower((string) $file->getExtension()) !== 'png') {
            throw new RuntimeException('Background sertifikat hanya boleh file PNG.');
        }
        if ($file->getSizeByUnit('kb') > 3072) {
            throw new RuntimeException('Ukuran file maksimal 3 MB.');
        }
        $dir = FCPATH . 'uploads/sertifikat';
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file->move($dir, 'sertifikat.png', true);
    }

    public function hasBackground(): bool
    {
        return is_file(FCPATH . 'uploads/sertifikat/sertifikat.png');
    }

    public function backgroundUrl(): string
    {
        if (! $this->hasBackground()) {
            return '';
        }
        $mtime = @filemtime(FCPATH . 'uploads/sertifikat/sertifikat.png') ?: time();
        return base_url('uploads/sertifikat/sertifikat.png') . '?v=' . $mtime;
    }

    // ============================================================
    //  Settings helpers
    // ============================================================

    public function domainHosting(): string
    {
        return (string) (get_setting('domain_hosting') ?: base_url());
    }

    public function hideSertifikatBackground(): bool
    {
        return get_setting('hide_sertifikat_background') === '1';
    }

    // ============================================================
    //  QR Code URL builder
    // ============================================================

    /**
     * Build QR code URL for a peserta's bagan page.
     * Uses domain_hosting as base, same as legacy.
     */
    public function qrcodeUrl(string $tipe, int $id): string
    {
        $base = rtrim($this->domainHosting(), '/');
        return $base . '/bagan-' . $tipe . '/' . $id;
    }

    // ============================================================
    //  Peserta Tanding Queries
    // ============================================================

    public function listPesertaTanding(): array
    {
        return db_connect()
            ->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding',
                'pt.status_sertifikat',
                'pt.nomor_sertifikat',
                'p.nama_pendaftar',
                'k.nama_kontingen',
                'p.nama_sekolah',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kt.label',
                'pmt.jenis_medali',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('perolehan_medali_tanding pmt', 'pmt.id_peserta_tanding = pt.id_peserta_tanding', 'left')
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()->getResult();
    }

    public function getPesertaTanding(int $id): ?object
    {
        return db_connect()
            ->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding',
                'pt.nomor_sertifikat',
                'p.nama_pendaftar',
                'k.nama_kontingen',
                'p.nama_sekolah',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kt.label',
                'kl.nama_kategori_lomba',
                'pmt.jenis_medali',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('perolehan_medali_tanding pmt', 'pmt.id_peserta_tanding = pt.id_peserta_tanding', 'left')
            ->where('pt.id_peserta_tanding', $id)
            ->get()->getRow();
    }

    public function ubahStatusSertifikatTanding(int $id): void
    {
        db_connect()
            ->table('peserta_tanding')
            ->where('id_peserta_tanding', $id)
            ->update(['status_sertifikat' => 'sudah_dicetak']);
    }

    // ============================================================
    //  Peserta Seni Queries
    // ============================================================

    public function listPesertaSeni(): array
    {
        return db_connect()
            ->table('peserta_seni ps')
            ->select([
                'ps.id_peserta_seni',
                'ps.status_sertifikat',
                'ps.nomor_sertifikat',
                'ps.id_kelompok_peserta_seni',
                'p.nama_pendaftar',
                'k.nama_kontingen',
                'p.nama_sekolah',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'sks.nama_seni',
                'sks.jenis_seni',
                'pms.jenis_medali',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('perolehan_medali_seni pms', 'pms.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()->getResult();
    }

    public function getPesertaSeni(int $id): ?object
    {
        return db_connect()
            ->table('peserta_seni ps')
            ->select([
                'ps.id_peserta_seni',
                'ps.nomor_sertifikat',
                'ps.id_kelompok_peserta_seni',
                'p.nama_pendaftar',
                'k.nama_kontingen',
                'p.nama_sekolah',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'sks.nama_seni',
                'sks.jenis_seni',
                'kl.nama_kategori_lomba',
                'pms.jenis_medali',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('perolehan_medali_seni pms', 'pms.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')
            ->where('ps.id_peserta_seni', $id)
            ->get()->getRow();
    }

    public function ubahStatusSertifikatSeni(int $id): void
    {
        db_connect()
            ->table('peserta_seni')
            ->where('id_peserta_seni', $id)
            ->update(['status_sertifikat' => 'sudah_dicetak']);
    }

    // ============================================================
    //  Kategori (label) builders
    // ============================================================

    public static function medaliLabel(?string $jenis): string
    {
        return match ($jenis) {
            'emas'     => 'I',
            'perak'    => 'II',
            'perunggu' => 'III',
            default    => '',
        };
    }

    /**
     * Teks kategori "PESERTA ..." (sertifikat partisipasi).
     */
    public function kategoriPeserta(object $p, string $tipe): string
    {
        $usia = strtoupper((string) ($p->nama_kategori_usia ?? ''));
        $jk   = strtoupper((string) ($p->jenis_kelamin ?? ''));
        if ($tipe === 'tanding') {
            $kelas = $p->label ? ' KELAS ' . strtoupper((string) $p->label) : '';
            return trim("PESERTA {$usia} {$jk}{$kelas}");
        }
        $seni = strtoupper(trim((string) ($p->jenis_seni ?? '') . ' ' . (string) ($p->nama_seni ?? '')));
        return trim("PESERTA {$usia} {$jk} SENI {$seni}");
    }

    /**
     * Teks kategori "JUARA I/II/III ..." (sertifikat juara/peraih medali).
     */
    public function kategoriJuara(object $p, string $tipe): string
    {
        $medali = self::medaliLabel($p->jenis_medali ?? null);
        $usia = strtoupper((string) ($p->nama_kategori_usia ?? ''));
        $jk   = strtoupper((string) ($p->jenis_kelamin ?? ''));
        if ($tipe === 'tanding') {
            $kelas = $p->label ? ' KELAS ' . strtoupper((string) $p->label) : '';
            return trim("JUARA {$medali} TANDING {$jk} {$usia}{$kelas}");
        }
        $seni = strtoupper(trim((string) ($p->jenis_seni ?? '') . ' ' . (string) ($p->nama_seni ?? '')));
        return trim("JUARA {$medali} {$seni} {$jk} {$usia}");
    }

    // ============================================================
    //  Statistics
    // ============================================================

    public function getStatistik(): array
    {
        $db = db_connect();
        $totalT = (int) $db->table('peserta_tanding')->countAll();
        $sudahT = (int) $db->table('peserta_tanding')->where('status_sertifikat', 'sudah_dicetak')->countAllResults();
        $totalS = (int) $db->table('peserta_seni')->countAll();
        $sudahS = (int) $db->table('peserta_seni')->where('status_sertifikat', 'sudah_dicetak')->countAllResults();
        return [
            'total_tanding' => $totalT,
            'sudah_tanding' => $sudahT,
            'total_seni'    => $totalS,
            'sudah_seni'    => $sudahS,
        ];
    }
}

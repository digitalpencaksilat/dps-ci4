# Audit UI/UX — DPS CI4

- Tanggal audit: 2026-06-07
- Cakupan: tiga permukaan UI (landing public, area kontingen, area admin), design system, dan pola lintas view.
- Sifat dokumen: temuan + rekomendasi. Belum ada perubahan kode yang dilakukan dari audit ini, kecuali yang disebut eksplisit di bagian "Riwayat perbaikan terkait".
- Metode: penelusuran statis view/CSS/layout + sampling view representatif. Item yang butuh verifikasi runtime ditandai khusus.

---

## 1. Ringkasan eksekutif

Fondasi UI project sudah kuat dan matang. Terdapat design system admin nyata di
[`public/assets/css/admin/admin.css`](../public/assets/css/admin/admin.css) dengan token warna brand,
komponen reusable (`admin-card`, `admin-table`, `metric-card`, `status-badge`), dan tiga tema terpisah
yang konsisten (public / kontingen / admin) memakai font Oswald + Poppins.

Adopsi design system tinggi: **106 file view** memakai `admin-card`/`section-title`. Masalah yang
tampak di permukaan (mis. header Cek Data Arsip) adalah **pengecualian terlokalisasi**, bukan pola umum.

Prioritas perbaikan paling berdampak:
1. Seragamkan sisa header tak konsisten (tipografi heading) — cepat, kosmetik, dampak visual langsung.
2. Hilangkan color drift biru Bootstrap default ke token brand merah.
3. (Dampak besar) Optimasi berat gambar + lazy-load, fallback CDN, dan UX tabel lebar di mobile.

---

## 2. Yang sudah baik (pertahankan)

- **Tokenisasi warna** via CSS variables di `:root` ([admin.css](../public/assets/css/admin/admin.css)):
  brand merah `--brand-primary:#c60000`, emas `--brand-secondary:#c5a017`, plus token corner
  tanding (`--corner-blue`, `--corner-red`). Tema terpusat → mudah dipelihara.
- **Responsif tertata**: sidebar collapse, overlay mobile, breakpoint `991.98px` / `575.98px`,
  sidebar off-canvas dengan transform.
- **Aksesibilitas dasar ada**: `lang="id"`, `aria-label` pada tombol toggle/close,
  `aria-expanded` + `aria-controls` pada menu kontingen, `aria-label="Navigasi kontingen"` pada sidebar.
- **Form umumnya berlabel**: rasio sehat ~251 `<label>` berbanding ~202 `form-control`/`form-select`
  pada area admin.
- **Empty/fallback state** dipikirkan (mis. poster fallback di landing
  [`pendaftaran/pages/home.php`](../app/Views/pendaftaran/pages/home.php)).
- **Detail teknis matang**: trik `:has()` mencegah dropdown aksi tabel terpotong oleh `overflow`,
  theming pagination DataTables, dan template cetak ID card sengaja pakai utility lokal agar
  `html2canvas` tidak perlu fetch CDN (bukti kesadaran tim atas risiko CDN).

---

## 3. Temuan terprioritisasi

Skala prioritas: **P1** = cepat & berdampak langsung, **P2** = penting (perubahan sedang),
**P3** = peningkatan jangka menengah / butuh audit lanjutan.

### P1-1 — Inkonsistensi tipografi heading kartu
- **Status**: ditemukan di ~23 file (mayoritas `app/Views/admin/super/*`, sebagian sekretariat
  `jadwal_tanding`, `jadwal_seni`, `id_card`).
- **Sifat**: bukan kartu yang lepas tema total. Pada banyak view legacy, `card-header` dipakai
  **di dalam** `admin-card` yang sudah bertema, tetapi heading memakai `card-title` (h6 polos)
  alih-alih `section-title`/`eyebrow` (Oswald, uppercase). Akibatnya tipografi judul section
  tidak seragam dengan halaman lain.
- **Bukti**: [`admin/super/jadwal_tanding_diagnosis.php`](../app/Views/admin/super/jadwal_tanding_diagnosis.php)
  (`<div class="admin-card">` → `<div class="card-header ...">` → `<h6 class="card-title">`).
- **Rekomendasi**: buat satu partial header bersama, mis.
  `app/Views/shared_components/admin/page_header.php` dengan slot `eyebrow` + `section-title` + `subtitle`,
  lalu migrasikan view legacy secara bertahap. Menghindari tambal-sulam per halaman.

### P1-2 — Color drift ke biru Bootstrap default
- **Status**: ad-hoc `bg-primary` / `text-primary` (biru `#0d6efd`) di beberapa tempat, bertabrakan
  dengan brand merah.
- **Bukti**: [`admin/sekretariat/cek_data_arsip/_detail_modal_body.php`](../app/Views/admin/sekretariat/cek_data_arsip/_detail_modal_body.php)
  memakai `card-header bg-primary text-white`; spinner loading memakai `text-primary`.
  Juga muncul di sebagian view `admin/super`.
- **Rekomendasi**: ganti ke token brand (`btn-admin-brand`, `--admin-accent`) atau utility tema
  (`status-badge.info` sudah tersedia bila perlu nuansa biru resmi). Tambahkan kelas spinner brand.

### P2-1 — Berat aset gambar + tidak ada lazy-load
- **Status terverifikasi**: **nol** penggunaan `loading="lazy"` di seluruh `app/Views`.
- **Bukti berat file** (`public/assets/images/landing/`):
  - `landing-hero-bg.jpg` ≈ 1.0 MB
  - `landing-category-tanding.jpg` ≈ 1.05 MB, `landing-category-ganda.jpg` ≈ 0.99 MB,
    `landing-category-tunggal.jpg` ≈ 0.94 MB, `landing-category-beregu.jpg` ≈ 0.80 MB
  - `public/assets/pendaftaran/img/live-medali.png` ≈ 0.93 MB,
    `alur-background.jpg` ≈ 0.65 MB, `timeline.jpeg` ≈ 0.37 MB
- **Dampak**: first paint berat di koneksi lambat (3G / wifi venue). Landing public adalah halaman
  paling sering diakses calon peserta.
- **Rekomendasi**: konversi ke WebP, tambahkan `loading="lazy"` untuk gambar di bawah lipatan,
  sediakan `srcset`/`sizes` responsif, dan kompres aset > 300 KB.

### P2-2 — Ketergantungan penuh pada CDN tanpa fallback lokal
- **Status terverifikasi**: aset inti dimuat via `online_asset()` di
  [`app/Helpers/ci3_compat_helper.php`](../app/Helpers/ci3_compat_helper.php) — Bootstrap 5.3.3,
  DataTables 1.13.8 (+responsive/buttons), jQuery 3.7.1, FontAwesome 6.5.2, Select2, Toastr, SweetAlert2,
  semuanya jsdelivr/cdnjs/datatables.net. Dipakai di semua layout
  ([admin](../app/Views/layouts/admin.php), [kontingen](../app/Views/layouts/kontingen.php),
  [pendaftaran](../app/Views/pendaftaran/template.php)).
- **Dampak**: aplikasi event sering dipakai di venue dengan koneksi tidak stabil. Bila CDN gagal,
  CSS/JS inti tidak termuat → UI rusak total.
- **Catatan positif**: template cetak ID card sudah sengaja memakai utility CSS lokal agar tidak
  bergantung CDN saat render `html2canvas` — pola ini bisa diperluas.
- **Rekomendasi**: simpan salinan lokal aset kritikal di `public/assets/vendor/` dan jadikan
  `online_asset()` mengembalikan path lokal (atau CDN dengan fallback `onerror`). Minimal untuk
  Bootstrap CSS/JS, jQuery, dan DataTables.

### P2-3 — UX tabel lebar di mobile
- **Status**: `.table.admin-table` memaksa `white-space: nowrap` dan tabel `width: max-content`
  dengan scroll horizontal ([admin.css](../public/assets/css/admin/admin.css)).
- **Bukti**: Data Atlet ([`admin/sekretariat/pendaftar/index.php`](../app/Views/admin/sekretariat/pendaftar/index.php))
  punya 12 kolom → di ponsel menjadi scroll horizontal sangat panjang.
- **Rekomendasi**: aktifkan DataTables `responsive` (sudah ada `datatables_responsive_*` di
  `online_asset`) dengan prioritas kolom, atau tampilan kartu (stacked) untuk layar kecil pada
  tabel terlebar.

### P3-1 — Tidak ada skip-to-content link
- **Status terverifikasi**: tidak ada `skip-link`/skip-to-content di layout mana pun.
- **Dampak**: pengguna keyboard/screen reader harus menelusuri seluruh sidebar tiap halaman.
- **Rekomendasi**: tambahkan link "Lewati ke konten" tersembunyi yang muncul saat fokus, menuju
  `id` kontainer `<main>` di setiap layout.

### P3-2 — Indikator fokus keyboard lemah pada kontrol custom
- **Status**: hover/focus styling ada, tetapi `:focus-visible` eksplisit minim di luar
  `super-mode-card`. Banyak tombol pill custom (`.btn-admin-brand`, action toggle tabel) tanpa
  outline fokus yang jelas.
- **Rekomendasi**: tambahkan aturan global `:focus-visible` (outline kontras) untuk tombol, link nav,
  dan kontrol interaktif custom.

### P3-3 — Risiko kompatibilitas `:has()`
- **Status**: penanganan overflow dropdown aksi tabel bergantung penuh pada selector CSS `:has()`.
- **Dampak**: didukung browser modern (2023+), tetapi Safari < 15.4 dan browser lama tidak →
  menu aksi bisa terpotong di perangkat lama.
- **Rekomendasi**: bila target mencakup perangkat lama, sediakan fallback (mis. memindah menu ke
  `body` via Popper/JS, atau strategi `position` alternatif).

### P3-4 — Inline style tersebar
- **Status**: ~156 kemunculan `style="` di 33 file. **Mayoritas wajar** (view PDF/print dan
  positioning kanvas ID card). Sebagian kecil di view biasa bisa dipindah ke kelas utilitas tema.
- **Rekomendasi**: prioritas rendah; rapikan saat menyentuh file terkait.

---

## 4. Verifikasi yang masih diperlukan (runtime)

Item berikut tidak dapat dipastikan dari analisis statis dan perlu pengujian di browser:

- **Kontras warna** WCAG AA: teks muted (`--admin-muted:#7d6670`) pada latar putih, badge status,
  dan teks di atas gradient brand. Perlu cek dengan kontras checker.
- **Asosiasi label–input**: rasio label sehat, tetapi perlu memastikan tiap input punya `for`/`id`
  atau dibungkus `<label>`, dan kontrol placeholder-only (mis. search DataTables) punya `aria-label`.
- **Alt text gambar dinamis**: logo event dan poster sudah ber-`alt`; perlu cek thumbnail dinamis
  lain (mis. foto peserta) konsisten ber-`alt`.
- **Navigasi keyboard penuh**: urutan tab, trap fokus pada modal, dan dismissal via Escape.

---

## 5. Rencana tindak lanjut yang disarankan

| Prioritas | Item | Estimasi dampak | Estimasi usaha |
|-----------|------|-----------------|----------------|
| P1-1 | Partial header bersama + migrasi heading legacy | Tinggi (konsistensi) | Sedang |
| P1-2 | Ganti `bg-primary`/`text-primary` ke token brand | Sedang | Rendah |
| P2-1 | Kompres + WebP + lazy-load gambar landing | Tinggi (perf) | Sedang |
| P2-2 | Fallback lokal aset CDN kritikal | Tinggi (keandalan) | Sedang |
| P2-3 | Responsive table untuk tabel terlebar | Sedang (mobile) | Sedang |
| P3-1 | Skip-to-content link | Rendah–Sedang (a11y) | Rendah |
| P3-2 | Aturan `:focus-visible` global | Sedang (a11y) | Rendah |
| P3-3 | Fallback dropdown tanpa `:has()` | Rendah (kondisional) | Sedang |
| P3-4 | Rapikan inline style non-print | Rendah | Rendah |

Urutan eksekusi disarankan: **P1-1 → P1-2** (cepat, terlihat), lalu **P2-1 → P2-2 → P2-3**
(dampak besar, bertahap), kemudian **P3** sesuai kapasitas.

---

## 6. Riwayat perbaikan terkait

- 2026-06-07 — **P2-1 (selesai)**: gambar landing dioptimasi menyeluruh.
  Ditambahkan `loading="lazy"` + `decoding="async"` pada gambar di bawah lipatan
  ([`home.php`](../app/Views/pendaftaran/pages/home.php) kartu kategori), serta
  `fetchpriority="high"` + `decoding="async"` pada poster hero (LCP) dan `decoding="async"`
  pada logo topnav. Gambar `public/assets/images/landing/*` yang sangat oversized (kategori
  4512×3008, hero 2816×1536) di-resize + recompress in-place (kategori → 1280px, hero → 1920px,
  kualitas JPEG 82): total **4.6 MB → 1.3 MB (~72% lebih kecil)**, reversible via git.
  Ditambahkan varian **WebP** (`cwebp` q80, total **420 KB**) + helper
  [`webp_picture()`](../app/Helpers/ui_helper.php) (di-autoload via `Config/Autoload.php`) yang
  meng-emit `<picture>` dengan sumber WebP + fallback gambar asli, dan **degrade aman** ke `<img>`
  biasa jika varian WebP tidak ada (aman untuk gambar upload dinamis). Background hero memakai
  `image-set()` WebP dengan fallback JPEG (+`-webkit-` prefix). Sisa opsional: `srcset` multi-lebar
  benar-benar responsif, dan optimasi gambar berat di komponen yang belum aktif
  (`pendaftaran/img/live-medali.png`, `alur-background.jpg` — saat ini hanya direferensikan di
  kode backup/dead, tidak berdampak live).
- 2026-06-07 — **P1-1 (selesai sebagian besar)**: dibuat partial header bersama
  [`shared_components/admin/page_header.php`](../app/Views/shared_components/admin/page_header.php)
  (param `eyebrow`/`title`/`subtitle`/`icon`/`actions`/`titleTag`/`titleSize`). Halaman
  `jadwal_tanding/index.php` dan `jadwal_seni/index.php` dimigrasi ke partial; judul Inggris pada
  halaman `jadwal_*/show.php` diterjemahkan ke Indonesia. Ditambahkan shim CSS terscoped di
  [admin.css](../public/assets/css/admin/admin.css) agar sisa `.admin-card .card-header .card-title`
  legacy (diagnosis/overview/penjadwalan/id_card) otomatis memakai tipografi `section-title`
  (Oswald uppercase) + header transparan, tanpa churn 23 file. Migrasi markup penuh untuk halaman
  diagnostik/overview internal dapat dilanjutkan bertahap.
- 2026-06-07 — **P1-2 (selesai)**: color drift biru Bootstrap dihapus. Ditambah utility
  `.bg-admin-brand` dan `.text-corner-blue` di [admin.css](../public/assets/css/admin/admin.css).
  Diganti: header modal arsip (`bg-primary`→`bg-admin-brand`), spinner arsip
  (`text-primary`→`text-admin-brand`), badge ID card cetak (`bg-primary`→`bg-admin-brand`),
  metric diagnosis seni (`text-primary`→`text-admin-brand`), dan nama peserta sudut biru seni
  (`text-primary`→`text-corner-blue`, sesuai semantik sudut). Satu-satunya `bg-primary` tersisa
  (`penjadwalan_tanding_otomatis.php`) memang sudah di-override lokal ke `--schedule-accent`.
- 2026-06-07 — Halaman **Cek Data Arsip** (sekretariat): header kartu diselaraskan ke tema
  `admin-card`/`eyebrow`/`section-title` (mengikuti halaman Data Atlet) dan error CSRF pada aksi
  Detail diperbaiki (set header `X-CSRF-TOKEN` di controller + refresh token di JS). Lihat
  [`cek_data_arsip/index.php`](../app/Views/admin/sekretariat/cek_data_arsip/index.php) dan
  [`CekDataArsipController.php`](../app/Controllers/Admin/Sekretariat/CekDataArsipController.php).
  Catatan: color drift biru di `_detail_modal_body.php` sudah diperbaiki pada P1-2.

---

## 7. Referensi file kunci

- Design system: [`public/assets/css/admin/admin.css`](../public/assets/css/admin/admin.css),
  [`public/assets/css/kontingen-theme.css`](../public/assets/css/kontingen-theme.css)
- Layout: [`app/Views/layouts/admin.php`](../app/Views/layouts/admin.php),
  [`app/Views/layouts/kontingen.php`](../app/Views/layouts/kontingen.php),
  [`app/Views/pendaftaran/template.php`](../app/Views/pendaftaran/template.php)
- Helper CDN: [`app/Helpers/ci3_compat_helper.php`](../app/Helpers/ci3_compat_helper.php) (`online_asset()`)
- Contoh view bertema baik: [`app/Views/admin/sekretariat/pendaftar/index.php`](../app/Views/admin/sekretariat/pendaftar/index.php)
- Contoh inkonsistensi: [`app/Views/admin/super/jadwal_tanding_diagnosis.php`](../app/Views/admin/super/jadwal_tanding_diagnosis.php)

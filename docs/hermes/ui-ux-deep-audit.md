# Deep UI/UX Review Audit

## Scope

- Layout admin: `app/Views/layouts/admin.php`
- Layout kontingen: `app/Views/layouts/kontingen.php`
- CSS admin: `public/assets/css/admin/admin.css`
- CSS kontingen: `public/assets/css/kontingen-theme.css`
- Sampel halaman: dashboard super, sekretariat, bendahara, kontingen, list/detail kontingen, peserta, pembayaran.

## Audit Mode

- Mode: Deep UI/UX Review + Redesign Recommendation
- Device target: 50/50 desktop dan mobile
- Design system: Bootstrap + custom theme merah/emas

## Executive Diagnosis

- Struktur UI sudah cukup rapi dan modern untuk hasil migrasi CI3->CI4: sudah ada sidebar, card system, DataTables, empty state, modal, toast, dan responsive breakpoint.
- Masalah utama ada di konsistensi dan scalability: beberapa halaman sudah "modern Bootstrap", tapi beberapa detail page masih sangat padat, inline-heavy, dan raw table-heavy.
- Untuk mobile 50/50, area paling berisiko adalah tabel besar, modal form panjang, sidebar admin yang menunya sangat banyak, dan tombol aksi kecil/dropdown icon-only.

## Top Findings

- High: Sidebar sekretariat terlalu panjang dan berat secara kognitif; user mobile harus scroll menu panjang untuk menemukan fitur.
- High: Tabel data-heavy masih mengandalkan horizontal scroll; kurang ideal untuk mobile 50/50.
- High: Detail kontingen sekretariat terlalu padat dan sebagian markup ditulis satu baris panjang; sulit dirawat dan berpotensi inkonsisten.
- Medium: Focus state/accessibility belum distandardisasi di banyak komponen custom.
- Medium: Status badge belum sepenuhnya konsisten antara admin/kontingen dan antar halaman.
- Medium: Form modal panjang belum punya step/progress yang jelas, terutama input peserta + arsip.

## Findings By Area

### 1. Information Architecture

- Problem: `app/Views/layouts/admin.php:148` sampai `app/Views/layouts/admin.php:280` membuat sidebar sekretariat sangat panjang: Data Kontingen, Atlet, Statistik, Kategori Tanding, Pesilat Terbaik, Kategori Seni, Jadwal, Medal, Tools.
- Impact: Untuk role sekretariat, menu sudah seperti "mega navigation" dalam sidebar. Di mobile, user perlu membuka menu dan scroll panjang; fitur penting bisa terasa tersembunyi.
- Recommendation: Kelompokkan ulang sidebar menjadi 4-5 cluster utama:
  - Operasional: Kontingen, Atlet, Peserta Tanding, Kelompok Seni
  - Kompetisi: Kategori Tanding, Kategori Seni, Jadwal
  - Hasil: Statistik, Medal, Pesilat Terbaik
  - Administrasi: Tools, Sertifikat, Pengadaan Medali
  - Tambahkan search/quick command sederhana di sidebar untuk desktop, atau "Favorit" untuk menu paling sering dipakai.

### 2. Role Separation

- Problem: Layout admin memakai satu file besar untuk super, sekretariat, dan bendahara (`app/Views/layouts/admin.php`). Ini membuat navigasi role-specific bercampur dan rawan makin sulit dirawat.
- Impact: Saat role bertambah atau menu berubah, potensi inkonsistensi dan typo meningkat. Contoh ada string mode `perngaturan_kategori_lomba` di `app/Views/layouts/admin.php:105` dan `app/Views/admin/super/dashboard.php:36`; secara UI mungkin jalan kalau konsisten, tapi typo ini bikin maintenance membingungkan.
- Recommendation: Pecah menu menjadi partial/config:
  - `app/Views/layouts/partials/admin_sidebar_super.php`
  - `app/Views/layouts/partials/admin_sidebar_sekretariat.php`
  - `app/Views/layouts/partials/admin_sidebar_bendahara.php`
  - Atau array config menu per role lalu render dengan partial reusable.

### 3. Layout & Visual Hierarchy

- Problem: Dashboard bendahara sudah punya hero dan metric card yang kuat (`app/Views/admin/bendahara/dashboard.php`), sementara dashboard sekretariat lebih sederhana dan terasa kurang action-oriented.
- Impact: Sekretariat punya banyak pekerjaan operasional, tapi dashboard belum benar-benar memberi prioritas: apa yang harus dicek hari ini, mana yang bermasalah, mana yang butuh tindakan.
- Recommendation: Tambahkan "task dashboard" untuk sekretariat:
  - Kontingen belum input peserta
  - Peserta belum memilih kategori
  - Data arsip belum lengkap
  - Jadwal/kelas yang belum siap
  - CTA langsung: "Cek Kontingen Bermasalah", "Lihat Peserta Belum Lengkap".

### 4. Tabel & Mobile Experience

- Problem: Banyak tabel besar memakai `table-responsive` / `admin-table-scroller`, misalnya `app/Views/admin/sekretariat/kontingen/show.php:84`, `app/Views/kontingen/peserta/index.php:40`, `app/Views/admin/bendahara/pembayaran/index.php:20`.
- Impact: Horizontal scroll memang aman secara teknis, tapi kurang nyaman untuk user mobile 50/50, terutama tabel peserta dengan NIK, KK, TB, BB, sekolah, tanggal lahir.
- Recommendation: Untuk mobile, buat pola alternatif:
  - Desktop: DataTable normal.
  - Mobile: card list per row dengan 3 info utama + "Lihat detail/Edit".
  - Kolom prioritas mobile: nama, status/kategori, aksi.
  - Kolom detail seperti NIK/KK/alamat masuk drawer/modal detail.
- Quick fix: aktifkan DataTables responsive secara konsisten untuk admin juga. Saat ini `initAdminDataTable` di `app/Views/layouts/admin.php:362` memakai `responsive: false`, sementara kontingen di `app/Views/layouts/kontingen.php:147` memakai `responsive: true`.

### 5. Forms & Modal Flow

- Problem: Form peserta kontingen cukup panjang dan digabung dalam modal besar dengan tabs Data Peserta + Arsip Peserta (`app/Views/kontingen/peserta/index.php:121`).
- Impact: Di mobile, modal panjang + tab + upload arsip bisa membuat user kehilangan konteks. Kalau validasi gagal, user bisa bingung field mana yang bermasalah, apalagi jika error ada di tab lain.
- Recommendation:
  - Mode mobile: ubah modal menjadi full-screen modal (`modal-fullscreen-md-down`).
  - Tambahkan indikator step: `1. Data Peserta`, `2. Arsip`, `3. Review`.
  - Jika error ada di tab Arsip, beri badge error di tab.
  - Tambahkan ringkasan sebelum submit untuk data penting: nama, kategori, arsip wajib.
- Good point: Form sudah punya label, `required`, `invalid-feedback`, `inputmode`, dan validasi file. Ini dasar UX yang bagus.

### 6. Actions & Button Clarity

- Problem: Beberapa action memakai tombol kecil/dropdown icon-only, misalnya aksi peserta di `app/Views/kontingen/peserta/index.php:70`.
- Impact: Icon ellipsis lebih hemat ruang, tapi untuk user non-teknis di mobile, aksi "Edit/Hapus" bisa kurang discoverable.
- Recommendation:
  - Desktop boleh tetap dropdown.
  - Mobile gunakan action row/card dengan tombol teks: "Edit", "Hapus".
  - Tambahkan `aria-label` pada tombol icon-only, misalnya "Buka menu aksi peserta".
  - Gunakan warna destructive hanya untuk aksi hapus, bukan semua outline-danger.

### 7. Accessibility & Focus

- Problem: CSS custom punya hover/focus untuk beberapa elemen, tapi belum ada sistem focus-visible global untuk tombol, link sidebar, input, card-link, dan dropdown. Di `public/assets/css/kontingen-theme.css` tidak ditemukan pola `focus-visible`.
- Impact: User keyboard bisa kesulitan melihat posisi fokus, terutama di sidebar gelap, card shortcut, dan tabel.
- Recommendation: Tambahkan standar focus ring global:
  - `.btn:focus-visible`
  - `.sidebar-link:focus-visible`
  - `.admin-nav-link:focus-visible`
  - `.shortcut-card:focus-visible`
  - `.form-control:focus`
  - Gunakan outline kontras, bukan hanya perubahan warna/background.

### 8. Status & Badge System

- Problem: Status badge muncul dalam beberapa gaya: `status-badge success/warning/neutral`, Bootstrap `badge text-bg-success`, `badge text-bg-warning`, `badge text-bg-danger`.
- Impact: Status "lunas/menunggu/belum lunas/aktif/ditutup" bisa terlihat beda antar halaman admin dan kontingen.
- Recommendation: Buat satu partial/helper badge status:
  - `payment_status_badge($status)`
  - `feature_access_badge($enabled, $label)`
  - `data_status_badge($status)`
  - Gunakan style yang sama di admin dan kontingen.

### 9. Content & Microcopy

- Problem: Beberapa copy masih teknis/admin, misalnya "Tampilan disesuaikan dengan tabel peserta tanding CI3" di `app/Views/admin/sekretariat/kontingen/show.php:116` dan "Mengikuti tabel kelompok peserta seni CI3" di `app/Views/admin/sekretariat/kontingen/show.php:146`.
- Impact: Ini terasa seperti catatan migrasi, bukan bahasa produk untuk user akhir.
- Recommendation: Ganti menjadi user-facing:
  - "Daftar peserta yang terdaftar pada kategori tanding."
  - "Kelola kelompok peserta seni, pool, nomor undi, dan status pembayaran."

### 10. Code Maintainability That Affects UX

- Problem: `app/Views/admin/sekretariat/kontingen/show.php:197` sampai `app/Views/admin/sekretariat/kontingen/show.php:207` berisi markup sangat panjang satu baris untuk kontrol, modal, dan form.
- Impact: Sulit review, sulit konsisten, dan riskan saat ingin memperbaiki UI/UX. Ini biasanya membuat UX cepat drift antar halaman.
- Recommendation: Pecah menjadi partial:
  - `_summary_cards.php`
  - `_tabs_pendaftar.php`
  - `_tabs_tanding.php`
  - `_tabs_seni.php`
  - `_modal_edit_kontingen.php`
  - `_modal_peserta.php`
  - `_danger_zone.php`

## Redesign Recommendation

### Layout Direction

- Pertahankan visual merah/emas karena cocok dengan identitas pencak silat/event.
- Buat dashboard tiap role lebih task-based, bukan hanya metric-based.
- Untuk mobile, prioritaskan card list + CTA jelas daripada table penuh.

### Component Changes

- Buat komponen standar: page header, section card, stat card, status badge, empty state, action toolbar, mobile row card.
- Gunakan partial Bootstrap agar form/tabel konsisten.
- Tambahkan `modal-fullscreen-md-down` untuk form panjang.

### Visual System

- Admin dan kontingen sudah punya token CSS masing-masing, tapi sebaiknya disatukan sebagian:
  - radius scale
  - shadow scale
  - badge colors
  - focus ring
  - table spacing
  - button hierarchy
- Warna `danger` saat ini banyak dipakai sebagai brand primary. Pisahkan konsep:
  - brand red untuk primary action
  - danger red untuk hapus/error
  - warning untuk menunggu
  - success untuk lunas/aktif

### UX Copy Improvements

- Hilangkan copy internal seperti "CI3".
- Tombol submit dibuat spesifik: "Simpan Peserta", "Simpan Kelompok", "Upload Bukti Pembayaran".
- Empty state sudah cukup baik; lanjutkan pola ini untuk semua tabel kosong.

## Priority Roadmap

### Quick Wins (1-2 hari)

- Aktifkan focus ring global di `public/assets/css/admin/admin.css` dan `public/assets/css/kontingen-theme.css`.
- Tambahkan `aria-label` untuk tombol icon-only/dropdown aksi.
- Ganti copy internal "CI3" di halaman user/admin.
- Samakan style badge status pembayaran.
- Ubah modal panjang jadi `modal-fullscreen-md-down` untuk mobile.

### Medium Effort

- Pecah sidebar admin per role menjadi partial/config.
- Buat komponen reusable untuk page header, action toolbar, empty state, status badge.
- Buat mobile card view untuk tabel peserta, pembayaran, dan detail kontingen.
- Tambahkan dashboard "perlu tindakan" untuk sekretariat dan bendahara.

### Larger Redesign

- Rancang ulang IA sekretariat menjadi task-based navigation.
- Buat design system Bootstrap internal: tokens, components, states, accessibility rules.
- Buat pola responsive khusus data-heavy pages: desktop table, mobile card/detail drawer.
- Buat "review/summary step" untuk flow pembayaran dan input peserta + arsip.

## Notes / Limitations

- Review ini berdasarkan code, bukan live browser. Jadi belum memverifikasi rendering final, overflow nyata, console error, atau perilaku DataTables saat data real banyak.
- Untuk audit berikutnya, paling bagus jalankan app lalu cek 4 flow live: login role, tambah peserta, checkout pembayaran, detail kontingen sekretariat.

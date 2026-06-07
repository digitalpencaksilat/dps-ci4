# Rencana: Label Babak (Cetak) + Auto-shrink Landscape — Pencetakan Bagan DPS CI4

- **Tanggal**: 2026-06-08
- **Project**: DPS CI4 (`/Applications/XAMPP/xamppfiles/htdocs/dps-ci4`)
- **Fitur induk**: Pencetakan Bagan (menu Tools → Sekretariat)
- **Status**: Rencana disetujui sebagian — menunggu eksekusi

---

## 1. Tujuan

1. Menampilkan **keterangan babak** di atas tiap kolom bagan saat **dicetak**, untuk mempermudah pembaca tahu peserta main di babak apa. Contoh label: `1/8 Final`, `1/4 Final`, `Semi Final`, `Final`.
2. Jika jumlah bagan banyak (peserta banyak), tampilan bagan **otomatis mengecil (auto-shrink)** agar muat dalam lebar kertas.

## 2. Keputusan User (final)

- **Label babak hanya untuk cetak** — halaman interaktif (pool tanding/seni di sekretariat & drawing) TIDAK diubah.
- **Kertas landscape** — acuan lebar auto-shrink memakai A4 landscape.
- **Seni pool** tidak tersentuh (tabel hasil, bukan bracket).

## 3. Konteks Teknis (hasil investigasi)

- Plugin `jquery.bracket` me-render tiap babak sebagai kolom `.round` di dalam `.jQBracket > .bracket`.
- Plugin **tidak punya fitur judul babak bawaan** → label harus disuntik via JS setelah render.
- Bisa ada lebih dari satu `.bracket` dalam satu kompetisi (bracket utama + perebutan juara 3).
- Aturan nama babak (konsisten dengan `public/assets/bracket-pertandingan/customBracket.js` → fungsi `get_babak()`):
  - jumlah match di kolom `1` → **Final**
  - `2` → **Semi Final**
  - `4` → **1/4 Final**
  - `8` → **1/8 Final**
  - umum: `1/N Final`
  - `.bracket` kedua → **Perebutan Juara 3**
- Lebar bagan fixed: `teamWidth 260 + matchMargin 60 + roundMargin 60` per kolom → peserta banyak pasti melebihi lebar kertas.

## 4. Strategi Implementasi

Karena label babak HANYA untuk cetak, seluruh logika ditaruh **di print view**, BUKAN di partial bracket. Partial `bagan_pertandingan.php` & `bagan_battle_seni.php` TIDAK diubah sama sekali (zero-risk untuk halaman interaktif).

### File baru: `public/assets/bracket-pertandingan/bracketPrintEnhancer.js`

- `labelRounds(scopeEl)`:
  - Untuk tiap `.jQBracket .bracket` di dalam scope, hitung jumlah `.teamContainer` di kolom `.round` pertama.
  - Tiap kolom `.round` ke-r diberi header babak: `matches = first / 2^r`, dipetakan ke nama babak via aturan `get_babak()`.
  - Bracket ke-2 dalam satu kompetisi → label "Perebutan Juara 3".
  - Header babak: pill rapi (font Oswald, warna brand merah, `print-color-adjust: exact`).
- `fitToWidth(blockEl, availableWidthPx)`:
  - Ukur lebar natural `.jQBracket`.
  - `scale = min(1, available / natural)` — HANYA mengecilkan, tidak pernah memperbesar.
  - Terapkan `transform: scale()` + `transform-origin: top left`.
  - Koreksi tinggi container agar tidak ada gap kosong setelah diskalakan.
- `enhanceForPrint()`:
  - Jalankan `labelRounds()` lalu `fitToWidth()` untuk tiap blok bagan.
  - Pakai lebar acuan landscape.

### Edit 2 print view: `cetak_tanding.php` & `cetak_seni_battle.php`

1. Tambahkan `@page { size: A4 landscape; margin: 10mm; }` + CSS header babak.
2. Load `bracketPrintEnhancer.js`.
3. Atur urutan script `window load`:
   `render bracket (via partial) → tunggu siap → labelRounds → fitToWidth → window.print()`.
   Delay print yang sekarang (1200ms) dipakai memastikan bracket selesai sebelum enhancer jalan.

## 5. Detail Auto-shrink (Landscape)

- Lebar konten acuan: A4 landscape ≈ 297mm − margin 20mm = **277mm**.
- Pendekatan px via `offsetWidth` wrapper cetak supaya akurat lintas browser; fallback ke 277mm bila 0.
- Hanya mengecilkan (`scale ≤ 1`) — bagan 2 peserta tetap proporsional.
- Tiap bagan diskalakan independen (satu kategori bisa beda jumlah peserta).

## 6. File yang Disentuh

- **Baru**: `public/assets/bracket-pertandingan/bracketPrintEnhancer.js`
- **Edit**: `app/Views/admin/sekretariat/pencetakan_bagan/cetak_tanding.php`, `cetak_seni_battle.php`
- **TIDAK diubah**: partial bracket, halaman interaktif, seni pool, controller, service, route.

## 7. Verifikasi

- `php -l` pada 2 print view.
- Render-test via spark command sementara: pastikan `bracketPrintEnhancer.js` ter-include dan enhancer terpanggil. (Label & scale aktual butuh browser karena dihitung runtime dari DOM bracket → dicatat sebagai QA manual.)
- Uji data nyata: kompetisi id 7 (10 kelompok → ada 1/8 & 1/4 Final) dan kategori kecil (2 peserta → langsung Final).
- `chmod 644` file JS baru.
- Commit + push.

## 8. Risiko

- Rendah & terisolasi ke cetak. Tidak menyentuh data/save/skema.
- **Wajib QA manual di browser**: hasil `transform: scale()` di dialog print Chrome (kadang perlu kalibrasi lebar acuan). Ada fallback lebar landscape + origin top-left, tapi hasil cetak final perlu dilihat sekali.

## 9. Catatan Eksekusi

### Revisi 2026-06-08 — perbaikan 3 masalah (label salah, kurang presisi, terpotong)

Pekerjaan awal (sesi lain) sudah membuat `bracketPrintEnhancer.js` + wiring 2 print view,
tapi ada 3 masalah yang dilaporkan user. Root cause ditemukan via inspeksi DOM bracket
asli (harness statis di `public/_debug/`, dibuka browser — Apache serve langsung, bypass
maintenance CI4). Hasil inspeksi sample id 74 (9 peserta): kolom pertama = **8 .match**
(bukan teamContainer/2), `.round` float:left + `.teamContainer` position:absolute.

**Root cause & fix:**

1. **Penamaan babak salah** — enhancer lama menghitung `teamContainers / 2` lalu masih
   dibagi lagi, dan menghitung ulang tiap kolom dari DOM (terkecoh match perebutan juara 3
   yang menambah `.match` di kolom terakhir).
   → Fix: `firstMatches = jumlah .match di kolom pertama`, lalu `matchesInCol =
   firstMatches / 2^idx`. Hasil terverifikasi: 9 peserta → 1/8, 1/4, Semi, Final;
   4 peserta → Semi, Final.

2. **Kurang presisi** — label disisip via `insertBefore` di dalam `.round`, padahal
   `.teamContainer` diposisikan absolute oleh plugin → kolom jadi tak sejajar.
   → Fix: label dipasang **absolute relatif ke `.jQBracket`** memakai `round.offsetLeft`
   + `round.offsetWidth`; ruang label direservasi via `padding-top:30px` pada `.jQBracket`.
   Terverifikasi: label.left = 10/365/720/1075 = persis offset tiap kolom.

3. **Bagan terpotong dengan card** — `fitToWidth` lama menaruh `height` + `overflow:hidden`
   pada `.bagan` (row terluar berisi header+card) → card kepotong.
   → Fix: collapse box pada **parent langsung `.jQBracket`** (holder), bukan `.bagan`.
   Holder di-set `width/height = natural × scale` + `overflow:hidden` sehingga card
   membungkus tepat. Terverifikasi: scrollHeight 870 × 0.664 = 578px = holder height.

**File final:**
- `public/assets/bracket-pertandingan/bracketPrintEnhancer.js` — ditulis ulang penuh.
- `cetak_tanding.php` & `cetak_seni_battle.php` — sudah load enhancer + `@page landscape`
  + panggil `enhanceForPrint()` sebelum `window.print()` (tidak diubah pada revisi ini).

**Verifikasi:** inspeksi DOM via browser (label benar + alignment + tinggi collapse akurat)
pada sample 9 & 4 peserta; `chmod 644`; harness debug dihapus.

**Acuan lebar landscape:** `PRINT_CONTENT_WIDTH = 950px` (≈A4 landscape usable − padding card).

**QA manual tersisa:** buka cetak bagan di browser (sesi sekretariat), pastikan hasil
`window.print()` Chrome menampilkan label + skala pas di kertas landscape.

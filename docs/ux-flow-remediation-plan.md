# Rencana Solusi UX Flow

## Tujuan

Dokumen ini menjelaskan detail solusi untuk temuan UX flow pada area:

- user kontingen
- admin bendahara
- admin sekretariat

Dokumen ini dipakai sebagai acuan implementasi, QA manual, dan prioritas backlog.

Tanggal review: 2026-05-26  
Project: `dps-ci4`

## Ringkasan Prioritas

| Prioritas | Fokus | Alasan |
|---|---|---|
| Tinggi | Bendahara confirm/reject pembayaran | Aksi pembayaran bersifat sensitif dan berdampak langsung ke status transaksi |
| Sedang | Kontingen payment feedback, modal validation, AJAX failure | Mengurangi kebingungan dan kehilangan input user |
| Sedang | Sekretariat detail continuity dan destructive action | Memperbaiki flow kerja operator dan mengurangi risiko salah aksi |
| Rendah | Back navigation, copy empty state, label minor | Penyempurnaan pengalaman pakai |

## Daftar Temuan dan Solusi

### 1. Bendahara Bisa Menolak Transaksi yang Sudah Lunas

Status: belum dikerjakan  
Prioritas: tinggi  
Target file:

- `app/Views/admin/bendahara/pembayaran/show.php`
- `app/Controllers/Admin/Bendahara/PembayaranController.php`
- service pembayaran bendahara terkait jika ada validasi status di service

Masalah:

- Pada detail pembayaran, tombol `Tolak Pembayaran` tetap tampil walau transaksi sudah `lunas`.
- Admin bisa melihat status lunas, tetapi aksi destruktif tetap tersedia.

Solusi UI:

- Sembunyikan tombol `Tolak Pembayaran` jika `status_pembayaran === 'lunas'`.
- Tampilkan badge/informasi statis: `Transaksi sudah lunas dan tidak dapat ditolak.`

Solusi backend:

- Tambahkan guard di controller/service reject.
- Jika status sudah `lunas`, reject harus ditolak dan redirect dengan error.

Contoh aturan:

```php
if ($pembayaran->status_pembayaran === 'lunas') {
    return redirect()->back()->with('status', false)->with('message', 'Pembayaran yang sudah lunas tidak dapat ditolak.');
}
```

Acceptance criteria:

- Transaksi `menunggu` masih bisa dikonfirmasi atau ditolak.
- Transaksi `lunas` tidak menampilkan tombol tolak.
- Request reject manual ke URL tetap ditolak oleh backend.

QA manual:

1. Buka detail transaksi status `menunggu`.
2. Pastikan tombol konfirmasi dan tolak tersedia.
3. Konfirmasi transaksi menjadi `lunas`.
4. Buka detail transaksi `lunas`.
5. Pastikan tombol tolak tidak tersedia.
6. Coba POST reject langsung jika memungkinkan, pastikan ditolak.

### 2. Bendahara Confirm/Reject Belum Memiliki Confirmation Modal

Status: selesai  
Prioritas: tinggi  
Target file:

- `app/Views/admin/bendahara/pembayaran/show.php`
- kemungkinan menggunakan helper JS global di `app/Views/layouts/admin.php`

Masalah:

- Tombol konfirmasi dan tolak langsung melakukan POST.
- Satu salah klik bisa mengubah status pembayaran.

Solusi UI:

- Tambahkan SweetAlert confirmation untuk `Konfirmasi Pembayaran`.
- Tambahkan SweetAlert confirmation yang lebih kuat untuk `Tolak Pembayaran`.
- Copy tolak harus menjelaskan dampaknya:
  - relasi item pembayaran dikosongkan
  - bukti pembayaran dihapus jika memang sistem melakukan itu
  - kontingen perlu upload ulang bukti pembayaran

Contoh copy konfirmasi:

```text
Konfirmasi pembayaran ini?
Status transaksi akan berubah menjadi lunas.
```

Contoh copy tolak:

```text
Tolak pembayaran ini?
Item pembayaran akan dikembalikan ke tagihan aktif dan bukti pembayaran akan dilepas.
```

Solusi teknis:

- Gunakan pola `confirmAdminAction()` yang sudah ada di admin layout jika tersedia.
- Jika belum cocok, tambahkan wrapper confirm khusus di view.
- Jangan ubah endpoint jika tidak perlu.

Acceptance criteria:

- Klik konfirmasi membuka modal confirm.
- Klik batal tidak mengirim form.
- Klik setuju mengirim form.
- Klik tolak membuka modal confirm dengan copy destruktif.

QA manual:

1. Klik `Konfirmasi Pembayaran`, pilih batal, status tidak berubah.
2. Klik `Konfirmasi Pembayaran`, pilih lanjut, status berubah `lunas`.
3. Klik `Tolak Pembayaran`, pilih batal, status tidak berubah.
4. Klik `Tolak Pembayaran`, pilih lanjut, item kembali ke tagihan aktif.

### 3. Kontingen Payment Submit Disabled Tanpa Helper Text

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/kontingen/pembayaran/index.php`

Masalah:

- Tombol upload sudah disabled saat total `Rp 0`.
- Namun belum ada pesan inline yang menjelaskan kenapa tombol disabled.

Solusi UI:

- Tambahkan helper text di dekat total atau tombol.
- Saat belum ada item dipilih, tampilkan:

```text
Pilih minimal satu item pembayaran untuk mengaktifkan tombol upload.
```

- Saat item dipilih, ganti menjadi:

```text
Pastikan bukti transfer sesuai dengan total tagihan terpilih.
```

Solusi teknis:

- Tambahkan elemen helper, misalnya `id="paymentSelectionHint"`.
- Update text di fungsi `updateTotal()`.

Acceptance criteria:

- Saat belum pilih item, tombol disabled dan helper menjelaskan alasannya.
- Saat pilih item, tombol aktif dan helper berubah.

QA manual:

1. Buka halaman tagihan.
2. Pastikan tombol upload disabled.
3. Pastikan helper muncul.
4. Pilih satu item.
5. Pastikan tombol aktif dan helper berubah.

### 4. Kontingen Payment Belum Menampilkan Preview/Nama File Bukti

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/kontingen/pembayaran/index.php`

Masalah:

- Setelah memilih file bukti pembayaran, user tidak mendapat konfirmasi visual selain file input browser.
- User bisa ragu apakah file benar sudah terpilih.

Solusi UI:

- Tampilkan nama file dan ukuran setelah file valid dipilih.
- Opsional: tampilkan thumbnail kecil untuk gambar.
- Jika file invalid, kosongkan preview dan tampilkan error.

Contoh tampilan:

```text
File dipilih: bukti-transfer.jpg (1.2 MB)
```

Solusi teknis:

- Tambahkan elemen `id="paymentProofPreview"`.
- Pada event `change`, jika file valid, isi nama dan ukuran.
- Format ukuran ke MB.

Acceptance criteria:

- File valid menampilkan nama dan ukuran.
- File invalid menghapus input dan preview.
- Pesan error ukuran memakai `10 MB`, bukan `10240 KB`.

QA manual:

1. Pilih file JPG valid.
2. Pastikan nama dan ukuran tampil.
3. Pilih file PDF.
4. Pastikan error muncul dan preview kosong.
5. Pilih file lebih dari 10 MB.
6. Pastikan error memakai copy `10 MB`.

### 5. Empty State Payment Belum Memiliki CTA Lanjutan

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/kontingen/pembayaran/index.php`

Masalah:

- Empty state sudah dibedakan berdasarkan kondisi.
- Namun belum ada tombol aksi lanjutan.

Solusi UI:

- Jika ada transaksi menunggu, tampilkan tombol:

```text
Lihat Menunggu Konfirmasi
```

- Jika semua lunas, tampilkan tombol:

```text
Lihat Pembayaran Lunas
```

- Jika belum ada tagihan, tampilkan tombol menuju peserta/tanding/seni sesuai kebutuhan:

```text
Tambah Peserta
```

Acceptance criteria:

- Empty state tidak hanya menjelaskan, tetapi memberi next action.
- CTA sesuai kondisi data.

QA manual:

1. Simulasikan kondisi semua item menunggu.
2. Pastikan CTA ke halaman menunggu tampil.
3. Simulasikan kondisi semua lunas.
4. Pastikan CTA ke halaman lunas tampil.
5. Simulasikan belum ada tagihan.
6. Pastikan CTA ke halaman peserta/tanding/seni tampil.

### 6. Dashboard Kontingen Menampilkan Semua Menu Sebagai Aktif

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/kontingen/dashboard/index.php`
- controller dashboard kontingen jika data access flags belum dikirim

Masalah:

- Dashboard menampilkan menu sebagai `Aktif`, meskipun akses create/edit/payment bisa ditutup oleh konfigurasi.
- User mendapat ekspektasi bahwa semua fitur bisa dipakai.

Solusi UI:

- Badge dashboard harus mengikuti access flag sebenarnya.
- Contoh badge:
  - `Aktif`
  - `Ditutup`
  - `Hanya Lihat`
  - `Menunggu Jadwal`

Solusi teknis:

- Controller perlu mengirim status akses:
  - pendaftaran peserta
  - kategori tanding/seni
  - pembayaran
- View membaca status tersebut untuk label dan styling.

Acceptance criteria:

- Jika pembayaran ditutup, dashboard tidak menampilkan `Aktif` untuk pembayaran.
- Jika create peserta ditutup, dashboard menjelaskan bahwa input peserta ditutup.

QA manual:

1. Matikan akses pembayaran di config.
2. Buka dashboard kontingen.
3. Pastikan kartu pembayaran menunjukkan `Ditutup`.
4. Hidupkan lagi akses, pastikan badge kembali sesuai.

### 7. Modal Peserta Kehilangan Konteks Saat Validasi Gagal

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Controllers/PesertaController.php`
- `app/Views/kontingen/peserta/index.php`

Masalah:

- Saat validasi store/update gagal, halaman reload dan modal tertutup.
- User harus membuka modal lagi.
- Pada update, input belum dipreserve karena redirect belum memakai `withInput()`.

Solusi backend:

- Tambahkan `withInput()` pada update validation fail.
- Tambahkan flashdata penanda modal harus dibuka kembali:

```php
->with('openModal', 'peserta')
->with('modalMode', 'edit')
```

Solusi frontend:

- Saat flashdata `openModal` ada, buka modal otomatis.
- Isi field dari `old()` atau data flash.
- Tampilkan error dekat field jika memungkinkan.

Acceptance criteria:

- Store gagal membuka kembali modal tambah.
- Update gagal membuka kembali modal edit.
- Input user tidak hilang.

QA manual:

1. Tambah peserta tanpa field wajib.
2. Pastikan modal terbuka lagi dan input lama ada.
3. Edit peserta dengan NIK 15 digit.
4. Pastikan modal edit terbuka lagi dan input tidak hilang.

### 8. Tanding Option AJAX Belum Membedakan Error dan Data Kosong

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/kontingen/tanding/index.php`

Masalah:

- Jika AJAX gagal, dropdown menampilkan pesan seperti data tidak ditemukan.
- User tidak tahu apakah memang tidak ada kategori atau terjadi error jaringan/session.

Solusi UI:

- Saat fetch dimulai, tampilkan:

```text
Memuat kategori tanding...
```

- Jika response gagal, tampilkan:

```text
Gagal memuat kategori. Coba pilih atlet ulang atau muat ulang halaman.
```

- Jika sukses tapi kosong, baru tampilkan:

```text
Kategori tanding tidak ditemukan.
```

Solusi teknis:

- Ubah `loadOptions()` agar punya `try/catch`.
- Tambahkan parameter/error mode ke `fillOptions()`.

Acceptance criteria:

- Loading state muncul.
- Server/network error berbeda dari data kosong.
- Submit tetap tidak bisa jika kategori tidak valid.

QA manual:

1. Pilih atlet normal, pastikan kategori muncul.
2. Simulasikan endpoint error.
3. Pastikan pesan error tampil, bukan data kosong.

### 9. Seni Option AJAX Belum Membedakan Error dan Data Kosong

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/kontingen/seni/index.php`

Masalah:

- Jika AJAX gagal, UI mengatakan tidak ada atlet yang memenuhi syarat.
- Ini membingungkan jika masalah sebenarnya jaringan/server/session.

Solusi UI:

- Saat fetch dimulai:

```text
Memuat atlet yang tersedia...
```

- Jika gagal:

```text
Gagal memuat atlet. Coba pilih kategori ulang atau muat ulang halaman.
```

- Jika sukses tapi kosong:

```text
Tidak ada atlet yang memenuhi syarat untuk kategori ini.
```

Solusi teknis:

- Tambahkan `try/catch` di `loadAtlet()`.
- Tambahkan state error yang juga disable submit.

Acceptance criteria:

- Error jaringan tidak dianggap data kosong.
- Submit tetap disabled saat gagal load.

QA manual:

1. Pilih kategori seni normal.
2. Simulasikan endpoint error.
3. Pastikan pesan error jelas dan submit disabled.

### 10. Admin Sekretariat Detail Membuat Banyak Modal Edit Atlet

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/admin/sekretariat/kontingen/show.php`

Masalah:

- Satu modal dibuat untuk setiap atlet.
- Jika atlet banyak, DOM besar dan halaman lambat.

Solusi UI/teknis:

- Ganti menjadi satu modal reusable.
- Tombol edit membawa data lewat `data-*` attribute atau fetch detail via AJAX.
- Modal diisi saat tombol edit diklik.

Acceptance criteria:

- Jumlah modal di DOM tidak bertambah mengikuti jumlah atlet.
- Edit atlet tetap bekerja.
- Halaman detail lebih ringan.

QA manual:

1. Buka detail kontingen dengan banyak atlet.
2. Pastikan halaman tetap responsif.
3. Edit beberapa atlet berbeda.
4. Pastikan data yang masuk ke modal benar.

### 11. Admin Sekretariat Tab Tidak Dipertahankan Setelah Action

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/admin/sekretariat/kontingen/show.php`
- `app/Controllers/Admin/Sekretariat/KontingenController.php`

Masalah:

- Setelah action dari tab tanding/seni, halaman kembali ke tab awal.
- Operator kehilangan konteks kerja.

Solusi:

- Gunakan query parameter, hash, atau flashdata `activeTab`.
- Redirect setelah action membawa konteks tab:

```php
return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=tanding'));
```

- View membaca `tab` dan membuka tab terkait.

Acceptance criteria:

- Setelah tambah/edit tanding, kembali ke tab tanding.
- Setelah tambah/edit seni, kembali ke tab seni.
- Default tetap tab data atlet.

QA manual:

1. Buka tab tanding.
2. Tambah/update data.
3. Pastikan kembali ke tab tanding.
4. Ulangi untuk tab seni.

### 12. Hapus Kontingen Terlalu Dekat dengan Reset Password

Status: selesai  
Prioritas: sedang  
Target file:

- `app/Views/admin/sekretariat/kontingen/show.php`

Masalah:

- `Reset Password` dan `Hapus Kontingen` dekat secara visual.
- Aksi rutin dan destruktif tidak dipisahkan.

Solusi UI:

- Buat section `Danger Zone`.
- Letakkan `Hapus Kontingen` di area terpisah dengan border merah/pesan peringatan.
- Tambahkan copy jelas:

```text
Menghapus kontingen dapat memengaruhi peserta, kategori, dan data terkait.
```

Solusi opsional:

- Tambahkan confirm lebih kuat, misalnya mengetik nama kontingen.

Acceptance criteria:

- Reset password tidak berdekatan dengan hapus kontingen.
- Hapus kontingen terlihat sebagai aksi berbahaya.

QA manual:

1. Buka detail kontingen.
2. Pastikan reset password berada di area aksi biasa.
3. Pastikan hapus kontingen berada di danger zone.

### 13. Kontingen Peserta File Validation Copy dan Feedback Per Slot

Status: selesai  
Prioritas: rendah  
Target file:

- `app/Views/kontingen/peserta/index.php`
- service upload arsip peserta jika pesan error berasal dari backend

Masalah:

- Client validation sudah ada, tetapi jika backend/service menolak file, feedback bisa terasa generik.
- User butuh tahu slot arsip mana yang bermasalah.

Solusi:

- Pastikan error backend menyebut nama slot arsip.
- Tambahkan area inline per file slot untuk pesan error.
- Pertahankan preview/existing file yang sudah ada.

Acceptance criteria:

- Jika file KTP salah tipe, pesan menyebut slot KTP.
- Jika file terlalu besar, pesan menyebut batas slot terkait.

QA manual:

1. Upload file salah tipe pada salah satu slot.
2. Pastikan pesan menyebut slot terkait.
3. Upload file terlalu besar.
4. Pastikan pesan ukuran jelas.

### 14. NIK/KK Validation Berbeda antara Kontingen dan Sekretariat

Status: selesai  
Prioritas: rendah  
Target file:

- `app/Controllers/PesertaController.php`
- `app/Controllers/Admin/Sekretariat/KontingenController.php`

Masalah:

- Kontingen mewajibkan NIK/KK numeric 16 digit.
- Sekretariat lebih longgar, maksimal 100 karakter.
- Data yang disimpan sekretariat bisa tidak valid di kontingen.

Solusi:

- Samakan aturan validasi kecuali ada alasan admin boleh override.
- Jika admin boleh override, tampilkan helper text bahwa admin dapat menyimpan data pengecualian.

Acceptance criteria:

- Aturan NIK/KK konsisten di kedua area, atau pengecualian admin terdokumentasi jelas.

QA manual:

1. Input NIK 15 digit di kontingen, pastikan gagal.
2. Input NIK 15 digit di sekretariat, pastikan perilaku sesuai keputusan.

### 15. Admin Payment Detail Back Selalu ke Semua Transaksi

Status: selesai  
Prioritas: rendah  
Target file:

- `app/Views/admin/bendahara/pembayaran/show.php`
- controller/list pembayaran jika perlu passing parameter `from`

Masalah:

- Admin dari list `menunggu`, `lunas`, atau filter kembali ke semua transaksi.
- Konteks filter hilang.

Solusi:

- Tambahkan parameter `from` saat menuju detail.
- Tombol kembali memakai `from` yang aman, fallback ke semua transaksi.
- Alternatif: gunakan `previous_url()` dengan validasi path internal.

Acceptance criteria:

- Dari menunggu ke detail, tombol kembali balik ke menunggu.
- Dari lunas ke detail, tombol kembali balik ke lunas.
- Jika direct URL, tombol kembali fallback ke semua transaksi.

QA manual:

1. Buka detail dari list menunggu.
2. Klik kembali, pastikan ke list menunggu.
3. Buka detail direct URL, klik kembali, pastikan fallback aman.

### 16. Tanding Edit Disable Athlete Select Tapi Copy Kurang Jelas

Status: selesai  
Prioritas: rendah  
Target file:

- `app/Views/kontingen/tanding/index.php`

Masalah:

- Saat edit, select atlet disabled dan hidden input dipakai.
- User mungkin mengira atlet bisa diganti.

Solusi:

- Saat mode edit, ubah label/helper:

```text
Atlet tidak dapat diubah pada mode edit. Hapus kategori ini jika ingin memilih atlet lain.
```

- Jika memang atlet boleh diganti, jangan disable select dan validasi ulang backend.

Acceptance criteria:

- Mode edit menjelaskan atlet fixed.
- User tidak bingung kenapa select disabled.

QA manual:

1. Edit kategori tanding.
2. Pastikan helper mode edit tampil.
3. Pastikan mode tambah tidak menampilkan helper tersebut.

### 17. Empty State Peserta/Tanding/Seni Kurang Memberi Arah

Status: selesai  
Prioritas: rendah  
Target file:

- `app/Views/kontingen/peserta/index.php`
- `app/Views/kontingen/tanding/index.php`
- `app/Views/kontingen/seni/index.php`

Masalah:

- Empty state hanya menyatakan belum ada data.
- Belum menjelaskan langkah berikutnya.

Solusi UI:

- Peserta kosong:

```text
Tambahkan atlet terlebih dahulu untuk mendaftarkan kategori tanding atau seni.
```

- Tanding kosong:

```text
Pilih atlet yang sudah terdaftar, lalu masukkan ke kategori tanding.
```

- Seni kosong:

```text
Pilih kategori seni, lalu pilih atlet sesuai jumlah peserta yang dibutuhkan.
```

- Jika `allowCreate` aktif, tampilkan CTA tombol tambah.
- Jika tidak aktif, tampilkan alasan fitur tertutup.

Acceptance criteria:

- User baru tahu langkah berikutnya dari empty state.
- CTA hanya muncul bila user memang boleh membuat data.

QA manual:

1. Buka peserta kosong.
2. Pastikan copy menjelaskan langkah berikutnya.
3. Buka tanding/seni kosong dengan `allowCreate` aktif.
4. Pastikan CTA tersedia.
5. Buka saat `allowCreate` false.
6. Pastikan CTA tidak muncul dan copy menjelaskan akses tertutup.

## Prioritas Implementasi Disarankan

| Urutan | Temuan | Area | Alasan |
|---|---|---|---|
| 1 | 1 dan 2 | Bendahara | Mencegah salah aksi pada pembayaran sensitif |
| 2 | 3 dan 4 | Kontingen pembayaran | Memperjelas checkout dan bukti transfer |
| 3 | 8 dan 9 | Kontingen tanding/seni | Membedakan data kosong vs error sistem |
| 4 | 7 | Kontingen peserta | Mengurangi kehilangan input user |
| 5 | 11 dan 12 | Sekretariat | Menjaga kontinuitas kerja dan keamanan aksi destruktif |
| 6 | 6 | Kontingen dashboard | Mencegah ekspektasi fitur aktif yang salah |
| 7 | 10 | Sekretariat | Optimasi performa halaman detail besar |
| 8 | 13 sampai 17 | Lintas area | Polish dan konsistensi UX |

## Checklist Implementasi

- [ ] Bendahara hide reject untuk transaksi lunas
- [x] Bendahara confirm modal untuk konfirmasi pembayaran
- [x] Bendahara confirm modal untuk tolak pembayaran
- [x] Kontingen payment helper saat belum memilih item
- [x] Kontingen payment file name/size preview
- [x] Kontingen payment CTA pada empty state
- [x] Dashboard kontingen badge sesuai access flag
- [x] Modal peserta reopen dan preserve input saat validasi gagal
- [x] Tanding AJAX loading/error state
- [x] Seni AJAX loading/error state
- [x] Sekretariat detail single reusable modal
- [x] Sekretariat preserve active tab setelah action
- [x] Sekretariat danger zone untuk hapus kontingen
- [x] Arsip peserta feedback per slot
- [x] Selaraskan validasi NIK/KK kontingen dan sekretariat
- [x] Tombol kembali detail bendahara preserve context
- [x] Helper mode edit tanding untuk atlet fixed
- [x] Empty state peserta/tanding/seni dengan next action

## Catatan QA Umum

Sebelum menandai item selesai:

1. Jalankan `php -l` pada file PHP yang diubah.
2. Uji minimal satu flow sukses dan satu flow gagal.
3. Pastikan flash message/toastr tidak bertabrakan dengan modal.
4. Pastikan mobile modal/sidebar tetap bisa discroll.
5. Pastikan perubahan pembayaran tidak membuka celah bypass melalui direct POST.

## Kesimpulan

Fokus perbaikan terbaik adalah mengamankan flow pembayaran bendahara terlebih dahulu, lalu memperjelas feedback kontingen pada checkout dan AJAX form kategori. Area sekretariat dapat dikerjakan bertahap karena dampaknya lebih banyak ke efisiensi operator dan performa halaman detail.

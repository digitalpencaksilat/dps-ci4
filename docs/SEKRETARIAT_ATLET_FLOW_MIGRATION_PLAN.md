# Plan Migrasi Flow Atlet Sekretariat

Dokumen ini merangkum plan migrasi flow tambah `Peserta Tanding` dan `Peserta Seni` pada modul Sekretariat di project CI4 agar selaras dengan DPS CI3 lama. Fokus migrasi ada pada UX tambah data, validasi kategori/atlet, dan auto pool. Rule delete tetap memakai pendekatan CI4 yang lebih aman.

## Target

- Flow tambah `Peserta Tanding` mengikuti DPS CI3 lama: pilih atlet dulu, kategori tanding muncul dinamis sesuai atlet.
- Flow tambah `Peserta Seni` mengikuti DPS CI3 lama: pilih kontingen dan kategori dulu, atlet valid muncul dinamis sebagai checkbox.
- Flow ini berlaku untuk role `sekretariat` dan `kontingen`.
- Sekretariat bisa input lintas kontingen; kontingen hanya bisa input untuk kontingen sendiri.
- Backend CI4 memvalidasi ulang semua rule utama, tidak bergantung pada frontend.
- Auto tambah pool tanding dan seni dimigrasikan dari DPS CI3 lama.
- Delete tetap aman: data tidak boleh dihapus jika sudah terkait pembayaran.

## Prinsip

- Form tambah mengikuti UX DPS lama.
- Semua rule validasi utama wajib ada di service/backend.
- Core rule validasi dibuat reusable agar dipakai oleh controller sekretariat dan controller kontingen.
- Logic CI3 dimigrasikan sebagai konsep, bukan copy mentah jika ada bug.
- Auto pool dibuat hati-hati sesuai schema CI4 aktual.
- Perubahan dikerjakan bertahap agar mudah dites.

## Keputusan Final

- Delete rule memakai pilihan kedua: tetap CI4 lebih aman.
- `Peserta Tanding` tidak boleh dihapus jika `id_pembayaran !== null`.
- `Kelompok Peserta Seni` tidak boleh dihapus jika `id_pembayaran !== null`.
- Anggota `Peserta Seni` tidak boleh dihapus jika parent `kelompok_peserta_seni.id_pembayaran !== null`.
- UX tambah mengikuti DPS CI3.
- Backend validation mengikuti rule DPS CI3, tapi dibenahi bila logic CI3 lama bermasalah.
- Auto pool wajib dimigrasikan agar flow pendaftaran tetap efisien.

## Scope Role

Flow migrasi berlaku untuk dua role:

- `sekretariat`
- `kontingen`

Perbedaan role hanya ada pada scope akses, sumber `id_kontingen`, dan redirect. Core rule tetap sama.

### Sekretariat

- Bisa input peserta tanding/seni lintas kontingen.
- Bisa tambah dari halaman global peserta tanding/seni.
- Bisa tambah dari detail kontingen.
- Bisa memilih kontingen saat input seni dari halaman global.
- Bisa memilih atlet lintas kontingen saat input tanding global.
- Delete tetap ditolak jika data sudah terkait pembayaran.

### Kontingen

- Hanya bisa input peserta untuk kontingen sendiri.
- `id_kontingen` wajib berasal dari session, bukan input bebas dari user.
- Opsi atlet hanya dari kontingen sendiri.
- Endpoint JSON tidak boleh membocorkan atlet/kategori kontingen lain.
- Create/update/delete hanya boleh untuk data milik kontingen sendiri.
- Akses tombol/form mengikuti config pendaftaran.
- Delete tetap ditolak jika data sudah terkait pembayaran.

### Config Yang Harus Dihormati Role Kontingen

- `perbolehkan_kontingen_memilih_kategori`
- `perbolehkan_ganti_atlet_dan_kategori`
- `perbolehkan_undur_diri_atlet`
- `perbolehkan_memilih_kategori_usia`
- `perbolehkan_memilih_kelas_tanding`
- `perbolehkan_atlet_dari_kontingen_yang_sama`

## Flow CI4 Saat Ini

### Peserta Tanding

File terkait:

- `app/Controllers/Admin/Sekretariat/PesertaTandingController.php`
- `app/Views/admin/sekretariat/peserta_tanding/_form.php`
- `app/Models/PesertaTandingModel.php`
- `app/Services/SekretariatPesertaKontingenService.php`

Alur saat ini:

1. `PesertaTandingController::index()` load data list, opsi pendaftar, dan semua opsi kompetisi tanding.
2. User pilih `id_pendaftar`.
3. User pilih `id_kompetisi_tanding` dari semua kategori.
4. Submit ke `PesertaTandingController::store()`.
5. Controller validasi `id_pendaftar` dan `id_kompetisi_tanding` wajib integer.
6. Service `createPesertaTanding()` cek pendaftar ada dan belum masuk tanding.
7. Service insert row `peserta_tanding` dengan default pembayaran/sertifikat.

Kekurangan saat ini:

- Kategori tidak difilter berdasarkan atlet.
- Belum validasi gender kategori vs atlet.
- Belum validasi umur kategori vs atlet.
- Belum validasi berat badan kelas vs atlet.
- Belum validasi kuota pool/kategori.
- Belum validasi larangan atlet dari kontingen sama.
- Belum auto tambah pool.

### Peserta Seni

File terkait:

- `app/Controllers/Admin/Sekretariat/KelompokPesertaSeniController.php`
- `app/Views/admin/sekretariat/kelompok_seni/_form.php`
- `app/Models/KelompokPesertaSeniModel.php`
- `app/Models/PesertaSeniModel.php`
- `app/Services/SekretariatPesertaKontingenService.php`

Alur saat ini:

1. `KelompokPesertaSeniController::index()` load data list, kontingen, semua kategori seni, dan opsi pendaftar seni.
2. User pilih kontingen.
3. User pilih kategori seni.
4. User pilih atlet dari select multiple statis.
5. Submit ke `KelompokPesertaSeniController::store()`.
6. Controller validasi kategori, status, nomor undi, kontingen, dan pendaftar.
7. Service `createKelompokSeni()` validasi jumlah atlet.
8. Service insert `kelompok_peserta_seni` dan child rows `peserta_seni` dalam transaksi.

Kekurangan saat ini:

- Atlet tidak difilter berdasarkan kategori seni.
- Kategori tidak memakai list pendaftaran yang memprioritaskan pool valid.
- Belum validasi semua atlet milik kontingen terpilih.
- Belum validasi gender/umur atlet vs kategori.
- Belum validasi peserta seni belum dipakai dengan rule seketat CI3.
- Belum auto tambah pool seni.

## Referensi DPS CI3 Lama

### Peserta Tanding CI3

File referensi:

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/resources/Peserta_tanding.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Peserta_tanding_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Kompetisi_tanding_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Kelas_tanding_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/shared_components/peserta_tanding/modal_insert.php`

Flow CI3:

1. User pilih atlet.
2. JS request kategori via `kompetisi-tanding/get-kompetisi-tanding-by-pendaftar/{id_pendaftar}`.
3. Backend menjalankan `Kompetisi_tanding_model::get_kompetisi_tanding_by_pendaftar()`.
4. Kategori difilter berdasarkan gender, usia, berat badan, kuota, pool, dan kontingen sama.
5. Opsi disabled tetap tampil dengan pesan seperti `Kuota Penuh`.
6. Submit create insert `peserta_tanding`.
7. Model `Peserta_tanding_model::create()` menjalankan transaksi.
8. Jika kompetisi `pemasalan`, model memanggil `Kelas_tanding_model::otomatis_menambahkan_pool()`.

### Peserta Seni CI3

File referensi:

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/resources/Kelompok_peserta_seni.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Kelompok_peserta_seni_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Kompetisi_seni_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Sub_kategori_seni_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Pendaftar_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/shared_components/kelompok_peserta_seni/modal_insert.php`

Flow CI3:

1. User pilih kategori seni.
2. JS request atlet valid via `pendaftar/get-pendaftar-by-kompetisi-seni/{id_kompetisi_seni}/{id_kontingen}`.
3. Backend menjalankan `Pendaftar_model::get_pendaftar_by_kompetisi_seni()`.
4. Atlet difilter berdasarkan kontingen, gender, dan umur.
5. Frontend render checkbox atlet.
6. Frontend validasi jumlah atlet sesuai jenis seni.
7. Submit create insert `kelompok_peserta_seni` dan child `peserta_seni`.
8. Model `Kelompok_peserta_seni_model::create_with_child_table()` menjalankan transaksi.
9. Setelah insert, model memanggil `Sub_kategori_seni_model::otomatis_menambahkan_pool()`.

## Catatan Bug CI3

Pada CI3 `Kelompok_peserta_seni::create()` ada bug logika server-side:

```php
$kompetisi_seni->jenis_seni == 'tunggal' &&
$kompetisi_seni->jenis_seni == 'ganda' &&
$kompetisi_seni->jenis_seni == 'beregu'
```

Satu nilai tidak mungkin sekaligus `tunggal`, `ganda`, dan `beregu`. CI4 tidak boleh menyalin bug ini. Rule yang benar mengikuti JS CI3 dan service CI4 saat ini:

- Strict types wajib jumlah tepat sama dengan `jumlah_peserta`.
- Non-strict types wajib minimal `jumlah_peserta`.

Strict types:

- `tunggal`
- `ganda`
- `beregu`
- `solo kreatif`
- `perorangan`
- `berpasangan`
- `berkelompok`

## Gap CI4 vs CI3

### Gap Tanding

- CI4 belum punya endpoint kategori berdasarkan atlet.
- CI4 belum punya method setara `get_kompetisi_tanding_by_pendaftar()`.
- CI4 form menampilkan semua kategori, bukan kategori yang valid untuk atlet.
- CI4 create belum validasi gender/usia/berat/kuota/kontingen sama.
- CI4 belum auto tambah pool setelah insert.
- CI4 update belum cek perubahan kategori jika biaya pembayaran berbeda.

### Gap Seni

- CI4 belum punya endpoint atlet berdasarkan kategori seni dan kontingen.
- CI4 belum punya method setara `get_pendaftar_by_kompetisi_seni()`.
- CI4 belum punya method setara `get_kompetisi_seni_pendaftaran()`.
- CI4 form memakai select multiple statis, bukan checkbox dinamis.
- CI4 create belum validasi semua atlet terhadap kontingen/gender/umur/ketersediaan.
- CI4 belum auto tambah pool setelah insert.
- CI4 update belum cek perubahan kategori jika biaya pembayaran berbeda.

## Phase 1: Tanding - Data Dan Query

1. Tambah service method `getKompetisiTandingByPendaftar(int $idPendaftar): array`.
2. Logic migrasi dari CI3 `Kompetisi_tanding_model::get_kompetisi_tanding_by_pendaftar()`.
3. Filter wajib:
   - gender kategori sama dengan gender atlet.
   - kelas `label != 'sisipan'`.
4. Filter config:
   - jika `perbolehkan_memilih_kategori_usia = false`, umur atlet harus masuk range kategori.
   - jika `perbolehkan_memilih_kelas_tanding = false`, berat atlet harus masuk range kelas.
5. Pool selection:
   - tiap `id_kelas_tanding` hanya tampil 1 kompetisi.
   - prioritas pool yang masih punya kuota.
   - pool penuh tetap tampil disabled.
6. Kuota:
   - cek jumlah peserta per pool.
   - cek jumlah peserta per kategori lomba terhadap `kuota_peserta`.
7. Kontingen sama:
   - jika config melarang atlet dari kontingen sama, disable kategori prestasi yang sudah punya atlet dari kontingen itu.
8. Tambah method `assertPesertaTandingEligible(int $idPendaftar, int $idKompetisiTanding): void`.

## Phase 2: Tanding - Endpoint Dan Form

1. Tambah route JSON:
   - `admin/sekretariat/kompetisi-tanding/by-pendaftar/(:num)`
2. Tambah action controller, bisa di `PesertaTandingController`.
3. Ubah `_form.php` tanding:
   - saat create, kategori kosong dulu.
   - setelah peserta dipilih, fetch kategori via AJAX.
   - render option enabled/disabled.
4. Saat edit, kategori idealnya difilter berdasarkan atlet record yang sedang diedit.

## Phase 3: Tanding - Create, Update, Auto Pool

1. Perkuat `createPesertaTanding()`:
   - cek pendaftar ada.
   - cek kompetisi ada.
   - cek pendaftar belum masuk tanding.
   - panggil `assertPesertaTandingEligible()`.
   - insert dalam transaksi.
   - panggil auto pool jika perlu.
2. Buat method `ensureTandingPoolAvailable(int $idKelasTanding): bool`.
3. Logic auto pool migrasi dari CI3 `Kelas_tanding_model::otomatis_menambahkan_pool()`.
4. Perkuat `updatePesertaTanding()`:
   - cek eligibility kategori baru.
   - jika sudah ada pembayaran dan kategori baru beda biaya, tolak.
   - panggil auto pool jika perlu.
5. Pertahankan `deletePesertaTanding()`:
   - tolak jika `id_pembayaran !== null`.

## Phase 4: Seni - Data Dan Query

1. Tambah service method `listKompetisiSeniPendaftaran(bool $isAdmin = true, ?array $where = null): array`.
2. Logic migrasi dari CI3 `Kompetisi_seni_model::get_kompetisi_seni_pendaftaran()`.
3. Tiap `id_sub_kategori_seni` hanya tampil 1 pool.
4. Jika pool penuh, option tetap tampil disabled dengan message.
5. Tambah service method `getPendaftarByKompetisiSeni(int $idKompetisiSeni, int $idKontingen): array`.
6. Logic migrasi dari CI3 `Pendaftar_model::get_pendaftar_by_kompetisi_seni()`.
7. Filter atlet:
   - milik kontingen.
   - gender cocok kategori.
   - belum masuk `peserta_seni`.
   - umur cocok jika config menutup pemilihan usia.
8. Tambah method `assertKelompokSeniEligible(int $idKontingen, int $idKompetisiSeni, array $idPendaftar): void`.

## Phase 5: Seni - Endpoint Dan Form

1. Tambah route JSON:
   - `admin/sekretariat/pendaftar/by-kompetisi-seni/(:num)/(:num)`
2. Tambah action controller, bisa di `KelompokPesertaSeniController`.
3. Ubah `_form.php` seni:
   - pilih kontingen.
   - pilih kategori.
   - atlet valid dimuat via AJAX sebagai checkbox.
4. Frontend validasi jumlah atlet:
   - strict types harus tepat sama dengan `jumlah_peserta`.
   - non-strict harus minimal `jumlah_peserta`.
5. Submit button disabled sampai jumlah valid.

## Phase 6: Seni - Create, Update, Auto Pool

1. Perkuat `createKelompokSeni()`:
   - normalize atlet.
   - panggil `assertKelompokSeniEligible()`.
   - transaksi insert kelompok dan child `peserta_seni`.
   - panggil auto pool seni.
2. Buat method `ensureSeniPoolAvailable(int $idSubKategoriSeni): bool`.
3. Logic auto pool migrasi dari CI3 `Sub_kategori_seni_model::otomatis_menambahkan_pool()`.
4. Perkuat `updateKelompokSeni()`:
   - jika pembayaran ada dan kategori baru beda biaya, tolak.
   - validasi kategori baru kompatibel dengan anggota existing.
   - panggil auto pool jika perlu.
5. Perkuat `addPesertaSeni()`:
   - tolak jika parent sudah terkait pembayaran.
   - validasi atlet cocok kategori.
   - validasi jumlah anggota tidak melanggar rule strict.
6. Perkuat `deletePesertaSeni()`:
   - tolak jika parent sudah terkait pembayaran.
7. Pertahankan `deleteKelompokSeni()`:
   - tolak jika `id_pembayaran !== null`.

## Phase 7: Detail Kontingen Integration

1. Detail kontingen tambah tanding tetap memakai form yang sama.
2. Opsi pendaftar dibatasi kontingen.
3. Kategori tetap AJAX berdasarkan pendaftar.
4. Submit tetap ke `admin/sekretariat/kontingen/{id}/peserta-tanding`.
5. Detail kontingen tambah seni:
   - kontingen sudah diketahui.
   - kategori dipilih dulu.
   - checkbox atlet AJAX pakai `id_kontingen` dari halaman detail.
   - submit tetap ke `admin/sekretariat/kontingen/{id}/kelompok-seni`.

## Phase 8: Config Compatibility

Pastikan CI4 bisa baca config lama:

- `pendaftaran/akses_pemilihan_kategori_perlombaan`
- `perbolehkan_memilih_kategori_usia`
- `perbolehkan_memilih_kelas_tanding`
- `perbolehkan_atlet_dari_kontingen_yang_sama`
- `pendaftaran/akses_pendaftaran`
- `perbolehkan_kontingen_memilih_kategori`
- `perbolehkan_ganti_atlet_dan_kategori`
- `perbolehkan_undur_diri_atlet`

Rekomendasi default jika config belum tersedia:

- `perbolehkan_memilih_kategori_usia = false`
- `perbolehkan_memilih_kelas_tanding = false`
- `perbolehkan_atlet_dari_kontingen_yang_sama = false`

## Phase 9: Kontingen Role Integration

1. Audit controller dan view kontingen saat ini:
   - `app/Controllers/PesertaController.php`
   - `app/Controllers/KategoriTandingController.php`
   - `app/Controllers/KategoriSeniController.php`
   - `app/Views/kontingen/tanding/index.php`
   - `app/Views/kontingen/seni/index.php`
   - `app/Views/kontingen/peserta/index.php`
2. Samakan flow tambah tanding kontingen dengan sekretariat:
   - atlet hanya dari `session()->get('id_kontingen')`.
   - atlet yang sudah ikut tanding tidak muncul.
   - kategori tanding dimuat via AJAX setelah atlet dipilih.
   - validasi kategori memakai service yang sama dengan sekretariat.
   - submit create memakai service `createPesertaTanding()` yang sama.
3. Samakan flow tambah seni kontingen dengan sekretariat:
   - `id_kontingen` diambil dari session.
   - user pilih kategori seni.
   - atlet valid dimuat via AJAX berdasarkan kategori + kontingen session.
   - atlet muncul sebagai checkbox.
   - validasi jumlah atlet sama seperti flow sekretariat dan DPS CI3.
   - submit create memakai service `createKelompokSeni()` yang sama.
4. Endpoint JSON harus role-aware:
   - sekretariat boleh memakai parameter kontingen untuk kebutuhan halaman global/detail.
   - kontingen wajib memakai `id_kontingen` dari session.
   - kontingen tidak boleh request/lihat atlet kontingen lain.
5. Controller kontingen cukup menjadi wrapper akses dan redirect:
   - validasi session kontingen.
   - ambil `id_kontingen` dari session.
   - panggil service reusable.
   - redirect ke halaman kontingen.
6. Terapkan config akses kontingen:
   - jika `perbolehkan_kontingen_memilih_kategori = false`, tombol/form tambah kategori harus disabled.
   - jika `perbolehkan_ganti_atlet_dan_kategori = false`, update kategori/atlet oleh kontingen harus ditolak.
   - jika `perbolehkan_undur_diri_atlet = false`, delete oleh kontingen harus ditolak walaupun belum ada pembayaran.
7. Pertahankan rule delete pembayaran:
   - kontingen tidak boleh delete tanding jika `id_pembayaran !== null`.
   - kontingen tidak boleh delete kelompok seni jika `id_pembayaran !== null`.
   - kontingen tidak boleh delete anggota seni jika parent kelompok sudah punya `id_pembayaran`.
8. Test security role kontingen:
   - tidak bisa input `id_pendaftar` milik kontingen lain lewat request manual.
   - tidak bisa fetch atlet kontingen lain via endpoint JSON.
   - tidak bisa update/delete data kontingen lain.
   - auto pool tetap jalan dari flow kontingen.

## Phase 10: Testing Manual

### Test Tanding Create

- Atlet putra hanya melihat kategori putra.
- Atlet putri hanya melihat kategori putri.
- Berat atlet hanya melihat kelas yang sesuai jika config menutup pemilihan kelas.
- Umur atlet hanya melihat kategori usia yang sesuai jika config menutup pemilihan usia.
- Atlet yang sudah tanding tidak muncul lagi.
- Pool penuh tampil disabled.
- Setelah pool pemasalan penuh, pool baru otomatis terbuat.

### Test Tanding Update/Delete

- Ganti ke kategori valid sukses.
- Ganti kategori beda gender ditolak.
- Ganti kategori beda biaya saat sudah ada pembayaran ditolak.
- Delete saat belum ada pembayaran sukses.
- Delete saat ada pembayaran ditolak.

### Test Seni Create

- Kategori seni penuh tampil disabled.
- Setelah pilih kategori, hanya atlet valid yang tampil.
- Tunggal wajib 1 atlet.
- Ganda wajib 2 atlet.
- Beregu wajib sesuai `jumlah_peserta`.
- Non-strict type minimal sesuai `jumlah_peserta`.
- Atlet yang sudah seni tidak muncul lagi.
- Setelah pool penuh, pool baru otomatis terbuat.

### Test Seni Update/Member/Delete

- Tambah anggota valid sukses.
- Tambah anggota saat kelompok sudah punya pembayaran ditolak.
- Hapus anggota saat kelompok sudah punya pembayaran ditolak.
- Delete kelompok saat sudah punya pembayaran ditolak.
- Update kategori beda biaya saat sudah punya pembayaran ditolak.

## Urutan Eksekusi Disarankan

1. Implement query/helper service tanding.
2. Implement endpoint JSON tanding.
3. Update form tanding AJAX.
4. Perkuat create/update tanding.
5. Implement auto pool tanding.
6. Test tanding lengkap.
7. Implement query/helper service seni.
8. Implement endpoint JSON seni.
9. Update form seni AJAX/checkbox.
10. Perkuat create/update/add/delete member seni.
11. Implement auto pool seni.
12. Test seni lengkap.
13. Integrasikan flow yang sama ke role kontingen.
14. Test kontingen role lengkap, termasuk security request manual.
15. Uji detail kontingen dan halaman global sekretariat.
16. Rapikan message/error dan edge case.

## Risiko

- Struktur kolom CI4 mungkin tidak lengkap untuk auto pool seperti `bagan_pertandingan`, `perhitungan_medali`, atau `keterangan`.
- Auto pool membuat data kompetisi baru otomatis, jadi harus selaras dengan setup super admin.
- Logic CI3 punya bug di validasi jumlah seni; CI4 harus memakai logic yang sudah dibenahi.
- Rule pembayaran harus konsisten dengan modul Bendahara supaya transaksi/nota tidak mismatch.

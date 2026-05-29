# Penjadwalan Seni Otomatis Pool & Battle - Audit Flow, Parity, dan Migrasi CI4

## Ringkasan

Dokumen ini mencatat hasil pengecekan feature penjadwalan seni otomatis pada project source `../dps` (CI3) dan hasil migrasinya ke project CI4 saat ini.

Fokus audit:
- flow penjadwalan otomatis seni sistem **pool**
- flow penjadwalan otomatis seni sistem **battle**
- keterkaitan **controller**, **view**, **model/service**, dan tabel database
- parity perilaku inti dari CI3 ke CI4

Status implementasi migrasi saat dokumen ini dibuat:
- flow UI penjadwalan otomatis seni untuk pool dan battle sudah ditambahkan di CI4
- route khusus admin super untuk penjadwalan otomatis seni sudah ditambahkan
- service CI4 untuk generate jadwal seni otomatis pool dan battle sudah ditambahkan
- service penugasan juri seni dasar sudah ditambahkan
- dokumentasi parity dan gap sudah dicatat di bawah

---

## Source Project yang Dicek

Source audit diambil dari project CI3 pada path:
- `../dps`

File source utama yang relevan:
- `../dps/application/controllers/resources/Jadwal_seni.php`
- `../dps/application/models/services/penjadwalan_otomatis/Penjadwalan_otomatis_seni_model.php`
- `../dps/application/models/resources/Penilaian_seni_model.php`
- `../dps/application/config/routes/resources.php`

Referensi bahasa/label fitur:
- `../dps/application/language/indonesia/system_lang.php`

---

## Hasil Pengecekan Flow CI3

## 1. Flow Pool Otomatis di CI3

Entry point controller:
- `../dps/application/controllers/resources/Jadwal_seni.php:442`

Method:
- `buat_jadwal_seni_sistem_pool_otomatis()`

Payload utama dari form:
- `tanggal`
- `jam_mulai`
- `jam_selesai`
- `keterangan`
- `id_gelanggang[]`
- `jumlah_pool[]`
- `urutan_id_sub_kategori_seni[]`
- `langsung_buat_pdf`
- `pdf_library`

Flow CI3:
1. Validasi form rule `create_jadwal_seni_otomatis`.
2. Build array `$pengaturan`.
3. Call model/resource `Jadwal_seni_model->jadwal_seni_otomatis_sistem_pool($pengaturan)`.
4. Di layer service/model, sistem:
   - validasi parameter
   - ambil `kompetisi_seni` berdasarkan urutan sub kategori
   - validasi kapasitas pool total terhadap jumlah kompetisi
   - cek apakah penampilan seni sudah pernah masuk `detail_jadwal_seni`
   - distribusi kompetisi ke gelanggang berdasar kapasitas `jumlah_pool`
   - insert `jadwal_seni` per gelanggang
   - ambil `kelompok_peserta_seni` per `id_kompetisi_seni`
   - buat `penampilan_seni`
   - insert `detail_jadwal_seni`
   - tugaskan juri via `Penilaian_seni_model->tugaskan_wasit_juri(...)`
   - update `status_penampilan = belum_tampil`
5. Bila opsi PDF aktif, generate PDF per jadwal.
6. Flash message sukses/gagal.

Source flow service utama:
- `../dps/application/models/services/penjadwalan_otomatis/Penjadwalan_otomatis_seni_model.php:14`
- `../dps/application/models/services/penjadwalan_otomatis/Penjadwalan_otomatis_seni_model.php:170`
- `../dps/application/models/services/penjadwalan_otomatis/Penjadwalan_otomatis_seni_model.php:207`

## 2. Flow Battle Otomatis di CI3

Entry point controller:
- `../dps/application/controllers/resources/Jadwal_seni.php:482`

Method:
- `buat_jadwal_seni_battle_otomatis()`

Payload utama dari form:
- `tanggal`
- `jam_mulai`
- `jam_selesai`
- `keterangan`
- `id_gelanggang[]`
- `babak_battle_seni[]`
- `jumlah_partai[]`
- `urutan_id_sub_kategori_seni[]`
- `jenis_penjadwalan`
- `langsung_buat_pdf`
- `pdf_library`

Jenis penjadwalan battle di CI3:
- `prestasi`
- `pemasalan_seling_1`
- `pemasalan_seling_2`
- `pemasalan_seling_3`

Flow CI3:
1. Validasi form rule `create_jadwal_battle_seni_otomatis`.
2. Build array `$pengaturan`.
3. Switch `jenis_penjadwalan`.
4. Call method model:
   - `jadwal_battle_seni_otomatis_prestasi($pengaturan)` atau
   - `jadwal_battle_seni_otomatis_pemasalan($pengaturan)`
5. Di layer service/model, sistem:
   - ambil `battle_seni` berdasarkan sub kategori dan babak
   - exclude `jenis_kemenangan = BYE`
   - validasi battle belum ada di `detail_jadwal_seni`
   - distribusi battle ke gelanggang berdasarkan `jumlah_partai`
   - insert `jadwal_seni`
   - insert `detail_jadwal_seni` dengan `id_battle_seni`
   - tugaskan juri untuk `id_penampilan_seni_biru` dan `id_penampilan_seni_merah`
6. Bila opsi PDF aktif, generate PDF per jadwal.
7. Flash message sukses/gagal.

Source flow service utama:
- `../dps/application/models/services/penjadwalan_otomatis/Penjadwalan_otomatis_seni_model.php:273`
- `../dps/application/models/services/penjadwalan_otomatis/Penjadwalan_otomatis_seni_model.php:330`
- `../dps/application/models/services/penjadwalan_otomatis/Penjadwalan_otomatis_seni_model.php:402`

## 3. Penugasan Juri Seni di CI3

Source:
- `../dps/application/models/resources/Penilaian_seni_model.php:90`

Flow:
1. Ambil `penampilan_seni`.
2. Cek jumlah data `penilaian_seni` terhadap `jumlah_juri`.
3. Jika kurang, reset lalu generate ulang `penilaian_seni`.
4. Ambil `perangkat_pertandingan` posisi `juri` sesuai `id_gelanggang`.
5. Update `id_perangkat_pertandingan` pada setiap row `penilaian_seni`.
6. Reset `status_ready = 0`.

---

## Komponen CI4 Sebelum Migrasi

Sebelum migrasi ini, project CI4 sudah memiliki komponen terkait jadwal seni, tetapi belum memiliki flow penjadwalan otomatis seni yang parity dengan CI3.

Komponen yang sudah ada:
- `app/Controllers/Admin/Super/PembuatanJadwalController.php`
- `app/Controllers/Admin/Sekretariat/JadwalSeniController.php`
- `app/Models/JadwalSeniModel.php`
- `app/Models/BattleSeniModel.php`
- `app/Models/PenampilanSeniModel.php`
- `app/Models/KompetisiSeniModel.php`
- `app/Views/admin/super/drawing_seni.php`
- `app/Views/admin/sekretariat/jadwal_seni/index.php`
- `app/Views/admin/sekretariat/jadwal_seni/show.php`

Temuan sebelum migrasi:
- belum ada controller khusus `PenjadwalanSeniOtomatisController`
- belum ada route admin super untuk form/process penjadwalan otomatis seni
- belum ada service CI4 yang setara `Penjadwalan_otomatis_seni_model`
- belum ada service CI4 khusus penugasan juri seni setara `Penilaian_seni_model->tugaskan_wasit_juri()`
- view `drawing_seni` hanya menangani distribusi peserta, nomor undi, dan acak bagan battle; belum menangani generate jadwal otomatis pool/battle

---

## Komponen CI4 yang Ditambahkan/Migrasikan

## 1. Route

Ditambahkan route admin super:
- `app/Config/Routes.php:170`
- `app/Config/Routes.php:171`
- `app/Config/Routes.php:172`

Route baru:
- `GET admin/super/jadwal-seni/penjadwalan-otomatis`
- `POST admin/super/jadwal-seni/buat-jadwal-seni-pool-otomatis`
- `POST admin/super/jadwal-seni/buat-jadwal-seni-battle-otomatis`

## 2. Controller

Ditambahkan controller baru:
- `app/Controllers/Admin/Super/PenjadwalanSeniOtomatisController.php`

Method:
- `index()`
- `storePool()`
- `storeBattle()`
- helper PDF internal

Tugas controller:
- load data gelanggang
- load data sub kategori seni
- tampilkan form pool dan battle
- validasi request
- mapping payload parity CI3
- call service otomatis
- opsional generate PDF
- redirect ke daftar jadwal seni

## 3. Service Otomatis Seni

Ditambahkan service baru:
- `app/Services/JadwalSeniOtomatisService.php`

Method utama:
- `generatePool(array $pengaturan)`
- `generateBattle(array $pengaturan)`

Paritas yang dicakup:
- validasi payload inti
- urutan sub kategori seni
- kapasitas per gelanggang
- validasi belum terjadwal
- distribusi item ke gelanggang
- insert `jadwal_seni`
- insert `detail_jadwal_seni`
- create `penampilan_seni` jika belum ada pada sistem pool
- penugasan juri seni
- opsi hasil `jadwal_ids` untuk PDF pasca generate

## 4. Service Penilaian Seni

Ditambahkan service baru:
- `app/Services/PenilaianSeniService.php`

Method utama:
- `tugaskanWasitJuri(int $idPenampilanSeni, int $idGelanggang)`

Paritas yang dicakup:
- ambil metadata penampilan
- baca `jumlah_juri` dari `kategori_lomba`
- cek jumlah row `penilaian_seni`
- reset/recreate row jika kurang
- ambil juri dari `perangkat_pertandingan`
- assign `id_perangkat_pertandingan`
- reset `status_ready = 0`

## 5. View

Ditambahkan view baru:
- `app/Views/admin/super/jadwal_seni/penjadwalan_seni_otomatis.php`

Isi view:
- tab `Sistem Pool`
- tab `Sistem Battle`
- form parity payload CI3
- multi select sub kategori
- multi select gelanggang
- kapasitas pool / jumlah partai per gelanggang
- pilihan jenis penjadwalan battle
- pilihan babak battle
- opsi langsung buat PDF

---

## Mapping Parity CI3 -> CI4

## Pool

CI3:
- `Jadwal_seni::buat_jadwal_seni_sistem_pool_otomatis()`
- `Penjadwalan_otomatis_seni_model::buat_jadwal_sistem_pool()`

CI4:
- `app/Controllers/Admin/Super/PenjadwalanSeniOtomatisController.php::storePool()`
- `app/Services/JadwalSeniOtomatisService.php::generatePool()`

Parity utama:
- payload request sejenis
- distribusi berdasarkan urutan sub kategori
- alokasi ke gelanggang menurut `jumlah_pool`
- validasi belum terjadwal
- pembuatan `jadwal_seni`
- pembuatan `detail_jadwal_seni`
- pembuatan `penampilan_seni`
- assign juri
- update `status_penampilan`
- dukungan langsung generate PDF

## Battle

CI3:
- `Jadwal_seni::buat_jadwal_seni_battle_otomatis()`
- `Penjadwalan_otomatis_seni_model::buat_jadwal_sistem_battle_prestasi()`
- `Penjadwalan_otomatis_seni_model::jadwal_battle_seni_otomatis_pemasalan()`

CI4:
- `app/Controllers/Admin/Super/PenjadwalanSeniOtomatisController.php::storeBattle()`
- `app/Services/JadwalSeniOtomatisService.php::generateBattle()`

Parity utama:
- payload request sejenis
- jenis penjadwalan `prestasi`, `pemasalan_seling_1`, `pemasalan_seling_2`, `pemasalan_seling_3`
- filter babak battle
- exclude battle `BYE`
- validasi battle belum terjadwal
- distribusi ke gelanggang menurut `jumlah_partai`
- pembuatan `jadwal_seni`
- pembuatan `detail_jadwal_seni`
- assign juri untuk penampilan biru dan merah
- dukungan langsung generate PDF

---

## View / Flow / Model / Controller yang Perlu Diketahui

## Controller CI3 yang diaudit
- `../dps/application/controllers/resources/Jadwal_seni.php`

## Model/Service CI3 yang diaudit
- `../dps/application/models/services/penjadwalan_otomatis/Penjadwalan_otomatis_seni_model.php`
- `../dps/application/models/resources/Penilaian_seni_model.php`

## Controller CI4 hasil migrasi
- `app/Controllers/Admin/Super/PenjadwalanSeniOtomatisController.php`

## Service CI4 hasil migrasi
- `app/Services/JadwalSeniOtomatisService.php`
- `app/Services/PenilaianSeniService.php`

## Model CI4 yang dipakai
- `app/Models/JadwalSeniModel.php`
- `app/Models/PenampilanSeniModel.php`
- `app/Models/BattleSeniModel.php`
- `app/Models/KompetisiSeniModel.php`

## View CI4 hasil migrasi
- `app/Views/admin/super/jadwal_seni/penjadwalan_seni_otomatis.php`

---

## Gap / Catatan Parity

Walau flow inti sudah dimigrasikan, masih ada beberapa catatan parity yang perlu diperhatikan:

1. **Sorting battle by nilai babak**
   - CI3 memiliki CASE khusus untuk bobot babak (`Final`, `Semi Final`, dst).
   - CI4 saat ini masih mengurutkan terutama berdasarkan sub kategori, pool, dan nomor battle.
   - Jika diperlukan parity penuh urutan lintas babak, service CI4 perlu ditambah CASE ranking babak yang identik.

2. **Mode pemasalan battle**
   - CI3 punya flow pemasalan dengan selang-seling yang lebih spesifik di layer model.
   - CI4 saat ini menerima value `pemasalan_seling_1/2/3`, tetapi distribusi battle masih memakai distribusi kapasitas gelanggang generik.
   - Jika source CI3 punya pola interleave khusus antar battle, logic itu perlu dipindahkan lebih detail.

3. **Create penampilan pool**
   - CI3 memakai helper/model method khusus `create_penampilan_seni(...)`.
   - CI4 saat ini membuat row `penampilan_seni` minimal bila belum ada.
   - Jika schema real mewajibkan field tambahan saat insert, method ini harus disesuaikan lagi berdasarkan DB production.

4. **Validasi/normalisasi nomor undi**
   - CI3 punya perilaku shuffle saat nomor undi tidak tersedia.
   - CI4 saat ini mengambil `kelompok_peserta_seni` urut `nomor_undi ASC` tanpa shuffle fallback.
   - Bila dibutuhkan parity exact, fallback shuffle perlu ditambahkan.

5. **PDF library option**
   - CI3 menerima pilihan library PDF.
   - CI4 saat ini hanya mengizinkan `mpdf`.

6. **Status/struktur `penilaian_seni`**
   - Implementasi CI4 membuat row `penilaian_seni` minimal dengan field yang diasumsikan tersedia: `id_penampilan_seni`, `id_perangkat_pertandingan`, `status_ready`.
   - Jika skema aktual berbeda, service `PenilaianSeniService` perlu disesuaikan.

---

## Checklist Verifikasi yang Disarankan

Setelah migrasi, lakukan verifikasi berikut di environment yang punya database lengkap:

1. Buka:
   - `admin/super/jadwal-seni/penjadwalan-otomatis`
2. Uji generate **pool**:
   - pilih beberapa sub kategori pool
   - pilih 1..N gelanggang
   - isi `jumlah_pool`
   - generate
   - pastikan row masuk ke `jadwal_seni`
   - pastikan row masuk ke `detail_jadwal_seni`
   - pastikan `penampilan_seni` terbentuk
   - pastikan `penilaian_seni` assigned ke juri gelanggang
3. Uji generate **battle**:
   - pilih sub kategori battle
   - pilih babak battle
   - pilih jenis penjadwalan
   - isi `jumlah_partai`
   - generate
   - pastikan `detail_jadwal_seni.id_battle_seni` terisi
   - pastikan juri ter-assign ke penampilan merah/biru
4. Uji duplicate prevention:
   - generate ulang kategori yang sama
   - pastikan sistem menolak jika sudah terjadwal
5. Uji PDF:
   - centang langsung buat PDF
   - pastikan `pdf_path` update di `jadwal_seni`

---

## File Hasil Migrasi

File baru/diubah dalam pekerjaan ini:
- `app/Config/Routes.php`
- `app/Controllers/Admin/Super/PenjadwalanSeniOtomatisController.php`
- `app/Services/JadwalSeniOtomatisService.php`
- `app/Services/PenilaianSeniService.php`
- `app/Views/admin/super/jadwal_seni/penjadwalan_seni_otomatis.php`
- `docs/penjadwalan-seni-otomatis-pool-battle-ci4.md`

---

## Kesimpulan

Feature penjadwalan seni otomatis untuk **pool** dan **battle** pada source CI3 sudah berhasil dipetakan flow utamanya, lalu dimigrasikan ke CI4 dalam bentuk:
- route baru
- controller baru
- service otomatis baru
- service penugasan juri seni baru
- view form otomatis baru
- dokumen audit/parity ini

Parity inti yang sudah tercapai:
- payload form utama
- generate jadwal pool
- generate jadwal battle
- create detail jadwal
- create/fetch penampilan seni
- assign juri seni
- dukungan generate PDF pasca proses

Parity yang masih parsial dan perlu pendalaman lanjutan terutama ada pada:
- pola urutan battle lintas babak
- perilaku pemasalan selang-seling detail
- fallback shuffle nomor undi
- penyesuaian final terhadap schema produksi `penilaian_seni` dan `penampilan_seni`

# OpenCode CI4 Migration Cheatsheet

Cheatsheet ini menjelaskan command OpenCode yang sudah ditambahkan untuk membantu migrasi project DPS ke CodeIgniter 4.

Jalankan OpenCode dari root project:

```bash
opencode
```

Lalu gunakan command berikut di dalam sesi OpenCode.

## Command Utama

| Command | Kapan Dipakai | Tujuan |
|---|---|---|
| `/plan` | Sebelum mengerjakan fitur/fix umum | Membuat rencana implementasi singkat |
| `/code-review` | Setelah ada perubahan kode | Review bug, regresi, security, dan test gap |
| `/security` | Untuk auth, role, payment, upload, PDF, data sensitif | Review risiko keamanan |
| `/build-fix` | Saat ada error PHP, Composer, PHPUnit, atau CI4 runtime | Diagnosis dan perbaikan minimal |
| `/verify` | Setelah perubahan selesai | Checklist verifikasi umum |
| `/tdd` | Saat ingin implementasi test-first | Panduan test dulu, fix kemudian |
| `/migration-scan` | Saat ingin memetakan status migrasi | Melihat modul mana yang complete/partial/blocked |
| `/migration-plan` | Sebelum migrasi modul tertentu | Rencana migrasi CI3 ke CI4 per modul |
| `/migration-verify` | Setelah migrasi modul tertentu | Cek apakah modul layak ditandai selesai |
| `/db-review` | Saat mengubah migration/model/query | Review database, index, rollback, data safety |
| `/release-audit` | Sebelum deploy/handoff | Audit kesiapan release migrasi |

## Workflow Harian Yang Disarankan

1. Scan status migrasi:

```text
/migration-scan
```

2. Pilih modul yang mau dikerjakan:

```text
/migration-plan sekretariat kontingen
```

3. Kerjakan perubahan kode bersama OpenCode.

4. Review hasil perubahan:

```text
/code-review
```

5. Jika menyentuh auth, role, payment, upload, atau PDF:

```text
/security admin/sekretariat
```

6. Jika menyentuh database:

```text
/db-review app/Database/Migrations app/Models
```

7. Verifikasi modul:

```text
/migration-verify sekretariat kontingen
```

8. Update tracker:

```text
Update docs/migration-ci4-status.md berdasarkan hasil verifikasi terakhir.
```

## Command Migrasi

### `/migration-scan`

Gunakan untuk memetakan kondisi migrasi secara menyeluruh.

Contoh:

```text
/migration-scan
```

Contoh dengan scope:

```text
/migration-scan admin sekretariat
```

Output yang diharapkan:

- Status per modul: `Not Started`, `Partial`, `Needs Review`, `Blocked`, atau `Complete`.
- Gap routes, controllers, models, services, views, migrations, tests.
- Risiko dan prioritas pekerjaan berikutnya.

### `/migration-plan`

Gunakan sebelum mulai migrasi modul.

Contoh:

```text
/migration-plan pembayaran kontingen
```

Contoh lain:

```text
/migration-plan admin sekretariat peserta tanding
```

Output yang diharapkan:

- File yang perlu dicek/diubah.
- Dampak route dan filter.
- Dampak model/service/database.
- Checklist validasi.
- Test/manual QA yang perlu dilakukan.
- Risiko rollback atau data migration.

### `/migration-verify`

Gunakan setelah modul dimigrasikan.

Contoh:

```text
/migration-verify kontingen pembayaran
```

Output yang diharapkan:

- Apakah route sudah lengkap.
- Apakah controller dan view sudah sesuai flow.
- Apakah model/service aman.
- Apakah auth/filter benar.
- Apakah database changes aman.
- Apakah test/manual QA cukup.
- Apakah status di `docs/migration-ci4-status.md` boleh dinaikkan.

### `/db-review`

Gunakan saat ada perubahan database, migration, model, query, atau transaksi.

Contoh:

```text
/db-review app/Database/Migrations/2026-05-24-000001_CreateSekretariatResourceTables.php
```

Contoh lain:

```text
/db-review app/Models app/Services/KategoriTandingService.php
```

Cek utama:

- Migration reversible atau punya rollback note.
- Tidak ada destructive change tanpa rencana recovery.
- `allowedFields` model benar.
- Primary key, timestamp, soft delete sesuai kebutuhan.
- Query builder dipakai dengan aman.
- Transaksi dipakai untuk operasi multi-step yang harus atomic.
- Index cukup untuk query admin/dashboard.

### `/release-audit`

Gunakan sebelum deploy, demo, atau handoff.

Contoh:

```text
/release-audit
```

Output yang diharapkan:

- Daftar blocker release.
- Modul yang belum complete.
- Risiko auth/security/database.
- Status test dan manual QA.
- Catatan environment dan rollback.

## Command Umum

### `/plan`

Gunakan untuk rencana pekerjaan umum, bukan hanya migrasi.

Contoh:

```text
/plan tambah validasi NIK di form pendaftaran
```

### `/code-review`

Gunakan setelah perubahan kode.

Contoh:

```text
/code-review
```

Contoh dengan scope:

```text
/code-review app/Controllers/Admin/Sekretariat
```

Output review akan memprioritaskan:

- Bug.
- Regresi behavior.
- Security issue.
- Data integrity risk.
- Missing tests.

### `/security`

Gunakan untuk area sensitif.

Contoh:

```text
/security pembayaran kontingen
```

Area yang wajib security review:

- Login/logout.
- Admin role dan filters.
- Payment confirmation/rejection.
- Upload file.
- Generated PDF/nota.
- Data NIK/KK/password/payment.
- Form registrasi publik.

### `/build-fix`

Gunakan ketika ada error.

Contoh:

```text
/build-fix PHPUnit gagal di ExampleDatabaseTest
```

Contoh lain:

```text
/build-fix error Undefined variable pada view admin/sekretariat/dashboard.php
```

### `/verify`

Gunakan untuk verifikasi umum.

Contoh:

```text
/verify perubahan terakhir
```

Command yang biasanya dipakai:

```bash
php -l path/to/file.php
composer test
vendor/bin/phpunit
php spark
```

### `/tdd`

Gunakan jika ingin fitur/fix dibuat dengan test dulu.

Contoh:

```text
/tdd validasi peserta tanding tidak boleh duplikat kategori
```

## Tracker Migrasi

Tracker utama ada di:

```text
docs/migration-ci4-status.md
```

Gunakan tracker ini untuk mencatat:

- Modul yang sudah selesai.
- Modul yang masih partial.
- Modul yang blocked.
- Catatan test/manual QA.
- Risiko database/security.

Status yang dipakai:

- `Not Started`
- `Partial`
- `Needs Review`
- `Blocked`
- `Complete`

Jangan ubah status menjadi `Complete` sebelum:

- Routes sudah dicek.
- Controller behavior sudah dicek.
- Model/service sudah dicek.
- View sudah dicek.
- Auth/filter sudah benar.
- Database impact jelas.
- `php -l` untuk file PHP yang disentuh sudah aman.
- Test atau manual QA sudah dicatat.

## Prompt Siap Pakai

Gunakan prompt berikut jika ingin hasil lebih terarah.

```text
/migration-scan fokus pada modul sekretariat. Bandingkan routes, controller, model, service, view, migration, dan test. Beri status per submodul dan rekomendasi prioritas.
```

```text
/migration-plan modul pembayaran kontingen. Pastikan payment state, nota PDF, role access, dan rollback database dibahas.
```

```text
/migration-verify modul peserta tanding. Cek CRUD, validasi kategori, relasi kontingen/pendaftar, auth filter, dan query database.
```

```text
/db-review migration sekretariat terbaru. Fokus pada rollback, destructive change, index, foreign key, dan data lama dari CI3.
```

```text
/release-audit cek kesiapan migrasi sebelum demo. Fokus pada blocker, test gap, security, database migration, dan manual QA.
```

## Kebiasaan Aman

- Selalu mulai modul besar dengan `/migration-plan`.
- Selalu akhiri modul dengan `/migration-verify`.
- Selalu gunakan `/security` untuk auth, role, payment, upload, PDF, dan data sensitif.
- Selalu gunakan `/db-review` sebelum menjalankan migration penting.
- Selalu update `docs/migration-ci4-status.md` setelah verifikasi.

# CI4 Migration Status

Use this tracker as the working source of truth for CI3-to-CI4 migration progress. Update it when a module is planned, migrated, verified, blocked, or released.

Related migration docs:

- `docs/internal/MIGRATION_PLAN_DPS_TO_CI4.md`
- `docs/internal/bendahara-ci4-migration-plan.md`
- `docs/internal/bendahara-ci3-ci4-audit.md`
- `docs/MIGRATION_PLAN_SEKRETARIAT.md`
- `docs/SEKRETARIAT_ATLET_FLOW_MIGRATION_PLAN.md`

## Status Legend

- `Not Started`: no CI4 implementation found or migration has not begun.
- `Partial`: implementation exists but routes, views, data, tests, or QA are incomplete.
- `Needs Review`: implementation appears present but needs focused review before completion.
- `Blocked`: work is waiting on schema, data, environment, decision, or dependency.
- `Complete`: implementation, verification, and documentation are done.

## Module Tracker

| Module | Routes | Controllers | Models/Services | Views | DB/Migrations | Tests/QA | Status | Notes |
|---|---|---|---|---|---|---|---|---|
| Public landing/pendaftaran | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Needs Review | Verify registration behavior, validation, and download routes. |
| Kontingen auth/dashboard | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Needs Review | Verify `kontingenauth` filters and session handling. |
| Kontingen peserta | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Needs Review | Verify create/update/delete flows and participant constraints. |
| Kontingen kategori tanding | Present | Present | Present | Present | Needs Review | Missing/Manual | Needs Review | Verify option loading, limits, and payment implications. |
| Kontingen kategori seni | Present | Present | Present | Present | Needs Review | Missing/Manual | Needs Review | Verify group/member behavior and category constraints. |
| Kontingen pembayaran | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Needs Review | Security-sensitive; verify payment state transitions and receipt/PDF behavior. |
| Admin bendahara | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Needs Review | Cross-check existing bendahara audit docs. |
| Admin sekretariat dashboard | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Partial | Related untracked/current work appears active; verify before marking complete. |
| Admin sekretariat kontingen | Present | Present | Partial | Present | Needs Review | Missing/Manual | Partial | CI4 now includes sekretariat `kontingen/rekap-atlet` route and rekap table with peserta/seni/official aggregates; payment data remains bendahara-only. Verify CI3 table parity, role filter, reset password flow, and pendaftar management. |
| Admin sekretariat data atlet | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Partial | Verify list/detail/export expectations. |
| Admin sekretariat peserta tanding | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Partial | Verify by-pendaftar route and CRUD behavior. |
| Admin sekretariat kelompok seni | Present | Present | Needs Review | Present | Needs Review | Missing/Manual | Partial | Verify member add/remove behavior and constraints. |
| Admin sekretariat pesilat terbaik | Present | Present | Partial | Present | N/A | Missing/Manual | Partial | CI4 now includes Urutan Poin Tanding, Urutan Poin Battle Seni, and Urutan Poin Pool; manual QA and focused tests still pending. |
| Admin sekretariat statistik | Present | Present | Partial | Present | N/A | Missing/Manual | Partial | CI4 now includes Progress Pendaftaran, Statistik Tanding, and Statistik Seni with ApexCharts; lint and route/manual QA still required before completion. |
| Admin super | Present | Minimal | Needs Review | Needs Review | Needs Review | Missing/Manual | Partial | Confirm intended feature scope. |
| Admin super pengaturan event | Present | Present | Partial | Present | Needs Review | Syntax OK/Test Warning/Manual Missing | Partial | Dashboard pengaturan event kini menampilkan ringkasan konfigurasi CI3 untuk medali, peraturan, juara tiga bersama, format penilaian, biaya pendaftaran, sistem penampilan seni, max peserta per pool, dan detail harga per kategori usia. Migrasi lanjutan wajib native CI4, bukan membawa `Config_form_library`/writer CI3. Halaman `profil_kejuaraan` kini mulai dimigrasikan via controller, service, validation, view, dan persistence ke `site_builder_settings`; halaman aman berikutnya tetap `akses_pendaftaran`, `akses_pemilihan_kategori`, dan `konten_halaman_landing`. |
| Admin super pengaturan arsip pendaftar | Present | Complete | Complete | Complete | Complete | Complete | Complete | Phase 1-3 complete: Settings CRUD + toggle active + upload/edit/delete controller + validation helpers. Routes, controller, service methods, helper functions, dan view toggle handler sudah implemented. Pending: Phase 4 view enhancement verification + Phase 5 testing & QA. |
| Admin super pengaturan rekening pembayaran | Present | Complete | Complete | Complete | Complete | Syntax OK/Test Warning | Complete | Rekening pembayaran sekarang database-first via `rekening_pembayaran_accounts`, default kosong, account dinamis tambah/hapus, QR dihapus, dan landing card setting dihapus. PHPUnit filter rekening passed; warning hanya coverage driver missing. |
| Admin super pengaturan kategori lomba | Present | Partial | Partial | Present | Needs Review | Syntax OK/Test Warning/Manual Missing | Partial | Stage 4 masih partial tetapi lebih lengkap: mode pengaturan kategori lomba memakai session historis `perngaturan_kategori_lomba`; kategori usia kini punya route/controller/view detail (`show`) selain create/edit/delete; kategori lomba, kelas tanding, dan sub kategori seni tersedia di `admin/super`. Kelas tanding super admin kini punya route/controller/service/view CI4 untuk list/detail/edit, create single, create multiple, delete, auto tambah pool, update max peserta per pool, guard validasi rentang berat, guard pengurangan kapasitas pool, serta template `bagan_pertandingan` awal via service CI4. Unit test `KelasTandingServiceTest` sudah ditambahkan dan lulus; manual QA parity CI3 end-to-end masih pending. |
| Location helpers | Present | Present | Needs Review | N/A | Needs Review | Missing/Manual | Needs Review | Verify country/province/regency/district/village JSON outputs. |

## Completion Checklist

- Routes are present in `app/Config/Routes.php` and use the intended filters.
- Controller methods preserve CI3 behavior or document intentional behavior changes.
- Models/services use CI4 patterns and have correct `allowedFields`, primary keys, and return types.
- Database migrations are reversible or have documented manual rollback/recovery notes.
- Views escape user-controlled output and preserve Indonesian UI terminology.
- Auth, role, payment, registration, file, and PDF flows have security review.
- `php -l` passes for touched PHP files.
- `composer test` or `vendor/bin/phpunit` has been run, or blockers are documented.
- Manual QA steps are recorded for flows without automated tests.

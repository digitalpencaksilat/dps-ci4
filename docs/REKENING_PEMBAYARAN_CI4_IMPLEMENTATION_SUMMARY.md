# Rekening Pembayaran CI4 Implementation Summary

Status: complete.

## Changes

- Rekening pembayaran sekarang disimpan di `site_builder_settings` key `rekening_pembayaran_accounts`.
- Value disimpan sebagai JSON array dengan `is_array = 1`.
- Default rekening kosong jika DB key belum ada.
- QR code flow dihapus dari setting rekening pembayaran.
- Account rekening bisa ditambah dan dihapus dinamis dari halaman setting.
- View setting rekening tidak lagi memakai `admin-landing-card`.
- Tampilan kontingen/bendahara membaca rekening aktif dari JSON database baru.

## Files

- `app/Services/Admin/Super/SettingWriterService.php`
- `app/Services/Admin/Super/RekeningPembayaranService.php`
- `app/Controllers/Admin/Super/RekeningPembayaranController.php`
- `app/Views/admin/super/pengaturan_event/rekening_pembayaran.php`
- `app/Services/PembayaranKontingenService.php`
- `tests/unit/Services/RekeningPembayaranServiceTest.php`

## Verification

- `php -l` passed for modified PHP files.
- `composer test -- --filter RekeningPembayaran` ran 5 tests and 12 assertions successfully.
- PHPUnit returned exit code 1 only because no code coverage driver is available.

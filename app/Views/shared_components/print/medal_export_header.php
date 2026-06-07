<?php
/**
 * Print header untuk export Perolehan Medali — tema project (putih + garis merah).
 * Logo host (kiri) + judul (tengah, font Oswald) + logo event (kanan).
 * Background putih, garis tegas merah di bawah header. Tanpa wrap text.
 * Inline-style karena print window DataTables tidak memuat CSS project.
 *
 * @var string $title      Judul utama (mis. DATA PEROLEHAN MEDALI TANDING)
 * @var string $subtitle   Nama event
 */
$logoHost = get_setting('event_host_big_logo', 'pendaftaran/gambar_dan_juknis')
    ?? get_setting('event_host_logo', 'pendaftaran/gambar_dan_juknis')
    ?? get_setting('event_logo', 'pendaftaran/gambar_dan_juknis');
$logoEvent = get_setting('event_big_logo', 'pendaftaran/gambar_dan_juknis')
    ?? get_setting('event_logo', 'pendaftaran/gambar_dan_juknis');
?>
<table class="medal-print-header" style="width:100%;border-collapse:collapse;margin:0 0 16px 0;border-bottom:3px solid #c60000;background:#ffffff;">
    <tr>
        <td style="width:14%;background:#ffffff;text-align:center;vertical-align:middle;padding:8px 10px;border:none;">
            <?php if (! empty($logoHost)) : ?>
                <img src="<?= esc((string) $logoHost) ?>" style="max-height:64px;max-width:80px;width:auto;" alt="Logo Host">
            <?php endif; ?>
        </td>
        <td style="background:#ffffff;text-align:center;vertical-align:middle;padding:10px;border:none;">
            <div style="font-family:'Oswald','Poppins',Arial,sans-serif;font-size:22px;font-weight:700;color:#c60000;line-height:1.15;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">
                <?= esc($title ?? 'Data Export') ?>
            </div>
            <?php if (! empty($subtitle)) : ?>
                <div style="font-family:'Poppins',Arial,sans-serif;font-size:13px;font-weight:500;color:#212529;line-height:1.3;margin-top:3px;white-space:nowrap;">
                    <?= esc($subtitle) ?>
                </div>
            <?php endif; ?>
        </td>
        <td style="width:14%;background:#ffffff;text-align:center;vertical-align:middle;padding:8px 10px;border:none;">
            <?php if (! empty($logoEvent)) : ?>
                <img src="<?= esc((string) $logoEvent) ?>" style="max-height:64px;max-width:80px;width:auto;" alt="Logo Event">
            <?php endif; ?>
        </td>
    </tr>
</table>

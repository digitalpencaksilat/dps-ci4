<style>
    .atlet-img {
        width: <?= esc($layout['foto_atlet']['photo_width'] ?? '39mm') ?>;
        height: <?= esc($layout['foto_atlet']['photo_height'] ?? '49mm') ?>;
        inset: <?= esc($layout['foto_atlet']['photo_position'] ?? '4.8cm 0 0 1.5cm') ?>;
        display: <?= esc($layout['foto_atlet']['photo_display'] ?? 'none') ?>;
        position: absolute;
        overflow: hidden;
    }
    .atlet-img img {
        object-fit: cover;
        max-width: 100%;
    }
    .nama-atlet {
        display: <?= esc($layout['nama_atlet']['athlete_name_display'] ?? 'block') ?>;
        width: <?= esc($layout['nama_atlet']['athlete_name_container_width'] ?? '100%') ?>;
        position: absolute;
        font-size: <?= esc($layout['nama_atlet']['athlete_name_font_size'] ?? '13px') ?>;
        color: #0a0909e2;
        margin: 0;
        inset: <?= esc($layout['nama_atlet']['athlete_name_position'] ?? '10.8cm 0 0 1.2cm') ?>;
        text-align: <?= esc($layout['nama_atlet']['athlete_name_text_align'] ?? 'left') ?>;
        font-weight: <?= esc($layout['nama_atlet']['athlete_name_font_weight'] ?? 'bold') ?>;
        text-transform: <?= esc($layout['nama_atlet']['athlete_name_text_transform'] ?? 'uppercase') ?>;
        white-space: <?= esc($layout['nama_atlet']['athlete_name_white_space'] ?? 'nowrap') ?>;
        overflow: hidden;
        text-overflow: clip;
    }
    .kontingen-atlet {
        display: <?= esc($layout['nama_kontingen']['contingent_name_display'] ?? 'block') ?>;
        width: <?= esc($layout['nama_kontingen']['contingent_name_container_width'] ?? '100%') ?>;
        position: absolute;
        font-size: <?= esc($layout['nama_kontingen']['contingent_name_font_size'] ?? '11px') ?>;
        white-space: <?= esc($layout['nama_kontingen']['contingent_name_white_space'] ?? 'nowrap') ?>;
        overflow: hidden;
        inset: <?= esc($layout['nama_kontingen']['contingent_name_position'] ?? '11.7cm 0 0 1.2cm') ?>;
        text-overflow: clip;
        margin: 0;
        color: #0a0909e2;
        text-transform: <?= esc($layout['nama_kontingen']['contingent_name_text_transform'] ?? 'uppercase') ?>;
        font-weight: <?= esc($layout['nama_kontingen']['contingent_name_font_weight'] ?? 'bold') ?>;
        text-align: <?= esc($layout['nama_kontingen']['contingent_name_text_align'] ?? 'left') ?>;
    }
    .barcode {
        display: <?= esc($layout['barcode']['barcode_display'] ?? 'none') ?>;
        position: absolute;
        inset: <?= esc($layout['barcode']['barcode_position'] ?? '4.6cm 0 0 5cm') ?>;
        padding: 0;
        overflow: visible;
        margin: 0;
    }
    .barcode img {
        width: <?= esc($layout['barcode']['barcode_width'] ?? '4cm') ?>;
    }
    .kategori-lomba {
        display: <?= esc($layout['pertandingan']['match_category_display'] ?? 'none') ?>;
        width: <?= esc($layout['pertandingan']['match_category_width'] ?? '100%') ?>;
        position: absolute;
        border-collapse: collapse;
        font-weight: <?= esc($layout['pertandingan']['match_category_font_weight'] ?? 'bolder') ?>;
        text-align: center;
        text-overflow: clip;
        white-space: <?= esc($layout['pertandingan']['match_category_white_space'] ?? 'nowrap') ?>;
        overflow: hidden;
        inset: <?= esc($layout['pertandingan']['match_category_position'] ?? '11cm 0 0 0') ?>;
        font-size: <?= esc($layout['pertandingan']['match_category_font_size'] ?? '14px') ?>;
    }
    .tabel-pertandingan {
        display: <?= esc($layout['pertandingan']['matches_table_display'] ?? 'none') ?>;
        width: <?= esc($layout['pertandingan']['matches_table_width'] ?? '80%') ?>;
        position: absolute;
        border-collapse: collapse;
        inset: <?= esc($layout['pertandingan']['matches_table_position'] ?? '7.75cm 10% 0 10%') ?>;
        font-size: <?= esc($layout['pertandingan']['matches_table_font_size'] ?? '12px') ?>;
        text-align: <?= esc($layout['pertandingan']['matches_table_position_text_align'] ?? 'left') ?>;
        font-weight: <?= esc($layout['pertandingan']['matches_table_font_weight'] ?? 'bold') ?>;
    }
</style>

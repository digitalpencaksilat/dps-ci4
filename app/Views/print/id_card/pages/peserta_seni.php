<?= view('print/id_card/components/card_seni', [
    'peserta'          => $peserta ?? null,
    'partai'           => $partai ?? [],
    'data_penampilan'  => $data_penampilan ?? [],
    'data_battle'      => $data_battle ?? [],
    'background_url'   => $background_url ?? '',
]) ?>

<?php if (! ($is_preview ?? false)) : ?>
<script src="<?= online_asset('jquery_js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
JsBarcode("#bar_seni_<?= esc((string) ($peserta->id_peserta_seni ?? 0)) ?>", "<?= esc($barcode_value ?? '') ?>", {
    format: "ean8",
    width: 1,
    height: 30,
    displayValue: false,
    margin: 0,
});
window.onload = function() { window.print(); };
</script>
<?php endif; ?>

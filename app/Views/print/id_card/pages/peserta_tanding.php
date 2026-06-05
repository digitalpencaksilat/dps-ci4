<?= view('print/id_card/components/card_tanding', [
    'peserta'         => $peserta ?? null,
    'partai'          => $partai ?? [],
    'background_url'  => $background_url ?? '',
]) ?>

<?php if (! ($is_preview ?? false)) : ?>
<script src="<?= online_asset('jquery_js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
JsBarcode("#bar_tanding_<?= esc((string) ($peserta->id_peserta_tanding ?? 0)) ?>", "<?= esc($barcode_value ?? '') ?>", {
    format: "ean8",
    width: 1,
    height: 30,
    displayValue: false,
    margin: 0,
});
window.onload = function() { window.print(); };
</script>
<?php endif; ?>
